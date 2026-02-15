<?php

namespace Modules\Import\Services;

use Modules\Apartment\Models\Resident;
use Modules\Billing\Models\Charge;
use Modules\Billing\Models\Expense;
use Modules\Billing\Models\Payment;
use Modules\Import\Models\ParsedTransaction;

class TransactionMatcher
{
    public function match(ParsedTransaction $parsed): void
    {
        $duplicate = ParsedTransaction::where('fingerprint', $parsed->fingerprint)
            ->where('id', '!=', $parsed->id)
            ->whereIn('reconciliation_status', ['auto_matched', 'manual_matched', 'reconciled'])
            ->exists();

        if ($duplicate) {
            $parsed->update(['reconciliation_status' => 'reconciled']);

            return;
        }

        if ($parsed->direction === 'credit') {
            $this->matchCredit($parsed);
        } else {
            $this->matchDebit($parsed);
        }
    }

    public function reconcileFromBank(ParsedTransaction $parsed): void
    {
        $duplicate = ParsedTransaction::where('fingerprint', $parsed->fingerprint)
            ->where('id', '!=', $parsed->id)
            ->whereIn('reconciliation_status', ['auto_matched', 'manual_matched', 'reconciled'])
            ->exists();

        if ($duplicate) {
            $parsed->update(['reconciliation_status' => 'reconciled']);

            return;
        }

        if ($parsed->direction === 'credit') {
            $this->reconcileCreditFromBank($parsed);
        } else {
            $this->reconcileDebitFromBank($parsed);
        }
    }

    private function matchCredit(ParsedTransaction $parsed): void
    {
        if (! $parsed->sender_name) {
            return;
        }

        $resident = $this->findResidentByGpayName($parsed->sender_name);

        if (! $resident) {
            return;
        }

        $charge = Charge::where('unit_id', $resident->unit_id)
            ->where('status', '!=', 'paid')
            ->orderBy('billing_month')
            ->first();

        $payment = Payment::create([
            'charge_id' => $charge?->id,
            'unit_id' => $resident->unit_id,
            'amount' => $parsed->amount,
            'paid_date' => $parsed->date,
            'source' => 'gpay',
            'matched_by' => 'auto',
            'reconciliation_status' => 'pending_verification',
        ]);

        $charge?->updateStatus();

        $parsed->update([
            'match_type' => 'payment',
            'matched_payment_id' => $payment->id,
            'reconciliation_status' => 'auto_matched',
        ]);
    }

    private function matchDebit(ParsedTransaction $parsed): void
    {
        $expense = Expense::create([
            'description' => $parsed->sender_name ?? 'Unknown expense',
            'amount' => $parsed->amount,
            'paid_date' => $parsed->date,
            'category' => 'other',
            'source' => 'gpay',
            'reconciliation_status' => 'pending_verification',
        ]);

        $parsed->update([
            'match_type' => 'expense',
            'matched_expense_id' => $expense->id,
            'reconciliation_status' => 'auto_matched',
        ]);
    }

    private function reconcileCreditFromBank(ParsedTransaction $parsed): void
    {
        $payment = Payment::where('amount', $parsed->amount)
            ->whereBetween('paid_date', [
                $parsed->date->copy()->subDay(),
                $parsed->date->copy()->addDay(),
            ])
            ->where('reconciliation_status', 'pending_verification')
            ->first();

        if ($payment) {
            $payment->update(['reconciliation_status' => 'bank_verified']);

            $parsed->update([
                'match_type' => 'payment',
                'matched_payment_id' => $payment->id,
                'reconciliation_status' => 'reconciled',
            ]);

            return;
        }

        $this->matchCredit($parsed);
    }

    private function reconcileDebitFromBank(ParsedTransaction $parsed): void
    {
        $expense = Expense::where('amount', $parsed->amount)
            ->whereBetween('paid_date', [
                $parsed->date->copy()->subDay(),
                $parsed->date->copy()->addDay(),
            ])
            ->where('reconciliation_status', 'pending_verification')
            ->first();

        if ($expense) {
            $expense->update(['reconciliation_status' => 'bank_verified']);

            $parsed->update([
                'match_type' => 'expense',
                'matched_expense_id' => $expense->id,
                'reconciliation_status' => 'reconciled',
            ]);

            return;
        }

        $this->matchDebit($parsed);
    }

    private function findResidentByGpayName(string $senderName): ?Resident
    {
        return Resident::whereNotNull('gpay_name')
            ->get()
            ->first(function ($resident) use ($senderName) {
                $gpayName = strtolower($resident->gpay_name);
                $sender = strtolower($senderName);

                return str_contains($gpayName, $sender)
                    || str_contains($sender, $gpayName);
            });
    }
}
