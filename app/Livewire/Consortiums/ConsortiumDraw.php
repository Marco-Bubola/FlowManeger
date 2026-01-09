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
        if ($consortium->user_id !== Auth::id()) {
            abort(403, 'Você não tem permissão para realizar sorteios neste consórcio.');
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
        $this->eligibleParticipants = $this->consortium->participants()
            ->with('client')
            ->where('status', 'active')
            ->where('is_contemplated', false)
            ->get()
            ->filter(function ($participant) {
                // Verificar se está em dia com os pagamentos
                // Considerar pagamentos vencidos há mais de 30 dias
                $latePayments = $participant->payments()
                    ->where('status', 'pending')
                    ->where('due_date', '<', now()->subDays(30))
                    ->count();

                // Também verificar se tem ao menos 1 pagamento realizado
                $paidPayments = $participant->payments()
                    ->where('status', 'paid')
                    ->count();

                return $latePayments === 0 && $paidPayments > 0;
            })
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
            $lastDraw = $this->consortium->draws()->orderBy('draw_date', 'desc')->first();
            if ($lastDraw) {
                $daysSinceLastDraw = now()->diffInDays($lastDraw->draw_date);
                $frequencyDays = match($this->consortium->draw_frequency) {
                    'weekly' => 7,
                    'biweekly' => 14,
                    'monthly' => 30,
                    'quarterly' => 90,
                    default => 30
                };

                if ($daysSinceLastDraw < ($frequencyDays * 0.8)) {
                    session()->flash('error', 'Aguarde mais tempo entre sorteios. Último sorteio foi há ' . $daysSinceLastDraw . ' dias. Frequência: ' . $this->consortium->draw_frequency_label);
                    return;
                }
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

            // Criar registro do sorteio
            $draw = ConsortiumDrawModel::create([
                'consortium_id' => $this->consortium->id,
                'draw_date' => $this->drawDate,
                'draw_number' => $this->drawNumber,
                'winner_participant_id' => $this->selectedWinner->id,
                'status' => 'completed',
            ]);

            // Atualizar participante como contemplado
            $this->selectedWinner->update([
                'is_contemplated' => true,
                'status' => 'contemplated',
            ]);

            // Criar registro de contemplação
            $contemplation = ConsortiumContemplation::create([
                'consortium_participant_id' => $this->selectedWinner->id,
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
