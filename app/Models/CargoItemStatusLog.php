<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class CargoItemStatusLog extends Model
{
    public $timestamps = false;

    protected $fillable = ['cargo_item_id', 'status', 'changed_at', 'user_id'];

    protected $casts = ['changed_at' => 'datetime'];

    /** Log a status for many items at once (used by bulk status updates that skip model events). */
    public static function recordMany(array $itemIds, string $status): void
    {
        if (empty($itemIds) || in_array($status, ['pending', 'packed'], true)) {
            return;
        }
        $now = now();
        $rows = array_map(fn ($id) => [
            'cargo_item_id' => $id, 'status' => $status, 'changed_at' => $now, 'user_id' => Auth::id(),
        ], array_values($itemIds));

        foreach (array_chunk($rows, 500) as $chunk) {
            self::insert($chunk);
        }
    }
}
