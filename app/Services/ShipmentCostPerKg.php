<?php

namespace App\Services;

use App\Models\ItemsShipment;

/**
 * Per-kg cost of one shipment.
 *
 *  weights   : original (sum weight_kg), final (billing weight), freight agent weight, freight CBM weight
 *  basis     : freight agent weight when entered, otherwise the final (billing) weight
 *  Row 1     : freight / other / total per kg on the basis weight
 *  Row 2     : (only when a "Freight Agent Cost CBM" bill AND its weight exist)
 *              CBM bill / CBM weight, other bills / basis weight, total
 */
class ShipmentCostPerKg
{
    public static function isCbm($costType): bool
    {
        $n = strtolower((string) optional($costType)->name);
        return str_contains($n, 'freight') && str_contains($n, 'cbm');
    }

    public static function isFreight($costType): bool
    {
        $n = strtolower((string) optional($costType)->name);
        return str_contains($n, 'freight') && !str_contains($n, 'cbm');
    }

    public static function compute(ItemsShipment $s): array
    {
        $s->loadMissing(['cargoItems', 'costs.costType']);

        $original = round((float) $s->cargoItems->sum('weight_kg'), 2);
        $final    = round((float) $s->cargoItems->sum('billing_weight'), 2);

        $val = fn ($c) => (float) ($c->final_bdt_cost ?: $c->amount);

        $freightLines = $s->costs->filter(fn ($c) => self::isFreight($c->costType));
        $cbmLines     = $s->costs->filter(fn ($c) => self::isCbm($c->costType));
        $otherLines   = $s->costs->reject(fn ($c) => self::isFreight($c->costType) || self::isCbm($c->costType));

        $freightWeight = (float) $freightLines->max('total_weight');
        $cbmWeight     = (float) $cbmLines->max('total_weight');

        $freightCost = round($freightLines->sum($val), 2);
        $cbmCost     = round($cbmLines->sum($val), 2);
        $otherCost   = round($otherLines->sum($val), 2);

        $basis       = $freightWeight > 0 ? $freightWeight : $final;
        $basisSource = $freightWeight > 0 ? 'Freight agent weight' : 'Final weight';
        $div = fn ($a, $w) => $w > 0 ? round($a / $w, 2) : null;

        $hasCbm = $cbmCost > 0 && $cbmWeight > 0;

        // Row 1: everything on the basis weight (a CBM bill without a CBM weight is also spread on it)
        $row1Freight = $freightCost + ($hasCbm ? 0 : $cbmCost);
        $row1 = [
            'label'   => 'On ' . strtolower($basisSource) . ' (' . number_format($basis, 2) . ' kg)',
            'freight' => $div($row1Freight, $basis),
            'other'   => $div($otherCost, $basis),
            'total'   => $div($row1Freight + $otherCost, $basis),
        ];

        $row2 = null;
        if ($hasCbm) {
            $cbmKg   = $div($cbmCost, $cbmWeight);
            $otherKg = $div($freightCost + $otherCost, $basis);
            $row2 = [
                'label' => 'CBM bill on CBM weight (' . number_format($cbmWeight, 2) . ' kg), other bills on ' . strtolower($basisSource) . ' (' . number_format($basis, 2) . ' kg)',
                'cbm'   => $cbmKg,
                'other' => $otherKg,
                'total' => round(($cbmKg ?? 0) + ($otherKg ?? 0), 2),
            ];
        }

        return [
            'original' => $original, 'final' => $final,
            'freight_weight' => $freightWeight ?: null, 'cbm_weight' => $cbmWeight ?: null,
            'basis' => $basis, 'basis_source' => $basisSource,
            'freight_cost' => $freightCost, 'cbm_cost' => $cbmCost, 'other_cost' => $otherCost,
            'total_cost' => round($freightCost + $cbmCost + $otherCost, 2),
            'row1' => $row1, 'row2' => $row2,
        ];
    }
}
