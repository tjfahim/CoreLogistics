<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('items_shipment_cost_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('items_shipment_cost_id')
                  ->constrained('items_shipment_costs')
                  ->cascadeOnDelete();
            $table->string('file_path');                 // path on the "public" disk
            $table->string('original_name');
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('file_size')->default(0); // bytes
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('items_shipment_cost_attachments');
    }
};