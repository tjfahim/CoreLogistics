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
        Schema::create('shipments', function (Blueprint $table) {
             $table->id();
            $table->string('shipment_code')->unique();
            $table->unsignedBigInteger('freight_agent_id')->nullable();
            $table->unsignedBigInteger('cnf_agent_id')->nullable();
            $table->decimal('total_weight', 10, 2)->nullable();
            $table->string('status')->default('preparing');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};
