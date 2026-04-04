<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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
use App\Livewire\Products\BarcodeScanner;

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
use App\Http\Controllers\ClientPortalController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InfoUserController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\ResetController;
use App\Http\Controllers\SessionsController;
use App\Http\Controllers\TeamController;
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

// Importar componentes Livewire de consórcios
use App\Livewire\Consortiums\ConsortiumsIndex;
use App\Livewire\Consortiums\CreateConsortium;
use App\Livewire\Consortiums\EditConsortium;
use App\Livewire\Consortiums\ShowConsortium;
use App\Livewire\Consortiums\ConsortiumDraw;
use App\Livewire\Consortiums\AddContemplationProducts;
use App\Livewire\Consortiums\AddParticipants;

// Importar componentes Livewire de metas e objetivos
use App\Livewire\Goals\GoalsDashboard;
use App\Livewire\Goals\GoalsBoard;
use App\Livewire\Goals\CreateGoal;
use App\Livewire\Goals\EditGoal;

// Importar componentes Livewire de hábitos diários
use App\Livewire\DailyHabits\DailyHabitsDashboard;
use App\Livewire\DailyHabits\CreateHabit;
use App\Livewire\DailyHabits\EditHabit;

// Importar componentes Livewire de conquistas
use App\Livewire\Achievements\AchievementsPage;

