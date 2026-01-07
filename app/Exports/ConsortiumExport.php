<?php

namespace App\Exports;

use App\Models\Consortium;
use App\Models\ConsortiumParticipant;
use Illuminate\Support\Collection;

class ConsortiumExport
{
    protected $consortiumId;
    protected $clientId;
    protected $exportType;

    public function __construct($consortiumId = null, $clientId = null, $exportType = 'full')
    {
        $this->consortiumId = $consortiumId;
        $this->clientId = $clientId;
        $this->exportType = $exportType;
    }

    /**
     * Retorna dados completos do consórcio para Excel
     */
    public function collection()
    {
        if ($this->exportType === 'by_client' && $this->clientId) {
            return $this->exportByClient();
        }

        if ($this->exportType === 'by_client_consortium' && $this->clientId && $this->consortiumId) {
            return $this->exportByClientInConsortium();
        }

        if ($this->consortiumId) {
            return $this->exportSingleConsortium();
        }

        return $this->exportAllConsortiums();
    }

    /**
     * Exporta todos os consórcios (resumo)
     */
    protected function exportAllConsortiums()
    {
        $consortiums = Consortium::with(['participants', 'draws'])->get();

        $data = $consortiums->map(function ($consortium) {
            return [
                'ID' => $consortium->id,
                'Nome' => $consortium->name,
                'Status' => $consortium->status_label,
                'Valor Mensal' => 'R$ ' . number_format($consortium->monthly_value, 2, ',', '.'),
                'Duração' => $consortium->duration_months . ' meses',
                'Total Participantes' => $consortium->participants->count(),
                'Participantes Ativos' => $consortium->participants->where('status', 'active')->count(),
                'Contemplados' => $consortium->participants->where('is_contemplated', true)->count(),
                'Total Arrecadado' => 'R$ ' . number_format($consortium->participants->sum('total_paid'), 2, ',', '.'),
                'Data Início' => $consortium->start_date?->format('d/m/Y') ?? '-',
                'Frequência Sorteios' => $consortium->draw_frequency_label,
                'Total Sorteios' => $consortium->draws->count(),
            ];
        });

        return new Collection($data->toArray());
    }

    /**
     * Exporta um consórcio específico com detalhes dos participantes
     */
    protected function exportSingleConsortium()
    {
        $participants = ConsortiumParticipant::with(['client', 'payments', 'contemplation'])
            ->where('consortium_id', $this->consortiumId)
            ->get();

        $data = $participants->map(function ($participant) {
            $totalParcelas = $participant->payments->count();
            $parcelasPagas = $participant->payments->where('status', 'paid')->count();
            $parcelasVencidas = $participant->payments->where('status', 'overdue')->count();
            $valorPendente = $participant->payments->whereIn('status', ['pending', 'overdue'])->sum('amount');

            return [
                'Número' => $participant->participation_number,
                'Cliente' => $participant->client->name ?? '-',
                'CPF/CNPJ' => $participant->client->cpf ?? $participant->client->cnpj ?? '-',
                'Status' => $participant->status_label,
                'Contemplado' => $participant->is_contemplated ? 'Sim' : 'Não',
                'Data Contemplação' => $participant->contemplation?->contemplation_date?->format('d/m/Y') ?? '-',
                'Sorteio' => $participant->contemplation?->draw->draw_number ?? '-',
                'Produtos Resgatados' => $participant->contemplation?->products->count() ?? 0,
                'Data Entrada' => $participant->entry_date?->format('d/m/Y') ?? '-',
                'Total Parcelas' => $totalParcelas,
                'Parcelas Pagas' => $parcelasPagas,
                'Parcelas Vencidas' => $parcelasVencidas,
                'Total Pago' => 'R$ ' . number_format($participant->total_paid, 2, ',', '.'),
                'Valor Pendente' => 'R$ ' . number_format($valorPendente, 2, ',', '.'),
                'Progresso' => $totalParcelas > 0 ? round(($parcelasPagas / $totalParcelas) * 100, 1) . '%' : '0%',
            ];
        });

        return new Collection($data->toArray());
    }

