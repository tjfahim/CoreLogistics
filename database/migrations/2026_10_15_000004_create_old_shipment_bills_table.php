<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('old_shipment_bills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('old_shipment_id')->constrained('old_shipments')->cascadeOnDelete();
            $table->string('cost_type');
            $table->decimal('total_price', 14, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('old_shipment_bills');
    }
};
