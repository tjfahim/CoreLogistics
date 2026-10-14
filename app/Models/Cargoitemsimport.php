<?php

namespace App\Imports;

/**
 * Plain import object: Excel::toArray() returns the first sheet as a
 * numeric-indexed array (no heading row).
 *
 * Formulas are deliberately NOT calculated (no WithCalculatedFormulas):
 * the packing list's totals row has SUM/COUNTA over ranges up to row 9765
 * plus TODAY(), and calculating them can blow up memory. The only formulas
 * in the data rows are CBM (=L*W*H/1000000), which the controller computes
 * itself from L/W/H.
 *
 * Header detection / column mapping is done in
 * CargoItemController::parseExcel().
 */
class CargoItemsImport
{
}