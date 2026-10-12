<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Office extends Model
{
    protected $fillable = ['name', 'location'];

    public function employees() { return $this->hasMany(Employee::class); }
    public function bills()     { return $this->hasMany(OfficeBill::class); }
    public function warehouseBills() { return $this->hasMany(WarehouseBill::class); }
}