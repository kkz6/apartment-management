<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_slabs', function (Blueprint $table) {
            $table->id();
            $table->string('flat_type');
            $table->decimal('amount', 10, 2);
            $table->date('effective_from');
            $table->timestamps();

            $table->index(['flat_type', 'effective_from']);
        });
    }
};
