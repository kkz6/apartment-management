<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parsed_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('upload_id')->constrained()->cascadeOnDelete();
            $table->text('raw_text')->nullable();
            $table->string('sender_name')->nullable();
            $table->decimal('amount', 10, 2);
            $table->date('date');
            $table->string('direction');
            $table->string('fingerprint')->index();
            $table->string('match_type')->default('unmatched');
            $table->foreignId('matched_payment_id')->nullable()->constrained('payments')->nullOnDelete();
            $table->foreignId('matched_expense_id')->nullable()->constrained('expenses')->nullOnDelete();
            $table->string('reconciliation_status')->default('unmatched');
            $table->timestamps();
        });
    }
};
