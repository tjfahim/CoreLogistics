<?php

namespace App\Support;

use Illuminate\Http\Request;

/**
 * Payments are always stored in BDT (`amount`). When the money was paid / received in RMB, USD...
 * the foreign amount and the rate are kept too:   amount (BDT) = foreign_amount * exchange_rate
 */
class PaymentCurrency
{
    public const CURRENCIES = ['bdt' => 'BDT', 'rmb' => 'RMB', 'usd' => 'USD', 'dinar' => 'Dinar'];

    /**
     * Read currency / foreign_amount / exchange_rate from the request, recompute the BDT amount
     * server-side (never trust the browser) and merge it back as `amount`.
     *
     * @return array{currency:string, foreign_amount:?float, exchange_rate:float}
     */
    public static function apply(Request $request): array
    {
        $currency = strtolower((string) $request->input('currency', 'bdt'));
        if (!isset(self::CURRENCIES[$currency])) {
            $currency = 'bdt';
        }

        if ($currency === 'bdt') {
            return ['currency' => 'bdt', 'foreign_amount' => null, 'exchange_rate' => 1.0];
        }

        $foreign = round((float) $request->input('foreign_amount'), 2);
        $rate    = round((float) $request->input('exchange_rate'), 4);

        if ($foreign <= 0 || $rate <= 0) {
            throw new \InvalidArgumentException('Enter the ' . strtoupper($currency) . ' amount and the exchange rate.');
        }

        $request->merge(['amount' => round($foreign * $rate, 2)]);

        return ['currency' => $currency, 'foreign_amount' => $foreign, 'exchange_rate' => $rate];
    }
}
