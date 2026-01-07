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
        // Verificar se pode realizar sorteio
        if (!$this->consortium->canPerformDraw()) {
            session()->flash('error', 'Não é possível realizar sorteio neste momento.');
            return;
        }

        if ($this->eligibleParticipants->isEmpty()) {
            session()->flash('error', 'Não há participantes elegíveis para o sorteio.');
            return;
        }

        $this->showDrawModal = true;
    }

    public function performDraw()
    {
        try {
            $this->isDrawing = true;

            // Validar se há participantes elegíveis
            if ($this->eligibleParticipants->isEmpty()) {
                session()->flash('error', 'Não há participantes elegíveis para o sorteio.');
                $this->isDrawing = false;
                return;
            }

            DB::beginTransaction();

            // Selecionar participante vencedor aleatoriamente
            $winner = $this->eligibleParticipants->random();
            $this->selectedWinner = $winner;

            // Criar registro do sorteio
            $draw = ConsortiumDrawModel::create([
                'consortium_id' => $this->consortium->id,
                'draw_date' => $this->drawDate,
                'draw_number' => $this->drawNumber,
                'winner_participant_id' => $winner->id,
                'status' => 'completed',
            ]);

            // Atualizar participante como contemplado
            $winner->update([
                'is_contemplated' => true,
                'status' => 'contemplated',
            ]);

            // Criar registro de contemplação
            ConsortiumContemplation::create([
                'consortium_participant_id' => $winner->id,
                'draw_id' => $draw->id,
                'contemplation_type' => 'draw',
                'contemplation_date' => now(),
                'redemption_type' => 'pending',
                'status' => 'pending',
            ]);

            DB::commit();

            $this->showDrawModal = false;
            $this->showWinnerModal = true;
            $this->isDrawing = false;

            // Atualizar lista de elegíveis
            $this->loadEligibleParticipants();

            // Incrementar número do próximo sorteio
            $this->drawNumber = $this->consortium->draws()->count() + 1;

            session()->flash('success', 'Sorteio realizado com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->isDrawing = false;
            session()->flash('error', 'Erro ao realizar sorteio: ' . $e->getMessage());
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

        try {
            $contemplation = $this->selectedWinner->contemplation;

            $contemplation->update([
                'redemption_type' => $this->redemptionType,
                'status' => $this->redemptionType === 'pending' ? 'pending' : 'redeemed',
            ]);

            $this->showRedemptionModal = false;
            $this->selectedWinner = null;

            session()->flash('success', 'Tipo de resgate definido com sucesso!');
        } catch (\Exception $e) {
            session()->flash('error', 'Erro ao salvar tipo de resgate: ' . $e->getMessage());
        }
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
