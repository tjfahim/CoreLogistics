<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Manual cash-book entries: opening balance, cash<->bank transfer, owner deposit / withdraw. */
class FundMovement extends Model
{
    protected $fillable = ['movement_date', 'type', 'from_account', 'to_account', 'amount', 'note', 'user_id'];
    protected $casts = ['movement_date' => 'date', 'amount' => 'float'];

    public const ACCOUNTS = ['cash' => 'Cash in hand', 'bank' => 'Bank / mobile banking'];
    public const TYPES = [
        'opening'  => 'Opening balance (cash/bank you have when you start)',
        'transfer' => 'Transfer (cash <-> bank)',
        'deposit'  => 'Money added (boss / owner / loan)',
        'withdraw' => 'Money taken out (owner withdraw)',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
