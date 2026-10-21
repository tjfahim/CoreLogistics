<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CtnSequence extends Model
{
    protected $fillable = ['prefix', 'last_number', 'pad_width', 'last_used_at'];

    protected $casts = ['last_used_at' => 'datetime'];

    /** Split "AB-100" into ['AB-', 100, 3]. Returns null if no trailing number. */
    public static function parse(?string $value): ?array
    {
        $value = trim((string) $value);
        if ($value === '' || !preg_match('/^(.*?)(\d+)$/', $value, $m)) {
            return null;
        }
        $pad = (strlen($m[2]) > 1 && $m[2][0] === '0') ? strlen($m[2]) : 0;
        return [$m[1], (int) $m[2], $pad];
    }

    /** Remember $value as the most recently used CTN of its prefix. */
    public static function record(?string $value): void
    {
        $p = self::parse($value);
        if (!$p) return;
        [$prefix, $number, $pad] = $p;

        $seq = self::firstOrNew(['prefix' => $prefix]);
        $seq->last_number = max((int) $seq->last_number, $number);
        $seq->pad_width = $pad;
        $seq->last_used_at = now();
        $seq->save();
    }

    /** Suggested next CTN (e.g. "AB-100") based on the latest used prefix, or null. */
    public static function suggestion(): ?string
    {
        $seq = self::orderByDesc('last_used_at')->orderByDesc('id')->first();
        if (!$seq) return null;
        return $seq->prefix . str_pad((string) ($seq->last_number + 1), $seq->pad_width, '0', STR_PAD_LEFT);
    }
}
