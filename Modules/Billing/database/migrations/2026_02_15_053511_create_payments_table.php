<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('charge_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('unit_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->date('paid_date');
            $table->string('source');
            $table->string('reference_number')->nullable();
            $table->string('matched_by')->default('manual');
            $table->string('reconciliation_status')->default('pending_verification');
            $table->timestamps();

            $table->index(['unit_id', 'paid_date']);
            $table->index(['reconciliation_status']);
        });
    }
};
