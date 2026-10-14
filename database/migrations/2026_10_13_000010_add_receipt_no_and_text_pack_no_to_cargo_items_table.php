<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1) New Excel column "Receipt_NO"
        if (!Schema::hasColumn('cargo_items', 'receipt_no')) {
            Schema::table('cargo_items', function (Blueprint $table) {
                $table->string('receipt_no', 50)->nullable()->after('written_ctn_no');
            });
        }

        // 2) Pack_NO in the new sheet is text like "10-1", "14-12" (carton x of y),
        //    so it can no longer be an integer column. Existing numbers are kept as text.
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE cargo_items MODIFY pack_no VARCHAR(30) NULL');
        } else {
            Schema::table('cargo_items', function (Blueprint $table) {
                $table->string('pack_no', 30)->nullable()->change(); // needs doctrine/dbal on Laravel <= 10
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('cargo_items', 'receipt_no')) {
            Schema::table('cargo_items', function (Blueprint $table) {
                $table->dropColumn('receipt_no');
            });
        }
        // pack_no is intentionally left as text (converting "10-1" back to integer would lose data)
    }
};