<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('cargo_items', function (Blueprint $t) {
            // NULL = not weighed again yet -> billing / reports keep using weight_kg
            $t->decimal('final_weight_kg', 10, 3)->nullable()->after('weight_kg');
        });
    }

    public function down(): void
    {
        Schema::table('cargo_items', function (Blueprint $t) {
            $t->dropColumn('final_weight_kg');
        });
    }
};
