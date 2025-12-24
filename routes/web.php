<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\AgentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CargoItemController;
use App\Http\Controllers\CostTypeController;
use App\Http\Controllers\CustomerBillController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ItemShipmentController;
use App\Http\Controllers\ItemShipmentCostController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ShipmentController;
use App\Http\Controllers\ShipmentCostController;
use App\Http\Controllers\ShipmentPaymentController;
use App\Http\Controllers\ShipmentReportController;
use Illuminate\Support\Facades\Artisan;
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
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/register', [AuthController::class, 'showRegisterFrom'])->name('register');
Route::post('/register-submit', [AuthController::class, 'registerSubmit'])->name('register.submit');
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
Route::group(['middleware' => 'role:2', 'prefix' => 'admin'], function () {
    Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('admin.dashboard');
    Route::resource('customers', CustomerController::class);
    Route::resource('products', ProductController::class);
    Route::resource('agents', AgentController::class);
    Route::resource('cost-types', CostTypeController::class);



    Route::resource('cargo-items', CargoItemController::class);
    Route::post('cargo-items/import', [CargoItemController::class, 'import'])->name('cargo-items.import');

    Route::resource('itemsshipments', ItemShipmentController::class)->parameters([
    'itemsshipments' => 'itemshipment'
]);
    Route::get('shipment-items', [ItemShipmentController::class, 'getShipmentItems'])->name('shipment.items');
    Route::post('/itemsshipments/get-or-create-draft', [ItemShipmentController::class, 'getOrCreateDraft'])->name('itemsshipments.get-or-create-draft');

    Route::post('/itemsshipments/get-or-create-draft', [ItemShipmentController::class, 'getOrCreateDraft'])->name('itemsshipments.get-or-create-draft');
Route::post('/itemsshipments/toggle-item', [ItemShipmentController::class, 'toggleItem'])->name('itemsshipments.toggle-item');

// Routes for item shipment costs
Route::get('itemshipments/{itemShipmentId}/costs', [ItemShipmentCostController::class, 'show'])->name('itemshipments.costs.show');
Route::post('itemshipments/{itemShipmentId}/costs', [ItemShipmentCostController::class, 'store'])->name('itemshipments.costs.store');
Route::post('itemshipments/costs/{costId}/payments', [ItemShipmentCostController::class, 'addPayment'])->name('itemshipments.costs.payments.store');
Route::delete('itemshipments/costs/{costId}', [ItemShipmentCostController::class, 'destroyCost'])->name('itemshipments.costs.destroy');
Route::delete('itemshipments/payments/{paymentId}', [ItemShipmentCostController::class, 'destroyPayment'])->name('itemshipments.payments.destroy');
Route::get('itemshipments/{itemShipmentId}/financial-summary', [ItemShipmentCostController::class, 'getFinancialSummary'])->name('itemshipments.financial-summary');

Route::get('itemshipments/{itemshipment}/edit-prices', [ItemShipmentCostController::class, 'pricepage'])->name('itemshipments.edit-prices');
Route::post('itemshipments/{itemshipment}/update-prices', [ItemShipmentCostController::class, 'updatePrices'])->name('itemshipments.update-prices');

Route::get('itemshipments/{itemshipment}/edit-ctn', [ItemShipmentCostController::class, 'ctn_update'])->name('itemshipments.edit-ctn-numbers');
Route::post('/itemshipments/{itemshipment}/update-ctn', 
    [ItemShipmentCostController::class, 'updateCtnNumbers']
)->name('itemshipments.update-ctn');




 Route::get('itemshipments/{itemshipment}/customer-assignment', [CustomerController::class, 'showProductAssignment'])
        ->name('itemshipments.customer.assignment');
    Route::post('itemshipments/{itemshipment}/customer-assignment/assign', [CustomerController::class, 'assignProducts'])
        ->name('itemshipments.customer.assign');
    Route::post('itemshipments/{itemshipment}/customer-assignment/bulk-assign', [CustomerController::class, 'bulkAssign'])
        ->name('itemshipments.customer.bulk-assign');
Route::get('itemshipments/{itemshipment}/customer-assignment/auto-assign', [CustomerController::class, 'autoAssignByShippingMark'])
    ->name('itemshipments.customer.autoassign');





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
        
    Route::get('/customers/{customer}/shipment/{itemshipment}/payment', 
        [PaymentController::class, 'create'])
        ->name('payments.create');

    Route::post('/customers/{customer}/shipment/{itemshipment}/payment', 
        [PaymentController::class, 'store'])
        ->name('payments.store');

    Route::get('/customers/{customer}/shipment/{itemshipment}/payment-history', 
        [PaymentController::class, 'history'])
        ->name('payments.history');


        



Route::prefix('reports')->group(function () {
    Route::get('/dashboard', [ShipmentReportController::class, 'dashboard'])->name('reports.dashboard');
    Route::get('/shipment/{id}', [ShipmentReportController::class, 'singleShipmentReport'])->name('reports.single');
    Route::get('/shipments', [ShipmentReportController::class, 'multipleShipmentsReport'])->name('reports.multiple');
    Route::post('/shipments/filter', [ShipmentReportController::class, 'multipleShipmentsReport'])->name('reports.multiple.filter');
    Route::get('/download/{type}/{id?}', [ShipmentReportController::class, 'downloadPdf'])->name('reports.download.pdf');
    Route::get('/export/{type}/{id?}', [ShipmentReportController::class, 'exportExcel'])->name('reports.export.excel');
});

});