<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('items_shipment_costs', function (Blueprint $table) {
            $table->string('currency', 10)->default('bdt')->after('cost_type_id');      // bdt, usd, rmb, dinar
            $table->decimal('exchange_rate_bdt', 15, 4)->default(1)->after('currency'); // 1 unit of currency = ? BDT
            $table->decimal('final_bdt_cost', 15, 2)->default(0)->after('amount');      // amount * exchange_rate_bdt
        });

        // Backfill old rows: they were all BDT, so final cost = amount
        DB::table('items_shipment_costs')->update([
            'currency'          => 'bdt',
            'exchange_rate_bdt' => 1,
            'final_bdt_cost'    => DB::raw('amount'),
        ]);
    }

    public function down(): void
    {
        Schema::table('items_shipment_costs', function (Blueprint $table) {
            $table->dropColumn(['currency', 'exchange_rate_bdt', 'final_bdt_cost']);
        });
    }
};