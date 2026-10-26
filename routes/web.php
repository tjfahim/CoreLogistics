<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\AgentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CargoItemController;
use App\Http\Controllers\CostTypeController;
use App\Http\Controllers\CustomerBillController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerExtraShippingMarkController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\ItemShipmentController;
use App\Http\Controllers\ItemShipmentCostController;
use App\Http\Controllers\LedgerController;
use App\Http\Controllers\LegacyUploadController;
use App\Http\Controllers\OfficeBillController;
use App\Http\Controllers\OfficeController;
use App\Http\Controllers\OldCustomerController;
use App\Http\Controllers\OldPaymentAndReceiveController;
use App\Http\Controllers\OldShipmentController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ShipmentController;
use App\Http\Controllers\ShipmentCostController;
use App\Http\Controllers\ShipmentPaymentController;
use App\Http\Controllers\ShipmentBillController;
use App\Http\Controllers\ReceiptNotificationController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WarehouseBillController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/


Route::get('/', [AuthController::class, 'showLoginFrom'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::get('/register', [AuthController::class, 'showRegisterFrom'])->name('register');
Route::post('/register-submit', [AuthController::class, 'registerSubmit'])->name('register.submit');


Route::fallback(function () {
    abort(404);
});


// PUBLIC + AUTH SEARCH FORM (same controller method)
Route::get('/tracking', [CargoItemController::class, 'trackingSearchForm'])
    ->name('cargo-items.tracking-search');

// Tracking search view for POST (public + auth handled inside controller)
Route::post('/tracking', [CargoItemController::class, 'trackingView'])
    ->name('cargo-items.tracking-view');
Route::get('/migrate-fresh-seed', function () {
    try {
        Artisan::call('migrate:fresh', [
            '--seed' => true,
            '--force' => true
        ]);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Database migrated and seeded successfully',
            'output' => Artisan::output()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ], 500);
    }
});
Route::get('/clear-cache', function () {
    $results = [];

    try {
        // 1. Storage: unlink the existing symlink (if any)
        $link = public_path('storage');
        if (File::exists($link) || is_link($link)) {
            // is_link check covers broken symlinks on Windows/Linux
            if (is_link($link)) {
                @unlink($link);
                $results['storage_unlink'] = 'Removed existing symlink';
            } elseif (File::isDirectory($link)) {
                // If it's a real folder (not a symlink), don't nuke it silently
                File::deleteDirectory($link);
                $results['storage_unlink'] = 'Removed existing directory at public/storage';
            }
        } else {
            $results['storage_unlink'] = 'Nothing to remove';
        }

        // 2. Storage: link
        Artisan::call('storage:link');
        $results['storage_link'] = trim(Artisan::output());

        // 3. Optimize: clear
        Artisan::call('optimize:clear');
        $results['optimize_clear'] = trim(Artisan::output());


        return response()->json([
            'status'  => 'success',
                'message' => 'Deploy refresh completed successfully',
            'results' => $results,
        ]);

    } catch (\Throwable $e) {
        return response()->json([
            'status'  => 'error',
            'message' => $e->getMessage(),
            'results' => $results, // shows which steps ran before failure
        ], 500);
    }
});
Route::middleware(['admin.access'])
    ->prefix('admin')
    ->group(function () {
    Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('admin.dashboard');
    Route::post('/admin/invoice-search', [DashboardController::class, 'searchInvoice'])
    ->name('admin.invoice.search');
        Route::post('/tracking-search', [DashboardController::class, 'searchTracking'])
        ->name('admin.tracking.search');
    Route::resource('customers', CustomerController::class);
    Route::resource('products', ProductController::class);
    Route::resource('agents', AgentController::class);
    Route::resource('cost-types', CostTypeController::class);

Route::resource('users', UserController::class);
    Route::post('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');

   // Role CRUD
    Route::resource('roles', RoleController::class);

    // Toggle status (AJAX)
    Route::post('roles/{role}/toggle-status', [RoleController::class, 'toggleStatus'])
        ->name('roles.toggle-status');
     // Permissions
    Route::resource('permissions', PermissionController::class);
    Route::post('permissions/bulk-store', [PermissionController::class, 'bulkStore'])
        ->name('permissions.bulk-store');
// Existing (keep as is)
Route::get('cargo-items/pending', [CargoItemController::class, 'pendingItems'])->name('cargo-items.pending');
Route::put('cargo-items/{cargoItem}', [CargoItemController::class, 'update'])->name('cargo-items.update');Route::get('cargo-items/packed', [CargoItemController::class, 'packedItems'])->name('cargo-items.packed');
Route::get('itemsshipments/{itemshipment}/manage-returns',
    [ItemShipmentController::class, 'manageReturns']
)->name('itemsshipments.manage-returns');

Route::post('itemsshipments/{itemshipment}/return-items',
    [ItemShipmentController::class, 'returnItems']
)->name('itemsshipments.return-items');
// NEW: Bangladesh-side routes
Route::post('cargo-items/{cargoItem}/cancel', [CargoItemController::class, 'cancel'])->name('cargo-items.cancel')->middleware('can:cancel-cargo-item');
Route::post('cargo-items/{cargoItem}/restore', [CargoItemController::class, 'restore'])->name('cargo-items.restore')->middleware('can:restore-cargo-item');
Route::post('itemsshipments/{itemshipment}/make-box', [ItemShipmentController::class, 'makeBox'])->name('itemsshipments.make-box');
Route::post('itemsshipments/{itemshipment}/remove-box', [ItemShipmentController::class, 'removeBox'])->name('itemsshipments.remove-box');
Route::get('cargo-items/arrived-bd', [CargoItemController::class, 'arrivedBdItems'])->name('cargo-items.arrived-bd');
Route::get('cargo-items/{cargoItem}/edit', [CargoItemController::class, 'edit'])->name('cargo-items.edit');
Route::get('cargo-items/bangladesh-warehouse', [CargoItemController::class, 'bangladeshWarehouseItems'])->name('cargo-items.bangladesh-warehouse');
Route::post('cargo-items/bulk-third-party', [CargoItemController::class, 'bulkThirdParty'])->name('cargo-items.bulk-third-party');
Route::get('cargo-items/ajax/items', [CargoItemController::class, 'ajaxItems'])->name('cargo-items.ajax-items');

// Status-specific (existing)
Route::get('cargo-items/status/{status}', [CargoItemController::class, 'otherStatusPage'])->name('cargo-items.status');

// Excel import routes (existing)
Route::post('cargo-items/import', [CargoItemController::class, 'import'])->name('cargo-items.import');
Route::get('cargo-items/import/{fileId}/preview', [CargoItemController::class, 'importPreview'])->name('cargo-items.import.preview');
Route::post('cargo-items/import/{fileId}/confirm', [CargoItemController::class, 'importConfirm'])->name('cargo-items.import.confirm');
Route::post('cargo-items/import/{fileId}/cancel', [CargoItemController::class, 'importCancel'])->name('cargo-items.import.cancel');

// Imported files (existing)
Route::get('cargo-items/files', [CargoItemController::class, 'filesIndex'])->name('cargo-items.files.index');
Route::get('cargo-items/files/{file}', [CargoItemController::class, 'filesView'])->name('cargo-items.files.view');
Route::get('cargo-items/files/{file}/download', [CargoItemController::class, 'filesDownload'])->name('cargo-items.files.download');
Route::delete('cargo-items/files/{file}', [CargoItemController::class, 'filesDestroy'])->name('cargo-items.files.destroy');

// Resource (last)
Route::resource('cargo-items', CargoItemController::class);

    Route::resource('itemsshipments', ItemShipmentController::class)->parameters([
    'itemsshipments' => 'itemshipment'
]);
    Route::get('shipment-items', [ItemShipmentController::class, 'getShipmentItems'])->name('shipment.items');
    Route::post('/itemsshipments/get-or-create-draft', [ItemShipmentController::class, 'getOrCreateDraft'])->name('itemsshipments.get-or-create-draft');
Route::post('itemsshipments/bulk-unpack-items', [ItemShipmentController::class, 'bulkUnpackItems'])
    ->name('itemsshipments.bulk-unpack-items');
Route::post('/itemsshipments/toggle-item', [ItemShipmentController::class, 'toggleItem'])->name('itemsshipments.toggle-item');
Route::post('itemsshipments/bulk-toggle-items', [ItemShipmentController::class, 'bulkToggleItems'])
    ->name('itemsshipments.bulk-toggle-items');
// Routes for item shipment costs
Route::get('itemshipments/{itemShipmentId}/costs', [ItemShipmentCostController::class, 'show'])->name('itemshipments.costs.show');
Route::post('itemshipments/{itemShipmentId}/costs', [ItemShipmentCostController::class, 'store'])->name('itemshipments.costs.store');
Route::post('itemshipments/costs/{costId}/payments', [ItemShipmentCostController::class, 'addPayment'])->name('itemshipments.costs.payments.store');
Route::post('itemshipments/costs/{costId}/attachments', [ItemShipmentCostController::class, 'addAttachments'])->name('itemshipments.costs.attachments.store');
Route::delete('itemshipments/attachments/{attachmentId}', [ItemShipmentCostController::class, 'destroyAttachment'])->name('itemshipments.attachments.destroy');
Route::delete('itemshipments/costs/{costId}', [ItemShipmentCostController::class, 'destroyCost'])->name('itemshipments.costs.destroy');
Route::delete('itemshipments/payments/{paymentId}', [ItemShipmentCostController::class, 'destroyPayment'])->name('itemshipments.payments.destroy');
Route::get('itemshipments/{itemShipmentId}/financial-summary', [ItemShipmentCostController::class, 'getFinancialSummary'])->name('itemshipments.financial-summary');

Route::resource('old-shipments', OldShipmentController::class)
    ->parameters(['old-shipments' => 'old_shipment']);

Route::resource('old-customers', OldCustomerController::class)
    ->parameters(['old-customers' => 'old_customer']);

// Monthly report (keep this BEFORE the old-payments resource)
Route::get('old-payments-report', [OldPaymentAndReceiveController::class, 'report'])
    ->name('old-payments.report');

Route::resource('old-payments', OldPaymentAndReceiveController::class)
    ->except(['show'])
    ->parameters(['old-payments' => 'old_payment']);



Route::get('itemshipments/{itemshipment}/edit-prices', [ItemShipmentCostController::class, 'pricepage'])->name('itemshipments.edit-prices');
Route::post('itemshipments/{itemshipment}/update-prices', [ItemShipmentCostController::class, 'updatePrices'])->name('itemshipments.update-prices');

Route::get('itemshipments/{itemshipment}/edit-ctn', [ItemShipmentCostController::class, 'ctn_update'])->name('itemshipments.edit-ctn-numbers');
Route::post('/itemshipments/{itemshipment}/update-ctn', 
    [ItemShipmentCostController::class, 'updateCtnNumbers']
)->name('itemshipments.update-ctn');
Route::get('itemshipments/{itemshipment}/final-weight', [ItemShipmentCostController::class, 'finalWeightPage'])
    ->name('itemshipments.final-weight')->middleware('can:view-final-weight');
Route::post('itemshipments/{itemshipment}/final-weight', [ItemShipmentCostController::class, 'updateFinalWeight'])
    ->name('itemshipments.final-weight.update')->middleware('can:edit-final-weight');
Route::post('/itemshipments/{itemshipment}/auto-assign-ctn',
    [ItemShipmentCostController::class, 'autoAssignCtn']
)->name('itemshipments.auto-assign-ctn');



Route::get('settings', [\App\Http\Controllers\SettingController::class, 'index'])->name('settings.index')->middleware('can:view-settings');
Route::put('settings', [\App\Http\Controllers\SettingController::class, 'update'])->name('settings.update')->middleware('can:edit-settings');

Route::resource('offices', OfficeController::class)->middleware('can:manage-offices');
Route::resource('employees', EmployeeController::class)->middleware('can:manage-employees');
Route::post('itemsshipments/{itemshipment}/set-third-party', [ItemShipmentController::class, 'setThirdParty'])->name('itemsshipments.set-third-party');
Route::get('salary-history', [\App\Http\Controllers\EmployeeController::class, 'history'])->name('salary-history')->middleware('can:view-salary-history');
Route::resource('office-bills', OfficeBillController::class)
     ->parameters(['office-bills' => 'office_bill'])->middleware('can:manage-office-bills');
Route::resource('warehouse-bills', WarehouseBillController::class)
     ->only(['index', 'store', 'destroy'])
     ->parameters(['warehouse-bills' => 'warehouse_bill']);

Route::delete('warehouse-bills/summary/{summary}', [WarehouseBillController::class, 'destroySummary'])->name('warehouse-bills.summary.destroy');
Route::get('warehouse-bills/summary', [WarehouseBillController::class, 'summary'])
     ->name('warehouse-bills.summary');
Route::post('warehouse-bills/monthly-summary',
    [WarehouseBillController::class, 'saveMonthlySummary'])
    ->name('warehouse-bills.monthly-summary');

 Route::get('itemshipments/{itemshipment}/customer-assignment', [CustomerController::class, 'showProductAssignment'])
        ->name('itemshipments.customer.assignment');
    Route::post('itemshipments/{itemshipment}/customer-assignment/assign', [CustomerController::class, 'assignProducts'])
        ->name('itemshipments.customer.assign');
    Route::post('itemshipments/{itemshipment}/customer-assignment/bulk-assign', [CustomerController::class, 'bulkAssign'])
        ->name('itemshipments.customer.bulk-assign');
Route::get('itemshipments/{itemshipment}/customer-assignment/auto-assign', [CustomerController::class, 'autoAssignByShippingMark'])
    ->name('itemshipments.customer.autoassign');


Route::get('customer-extra-shipping-marks', [CustomerExtraShippingMarkController::class, 'index'])
    ->name('customer-extra-shipping-marks.index');
Route::get('customer-extra-shipping-marks/create', [CustomerExtraShippingMarkController::class, 'create'])
    ->name('customer-extra-shipping-marks.create');
Route::post('customer-extra-shipping-marks', [CustomerExtraShippingMarkController::class, 'store'])
    ->name('customer-extra-shipping-marks.store');
Route::get('customer-extra-shipping-marks/{id}', [CustomerExtraShippingMarkController::class, 'show'])
    ->name('customer-extra-shipping-marks.show');
Route::get('customer-extra-shipping-marks/{id}/edit', [CustomerExtraShippingMarkController::class, 'edit'])
    ->name('customer-extra-shipping-marks.edit');
Route::put('customer-extra-shipping-marks/{id}', [CustomerExtraShippingMarkController::class, 'update'])
    ->name('customer-extra-shipping-marks.update');
Route::delete('customer-extra-shipping-marks/{id}', [CustomerExtraShippingMarkController::class, 'destroy'])
    ->name('customer-extra-shipping-marks.destroy');

// AJAX inline actions
Route::post('customer-extra-shipping-marks/inline-store', [CustomerExtraShippingMarkController::class, 'inlineStore'])
    ->name('customer-extra-shipping-marks.inline-store');
Route::delete('customer-extra-shipping-marks/inline-destroy/{id}', [CustomerExtraShippingMarkController::class, 'inlineDestroy'])
    ->name('customer-extra-shipping-marks.inline-destroy');


         Route::get('/shipments/{itemshipment}/billing', 
        [CustomerBillController::class, 'showShipmentBilling'])
        ->name('shipments.billing');
    
    // Individual customer bill
    Route::get('/customers/{customer}/bill', 
        [CustomerBillController::class, 'showCustomerBill'])
        ->name('customers.bill');
    
    Route::get('/customers/{customer}/shipment/{itemshipment}/bill', 
        [CustomerBillController::class, 'showCustomerBill'])
        ->name('customers.shipment.bill');
    
    // Update item prices
    Route::post('/shipments/{itemshipment}/update-prices', 
        [CustomerBillController::class, 'updateItemPrices'])
        ->name('shipments.update-prices');
        


// old link from billing page -> redirects to the new form (keeps old buttons working)
Route::get('/customers/{customer}/shipment/{itemshipment}/payment', [PaymentController::class, 'create'])->name('payments.create');

// NEW: receive one amount and split it across shipments
Route::get('/customers/{customer}/receive-payment', [PaymentController::class, 'receive'])->name('payments.receive');
Route::post('/customers/{customer}/receive-payment', [PaymentController::class, 'storeReceipt'])->name('payments.receive.store');

// NEW: delete a whole receipt (permission: delete-payment)
Route::delete('/payments/{payment}', [PaymentController::class, 'destroy'])->name('payments.destroy');

Route::get('/customers/{customer}/shipment/{itemshipment}/payment-history', [PaymentController::class, 'history'])->name('payments.history');


        // ---- Accounting: Shipment Billing (what we owe per shipment; one voucher can pay many shipments) ----
Route::get('shipment-bills', [ShipmentBillController::class, 'index'])->name('shipment-bills.index');
Route::get('shipment-bills/ledger', [ShipmentBillController::class, 'ledger'])->name('shipment-bills.ledger');
Route::get('shipment-bills/pay', [ShipmentBillController::class, 'create'])->name('shipment-bills.create');
Route::post('shipment-bills/pay', [ShipmentBillController::class, 'store'])->name('shipment-bills.store');
Route::get('shipment-bills/{voucher}', [ShipmentBillController::class, 'show'])->name('shipment-bills.show');
Route::delete('shipment-bills/{voucher}', [ShipmentBillController::class, 'destroy'])->name('shipment-bills.destroy');

        Route::prefix('ledger')->name('ledger.')->group(function () {
    Route::get('/', [LedgerController::class, 'index'])->name('index')->middleware('can:view-customer-ledger');
    Route::get('/agents', [\App\Http\Controllers\PartyLedgerController::class, 'agents'])->name('agents')->middleware('can:view-agent-ledger');
    Route::get('/agents/{agent}', [\App\Http\Controllers\PartyLedgerController::class, 'agent'])->name('agent')->middleware('can:view-agent-ledger');
    Route::get('/shipments/{itemshipment}', [\App\Http\Controllers\PartyLedgerController::class, 'shipment'])->name('shipment')->middleware('can:view-shipment-ledger');
    Route::get('/{customer}', [LedgerController::class, 'customer'])->name('customer')->middleware('can:view-customer-ledger');
});



Route::prefix('reports')->group(function () {
    Route::get('/final', [\App\Http\Controllers\FinalReportController::class, 'index'])->name('reports.final')->middleware('can:view-final-report');
    Route::get('/final/detail/{type}', [\App\Http\Controllers\FinalReportController::class, 'detail'])->name('reports.final.detail')->middleware('can:view-final-report');
    Route::get('/dashboard', [ReportController::class, 'dashboard'])->name('reports.dashboard');
    Route::get('/summary', [ReportController::class, 'summary'])->name('reports.summary')->middleware('can:view-business-summary');
    Route::get('/period', [ReportController::class, 'period'])->name('reports.period')->middleware('can:view-period-report');
    Route::get('/shipments', [ReportController::class, 'shipments'])->name('reports.shipments')->middleware('can:view-shipment-report');
    Route::get('/customers', [ReportController::class, 'customers'])->name('reports.customers')->middleware('can:view-customer-report');
    Route::get('/office', [ReportController::class, 'office'])->name('reports.office')->middleware('can:view-office-report');
    Route::get('/shipment/{id}', [ReportController::class, 'single'])->name('reports.single')->middleware('can:view-shipment-report');
});

Route::prefix('notifications')->name('notifications.')->group(function () {
    Route::get('/', [ReceiptNotificationController::class, 'index'])->name('index');
    Route::post('/toggle', [ReceiptNotificationController::class, 'toggle'])->name('toggle');
    Route::post('/bulk', [ReceiptNotificationController::class, 'bulk'])->name('bulk');
});



Route::prefix('legacy')->name('legacy.')->middleware('can:view-legacy-upload')->group(function () {
    Route::get('/',                          [LegacyUploadController::class, 'index'])->name('index');
    Route::post('/',                         [LegacyUploadController::class, 'store'])->name('store');
    Route::get('/{upload}',                  [LegacyUploadController::class, 'show'])->name('show');
    Route::post('/{upload}/approve',         [LegacyUploadController::class, 'approve'])->name('approve');
    Route::post('/{upload}/import-live',     [LegacyUploadController::class, 'importLive'])->name('import-live');
    Route::get('/{upload}/report',           [LegacyUploadController::class, 'report'])->name('report');
    Route::get('/{upload}/download',         [LegacyUploadController::class, 'download'])->name('download');
    Route::delete('/{upload}',               [LegacyUploadController::class, 'destroy'])->name('destroy');
});
}); 