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
        Schema::create('boxes', function (Blueprint $table) {
            $table->id();
              $table->string('box_code')->unique();
              $table->decimal('total_weight', 10, 2)->default(0);
              $table->enum('status', [
                'packed',            
                'shipping_ongoing',  
                'arrived_bd',  
                'ready_for_delivery', 
                'delivered'    
            ])->default('packed');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('boxes');
    }
};
