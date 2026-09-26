<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'customer_id',
        'itemshipment_id',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function itemshipment()
    {
        return $this->belongsTo(ItemsShipment::class, 'itemshipment_id');
    }

    /**
     * Generate a unique invoice number.
     * Format: INV{YY}{00001}
     * Example: INV2600001
     */
    public static function generateNumber(int $customerId, int $shipmentId): string
    {
        $year = now()->format('y'); // 2-digit year, e.g. "26"

        // Count existing invoices for this year
        $countThisYear = self::where('invoice_number', 'like', "INV{$year}%")->count();
        $sequence = $countThisYear + 1;

        $number = sprintf('INV%s%05d', $year, $sequence);

        // Safety: bump if the exact number exists
        while (self::where('invoice_number', $number)->exists()) {
            $sequence++;
            $number = sprintf('INV%s%05d', $year, $sequence);
        }

        return $number;
    }

    /**
     * Find or create an invoice for a customer + shipment pair.
     */
    public static function forPair(int $customerId, int $shipmentId): self
    {
        return self::firstOrCreate(
            [
                'customer_id'     => $customerId,
                'itemshipment_id' => $shipmentId,
            ],
            [
                'invoice_number' => self::generateNumber($customerId, $shipmentId),
            ]
        );
    }
}