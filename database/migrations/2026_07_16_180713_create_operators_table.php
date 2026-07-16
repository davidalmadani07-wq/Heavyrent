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
        Schema::create('operators', function (Blueprint $table) {
    $table->id();
    $table->string('name', 150);
    $table->string('phone', 30);
    $table->string('certification', 150)->nullable();
    $table->decimal('price_per_day', 12, 2);
    $table->enum('status', ['available', 'assigned'])->default('available');
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operators');
    }
};
