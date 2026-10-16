<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('old_shipment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('old_shipment_id')->constrained('old_shipments')->cascadeOnDelete();
            // one item belongs to only one customer
            $table->foreignId('old_customer_id')->nullable()->constrained('old_customers')->nullOnDelete();
            $table->string('name');
            $table->decimal('weight', 12, 3)->default(0);
            $table->string('ctn_no')->nullable();
            $table->decimal('cbm', 12, 3)->default(0);
            $table->string('remark')->nullable();
            $table->string('shipping_mark')->nullable();
            $table->decimal('price_per_kg', 12, 2)->default(0);
            $table->decimal('final_price', 14, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('old_shipment_items');
    }
};
