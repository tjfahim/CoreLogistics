<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ItemsShipmentCostAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'items_shipment_cost_id',
        'file_path',
        'original_name',
        'mime_type',
        'file_size',
    ];

    public function cost()
    {
        return $this->belongsTo(ItemsShipmentCost::class, 'items_shipment_cost_id');
    }

    public function getUrlAttribute()
    {
        // files saved by the fallback (public/uploads/...) are served directly
        return str_starts_with((string) $this->file_path, 'uploads/')
            ? asset($this->file_path)
            : Storage::disk('public')->url($this->file_path);
    }

    /**
     * Save an uploaded file. Normal place: storage/app/public/shipment-costs/{id}.
     * If the server will not let PHP create that folder (permission problem), the file goes to public/uploads/shipment-costs/{id}.
     * Returns the path to keep in file_path.
     */
    public static function storeUpload(\Illuminate\Http\UploadedFile $file, int $costId): string
    {
        $dir = "shipment-costs/{$costId}";
        try {
            $abs = Storage::disk('public')->path($dir);
            if (!is_dir($abs)) {
                @mkdir($abs, 0775, true);
            }
            $path = $file->store($dir, 'public');
            if ($path) {
                return $path;
            }
        } catch (\Throwable $e) {
            \Log::warning('Attachment: storage disk failed, using public/uploads. ' . $e->getMessage());
        }

        $target = public_path("uploads/{$dir}");
        if (!is_dir($target) && !@mkdir($target, 0775, true) && !is_dir($target)) {
            throw new \RuntimeException('Cannot create upload folder. Please make storage/ and public/uploads writable (chmod 775).');
        }
        $name = \Illuminate\Support\Str::random(40) . '.' . ($file->guessExtension() ?: $file->getClientOriginalExtension());
        $file->move($target, $name);
        return "uploads/{$dir}/{$name}";
    }

    /** Delete stored files, wherever they were saved. */
    public static function deleteStored(array $paths): void
    {
        foreach ($paths as $p) {
            if (str_starts_with((string) $p, 'uploads/')) {
                @unlink(public_path($p));
            } else {
                Storage::disk('public')->delete($p);
            }
        }
    }

    public function getIsImageAttribute()
    {
        return str_starts_with((string) $this->mime_type, 'image/');
    }

    public function getSizeKbAttribute()
    {
        return round($this->file_size / 1024, 1);
    }
}