<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;
use Modules\Apartment\Models\Unit;
use Modules\Billing\Models\Charge;
use Modules\Billing\Models\Expense;
use Modules\Billing\Models\Payment;
use Modules\Billing\Support\BillingQuarter;
use Modules\Import\Models\ParsedTransaction;
use Modules\Import\Models\Upload;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        $quarterRange = BillingQuarter::dateRange(BillingQuarter::current());

        $collectedThisQuarter = Payment::whereBetween('paid_date', [$quarterRange['start'], $quarterRange['end']])
            ->sum('amount');

        $pendingDues = Charge::where('status', '!=', 'paid')->sum('amount')
            - Payment::whereHas('charge', fn ($q) => $q->where('status', '!=', 'paid'))->sum('amount');

        $totalExpensesThisQuarter = Expense::whereBetween('paid_date', [$quarterRange['start'], $quarterRange['end']])
            ->sum('amount');

        $unitBalances = Unit::with('residents:id,unit_id,name')
            ->get()
            ->map(function ($unit) {
                $totalDue = Charge::where('unit_id', $unit->id)->sum('amount');
                $totalPaid = Payment::where('unit_id', $unit->id)->sum('amount');

                return [
                    'id' => $unit->id,
                    'flat_number' => $unit->flat_number,
                    'flat_type' => $unit->flat_type,
                    'resident' => $unit->residents->first()?->name ?? '-',
                    'total_due' => (float) $totalDue,
                    'total_paid' => (float) $totalPaid,
                    'balance' => (float) $totalDue - (float) $totalPaid,
                ];
            })
            ->sortBy('flat_number')
            ->values();

        $reconciliation = [
            'pending_verification' => Payment::where('reconciliation_status', 'pending_verification')->count()
                + Expense::where('reconciliation_status', 'pending_verification')->count(),
            'bank_verified' => Payment::where('reconciliation_status', 'bank_verified')->count()
                + Expense::where('reconciliation_status', 'bank_verified')->count(),
            'unmatched' => ParsedTransaction::where('reconciliation_status', 'unmatched')->count(),
        ];

        $recentUploads = Upload::latest()
            ->take(5)
            ->get()
            ->map(fn ($u) => [
                'id' => $u->id,
                'type' => $u->type,
                'status' => $u->status,
                'created_at' => $u->created_at->format('d-m-Y H:i'),
                'transactions_count' => $u->parsedTransactions()->count(),
            ]);

        return Inertia::render('Dashboard', [
            'collectedThisQuarter' => (float) $collectedThisQuarter,
            'pendingDues' => max(0, (float) $pendingDues),
            'totalExpensesThisQuarter' => (float) $totalExpensesThisQuarter,
            'unitBalances' => $unitBalances,
            'reconciliation' => $reconciliation,
            'recentUploads' => $recentUploads,
            'currentQuarter' => BillingQuarter::label(BillingQuarter::current()),
        ]);
    }
}
