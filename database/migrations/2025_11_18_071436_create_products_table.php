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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('short_description')->nullable();
            $table->decimal('weight', 10, 2);
            $table->decimal('price', 10, 2);
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->enum('status', [
                'pending',           
                'packed',            
                'shipping_ongoing',  
                'arrived_bd',  
                'ready_for_delivery', 
                'delivered'    
            ])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
