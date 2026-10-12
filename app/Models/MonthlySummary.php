<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MonthlySummary extends Model
{
    protected $fillable = [
        'year', 'month',
        'office_bills_total', 'salaries_total', 'warehouse_bills_total', 'grand_total',
        'default_kg', 'cost_per_kg',
    ];
}