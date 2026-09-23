<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\SaleController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\PurchaseController;
use App\Http\Controllers\Api\StockEntryController;
use App\Http\Controllers\Api\ReturnToSupplierController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\CustomerReturnController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SessionController;

// ── Public ───────────────────────────────────────────────────────
Route::get('/ping', fn() => response()->json(['status' => 'ok', 'timestamp' => now()]));
//Route::post('/login', [AuthController::class, 'login']);
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:6,1');

// ── Protected ────────────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me',      [AuthController::class, 'me']);

    // Categories — lectura libre, escritura protegida
    Route::get('/categories',               [CategoryController::class, 'index']);
    Route::get('/categories/{category}',    [CategoryController::class, 'show']);
    Route::post('/categories',              [CategoryController::class, 'store'])->middleware('permission:categories.manage');
    Route::put('/categories/{category}',    [CategoryController::class, 'update'])->middleware('permission:categories.manage');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->middleware('permission:categories.manage');

    // Products
    Route::get('/products',             [ProductController::class, 'index'])->middleware('permission:products.view');
    Route::get('/products/{product}',   [ProductController::class, 'show'])->middleware('permission:products.view');
    Route::post('/products',            [ProductController::class, 'store'])->middleware('permission:products.create');
    Route::put('/products/{product}',   [ProductController::class, 'update'])->middleware('permission:products.edit');
    Route::delete('/products/{product}',[ProductController::class, 'destroy'])->middleware('permission:products.delete');

    // Suppliers — lectura libre, escritura protegida
    Route::get('/suppliers',              [SupplierController::class, 'index']);
    Route::get('/suppliers/{supplier}',   [SupplierController::class, 'show']);
    Route::post('/suppliers',             [SupplierController::class, 'store'])->middleware('permission:suppliers.manage');
    Route::put('/suppliers/{supplier}',   [SupplierController::class, 'update'])->middleware('permission:suppliers.manage');
    Route::delete('/suppliers/{supplier}',[SupplierController::class, 'destroy'])->middleware('permission:suppliers.manage');

    // Customers
    Route::get('/customers',              [CustomerController::class, 'index'])->middleware('permission:customers.view');
    Route::get('/customers/{customer}',   [CustomerController::class, 'show'])->middleware('permission:customers.view');
    Route::post('/customers',             [CustomerController::class, 'store'])->middleware('permission:customers.manage');
    Route::put('/customers/{customer}',   [CustomerController::class, 'update'])->middleware('permission:customers.manage');
    Route::delete('/customers/{customer}',[CustomerController::class, 'destroy'])->middleware('permission:customers.manage');

    // Customer Returns — permisos aplicados por consistencia; lógica de negocio sin revisar todavía
    Route::get('/customer-returns',                     [CustomerReturnController::class, 'index'])->middleware('permission:customer-returns.view');
    Route::get('/customer-returns/{customerReturn}',    [CustomerReturnController::class, 'show'])->middleware('permission:customer-returns.view');
    Route::post('/customer-returns',                    [CustomerReturnController::class, 'store'])->middleware('permission:customer-returns.manage');
    Route::put('/customer-returns/{customerReturn}',    [CustomerReturnController::class, 'update'])->middleware('permission:customer-returns.manage');
    Route::delete('/customer-returns/{customerReturn}', [CustomerReturnController::class, 'destroy'])->middleware('permission:customer-returns.manage');

    // Orders
    Route::get('/orders',           [OrderController::class, 'index'])->middleware('permission:orders.view');
    Route::get('/orders/{order}',   [OrderController::class, 'show'])->middleware('permission:orders.view');
    Route::post('/orders',          [OrderController::class, 'store'])->middleware('permission:orders.create');
    Route::put('/orders/{order}',   [OrderController::class, 'update'])->middleware('permission:orders.edit');
    Route::delete('/orders/{order}',[OrderController::class, 'destroy'])->middleware('permission:orders.delete');

    // Sales — sin duplicar; el refund es la única vía a "Refunded"
    Route::get('/sales',                [SaleController::class, 'index'])->middleware('permission:sales.view');
    Route::get('/sales/{sale}',         [SaleController::class, 'show'])->middleware('permission:sales.view');
    Route::post('/sales',               [SaleController::class, 'store'])->middleware('permission:sales.manage');
    Route::put('/sales/{sale}',         [SaleController::class, 'update'])->middleware('permission:sales.manage');
    Route::delete('/sales/{sale}',      [SaleController::class, 'destroy'])->middleware('permission:sales.manage');
    Route::post('/sales/{sale}/refund', [SaleController::class, 'refund'])->middleware('permission:sales.manage');

    // Payments — permisos aplicados por consistencia; lógica de negocio sin revisar todavía
    Route::get('/payments',             [PaymentController::class, 'index'])->middleware('permission:payments.view');
    Route::get('/payments/{payment}',   [PaymentController::class, 'show'])->middleware('permission:payments.view');
    Route::post('/payments',            [PaymentController::class, 'store'])->middleware('permission:payments.manage');
    Route::put('/payments/{payment}',   [PaymentController::class, 'update'])->middleware('permission:payments.manage');
    Route::delete('/payments/{payment}',[PaymentController::class, 'destroy'])->middleware('permission:payments.manage');

    // Purchases
    Route::get('/purchases',              [PurchaseController::class, 'index'])->middleware('permission:purchases.view');
    Route::get('/purchases/{purchase}',   [PurchaseController::class, 'show'])->middleware('permission:purchases.view');
    Route::post('/purchases',             [PurchaseController::class, 'store'])->middleware('permission:purchases.manage');
    Route::put('/purchases/{purchase}',   [PurchaseController::class, 'update'])->middleware('permission:purchases.manage');
    Route::delete('/purchases/{purchase}',[PurchaseController::class, 'destroy'])->middleware('permission:purchases.manage');

    // Stock Entries
    Route::get('/stock-entries',                [StockEntryController::class, 'index'])->middleware('permission:stock-entries.view');
    Route::get('/stock-entries/{stockEntry}',   [StockEntryController::class, 'show'])->middleware('permission:stock-entries.view');
    Route::post('/stock-entries',               [StockEntryController::class, 'store'])->middleware('permission:stock-entries.manage');
    Route::put('/stock-entries/{stockEntry}',   [StockEntryController::class, 'update'])->middleware('permission:stock-entries.manage');
    Route::delete('/stock-entries/{stockEntry}',[StockEntryController::class, 'destroy'])->middleware('permission:stock-entries.manage');

    // Return to Supplier
    Route::get('/return-to-suppliers',                       [ReturnToSupplierController::class, 'index'])->middleware('permission:returns.view');
    Route::get('/return-to-suppliers/{returnToSupplier}',    [ReturnToSupplierController::class, 'show'])->middleware('permission:returns.view');
    Route::post('/return-to-suppliers',                      [ReturnToSupplierController::class, 'store'])->middleware('permission:returns.manage');
    Route::put('/return-to-suppliers/{returnToSupplier}',    [ReturnToSupplierController::class, 'update'])->middleware('permission:returns.manage');
    Route::delete('/return-to-suppliers/{returnToSupplier}', [ReturnToSupplierController::class, 'destroy'])->middleware('permission:returns.manage');

    //returns to customers

    // Security
    Route::apiResource('roles',       RoleController::class)->middleware('permission:roles.manage');
    Route::apiResource('permissions', PermissionController::class)->middleware('permission:settings.manage');
    Route::apiResource('users',       UserController::class)->middleware('permission:users.manage');

    // Reports
    // Reports
    Route::get('/reports/summary', [\App\Http\Controllers\Api\ReportController::class, 'summary'])->middleware('permission:reports.view');
    Route::get('/reports/sales',   [\App\Http\Controllers\Api\ReportController::class, 'sales'])->middleware('permission:reports.view');
    Route::get('/reports/stock',   [\App\Http\Controllers\Api\ReportController::class, 'stock'])->middleware('permission:reports.view');

    // Audit Logs
    Route::get('/audit-logs', [\App\Http\Controllers\Api\AuditLogController::class, 'index'])
    ->middleware('permission:audit.view');

    // Sessions
    Route::get('/users/{user}/sessions',              [SessionController::class, 'index'])->middleware('permission:users.manage');
    Route::delete('/users/{user}/sessions',           [SessionController::class, 'revokeAll'])->middleware('permission:users.manage');
    Route::delete('/users/{user}/sessions/{tokenId}', [SessionController::class, 'revokeOne'])->middleware('permission:users.manage');
        
    });