use App\Http\Controllers\UploadCashbookController;
use App\Http\Controllers\ClienteResumoController;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/dashboard', DashboardIndex::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('guest:portal')->prefix('portal')->name('portal.')->group(function () {
    Route::get('/login', [ClientPortalController::class, 'showLogin'])->name('login');
    Route::post('/login', [ClientPortalController::class, 'login'])->name('login.post');
    Route::get('/auth/google', [ClientPortalController::class, 'redirectToGoogle'])->name('google.redirect');
    Route::get('/auth/google/callback', [ClientPortalController::class, 'handleGoogleCallback'])->name('google.callback');
    Route::get('/esqueci-a-senha', [ClientPortalController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/esqueci-a-senha', [ClientPortalController::class, 'sendResetLink'])->name('password.email');
    Route::get('/resetar-senha/{token}', [ClientPortalController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/resetar-senha', [ClientPortalController::class, 'resetPassword'])->name('password.update');
});

Route::middleware('portal.auth')->prefix('portal')->name('portal.')->group(function () {
    Route::get('/', [ClientPortalController::class, 'dashboard'])->name('dashboard');
    Route::get('/vendas', [ClientPortalController::class, 'sales'])->name('sales');
    Route::get('/produtos', [ClientPortalController::class, 'products'])->name('products');
    Route::get('/orcamentos', [ClientPortalController::class, 'quotes'])->name('quotes');
    Route::get('/orcamentos/novo', [ClientPortalController::class, 'createQuote'])->name('quotes.create');
    Route::post('/orcamentos', [ClientPortalController::class, 'storeQuote'])->name('quotes.store');
    Route::get('/orcamentos/{quote}', [ClientPortalController::class, 'showQuote'])->name('quotes.show');
    Route::patch('/orcamentos/{quote}/resposta', [ClientPortalController::class, 'respondToQuote'])->name('quotes.respond');
    Route::get('/perfil', [ClientPortalController::class, 'profile'])->name('profile');
    Route::patch('/perfil', [ClientPortalController::class, 'updateProfile'])->name('profile.update');
    Route::post('/logout', [ClientPortalController::class, 'logout'])->name('logout');
});

// --- Rotas Autenticadas ---
Route::middleware(['auth'])->group(function () {

    // --- Rotas de Dashboards (Livewire) ---
    Route::get('/dashboard/main', DashboardIndex::class)->name('dashboard.index');
    Route::get('/dashboard/cashbook', DashboardCashbook::class)->name('dashboard.cashbook');
    Route::get('/dashboard/products', DashboardProducts::class)->name('dashboard.products');
    Route::get('/dashboard/sales', DashboardSales::class)->name('dashboard.sales');
    Route::get('/dashboard/banks', \App\Livewire\Dashboard\DashboardBanks::class)->name('dashboard.banks');
    // Dashboard de Clientes
    Route::get('/dashboard/clients', \App\Livewire\Dashboard\DashboardClientes::class)->name('dashboard.clients');

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
    Route::get('/clients/{client}/portal/access', [ClientPortalController::class, 'accessPage'])->name('clients.portal.access');
    Route::post('/clients/{client}/portal/send-access', [ClientPortalController::class, 'sendAccess'])->name('clients.portal.send-access');
    Route::post('/clients/{client}/portal/reset-access-link', [ClientPortalController::class, 'generateResetAccessLink'])->name('clients.portal.reset-access-link');
    Route::delete('/clients/{client}/portal/revoke-access', [ClientPortalController::class, 'revokeAccess'])->name('clients.portal.revoke-access');
    Route::get('/clients/{client}/portal/quotes', [ClientPortalController::class, 'adminClientQuotes'])->name('clients.portal.quotes');
    Route::patch('/clients/portal/quotes/{quote}', [ClientPortalController::class, 'adminRespondQuote'])->name('clients.portal.quotes.update');

    // Manter algumas rotas específicas se necessário
    // Route::get('/client/{id}/data', [SaleController::class, 'getClientData']);
    // Route::get('/clients/{id}/history', [ClientController::class, 'getPurchaseHistory'])->name('clients.history');
    Route::post('/invoices/{id}/toggle-dividida', [ClienteResumoController::class, 'toggleDividida'])->name('invoices.toggleDividida');

    // --- Rotas de Produtos (Livewire) ---
    Route::get('/products', ProductsIndex::class)->name('products.index');
    Route::get('/products/create', CreateProduct::class)->name('products.create');
    Route::get('/products/{product}/edit', EditProduct::class)->name('products.edit');
    Route::get('/products/show/{productCode}', ShowProduct::class)->name('products.show');
    Route::get('/products/kit/create', CreateKit::class)->name('products.kit.create');
    Route::get('/products/kit/{kit}/edit', EditKit::class)->name('products.kit.edit');
    Route::get('/products/upload', UploadProducts::class)->name('products.upload');
    Route::get('/products/barcode-scanner', BarcodeScanner::class)->name('products.barcode-scanner');

    // Manter rotas que ainda usam controller para funcionalidades específicas
    // Route::get('/products/search', [ProductController::class, 'search'])->name('products.search');
    // Route::post('/products/update-all', [ProductController::class, 'updateAll'])->name('products.updateAll');
    // Route::post('/update-stock/{productId}', [SaleController::class, 'updateStock']);

    // --- Rotas de Vendas (Livewire) ---
    Route::get('/sales', SalesIndex::class)->name('sales.index');
    Route::get('/sales/create', CreateSale::class)->name('sales.create');
    Route::get('/sales/{id}', ShowSale::class)->name('sales.show');
    Route::get('/sales/{id}/edit', EditSale::class)->name('sales.edit');
    Route::get('/sales/{sale}/add-products', AddProducts::class)->name('sales.add-products');
    Route::get('/sales/{saleId}/edit-prices', EditPrices::class)->name('sales.edit-prices');
    Route::get('/sales/{saleId}/add-payments', AddPayments::class)->name('sales.add-payments');
    Route::get('/sales/{saleId}/edit-payments', EditPayments::class)->name('sales.edit-payments');

    // Export de relatórios (CSV/XLSX)
    Route::get('/reports/vendas/export', [\App\Http\Controllers\ReportExportController::class, 'exportVendas'])->name('reports.vendas.export');
    Route::get('/reports/dashboard-cashbook/export', [\App\Http\Controllers\ReportExportController::class, 'exportDashboardCashbook'])->name('reports.dashboard-cashbook.export');
    Route::get('/reports/dashboard-banks/export', [\App\Http\Controllers\ReportExportController::class, 'exportDashboardBanks'])->name('reports.dashboard-banks.export');
    Route::get('/reports/dashboard-products/export', [\App\Http\Controllers\ReportExportController::class, 'exportDashboardProducts'])->name('reports.dashboard-products.export');

    // Manter algumas rotas específicas do controller antigo se necessário
    // Route::get('/sales/{id}/export', [SaleController::class, 'export'])->name('sales.export');
    // Route::post('/update-stock/{productId}', [SaleController::class, 'updateStock']);
    // Route::post('/parcelas/{id}/pagar', [SaleController::class, 'pagarParcela'])->name('parcelas.pagar');

    // --- Rotas de Categorias (Livewire) ---
    Route::get('/categories', CategoriesIndex::class)->name('categories.index');
    Route::get('/categories/create', CreateCategory::class)->name('categories.create');
    Route::get('/categories/{category}/edit', EditCategory::class)->name('categories.edit');

    // --- Rotas de Livro Caixa (Livewire) ---
    Route::get('/cashbook', CashbookIndex::class)->name('cashbook.index');
    Route::get('/cashbook/create', CreateCashbook::class)->name('cashbook.create');
    Route::get('/cashbook/{cashbook}/edit', EditCashbook::class)->name('cashbook.edit');
    Route::get('/cashbook/upload', UploadCashbook::class)->name('cashbook.upload');
    Route::get('/cashbook/upload2', \App\Livewire\Cashbook\UploadCashbookMinimal::class)->name('cashbook.upload.minimal');
    Route::get('/cashbook/upload3', \App\Livewire\Cashbook\UploadCashbook2::class)->name('cashbook.upload2');

    // --- Rotas de Cofrinhos (Livewire) ---
    Route::get('/cofrinhos', CofrinhoIndex::class)->name('cofrinhos.index');
    Route::get('/cofrinhos/create', CreateCofrinho::class)->name('cofrinhos.create');
    Route::get('/cofrinhos/{cofrinho}/edit', EditCofrinho::class)->name('cofrinhos.edit');
    Route::get('/cofrinhos/{cofrinho}', ShowCofrinho::class)->name('cofrinhos.show');

    // --- Rotas de Consórcios (Livewire) ---
    Route::get('/consortiums', ConsortiumsIndex::class)->name('consortiums.index');
    Route::get('/consortiums/create', CreateConsortium::class)->name('consortiums.create');
    Route::get('/consortiums/{consortium}/edit', EditConsortium::class)->name('consortiums.edit');
    Route::get('/consortiums/{consortium}', ShowConsortium::class)->name('consortiums.show');
    Route::get('/consortiums/{consortium}/draw', ConsortiumDraw::class)->name('consortiums.draw');
    Route::get('/consortiums/{consortium}/add-participants', AddParticipants::class)->name('consortiums.add-participants');
    Route::get('/consortiums/contemplation/{contemplation}/products', AddContemplationProducts::class)->name('consortiums.contemplation.products');
    Route::get('/clients/{client}/consortiums', \App\Livewire\Consortiums\ShowClientConsortiums::class)->name('clients.consortiums');

    // --- Rotas de Metas e Objetivos (Livewire) ---
    Route::get('/goals', GoalsDashboard::class)->name('goals.dashboard');
    Route::get('/goals/board/{boardId}', GoalsBoard::class)->name('goals.board');
    Route::get('/goals/create/{boardId}', CreateGoal::class)->name('goals.create');
    Route::get('/goals/edit/{goalId}', EditGoal::class)->name('goals.edit');

    // --- Rotas de Hábitos Diários (Livewire) ---
    Route::get('/daily-habits', DailyHabitsDashboard::class)->name('daily-habits.dashboard');
    Route::get('/daily-habits/create', CreateHabit::class)->name('daily-habits.create');
    Route::get('/daily-habits/edit/{habitId}', EditHabit::class)->name('daily-habits.edit');
    // --- Rotas de Achievements/Conquistas (Livewire) ---
    Route::get('/achievements', AchievementsPage::class)->name('achievements.index');
    // --- Rotas de Conquistas (Livewire) ---
    Route::get('/achievements', AchievementsPage::class)->name('achievements.index');

    // --- Preferências de usuário (persistência DB) ---
    Route::post('/settings/preferences/{group}', function(\Illuminate\Http\Request $req, string $group) {
        abort_unless(in_array($group, ['notifications', 'system', 'devices', 'appearance'], true), 422);
        $authUser = Auth::user();
        abort_unless($authUser instanceof \App\Models\User, 403);
        /** @var \App\Models\User $user */
        $user = $authUser;
        $prefs = is_array($user->preferences) ? $user->preferences : [];
        $prefs[$group] = $req->input('data', []);
        $user->update(['preferences' => $prefs]);
        return response()->json(['ok' => true]);
    })->name('settings.preferences.save');

    Route::prefix('settings/team')->name('settings.team.')->group(function () {
        Route::post('/', [TeamController::class, 'store'])->name('store');
        Route::patch('/', [TeamController::class, 'update'])->name('update');
        Route::post('/invite', [TeamController::class, 'invite'])->name('invite');
        Route::patch('/members/{member}', [TeamController::class, 'updateMember'])->name('members.update');
        Route::delete('/members/{member}', [TeamController::class, 'removeMember'])->name('members.destroy');
        Route::post('/invitations/{invitation}/accept', [TeamController::class, 'acceptInvitation'])->name('invitations.accept');
        Route::delete('/invitations/{invitation}', [TeamController::class, 'cancelInvitation'])->name('invitations.destroy');
        Route::delete('/leave', [TeamController::class, 'leave'])->name('leave');
        Route::post('/transfer', [TeamController::class, 'transferRecords'])->name('transfer');
    });

    // --- Rotas de Settings (Livewire Volt) ---
    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/team', 'settings.team')->name('settings.team');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
    Volt::route('settings/notifications', 'settings.notifications')->name('settings.notifications');
    Volt::route('settings/security', 'settings.security')->name('settings.security');
    Volt::route('settings/system', 'settings.system')->name('settings.system');
    Volt::route('settings/devices', 'settings.devices')->name('settings.devices');
    Volt::route('settings/plan', 'settings.plan')->name('settings.plan');
    Volt::route('settings/connections', 'settings.connections')->name('settings.connections');

    // --- Outras Rotas ---
    Route::redirect('settings', 'settings/profile');
    Route::get('tables', function () {
        return view('tables');
    })->name('tables');
    // Route::get('user-management', [InfoUserController::class, 'index'])->name('user-management');
    // Route::get('/user-management/create', [InfoUserController::class, 'create'])->name('user.create');
    // Route::post('/user-management/create', [InfoUserController::class, 'store'])->name('user.store');
    // Route::delete('/user-management/{id}', [InfoUserController::class, 'destroy'])->name('user.delete');

});
// Rota de login fora do grupo de autenticação
Route::get('/login', function () {
    return view('session/login-session');
})->name('login');

// ==================== MERCADO LIVRE ROUTES ====================
use App\Http\Controllers\MercadoLivre\AuthController as MercadoLivreAuthController;
use App\Http\Controllers\MercadoLivre\WebhookController;
use App\Http\Controllers\MercadoLivre\ProductController as MercadoLivreProductController;
use App\Livewire\MercadoLivre\Settings;
use App\Livewire\MercadoLivre\ProductIntegration;
use App\Livewire\MercadoLivre\PublishProduct;
use App\Livewire\MercadoLivre\EditPublication;

Route::prefix('mercadolivre')->middleware(['auth'])->name('mercadolivre.')->group(function () {
    // Rota de DEBUG
    Route::get('/debug', function () {
        Log::info('DEBUG: Testando componente ProductIntegration');
        
        try {
            $component = new \App\Livewire\MercadoLivre\ProductIntegration();
            $component->mount();
            $result = $component->render();
            $data = $result->getData();
            
            return response()->json([
                'success' => true,
                'user_id' => Auth::id(),
                'user_name' => Auth::user()->name,
                'products_count' => $data['products']->count(),
                'total_products' => $data['products']->total(),
                'categories_count' => $data['categories']->count(),
                'isConnected' => $component->isConnected,
                'products' => $data['products']->map(function($p) {
                    return [
                        'id' => $p->id,
                        'name' => $p->name,
                        'stock' => $p->stock_quantity,
                        'ready' => $p->isReadyForMercadoLivre()['ready']
                    ];
                })
            ]);
        } catch (\Throwable $e) {
            Log::error('DEBUG: Erro ao testar componente', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'file' => $e->getFile() . ':' . $e->getLine()
            ], 500);
        }
    })->name('debug');
    
    // Settings Page (Livewire Component)
    Route::get('/settings', function () {
        return view('layouts.settings-wrapper');
    })->name('settings');
    
    // Product Integration Page (Livewire Component)
    Route::get('/products', function () {
        Log::info('ROTA ACESSADA: /mercadolivre/products', [
            'user_id' => Auth::id(),
            'user_name' => Auth::user()?->name,
            'timestamp' => now()->toDateTimeString()
        ]);
        return view('layouts.product-integration-wrapper');
    })->name('products');
    
    // Publish Product Page - Nova publicação (sem produto pré-selecionado)
    Route::get('/products/publish/create', PublishProduct::class)
        ->name('products.publish.create');
    
    // Publish Product Page - Com produto pré-selecionado (do card)
    Route::get('/products/{product}/publish', PublishProduct::class)
        ->name('products.publish');
    
    // Publications Management
    Route::get('/publications', \App\Livewire\MercadoLivre\PublicationsList::class)
        ->name('publications');
    
    Route::get('/publications/{publication}', \App\Livewire\MercadoLivre\ShowPublication::class)
        ->name('publications.show');
    
    Route::get('/publications/{publication}/edit', EditPublication::class)
        ->name('publications.edit');
    
    // Orders Management
    Route::get('/orders', \App\Livewire\MercadoLivre\OrdersManager::class)
        ->name('orders');

    Route::get('/orders/{orderId}', \App\Livewire\MercadoLivre\ShowOrder::class)
        ->name('orders.show');

    // Questions (Perguntas de compradores)
    Route::get('/questions', \App\Livewire\MercadoLivre\Questions::class)
        ->name('questions');

    // Messages (Mensagens pós-venda)
    Route::get('/messages', \App\Livewire\MercadoLivre\Messages::class)
        ->name('messages');

    // Reputation (Reputação & Métricas)
    Route::get('/reputation', \App\Livewire\MercadoLivre\Reputation::class)
        ->name('reputation');

    // Mediations (Devoluções & Disputas)
    Route::get('/mediations', \App\Livewire\MercadoLivre\Mediations::class)
        ->name('mediations');

    // Promotions (Promoções)
    Route::get('/promotions', \App\Livewire\MercadoLivre\Promotions::class)
        ->name('promotions');

    // OAuth 2.0 Authentication
    Route::get('/auth/redirect', [MercadoLivreAuthController::class, 'redirect'])
        ->name('auth.redirect');
    
    Route::get('/auth/callback', [MercadoLivreAuthController::class, 'callback'])
        ->name('auth.callback')
        ->withoutMiddleware(['auth']); // Callback pode vir sem sessão ativa
    
    Route::post('/auth/disconnect', [MercadoLivreAuthController::class, 'disconnect'])
        ->name('auth.disconnect');
    
    // AJAX endpoints
    Route::get('/auth/status', [MercadoLivreAuthController::class, 'status'])
        ->name('auth.status');
    
    Route::post('/auth/test', [MercadoLivreAuthController::class, 'testConnection'])
        ->name('auth.test');
    
    // Product REST API endpoints
    Route::get('/api/products', [MercadoLivreProductController::class, 'index'])
        ->name('api.products.index');
    
    Route::post('/api/products/{id}/publish', [MercadoLivreProductController::class, 'publish'])
        ->name('api.products.publish');
    
    Route::post('/api/products/{id}/sync', [MercadoLivreProductController::class, 'sync'])
        ->name('api.products.sync');
    
    Route::post('/api/products/{id}/pause', [MercadoLivreProductController::class, 'pause'])
        ->name('api.products.pause');
    
    Route::post('/api/products/{id}/activate', [MercadoLivreProductController::class, 'activate'])
        ->name('api.products.activate');
    
    Route::delete('/api/products/{id}', [MercadoLivreProductController::class, 'delete'])
        ->name('api.products.delete');
    
    Route::post('/api/products/{id}/update-stock', [MercadoLivreProductController::class, 'updateStock'])
        ->name('api.products.update-stock');
    
    Route::post('/api/products/{id}/update-price', [MercadoLivreProductController::class, 'updatePrice'])
        ->name('api.products.update-price');
});

// Webhook endpoint (sem middleware auth - ML precisa acessar externamente)
Route::post('/mercadolivre/webhooks', [WebhookController::class, 'handle'])
    ->name('mercadolivre.webhooks.handle')
    ->withoutMiddleware(['auth', 'web']);

Route::get('/mercadolivre/webhooks/test', [WebhookController::class, 'test'])
    ->name('mercadolivre.webhooks.test');
// ==================== END MERCADO LIVRE ROUTES ====================

// ==================== SHOPEE ROUTES ====================
use App\Http\Controllers\Shopee\AuthController as ShopeeAuthController;
use App\Http\Controllers\Shopee\WebhookController as ShopeeWebhookController;
use App\Livewire\Shopee\Settings as ShopeeSettings;
use App\Livewire\Shopee\PublishProduct as ShopeePublishProduct;
use App\Livewire\Shopee\PublicationsList as ShopeePublicationsList;

Route::prefix('shopee')->middleware(['auth'])->name('shopee.')->group(function () {
    // Configurações da integração Shopee
    Route::get('/settings', ShopeeSettings::class)->name('settings');

    // Publicações
    Route::get('/publications', ShopeePublicationsList::class)->name('publications');

    // Publicar produto
    Route::get('/products/publish/create', ShopeePublishProduct::class)
        ->name('products.publish.create');
    Route::get('/products/{product}/publish', ShopeePublishProduct::class)
        ->name('products.publish');

    // OAuth 2.0
    Route::get('/auth/connect', [ShopeeAuthController::class, 'connect'])->name('auth.connect');
    Route::get('/auth/callback', [ShopeeAuthController::class, 'callback'])
        ->name('auth.callback')
        ->withoutMiddleware(['auth']);
    Route::post('/auth/disconnect', [ShopeeAuthController::class, 'disconnect'])->name('auth.disconnect');
});

// Shopee Webhook — público, sem middleware (Shopee chama externamente)
Route::post('/shopee/webhook', [ShopeeWebhookController::class, 'handle'])
    ->name('shopee.webhook.handle')
    ->withoutMiddleware(['auth', 'web']);

Route::get('/shopee/webhook', [ShopeeWebhookController::class, 'verify'])
    ->name('shopee.webhook.verify')
    ->withoutMiddleware(['auth', 'web']);
// ==================== END SHOPEE ROUTES ====================


// ==================== SUBSCRIPTION ROUTES ====================
use App\Http\Controllers\SubscriptionController;

Route::middleware(['auth'])->prefix('subscription')->name('subscription.')->group(function () {
    Route::get('/plans',    [SubscriptionController::class, 'plans'])->name('plans');
    Route::get('/checkout/{plan}', [SubscriptionController::class, 'checkout'])->name('checkout');
    Route::post('/checkout',       [SubscriptionController::class, 'processCheckout'])->name('checkout.process');
    Route::get('/success',         [SubscriptionController::class, 'success'])->name('success');
    Route::post('/cancel',         [SubscriptionController::class, 'cancel'])->name('cancel');
});

// ==================== ADMIN ROUTES ====================
use App\Http\Controllers\Admin\PlansController as AdminPlansController;

Route::middleware(['auth'])->get('/access-center', [AdminPlansController::class, 'users'])->name('access.center');

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', fn () => redirect()->route('admin.plans.index'))->name('dashboard');

    // Plans CRUD
    Route::resource('plans', AdminPlansController::class)->except('show');
    Route::patch('plans/{plan}/toggle', [AdminPlansController::class, 'toggleActive'])->name('plans.toggle');
    Route::get('plans-users', [AdminPlansController::class, 'users'])->name('plans.users');
    Route::post('plans/grant', [AdminPlansController::class, 'grantAccess'])->name('plans.grant');
    Route::post('users/toggle-admin', [AdminPlansController::class, 'toggleAdmin'])->name('users.toggle-admin');

    // Subscriptions management
    Route::get('subscriptions', [AdminPlansController::class, 'subscriptions'])->name('subscriptions.index');
    Route::post('subscriptions/{subscription}/revoke', [AdminPlansController::class, 'revokeAccess'])->name('subscriptions.revoke');
    Route::post('subscriptions/{subscription}/extend', [AdminPlansController::class, 'extendSubscription'])->name('subscriptions.extend');
});

// ==================== PAYMENT WEBHOOKS (public, no auth) ====================
Route::post('/webhooks/stripe',      [SubscriptionController::class, 'webhookStripe'])
    ->name('webhooks.stripe')->withoutMiddleware(['auth', 'web']);
Route::post('/webhooks/mercadopago', [SubscriptionController::class, 'webhookMercadoPago'])
    ->name('webhooks.mercadopago')->withoutMiddleware(['auth', 'web']);
Route::post('/webhooks/pagseguro',   [SubscriptionController::class, 'webhookPagSeguro'])
    ->name('webhooks.pagseguro')->withoutMiddleware(['auth', 'web']);
// ==================== END SUBSCRIPTION/ADMIN ROUTES ====================

require __DIR__.'/auth.php';
