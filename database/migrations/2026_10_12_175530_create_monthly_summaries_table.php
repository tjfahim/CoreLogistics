<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
 // database/migrations/xxxx_create_monthly_summaries_table.php
public function up()
{
    Schema::create('monthly_summaries', function (Blueprint $table) {
        $table->id();
        $table->year('year');
        $table->unsignedTinyInteger('month');

        $table->decimal('office_bills_total',     14, 2)->default(0);
        $table->decimal('salaries_total',         14, 2)->default(0);
        $table->decimal('warehouse_bills_total',  14, 2)->default(0);
        $table->decimal('grand_total',            14, 2)->default(0);

        $table->decimal('default_kg',             12, 2)->default(0);
        $table->decimal('cost_per_kg',            12, 4)->default(0);

        $table->timestamps();
        $table->unique(['year', 'month']);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monthly_summaries');
    }
};
