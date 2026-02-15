# Apartment Management System — Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Build a Laravel 12 apartment management system with GPay/bank statement import, auto-matching, reconciliation, and Google Sheets as the primary record.

**Architecture:** Modular Laravel 12 app using nwidart/laravel-modules (Apartment, Billing, Import, Sheet). Inertia.js + Vue 3 frontend for committee members. Laravel AI SDK for OCR/PDF parsing. Queued jobs for async processing and Google Sheets sync.

**Tech Stack:** Laravel 12, Inertia.js, Vue 3, Tailwind CSS, nwidart/laravel-modules, laravel/ai, revolution/laravel-google-sheets, Pest for testing, SQLite for dev.

---

## Phase 1: Project Scaffolding

### Task 1: Create Laravel Project & Install Core Dependencies

**Files:**
- Create: Laravel project at `/Users/karthick/projects/apartment-management/`

**Step 1: Create Laravel project**

Run:
```bash
composer create-project laravel/laravel:^12.0 . --prefer-dist
```

**Step 2: Install core packages**

Run:
```bash
composer require nwidart/laravel-modules filament/filament:"^3.3" -W
```

**Step 3: Set up Filament**

Run:
```bash
php artisan filament:install --panels
```

**Step 4: Configure SQLite database**

Edit `.env`:
```
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite
```

Run:
```bash
touch database/database.sqlite
php artisan migrate
```

**Step 5: Publish laravel-modules config**

Run:
```bash
php artisan vendor:publish --provider="Nwidart\Modules\LaravelModulesServiceProvider" --tag="config"
```

**Step 6: Create Filament admin user**

Run:
```bash
php artisan make:filament-user
```
Use: admin@apartment.local / password

**Step 7: Initialize git and commit**

Run:
```bash
git init
git add -A
git commit -m "scaffold Laravel 12 with Filament and laravel-modules"
```

---

## Phase 2: Apartment Module (Units, Residents, Slabs)

### Task 2: Create Apartment Module & Unit Model

**Files:**
- Create: `app-modules/apartment/` (via artisan)
- Create: Unit model, migration, factory, seeder

**Step 1: Generate module**

Run:
```bash
php artisan module:make Apartment
```

**Step 2: Create Unit model with migration and factory**

Run:
```bash
php artisan make:model Unit --module=Apartment -mf
```

**Step 3: Write the Unit migration**

Edit: `app-modules/apartment/database/migrations/xxxx_create_units_table.php`

```php
public function up(): void
{
    Schema::create('units', function (Blueprint $table) {
        $table->id();
        $table->string('flat_number')->unique();
        $table->string('flat_type'); // 1BHK, 2BHK, 3BHK
        $table->integer('floor');
        $table->decimal('area_sqft', 8, 2)->nullable();
        $table->timestamps();
    });
}
```

**Step 4: Write the Unit model**

Edit: `app-modules/apartment/src/Models/Unit.php`

```php
<?php

namespace Modules\Apartment\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Unit extends Model
{
    use HasFactory;

    protected $fillable = [
        'flat_number',
        'flat_type',
        'floor',
        'area_sqft',
    ];

    protected $casts = [
        'area_sqft' => 'decimal:2',
    ];

    public function residents(): HasMany
    {
        return $this->hasMany(Resident::class);
    }
}
```

**Step 5: Write the Unit factory**

Edit: `app-modules/apartment/database/factories/UnitFactory.php`

```php
<?php

namespace Modules\Apartment\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Apartment\Models\Unit;

class UnitFactory extends Factory
{
    protected $model = Unit::class;

    public function definition(): array
    {
        $flatTypes = ['1BHK', '2BHK', '3BHK'];
        $floor = $this->faker->numberBetween(1, 5);

        return [
            'flat_number' => "{$floor}{$this->faker->unique()->numberBetween(1, 10)}",
            'flat_type' => $this->faker->randomElement($flatTypes),
            'floor' => $floor,
            'area_sqft' => $this->faker->randomFloat(2, 500, 1500),
        ];
    }
}
```

**Step 6: Write test for Unit model**

Run:
```bash
php artisan make:test UnitTest --module=Apartment
```

Edit: `app-modules/apartment/tests/Feature/UnitTest.php`