    /**
     * Exporta consórcios de um cliente específico
     */
    protected function exportByClient()
    {
        $participations = ConsortiumParticipant::with(['consortium', 'payments', 'contemplation.draw', 'contemplation.products'])
            ->where('client_id', $this->clientId)
            ->get();

        $data = $participations->map(function ($participation) {
            $totalParcelas = $participation->payments->count();
            $parcelasPagas = $participation->payments->where('status', 'paid')->count();
            $parcelasVencidas = $participation->payments->where('status', 'overdue')->count();
            $valorPendente = $participation->payments->whereIn('status', ['pending', 'overdue'])->sum('amount');

            $produtos = '';
            if ($participation->contemplation && $participation->contemplation->products->count() > 0) {
                $produtos = $participation->contemplation->products->pluck('name')->implode(', ');
            }

            return [
                'Consórcio' => $participation->consortium->name,
                'Número Participação' => $participation->participation_number,
                'Status' => $participation->status_label,
                'Contemplado' => $participation->is_contemplated ? 'Sim' : 'Não',
                'Data Contemplação' => $participation->contemplation?->contemplation_date?->format('d/m/Y') ?? '-',
                'Número Sorteio' => $participation->contemplation?->draw->draw_number ?? '-',
                'Produtos Resgatados' => $produtos ?: '-',
                'Valor Produtos' => $participation->contemplation ? 'R$ ' . number_format($participation->contemplation->products->sum('price'), 2, ',', '.') : '-',
                'Data Entrada' => $participation->entry_date?->format('d/m/Y') ?? '-',
                'Valor Mensal' => 'R$ ' . number_format($participation->consortium->monthly_value, 2, ',', '.'),
                'Duração' => $participation->consortium->duration_months . ' meses',
                'Total Parcelas' => $totalParcelas,
                'Parcelas Pagas' => $parcelasPagas,
                'Parcelas Vencidas' => $parcelasVencidas,
                'Total Pago' => 'R$ ' . number_format($participation->total_paid, 2, ',', '.'),
                'Valor Pendente' => 'R$ ' . number_format($valorPendente, 2, ',', '.'),
                'Progresso' => $totalParcelas > 0 ? round(($parcelasPagas / $totalParcelas) * 100, 1) . '%' : '0%',
            ];
        });

        return new Collection($data->toArray());
    }

    /**
     * Exporta consórcios de um cliente dentro de um consórcio específico
     */
    protected function exportByClientInConsortium()
    {
        $participations = ConsortiumParticipant::with(['consortium', 'payments', 'contemplation.draw', 'contemplation.products'])
            ->where('client_id', $this->clientId)
            ->where('consortium_id', $this->consortiumId)
            ->get();

        $data = $participations->map(function ($participation) {
            $totalParcelas = $participation->payments->count();
            $parcelasPagas = $participation->payments->where('status', 'paid')->count();
            $parcelasVencidas = $participation->payments->where('status', 'overdue')->count();
            $valorPendente = $participation->payments->whereIn('status', ['pending', 'overdue'])->sum('amount');

            $produtos = '';
            if ($participation->contemplation && $participation->contemplation->products->count() > 0) {
                $produtos = $participation->contemplation->products->pluck('name')->implode(', ');
            }

            return [
                'Consórcio' => $participation->consortium->name,
                'Número Participação' => $participation->participation_number,
                'Status' => $participation->status_label,
                'Contemplado' => $participation->is_contemplated ? 'Sim' : 'Não',
                'Data Contemplação' => $participation->contemplation?->contemplation_date?->format('d/m/Y') ?? '-',
                'Número Sorteio' => $participation->contemplation?->draw->draw_number ?? '-',
                'Produtos Resgatados' => $produtos ?: '-',
                'Valor Produtos' => $participation->contemplation ? 'R$ ' . number_format($participation->contemplation->products->sum('price'), 2, ',', '.') : '-',
                'Data Entrada' => $participation->entry_date?->format('d/m/Y') ?? '-',
                'Valor Mensal' => 'R$ ' . number_format($participation->consortium->monthly_value, 2, ',', '.'),
                'Duração' => $participation->consortium->duration_months . ' meses',
                'Total Parcelas' => $totalParcelas,
                'Parcelas Pagas' => $parcelasPagas,
                'Parcelas Vencidas' => $parcelasVencidas,
                'Total Pago' => 'R$ ' . number_format($participation->total_paid, 2, ',', '.'),
                'Valor Pendente' => 'R$ ' . number_format($valorPendente, 2, ',', '.'),
                'Progresso' => $totalParcelas > 0 ? round(($parcelasPagas / $totalParcelas) * 100, 1) . '%' : '0%',
            ];
        });

        return new Collection($data->toArray());
    }

