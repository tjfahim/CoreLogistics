<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_extra_shipping_marks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->string('shipping_mark');
            $table->timestamps();

            $table->unique(['customer_id', 'shipping_mark']);
            $table->index('shipping_mark');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_extra_shipping_marks');
    }
};