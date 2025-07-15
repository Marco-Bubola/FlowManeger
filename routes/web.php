<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

// Importe o componente Livewire corretamente
use App\Livewire\Banks\BanksIndex;
use App\Livewire\Banks\CreateBank; // <--- Verifique se o 'use' está correto
use App\Livewire\Banks\EditBank;

// Importar componentes Livewire de produtos
use App\Livewire\Products\ProductsIndex;
use App\Livewire\Products\CreateProduct;
use App\Livewire\Products\EditProduct;
use App\Livewire\Products\CreateKit;
use App\Livewire\Products\EditKit;
use App\Livewire\Products\UploadProducts;
use App\Livewire\Products\ShowProduct;

// Importar componentes Livewire de clientes
use App\Livewire\Clients\ClientsIndex;
use App\Livewire\Clients\CreateClient;
use App\Livewire\Clients\EditClient;
use App\Livewire\Clients\ClientResumo;
use App\Livewire\Clients\ClientFaturas;
use App\Livewire\Clients\ClientTransferencias;
use App\Livewire\Clients\ClientDashboard;

// Importar componentes Livewire de vendas
use App\Livewire\Sales\SalesIndex;
use App\Livewire\Sales\CreateSale;
use App\Livewire\Sales\ShowSale;
use App\Livewire\Sales\EditSale;
use App\Livewire\Sales\AddProducts;
use App\Livewire\Sales\EditPrices;
use App\Livewire\Sales\AddPayments;
use App\Livewire\Sales\EditPayments;

// Importar componentes Livewire de categorias
use App\Livewire\Categories\CategoriesIndex;
use App\Livewire\Categories\CreateCategory;
use App\Livewire\Categories\EditCategory;

// Importar componentes Livewire de cashbook
use App\Livewire\Cashbook\CashbookIndex;
use App\Livewire\Cashbook\CreateCashbook;
use App\Livewire\Cashbook\EditCashbook;
use App\Livewire\Cashbook\UploadCashbook;

// Importar componentes Livewire de invoices
use App\Livewire\Invoices\InvoicesIndex;
use App\Livewire\Invoices\CreateInvoice;
use App\Livewire\Invoices\EditInvoice;
use App\Livewire\Invoices\CopyInvoice;
use App\Livewire\Invoices\UploadInvoice;

// Importar componentes Livewire de dashboard
use App\Livewire\Dashboard\DashboardIndex;
use App\Livewire\Dashboard\DashboardCashbook;
use App\Livewire\Dashboard\DashboardProducts;
use App\Livewire\Dashboard\DashboardSales;

// Importar todos os controladores que você usa
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ChangePasswordController;
use App\Http\Controllers\ClientController;

use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InfoUserController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\ResetController;
use App\Http\Controllers\SessionsController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SaleController;
use App\Models\Client;
use App\Http\Controllers\UploadInvoiceController;
use App\Http\Controllers\CashbookController;
// Importar componentes Livewire de cofrinhos
use App\Livewire\Cofrinhos\CofrinhoIndex;
use App\Livewire\Cofrinhos\CreateCofrinho;
use App\Livewire\Cofrinhos\EditCofrinho;
use App\Livewire\Cofrinhos\ShowCofrinho;

