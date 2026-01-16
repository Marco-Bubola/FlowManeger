<div class="w-full h-full bg-slate-900 text-white p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Dashboard de Bancos</h1>
        <a href="{{ route('dashboard.cashbook') }}" class="text-indigo-400 hover:text-indigo-300">Voltar</a>
    </div>

    <!-- KPIs -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <div class="bg-slate-800 rounded-xl p-4">
            <p class="text-sm text-slate-400">Total de Bancos</p>
            <p class="text-2xl font-bold">{{ $totalBancos }}</p>
        </div>
        <div class="bg-slate-800 rounded-xl p-4">
            <p class="text-sm text-slate-400">Total Invoices</p>
            <p class="text-2xl font-bold text-red-500">R$ {{ number_format($totalInvoicesBancos, 2, ',', '.') }}</p>
        </div>
        <div class="bg-slate-800 rounded-xl p-4">
            <p class="text-sm text-slate-400">Saldo Total</p>
            <p class="text-2xl font-bold {{ $saldoTotalBancos >= 0 ? 'text-green-500' : 'text-red-500' }}">
                R$ {{ number_format($saldoTotalBancos, 2, ',', '.') }}
            </p>
        </div>
        <div class="bg-slate-800 rounded-xl p-4">
            <p class="text-sm text-slate-400">Total Saídas</p>
            <p class="text-2xl font-bold text-red-500">R$ {{ number_format($totalSaidasBancos, 2, ',', '.') }}</p>
        </div>
    </div>

    <!-- Bank Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($bancosInfo as $banco)
            <div class="bg-slate-800 rounded-xl p-6">
                <h3 class="text-lg font-bold mb-4">{{ $banco['nome'] }}</h3>
                <p class="text-sm text-slate-400 mb-4">{{ $banco['descricao'] }}</p>

                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-sm">Qtd. Invoices:</span>
                        <span class="font-bold">{{ $banco['qtd_invoices'] }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm">Total Invoices:</span>
                        <span class="font-bold text-red-500">R$ {{ number_format($banco['total_invoices'], 2, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm">Média por Invoice:</span>
                        <span class="font-bold">R$ {{ number_format($banco['media_invoices'], 2, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm">Maior Invoice:</span>
                        <span class="font-bold">R$ {{ $banco['maior_invoice'] ? number_format($banco['maior_invoice']['value'], 2, ',', '.') : 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm">Menor Invoice:</span>
                        <span class="font-bold">R$ {{ $banco['menor_invoice'] ? number_format($banco['menor_invoice']['value'], 2, ',', '.') : 'N/A' }}</span>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