```php
<?php

use Modules\Apartment\Models\Unit;

it('can create a unit', function () {
    $unit = Unit::factory()->create([
        'flat_number' => '101',
        'flat_type' => '2BHK',
        'floor' => 1,
        'area_sqft' => 950.00,
    ]);

    expect($unit)->toBeInstanceOf(Unit::class)
        ->and($unit->flat_number)->toBe('101')
        ->and($unit->flat_type)->toBe('2BHK');
});

it('enforces unique flat numbers', function () {
    Unit::factory()->create(['flat_number' => '101']);
    Unit::factory()->create(['flat_number' => '101']);
})->throws(\Illuminate\Database\QueryException::class);
```

**Step 7: Run migration and tests**

Run:
```bash
php artisan migrate
php artisan test --filter=UnitTest
```
Expected: 2 tests pass

**Step 8: Commit**

```bash
git add -A
git commit -m "add Apartment module with Unit model, migration, factory, and tests"
```

---

### Task 3: Resident Model

**Files:**
- Create: Resident model, migration, factory in Apartment module

**Step 1: Create Resident model with migration and factory**

Run:
```bash
php artisan make:model Resident --module=Apartment -mf
```

**Step 2: Write the Resident migration**

```php
public function up(): void
{
    Schema::create('residents', function (Blueprint $table) {
        $table->id();
        $table->foreignId('unit_id')->constrained()->cascadeOnDelete();
        $table->string('name');
        $table->string('phone')->nullable();
        $table->string('email')->nullable();
        $table->boolean('is_owner')->default(false);
        $table->string('gpay_name')->nullable();
        $table->timestamps();
    });
}
```

**Step 3: Write the Resident model**

```php
<?php

namespace Modules\Apartment\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Resident extends Model
{
    use HasFactory;

    protected $fillable = [
        'unit_id',
        'name',
        'phone',
        'email',
        'is_owner',
        'gpay_name',
    ];

    protected $casts = [
        'is_owner' => 'boolean',
    ];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }
}
```

**Step 4: Write the Resident factory**

```php
<?php

namespace Modules\Apartment\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Apartment\Models\Resident;
use Modules\Apartment\Models\Unit;

class ResidentFactory extends Factory
{
    protected $model = Resident::class;

    public function definition(): array
    {
        return [
            'unit_id' => Unit::factory(),
            'name' => $this->faker->name(),
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->safeEmail(),
            'is_owner' => $this->faker->boolean(70),
            'gpay_name' => $this->faker->name(),
        ];
    }
}
```

**Step 5: Add residents relationship to Unit model**

Already done in Task 2.

**Step 6: Write tests**

```php
<?php

use Modules\Apartment\Models\Resident;
use Modules\Apartment\Models\Unit;

it('can create a resident linked to a unit', function () {
    $unit = Unit::factory()->create(['flat_number' => '201']);
    $resident = Resident::factory()->create([
        'unit_id' => $unit->id,
        'name' => 'Karthick',
        'gpay_name' => 'Karthick S',
    ]);

    expect($resident->unit->flat_number)->toBe('201')
        ->and($resident->gpay_name)->toBe('Karthick S');
});

it('cascades delete when unit is deleted', function () {
    $unit = Unit::factory()->create();
    Resident::factory()->create(['unit_id' => $unit->id]);

    $unit->delete();

    expect(Resident::count())->toBe(0);
});
```

**Step 7: Run migration and tests**

```bash
php artisan migrate
php artisan test --filter=ResidentTest
```

**Step 8: Commit**

```bash
git add -A
git commit -m "add Resident model with unit relationship and tests"
```

---

### Task 4: MaintenanceSlab Model

**Files:**
- Create: MaintenanceSlab model, migration, factory in Apartment module

**Step 1: Create model**

Run:
```bash
php artisan make:model MaintenanceSlab --module=Apartment -mf
```

**Step 2: Write migration**

```php
public function up(): void
{
    Schema::create('maintenance_slabs', function (Blueprint $table) {
        $table->id();
        $table->string('flat_type'); // 1BHK, 2BHK, 3BHK
        $table->decimal('amount', 10, 2);
        $table->date('effective_from');
        $table->timestamps();

        $table->index(['flat_type', 'effective_from']);
    });
}
```

**Step 3: Write model with scope for active slab**