use App\Http\Controllers\UploadCashbookController;
use App\Http\Controllers\ClienteResumoController;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::redirect('/dashboard', '/dashboard/main')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// --- Rotas Autenticadas ---
Route::middleware(['auth'])->group(function () {
    
    // --- Rotas de Dashboards (Livewire) ---
    Route::get('/dashboard/main', DashboardIndex::class)->name('dashboard.index');
    Route::get('/dashboard/cashbook', DashboardCashbook::class)->name('dashboard.cashbook');
    Route::get('/dashboard/products', DashboardProducts::class)->name('dashboard.products');
    Route::get('/dashboard/sales', DashboardSales::class)->name('dashboard.sales');
    
     // Suas rotas para Livewire
    Route::get('/banks', BanksIndex::class)->name('banks.index');
    Route::get('/banks/create', CreateBank::class)->name('banks.create'); // <--- A rota aponta para a classe
    Route::get('/banks/{bank}/edit', EditBank::class)->name('banks.edit');
    
    
    // --- Rotas de Invoices/Faturas (Livewire) ---
    Route::get('/invoices/{bankId?}', InvoicesIndex::class)->name('invoices.index');
    Route::get('/invoices/{bankId}/create', CreateInvoice::class)->name('invoices.create');
    Route::get('/invoices/{invoiceId}/edit', EditInvoice::class)->name('invoices.edit');
    Route::get('/invoices/{invoiceId}/copy', CopyInvoice::class)->name('invoices.copy');
    Route::get('/invoices/{bankId}/upload', UploadInvoice::class)->name('invoices.upload');
    Route::post('/invoices/{id}/toggle-dividida', [ClienteResumoController::class, 'toggleDividida'])->name('invoices.toggleDividida');

    // --- Rotas de Clientes (Livewire) ---
    Route::get('/clients', ClientsIndex::class)->name('clients.index');
    Route::get('/clients/create', CreateClient::class)->name('clients.create');
    Route::get('/clients/{client}/edit', EditClient::class)->name('clients.edit');
    
    // --- Rotas de Resumo Financeiro do Cliente (Livewire) ---
    Route::get('/clients/{cliente}/dashboard', ClientDashboard::class)->name('clients.dashboard');
    Route::get('/clients/{cliente}/resumo', ClientResumo::class)->name('clients.resumo');
    Route::get('/clients/{cliente}/faturas', ClientFaturas::class)->name('clients.faturas');
    Route::get('/clients/{cliente}/transferencias/{tipo?}', ClientTransferencias::class)->name('clients.transferencias');
    
    // Manter algumas rotas específicas se necessário
    Route::get('/client/{id}/data', [SaleController::class, 'getClientData']);
    Route::get('/clients/{id}/history', [ClientController::class, 'getPurchaseHistory'])->name('clients.history');
    Route::post('/invoices/{id}/toggle-dividida', [ClienteResumoController::class, 'toggleDividida'])->name('invoices.toggleDividida');

    // --- Rotas de Produtos (Livewire) ---
    Route::get('/products', ProductsIndex::class)->name('products.index');
    Route::get('/products/create', CreateProduct::class)->name('products.create');
    Route::get('/products/{product}/edit', EditProduct::class)->name('products.edit');
    Route::get('/products/show/{productCode}', ShowProduct::class)->name('products.show');
    Route::get('/products/kit/create', CreateKit::class)->name('products.kit.create');
    Route::get('/products/kit/{kit}/edit', EditKit::class)->name('products.kit.edit');
    Route::get('/products/upload', UploadProducts::class)->name('products.upload');
    
    // Manter rotas que ainda usam controller para funcionalidades específicas
    Route::get('/products/search', [ProductController::class, 'search'])->name('products.search');
    Route::post('/products/update-all', [ProductController::class, 'updateAll'])->name('products.updateAll');
    Route::post('/update-stock/{productId}', [SaleController::class, 'updateStock']);

    // --- Rotas de Vendas (Livewire) ---
    Route::get('/sales', SalesIndex::class)->name('sales.index');
    Route::get('/sales/create', CreateSale::class)->name('sales.create');
    Route::get('/sales/{id}', ShowSale::class)->name('sales.show');
    Route::get('/sales/{id}/edit', EditSale::class)->name('sales.edit');
    Route::get('/sales/{sale}/add-products', AddProducts::class)->name('sales.add-products');
    Route::get('/sales/{saleId}/edit-prices', EditPrices::class)->name('sales.edit-prices');
    Route::get('/sales/{saleId}/add-payments', AddPayments::class)->name('sales.add-payments');
    Route::get('/sales/{saleId}/edit-payments', EditPayments::class)->name('sales.edit-payments');
    
    // Manter algumas rotas específicas do controller antigo se necessário
    Route::get('/sales/{id}/export', [SaleController::class, 'export'])->name('sales.export');
    Route::post('/update-stock/{productId}', [SaleController::class, 'updateStock']);
    Route::post('/parcelas/{id}/pagar', [SaleController::class, 'pagarParcela'])->name('parcelas.pagar');

    // --- Rotas de Categorias (Livewire) ---
    Route::get('/categories', CategoriesIndex::class)->name('categories.index');
    Route::get('/categories/create', CreateCategory::class)->name('categories.create');
    Route::get('/categories/{category}/edit', EditCategory::class)->name('categories.edit');
    
    // --- Rotas de Livro Caixa (Livewire) ---
    Route::get('/cashbook', CashbookIndex::class)->name('cashbook.index');
    Route::get('/cashbook/create', CreateCashbook::class)->name('cashbook.create');
    Route::get('/cashbook/{cashbook}/edit', EditCashbook::class)->name('cashbook.edit');
    Route::get('/cashbook/upload', UploadCashbook::class)->name('cashbook.upload');
    
    // --- Rotas de Cofrinhos (Livewire) ---
    Route::get('/cofrinhos', CofrinhoIndex::class)->name('cofrinhos.index');
    Route::get('/cofrinhos/create', CreateCofrinho::class)->name('cofrinhos.create');
    Route::get('/cofrinhos/{cofrinho}/edit', EditCofrinho::class)->name('cofrinhos.edit');
    Route::get('/cofrinhos/{cofrinho}', ShowCofrinho::class)->name('cofrinhos.show');
    
    // --- Rotas de Settings (Livewire Volt) ---
    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
    
    // --- Outras Rotas ---
    Route::redirect('settings', 'settings/profile');
    Route::get('tables', function () {
        return view('tables');
    })->name('tables');
    Route::get('user-management', [InfoUserController::class, 'index'])->name('user-management');
    Route::get('/user-management/create', [InfoUserController::class, 'create'])->name('user.create');
    Route::post('/user-management/create', [InfoUserController::class, 'store'])->name('user.store');
    Route::delete('/user-management/{id}', [InfoUserController::class, 'destroy'])->name('user.delete');
    
});
// Rota de login fora do grupo de autenticação
Route::get('/login', function () {
    return view('session/login-session');
})->name('login');


require __DIR__.'/auth.php';