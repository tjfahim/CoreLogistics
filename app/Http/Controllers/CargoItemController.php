<?php

namespace App\Http\Controllers;

use App\Imports\CargoItemsImport;
use App\Models\CargoItem;
use App\Models\ImportedExcelFile;
use App\Models\ItemsShipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class CargoItemController extends Controller
{
public function index()
{
    return view('admin.cargo_items.index', [
        'currentPageStatus' => 'all'
    ]);
}
public function pendingItems()
{
    return view('admin.cargo_items.index', ['currentPageStatus' => 'pending']);
}

public function packedItems()
{
    return view('admin.cargo_items.index', ['currentPageStatus' => 'packed']);
}

public function arrivedBdItems()
{
    return view('admin.cargo_items.index', ['currentPageStatus' => 'arrived_bd']);
}

public function bangladeshWarehouseItems()
{
    return view('admin.cargo_items.index', ['currentPageStatus' => 'bangladesh_warehouse']);
}

public function otherStatusPage($status)
{
    return view('admin.cargo_items.index', ['currentPageStatus' => $status]);
}


public function ajaxItems(Request $request)
{
    $status = $request->input('status', 'all');
    $search = $request->input('search', '');
    $perPage = (int) $request->input('per_page', 50);
    $page = (int) $request->input('page', 1);

    // Which DB statuses map to which UI "page"
    $statusGroups = [
        'pending'              => ['pending'],
        'packed'               => ['packed'],
        'shipping_ongoing'     => ['shipping_ongoing'],
        'arrived_bd'           => ['arrived_bd', 'waiting_for_customs', 'ready_to_deliver'],
        'waiting_for_customs'  => ['waiting_for_customs'],
        'ready_to_deliver'     => ['ready_to_deliver'],
        'delivered'            => ['delivered'],
        'bangladesh_warehouse' => ['ready_to_deliver', 'delivered'],
    ];

    $query = CargoItem::query();

    if ($status !== 'all' && isset($statusGroups[$status])) {
        $query->whereIn('status', $statusGroups[$status]);
    } elseif ($status !== 'all') {
        $query->where('status', $status);
    }

    if ($search !== '') {
        $query->where(function ($q) use ($search) {
            $q->where('tracking_no', 'like', "%{$search}%")
              ->orWhere('goods_name_en', 'like', "%{$search}%")
              ->orWhere('printed_ctn_no', 'like', "%{$search}%")
              ->orWhere('written_ctn_no', 'like', "%{$search}%");
        });
    }

    $items = $query->orderBy('id', 'desc')->paginate($perPage, ['*'], 'page', $page);

    return response()->json([
        'data' => $items->items(),
        'meta' => [
            'current_page' => $items->currentPage(),
            'last_page'    => $items->lastPage(),
            'per_page'     => $items->perPage(),
            'total'        => $items->total(),
            'from'         => $items->firstItem(),
            'to'           => $items->lastItem(),
        ],
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

        // Check duplicates (manual entry)
        $duplicates = [];
       $seen = [];
foreach ($validated['items'] as $index => $itemData) {
    $key = $this->buildRowKey($itemData);
    if ($key === null) continue;
    $exists = $this->rowExistsInDatabase($itemData);
    $isDuplicate = $exists || isset($seen[$key]);
    if ($isDuplicate) {
        $duplicates[] = [
            'row' => $index + 1,
            'tracking_no' => $itemData['tracking_no'],
            'goods_name_en' => $itemData['goods_name_en'],
            'reason' => $exists ? 'Already in database' : 'Duplicate in form',
        ];
    }
    $seen[$key] = true;
}


        // If duplicate action was requested
        $duplicateAction = $request->input('duplicate_action', 'skip');

        DB::transaction(function () use ($validated, $duplicateAction) {
            $seen = [];
            foreach ($validated['items'] as $itemData) {
                $itemData['is_copy'] = !empty($itemData['is_copy']);

                $key = strtolower(trim(($itemData['tracking_no'] ?? '') . '|' . ($itemData['goods_name_en'] ?? '')));
                if (empty($itemData['tracking_no'])) continue;

                $exists = CargoItem::where('tracking_no', $itemData['tracking_no'])
                    ->where('goods_name_en', $itemData['goods_name_en'])
                    ->exists();

                $isDuplicate = $exists || isset($seen[$key]);
                $seen[$key] = true;

                if ($isDuplicate && $duplicateAction === 'skip') {
                    continue;
                }

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
            'status'           => ['nullable'],
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

    // ============================================
    // EXCEL IMPORT
    // ============================================

    /**
     * Handle Excel upload - store file and redirect to preview
     */
    public function import(Request $request)
    {
        $request->validate([
            'file'  => ['required', 'file', 'mimes:xlsx,xls'],
            'title' => ['nullable', 'string', 'max:255'],
            'note'  => ['nullable', 'string', 'max:1000'],
        ]);

        $uploadedFile = $request->file('file');

        // Store file permanently
        $fileName = time() . '_' . preg_replace('/[^A-Za-z0-9.\-_]/', '_', $uploadedFile->getClientOriginalName());
        $filePath = $uploadedFile->storeAs('imported_excel', $fileName, 'local');

        // Save file record
        $excelFile = ImportedExcelFile::create([
            'file_name'   => $uploadedFile->getClientOriginalName(),
            'file_path'   => $filePath,
            'title'       => $request->input('title') ?: $uploadedFile->getClientOriginalName(),
            'note'        => $request->input('note'),
            'uploaded_by' => auth()->id(),
        ]);

        // Parse file for preview
        $importedData = Excel::toArray(new CargoItemsImport, $uploadedFile);
        $importedData = $importedData[0] ?? [];

        array_shift($importedData); // remove header

        $transformedItems = [];
        foreach ($importedData as $row) {
            if ($this->isValidRow($row)) {
                $transformedItems[] = $this->transformRow($row);
            }
        }

        // Detect duplicates
        $duplicates = $this->detectDuplicates($transformedItems);

        // Update file stats
        $excelFile->update([
            'total_rows'     => count($transformedItems),
            'duplicate_rows' => count($duplicates),
        ]);

        // Store preview data in session
        session([
            'import_preview_' . $excelFile->id => [
                'items'      => $transformedItems,
                'duplicates' => $duplicates,
                'file_id'    => $excelFile->id,
            ]
        ]);

        return redirect()->route('cargo-items.import.preview', $excelFile->id);
    }

    /**
     * Show import preview with duplicate warnings
     */
   public function importPreview($fileId)
{
    $sessionKey = 'import_preview_' . $fileId;
    $preview = session($sessionKey);

    if (!$preview) {
        return redirect()->route('cargo-items.create')
            ->with('error', 'Preview data expired. Please upload again.');
    }

    $excelFile = ImportedExcelFile::findOrFail($fileId);

    // Build list of unique rows and their duplicate status
    $duplicateKeys = [];
    foreach ($preview['duplicates'] as $dup) {
        $duplicateKeys[] = strtolower(
            ($dup['tracking_no'] ?? '') . '|' .
            ($dup['goods_name_en'] ?? '') . '|' .
            ($dup['printed_ctn_no'] ?? '') . '|' .
            ($dup['written_ctn_no'] ?? '') . '|' .
            ($dup['weight_kg'] ?? '')
        );
    }

    $uniqueItems = [];
    foreach ($preview['items'] as $i => $item) {
        $key = strtolower(
            ($item['tracking_no'] ?? '') . '|' .
            ($item['goods_name_en'] ?? '') . '|' .
            ($item['printed_ctn_no'] ?? '') . '|' .
            ($item['written_ctn_no'] ?? '') . '|' .
            ($item['weight_kg'] ?? '')
        );

        // Only include if NOT in duplicate list (based on row number)
        $isDuplicate = false;
        foreach ($preview['duplicates'] as $dup) {
            if ($dup['row'] === ($i + 1)) {
                $isDuplicate = true;
                break;
            }
        }
        if (!$isDuplicate) {
            $uniqueItems[] = array_merge($item, ['_row' => $i + 1]);
        }
    }

    return view('admin.cargo_items.import_preview', [
        'excelFile'    => $excelFile,
        'items'        => $preview['items'],
        'duplicates'   => $preview['duplicates'],
        'uniqueItems'  => $uniqueItems,
    ]);
}

    /**
     * Confirm import - save items to DB (with skip duplicates option)
     */
 public function importConfirm(Request $request, $fileId)
{
    $sessionKey = 'import_preview_' . $fileId;
    $preview = session($sessionKey);

    if (!$preview) {
        return redirect()->route('cargo-items.create')
            ->with('error', 'Preview data expired. Please upload again.');
    }

    $items = $preview['items'];
    $skipDuplicates = $request->input('skip_duplicates', '1') === '1';

    $importedCount = 0;
    $skippedCount = 0;

    DB::transaction(function () use ($items, $skipDuplicates, &$importedCount, &$skippedCount) {
        $seen = [];
        foreach ($items as $itemData) {
            $key = $this->buildRowKey($itemData);
            if ($key === null) continue;

            $exists = $this->rowExistsInDatabase($itemData);
            $isDuplicate = $exists || isset($seen[$key]);

            if ($isDuplicate && $skipDuplicates) {
                $seen[$key] = true;
                $skippedCount++;
                continue;
            }

            CargoItem::create($itemData);
            $seen[$key] = true;
            $importedCount++;
        }
    });

    $excelFile = ImportedExcelFile::findOrFail($fileId);
    $excelFile->update([
        'imported_rows'  => $importedCount,
        'duplicate_rows' => $skippedCount,
    ]);

    session()->forget($sessionKey);

    return redirect()
        ->route('cargo-items.index')
        ->with('success', "{$importedCount} cargo items imported successfully from '{$excelFile->title}'. Skipped {$skippedCount} duplicates.");
}
    /**
     * Cancel import preview
     */
    public function importCancel($fileId)
    {
        session()->forget('import_preview_' . $fileId);

        return redirect()->route('cargo-items.create')
            ->with('info', 'Import cancelled.');
    }

    /**
     * Detect duplicate rows (both in file and in DB)
     */
   /**
 * Detect duplicate rows (both in file and in DB)
 * 
 * Duplicate = same tracking_no AND same goods_name_en AND same printed_ctn_no
 *             AND same written_ctn_no AND same weight_kg
 * 
 * This handles the case where one tracking_no has multiple cartons/goods.
 */
private function detectDuplicates($items)
{
    $duplicates = [];
    $seen = [];

    foreach ($items as $index => $item) {
        $key = $this->buildRowKey($item);

        if ($key === null) {
            continue; // skip empty keys
        }

        // Check DB
        $existsInDb = $this->rowExistsInDatabase($item);

        $isDuplicateInFile = isset($seen[$key]);

        if ($existsInDb || $isDuplicateInFile) {
            $duplicates[] = [
                'row'           => $index + 1,
                'tracking_no'   => $item['tracking_no'] ?? '',
                'goods_name_en' => $item['goods_name_en'] ?? '',
                'printed_ctn_no'=> $item['printed_ctn_no'] ?? '',
                'written_ctn_no'=> $item['written_ctn_no'] ?? '',
                'weight_kg'     => $item['weight_kg'] ?? '',
                'reason'        => $existsInDb
                    ? 'Already exists in database'
                    : 'Duplicate within the file',
            ];
        }

        $seen[$key] = true;
    }

    return $duplicates;
}

private function buildRowKey($item)
{
    $tracking = trim((string)($item['tracking_no'] ?? ''));
    if ($tracking === '') {
        return null;
    }

    // Normalize: trim + lowercase + collapse whitespace
    $norm = function ($v) {
        if ($v === null) return '';
        $v = strtolower(trim((string)$v));
        return preg_replace('/\s+/', ' ', $v); // collapse multiple spaces
    };

    // Normalize numbers to fixed precision so 34 vs 34.0 vs "34.000" match
    $num = function ($v) {
        if ($v === null || $v === '') return '';
        return number_format((float)$v, 4, '.', ''); // up to 4 decimals
    };

    $parts = [
        $norm($tracking),
        $norm($item['goods_name_en']  ?? ''),
        $norm($item['printed_ctn_no'] ?? ''),
        $norm($item['written_ctn_no'] ?? ''),
        $num($item['pack_no']         ?? ''),
        $num($item['weight_kg']       ?? ''),
    ];

    return implode('|', $parts);
}
/**
 * Check if a row already exists in DB using the same multi-field key.
 */
private function rowExistsInDatabase($item)
{
    $tracking = trim((string)($item['tracking_no'] ?? ''));
    if ($tracking === '') {
        return false;
    }

    return CargoItem::whereRaw('TRIM(tracking_no) = ?', [$tracking])
        ->whereRaw('TRIM(COALESCE(goods_name_en, "")) = ?', [trim((string)($item['goods_name_en'] ?? ''))])
        ->whereRaw('TRIM(COALESCE(printed_ctn_no, "")) = ?', [trim((string)($item['printed_ctn_no'] ?? ''))])
        ->whereRaw('TRIM(COALESCE(written_ctn_no, "")) = ?', [trim((string)($item['written_ctn_no'] ?? ''))])
        ->where('pack_no', intval($item['pack_no'] ?? 0))
        ->where('weight_kg', floatval($item['weight_kg'] ?? 0))
        ->exists();
}
    // ============================================
    // IMPORTED FILES MANAGEMENT (view / delete only)
    // ============================================

    public function filesIndex()
    {
        $files = ImportedExcelFile::with('uploader')->latest()->paginate(20);
        return view('admin.cargo_items.files_index', compact('files'));
    }

    public function filesView(ImportedExcelFile $file)
    {
        if (!Storage::disk('local')->exists($file->file_path)) {
            return redirect()->route('cargo-items.files.index')
                ->with('error', 'File not found on storage.');
        }

        // Parse the file to show data
        $fullPath = Storage::disk('local')->path($file->file_path);
        $importedData = Excel::toArray(new CargoItemsImport, $fullPath);
        $importedData = $importedData[0] ?? [];

        array_shift($importedData);

        $transformedItems = [];
        foreach ($importedData as $row) {
            if ($this->isValidRow($row)) {
                $transformedItems[] = $this->transformRow($row);
            }
        }

        return view('admin.cargo_items.files_view', compact('file', 'transformedItems'));
    }

    public function filesDownload(ImportedExcelFile $file)
    {
        if (!Storage::disk('local')->exists($file->file_path)) {
            return redirect()->route('cargo-items.files.index')
                ->with('error', 'File not found.');
        }

        return Storage::disk('local')->download($file->file_path, $file->file_name);
    }

    public function filesDestroy(ImportedExcelFile $file)
    {
        if (Storage::disk('local')->exists($file->file_path)) {
            Storage::disk('local')->delete($file->file_path);
        }

        $file->delete();

        return redirect()->route('cargo-items.files.index')
            ->with('success', 'File deleted successfully.');
    }

    // ============================================
    // HELPERS (existing)
    // ============================================

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

    $copyOrNo = strtolower(trim((string)($row['copy_or_no'] ?? '')));
    $isCopy = in_array($copyOrNo, ['yes', 'copy', 'true', '1', 'y']);

    // Helper to trim safely (handles null)
    $clean = function ($value) {
        if ($value === null) return null;
        $trimmed = trim((string)$value);
        return $trimmed === '' ? null : $trimmed;
    };

    return [
        'date'             => $date,
        'goods_received'   => !empty($row['goods_received']) && is_numeric(str_replace('.', '', $row['goods_received'])) ? 1 : 0,
        'printed_ctn_no'   => $clean($row['printed_ctn_no'] ?? null),
        'written_ctn_no'   => $clean($row['written_ctn_no'] ?? null),
        'tracking_no'      => $clean($row['trackings'] ?? null),       // ← trimmed!
        'goods_name_en'    => $clean($row['goods_name'] ?? null),
        'goods_name_local' => $clean($row[''] ?? null),
        'pack_no'          => intval($row['pack_no'] ?? 0),
        'unit_pcs'         => intval($row['unit_pcs'] ?? 0),
        'weight_kg'        => floatval($row['weight_kg'] ?? 0),
        'length_cm'        => floatval($row['size'] ?? 0),
        'width_cm'         => floatval($row['10'] ?? 0),
        'height_cm'        => floatval($row['11'] ?? 0),
        'cbm'              => $cbm,
        'shipping_mark'    => $clean($row['shipping_mark'] ?? null),
        'remark'           => $clean($row['remark_for_liquidbattery_or_powder'] ?? null),
        'warehouse'        => $clean($row['ware_house'] ?? null),
        'is_copy'          => $isCopy,
    ];
}

    private function calculateCBM($row)
    {
        if (is_numeric($row['cbm'])) {
            return floatval($row['cbm']);
        }

        $length = floatval($row['size'] ?? 0);
        $width = floatval($row['10'] ?? 0);
        $height = floatval($row['11'] ?? 0);

        if ($length > 0 && $width > 0 && $height > 0) {
            return ($length * $width * $height) / 1000000;
        }

        return 0;
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
            return redirect()->route('cargo-items.tracking-search')
                ->with('error', 'No item found with Tracking No: ' . $request->tracking_no);
        }

        if (auth()->check()) {
            return view('admin.cargo_items.tracking_view', compact('item'));
        } else {
            return view('admin.cargo_items.tracking_view_public', compact('item'));
        }
    }
}