```php
<?php

namespace Modules\Apartment\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class MaintenanceSlab extends Model
{
    use HasFactory;

    protected $fillable = [
        'flat_type',
        'amount',
        'effective_from',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'effective_from' => 'date',
    ];

    public function scopeActiveFor(Builder $query, string $flatType, ?string $date = null): Builder
    {
        $date ??= now()->toDateString();

        return $query->where('flat_type', $flatType)
            ->where('effective_from', '<=', $date)
            ->orderByDesc('effective_from');
    }

    public static function currentRate(string $flatType, ?string $date = null): ?float
    {
        return static::activeFor($flatType, $date)->value('amount');
    }
}
```

**Step 4: Write factory**

```php
<?php

namespace Modules\Apartment\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Apartment\Models\MaintenanceSlab;

class MaintenanceSlabFactory extends Factory
{
    protected $model = MaintenanceSlab::class;

    public function definition(): array
    {
        return [
            'flat_type' => $this->faker->randomElement(['1BHK', '2BHK', '3BHK']),
            'amount' => $this->faker->randomFloat(2, 1000, 5000),
            'effective_from' => $this->faker->date(),
        ];
    }
}
```

**Step 5: Write tests**

```php
<?php

use Modules\Apartment\Models\MaintenanceSlab;

it('returns the active slab rate for a flat type', function () {
    MaintenanceSlab::factory()->create([
        'flat_type' => '2BHK',
        'amount' => 1500.00,
        'effective_from' => '2025-01-01',
    ]);

    MaintenanceSlab::factory()->create([
        'flat_type' => '2BHK',
        'amount' => 2000.00,
        'effective_from' => '2026-01-01',
    ]);

    $rate = MaintenanceSlab::currentRate('2BHK', '2026-02-15');

    expect($rate)->toBe(2000.00);
});

it('returns null when no slab exists', function () {
    $rate = MaintenanceSlab::currentRate('4BHK');

    expect($rate)->toBeNull();
});
```

**Step 6: Run migration and tests**

```bash
php artisan migrate
php artisan test --filter=MaintenanceSlabTest
```

**Step 7: Commit**

```bash
git add -A
git commit -m "add MaintenanceSlab model with active rate lookup and tests"
```

---

### Task 5: Filament Resources for Apartment Module

**Files:**
- Create: UnitResource, ResidentResource, MaintenanceSlabResource (Filament)

**Step 1: Create Filament resources**

Run:
```bash
php artisan make:filament-resource Unit --generate --module=Apartment
php artisan make:filament-resource Resident --generate --module=Apartment
php artisan make:filament-resource MaintenanceSlab --generate --module=Apartment
```

Note: If `--module` flag is not supported by Filament, create resources in `app/Filament/Resources/` and import module models.

**Step 2: Configure UnitResource**

Key fields for the form:
- `flat_number` — TextInput, required, unique
- `flat_type` — Select with options: 1BHK, 2BHK, 3BHK
- `floor` — TextInput, numeric
- `area_sqft` — TextInput, numeric, nullable

Table columns: flat_number, flat_type, floor, area_sqft, residents count

**Step 3: Configure ResidentResource**

Key fields:
- `unit_id` — Select, relationship to Unit (display flat_number)
- `name` — TextInput, required
- `phone` — TextInput
- `email` — TextInput
- `is_owner` — Toggle
- `gpay_name` — TextInput (important for auto-matching)

Table columns: name, unit.flat_number, phone, is_owner, gpay_name

**Step 4: Configure MaintenanceSlabResource**

Key fields:
- `flat_type` — Select (1BHK, 2BHK, 3BHK)
- `amount` — TextInput, numeric, prefix ₹
- `effective_from` — DatePicker

Table columns: flat_type, amount, effective_from

**Step 5: Verify in browser**

Run: `php artisan serve`
Visit: `http://localhost:8000/admin`
Verify: All three resources appear in sidebar, CRUD works.

**Step 6: Commit**

```bash
git add -A
git commit -m "add Filament resources for units, residents, and maintenance slabs"
```

---

## Phase 3: Billing Module (Charges & Payments)

### Task 6: Create Billing Module with Charge Model

**Files:**
- Create: `app-modules/billing/` (via artisan)
- Create: Charge model, migration, factory

**Step 1: Generate module**

```bash
php artisan module:make Billing
```

**Step 2: Create Charge model**

```bash
php artisan make:model Charge --module=Billing -mf
```

**Step 3: Write Charge migration**

