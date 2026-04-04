<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Services\Payment\PaymentService;
use Illuminate\Http\Request;

class PlansController extends Controller
{

    // ── Planos ────────────────────────────────────────────────────

    public function index()
    {
        $plans = Plan::withCount('activeSubscriptions')->orderBy('sort_order')->get();

        $stats = [
            'total_subscribers'   => Subscription::whereIn('status', ['active', 'trialing'])->count(),
            'revenue_this_month'  => Subscription::where('status', 'active')
                ->whereMonth('current_period_start', now()->month)
                ->sum('price_paid'),
            'canceled_this_month' => Subscription::where('status', 'canceled')
                ->whereMonth('canceled_at', now()->month)
                ->count(),
            'trialing'            => Subscription::where('status', 'trialing')->count(),
        ];

        return view('admin.plans.index', compact('plans', 'stats'));
    }

    public function create()
    {
        return view('admin.plans.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validatePlan($request);

        if ($validated['is_default']) {
            Plan::query()->update(['is_default' => false]);
        }

        $plan = Plan::create($validated);

        return redirect()->route('admin.plans.index')
            ->with('success', "Plano \"{$plan->name}\" criado com sucesso!");
    }

    public function edit(Plan $plan)
    {
        $subscribersCount = $plan->activeSubscriptions()->count();
        return view('admin.plans.edit', compact('plan', 'subscribersCount'));
    }

    public function update(Request $request, Plan $plan)
    {
        $validated = $this->validatePlan($request);

        if ($validated['is_default']) {
            Plan::whereKeyNot($plan->id)->update(['is_default' => false]);
        }

        $plan->update($validated);

        return redirect()->route('admin.plans.index')
            ->with('success', "Plano \"{$plan->name}\" atualizado com sucesso!");
    }

    public function destroy(Plan $plan)
    {
        if ($plan->activeSubscriptions()->count() > 0) {
            return back()->with('error', 'Não é possível excluir um plano com assinantes ativos.');
        }
        $plan->delete();
        return redirect()->route('admin.plans.index')
            ->with('success', "Plano \"{$plan->name}\" excluído.");
    }

    public function toggleActive(Plan $plan)
    {
        $plan->update(['is_active' => !$plan->is_active]);
        $status = $plan->is_active ? 'ativado' : 'desativado';
        return back()->with('success', "Plano \"{$plan->name}\" {$status}.");
    }

    // ── Assinaturas ───────────────────────────────────────────────

    public function subscriptions(Request $request)
    {
        $query = Subscription::with(['user', 'plan'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('plan')) {
            $query->where('plan_id', $request->plan);
        }
        if ($request->filled('search')) {
            $query->whereHas('user', fn ($q) =>
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%")
            );
        }

        $subscriptions = $query->paginate(25)->withQueryString();
        $plans = Plan::all();

        return view('admin.plans.subscriptions', compact('subscriptions', 'plans'));
    }

    public function showSubscription(Subscription $subscription)
    {
        $subscription->load(['user', 'plan']);
        return view('admin.plans.subscription-detail', compact('subscription'));
    }

    /**
     * Admin concede acesso manual a um usuário.
     */
    public function grantAccess(Request $request)
    {
        $request->validate([
            'user_id'       => 'required|exists:users,id',
            'plan_id'       => 'required|exists:plans,id',
            'billing_cycle' => 'required|in:monthly,annual',
            'notes'         => 'nullable|string|max:500',
        ]);

        $user = User::findOrFail($request->user_id);
        $plan = Plan::findOrFail($request->plan_id);
        $ps   = new PaymentService();

        $sub = $ps->activateSubscription(
            $user,
            $plan,
            $request->billing_cycle,
            'manual',
            0,
            null,
            null,
            null
        );

        if ($request->notes) {
            $sub->update(['notes' => $request->notes]);
        }

        return back()->with('success', "Acesso concedido a {$user->name} — Plano {$plan->name}.");
    }

    /**
     * Ativa ou desativa o acesso admin de um usuário.
     */
    public function toggleAdmin(Request $request)
    {
        $request->validate(['user_id' => 'required|exists:users,id']);

        $user = User::findOrFail($request->user_id);

        // Impede revogar o próprio admin
        if ($user->id === auth()->id() && $user->is_admin) {
            return back()->with('error', 'Você não pode revogar seu próprio acesso admin.');
        }

        $user->update(['is_admin' => !$user->is_admin]);

        $status = $user->is_admin ? 'promovido a admin' : 'removido do admin';
        return back()->with('success', "{$user->name} foi {$status}.");
    }

    /**
     * Admin revoga assinatura de um usuário.
     */
    public function revokeAccess(Subscription $subscription)
    {
        $user = $subscription->user;
        $subscription->update([
            'status'      => Subscription::STATUS_CANCELED,
            'canceled_at' => now(),
        ]);
        return back()->with('success', "Assinatura de {$user->name} revogada.");
    }

    /**
     * Admin estende o período de uma assinatura.
     */
    public function extendSubscription(Request $request, Subscription $subscription)
    {
        $request->validate(['days' => 'required|integer|min:1|max:365']);

        $current = $subscription->current_period_end ?? now();
        $subscription->update([
            'current_period_end' => $current->addDays($request->days),
        ]);

        return back()->with('success', "Assinatura estendida por {$request->days} dias.");
    }

    // ── Usuários ──────────────────────────────────────────────────

    public function users(Request $request)
    {
        $viewer = $request->user();
        $isAdminView = $viewer->isAdmin();
        $query = User::with(['activeSubscription.plan'])->latest();

        if ($isAdminView) {
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            if ($request->filled('plan')) {
                $planSlug = $request->plan;
                $query->whereHas('subscriptions', function ($q) use ($planSlug) {
                    $q->whereIn('status', ['active', 'trialing'])
                      ->whereHas('plan', fn ($p) => $p->where('slug', $planSlug));
                });
            }
        } else {
            $query->whereKey($viewer->id);
        }

        $users = $query->paginate($isAdminView ? 30 : 1)->withQueryString();
        $plans = Plan::active()->ordered()->get();
        $defaultPlan = Plan::getDefault();
        $stats = $isAdminView
            ? $this->adminAccessStats()
            : $this->selfAccessStats($viewer, $defaultPlan);

        return view('admin.plans.users', compact('users', 'plans', 'defaultPlan', 'stats', 'isAdminView'));
    }

    private function adminAccessStats(): array
    {
        return [
            'total_users' => User::count(),
            'admin_users' => User::where('is_admin', true)->count(),
            'paid_users' => User::whereHas('activeSubscription.plan', fn ($q) => $q->where('slug', '!=', 'free'))->count(),
            'free_users' => User::where(function ($q) {
                $q->whereDoesntHave('activeSubscription')
                    ->orWhereHas('activeSubscription.plan', fn ($planQuery) => $planQuery->where('slug', 'free'));
            })->count(),
            'team_users' => User::whereHas('activeSubscription.plan', fn ($q) => $q->where('max_users', '>', 1))->count(),
        ];
    }

    private function selfAccessStats(User $viewer, ?Plan $defaultPlan): array
    {
        $viewer->loadMissing(['activeSubscription.plan']);

        $currentPlan = $viewer->activeSubscription?->plan ?? $defaultPlan;
        $hasPaidPlan = $currentPlan && $currentPlan->slug !== 'free';
        $enabledPermissions = collect($viewer->effectivePermissions())
            ->where('enabled', true)
            ->count();

        return [
            'total_users' => 1,
            'admin_users' => $viewer->isAdmin() ? 1 : 0,
            'paid_users' => $hasPaidPlan ? 1 : 0,
            'free_users' => $hasPaidPlan ? 0 : 1,
            'team_users' => (($currentPlan?->max_users ?? 1) > 1) ? 1 : 0,
            'enabled_permissions' => $enabledPermissions,
        ];
    }

    // ── Private helpers ───────────────────────────────────────────

    private function validatePlan(Request $request): array
    {
        $validated = $request->validate([
            'name'                    => 'required|string|max:100',
            'slug'                    => 'required|string|max:50|unique:plans,slug,' . ($request->route('plan')?->id),
            'description'             => 'nullable|string|max:500',
            'price_monthly'           => 'required|numeric|min:0',
            'price_annual'            => 'required|numeric|min:0',
            'trial_days'              => 'nullable|integer|min:0|max:90',
            'is_active'               => 'nullable|boolean',
            'is_default'              => 'nullable|boolean',
            'sort_order'              => 'nullable|integer',
            'max_products'            => 'required|integer|min:-1',
            'max_orders_per_month'    => 'required|integer|min:-1',
            'max_clients'             => 'required|integer|min:-1',
            'max_users'               => 'required|integer|min:1',
            'has_ml_integration'      => 'nullable|boolean',
            'has_shopee_integration'  => 'nullable|boolean',
            'has_ai_features'         => 'nullable|boolean',
            'has_advanced_reports'    => 'nullable|boolean',
            'has_financial_control'   => 'nullable|boolean',
            'has_api_access'          => 'nullable|boolean',
            'has_priority_support'    => 'nullable|boolean',
            'has_export_pdf_excel'    => 'nullable|boolean',
            'color'                   => 'nullable|string|max:20',
            'badge_label'             => 'nullable|string|max:50',
        ]);

        foreach ([
            'is_active',
            'is_default',
            'has_ml_integration',
            'has_shopee_integration',
            'has_ai_features',
            'has_advanced_reports',
            'has_financial_control',
            'has_api_access',
            'has_priority_support',
            'has_export_pdf_excel',
        ] as $booleanField) {
            $validated[$booleanField] = $request->boolean($booleanField);
        }

        $validated['trial_days'] = (int) ($validated['trial_days'] ?? 0);
        $validated['sort_order'] = (int) ($validated['sort_order'] ?? 0);

        return $validated;
    }
}
