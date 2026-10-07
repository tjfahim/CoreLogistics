<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LegacyUpload extends Model
{
    protected $guarded = [];

    protected $casts = [
        'source_totals' => 'array',
        'approved_at'   => 'datetime',
        'imported_at'   => 'datetime',
    ];

    public function rows()      { return $this->hasMany(LegacyRow::class, 'upload_id'); }
    public function payments()  { return $this->hasMany(LegacyPayment::class, 'upload_id'); }
    public function customers() { return $this->hasMany(LegacyCustomer::class, 'upload_id'); }
    public function uploader()  { return $this->belongsTo(User::class, 'uploaded_by'); }

    public function getTotalDueAttribute()
    {
        return $this->total_billed - $this->total_paid;
    }
}
