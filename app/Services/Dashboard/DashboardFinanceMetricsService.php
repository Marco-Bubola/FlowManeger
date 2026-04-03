<?php

namespace App\Services\Dashboard;

use App\Models\Bank;
use App\Models\Cashbook;
use App\Models\CashbookUploadHistory;
use App\Models\Category;
use App\Models\Cofrinho;
use App\Models\Invoice;
use App\Models\InvoiceUploadHistory;
use App\Models\Orcamento;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class DashboardFinanceMetricsService
{
    public function getCashbookMetrics(int $userId, int $year, int $month, ?int $cofrinhoFiltro = null): array
    {
        $referenceDate = Carbon::create($year, $month, 1);
        $startOfMonth = $referenceDate->copy()->startOfMonth();
        $endOfMonth = $referenceDate->copy()->endOfMonth();
        $startOfYear = $referenceDate->copy()->startOfYear();
        $endOfYear = $referenceDate->copy()->endOfYear();
        $previousMonth = $referenceDate->copy()->subMonth();

        $monthlyCashbook = Cashbook::query()
            ->where('user_id', $userId)
            ->whereYear('date', $year)
            ->whereNull('cofrinho_id')
            ->selectRaw('MONTH(date) as month_number, type_id, SUM(value) as total')
            ->groupBy('month_number', 'type_id')
            ->get();

        $dadosReceita = array_fill(0, 12, 0.0);
        $dadosDespesa = array_fill(0, 12, 0.0);
        foreach ($monthlyCashbook as $row) {
            $index = (int) $row->month_number - 1;
            if ((int) $row->type_id === 1) {
                $dadosReceita[$index] = (float) $row->total;
            } else {
                $dadosDespesa[$index] = (float) $row->total;
            }
        }
        $saldosMes = array_map(fn ($receita, $despesa) => $receita - $despesa, $dadosReceita, $dadosDespesa);

        $totalReceitas = array_sum($dadosReceita);
        $totalDespesas = array_sum($dadosDespesa);
        $saldoTotal = (float) Cashbook::where('user_id', $userId)
            ->whereNull('cofrinho_id')
            ->selectRaw('SUM(CASE WHEN type_id = 1 THEN value ELSE -value END) as saldo')
            ->value('saldo');

        $receitaMesAtual = (float) ($dadosReceita[$month - 1] ?? 0);
        $despesaMesAtual = (float) ($dadosDespesa[$month - 1] ?? 0);
        $saldoMesAtual = $receitaMesAtual - $despesaMesAtual;

        $receitaMesAnterior = (float) Cashbook::where('user_id', $userId)
            ->whereNull('cofrinho_id')
            ->where('type_id', 1)
            ->whereYear('date', $previousMonth->year)
            ->whereMonth('date', $previousMonth->month)
            ->sum('value');

        $despesaMesAnterior = (float) Cashbook::where('user_id', $userId)
            ->whereNull('cofrinho_id')
            ->where('type_id', 2)
            ->whereYear('date', $previousMonth->year)
            ->whereMonth('date', $previousMonth->month)
            ->sum('value');

        $invoiceMonthlyRows = Invoice::query()
            ->where('user_id', $userId)
            ->whereYear('invoice_date', $year)
            ->selectRaw('MONTH(invoice_date) as month_number, SUM(value) as total, COUNT(*) as quantity')
            ->groupBy('month_number')
            ->get();

        $invoiceTotalsByMonth = array_fill(0, 12, 0.0);
        $invoiceCountsByMonth = array_fill(0, 12, 0);
        foreach ($invoiceMonthlyRows as $row) {
            $index = (int) $row->month_number - 1;
            $invoiceTotalsByMonth[$index] = (float) $row->total;
            $invoiceCountsByMonth[$index] = (int) $row->quantity;
        }

        $invoiceMesAtual = (float) ($invoiceTotalsByMonth[$month - 1] ?? 0);
        $invoiceQuantidadeMesAtual = (int) ($invoiceCountsByMonth[$month - 1] ?? 0);
        $ticketMedioInvoice = $invoiceQuantidadeMesAtual > 0 ? $invoiceMesAtual / $invoiceQuantidadeMesAtual : 0.0;
        $invoiceMesAnterior = (float) Invoice::where('user_id', $userId)
            ->whereYear('invoice_date', $previousMonth->year)
            ->whereMonth('invoice_date', $previousMonth->month)
            ->sum('value');

        $highestInvoice = Invoice::where('user_id', $userId)
            ->whereBetween('invoice_date', [$startOfMonth, $endOfMonth])
            ->orderByDesc('value')
            ->first();

        $diasNoMes = $referenceDate->daysInMonth;
        $diasInvoices = [];
        $valoresInvoicesTemp = array_fill(1, $diasNoMes, 0.0);
        for ($day = 1; $day <= $diasNoMes; $day++) {
            $diasInvoices[] = Carbon::create($year, $month, $day)->format('d/m');
        }

        $invoicesData = Invoice::query()
            ->where('user_id', $userId)
            ->whereYear('invoice_date', $year)
            ->whereMonth('invoice_date', $month)
            ->selectRaw('DAY(invoice_date) as day_number, SUM(value) as total')
            ->groupBy('day_number')
            ->get();

        foreach ($invoicesData as $row) {
            $valoresInvoicesTemp[(int) $row->day_number] = (float) $row->total;
        }

        $categoriaRows = Invoice::query()
            ->where('invoice.user_id', $userId)
            ->whereYear('invoice_date', $year)
            ->join('category', 'invoice.category_id', '=', 'category.id_category')
            ->selectRaw('category.name as categoria, SUM(invoice.value) as total')
            ->groupBy('category.id_category', 'category.name')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $expenseCategoryRows = collect();
        $cashbookCategories = Cashbook::query()
            ->where('cashbook.user_id', $userId)
            ->whereNull('cofrinho_id')
            ->where('type_id', 2)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->join('category', 'cashbook.category_id', '=', 'category.id_category')
            ->selectRaw('category.name as categoria, SUM(cashbook.value) as total')
            ->groupBy('category.id_category', 'category.name')
            ->get();

        $invoiceCategories = Invoice::query()
            ->where('invoice.user_id', $userId)
            ->whereBetween('invoice_date', [$startOfMonth, $endOfMonth])
            ->join('category', 'invoice.category_id', '=', 'category.id_category')
            ->selectRaw('category.name as categoria, SUM(invoice.value) as total')
            ->groupBy('category.id_category', 'category.name')
            ->get();

        $expenseCategoryRows = $cashbookCategories
            ->concat($invoiceCategories)
            ->groupBy('categoria')
            ->map(fn (Collection $items, string $categoria) => [
                'categoria' => $categoria,
                'total' => (float) $items->sum('total'),
            ])
            ->sortByDesc('total')
            ->take(8)
            ->values();

        $cashbookDays = Cashbook::query()
            ->where('user_id', $userId)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->distinct()
            ->pluck('date')
            ->map(fn ($date) => Carbon::parse($date)->day)
            ->values()
            ->all();

        $invoiceDays = Invoice::query()
            ->where('user_id', $userId)
            ->whereYear('invoice_date', $year)
            ->whereMonth('invoice_date', $month)
            ->distinct()
            ->pluck('invoice_date')
            ->map(fn ($date) => Carbon::parse($date)->day)
            ->values()
            ->all();

        $cofrinhosData = $this->buildCofrinhosData($userId);
        $cofrinhos = $cofrinhosData['cofrinhos'];
        $totalCofrinhos = (float) collect($cofrinhos)->sum('valor_guardado');
        $totalMetasCofrinhos = (float) collect($cofrinhos)->sum('meta_valor');
        $cofrinhosTopMeta = collect($cofrinhos)
            ->filter(fn ($item) => (float) $item['progresso'] < 100)
            ->sortByDesc('progresso')
            ->take(3)
            ->values()
            ->all();
        $economiadoMesAtual = (float) Cashbook::query()
            ->where('user_id', $userId)
            ->whereNotNull('cofrinho_id')
            ->where('type_id', 1)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->sum('value');
        $economiadoMesAnterior = (float) Cashbook::query()
            ->where('user_id', $userId)
            ->whereNotNull('cofrinho_id')
            ->where('type_id', 1)
            ->whereYear('date', $previousMonth->year)
            ->whereMonth('date', $previousMonth->month)
            ->sum('value');
        $evolucaoCofrinhos = $this->buildCofrinhosEvolution($userId, $year);

        $selectedCofrinhoSummary = null;
        if ($cofrinhoFiltro) {
            $selected = collect($cofrinhos)->firstWhere('id', $cofrinhoFiltro);
            if ($selected) {
                $monthEntries = (float) Cashbook::query()
                    ->where('user_id', $userId)
                    ->where('cofrinho_id', $cofrinhoFiltro)
                    ->where('type_id', 1)
                    ->whereBetween('date', [$startOfMonth, $endOfMonth])
                    ->sum('value');
                $monthExits = (float) Cashbook::query()
                    ->where('user_id', $userId)
                    ->where('cofrinho_id', $cofrinhoFiltro)
                    ->where('type_id', 2)
                    ->whereBetween('date', [$startOfMonth, $endOfMonth])
                    ->sum('value');

                $selectedCofrinhoSummary = [
                    'nome' => $selected['nome'],
                    'valor_guardado' => $selected['valor_guardado'],
                    'meta_valor' => $selected['meta_valor'],
                    'progresso' => $selected['progresso'],
                    'saldo_mes' => $monthEntries - $monthExits,
                ];
            }
        }

        $orcamentoMesTotal = (float) Orcamento::query()
            ->where('user_id', $userId)
            ->where('mes', $month)
            ->where('ano', $year)
            ->sum('valor');

        $orcamentoMesUsado = (float) Cashbook::query()
            ->where('user_id', $userId)
            ->where('type_id', 2)
            ->whereNull('cofrinho_id')
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->sum('value');

        $orcamentosTopEstouro = $this->buildBudgetOverruns($userId, $year, $month);
        $orcamentoRestante = $orcamentoMesTotal - $orcamentoMesUsado;
        $orcamentoUsoPercentual = $orcamentoMesTotal > 0 ? ($orcamentoMesUsado / $orcamentoMesTotal) * 100 : 0.0;

        [$previsao30dias, $previsao60dias, $previsao90dias] = $this->buildForecasts($userId, $saldoTotal);

        $banks = Bank::query()->where('user_id', $userId)->get();
        $bankBreakdown = Invoice::query()
            ->where('user_id', $userId)
            ->whereBetween('invoice_date', [$startOfYear, $endOfYear])
            ->selectRaw('id_bank, SUM(value) as total, COUNT(*) as quantity')
            ->groupBy('id_bank')
            ->get()
            ->map(function ($row) use ($banks, $invoiceMesAtual, $year, $month, $userId) {
                $bank = $banks->firstWhere('id_bank', $row->id_bank);
                $monthlyTotal = (float) Invoice::query()
                    ->where('user_id', $userId)
                    ->where('id_bank', $row->id_bank)
                    ->whereYear('invoice_date', $year)
                    ->whereMonth('invoice_date', $month)
                    ->sum('value');

                return [
                    'id_bank' => (int) $row->id_bank,
                    'name' => $bank?->name ?? 'Banco #' . $row->id_bank,
                    'total' => (float) $row->total,
                    'quantity' => (int) $row->quantity,
                    'monthly_total' => $monthlyTotal,
                    'share' => $invoiceMesAtual > 0 ? ($monthlyTotal / $invoiceMesAtual) * 100 : 0.0,
                ];
            })
            ->sortByDesc('monthly_total')
            ->take(6)
            ->values()
            ->all();

        $bankCycleOverview = $this->buildBankCycleOverview($banks, $userId, $referenceDate);
        $activityFeed = $this->buildFinanceActivityFeed($userId, $cofrinhoFiltro);
        $recentTransactions = $this->buildRecentTransactions($userId, null, $cofrinhoFiltro);
        $uploadSummary = $this->buildUploadSummary($userId);
        $activeDaysCount = collect($cashbookDays)->concat($invoiceDays)->unique()->count();
        $daysInScope = now()->year === $year && now()->month === $month ? min(now()->day, $diasNoMes) : $diasNoMes;
        $topExpenseCategory = $expenseCategoryRows->first();
        $topBank = collect($bankBreakdown)->first();
        $mediaReceitaDia = $daysInScope > 0 ? $receitaMesAtual / $daysInScope : 0.0;
        $mediaDespesaDia = $daysInScope > 0 ? $despesaMesAtual / $daysInScope : 0.0;
        $mediaInvoiceDia = $daysInScope > 0 ? $invoiceMesAtual / $daysInScope : 0.0;
        $invoicePressurePercent = $receitaMesAtual > 0 ? ($invoiceMesAtual / $receitaMesAtual) * 100 : ($invoiceMesAtual > 0 ? 100.0 : 0.0);
        $savingsRatePercent = $receitaMesAtual > 0 ? ($economiadoMesAtual / $receitaMesAtual) * 100 : 0.0;

        $periodComparison = [
            'labels' => ['Receitas', 'Despesas', 'Invoices', 'Economia'],
            'current' => [$receitaMesAtual, $despesaMesAtual, $invoiceMesAtual, $economiadoMesAtual],
            'previous' => [$receitaMesAnterior, $despesaMesAnterior, $invoiceMesAnterior, $economiadoMesAnterior],
        ];

        return [
            'periodLabel' => ucfirst($referenceDate->locale('pt_BR')->translatedFormat('F/Y')),
            'totalReceitas' => $totalReceitas,
            'totalDespesas' => $totalDespesas,
            'saldoTotal' => $saldoTotal,
            'dadosReceita' => array_map('floatval', $dadosReceita),
            'dadosDespesa' => array_map('floatval', $dadosDespesa),
            'saldosMes' => array_map('floatval', $saldosMes),
            'saldoUltimoMes' => $saldoMesAtual,
            'nomeUltimoMes' => ucfirst($referenceDate->translatedFormat('M')),
            'receitaUltimoMes' => $receitaMesAtual,
            'despesaUltimoMes' => $despesaMesAtual,
            'receitaMesAtual' => $receitaMesAtual,
            'despesaMesAtual' => $despesaMesAtual,
            'saldoMesAtual' => $saldoMesAtual,
            'receitaMesAnterior' => $receitaMesAnterior,
            'despesaMesAnterior' => $despesaMesAnterior,
            'invoiceMesAtual' => $invoiceMesAtual,
            'invoiceMesAnterior' => $invoiceMesAnterior,
            'invoiceQuantidadeMesAtual' => $invoiceQuantidadeMesAtual,
            'ticketMedioInvoice' => $ticketMedioInvoice,
            'highestInvoice' => $highestInvoice,
            'invoiceTotalsByMonth' => array_map('floatval', $invoiceTotalsByMonth),
            'invoiceCountsByMonth' => array_map('intval', $invoiceCountsByMonth),
            'diasInvoices' => $diasInvoices,
            'valoresInvoices' => array_values($valoresInvoicesTemp),
            'categorias' => $categoriaRows->pluck('categoria')->values()->all(),
            'valoresCategorias' => $categoriaRows->pluck('total')->map(fn ($value) => (float) $value)->values()->all(),
            'expenseCategories' => $expenseCategoryRows->all(),
            'cashbookDays' => $cashbookDays,
            'invoiceDays' => $invoiceDays,
            'cofrinhos' => $cofrinhos,
            'totalCofrinhos' => $totalCofrinhos,
            'totalMetasCofrinhos' => $totalMetasCofrinhos,
            'cofrinhosTopMeta' => $cofrinhosTopMeta,
            'economiadoMesAtual' => $economiadoMesAtual,
            'economiadoMesAnterior' => $economiadoMesAnterior,
            'evolucaoCofrinhos' => $evolucaoCofrinhos,
            'selectedCofrinhoSummary' => $selectedCofrinhoSummary,
            'orcamentoMesTotal' => $orcamentoMesTotal,
            'orcamentoMesUsado' => $orcamentoMesUsado,
            'orcamentoRestante' => $orcamentoRestante,
            'orcamentoUsoPercentual' => $orcamentoUsoPercentual,
            'orcamentosTopEstouro' => $orcamentosTopEstouro,
            'previsao30dias' => $previsao30dias,
            'previsao60dias' => $previsao60dias,
            'previsao90dias' => $previsao90dias,
            'bankBreakdown' => $bankBreakdown,
            'bankCycleOverview' => $bankCycleOverview,
            'activityFeed' => $activityFeed,
            'recentTransactions' => $recentTransactions,
            'uploadSummary' => $uploadSummary,
            'periodComparison' => $periodComparison,
            'totalBancos' => $banks->count(),
            'bancosAtivosComGasto' => collect($bankBreakdown)->filter(fn ($item) => $item['monthly_total'] > 0)->count(),
            'activeDaysCount' => $activeDaysCount,
            'mediaReceitaDia' => $mediaReceitaDia,
            'mediaDespesaDia' => $mediaDespesaDia,
            'mediaInvoiceDia' => $mediaInvoiceDia,
            'invoicePressurePercent' => $invoicePressurePercent,
            'savingsRatePercent' => $savingsRatePercent,
            'topExpenseCategory' => $topExpenseCategory,
            'topBank' => $topBank,
        ];
    }

    public function getBanksMetrics(int $userId, int $year, int $month): array
    {
        $referenceDate = Carbon::create($year, $month, 1);
        $startOfMonth = $referenceDate->copy()->startOfMonth();
        $endOfMonth = $referenceDate->copy()->endOfMonth();
        $rollingStart = $referenceDate->copy()->subMonths(5)->startOfMonth();
        $banks = Bank::query()->where('user_id', $userId)->get();

        $bankInfo = $banks->map(function (Bank $bank) use ($userId, $year, $month, $referenceDate) {
            $totalInvoices = (float) Invoice::query()->where('user_id', $userId)->where('id_bank', $bank->id_bank)->sum('value');
            $qtdInvoices = (int) Invoice::query()->where('user_id', $userId)->where('id_bank', $bank->id_bank)->count();
            $monthTotal = (float) Invoice::query()->where('user_id', $userId)->where('id_bank', $bank->id_bank)->whereYear('invoice_date', $year)->whereMonth('invoice_date', $month)->sum('value');
            $monthCount = (int) Invoice::query()->where('user_id', $userId)->where('id_bank', $bank->id_bank)->whereYear('invoice_date', $year)->whereMonth('invoice_date', $month)->count();
            $avgInvoice = $qtdInvoices > 0 ? $totalInvoices / $qtdInvoices : 0.0;
            $maiorInvoice = Invoice::query()->where('user_id', $userId)->where('id_bank', $bank->id_bank)->orderByDesc('value')->first();
            $menorInvoice = Invoice::query()->where('user_id', $userId)->where('id_bank', $bank->id_bank)->orderBy('value')->first();
            [$cycleStart, $cycleEnd] = $this->resolveBankCycle($bank, $referenceDate);
            $currentCycleTotal = (float) Invoice::query()->where('user_id', $userId)->where('id_bank', $bank->id_bank)->whereBetween('invoice_date', [$cycleStart, $cycleEnd])->sum('value');
            $topCategory = Invoice::query()
                ->where('invoice.user_id', $userId)
                ->where('invoice.id_bank', $bank->id_bank)
                ->whereBetween('invoice_date', [$cycleStart, $cycleEnd])
                ->join('category', 'invoice.category_id', '=', 'category.id_category')
                ->selectRaw('category.name as categoria, SUM(invoice.value) as total')
                ->groupBy('category.id_category', 'category.name')
                ->orderByDesc('total')
                ->first();

            return [
                'id_bank' => $bank->id_bank,
                'nome' => $bank->name,
                'descricao' => $bank->description,
                'total_invoices' => $totalInvoices,
                'qtd_invoices' => $qtdInvoices,
                'media_invoices' => $avgInvoice,
                'maior_invoice' => $maiorInvoice,
                'menor_invoice' => $menorInvoice,
                'saldo' => -$totalInvoices,
                'saidas' => $totalInvoices,
                'month_total' => $monthTotal,
                'month_count' => $monthCount,
                'cycle_total' => $currentCycleTotal,
                'cycle_start' => $cycleStart->format('d/m'),
                'cycle_end' => $cycleEnd->format('d/m'),
                'days_to_close' => max(0, now()->startOfDay()->diffInDays($cycleEnd->copy()->startOfDay(), false)),
                'top_category' => $topCategory?->categoria,
                'top_category_total' => (float) ($topCategory->total ?? 0),
                'link' => route('invoices.index', ['bankId' => $bank->id_bank]),
            ];
        })->sortByDesc('month_total')->values();

        $monthlyTotals = Invoice::query()
            ->where('user_id', $userId)
            ->where('invoice_date', '>=', $rollingStart)
            ->where('invoice_date', '<=', $endOfMonth)
            ->selectRaw("DATE_FORMAT(invoice_date, '%Y-%m') as month_key, SUM(value) as total")
            ->groupBy('month_key')
            ->pluck('total', 'month_key');

        $trendLabels = [];
        $trendValues = [];
        foreach (range(5, 0) as $monthsAgo) {
            $date = $referenceDate->copy()->subMonths($monthsAgo);
            $key = $date->format('Y-m');
            $trendLabels[] = ucfirst($date->translatedFormat('M/y'));
            $trendValues[] = (float) ($monthlyTotals[$key] ?? 0);
        }
        $trendLabels[] = ucfirst($referenceDate->translatedFormat('M/y'));
        $trendValues[] = (float) ($monthlyTotals[$referenceDate->format('Y-m')] ?? 0);

        $topBanks = $bankInfo->take(5)->pluck('nome')->values()->all();
        $topBanksMonthValues = $bankInfo->take(5)->pluck('month_total')->map(fn ($value) => (float) $value)->values()->all();
        $topBanksCycleValues = $bankInfo->take(5)->pluck('cycle_total')->map(fn ($value) => (float) $value)->values()->all();

        $invoiceCategoryShare = Invoice::query()
            ->where('invoice.user_id', $userId)
            ->whereBetween('invoice_date', [$startOfMonth, $endOfMonth])
            ->join('category', 'invoice.category_id', '=', 'category.id_category')
            ->selectRaw('category.name as categoria, SUM(invoice.value) as total')
            ->groupBy('category.id_category', 'category.name')
            ->orderByDesc('total')
            ->limit(8)
            ->get()
            ->map(fn ($row) => ['label' => $row->categoria, 'value' => (float) $row->total])
            ->values()
            ->all();

        $recentUploads = InvoiceUploadHistory::query()
            ->where('user_id', $userId)
            ->with('bank:id_bank,name')
            ->latest()
            ->take(6)
            ->get()
            ->map(fn ($upload) => [
                'bank' => $upload->bank?->name ?? 'Banco',
                'filename' => $upload->filename,
                'status' => $upload->status,
                'total_value' => (float) ($upload->total_value ?? 0),
                'success_rate' => (float) $upload->success_rate,
                'created_at' => optional($upload->created_at)?->format('d/m H:i'),
            ])
            ->values()
            ->all();

        $monthTotal = (float) Invoice::query()->where('user_id', $userId)->whereBetween('invoice_date', [$startOfMonth, $endOfMonth])->sum('value');
        $monthCount = (int) Invoice::query()->where('user_id', $userId)->whereBetween('invoice_date', [$startOfMonth, $endOfMonth])->count();
        $avgMonth = $monthCount > 0 ? $monthTotal / $monthCount : 0.0;
        $activeBanksMonth = (int) $bankInfo->filter(fn ($item) => (float) $item['month_total'] > 0)->count();
        $topBankSummary = $bankInfo->first();
        $topCategorySummary = collect($invoiceCategoryShare)->first();
        $avgCycleAmount = $bankInfo->count() > 0 ? (float) $bankInfo->avg('cycle_total') : 0.0;
        $avgDaysToClose = $bankInfo->count() > 0 ? (float) $bankInfo->avg('days_to_close') : 0.0;
        $uploadSuccessAverage = count($recentUploads) > 0 ? (float) collect($recentUploads)->avg('success_rate') : 0.0;
        $monthDailyAverage = $referenceDate->daysInMonth > 0 ? $monthTotal / $referenceDate->daysInMonth : 0.0;

        return [
            'periodLabel' => ucfirst($referenceDate->locale('pt_BR')->translatedFormat('F/Y')),
            'bancosInfo' => $bankInfo->all(),
            'totalBancos' => $banks->count(),
            'totalInvoicesBancos' => (float) $bankInfo->sum('total_invoices'),
            'saldoTotalBancos' => (float) (-$bankInfo->sum('total_invoices')),
            'totalSaidasBancos' => (float) $bankInfo->sum('total_invoices'),
            'monthTotal' => $monthTotal,
            'monthCount' => $monthCount,
            'avgMonth' => $avgMonth,
            'trendLabels' => $trendLabels,
            'trendValues' => $trendValues,
            'topBanks' => $topBanks,
            'topBanksMonthValues' => $topBanksMonthValues,
            'topBanksCycleValues' => $topBanksCycleValues,
            'invoiceCategoryShare' => $invoiceCategoryShare,
            'recentUploads' => $recentUploads,
            'activeBanksMonth' => $activeBanksMonth,
            'topBankSummary' => $topBankSummary,
            'topCategorySummary' => $topCategorySummary,
            'avgCycleAmount' => $avgCycleAmount,
            'avgDaysToClose' => $avgDaysToClose,
            'uploadSuccessAverage' => $uploadSuccessAverage,
            'monthDailyAverage' => $monthDailyAverage,
        ];
    }

    protected function buildCofrinhosData(int $userId): array
    {
        $cofrinhos = Cofrinho::query()->where('user_id', $userId)->get();

        return [
            'cofrinhos' => $cofrinhos->map(function ($cofrinho) use ($userId) {
                $entradas = (float) Cashbook::query()->where('user_id', $userId)->where('cofrinho_id', $cofrinho->id)->where('type_id', 1)->sum('value');
                $saidas = (float) Cashbook::query()->where('user_id', $userId)->where('cofrinho_id', $cofrinho->id)->where('type_id', 2)->sum('value');
                $valorGuardado = $entradas - $saidas;
                $progresso = $cofrinho->meta_valor > 0 ? ($valorGuardado / $cofrinho->meta_valor) * 100 : 0.0;

                return [
                    'id' => $cofrinho->id,
                    'nome' => $cofrinho->nome,
                    'meta_valor' => (float) $cofrinho->meta_valor,
                    'valor_guardado' => $valorGuardado,
                    'progresso' => round($progresso, 2),
                    'status' => $cofrinho->status,
                    'icone' => $cofrinho->icone,
                    'link' => route('cofrinhos.show', $cofrinho->id),
                ];
            })->values()->all(),
        ];
    }

    protected function buildCofrinhosEvolution(int $userId, int $year): array
    {
        $result = array_fill(0, 12, 0.0);

        for ($month = 1; $month <= 12; $month++) {
            $entradas = (float) Cashbook::query()
                ->where('user_id', $userId)
                ->whereNotNull('cofrinho_id')
                ->where('type_id', 1)
                ->whereYear('date', $year)
                ->whereMonth('date', '<=', $month)
                ->sum('value');

            $saidas = (float) Cashbook::query()
                ->where('user_id', $userId)
                ->whereNotNull('cofrinho_id')
                ->where('type_id', 2)
                ->whereYear('date', $year)
                ->whereMonth('date', '<=', $month)
                ->sum('value');

            $result[$month - 1] = $entradas - $saidas;
        }

        return $result;
    }

    protected function buildBudgetOverruns(int $userId, int $year, int $month): array
    {
        $orcamentosMes = Orcamento::query()
            ->where('user_id', $userId)
            ->where('mes', $month)
            ->where('ano', $year)
            ->with('category:id_category,name')
            ->get();

        $gastosPorCategoriaMes = Cashbook::query()
            ->where('user_id', $userId)
            ->where('type_id', 2)
            ->whereNull('cofrinho_id')
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->selectRaw('category_id, SUM(value) as total')
            ->groupBy('category_id')
            ->get()
            ->keyBy('category_id');

        $estouros = [];
        foreach ($orcamentosMes as $orcamento) {
            $gasto = (float) ($gastosPorCategoriaMes[$orcamento->category_id]->total ?? 0);
            $orcado = (float) $orcamento->valor;
            $diff = $gasto - $orcado;
            if ($diff > 0) {
                $estouros[] = [
                    'category' => $orcamento->category?->name ?? ('Categoria ' . $orcamento->category_id),
                    'estouro' => $diff,
                    'gasto' => $gasto,
                    'orcado' => $orcado,
                ];
            }
        }

        usort($estouros, fn ($left, $right) => $right['estouro'] <=> $left['estouro']);
        return array_slice($estouros, 0, 5);
    }

    protected function buildForecasts(int $userId, float $saldoAtual): array
    {
        $hoje = now();
        $inicio = $hoje->copy()->subDays(90);

        $transacoes = Cashbook::query()
            ->where('user_id', $userId)
            ->whereBetween('date', [$inicio, $hoje])
            ->selectRaw('type_id, SUM(value) as total')
            ->groupBy('type_id')
            ->get()
            ->keyBy('type_id');

        $receitas90dias = (float) ($transacoes[1]->total ?? 0);
        $despesas90dias = (float) ($transacoes[2]->total ?? 0);
        $mediaDiariaReceitas = $receitas90dias / 90;
        $mediaDiariaDespesas = $despesas90dias / 90;
        $deltaDiario = $mediaDiariaReceitas - $mediaDiariaDespesas;

        return [
            $saldoAtual + ($deltaDiario * 30),
            $saldoAtual + ($deltaDiario * 60),
            $saldoAtual + ($deltaDiario * 90),
        ];
    }

    protected function buildBankCycleOverview(Collection $banks, int $userId, Carbon $referenceDate): array
    {
        return $banks->map(function (Bank $bank) use ($userId, $referenceDate) {
            [$cycleStart, $cycleEnd] = $this->resolveBankCycle($bank, $referenceDate);
            $cycleTotal = (float) Invoice::query()
                ->where('user_id', $userId)
                ->where('id_bank', $bank->id_bank)
                ->whereBetween('invoice_date', [$cycleStart, $cycleEnd])
                ->sum('value');

            return [
                'id_bank' => $bank->id_bank,
                'name' => $bank->name,
                'cycle_total' => $cycleTotal,
                'cycle_start' => $cycleStart->format('d/m'),
                'cycle_end' => $cycleEnd->format('d/m'),
                'days_to_close' => max(0, now()->startOfDay()->diffInDays($cycleEnd->copy()->startOfDay(), false)),
                'link' => route('invoices.index', ['bankId' => $bank->id_bank]),
            ];
        })->sortByDesc('cycle_total')->take(6)->values()->all();
    }

    protected function buildFinanceActivityFeed(int $userId, ?int $cofrinhoFiltro = null): array
    {
        $cashbookQuery = Cashbook::query()
            ->where('user_id', $userId)
            ->with(['category:id_category,name', 'cofrinho:id,nome'])
            ->latest('date')
            ->limit(6);

        if ($cofrinhoFiltro) {
            $cashbookQuery->where('cofrinho_id', $cofrinhoFiltro);
        }

        $cashbookItems = $cashbookQuery->get()->map(fn ($item) => [
            'date' => Carbon::parse($item->date),
            'icon' => (int) $item->type_id === 1 ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down',
            'tone' => (int) $item->type_id === 1 ? 'emerald' : 'rose',
            'title' => $item->description ?: ((int) $item->type_id === 1 ? 'Entrada registrada' : 'Saida registrada'),
            'meta' => $item->cofrinho?->nome ?? $item->category?->name ?? 'Cashbook',
            'value' => (float) $item->value,
        ]);

        $invoiceItems = Invoice::query()
            ->where('user_id', $userId)
            ->with(['category:id_category,name', 'bank:id_bank,name'])
            ->latest('invoice_date')
            ->limit(6)
            ->get()
            ->map(fn ($item) => [
                'date' => Carbon::parse($item->invoice_date),
                'icon' => 'fa-file-invoice-dollar',
                'tone' => 'amber',
                'title' => $item->description ?: 'Invoice registrada',
                'meta' => ($item->bank?->name ?? 'Banco') . ' · ' . ($item->category?->name ?? 'Sem categoria'),
                'value' => (float) $item->value,
            ]);

        $uploadItems = InvoiceUploadHistory::query()
            ->where('user_id', $userId)
            ->with('bank:id_bank,name')
            ->latest()
            ->limit(3)
            ->get()
            ->map(fn ($item) => [
                'date' => $item->created_at ?? now(),
                'icon' => 'fa-cloud-arrow-up',
                'tone' => $item->status === 'failed' ? 'rose' : 'cyan',
                'title' => 'Upload de invoice ' . ($item->status === 'completed' ? 'concluido' : $item->status),
                'meta' => $item->bank?->name ?? 'Banco',
                'value' => (float) ($item->total_value ?? 0),
            ]);

        return $cashbookItems
            ->concat($invoiceItems)
            ->concat($uploadItems)
            ->sortByDesc('date')
            ->take(12)
            ->map(fn ($item) => [
                'date' => $item['date'] instanceof Carbon ? $item['date']->format('d/m H:i') : (string) $item['date'],
                'icon' => $item['icon'],
                'tone' => $item['tone'],
                'title' => $item['title'],
                'meta' => $item['meta'],
                'value' => $item['value'],
            ])
            ->values()
            ->all();
    }

    public function buildRecentTransactions(int $userId, ?string $filterType = null, ?int $cofrinhoFiltro = null): array
    {
        $cashbookQuery = Cashbook::query()->where('user_id', $userId)->with('category');
        if ($filterType === 'receitas') {
            $cashbookQuery->where('type_id', 1);
        } elseif ($filterType === 'despesas') {
            $cashbookQuery->where('type_id', 2);
        }
        if ($cofrinhoFiltro) {
            $cashbookQuery->where('cofrinho_id', $cofrinhoFiltro);
        }

        $cashbook = $cashbookQuery->latest('date')->limit(10)->get()->map(fn ($item) => [
            'description' => $item->description,
            'value' => (float) $item->value,
            'type_id' => (int) $item->type_id,
            'date' => Carbon::parse($item->date)->format('Y-m-d H:i:s'),
            'origin' => 'cashbook',
            'meta' => $item->category?->name,
        ]);

        if ($filterType === 'invoices') {
            return Invoice::query()->where('user_id', $userId)->with(['category:id_category,name', 'bank:id_bank,name'])->latest('invoice_date')->limit(10)->get()->map(fn ($item) => [
                'description' => $item->description,
                'value' => (float) $item->value,
                'type_id' => 2,
                'date' => Carbon::parse($item->invoice_date)->format('Y-m-d H:i:s'),
                'origin' => 'invoice',
                'meta' => trim(($item->bank?->name ?? 'Banco') . ' · ' . ($item->category?->name ?? 'Sem categoria'), ' ·'),
            ])->values()->all();
        }

        if ($filterType === null) {
            $invoices = Invoice::query()->where('user_id', $userId)->with(['category:id_category,name', 'bank:id_bank,name'])->latest('invoice_date')->limit(5)->get()->map(fn ($item) => [
                'description' => $item->description,
                'value' => (float) $item->value,
                'type_id' => 2,
                'date' => Carbon::parse($item->invoice_date)->format('Y-m-d H:i:s'),
                'origin' => 'invoice',
                'meta' => trim(($item->bank?->name ?? 'Banco') . ' · ' . ($item->category?->name ?? 'Sem categoria'), ' ·'),
            ]);

            return $cashbook->concat($invoices)->sortByDesc('date')->take(10)->values()->all();
        }

        return $cashbook->values()->all();
    }

    protected function buildUploadSummary(int $userId): array
    {
        $cashbookUploads = CashbookUploadHistory::query()->forUser($userId)->latest()->take(3)->get()->map(fn ($item) => [
            'type' => 'cashbook',
            'name' => $item->file_name,
            'status' => $item->status,
            'created_at' => optional($item->created_at)?->format('d/m H:i'),
            'total' => (int) ($item->transactions_created ?? 0),
        ]);

        $invoiceUploads = InvoiceUploadHistory::query()->where('user_id', $userId)->latest()->take(3)->get()->map(fn ($item) => [
            'type' => 'invoice',
            'name' => $item->filename,
            'status' => $item->status,
            'created_at' => optional($item->created_at)?->format('d/m H:i'),
            'total' => (int) ($item->total_transactions ?? 0),
        ]);

        return $cashbookUploads->concat($invoiceUploads)->sortByDesc('created_at')->take(6)->values()->all();
    }

    protected function resolveBankCycle(Bank $bank, Carbon $referenceDate): array
    {
        $startDay = $bank->start_date ? Carbon::parse($bank->start_date)->day : 1;
        $endDay = $bank->end_date ? Carbon::parse($bank->end_date)->day : $referenceDate->copy()->endOfMonth()->day;

        if ($startDay > $endDay) {
            $cycleStart = Carbon::create($referenceDate->year, $referenceDate->month, $startDay)->startOfDay();
            $cycleEnd = Carbon::create($referenceDate->year, $referenceDate->month, $startDay)->addMonth()->day($endDay)->endOfDay();
        } else {
            $cycleStart = Carbon::create($referenceDate->year, $referenceDate->month, $startDay)->startOfDay();
            $cycleEnd = Carbon::create($referenceDate->year, $referenceDate->month, $endDay)->endOfDay();
        }

        return [$cycleStart, $cycleEnd];
    }
}