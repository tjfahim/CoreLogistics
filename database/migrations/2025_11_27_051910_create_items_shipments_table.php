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
        Schema::create('items_shipments', function (Blueprint $table) {
           $table->id();
            $table->string('shipment_name');
            $table->date('shipment_date')->nullable();
            $table->text('note')->nullable();
            $table->enum('status', ['draft', 'pending', 'shipped','waiting_for_customs', 'delivered', 'cancelled'])->default('draft');
            $table->timestamps();
        });

        Schema::create('cargo_item_shipment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cargo_item_id')->constrained()->onDelete('cascade');
            $table->foreignId('items_shipment_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items_shipments');
         Schema::dropIfExists('cargo_item_shipment');
    }
};
