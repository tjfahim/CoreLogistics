<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /* ---- 1) Office bills: many bills per office (wifi, electricity, ...) ---- */
        Schema::table('office_bills', function (Blueprint $table) {
            $table->index('office_id', 'office_bills_office_id_idx');   // keeps the FK happy
        });
        Schema::table('office_bills', function (Blueprint $table) {
            $table->dropUnique('office_bills_office_id_year_month_unique');
            $table->string('bill_type', 80)->nullable()->after('office_id');   // Wifi, Electricity, Rent...
            $table->date('bill_date')->nullable()->after('bill_type');
            $table->index(['year', 'month']);
        });
        // old rows: use the 1st day of their month
        DB::table('office_bills')->whereNull('bill_date')->orderBy('id')->each(function ($b) {
            DB::table('office_bills')->where('id', $b->id)->update([
                'bill_date' => sprintf('%04d-%02d-01', $b->year, $b->month),
            ]);
        });

        /* ---- 2) Salary history: one row per employee per month ---- */
        Schema::create('salary_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month');
            $table->foreignId('employee_id')->nullable()->constrained()->nullOnDelete();
            $table->string('employee_name');                     // kept even if the employee is deleted
            $table->foreignId('office_id')->nullable()->constrained()->nullOnDelete();
            $table->string('office_name')->nullable();
            $table->decimal('amount', 12, 2)->default(0);
            $table->timestamps();
            $table->index(['year', 'month']);
        });

        /* ---- 3) Summary bills (per-kg): keep every save as its own bill ---- */
        Schema::table('monthly_summaries', function (Blueprint $table) {
            $table->index(['year', 'month'], 'monthly_summaries_year_month_idx');
        });
        Schema::table('monthly_summaries', function (Blueprint $table) {
            $table->dropUnique('monthly_summaries_year_month_unique');
            $table->string('title')->nullable()->after('month');
        });
    }

    public function down(): void
    {
        Schema::table('monthly_summaries', function (Blueprint $table) {
            $table->dropColumn('title');
            $table->dropIndex('monthly_summaries_year_month_idx');
        });
        Schema::dropIfExists('salary_records');
        Schema::table('office_bills', function (Blueprint $table) {
            $table->dropIndex(['year', 'month']);
            $table->dropColumn(['bill_type', 'bill_date']);
            $table->dropIndex('office_bills_office_id_idx');
        });
    }
};
