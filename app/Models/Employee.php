<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $fillable = ['name', 'office_id', 'salary'];

    public function office() { return $this->belongsTo(Office::class); }
}