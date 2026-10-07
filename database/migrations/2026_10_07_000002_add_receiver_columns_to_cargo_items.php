<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// OPTIONAL: only needed for the "Import to live tables" step.
return new class extends Migration {
    public function up(): void
    {
        Schema::table('cargo_items', function (Blueprint $t) {
            if (!Schema::hasColumn('cargo_items', 'delivered_at'))   $t->date('delivered_at')->nullable();
            if (!Schema::hasColumn('cargo_items', 'receiver_name'))  $t->string('receiver_name')->nullable();
            if (!Schema::hasColumn('cargo_items', 'receiver_phone')) $t->string('receiver_phone', 30)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('cargo_items', function (Blueprint $t) {
            $t->dropColumn(['delivered_at', 'receiver_name', 'receiver_phone']);
        });
    }
};
