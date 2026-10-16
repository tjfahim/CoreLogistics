<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * "Did we tell the customer that receipt X reached status Y?"
 * Cargo items are grouped by receipt_no; one row per (receipt_no, status).
 */
class ReceiptStatusNotification extends Model
{
    protected $fillable = [
        'receipt_no',
        'status',
        'is_notified',
        'notified_at',
        'notified_by',
        'note',
    ];

    protected $casts = [
        'is_notified' => 'boolean',
        'notified_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'notified_by');
    }

    /**
     * For every status: how many receipts have at least one item at/after that status
     * but the customer has NOT been notified yet.
     *
     * @return array<string,int>  status => pending receipts
     */
    public static function pendingCounts(): array
    {
        $keys   = array_keys(CargoItem::STATUS_FLOW);
        $result = [];

        foreach ($keys as $i => $status) {
            $result[$status] = (int) DB::table('cargo_items')
                ->whereNotNull('receipt_no')
                ->where('receipt_no', '<>', '')
                ->whereIn('status', array_slice($keys, $i))
                ->whereNotExists(function ($q) use ($status) {
                    $q->select(DB::raw(1))
                      ->from('receipt_status_notifications as n')
                      ->whereColumn('n.receipt_no', 'cargo_items.receipt_no')
                      ->where('n.status', $status)
                      ->where('n.is_notified', 1);
                })
                ->distinct()
                ->count('receipt_no');
        }

        return $result;
    }
}
