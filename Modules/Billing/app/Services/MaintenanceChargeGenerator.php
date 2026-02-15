<?php

namespace Modules\Billing\Services;

use Illuminate\Support\Collection;
use Modules\Apartment\Models\MaintenanceSlab;
use Modules\Apartment\Models\Unit;
use Modules\Billing\Models\Charge;
use Modules\Billing\Support\BillingQuarter;

class MaintenanceChargeGenerator
{
    /**
     * @return Collection<int, Charge>
     */
    public function generate(string $billingQuarter, ?string $dueDate = null): Collection
    {
        $units = Unit::all();
        $charges = collect();

        foreach ($units as $unit) {
            $existing = Charge::where('unit_id', $unit->id)
                ->where('billing_month', $billingQuarter)
                ->where('type', 'maintenance')
                ->exists();

            if ($existing) {
                continue;
            }

            $rate = MaintenanceSlab::currentRate($unit->flat_type);

            if ($rate === null) {
                continue;
            }

            $charges->push(Charge::create([
                'unit_id' => $unit->id,
                'type' => 'maintenance',
                'description' => "Maintenance for " . BillingQuarter::label($billingQuarter),
                'amount' => $rate * 3,
                'billing_month' => $billingQuarter,
                'due_date' => $dueDate,
                'status' => 'pending',
            ]));
        }

        return $charges;
    }
}
