<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->string('flat_number')->unique();
            $table->string('flat_type');
            $table->integer('floor');
            $table->decimal('area_sqft', 8, 2)->nullable();
            $table->timestamps();
        });
    }
};
