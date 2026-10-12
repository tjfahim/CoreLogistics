<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
 // database/migrations/xxxx_create_warehouse_bills_table.php
public function up()
{
    Schema::create('office_bills', function (Blueprint $table) {
        $table->id();
        $table->foreignId('office_id')->constrained()->cascadeOnDelete();
        $table->year('year');
        $table->unsignedTinyInteger('month'); // 1-12
        $table->decimal('amount', 12, 2)->default(0);
        $table->string('note')->nullable();
        $table->timestamps();
        $table->unique(['office_id', 'year', 'month']);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('office_bills');
    }
};
