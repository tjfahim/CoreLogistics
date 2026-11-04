<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('customer_extra_bills', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('items_shipment_id')->index();
            $table->unsignedBigInteger('customer_id')->index();
            $table->string('title');                                  // note / what the bill is for
            $table->string('bill_type', 10)->default('fixed');        // fixed | per_kg
            $table->decimal('amount', 14, 2)->default(0);             // fixed amount
            $table->decimal('rate_per_kg', 14, 2)->nullable();        // per_kg: rate x customer's final kg in that shipment
            $table->date('bill_date')->nullable();
            $table->text('note')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_extra_bills');
    }
};