```php
public function up(): void
{
    Schema::create('charges', function (Blueprint $table) {
        $table->id();
        $table->foreignId('unit_id')->nullable()->constrained()->nullOnDelete();
        $table->string('type'); // maintenance, ad-hoc
        $table->string('description')->nullable();
        $table->decimal('amount', 10, 2);
        $table->string('billing_month'); // 2026-01, 2026-02
        $table->date('due_date')->nullable();
        $table->string('status')->default('pending'); // pending, partial, paid
        $table->timestamps();

        $table->index(['unit_id', 'billing_month']);
        $table->index(['status']);
    });
}
```

**Step 4: Write Charge model**

```php
<?php

namespace Modules\Billing\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Apartment\Models\Unit;

class Charge extends Model
{
    use HasFactory;

    protected $fillable = [
        'unit_id',
        'type',
        'description',
        'amount',
        'billing_month',
        'due_date',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'due_date' => 'date',
    ];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function paidAmount(): float
    {
        return (float) $this->payments()->sum('amount');
    }

    public function balanceAmount(): float
    {
        return (float) $this->amount - $this->paidAmount();
    }

    public function updateStatus(): void
    {
        $paid = $this->paidAmount();

        $this->status = match (true) {
            $paid >= (float) $this->amount => 'paid',
            $paid > 0 => 'partial',
            default => 'pending',
        };

        $this->save();
    }
}
```

**Step 5: Write factory and tests**

Tests should cover: creating a charge, paid/partial/pending status calculation, balance amount.

**Step 6: Run and commit**

```bash
php artisan migrate
php artisan test --filter=ChargeTest
git add -A
git commit -m "add Billing module with Charge model and tests"
```

---

### Task 7: Payment Model

**Files:**
- Create: Payment model, migration, factory in Billing module

**Step 1: Create model**

```bash
php artisan make:model Payment --module=Billing -mf
```

**Step 2: Write Payment migration**

```php
public function up(): void
{
    Schema::create('payments', function (Blueprint $table) {
        $table->id();
        $table->foreignId('charge_id')->nullable()->constrained()->nullOnDelete();
        $table->foreignId('unit_id')->constrained()->cascadeOnDelete();
        $table->decimal('amount', 10, 2);
        $table->date('paid_date');
        $table->string('source'); // gpay, bank_transfer, cash
        $table->string('reference_number')->nullable();
        $table->string('matched_by')->default('manual'); // auto, manual
        $table->string('reconciliation_status')->default('pending_verification'); // pending_verification, bank_verified
        $table->timestamps();

        $table->index(['unit_id', 'paid_date']);
        $table->index(['reconciliation_status']);
    });
}
```

**Step 3: Write Payment model**

```php
<?php

namespace Modules\Billing\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Apartment\Models\Unit;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'charge_id',
        'unit_id',
        'amount',
        'paid_date',
        'source',
        'reference_number',
        'matched_by',
        'reconciliation_status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_date' => 'date',
    ];

    public function charge(): BelongsTo
    {
        return $this->belongsTo(Charge::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }
}
```

**Step 4: Write tests**

Test: creating a payment updates charge status, partial payments.

**Step 5: Run and commit**

```bash
php artisan migrate
php artisan test --filter=PaymentTest
git add -A
git commit -m "add Payment model with charge relationship and tests"
```

---

### Task 8: Expense Model

**Files:**
- Create: Expense model, migration, factory in Billing module

**Step 1: Create model**

```bash
php artisan make:model Expense --module=Billing -mf
```

**Step 2: Write Expense migration**

```php
public function up(): void
{
    Schema::create('expenses', function (Blueprint $table) {
        $table->id();
        $table->string('description');
        $table->decimal('amount', 10, 2);
        $table->date('paid_date');
        $table->string('category'); // electricity, water, maintenance, service, other
        $table->string('source')->default('bank_transfer'); // gpay, bank_transfer, cash
        $table->string('reference_number')->nullable();
        $table->string('reconciliation_status')->default('pending_verification');
        $table->timestamps();
    });
}
```

**Step 3: Write Expense model**

```php
<?php

namespace Modules\Billing\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'description',
        'amount',
        'paid_date',
        'category',
        'source',
        'reference_number',
        'reconciliation_status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_date' => 'date',
    ];
}
```

**Step 4: Write tests, run, commit**

```bash
php artisan migrate
php artisan test --filter=ExpenseTest
git add -A
git commit -m "add Expense model for tracking outgoing payments"
```

---

### Task 9: Maintenance Charge Generation Service

**Files:**
- Create: `app-modules/billing/src/Services/MaintenanceChargeGenerator.php`
- Create: Test file

