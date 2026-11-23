<?php
use App\Http\Controllers\{AiController, CommunicationController, CustomerController, DashboardController,
    EmployeeController, FinanceController, InventoryController, InvoiceController, JournalController,
    LeadController, OrderController, PayrollController, PurchaseOrderController, ReportController,
    StockMovementController, UserController, VendorController};
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('login'));

Route::middleware('auth')->group(function () {
    
    Route::get('report/crm', [ReportController::class, 'crm'])->middleware('role:admin,sales,pimpinan')->name('report.crm');
    Route::get('report/escm', [ReportController::class, 'escm'])->middleware('role:admin,manajer_gudang,pimpinan')->name('report.escm');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Penjaga sesi: dipanggil browser berkala selama pengguna aktif (dipakai fitur keluar otomatis)
    Route::post('/keep-alive', fn () => response()->noContent())->name('keepalive');

    // ===================== AI (semua role; tab dibatasi di controller) =====================
    Route::get('ai', [AiController::class, 'index'])->name('ai.index');
    Route::post('ai/run/{jenis}', [AiController::class, 'run'])->name('ai.run');

    // ===================== ADMIN: PENGGUNA =====================
    Route::middleware('role:admin')->resource('user', UserController::class)->except(['show']);

    // ===================== ERP =====================
    Route::middleware('role:admin,keuangan,pimpinan')->group(function () {
        Route::get('finance', [FinanceController::class, 'index'])->name('finance.index');
        Route::get('journal', [JournalController::class, 'index'])->name('journal.index');
        Route::get('report', [ReportController::class, 'index'])->name('report.index');
        Route::get('employee', [EmployeeController::class, 'index'])->name('employee.index');
    });
    Route::middleware('role:admin,keuangan')->group(function () {
        Route::resource('finance', FinanceController::class)->except(['index', 'show']);
        Route::resource('employee', EmployeeController::class)->except(['index', 'show']);
        Route::resource('payroll', PayrollController::class)->only(['index', 'create', 'store', 'destroy']);
        Route::patch('invoice/{invoice}/status', [InvoiceController::class, 'updateStatus'])->name('invoice.status');
        Route::delete('invoice/{invoice}', [InvoiceController::class, 'destroy'])->name('invoice.destroy');
    });
    Route::middleware('role:admin,keuangan,sales')->group(function () {
        Route::get('invoice/create', [InvoiceController::class, 'create'])->name('invoice.create');
        Route::post('invoice', [InvoiceController::class, 'store'])->name('invoice.store');
    });
    Route::middleware('role:admin,keuangan,sales,pimpinan')->group(function () {
        Route::get('invoice', [InvoiceController::class, 'index'])->name('invoice.index');
        Route::get('invoice/{invoice}', [InvoiceController::class, 'show'])->name('invoice.show');
    });

    // ===================== CRM =====================
    Route::middleware('role:admin,sales')->group(function () {          // tulis (create dulu, sebelum {lead})
        Route::resource('lead', LeadController::class)->except(['index', 'show']);
        Route::patch('lead/{lead}/status', [LeadController::class, 'status'])->name('lead.status');
        Route::post('lead/{lead}/convert', [LeadController::class, 'convert'])->name('lead.convert');
        Route::post('lead/{lead}/communication', [CommunicationController::class, 'store'])->name('communication.store');
        Route::delete('communication/{communication}', [CommunicationController::class, 'destroy'])->name('communication.destroy');
        Route::resource('customer', CustomerController::class)->except(['index', 'show']);
        Route::get('order/create', [OrderController::class, 'create'])->name('order.create');
        Route::post('order', [OrderController::class, 'store'])->name('order.store');
        Route::post('order/{order}/konfirmasi', [OrderController::class, 'konfirmasi'])->name('order.konfirmasi');
        Route::post('order/{order}/batal', [OrderController::class, 'batal'])->name('order.batal');
    });
    Route::middleware('role:admin,sales,pimpinan')->group(function () { // lihat
        Route::get('lead', [LeadController::class, 'index'])->name('lead.index');
        Route::get('lead/{lead}', [LeadController::class, 'show'])->name('lead.show');
        Route::get('pipeline', [LeadController::class, 'pipeline'])->name('pipeline');
        Route::get('communication', [CommunicationController::class, 'index'])->name('communication.index');
        Route::get('customer', [CustomerController::class, 'index'])->name('customer.index');
        Route::get('customer/{customer}', [CustomerController::class, 'show'])->name('customer.show');
    });

    // ===================== PESANAN (CRM <-> ESCM) =====================
    Route::middleware('role:admin,manajer_gudang')->group(function () {
        Route::post('order/{order}/proses', [OrderController::class, 'proses'])->name('order.proses');
        Route::post('order/{order}/kirim', [OrderController::class, 'kirim'])->name('order.kirim');
        Route::post('order/{order}/terima', [OrderController::class, 'terima'])->name('order.terima');
    });
    Route::middleware('role:admin,sales,manajer_gudang,pimpinan')->group(function () {
        Route::get('order', [OrderController::class, 'index'])->name('order.index');
        Route::get('order/{order}', [OrderController::class, 'show'])->name('order.show');
    });

    // ===================== ESCM =====================
    Route::middleware('role:admin,manajer_gudang')->group(function () {
        Route::resource('inventory', InventoryController::class)->except(['index', 'show']);
        Route::resource('vendor', VendorController::class)->except(['index', 'show']);
        Route::get('movement/create', [StockMovementController::class, 'create'])->name('movement.create');
        Route::post('movement', [StockMovementController::class, 'store'])->name('movement.store');
        Route::get('po/create', [PurchaseOrderController::class, 'create'])->name('po.create');
        Route::post('po', [PurchaseOrderController::class, 'store'])->name('po.store');
        Route::patch('po/{po}/status', [PurchaseOrderController::class, 'updateStatus'])->name('po.status');
        Route::delete('po/{po}', [PurchaseOrderController::class, 'destroy'])->name('po.destroy');
    });
    Route::middleware('role:admin,manajer_gudang,pimpinan')->group(function () {
        Route::get('inventory', [InventoryController::class, 'index'])->name('inventory.index');
        Route::get('vendor', [VendorController::class, 'index'])->name('vendor.index');
        Route::get('movement', [StockMovementController::class, 'index'])->name('movement.index');
        Route::get('po', [PurchaseOrderController::class, 'index'])->name('po.index');
    });
});

require __DIR__.'/auth.php';