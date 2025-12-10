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
         Schema::create('customer_deliveries', function (Blueprint $table) {
      $table->id();
    $table->foreignId('customer_bill_id')->constrained()->cascadeOnDelete();
    $table->decimal('delivered_weight', 8, 2);
    $table->decimal('amount', 10, 2); // payment made for this delivery
    $table->date('delivery_date');
    $table->text('notes')->nullable();
    $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_deliveries');
    }
};