**Step 1: Write the service**

```php
<?php

namespace Modules\Billing\Services;

use Modules\Apartment\Models\MaintenanceSlab;
use Modules\Apartment\Models\Unit;
use Modules\Billing\Models\Charge;
use Illuminate\Support\Collection;

class MaintenanceChargeGenerator
{
    /**
     * @return Collection<int, Charge>
     */
    public function generate(string $billingMonth, ?string $dueDate = null): Collection
    {
        $units = Unit::all();
        $charges = collect();

        foreach ($units as $unit) {
            $existing = Charge::where('unit_id', $unit->id)
                ->where('billing_month', $billingMonth)
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
                'description' => "Maintenance for {$billingMonth}",
                'amount' => $rate,
                'billing_month' => $billingMonth,
                'due_date' => $dueDate,
                'status' => 'pending',
            ]));
        }

        return $charges;
    }
}
```

**Step 2: Write tests**

Test: generates charges for all units based on slab, skips if already generated, skips units with no slab.

**Step 3: Run and commit**

```bash
php artisan test --filter=MaintenanceChargeGeneratorTest
git add -A
git commit -m "add MaintenanceChargeGenerator service with tests"
```

---

### Task 10: Filament Resources for Billing Module

**Files:**
- Create: ChargeResource, PaymentResource, ExpenseResource
- Create: Filament action for "Generate Monthly Charges"

**Step 1: Create Filament resources**

```bash
php artisan make:filament-resource Charge --generate
php artisan make:filament-resource Payment --generate
php artisan make:filament-resource Expense --generate
```

**Step 2: Add "Generate Monthly Charges" action**

Add a Filament page or header action on ChargeResource that:
- Shows a form with billing_month (month picker) and due_date
- Calls MaintenanceChargeGenerator::generate()
- Shows success notification with count of charges created

**Step 3: Add ad-hoc charge creation**

On ChargeResource create form, add a toggle for "Apply to all units". When toggled, creating the charge loops through all units and creates one charge per unit.

**Step 4: Verify and commit**

```bash
git add -A
git commit -m "add Filament resources for charges, payments, expenses with bulk generation"
```

---

## Phase 4: Import Module (File Upload, OCR, PDF Parsing)

### Task 11: Create Import Module with Upload Model

**Files:**
- Create: `app-modules/import/` (via artisan)
- Create: Upload model, ParsedTransaction model, migrations

**Step 1: Generate module**

```bash
php artisan module:make Import
```

**Step 2: Create models**

```bash
php artisan make:model Upload --module=Import -mf
php artisan make:model ParsedTransaction --module=Import -mf
```

**Step 3: Write Upload migration**

```php
public function up(): void
{
    Schema::create('uploads', function (Blueprint $table) {
        $table->id();
        $table->string('file_path');
        $table->string('type'); // gpay_screenshot, bank_statement
        $table->string('status')->default('pending'); // pending, processing, processed, failed
        $table->timestamp('processed_at')->nullable();
        $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
        $table->timestamps();
    });
}
```

**Step 4: Write ParsedTransaction migration**

```php
public function up(): void
{
    Schema::create('parsed_transactions', function (Blueprint $table) {
        $table->id();
        $table->foreignId('upload_id')->constrained()->cascadeOnDelete();
        $table->text('raw_text')->nullable();
        $table->string('sender_name')->nullable();
        $table->decimal('amount', 10, 2);
        $table->date('date');
        $table->string('direction'); // credit, debit
        $table->string('fingerprint')->index();
        $table->string('match_type')->default('unmatched'); // payment, expense, unmatched
        $table->foreignId('matched_payment_id')->nullable()->constrained('payments')->nullOnDelete();
        $table->foreignId('matched_expense_id')->nullable()->constrained('expenses')->nullOnDelete();
        $table->string('reconciliation_status')->default('unmatched'); // auto_matched, manual_matched, unmatched, reconciled
        $table->timestamps();
    });
}
```

**Step 5: Write models with fingerprint generation**

ParsedTransaction model should include:
```php
public static function generateFingerprint(float $amount, string $date, ?string $name = null): string
{
    $normalized = strtolower(trim($name ?? ''));

    return hash('xxh128', "{$amount}|{$date}|{$normalized}");
}
```

**Step 6: Write tests, run, commit**

```bash
php artisan migrate
php artisan test --filter=UploadTest
php artisan test --filter=ParsedTransactionTest
git add -A
git commit -m "add Import module with Upload and ParsedTransaction models"
```

