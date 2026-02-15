<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('charges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type');
            $table->string('description')->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('billing_month');
            $table->date('due_date')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();

            $table->index(['unit_id', 'billing_month']);
            $table->index(['status']);
        });
    }
};
