<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ThirdPartyPayment extends Model
{
    protected $fillable = [
        'third_party_id', 'user_id', 'amount', 'currency', 'foreign_amount', 'exchange_rate',
        'payment_date', 'payment_method', 'reference_no', 'notes',
    ];

    protected $casts = ['payment_date' => 'date'];

    public function thirdParty() { return $this->belongsTo(ThirdParty::class); }
    public function user()       { return $this->belongsTo(User::class); }
}