---

### Task 12: Install Laravel AI SDK & GPay Screenshot OCR

**Files:**
- Create: `app-modules/import/src/Services/GpayScreenshotParser.php`
- Create: `app-modules/import/src/Jobs/ProcessGpayScreenshot.php`

**Step 1: Install Laravel AI SDK**

```bash
composer require laravel/ai
php artisan vendor:publish --provider="Laravel\Ai\AiServiceProvider"
php artisan migrate
```

Add to `.env`:
```
ANTHROPIC_API_KEY=your_key_here
```

**Step 2: Create the GPay screenshot parser service**

```php
<?php

namespace Modules\Import\Services;

use Laravel\Ai\Files\Image;

class GpayScreenshotParser
{
    public function parse(string $imagePath): array
    {
        $agent = new \Modules\Import\Ai\TransactionExtractorAgent;

        $response = $agent->prompt(
            'Extract the transaction details from this GPay screenshot. Return JSON with fields: sender_name, amount (numeric, no currency symbol), date (YYYY-MM-DD format), direction (credit or debit). If multiple transactions are visible, return an array of objects.',
            attachments: [
                Image::fromPath($imagePath),
            ]
        );

        return json_decode((string) $response, true);
    }
}
```

**Step 3: Create the AI Agent**

```php
<?php

namespace Modules\Import\Ai;

use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Promptable;

class TransactionExtractorAgent implements Agent
{
    use Promptable;

    public function instructions(): string
    {
        return 'You are a financial transaction data extractor. You analyze payment screenshots and bank statements to extract structured transaction data. Always return valid JSON. For dates, use YYYY-MM-DD format. For amounts, return numeric values without currency symbols.';
    }

    public function provider(): string
    {
        return 'anthropic';
    }
}
```

**Step 4: Create the job**

```php
<?php

namespace Modules\Import\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Import\Models\Upload;
use Modules\Import\Services\GpayScreenshotParser;
use Modules\Import\Services\TransactionMatcher;

class ProcessGpayScreenshot implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Upload $upload,
    ) {}

    public function handle(
        GpayScreenshotParser $parser,
        TransactionMatcher $matcher,
    ): void {
        $this->upload->update(['status' => 'processing']);

        try {
            $transactions = $parser->parse(
                storage_path("app/{$this->upload->file_path}")
            );

            foreach ($transactions as $txn) {
                $parsed = $this->upload->parsedTransactions()->create([
                    'raw_text' => json_encode($txn),
                    'sender_name' => $txn['sender_name'] ?? null,
                    'amount' => $txn['amount'],
                    'date' => $txn['date'],
                    'direction' => $txn['direction'],
                    'fingerprint' => \Modules\Import\Models\ParsedTransaction::generateFingerprint(
                        $txn['amount'],
                        $txn['date'],
                        $txn['sender_name'] ?? null,
                    ),
                ]);

                $matcher->match($parsed);
            }

            $this->upload->update([
                'status' => 'processed',
                'processed_at' => now(),
            ]);
        } catch (\Throwable $e) {
            $this->upload->update(['status' => 'failed']);
            throw $e;
        }
    }
}
```

**Step 5: Write tests, commit**

```bash
php artisan test --filter=GpayScreenshotParserTest
git add -A
git commit -m "add GPay screenshot OCR with Laravel AI SDK"
```

---

### Task 13: Transaction Matching Service

**Files:**
- Create: `app-modules/import/src/Services/TransactionMatcher.php`

**Step 1: Write the matcher**

```php
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
        // Check for duplicate fingerprint
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

    private function matchCredit(ParsedTransaction $parsed): void
    {
        if (! $parsed->sender_name) {
            return;
        }

        // Fuzzy match against resident gpay_name
        $resident = Resident::whereNotNull('gpay_name')
            ->get()
            ->first(function ($r) use ($parsed) {
                return str_contains(
                    strtolower($r->gpay_name),
                    strtolower($parsed->sender_name)
                ) || str_contains(
                    strtolower($parsed->sender_name),
                    strtolower($r->gpay_name)
                );
            });

        if (! $resident) {
            return;
        }

        // Find oldest pending charge for this unit
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
}
```

**Step 2: Write tests**

Test: auto-matching credits by gpay_name, debit creates expense, duplicate fingerprint is reconciled.

**Step 3: Commit**

