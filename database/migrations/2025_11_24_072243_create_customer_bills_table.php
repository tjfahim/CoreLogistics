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
        Schema::create('customer_bills', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('shipment_id');
        $table->unsignedBigInteger('customer_id');
        $table->decimal('total_weight', 10, 2)->default(0);
        $table->decimal('total_price', 10, 2)->default(0);
        $table->decimal('paid_amount', 10, 2)->default(0);
        $table->decimal('due_amount', 10, 2)->default(0);
        $table->string('status')->default('pending'); // pending, partial, paid
        $table->timestamps();

        $table->foreign('shipment_id')->references('id')->on('shipments');
        $table->foreign('customer_id')->references('id')->on('customers');
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_bills');
    }
};
