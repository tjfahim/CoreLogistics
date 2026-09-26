<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImportedExcelFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'file_name',
        'file_path',
        'title',
        'note',
        'total_rows',
        'imported_rows',
        'duplicate_rows',
        'uploaded_by',
    ];

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getFileSizeAttribute()
    {
        $fullPath = storage_path('app/' . $this->file_path);
        if (file_exists($fullPath)) {
            $bytes = filesize($fullPath);
            $units = ['B', 'KB', 'MB', 'GB'];
            $i = 0;
            while ($bytes >= 1024 && $i < count($units) - 1) {
                $bytes /= 1024;
                $i++;
            }
            return round($bytes, 2) . ' ' . $units[$i];
        }
        return 'N/A';
    }
}