```bash
php artisan test --filter=TransactionMatcherTest
git add -A
git commit -m "add TransactionMatcher with fuzzy gpay_name matching and dedup"
```

---

### Task 14: HDFC Bank Statement PDF Parser

**Files:**
- Create: `app-modules/import/src/Services/HdfcStatementParser.php`
- Create: `app-modules/import/src/Jobs/ProcessBankStatement.php`

**Step 1: Write the parser using Laravel AI SDK**

```php
<?php

namespace Modules\Import\Services;

use Laravel\Ai\Files\Document;

class HdfcStatementParser
{
    public function parse(string $pdfPath): array
    {
        $agent = new \Modules\Import\Ai\TransactionExtractorAgent;

        $response = $agent->prompt(
            'Parse this HDFC bank statement PDF. Extract all transactions as a JSON array. Each transaction should have: date (YYYY-MM-DD), narration (string), reference_number (string or null), amount (numeric, positive), direction (credit if Deposit, debit if Withdrawal). The columns in HDFC statements are: Date, Narration, Chq/Ref Number, Value Date, Withdrawal Amt, Deposit Amt, Closing Balance.',
            attachments: [
                Document::fromPath($pdfPath),
            ]
        );

        return json_decode((string) $response, true);
    }
}
```

**Step 2: Write the job**

Similar structure to ProcessGpayScreenshot but:
- Uses HdfcStatementParser
- Processes multiple transactions from a single PDF
- For credits: checks against existing payments by amount + date for reconciliation
- For debits: checks against existing expenses for reconciliation

**Step 3: Write bank reconciliation logic in TransactionMatcher**

Add method `reconcileFromBank(ParsedTransaction $parsed)`:
- For credits: find existing payment with same amount and date ±1 day
  - Found → update payment reconciliation_status to `bank_verified`, mark parsed as `reconciled`
  - Not found → try fuzzy match against residents, create payment or leave unmatched
- For debits: find existing expense with same amount and date ±1 day
  - Found → update expense reconciliation_status to `bank_verified`, mark parsed as `reconciled`
  - Not found → create expense or leave unmatched

**Step 4: Write tests, commit**

```bash
php artisan test --filter=HdfcStatementParserTest
php artisan test --filter=BankReconciliationTest
git add -A
git commit -m "add HDFC bank statement PDF parser with reconciliation"
```

---

### Task 15: Filament Upload Pages & Review Queue

**Files:**
- Create: Upload Filament resource with file upload
- Create: Review queue page for unmatched transactions

**Step 1: Create Upload resource**

```bash
php artisan make:filament-resource Upload --generate
```

Add file upload field supporting images (GPay) and PDFs (bank statements).
On create, dispatch the appropriate job based on file type.

**Step 2: Create Review Queue page**

Custom Filament page showing ParsedTransactions with `reconciliation_status = unmatched`.
For each, show: raw_text, sender_name, amount, date, direction.
Actions: manually assign to a unit/resident (creates payment), mark as expense with category, dismiss.

**Step 3: Verify and commit**

```bash
git add -A
git commit -m "add file upload resource and transaction review queue"
```

---

## Phase 5: Google Sheets Integration

### Task 16: Install & Configure Google Sheets

**Files:**
- Create: `app-modules/sheet/` module
- Create: `config/google.php` updates

**Step 1: Generate module**

```bash
php artisan module:make Sheet
```

**Step 2: Install package**

```bash
composer require revolution/laravel-google-sheets
php artisan vendor:publish --tag="google-config"
```

**Step 3: Configure service account**

- Create Google Cloud project
- Enable Sheets API and Drive API
- Create service account, download JSON key
- Save to `storage/app/google-service-account.json`
- Add to `.env`:
```
GOOGLE_SERVICE_ENABLED=true
GOOGLE_SERVICE_ACCOUNT_JSON_LOCATION=storage/app/google-service-account.json
GOOGLE_SHEET_ID=your_spreadsheet_id
```
- Share the Google Sheet with the service account email

**Step 4: Commit**

```bash
git add -A
git commit -m "add Sheet module with Google Sheets configuration"
```

---

### Task 17: Google Sheets Sync Service

**Files:**
- Create: `app-modules/sheet/src/Services/SheetSyncService.php`
- Create: `app-modules/sheet/src/Jobs/SyncToGoogleSheet.php`

**Step 1: Write the sync service**

