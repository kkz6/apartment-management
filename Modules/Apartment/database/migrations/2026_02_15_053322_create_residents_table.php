<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
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
};
