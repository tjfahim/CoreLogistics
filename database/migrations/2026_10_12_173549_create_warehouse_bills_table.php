<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   // database/migrations/xxxx_create_warehouse_bills_table.php
public function up()
{
    Schema::create('warehouse_bills', function (Blueprint $table) {
        $table->id();
        $table->foreignId('office_id')->constrained()->cascadeOnDelete();
        $table->decimal('kg', 12, 2)->default(0);
        $table->decimal('rate_per_kg', 12, 2)->default(0);
        $table->decimal('total_amount', 14, 2)->default(0);
        $table->date('bill_date');
        $table->string('note')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouse_bills');
    }
};