```php
<?php

namespace Modules\Sheet\Services;

use Revolution\Google\Sheets\Facades\Sheets;
use Modules\Billing\Models\Charge;
use Modules\Billing\Models\Payment;
use Modules\Billing\Models\Expense;
use Modules\Apartment\Models\Unit;

class SheetSyncService
{
    private string $spreadsheetId;

    public function __construct()
    {
        $this->spreadsheetId = config('services.google.sheet_id');
    }

    public function syncMonthlyTab(string $billingMonth): void
    {
        $sheetName = $this->monthTabName($billingMonth);

        // Gather all payments and expenses for this month
        $payments = Payment::whereMonth('paid_date', substr($billingMonth, 5, 2))
            ->whereYear('paid_date', substr($billingMonth, 0, 4))
            ->with(['unit', 'charge'])
            ->orderBy('paid_date')
            ->get();

        $expenses = Expense::whereMonth('paid_date', substr($billingMonth, 5, 2))
            ->whereYear('paid_date', substr($billingMonth, 0, 4))
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
                'Income - ' . ($payment->charge?->type ?? 'payment'),
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

                $totalDue += $due;
                $totalPaid += $paid;
                $row[] = $paid > 0 ? "₹{$paid} / ₹{$due}" : "₹{$due} due";
            }

            $row[] = "₹{$totalDue}";
            $row[] = "₹{$totalPaid}";
            $row[] = '₹' . ($totalDue - $totalPaid);

            $rows[] = $row;
        }

        Sheets::spreadsheet($this->spreadsheetId)
            ->sheet('Summary')
            ->update($rows);
    }

    private function monthTabName(string $billingMonth): string
    {
        return date('M Y', strtotime("{$billingMonth}-01"));
    }
}
```

**Step 2: Write the queued job**

```php
<?php

namespace Modules\Sheet\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Sheet\Services\SheetSyncService;

class SyncToGoogleSheet implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public ?string $billingMonth = null,
    ) {}

    public function handle(SheetSyncService $service): void
    {
        if ($this->billingMonth) {
            $service->syncMonthlyTab($this->billingMonth);
        }

        $service->syncSummaryTab();
    }
}
```

**Step 3: Dispatch sync from payment/charge/expense observers**

Create model observers that dispatch SyncToGoogleSheet when records are created or updated.

**Step 4: Write tests, commit**

```bash
php artisan test --filter=SheetSyncServiceTest
git add -A
git commit -m "add Google Sheets sync service with monthly and summary tabs"
```

---

## Phase 6: Dashboard & Polish

### Task 18: Filament Dashboard Widgets

**Files:**
- Modify: `app/Filament/Pages/Dashboard.php`
- Create: Dashboard widgets

**Step 1: Create widgets**

- **Collection Overview**: Total collected this month, pending dues, total expenses
- **Unit-wise Balance**: Table showing each unit's outstanding balance
- **Reconciliation Status**: Count of pending_verification, bank_verified, unmatched
- **Recent Uploads**: Last 5 uploads with processing status

**Step 2: Commit**

```bash
git add -A
git commit -m "add Filament dashboard with collection, balance, and reconciliation widgets"
```

---

### Task 19: Seeder with Sample Data

**Files:**
- Create: `database/seeders/SampleDataSeeder.php`

**Step 1: Create seeder**

Generate ~10 sample units (mix of 1BHK, 2BHK, 3BHK), residents, maintenance slabs, a couple months of charges, and some payments.

**Step 2: Run and commit**

```bash
php artisan db:seed --class=SampleDataSeeder
git add -A
git commit -m "add sample data seeder for development"
```

---

## Task Dependency Summary

```
Task 1 (Scaffolding)
  └─ Task 2 (Unit) → Task 3 (Resident) → Task 4 (Slab) → Task 5 (Filament Apartment)
  └─ Task 6 (Charge) → Task 7 (Payment) → Task 8 (Expense) → Task 9 (Generator) → Task 10 (Filament Billing)
  └─ Task 11 (Import Models) → Task 12 (OCR) → Task 13 (Matcher) → Task 14 (PDF Parser) → Task 15 (Review Queue)
  └─ Task 16 (Sheets Config) → Task 17 (Sync Service)
  └─ Task 18 (Dashboard) — depends on Tasks 10, 15, 17
  └─ Task 19 (Seeder) — depends on Tasks 4, 8
```

Tasks 2-5, 6-10, 11-15, and 16-17 can proceed in parallel across their tracks after Task 1 completes.
