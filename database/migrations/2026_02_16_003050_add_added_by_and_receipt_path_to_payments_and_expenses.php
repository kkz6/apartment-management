<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('added_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('receipt_path')->nullable();
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->foreignId('added_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('receipt_path')->nullable();
        });
    }
};
