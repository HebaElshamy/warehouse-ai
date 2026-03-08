<?php

use App\Http\Controllers\AlertController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ForecastController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\ReportsController;
use Illuminate\Support\Facades\Route;



Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Categories
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

    // Products
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');

    // Inventory
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::put('/inventory/{product}', [InventoryController::class, 'update'])->name('inventory.update');
    Route::post('/inventory/scan', [InventoryController::class, 'scan'])->name('inventory.scan');

    // Suppliers
    Route::get('/suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
    Route::post('/suppliers', [SupplierController::class, 'store'])->name('suppliers.store');
    Route::put('/suppliers/{supplier}', [SupplierController::class, 'update'])->name('suppliers.update');
    Route::delete('/suppliers/{supplier}', [SupplierController::class, 'destroy'])->name('suppliers.destroy');

    //Forecast
    Route::get('/forecasting', [ForecastController::class, 'index'])->name('forecasting.index');
    Route::post('/forecasting/generate', [ForecastController::class, 'generate'])->name('forecasting.generate');
    //Alert
    Route::get('/alerts', [AlertController::class, 'index'])->name('alerts.index');
    Route::put('/alerts/{id}/resolve', [AlertController::class, 'resolve'])->name('alerts.resolve');
    //Storage Environment
    // Route::get('/storage-environment', function () {
    // $readings = \App\Models\SensorReading::orderByDesc('measured_at')
    //     ->take(20)
    //     ->get();

    // return view('dashboard.storage.index', compact('readings'));
    //     })->name('storage.index');

    // Storage Environment
Route::get('/storage-environment', function () {

    $minTemp = 2;
    $maxTemp = 30;
    $minHum  = 40;
    $maxHum  = 65;

    // آخر قراءة
    $latest = \App\Models\SensorReading::orderByDesc('measured_at')->first();

    // قراءات اليوم للشارت
    $todayStart = now()->startOfDay();
    $todayEnd   = now()->endOfDay();

    $todayReadings = \App\Models\SensorReading::whereBetween('measured_at', [$todayStart, $todayEnd])
        ->orderBy('measured_at')
        ->get();

    // جدول آخر 20 قراءة
    $readings = \App\Models\SensorReading::orderByDesc('measured_at')
        ->take(20)
        ->get();

    // Alerts اليوم
    $alertsToday = \App\Models\Alert::whereIn('type', ['TEMP_ALERT','HUMIDITY_ALERT'])
        ->whereDate('created_at', today())
        ->latest()
        ->take(10)
        ->get();

    return view('dashboard.storage.index', compact(
        'readings',
        'latest',
        'todayReadings',
        'alertsToday',
        'minTemp','maxTemp','minHum','maxHum'
    ));

})->name('storage.index');

    //Report
    Route::get('/reports', [ReportsController::class, 'index'])->name('reports.index');
    Route::get('/reports/monthly-pdf', [ReportsController::class, 'monthlyPdf'])->name('reports.monthly_pdf');

    });



require __DIR__.'/auth.php';
