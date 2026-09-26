<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('itemshipment_id')->constrained('items_shipments')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['customer_id', 'itemshipment_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};