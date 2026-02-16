<?php

namespace Modules\Sheet\Services;

use Carbon\Carbon;
use Google\Client as GoogleClient;
use Google\Service\Exception as GoogleServiceException;
use Google\Service\Sheets as GoogleSheets;
use Google\Service\Sheets\AddSheetRequest;
use Google\Service\Sheets\BatchUpdateSpreadsheetRequest;
use Google\Service\Sheets\Request as SheetRequest;
use Google\Service\Sheets\SheetProperties;
use Modules\Apartment\Models\Unit;
use Modules\Billing\Models\Charge;
use Modules\Billing\Models\Expense;
use Modules\Billing\Models\Payment;
use Revolution\Google\Sheets\Facades\Sheets;

class SheetSyncService
{
    private string $spreadsheetId;

    public function __construct()
    {
        $this->spreadsheetId = config('services.google.sheet_id', '');
    }

    public function syncMonth(int $year, int $month): void
    {
        $this->syncMonthlyTab("{$year}-" . str_pad($month, 2, '0', STR_PAD_LEFT));
    }

    public function syncMonthlyTab(string $month): void
    {
        $date = Carbon::createFromFormat('Y-m', $month);
        $start = $date->copy()->startOfMonth();
        $end = $date->copy()->endOfMonth();

        $payments = Payment::whereBetween('paid_date', [$start, $end])
            ->with(['unit', 'charge', 'addedBy'])
            ->orderBy('paid_date')
            ->get();

        $expenses = Expense::whereBetween('paid_date', [$start, $end])
            ->with('addedBy')
            ->orderBy('paid_date')
            ->get();

        $incomeTotal = (float) $payments->sum('amount');
        $expenseTotal = (float) $expenses->sum('amount');
        $netBalance = $incomeTotal - $expenseTotal;

        $header = ['Date', 'Type', 'Category', 'Description', 'Amount', 'Receipt', 'Added By', 'Timestamp'];

        $totalsRow = [
            'Totals',
            '',
            '',
            "Net Balance: " . $this->formatAmount($netBalance),
            "Income: " . $this->formatAmount($incomeTotal) . " / Expenses: " . $this->formatAmount($expenseTotal),
            '',
            '',
            '',
        ];

        $rows = [$header, $totalsRow];

        $combined = collect();

        foreach ($payments as $payment) {
            $combined->push([
                'date' => $payment->paid_date,
                'type' => 'income',
                'category' => $payment->charge?->type ?? 'payment',
                'description' => "Flat {$payment->unit?->flat_number} - " . ($payment->charge?->description ?? 'Payment'),
                'amount' => (float) $payment->amount,
                'receipt' => $payment->receipt_path ?? '',
                'added_by' => $payment->addedBy?->name ?? '',
                'timestamp' => $payment->created_at->toIso8601String(),
            ]);
        }

        foreach ($expenses as $expense) {
            $combined->push([
                'date' => $expense->paid_date,
                'type' => 'expense',
                'category' => $expense->category,
                'description' => $expense->description,
                'amount' => (float) $expense->amount,
                'receipt' => $expense->receipt_path ?? '',
                'added_by' => $expense->addedBy?->name ?? '',
                'timestamp' => $expense->created_at->toIso8601String(),
            ]);
        }

        $combined->sortBy('date')->each(function ($item) use (&$rows) {
            $rows[] = [
                $item['date']->format('Y-m-d'),
                $item['type'],
                $item['category'],
                $item['description'],
                $this->formatAmount($item['amount']),
                $item['receipt'],
                $item['added_by'],
                $item['timestamp'],
            ];
        });

        $sheetName = $this->monthTabName($month);

        $this->ensureSheetExists($sheetName);

        Sheets::spreadsheet($this->spreadsheetId)
            ->sheet($sheetName)
            ->update($rows);
    }

    public function syncSummaryTab(): void
    {
        $units = Unit::with('residents')->orderBy('flat_number')->get();
        $months = Charge::distinct()->pluck('billing_month')->sort()->values();

        $header = ['Unit', 'Resident'];

        foreach ($months as $month) {
            $header[] = $month;
        }

        $header = array_merge($header, ['Total Due', 'Total Paid', 'Balance']);

        $rows = [$header];

        foreach ($units as $unit) {
            $row = [
                $unit->flat_number,
                $unit->residents->first()?->name ?? '',
            ];

            $totalDue = 0;
            $totalPaid = 0;

            foreach ($months as $month) {
                $due = Charge::where('unit_id', $unit->id)
                    ->where('billing_month', $month)
                    ->sum('amount');

                $paid = Payment::where('unit_id', $unit->id)
                    ->whereHas('charge', fn ($q) => $q->where('billing_month', $month))
                    ->sum('amount');

                $totalDue += (float) $due;
                $totalPaid += (float) $paid;

                $row[] = $paid > 0
                    ? "Rs.{$paid} / Rs.{$due}"
                    : "Rs.{$due} due";
            }

            $row[] = "Rs.{$totalDue}";
            $row[] = "Rs.{$totalPaid}";
            $row[] = 'Rs.' . ($totalDue - $totalPaid);

            $rows[] = $row;
        }

        $this->ensureSheetExists('Summary');

        Sheets::spreadsheet($this->spreadsheetId)
            ->sheet('Summary')
            ->update($rows);
    }

    public function monthTabName(string $month): string
    {
        $date = Carbon::createFromFormat('Y-m', $month);

        return $date->format('M Y');
    }

    private function ensureSheetExists(string $sheetName): void
    {
        $service = $this->getSheetsService();

        try {
            $service->spreadsheets_values->get($this->spreadsheetId, $sheetName);
        } catch (GoogleServiceException $e) {
            if ($e->getCode() !== 400) {
                throw $e;
            }

            $addSheet = new SheetRequest([
                'addSheet' => new AddSheetRequest([
                    'properties' => new SheetProperties([
                        'title' => $sheetName,
                    ]),
                ]),
            ]);

            $service->spreadsheets->batchUpdate(
                $this->spreadsheetId,
                new BatchUpdateSpreadsheetRequest(['requests' => [$addSheet]])
            );
        }
    }

    private function getSheetsService(): GoogleSheets
    {
        if (app()->bound(GoogleSheets::class)) {
            return app(GoogleSheets::class);
        }

        $client = new GoogleClient;
        $client->setAuthConfig(base_path(config('google.service.file')));
        $client->setScopes([GoogleSheets::SPREADSHEETS]);

        return new GoogleSheets($client);
    }

    private function formatAmount(float $amount): string
    {
        return '₹' . number_format($amount, 0);
    }
}
