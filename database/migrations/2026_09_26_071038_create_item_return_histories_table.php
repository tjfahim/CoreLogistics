<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('item_return_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cargo_item_id')->constrained('cargo_items')->cascadeOnDelete();
            $table->foreignId('items_shipment_id')->nullable()->constrained('items_shipments')->nullOnDelete();
            $table->text('note')->nullable();
            $table->date('return_date');
            $table->string('previous_status')->nullable();
            $table->foreignId('returned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('cargo_item_id');
            $table->index('return_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_return_histories');
    }
};