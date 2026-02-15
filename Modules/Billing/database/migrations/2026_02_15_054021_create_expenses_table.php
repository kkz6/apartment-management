<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('description');
            $table->decimal('amount', 10, 2);
            $table->date('paid_date');
            $table->string('category');
            $table->string('source')->default('bank_transfer');
            $table->string('reference_number')->nullable();
            $table->string('reconciliation_status')->default('pending_verification');
            $table->timestamps();
        });
    }
};
