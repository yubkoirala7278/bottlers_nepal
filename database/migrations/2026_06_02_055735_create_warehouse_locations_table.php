<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('warehouse_locations', function (Blueprint $table) {
            $table->id();
            $table->string('location_code', 10); // A1, B2, etc.
            $table->string('level'); // A, B, C, D, E, F, G, H, I, J, K, L
            $table->integer('height'); // 1-6
            $table->integer('max_depth')->default(50);
            $table->integer('current_fill')->default(0);
            $table->timestamps();

            $table->unique('location_code');
            $table->index(['level', 'height']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouse_locations');
    }
};
