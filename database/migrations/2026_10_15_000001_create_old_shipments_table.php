<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('old_shipments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->date('date');
            $table->string('shipping_mark')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('old_shipments');
    }
};
