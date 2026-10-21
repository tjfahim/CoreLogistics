<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Third parties are now ordinary customers flagged as partners (customers.is_partner),
 * and their goods are flagged on the item (cargo_items.is_third_party).
 * The old third_parties / third_party_payments tables and cargo_items.third_party_id are left in place (unused).
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $t) {
            if (!Schema::hasColumn('customers', 'is_partner')) {
                $t->boolean('is_partner')->default(false)->after('address');
            }
        });
        Schema::table('cargo_items', function (Blueprint $t) {
            if (!Schema::hasColumn('cargo_items', 'is_third_party')) {
                $t->boolean('is_third_party')->default(false);
            }
        });

        if (Schema::hasColumn('cargo_items', 'third_party_id')) {
            DB::table('cargo_items')->whereNotNull('third_party_id')->update(['is_third_party' => true]);
        }
    }

    public function down(): void
    {
        Schema::table('customers', fn (Blueprint $t) => $t->dropColumn('is_partner'));
        Schema::table('cargo_items', fn (Blueprint $t) => $t->dropColumn('is_third_party'));
    }
};
