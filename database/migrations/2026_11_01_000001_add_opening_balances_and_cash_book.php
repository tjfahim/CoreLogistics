<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Balance carried over from the old system.
        //   customers: + = customer owes us (old due), - = we hold his advance
        //   agents   : + = we owe the agent (old due), - = agent owes us / advance paid
        Schema::table('customers', function (Blueprint $t) {
            $t->decimal('opening_balance', 15, 2)->default(0)->after('is_partner');
        });
        Schema::table('agents', function (Blueprint $t) {
            $t->decimal('opening_balance', 15, 2)->default(0);
        });

        // Cash book: opening balances + manual movements (cash <-> bank transfers, owner deposit/withdraw ...)
        Schema::create('fund_movements', function (Blueprint $t) {
            $t->id();
            $t->date('movement_date');
            $t->string('type', 30);               // opening | transfer | deposit | withdraw
            $t->string('from_account', 10)->nullable();   // cash | bank
            $t->string('to_account', 10)->nullable();     // cash | bank
            $t->decimal('amount', 15, 2);
            $t->string('note')->nullable();
            $t->unsignedBigInteger('user_id')->nullable();
            $t->timestamps();
            $t->index('movement_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fund_movements');
        Schema::table('agents', fn (Blueprint $t) => $t->dropColumn('opening_balance'));
        Schema::table('customers', fn (Blueprint $t) => $t->dropColumn('opening_balance'));
    }
};
