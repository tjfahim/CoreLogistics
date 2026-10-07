<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LegacyCustomer extends Model
{
    protected $guarded = [];
    protected $casts = ['unapplied_credit' => 'float', 'client_dues_list' => 'float'];
}
