<?php

namespace Modules\Sheet\Services;

use Modules\Apartment\Models\Unit;
use Modules\Billing\Models\Charge;
use Modules\Billing\Models\Expense;
use Modules\Billing\Models\Payment;
use Modules\Billing\Support\BillingQuarter;
use Revolution\Google\Sheets\Facades\Sheets;

class SheetSyncService
{
    private string $spreadsheetId;

    public function __construct()
    {
        $this->spreadsheetId = config('services.google.sheet_id', '');
    }

    public function syncMonthlyTab(string $billingQuarter): void
    {
        $sheetName = $this->monthTabName($billingQuarter);
        $range = BillingQuarter::dateRange($billingQuarter);

        $payments = Payment::whereBetween('paid_date', [$range['start'], $range['end']])
            ->with(['unit', 'charge'])
            ->orderBy('paid_date')
            ->get();

        $expenses = Expense::whereBetween('paid_date', [$range['start'], $range['end']])
            ->orderBy('paid_date')
            ->get();

        $header = ['Date', 'Unit', 'Resident', 'Amount', 'Type', 'Source', 'Reconciliation'];
        $rows = [$header];

        foreach ($payments as $payment) {
            $rows[] = [
                $payment->paid_date->format('d-m-Y'),
                $payment->unit?->flat_number ?? '',
                $payment->unit?->residents()->first()?->name ?? '',
                $payment->amount,
                "Income - " . ($payment->charge?->type ?? 'payment'),
                $payment->source,
                $payment->reconciliation_status,
            ];
        }

        foreach ($expenses as $expense) {
            $rows[] = [
                $expense->paid_date->format('d-m-Y'),
                '',
                '',
                "-{$expense->amount}",
                "Expense - {$expense->category}",
                $expense->source,
                $expense->reconciliation_status,
            ];
        }

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

        Sheets::spreadsheet($this->spreadsheetId)
            ->sheet('Summary')
            ->update($rows);
    }

    private function monthTabName(string $billingQuarter): string
    {
        return BillingQuarter::label($billingQuarter);
    }
}
