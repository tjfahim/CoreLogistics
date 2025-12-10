<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentTransaction extends Model
{
    use HasFactory;

      protected $fillable = [
        'agent_payment_id',
        'amount',
        'payment_date',
        'payment_method',
        'notes',
        'reference_number'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    public function agentPayment()
    {
        return $this->belongsTo(AgentPayment::class);
    }
}
