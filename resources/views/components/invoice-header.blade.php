@props([
    'title' => 'Transações Financeiras',
    'description' => 'Gerencie suas finanças com estilo e eficiência',
    'totalTransactions' => 0,
    'totalExpenses' => 0,
    'bankId' => null,
    'showQuickActions' => true
])

<!-- Header Moderno para Invoices (estilo consistente com cashbook) -->
<div class="relative overflow-hidden bg-gradient-to-r from-white/80 via-purple-50/90 to-pink-50/80 dark:from-slate-800/90 dark:via-purple-900/30 dark:to-pink-900/30 backdrop-blur-xl border-b border-white/20 dark:border-slate-700/50 rounded-2xl shadow-xl mb-4">
    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent dark:via-white/5"></div>
    <div class="absolute top-0 right-0 w-28 h-28 bg-gradient-to-br from-purple-400/20 via-pink-400/20 to-rose-400/20 rounded-full transform translate-x-12 -translate-y-12"></div>
    <div class="absolute bottom-0 left-0 w-24 h-24 bg-gradient-to-tr from-emerald-400/10 via-purple-400/10 to-pink-400/10 rounded-full transform -translate-x-8 translate-y-8"></div>

    <div class="relative px-6 py-4">
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
            <div class="flex items-center gap-4">
                <div class="relative flex items-center justify-center w-12 h-12 bg-gradient-to-br from-purple-500 via-pink-500 to-rose-500 rounded-xl shadow-md shadow-purple-400/20">
                    <i class="fas fa-file-invoice-dollar text-white text-2xl"></i>
                    <div class="absolute inset-0 rounded-xl bg-gradient-to-r from-white/15 to-transparent opacity-40"></div>
                </div>

                <div class="space-y-1">
                    <h1 class="text-2xl lg:text-3xl font-bold text-slate-800 dark:text-slate-100">
                        {{ $title }}
                    </h1>
                    <p class="text-sm text-slate-600 dark:text-slate-400">{{ $description }}</p>

                    <div class="flex items-center gap-3 mt-2">
                        <div class="flex items-center gap-2 px-2 py-1 bg-gradient-to-r from-red-500/15 to-pink-500/15 rounded-lg border border-red-200 dark:border-red-700">
                            <i class="fas fa-receipt text-red-600 dark:text-red-400 text-sm"></i>
                            <span class="text-sm font-medium text-red-700 dark:text-red-300">{{ $totalTransactions }} transações</span>
                        </div>

                        <div class="flex items-center gap-2 px-2 py-1 bg-gradient-to-r from-purple-500/15 to-pink-500/15 rounded-lg border border-purple-200 dark:border-purple-700">
                            <i class="fas fa-wallet text-purple-600 dark:text-purple-400 text-sm"></i>
                            <span class="text-sm font-medium text-purple-700 dark:text-purple-300">R$ {{ number_format($totalExpenses, 2, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            @if($showQuickActions && $bankId)
            <div class="flex flex-wrap gap-2 items-center">
                <a href="{{ route('invoices.create', ['bankId' => $bankId]) }}"
                   class="group relative inline-flex items-center justify-center px-4 py-2 bg-gradient-to-br from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white font-semibold rounded-lg transition-all duration-200 shadow-md hover:shadow-lg">
                    <i class="fas fa-plus mr-2 group-hover:scale-110 transition-transform duration-150"></i>
                    <span class="text-sm">Nova</span>
                </a>

                <a href="{{ route('invoices.upload', ['bankId' => $bankId]) }}"
                   class="group relative inline-flex items-center justify-center px-4 py-2 bg-gradient-to-br from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white font-semibold rounded-lg transition-all duration-200 shadow-md hover:shadow-lg">
                    <i class="fas fa-upload mr-2 group-hover:scale-110 transition-transform duration-150"></i>
                    <span class="text-sm">Upload</span>
                </a>
            </div>
            @endif
        </div>
    </div>
</div>
