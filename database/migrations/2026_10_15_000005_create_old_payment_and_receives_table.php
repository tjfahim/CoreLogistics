<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('old_payment_and_receives', function (Blueprint $table) {
            $table->id();
            // paid     = money we paid for a shipment bill  (needs old_shipment_id)
            // received = money collected from a customer    (needs old_customer_id)
            $table->enum('type', ['paid', 'received']);
            $table->foreignId('old_shipment_id')->nullable()->constrained('old_shipments')->cascadeOnDelete();
            $table->foreignId('old_customer_id')->nullable()->constrained('old_customers')->cascadeOnDelete();
            $table->decimal('amount', 14, 2);
            $table->date('date');
            $table->string('method')->nullable();   // cash, bank, bkash ...
            $table->string('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('old_payment_and_receives');
    }
};
