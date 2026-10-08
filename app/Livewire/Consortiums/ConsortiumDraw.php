<?php

namespace App\Livewire\Consortiums;

use App\Models\Consortium;
use App\Models\ConsortiumDraw as ConsortiumDrawModel;
use App\Models\ConsortiumContemplation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class ConsortiumDraw extends Component
{
    public Consortium $consortium;
    public $eligibleParticipants = [];
    public $selectedWinner = null;
    public $drawNumber;
    public $drawDate;
    public $showDrawModal = false;
    public $showWinnerModal = false;
    public $showRedemptionModal = false;
    public $redemptionType = 'pending';
    public $isDrawing = false;

    public function mount(Consortium $consortium)
    {
        // Verificar se o usuário é dono do consórcio
        $consortium->authorizeOwner();

        if ($consortium->mode === 'payoff') {
            session()->flash('error', 'Este consórcio é por quitação: não tem sorteio.');
            $this->redirectRoute('consortiums.show', $consortium);
            return;
        }

        $this->consortium = $consortium;
        $this->drawDate = now()->format('Y-m-d\TH:i');

        // Calcular próximo número do sorteio
        $this->drawNumber = $consortium->draws()->count() + 1;

        // Carregar participantes elegíveis
        $this->loadEligibleParticipants();
    }

    public function loadEligibleParticipants()
    {
        // Participantes elegíveis: ativos, não contemplados
        // Em dia (nada vencido há mais de 30 dias) e com ao menos 1 parcela paga.
        $this->eligibleParticipants = $this->consortium->participants()
            ->with('client')
            ->where('status', 'active')
            ->where('is_contemplated', false)
            ->withCount([
                'payments as late_count' => fn ($q) => $q->where('status', 'pending')->where('due_date', '<', now()->subDays(30)),
                'payments as paid_count' => fn ($q) => $q->where('status', 'paid'),
            ])
            ->get()
            ->filter(fn ($p) => $p->late_count == 0 && $p->paid_count > 0)
            ->values();
    }

    public function confirmDraw()
    {
        // Verificar elegibilidade dos participantes primeiro
        if ($this->eligibleParticipants->isEmpty()) {
            session()->flash('error', 'Não há participantes elegíveis para o sorteio. Participantes devem estar ativos, não contemplados, ter pelo menos 1 pagamento realizado e não ter atrasos maiores que 30 dias.');
            return;
        }

        // Verificar se pode realizar sorteio
        $canPerformResult = $this->consortium->canPerformDraw();
        if (!$canPerformResult) {
            // Fornecer mensagem mais específica
            if ($this->consortium->status !== 'active') {
                session()->flash('error', 'O consórcio não está ativo. Status atual: ' . $this->consortium->status_label);
                return;
            }

            if ($this->consortium->active_participants_count === 0) {
                session()->flash('error', 'Não há participantes ativos no consórcio.');
                return;
            }

            if (now()->lt($this->consortium->start_date)) {
                session()->flash('error', 'O consórcio ainda não iniciou. Data de início: ' . \Carbon\Carbon::parse($this->consortium->start_date)->format('d/m/Y'));
                return;
            }

            // Verificar frequência
            $faltam = $this->consortium->daysUntilNextDraw();
            if ($faltam > 0) {
                session()->flash('error', "O próximo sorteio libera em {$faltam} dia(s). Frequência: " . $this->consortium->draw_frequency_label . '.');
                return;
            }

            session()->flash('error', 'Não é possível realizar sorteio neste momento.');
            return;
        }

        // Abrir modal de confirmação
        $this->showDrawModal = true;
    }

    public function performDraw()
    {
        // Fechar modal de confirmação e iniciar animação
        $this->showDrawModal = false;
        $this->isDrawing = true;
    }

    public function executeDraw()
    {
        if (!$this->consortium->fresh()->canPerformDraw()) {
            session()->flash('error', 'O sorteio não está liberado agora.');
            $this->isDrawing = false;
            return;
        }

        // Apenas seleciona o vencedor, NÃO salva no banco ainda
        if ($this->eligibleParticipants->isEmpty()) {
            session()->flash('error', 'Não há participantes elegíveis para o sorteio.');
            $this->isDrawing = false;
            return;
        }

        // Selecionar participante vencedor aleatoriamente
        $winner = $this->eligibleParticipants->random();
        $this->selectedWinner = $winner;
        $this->isDrawing = false;
    }

    public function confirmWinner()
    {
        // Agora sim, salvar no banco de dados
        try {
            if (!$this->selectedWinner) {
                session()->flash('error', 'Nenhum vencedor selecionado.');
                return;
            }

            DB::beginTransaction();

            // Trava o consórcio: clique duplo não gera dois sorteios.
            $consortium = Consortium::whereKey($this->consortium->id)->lockForUpdate()->first();
            if (!$consortium->canPerformDraw()) {
                DB::rollBack();
                session()->flash('error', 'O sorteio já foi feito ou não está liberado agora.');
                return redirect()->route('consortiums.show', $consortium);
            }

            $winner = $consortium->participants()->whereKey($this->selectedWinner->id)
                ->where('status', 'active')->where('is_contemplated', false)->first();
            if (!$winner) {
                DB::rollBack();
                session()->flash('error', 'Este participante não pode mais ser contemplado. Faça o sorteio de novo.');
                $this->resetDraw();
                return;
            }

            // Criar registro do sorteio
            $draw = ConsortiumDrawModel::create([
                'consortium_id' => $consortium->id,
                'draw_date' => $this->drawDate,
                'draw_number' => $consortium->draws()->count() + 1,
                'winner_participant_id' => $winner->id,
                'status' => 'completed',
            ]);

            // Atualizar participante como contemplado
            $winner->update([
                'is_contemplated' => true,
                'status' => 'contemplated',
                'contemplation_date' => now(),
                'contemplation_type' => 'draw',
            ]);

            // Criar registro de contemplação
            $contemplation = ConsortiumContemplation::create([
                'consortium_participant_id' => $winner->id,
                'draw_id' => $draw->id,
                'contemplation_type' => 'draw',
                'contemplation_date' => now(),
                'redemption_type' => $this->redemptionType ?? 'pending',
                'status' => 'pending', // Sempre pending até que o resgate seja efetivado
            ]);

            DB::commit();

            // Fechar modal de resgate
            $this->showRedemptionModal = false;
            $this->selectedWinner = null;

            // Atualizar lista de elegíveis
            $this->loadEligibleParticipants();

            // Incrementar número do próximo sorteio
            $this->drawNumber = $this->consortium->draws()->count() + 1;

            session()->flash('success', 'Sorteio confirmado e salvo com sucesso!');

            // Redirecionar para a página de detalhes do consórcio
            return redirect()->route('consortiums.show', $this->consortium);
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Erro ao confirmar sorteio: ' . $e->getMessage());
        }
    }

    public function openRedemptionModal()
    {
        $this->showWinnerModal = false;
        $this->showRedemptionModal = true;
    }

    public function saveRedemption()
    {
        $this->validate([
            'redemptionType' => 'required|in:cash,products,pending',
        ]);

        // Confirmar o sorteio com o tipo de resgate escolhido
        $this->confirmWinner();
    }

    public function cancelDraw()
    {
        $this->showDrawModal = false;
    }

    public function closeWinnerModal()
    {
        $this->showWinnerModal = false;
        $this->selectedWinner = null;
    }

    public function resetDraw()
    {
        // Resetar o sorteio para permitir um novo
        $this->selectedWinner = null;
        $this->isDrawing = false;
        $this->showWinnerModal = false;
        $this->loadEligibleParticipants();
    }

    public function closeRedemptionModal()
    {
        $this->showRedemptionModal = false;
    }

    public function render()
    {
        return view('livewire.consortiums.consortium-draw', [
            'recentDraws' => $this->consortium->draws()
                ->with('winner.client')
                ->orderBy('draw_date', 'desc')
                ->limit(5)
                ->get(),
        ]);
    }
}
