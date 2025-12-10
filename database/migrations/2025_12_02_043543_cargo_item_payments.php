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
        Schema::create('cargo_item_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_payment_id')->constrained()->onDelete('cascade');
            $table->foreignId('cargo_item_id')->constrained()->onDelete('cascade');
            $table->decimal('amount_paid', 12, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cargo_item_payments');
    }
};
