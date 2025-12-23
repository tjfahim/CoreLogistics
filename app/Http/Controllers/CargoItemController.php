<?php

namespace App\Http\Controllers;

use App\Imports\CargoItemsImport;
use App\Models\CargoItem;
use App\Models\ItemsShipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class CargoItemController extends Controller
{
       public function index()
    {
       
        $items = CargoItem::all(); 
    return view('admin.cargo_items.index', [
        'items' => $items,
        'currentPageStatus' => 'all' 
    ]);
    }

    
     public function pendingItems() {
    $items = CargoItem::where('status', 'pending')->get();
    return view('admin.cargo_items.index', [
        'items' => $items,
        'currentPageStatus' => 'pending'
    ]);
}

public function packedItems() {
    $items = CargoItem::where('status', 'packed')->get();
    return view('admin.cargo_items.index', [
        'items' => $items,
        'currentPageStatus' => 'packed'
    ]);
}

public function otherStatusPage($status) {
    $items = CargoItem::where('status', $status)->get();
    return view('admin.cargo_items.index', [
        'items' => $items,
        'currentPageStatus' => $status
    ]);
}
    
    public function create()
    {
        return view('admin.cargo_items.create');
    }

   public function store(Request $request)
{
    $validated = $request->validate([
        'items'                   => ['required', 'array', 'min:1'],
        'items.*.date'            => ['nullable', 'date'],
        'items.*.goods_received'  => ['nullable', 'integer', 'min:0'],
        'items.*.printed_ctn_no'  => ['nullable', 'string', 'max:50'],
        'items.*.written_ctn_no'  => ['nullable', 'string', 'max:50'],
        'items.*.tracking_no'     => ['required', 'string', 'max:100'],
        'items.*.goods_name_en'   => ['required', 'string', 'max:255'],
        'items.*.goods_name_local'=> ['nullable', 'string', 'max:255'],
        'items.*.pack_no'         => ['nullable', 'integer', 'min:0'],
        'items.*.unit_pcs'        => ['nullable', 'integer', 'min:0'],
        'items.*.weight_kg'       => ['nullable', 'numeric', 'min:0'],
        'items.*.length_cm'       => ['nullable', 'numeric', 'min:0'],
        'items.*.width_cm'        => ['nullable', 'numeric', 'min:0'],
        'items.*.height_cm'       => ['nullable', 'numeric', 'min:0'],
        'items.*.cbm'             => ['nullable', 'numeric', 'min:0'],
        'items.*.shipping_mark'   => ['nullable', 'string', 'max:255'],
        'items.*.remark'          => ['nullable', 'string', 'max:500'],
        'items.*.warehouse'       => ['nullable', 'string', 'max:255'],
        'items.*.is_copy'         => ['nullable'], 
    ]);

    DB::transaction(function () use ($validated) {
        foreach ($validated['items'] as $itemData) {
            $itemData['is_copy'] = !empty($itemData['is_copy']);
            CargoItem::create($itemData);
        }
    });

    return redirect()
        ->route('cargo-items.index')
        ->with('success', 'Cargo items created successfully.');
}
    public function edit(CargoItem $cargoItem)
    {
        return view('admin.cargo_items.edit', compact('cargoItem'));
    }

    public function update(Request $request, CargoItem $cargoItem)
    {
        $data = $request->validate([
            'date'             => ['nullable', 'date'],
            'goods_received'   => ['nullable', 'integer', 'min:0'],
            'printed_ctn_no'   => ['nullable', 'string', 'max:50'],
            'written_ctn_no'   => ['nullable', 'string', 'max:50'],
            'tracking_no'      => ['required', 'string', 'max:100'],
            'goods_name_en'    => ['required', 'string', 'max:255'],
            'goods_name_local' => ['nullable', 'string', 'max:255'],
            'pack_no'          => ['nullable', 'integer', 'min:0'],
            'unit_pcs'         => ['nullable', 'integer', 'min:0'],
            'weight_kg'        => ['nullable', 'numeric', 'min:0'],
            'length_cm'        => ['nullable', 'numeric', 'min:0'],
            'width_cm'         => ['nullable', 'numeric', 'min:0'],
            'height_cm'        => ['nullable', 'numeric', 'min:0'],
            'cbm'              => ['nullable', 'numeric', 'min:0'],
            'shipping_mark'    => ['nullable', 'string', 'max:255'],
            'remark'           => ['nullable', 'string', 'max:500'],
            'warehouse'        => ['nullable', 'string', 'max:255'],
            'is_copy'          => ['nullable'],
            'status'          => ['nullable'],
        ]);

        $data['is_copy'] = $request->boolean('is_copy');

        $cargoItem->update($data);

        return redirect()
            ->route('cargo-items.index')
            ->with('success', 'Cargo item updated successfully.');
    }

    public function destroy(CargoItem $cargoItem)
    {
        $cargoItem->delete();

        return redirect()
            ->route('cargo-items.index')
            ->with('success', 'Cargo item deleted successfully.');
    }
  public function import(Request $request)
{
    $request->validate([
        'file' => ['required', 'file', 'mimes:xlsx,xls'],
    ]);

    $importedData = Excel::toArray(new CargoItemsImport, $request->file('file'));
    $importedData = $importedData[0] ?? [];

    array_shift($importedData);

    $transformedItems = [];
    
    foreach ($importedData as $row) {
        if ($this->isValidRow($row)) {
            $transformedItems[] = $this->transformRow($row);
        }
    }

    return $this->storeTransformedItems($transformedItems);
}

private function isValidRow($row)
{
    return !empty($row['trackings']) && !empty($row['goods_name']);
}
private function transformRow($row)
{
    $date = null;
    if (!empty($row['goods_received']) && $row['goods_received'] !== 'Date (MM.DD)') {
        $dateParts = explode('.', $row['goods_received']);
        if (count($dateParts) === 2) {
            $currentYear = date('Y');
            $date = $currentYear . '-' . str_pad($dateParts[0], 2, '0', STR_PAD_LEFT) . '-' . str_pad($dateParts[1], 2, '0', STR_PAD_LEFT);
            
            if (!strtotime($date)) {
                $date = null;
            }
        }
    }

    $cbm = $this->calculateCBM($row);

    $copyOrNo = strtolower(trim($row['copy_or_no'] ?? ''));
    $isCopy = false;
    
    if (in_array($copyOrNo, ['yes', 'copy', 'true', '1', 'y'])) {
        $isCopy = true;
    }

    return [
        'date' => $date,
        'goods_received' => !empty($row['goods_received']) && is_numeric(str_replace('.', '', $row['goods_received'])) ? 1 : 0,
        'printed_ctn_no' => $row['printed_ctn_no'] ?? null,
        'written_ctn_no' => $row['written_ctn_no'] ?? null,
        'tracking_no' => $row['trackings'] ?? null,
        'goods_name_en' => $row['goods_name'] ?? null,
        'goods_name_local' => $row[''] ?? null, 
        'pack_no' => intval($row['pack_no'] ?? 0),
        'unit_pcs' => intval($row['unit_pcs'] ?? 0),
        'weight_kg' => floatval($row['weight_kg'] ?? 0),
        'length_cm' => floatval($row['size'] ?? 0),        // 'size' maps to length_cm
        'width_cm' => floatval($row['10'] ?? 0),           // '10' maps to width_cm
        'height_cm' => floatval($row['11'] ?? 0),          // '11' maps to height_cm
        'cbm' => $cbm,
        'shipping_mark' => $row['shipping_mark'] ?? null,
        'remark' => $row['remark_for_liquidbattery_or_powder'] ?? null,
        'warehouse' => $row['ware_house'] ?? null,
        'is_copy' => $isCopy,
    ];
}

private function calculateCBM($row)
{
    // If CBM is already a numeric value, use it
    if (is_numeric($row['cbm'])) {
        return floatval($row['cbm']);
    }

    // If CBM contains formula or is empty, calculate from dimensions
    $length = floatval($row['size'] ?? 0);
    $width = floatval($row['10'] ?? 0);
    $height = floatval($row['11'] ?? 0);

    if ($length > 0 && $width > 0 && $height > 0) {
        return ($length * $width * $height) / 1000000; // Convert cm³ to m³
    }

    return 0;
}

private function storeTransformedItems($items)
{
    if (empty($items)) {
        return redirect()
            ->back()
            ->with('error', 'No valid data found to import.');
    }

    DB::transaction(function () use ($items) {
        foreach ($items as $itemData) {
            CargoItem::create($itemData);
        }
    });

    return redirect()
        ->route('cargo-items.index')
        ->with('success', count($items) . ' cargo items imported successfully.');
}
public function trackingSearchForm()
{
    if (auth()->check()) {
        return view('admin.cargo_items.tracking_search');
    } else {
        return view('admin.cargo_items.tracking_search_public');
    }
}

public function trackingView(Request $request)
{
    $request->validate([
        'tracking_no' => 'required|string',
    ]);

    $item = CargoItem::where('tracking_no', $request->tracking_no)->first();

    if (!$item) {
        if (auth()->check()) {
            return redirect()->route('cargo-items.tracking-search')
                ->with('error', 'No item found with Tracking No: ' . $request->tracking_no);
        } else {
            return redirect()->route('cargo-items.tracking-search')
                ->with('error', 'No item found with Tracking No: ' . $request->tracking_no);
        }
    }

    // If found show correct view
    if (auth()->check()) {
        return view('admin.cargo_items.tracking_view', compact('item'));
    } else {
        return view('admin.cargo_items.tracking_view_public', compact('item'));
    }
}

}
