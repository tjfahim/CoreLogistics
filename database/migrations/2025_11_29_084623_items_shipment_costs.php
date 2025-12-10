<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('items_shipment_costs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('items_shipment_id')->constrained()->onDelete('cascade');
            $table->foreignId('cost_type_id')->constrained()->onDelete('cascade');
            $table->foreignId('agent_id')->nullable()->constrained()->onDelete('set null');
            $table->text('description')->nullable();
            $table->decimal('rate_per_kg', 10, 2)->nullable();
            $table->decimal('total_weight', 10, 2)->nullable();
            $table->decimal('amount', 12, 2);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->decimal('due_amount', 12, 2)->default(0);
            $table->enum('status', ['pending', 'partial', 'paid'])->default('pending');
            $table->date('cost_date');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('items_shipment_costs');
    }
};