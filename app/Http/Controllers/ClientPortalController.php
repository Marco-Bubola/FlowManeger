<?php

namespace App\Http\Controllers;

use App\Mail\ClientPortalAccessMail;
use App\Models\Client;
use App\Models\ClientQuoteRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Notifications\PortalQuoteReceived;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class ClientPortalController extends Controller
{
    // ─── Entrada pública do portal ─────────────────────────────────────────────

    /**
     * Rota pública /portal — sem autenticação.
     * Autenticados: vão ao dashboard. Convidados: catálogo (cookie) ou login.
     */
    public function portalHome(Request $request)
    {
        if (Auth::guard('portal')->check()) {
            /** @var \App\Models\Client $client */
            $client = Auth::guard('portal')->user();
            return redirect()->route($client->needsPortalOnboarding() ? 'portal.profile' : 'portal.dashboard');
        }

        $ownerId = $request->cookie('portal_store');
        if ($ownerId) {
            return redirect()->route('portal.catalog', ['userId' => $ownerId]);
        }

        return redirect()->route('portal.login');
    }

    // ─── Login / Logout ────────────────────────────────────────────────────────

    public function showLogin(Request $request)
    {
        if (Auth::guard('portal')->check()) {
            /** @var \App\Models\Client $client */
            $client = Auth::guard('portal')->user();

            return redirect()->route($client->needsPortalOnboarding() ? 'portal.profile' : 'portal.dashboard');
        }

        // Salva intenção de redirecionar ao carrinho após login
        if ($request->query('redirect') === 'cart') {
            $request->session()->put('portal_intended', 'cart');
        }

        return view('portal.login');
    }

    public function redirectToGoogle(Request $request)
    {
        config(['services.google.redirect' => config('services.google_portal.redirect')]);

        if (Auth::guard('portal')->check() && $request->boolean('connect')) {
            $request->session()->put('portal_google_connect_client_id', Auth::guard('portal')->id());
        }

        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            config(['services.google.redirect' => config('services.google_portal.redirect')]);

            $googleUser = Socialite::driver('google')->user();

            $linkClientId = request()->session()->pull('portal_google_connect_client_id');

            if ($linkClientId) {
                $client = Client::query()->find($linkClientId);

                if (! $client) {
                    return redirect()->route('portal.profile')
                        ->with('error', 'Nao foi possivel localizar o cliente para conectar o Google.');
                }

                $conflictingClient = Client::query()
                    ->where('google_id', $googleUser->getId())
                    ->whereKeyNot($client->id)
                    ->first();

                if ($conflictingClient) {
                    return redirect()->route('portal.profile')
                        ->with('error', 'Esta conta Google ja esta conectada a outro cliente.');
                }

                $client->forceFill([
                    'google_id' => $googleUser->getId(),
                    'google_avatar' => $googleUser->getAvatar(),
                    'email' => $client->email ?: $googleUser->getEmail(),
                ])->save();

                return redirect()->route('portal.profile')
                    ->with('success', 'Conta Google conectada com sucesso ao portal.');
            }

            $client = Client::query()
                ->where('google_id', $googleUser->getId())
                ->where('portal_active', true)
                ->first();

            if (! $client) {
                $matchingClients = Client::query()
                    ->where('email', $googleUser->getEmail())
                    ->where('portal_active', true)
                    ->get();

                if ($matchingClients->count() > 1) {
                    return redirect()->route('portal.login')
                        ->with('error', 'Este e-mail do Google esta vinculado a mais de um portal. Entre com o login exclusivo enviado pelo vendedor.');
                }

                $client = $matchingClients->first();
            }

            if (! $client) {
                return redirect()->route('portal.login')
                    ->with('error', 'Nao encontramos um cliente vinculado a este e-mail do Google.');
            }

            if (! $client->portal_active) {
                return redirect()->route('portal.login')
                    ->with('error', 'Seu acesso ao portal esta desativado. Fale com o vendedor.');
            }

            if (blank($client->email) || strtolower((string) $client->email) !== strtolower((string) $googleUser->getEmail())) {
                return redirect()->route('portal.login')
                    ->with('error', 'O e-mail do Google nao corresponde ao e-mail cadastrado do cliente.');
            }

            $client->update([
                'google_id' => $googleUser->getId(),
                'google_avatar' => $googleUser->getAvatar(),
                'portal_last_login_at' => now(),
            ]);

            Auth::guard('portal')->login($client, true);
            request()->session()->regenerate();

            // Se veio do carrinho, retorna ao carrinho após login
            $intended = request()->session()->pull('portal_intended');
            if ($intended === 'cart') {
                return redirect()->route('portal.quotes.create');
            }

            return redirect()->route($client->needsPortalOnboarding() ? 'portal.profile' : 'portal.dashboard');
        } catch (\Throwable $exception) {
            return redirect()->route('portal.login')
                ->with('error', 'Nao foi possivel entrar com Google no portal.');
        }
    }

    public function showForgotPassword()
    {
        return view('portal.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate([
            'login' => ['required', 'string', 'max:255'],
        ]);

        $client = Client::query()
            ->where('portal_login', $request->string('login')->toString())
            ->where('portal_active', true)
            ->first();

        if (! $client || blank($client->email)) {
            return back()->withErrors([
                'login' => 'Nao encontramos um portal ativo com este login.',
            ])->withInput($request->only('login'));
        }

        $plainToken = $this->issuePortalResetToken($client);
        $client->sendPasswordResetNotification($plainToken);

        return back()->with('success', 'Enviamos o link de redefinicao para o e-mail cadastrado deste portal.');
    }

    public function showResetPassword(Request $request, string $token)
    {
        $client = Client::query()
            ->where('portal_login', $request->string('login')->toString())
            ->first();

        return view('portal.reset-password', [
            'token' => $token,
            'login' => $request->string('login')->toString(),
            'clientName' => $client?->name,
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => ['required'],
            'login' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $client = Client::query()
            ->where('portal_login', $request->string('login')->toString())
            ->where('portal_active', true)
            ->first();

        if (! $client || ! $this->hasValidPortalResetToken($client, $request->string('token')->toString())) {
            return back()
                ->withInput($request->only('login'))
                ->withErrors(['login' => 'Este link de redefinicao e invalido ou expirou.']);
        }

        $client->forceFill([
            'portal_password' => Hash::make($request->string('password')->toString()),
            'portal_force_password_change' => false,
            'portal_token' => null,
            'portal_token_expires_at' => null,
        ])->save();

        if ($client->hasRequiredPortalProfileData() && blank($client->portal_profile_completed_at)) {
            $client->forceFill([
                'portal_profile_completed_at' => now(),
            ])->save();
        }

        event(new PasswordReset($client));

        return redirect()->route('portal.login')->with('success', 'Senha redefinida com sucesso. Faça login no portal.');
    }

    public function login(Request $request)
    {
        $request->validate([
            'login'    => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
        ]);

        // Rate limiting: máx 5 tentativas por IP em 60s
        $throttleKey = 'portal-login:' . $request->ip() . ':' . Str::lower($request->string('login')->toString());
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return back()->withErrors([
                'login' => "Muitas tentativas. Tente novamente em {$seconds} segundos.",
            ])->withInput($request->only('login'));
        }

        $client = Client::where('portal_login', $request->string('login')->toString())
            ->where('portal_active', true)
            ->first();

        if (! $client || ! Hash::check($request->password, $client->portal_password)) {
            RateLimiter::hit($throttleKey, 60);
            return back()->withErrors([
                'login' => 'Login ou senha invalidos.',
            ])->withInput($request->only('login'));
        }

        RateLimiter::clear($throttleKey);
        Auth::guard('portal')->login($client, (bool) $request->remember);
        $client->update(['portal_last_login_at' => now()]);
        $request->session()->regenerate();

        // Salva cookie para catálogo público (30 dias)
        cookie()->queue('portal_store', (string) $client->user_id, 60 * 24 * 30);

        // Se veio do carrinho, retorna ao carrinho após login
        $intended = $request->session()->pull('portal_intended');
        if ($intended === 'cart') {
            return redirect()->route('portal.quotes.create');
        }

        return redirect()->route($client->needsPortalOnboarding() ? 'portal.profile' : 'portal.dashboard');
    }

    public function logout(Request $request)
    {
        Auth::guard('portal')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('portal.login')->with('success', 'Você saiu do portal com segurança.');
    }

    // ─── Portal Pages ───────────────────────────────────────────────────────────

    public function dashboard()
    {
        /** @var \App\Models\Client $client */
        $client = Auth::guard('portal')->user();

        $totalSales    = Sale::where('client_id', $client->id)->count();
        $totalPaid     = Sale::where('client_id', $client->id)->sum('amount_paid');
        $pendingSales  = Sale::where('client_id', $client->id)->where('status', 'pending')->count();
        $recentSales   = Sale::with('saleItems.product')
            ->where('client_id', $client->id)
            ->latest()
            ->limit(5)
            ->get();
        $recentQuotes  = ClientQuoteRequest::where('client_id', $client->id)
            ->latest()
            ->limit(3)
            ->get();

        return view('portal.dashboard', compact(
            'client', 'totalSales', 'totalPaid', 'pendingSales', 'recentSales', 'recentQuotes'
        ));
    }

    public function sales()
    {
        /** @var \App\Models\Client $client */
        $client = Auth::guard('portal')->user();

        $sales = Sale::with(['saleItems.product', 'payments'])
            ->where('client_id', $client->id)
            ->latest()
            ->paginate(12);

        // KPI aggregates (all client sales, not just this page)
        $allSales = Sale::where('client_id', $client->id);
        $kpiTotal      = (clone $allSales)->count();
        $kpiTotalValue = (clone $allSales)->sum('total_price');
        $kpiPending    = (clone $allSales)->whereIn('status', ['pendente', 'pending', 'orcamento'])->count();
        $kpiPaid       = Sale::with('payments')
            ->where('client_id', $client->id)
            ->get()
            ->sum(fn($s) => $s->total_paid);

        return view('portal.sales', compact(
            'client', 'sales',
            'kpiTotal', 'kpiTotalValue', 'kpiPending', 'kpiPaid'
        ));
    }

    public function products()
    {
        /** @var \App\Models\Client $client */
        $client = Auth::guard('portal')->user();

        $search   = request('search', '');
        $category = request('category', '');

        $query = Product::withoutGlobalScope('team_visibility')
            ->where('user_id', $client->user_id)
            ->where('stock_quantity', '>', 0)
            ->whereIn('status', ['active', 'ativo'])
            ->with(['category', 'images']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($category) {
            $query->where('category_id', $category);
        }

        $categories = Category::where('user_id', $client->user_id)->orderBy('name')->get();

        // Com filtro ativo: lista paginada flat
        // Sem filtro: todos os produtos agrupados por categoria
        if ($search || $category) {
            $products = $query->orderBy('name')->paginate(24);
            $grouped  = null;
        } else {
            $products = null;
            $all = $query->orderBy('name')->get();
            // Agrupa preservando ordem: categorias com nome primeiro, sem categoria no fim
            $grouped = $all->sortBy(fn($p) => $p->category?->name ?? 'ZZZZ')
                           ->groupBy(fn($p) => $p->category_id ?? 0);
        }

        return view('portal.products', compact('client', 'products', 'grouped', 'categories', 'search', 'category'));
    }

    public function quotes()
    {
        /** @var \App\Models\Client $client */
        $client = Auth::guard('portal')->user();

        $quotes = ClientQuoteRequest::where('client_id', $client->id)
            ->orderByRaw("CASE status WHEN 'quoted' THEN 0 WHEN 'pending' THEN 1 WHEN 'reviewing' THEN 2 WHEN 'approved' THEN 3 ELSE 4 END")
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('portal.quotes.index', compact('client', 'quotes'));
    }

    public function createQuote()
    {
        /** @var \App\Models\Client $client */
        $client = Auth::guard('portal')->user();

        $products = Product::withoutGlobalScope('team_visibility')
            ->where('user_id', $client->user_id)
            ->where('stock_quantity', '>', 0)
            ->whereIn('status', ['active', 'ativo'])
            ->with('category')
            ->orderBy('name')
            ->get();

        // Determina produtos frequentes deste cliente (top 10 mais pedidos)
        $frequentIds = ClientQuoteRequest::where('client_id', $client->id)
            ->get()
            ->flatMap(fn($q) => collect($q->items ?? [])->pluck('product_id'))
            ->countBy()
            ->sortDesc()
            ->keys()
            ->take(10)
            ->toArray();

        return view('portal.quotes.create', compact('client', 'products', 'frequentIds'));
    }

    // ─── Catálogo Público (sem autenticação) ────────────────────────────────────

    public function publicCatalog(Request $request, ?string $userId = null)
    {
        // Determina qual loja mostrar: parâmetro URL > cookie > query string
        $ownerId = $userId
            ?? $request->query('owner')
            ?? $request->cookie('portal_store');

        if (! $ownerId) {
            return redirect()->route('portal.login')->with('info', 'Acesse o catálogo pelo link compartilhado pelo vendedor.');
        }

        $ownerId   = (int) $ownerId;
        $search    = $request->query('search', '');
        $category  = $request->query('category', '');

        $query = Product::withoutGlobalScope('team_visibility')
            ->where('user_id', $ownerId)
            ->where('stock_quantity', '>', 0)
            ->whereIn('status', ['active', 'ativo']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($category) {
            $query->where('category_id', $category);
        }

        $products   = $query->with('category')->paginate(24);
        $categories = Category::where('user_id', $ownerId)->orderBy('name')->get();

        return view('portal.catalog', compact('products', 'categories', 'search', 'category', 'ownerId'));
    }

    public function storeQuote(Request $request)
    {
        /** @var \App\Models\Client $client */
        $client = Auth::guard('portal')->user();

        $validated = $request->validate([
            'items'               => ['nullable', 'array'],
            'items.*.product_id'  => ['required_with:items', 'integer', 'exists:products,id'],
            'items.*.quantity'    => ['required_with:items', 'integer', 'min:1'],
            'items.*.notes'       => ['nullable', 'string', 'max:255'],
            'extra_items'         => ['nullable', 'array'],
            'extra_items.*.desc'  => ['required_with:extra_items', 'string', 'max:255'],
            'extra_items.*.qty'   => ['required_with:extra_items', 'integer', 'min:1'],
            'client_notes'        => ['nullable', 'string', 'max:1000'],
            'payment_preference'  => ['nullable', 'string', 'in:pix,dinheiro,credito,debito,boleto,outro'],
        ]);

        if (empty($validated['items']) && empty($validated['extra_items'])) {
            return back()->withErrors(['items' => 'Adicione pelo menos um produto ou item ao orçamento.']);
        }

        // Enrich items with product name (snapshot so name changes don't affect history)
        $items = [];
        foreach ($validated['items'] ?? [] as $item) {
            $product = Product::withoutGlobalScope('team_visibility')
                ->where('id', $item['product_id'])
                ->where('user_id', $client->user_id)
                ->first();
            if ($product) {
                $items[] = [
                    'product_id' => $product->id,
                    'name'       => $product->name,
                    'quantity'   => (int) $item['quantity'],
                    'notes'      => $item['notes'] ?? '',
                    'price_ref'  => (float) $product->price_sale,
                ];
            }
        }

        $extraItems = [];
        foreach ($validated['extra_items'] ?? [] as $ei) {
            $extraItems[] = [
                'description' => $ei['desc'],
                'quantity'    => (int) $ei['qty'],
            ];
        }

        $quote = ClientQuoteRequest::create([
            'client_id'          => $client->id,
            'user_id'            => $client->user_id,
            'status'             => 'pending',
            'items'              => $items,
            'extra_items'        => $extraItems ?: null,
            'client_notes'       => $validated['client_notes'] ?? null,
            'payment_preference' => $validated['payment_preference'] ?? null,
        ]);

        // Notificar o admin (dono da conta) sobre o novo orçamento do portal
        $owner = User::find($client->user_id);
        if ($owner) {
            $owner->notify(new PortalQuoteReceived($quote));
        }

        return redirect()->route('portal.quotes.show', $quote)
            ->with('success', 'Orçamento enviado com sucesso! Aguarde a resposta do vendedor.');
    }

    public function showQuote(ClientQuoteRequest $quote)
    {
        /** @var \App\Models\Client $client */
        $client = Auth::guard('portal')->user();

        abort_if($quote->client_id !== $client->id, 403);

        return view('portal.quotes.show', compact('client', 'quote'));
    }

    public function respondToQuote(Request $request, ClientQuoteRequest $quote)
    {
        /** @var \App\Models\Client $client */
        $client = Auth::guard('portal')->user();

        abort_if((int) $quote->client_id !== (int) $client->id, 403);

        $validated = $request->validate([
            'status' => ['required', 'in:approved,rejected'],
        ]);

        abort_unless($quote->status === 'quoted', 422, 'Somente orçamentos respondidos podem ser aprovados ou recusados.');

        $quote->update(['status' => $validated['status']]);

        if ($validated['status'] === 'approved') {
            return back()->with('success', 'Orçamento aceito! Nosso time confirmará em breve.');
        }

        return back()->with('success', 'Orçamento recusado. Caso deseje, entre em contato conosco.');
    }

    public function editQuote(ClientQuoteRequest $quote)
    {
        /** @var \App\Models\Client $client */
        $client = Auth::guard('portal')->user();

        abort_if($quote->client_id !== $client->id, 403);
        abort_unless($quote->can_edit, 403, 'Este orçamento não pode mais ser editado.');

        $products = Product::withoutGlobalScope('team_visibility')
            ->where('user_id', $client->user_id)
            ->where('stock_quantity', '>', 0)
            ->whereIn('status', ['active', 'ativo'])
            ->with('category')
            ->orderBy('name')
            ->get();

        return view('portal.quotes.edit', compact('client', 'quote', 'products'));
    }

    public function updateQuote(Request $request, ClientQuoteRequest $quote)
    {
        /** @var \App\Models\Client $client */
        $client = Auth::guard('portal')->user();

        abort_if($quote->client_id !== $client->id, 403);
        abort_unless($quote->can_edit, 422, 'Este orçamento não pode mais ser editado.');

        $validated = $request->validate([
            'items'               => ['nullable', 'array'],
            'items.*.product_id'  => ['required_with:items', 'integer', 'exists:products,id'],
            'items.*.quantity'    => ['required_with:items', 'integer', 'min:1'],
            'items.*.notes'       => ['nullable', 'string', 'max:255'],
            'extra_items'         => ['nullable', 'array'],
            'extra_items.*.desc'  => ['required_with:extra_items', 'string', 'max:255'],
            'extra_items.*.qty'   => ['required_with:extra_items', 'integer', 'min:1'],
            'client_notes'        => ['nullable', 'string', 'max:1000'],
        ]);

        if (empty($validated['items']) && empty($validated['extra_items'])) {
            return back()->withErrors(['items' => 'Adicione pelo menos um produto ou item ao orçamento.']);
        }

        $items = [];
        foreach ($validated['items'] ?? [] as $item) {
            $product = Product::withoutGlobalScope('team_visibility')
                ->where('id', $item['product_id'])
                ->where('user_id', $client->user_id)
                ->first();
            if ($product) {
                $items[] = [
                    'product_id' => $product->id,
                    'name'       => $product->name,
                    'quantity'   => (int) $item['quantity'],
                    'notes'      => $item['notes'] ?? '',
                    'price_ref'  => (float) $product->price_sale,
                ];
            }
        }

        $extraItems = [];
        foreach ($validated['extra_items'] ?? [] as $ei) {
            $extraItems[] = [
                'description' => $ei['desc'],
                'quantity'    => (int) $ei['qty'],
            ];
        }

        $quote->update([
            'items'        => $items,
            'extra_items'  => $extraItems ?: null,
            'client_notes' => $validated['client_notes'] ?? null,
        ]);

        return redirect()->route('portal.quotes.show', $quote)
            ->with('success', 'Orçamento atualizado com sucesso!');
    }

    public function destroyQuote(ClientQuoteRequest $quote)
    {
        /** @var \App\Models\Client $client */
        $client = Auth::guard('portal')->user();

        abort_if($quote->client_id !== $client->id, 403);
        abort_unless($quote->can_edit, 422, 'Este orçamento não pode mais ser excluído.');

        $quote->delete();

        return redirect()->route('portal.quotes')
            ->with('success', 'Orçamento excluído com sucesso.');
    }

    public function profile()
    {
        /** @var \App\Models\Client $client */
        $client = Auth::guard('portal')->user();
        $requiresOnboarding = $client->needsPortalOnboarding();
        $requiresPasswordSetup = (bool) $client->portal_force_password_change;
        $requiresProfileCompletion = ! $client->hasRequiredPortalProfileData();

        return view('portal.profile', compact('client', 'requiresOnboarding', 'requiresPasswordSetup', 'requiresProfileCompletion'));
    }

    public function updateProfile(Request $request)
    {
        /** @var \App\Models\Client $client */
        $client = Auth::guard('portal')->user();

        $requiresPasswordSetup = (bool) $client->portal_force_password_change;
        $requiresProfileCompletion = ! $client->hasRequiredPortalProfileData();

        $request->merge([
            'phone' => $this->normalizePhone($request->input('phone')),
            'cpf_cnpj' => $this->normalizeDigits($request->input('cpf_cnpj')),
            'cep' => $this->normalizeCep($request->input('cep')),
        ]);

        $validated = $request->validate([
            'phone'        => [$requiresProfileCompletion ? 'required' : 'nullable', 'regex:/^\d{10,11}$/'],
            'cpf_cnpj'     => [$requiresProfileCompletion ? 'required' : 'nullable', 'regex:/^(\d{11}|\d{14})$/'],
            'cep'          => [$requiresProfileCompletion ? 'required' : 'nullable', 'regex:/^\d{8}$/'],
            'street'       => [$requiresProfileCompletion ? 'required' : 'nullable', 'string', 'max:180'],
            'number'       => [$requiresProfileCompletion ? 'required' : 'nullable', 'string', 'max:20'],
            'complement'   => ['nullable', 'string', 'max:120'],
            'neighborhood' => [$requiresProfileCompletion ? 'required' : 'nullable', 'string', 'max:120'],
            'city'         => [$requiresProfileCompletion ? 'required' : 'nullable', 'string', 'max:120'],
            'state'        => [$requiresProfileCompletion ? 'required' : 'nullable', 'string', 'size:2'],
            'birth_date'   => ['nullable', 'date', 'before:today'],
            'company'      => ['nullable', 'string', 'max:150'],
            'portal_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($requiresPasswordSetup) {
            $request->validate([
                'new_password'     => ['required', 'string', 'min:8', 'confirmed'],
            ]);
            $validated['portal_password'] = Hash::make($request->new_password);
            $validated['portal_force_password_change'] = false;
        } elseif ($request->filled('new_password')) {
            $request->validate([
                'current_password' => ['required'],
                'new_password'     => ['required', 'string', 'min:8', 'confirmed'],
            ]);

            if (! Hash::check($request->current_password, $client->portal_password)) {
                return back()->withErrors(['current_password' => 'Senha atual incorreta.']);
            }

            $validated['portal_password'] = Hash::make($request->new_password);
        }

        $validated['state'] = isset($validated['state']) ? strtoupper((string) $validated['state']) : null;
        $validated['phone'] = $this->formatPhoneForStorage($validated['phone'] ?? null);
        $validated['cpf_cnpj'] = $this->formatDocumentForStorage($validated['cpf_cnpj'] ?? null);
        $validated['cep'] = $this->formatCepForStorage($validated['cep'] ?? null);
        $validated['address'] = $this->buildAddressFromProfileData($validated);

        $client->fill($validated);

        if ($client->hasRequiredPortalProfileData()) {
            $client->portal_profile_completed_at = now();
        }

        $client->save();

        return back()->with('success', ($requiresPasswordSetup || $requiresProfileCompletion) ? 'Cadastro inicial concluido com sucesso! Agora seu acesso esta liberado.' : 'Perfil atualizado com sucesso!');
    }

    // ─── Admin Actions ──────────────────────────────────────────────────────────

    public function accessPage(Client $client)
    {
        $this->assertCanManageClient($client);
        $this->ensurePortalLogin($client);

        return view('clients.portal-access', compact('client'));
    }

    /** Gera credenciais e envia e-mail de acesso ao portal para o cliente */
    public function sendAccess(Request $request, Client $client)
    {
        $this->assertCanManageClient($client);

        $this->ensurePortalLogin($client);

        $plainPassword = Str::upper(Str::random(4)) . random_int(10, 99) . Str::lower(Str::random(2));
        $client->update($this->buildPortalAccessPayload($plainPassword));

        if (filled($client->email)) {
            try {
                Mail::to($client->email)->send(new ClientPortalAccessMail($client, $plainPassword));
                $message = 'Acesso ao portal enviado para ' . e($client->email) . '.';
            } catch (\Exception $e) {
                $message = 'O e-mail nao pode ser enviado agora. A senha temporaria foi gerada para copia manual.';
            }
        } else {
            $message = 'Cliente sem e-mail. O acesso foi criado normalmente e pode ser enviado manualmente por WhatsApp ou outro canal.';
        }

        $whatsAppMessage = $this->buildPortalAccessMessage($client, $plainPassword);

        return redirect()->route('clients.portal.access', $client)
            ->with('portal_access_sent', new HtmlString($message))
            ->with('portal_access_context', 'password')
            ->with('portal_access_login', $client->portal_login)
            ->with('portal_access_password', $plainPassword)
            ->with('portal_access_url', route('portal.login'))
            ->with('portal_access_whatsapp_message', $whatsAppMessage)
            ->with('portal_access_whatsapp_url', $this->buildWhatsAppUrl($client->phone, $whatsAppMessage));
    }

    /** Gera um link administrativo de redefinicao para compartilhar com o cliente */
    public function generateResetAccessLink(Client $client)
    {
        $this->assertCanManageClient($client);

        $this->ensurePortalLogin($client);

        if (! $client->portal_active) {
            $client->forceFill([
                'portal_active' => true,
                'portal_force_password_change' => true,
            ])->save();
        }

        $token = $this->issuePortalResetToken($client);
        $resetUrl = route('portal.password.reset', [
            'token' => $token,
            'login' => $client->portal_login,
        ]);

        $whatsAppMessage = implode("\n", [
            'Ola, ' . $client->name . '!',
            '',
            'Preparamos um link seguro e individual para voce definir sua senha do portal:',
            $resetUrl,
            '',
            'Login: ' . $client->portal_login,
            'Portal: ' . route('portal.login'),
            'Depois de definir a senha, complete apenas os dados do perfil que faltarem.',
        ]);

        $message = filled($client->email)
            ? 'Link de redefinicao gerado para <strong>' . e($client->email) . '</strong> e disponivel para copia.'
            : 'Link de redefinicao gerado para envio manual. O cliente pode definir a senha mesmo sem e-mail cadastrado.';

        return redirect()->route('clients.portal.access', $client)
            ->with('portal_access_sent', new HtmlString($message))
            ->with('portal_access_context', 'reset-link')
            ->with('portal_access_login', $client->portal_login)
            ->with('portal_access_url', route('portal.login'))
            ->with('portal_access_reset_url', $resetUrl)
            ->with('portal_access_whatsapp_message', $whatsAppMessage)
            ->with('portal_access_whatsapp_url', $this->buildWhatsAppUrl($client->phone, $whatsAppMessage));
    }

    /** Revoga o acesso do cliente ao portal */
    public function revokeAccess(Client $client)
    {
        $this->assertCanManageClient($client);

        $client->update(['portal_active' => false]);

        return back()->with('success', 'Acesso ao portal revogado.');
    }

    /** Lista orçamentos de um cliente (admin) */
    /** Admin confirma orçamento e cria uma Venda */
    public function adminConfirmQuote(Request $request, ClientQuoteRequest $quote)
    {
        $this->assertCanManageClient($quote->client);

        $validated = $request->validate([
            'total_price'    => ['required', 'numeric', 'min:0'],
            'payment_method' => ['required', 'string'],
            'tipo_pagamento' => ['required', 'in:a_vista,parcelado'],
            'parcelas'       => ['required_if:tipo_pagamento,parcelado', 'nullable', 'integer', 'min:1', 'max:120'],
        ]);

        $sale = Sale::create([
            'client_id'       => $quote->client_id,
            'user_id'         => Auth::id(),
            'total_price'     => $validated['total_price'],
            'amount_paid'     => 0,
            'status'          => 'pendente',
            'payment_method'  => $validated['payment_method'],
            'tipo_pagamento'  => $validated['tipo_pagamento'],
            'parcelas'        => $validated['tipo_pagamento'] === 'parcelado' ? ($validated['parcelas'] ?? 1) : 1,
            'source'          => 'portal',
            'portal_quote_id' => $quote->id,
        ]);

        foreach ($quote->items ?? [] as $item) {
            if (empty($item['product_id'])) {
                continue;
            }
            $product = Product::withoutGlobalScopes()->find($item['product_id']);
            if (!$product) {
                continue;
            }
            SaleItem::create([
                'sale_id'    => $sale->id,
                'product_id' => $product->id,
                'quantity'   => (int) ($item['quantity'] ?? 1),
                'price_sale' => $product->price_sale ?? $item['price_ref'] ?? 0,
                'price'      => $product->price_sale ?? $item['price_ref'] ?? 0,
            ]);

            // Baixar estoque
            if ($product->stock_quantity !== null) {
                $product->decrement('stock_quantity', (int) ($item['quantity'] ?? 1));
            }
        }

        $quote->update([
            'status'       => 'approved',
            'quoted_total' => $validated['total_price'],
        ]);

        return redirect()->route('sales.show', $sale)
            ->with('success', "Orçamento confirmado! Venda #{$sale->id} criada com sucesso.");
    }

    /** Admin visualiza orçamentos de um cliente */
    public function adminClientQuotes(Client $client)
    {
        $this->assertCanManageClient($client);

        $quotes = ClientQuoteRequest::where('client_id', $client->id)->latest()->get();

        return view('clients.portal-quotes', compact('client', 'quotes'));
    }

    /** Responde a um orçamento (admin) */
    public function adminRespondQuote(Request $request, ClientQuoteRequest $quote)
    {
        $this->assertCanManageClient($quote->client);

        $validated = $request->validate([
            'status'       => ['required', 'in:pending,reviewing,quoted,approved,rejected'],
            'admin_notes'  => ['nullable', 'string', 'max:2000'],
            'quoted_total' => ['nullable', 'numeric', 'min:0'],
            'valid_until'  => ['nullable', 'date', 'after:today'],
        ]);

        $quote->update($validated);

        return back()->with('success', 'Orçamento atualizado!');
    }

    private function assertCanManageClient(Client $client): void
    {
        $user = Auth::user();

        abort_unless(
            $user instanceof \App\Models\User
            && ($user->isAdmin() || (int) $user->id === (int) $client->user_id),
            403
        );
    }

    private function buildPortalAccessPayload(string $plainPassword): array
    {
        return [
            'portal_password' => Hash::make($plainPassword),
            'portal_active' => true,
            'portal_force_password_change' => true,
            'portal_profile_completed_at' => null,
            'portal_token' => null,
            'portal_token_expires_at' => null,
        ];
    }

    private function buildPortalAccessMessage(Client $client, string $plainPassword): string
    {
        return implode("\n", [
            'Ola, ' . $client->name . '!',
            '',
            'Seu acesso ao portal foi liberado.',
            'Portal: ' . route('portal.login'),
            'Login: ' . $client->portal_login,
            'Senha temporaria: ' . $plainPassword,
            '',
            'No primeiro acesso, troque a senha e complete apenas os dados do seu cadastro.',
            'Se quiser, depois voce tambem pode conectar sua conta Google dentro do proprio portal.',
        ]);
    }

    private function ensurePortalLogin(Client $client): void
    {
        if (filled($client->portal_login) && ! $client->hasLegacyPortalLogin()) {
            return;
        }

        $client->forceFill([
            'portal_login' => Client::generateUniquePortalLogin($client->name, $client->id),
        ])->save();

        $client->refresh();
    }

    private function issuePortalResetToken(Client $client): string
    {
        $plainToken = Str::random(64);

        $client->forceFill([
            'portal_token' => Hash::make($plainToken),
            'portal_token_expires_at' => now()->addMinutes(60),
        ])->save();

        return $plainToken;
    }

    private function hasValidPortalResetToken(Client $client, string $plainToken): bool
    {
        if (blank($client->portal_token) || blank($client->portal_token_expires_at)) {
            return false;
        }

        if ($client->portal_token_expires_at->isPast()) {
            return false;
        }

        return Hash::check($plainToken, $client->portal_token);
    }

    private function buildAddressFromProfileData(array $data): ?string
    {
        $parts = array_filter([
            $data['street'] ?? null,
            $data['number'] ?? null,
            $data['complement'] ?? null,
            $data['neighborhood'] ?? null,
            $data['city'] ?? null,
            $data['state'] ?? null,
            $data['cep'] ?? null,
        ]);

        return $parts ? implode(', ', $parts) : null;
    }

    private function buildWhatsAppUrl(?string $phone, string $message): string
    {
        $digits = $this->normalizeDigits($phone);

        if ($digits !== null && $digits !== '') {
            if (strlen($digits) === 10 || strlen($digits) === 11) {
                $digits = '55' . $digits;
            }

            return 'https://api.whatsapp.com/send?phone=' . $digits . '&text=' . urlencode($message);
        }

        return 'https://api.whatsapp.com/send?text=' . urlencode($message);
    }

    private function normalizeDigits(?string $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        return preg_replace('/\D+/', '', (string) $value);
    }

    private function normalizePhone(?string $value): ?string
    {
        return $this->normalizeDigits($value);
    }

    private function normalizeCep(?string $value): ?string
    {
        return $this->normalizeDigits($value);
    }

    private function formatPhoneForStorage(?string $value): ?string
    {
        $digits = $this->normalizePhone($value);

        if (blank($digits)) {
            return null;
        }

        if (strlen($digits) === 10) {
            return preg_replace('/^(\d{2})(\d{4})(\d{4})$/', '($1) $2-$3', $digits);
        }

        if (strlen($digits) === 11) {
            return preg_replace('/^(\d{2})(\d{5})(\d{4})$/', '($1) $2-$3', $digits);
        }

        return $digits;
    }

    private function formatDocumentForStorage(?string $value): ?string
    {
        $digits = $this->normalizeDigits($value);

        if (blank($digits)) {
            return null;
        }

        if (strlen($digits) === 11) {
            return preg_replace('/^(\d{3})(\d{3})(\d{3})(\d{2})$/', '$1.$2.$3-$4', $digits);
        }

        if (strlen($digits) === 14) {
            return preg_replace('/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/', '$1.$2.$3/$4-$5', $digits);
        }

        return $digits;
    }

    private function formatCepForStorage(?string $value): ?string
    {
        $digits = $this->normalizeCep($value);

        if (blank($digits)) {
            return null;
        }

        if (strlen($digits) === 8) {
            return preg_replace('/^(\d{5})(\d{3})$/', '$1-$2', $digits);
        }

        return $digits;
    }
}
