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
        Schema::create('cargo_items', function (Blueprint $table) {
            $table->id();
              $table->unsignedInteger('goods_received')->nullable(); 

            $table->string('printed_ctn_no')->nullable();        
            $table->string('written_ctn_no')->nullable();     
            $table->string('tracking_no')->nullable();              

            $table->string('goods_name_en')->nullable();
            $table->string('goods_name_local')->nullable();

            $table->unsignedInteger('pack_no')->nullable(0);
            $table->unsignedInteger('unit_pcs')->nullable(0);

            $table->decimal('weight_kg', 10, 3);

            // Size
            $table->decimal('length_cm', 8, 2)->nullable();        // "L(cm)"
            $table->decimal('width_cm', 8, 2)->nullable();         // "W(cm)"
            $table->decimal('height_cm', 8, 2)->nullable();        // "H(cm)"

            $table->decimal('cbm', 10, 6)->nullable();             // "CBM"

            $table->string('shipping_mark')->nullable();           // "Shipping Mark"
            $table->string('remark')->nullable();                  // "Remark for liquid,battery or powder"

            $table->boolean('is_copy')->default(false);            // "Copy or NO"
            $table->string('warehouse')->nullable();               // "ware-house"
            $table->date('date')->nullable();         
            $table->enum('status', [
                            'pending',           
                            'packed',            
                            'ready_to_ship',            
                            'shipping_ongoing',  
                            'arrived_bd',  
                            'waiting_for_customs', 
                            'ready_to_deliver', 
                            'delivered'    
                        ])->default('pending');
            $table->decimal('price', 12, 2)->nullable();
            $table->decimal('price_per_kg', 10, 2)->nullable();
            $table->foreignId('customer_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamp('paid_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cargo_items');
    }
};