    /**
     * Retorna cabeçalhos para Excel
     */
    public function headings(): array
    {
        if ($this->exportType === 'by_client') {
            return [
                'Consórcio', 'Número Participação', 'Status', 'Contemplado', 'Data Contemplação',
                'Número Sorteio', 'Produtos Resgatados', 'Valor Produtos', 'Data Entrada',
                'Valor Mensal', 'Duração', 'Total Parcelas', 'Parcelas Pagas', 'Parcelas Vencidas',
                'Total Pago', 'Valor Pendente', 'Progresso'
            ];
        }

        if ($this->exportType === 'by_client_consortium') {
            return [
                'Consórcio', 'Número Participação', 'Status', 'Contemplado', 'Data Contemplação',
                'Número Sorteio', 'Produtos Resgatados', 'Valor Produtos', 'Data Entrada',
                'Valor Mensal', 'Duração', 'Total Parcelas', 'Parcelas Pagas', 'Parcelas Vencidas',
                'Total Pago', 'Valor Pendente', 'Progresso'
            ];
        }

        if ($this->consortiumId) {
            return [
                'Número', 'Cliente', 'CPF/CNPJ', 'Status', 'Contemplado', 'Data Contemplação',
                'Sorteio', 'Produtos Resgatados', 'Data Entrada', 'Total Parcelas',
                'Parcelas Pagas', 'Parcelas Vencidas', 'Total Pago', 'Valor Pendente', 'Progresso'
            ];
        }

        return [
            'ID', 'Nome', 'Status', 'Valor Mensal', 'Duração', 'Total Participantes',
            'Participantes Ativos', 'Contemplados', 'Total Arrecadado', 'Data Início',
            'Frequência Sorteios', 'Total Sorteios'
        ];
    }

    /**
     * Retorna dados formatados para PDF
     */
    public function getPdfData()
    {
        if ($this->exportType === 'by_client' && $this->clientId) {
            return $this->getPdfDataByClient();
        }

        if ($this->exportType === 'by_client_consortium' && $this->clientId && $this->consortiumId) {
            return $this->getPdfDataByClientInConsortium();
        }

        if ($this->consortiumId) {
            return $this->getPdfDataSingleConsortium();
        }

        return null;
    }

    /**
     * Dados para PDF de um consórcio específico
     */
    protected function getPdfDataSingleConsortium()
    {
        $consortium = Consortium::with([
            'participants.client',
            'participants.payments',
            'participants.contemplation.draw',
            'participants.contemplation.products',
            'draws'
        ])->findOrFail($this->consortiumId);

        return [
            'consortium' => $consortium,
            'statistics' => [
                'total_participants' => $consortium->participants->count(),
                'active_participants' => $consortium->participants->where('status', 'active')->count(),
                'contemplated' => $consortium->participants->where('is_contemplated', true)->count(),
                'total_collected' => $consortium->participants->sum('total_paid'),
                'total_payments' => $consortium->participants->flatMap->payments->count(),
                'paid_payments' => $consortium->participants->flatMap->payments->where('status', 'paid')->count(),
                'overdue_payments' => $consortium->participants->flatMap->payments->where('status', 'overdue')->count(),
                'total_draws' => $consortium->draws->count(),
            ],
            'participants' => $consortium->participants
        ];
    }

    /**
     * Dados para PDF de consórcios de um cliente
     */
    protected function getPdfDataByClient()
    {
        $participations = ConsortiumParticipant::with([
            'consortium',
            'payments',
            'contemplation.draw',
            'contemplation.products'
        ])
        ->where('client_id', $this->clientId)
        ->get();

        $client = $participations->first()?->client;

        $allPayments = $participations->flatMap->payments;

        return [
            'client' => $client,
            'participations' => $participations,
            'statistics' => [
                'total_consortiums' => $participations->count(),
                'active_consortiums' => $participations->where('status', 'active')->count(),
                'contemplated' => $participations->where('is_contemplated', true)->count(),
                'total_paid' => $participations->sum('total_paid'),
                'total_payments' => $allPayments->count(),
                'paid_payments' => $allPayments->where('status', 'paid')->count(),
                'pending_payments' => $allPayments->where('status', 'pending')->count(),
                'overdue_payments' => $allPayments->where('status', 'overdue')->count(),
            ]
        ];
    }

    /**
     * Dados para PDF de consórcios de um cliente em um consórcio específico
     */
    protected function getPdfDataByClientInConsortium()
    {
        $participations = ConsortiumParticipant::with([
            'consortium',
            'payments',
            'contemplation.draw',
            'contemplation.products'
        ])
        ->where('client_id', $this->clientId)
        ->where('consortium_id', $this->consortiumId)
        ->get();

        $client = $participations->first()?->client;

        $allPayments = $participations->flatMap->payments;

        return [
            'client' => $client,
            'participations' => $participations,
            'statistics' => [
                'total_consortiums' => $participations->count(),
                'active_consortiums' => $participations->where('status', 'active')->count(),
                'contemplated' => $participations->where('is_contemplated', true)->count(),
                'total_paid' => $participations->sum('total_paid'),
                'total_payments' => $allPayments->count(),
                'paid_payments' => $allPayments->where('status', 'paid')->count(),
                'pending_payments' => $allPayments->where('status', 'pending')->count(),
                'overdue_payments' => $allPayments->where('status', 'overdue')->count(),
            ]
        ];
    }
}
