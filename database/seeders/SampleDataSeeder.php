<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Apartment\Models\MaintenanceSlab;
use Modules\Apartment\Models\Resident;
use Modules\Apartment\Models\Unit;
use Modules\Billing\Models\Charge;
use Modules\Billing\Models\Expense;
use Modules\Billing\Models\Payment;

class SampleDataSeeder extends Seeder
{
    public function run(): void
    {
        MaintenanceSlab::create(['flat_type' => '1BHK', 'amount' => 1500.00, 'effective_from' => '2025-01-01']);
        MaintenanceSlab::create(['flat_type' => '2BHK', 'amount' => 2500.00, 'effective_from' => '2025-01-01']);
        MaintenanceSlab::create(['flat_type' => '3BHK', 'amount' => 3500.00, 'effective_from' => '2025-01-01']);

        $units = [
            ['flat_number' => 'G01', 'flat_type' => '2BHK', 'floor' => 0, 'area_sqft' => 950, 'resident' => ['name' => 'Rajesh Kumar', 'phone' => '9876543210', 'gpay_name' => 'Rajesh Kumar']],
            ['flat_number' => 'G02', 'flat_type' => '3BHK', 'floor' => 0, 'area_sqft' => 1200, 'resident' => ['name' => 'Priya Sharma', 'phone' => '9876543211', 'gpay_name' => 'Priya S']],
            ['flat_number' => '101', 'flat_type' => '2BHK', 'floor' => 1, 'area_sqft' => 950, 'resident' => ['name' => 'Karthick Selvam', 'phone' => '9876543212', 'gpay_name' => 'Karthick S']],
            ['flat_number' => '102', 'flat_type' => '1BHK', 'floor' => 1, 'area_sqft' => 650, 'resident' => ['name' => 'Anitha Rajan', 'phone' => '9876543213', 'gpay_name' => 'Anitha R']],
            ['flat_number' => '201', 'flat_type' => '3BHK', 'floor' => 2, 'area_sqft' => 1200, 'resident' => ['name' => 'Venkatesh Iyer', 'phone' => '9876543214', 'gpay_name' => 'Venkatesh']],
            ['flat_number' => '202', 'flat_type' => '2BHK', 'floor' => 2, 'area_sqft' => 950, 'resident' => ['name' => 'Lakshmi Narayanan', 'phone' => '9876543215', 'gpay_name' => 'Lakshmi N']],
            ['flat_number' => '301', 'flat_type' => '2BHK', 'floor' => 3, 'area_sqft' => 950, 'resident' => ['name' => 'Suresh Babu', 'phone' => '9876543216', 'gpay_name' => 'Suresh B']],
            ['flat_number' => '302', 'flat_type' => '1BHK', 'floor' => 3, 'area_sqft' => 650, 'resident' => ['name' => 'Deepa Krishnan', 'phone' => '9876543217', 'gpay_name' => 'Deepa K']],
            ['flat_number' => '401', 'flat_type' => '3BHK', 'floor' => 4, 'area_sqft' => 1200, 'resident' => ['name' => 'Ramesh Chandran', 'phone' => '9876543218', 'gpay_name' => 'Ramesh C']],
            ['flat_number' => '402', 'flat_type' => '2BHK', 'floor' => 4, 'area_sqft' => 950, 'resident' => ['name' => 'Meena Sundaram', 'phone' => '9876543219', 'gpay_name' => 'Meena S']],
        ];

        foreach ($units as $unitData) {
            $residentData = $unitData['resident'];
            unset($unitData['resident']);

            $unit = Unit::create($unitData);

            Resident::create([
                'unit_id' => $unit->id,
                'name' => $residentData['name'],
                'phone' => $residentData['phone'],
                'email' => strtolower(str_replace(' ', '.', $residentData['name'])) . '@example.com',
                'is_owner' => true,
                'gpay_name' => $residentData['gpay_name'],
            ]);
        }

        $billingMonths = ['2026-01', '2026-02'];

        foreach ($billingMonths as $month) {
            foreach (Unit::all() as $unit) {
                $rate = MaintenanceSlab::currentRate($unit->flat_type);

                if ($rate) {
                    Charge::create([
                        'unit_id' => $unit->id,
                        'type' => 'maintenance',
                        'description' => "Maintenance for {$month}",
                        'amount' => $rate,
                        'billing_month' => $month,
                        'due_date' => "{$month}-10",
                        'status' => 'pending',
                    ]);
                }
            }
        }

        $janCharges = Charge::where('billing_month', '2026-01')
            ->where('type', 'maintenance')
            ->get();

        foreach ($janCharges->take(8) as $charge) {
            Payment::create([
                'charge_id' => $charge->id,
                'unit_id' => $charge->unit_id,
                'amount' => $charge->amount,
                'paid_date' => '2026-01-' . str_pad(rand(5, 15), 2, '0', STR_PAD_LEFT),
                'source' => collect(['gpay', 'bank_transfer', 'cash'])->random(),
                'matched_by' => 'manual',
                'reconciliation_status' => 'bank_verified',
            ]);

            $charge->updateStatus();
        }

        $febCharges = Charge::where('billing_month', '2026-02')
            ->where('type', 'maintenance')
            ->get();

        foreach ($febCharges->take(4) as $charge) {
            Payment::create([
                'charge_id' => $charge->id,
                'unit_id' => $charge->unit_id,
                'amount' => $charge->amount,
                'paid_date' => '2026-02-' . str_pad(rand(5, 12), 2, '0', STR_PAD_LEFT),
                'source' => 'gpay',
                'matched_by' => 'manual',
                'reconciliation_status' => 'pending_verification',
            ]);

            $charge->updateStatus();
        }

        Expense::create(['description' => 'Electricity bill - Common areas', 'amount' => 8500.00, 'paid_date' => '2026-01-20', 'category' => 'electricity', 'source' => 'bank_transfer', 'reconciliation_status' => 'bank_verified']);
        Expense::create(['description' => 'Water supply charges', 'amount' => 3200.00, 'paid_date' => '2026-01-25', 'category' => 'water', 'source' => 'bank_transfer', 'reconciliation_status' => 'bank_verified']);
        Expense::create(['description' => 'Lift maintenance', 'amount' => 5000.00, 'paid_date' => '2026-02-05', 'category' => 'maintenance', 'source' => 'gpay', 'reconciliation_status' => 'pending_verification']);
        Expense::create(['description' => 'Security guard salary', 'amount' => 12000.00, 'paid_date' => '2026-02-01', 'category' => 'service', 'source' => 'bank_transfer', 'reconciliation_status' => 'pending_verification']);

        foreach (Unit::all() as $unit) {
            Charge::create([
                'unit_id' => $unit->id,
                'type' => 'ad-hoc',
                'description' => 'Pongal festival fund',
                'amount' => 500.00,
                'billing_month' => '2026-01',
                'due_date' => '2026-01-10',
                'status' => 'pending',
            ]);
        }
    }
}
