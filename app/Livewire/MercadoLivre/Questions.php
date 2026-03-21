<?php

namespace App\Livewire\MercadoLivre;

use App\Services\MercadoLivre\QuestionService;
use App\Traits\HasNotifications;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class Questions extends Component
{
    use HasNotifications;

    // Filtros
    public string $statusFilter = 'UNANSWERED'; // UNANSWERED | ANSWERED | ''
    public string $itemIdFilter = '';
    public int    $perPage = 50;
    public int    $offset  = 0;

    // Dados
    public array $questions = [];
    public int   $total     = 0;
    public array $paging    = [];
    public bool  $loading   = false;

    // UI
    public bool    $showFiltersModal = false;
    public bool    $tipsOpen         = false;
    public ?array  $selectedQuestion = null;
    public bool    $showAnswerModal  = false;
    public string  $answerText       = '';
    public bool    $sendingAnswer    = false;

    public function mount(): void
    {
        $this->loadQuestions();
    }

    public function loadQuestions(): void
    {
        $this->loading = true;

        try {
            $service = app(QuestionService::class);
            $result  = $service->getQuestions([
                'status'  => $this->statusFilter ?: null,
                'item_id' => $this->itemIdFilter  ?: null,
                'limit'   => $this->perPage,
                'offset'  => $this->offset,
            ]);

            if ($result['success']) {
                $this->questions = $result['questions'];
                $this->total     = $result['total'];
                $this->paging    = $result['paging'] ?? [];
            } else {
                $this->notifyError($result['message'] ?? 'Erro ao carregar perguntas.');
            }
        } catch (\Exception $e) {
            Log::error('Questions::loadQuestions', ['error' => $e->getMessage()]);
            $this->notifyError('Erro ao carregar perguntas.');
        } finally {
            $this->loading = false;
        }
    }

    // Dispatcher para filtros live
    public function updatedStatusFilter(): void { $this->offset = 0; $this->loadQuestions(); }
    public function updatedItemIdFilter(): void  { $this->offset = 0; $this->loadQuestions(); }

    // Abrir modal de resposta
    public function openAnswer(array $question): void
    {
        $this->selectedQuestion = $question;
        $this->answerText       = '';
        $this->showAnswerModal  = true;
    }

    public function closeAnswerModal(): void
    {
        $this->showAnswerModal  = false;
        $this->selectedQuestion = null;
        $this->answerText       = '';
    }

    // Enviar resposta
    public function sendAnswer(): void
    {
        $this->validate(['answerText' => 'required|min:5|max:2000']);

        if ($this->sendingAnswer || !$this->selectedQuestion) {
            return;
        }

        $this->sendingAnswer = true;

        try {
            $service = app(QuestionService::class);
            $result  = $service->answerQuestion(
                (int)$this->selectedQuestion['id'],
                $this->answerText
            );

            if ($result['success']) {
                $this->notifySuccess($result['message']);
                $this->closeAnswerModal();
                $this->loadQuestions();
            } else {
                $this->notifyError($result['message']);
            }
        } catch (\Exception $e) {
            $this->notifyError('Erro ao enviar resposta.');
        } finally {
            $this->sendingAnswer = false;
        }
    }

    // Deletar pergunta
    public function deleteQuestion(int $questionId): void
    {
        try {
            $service = app(QuestionService::class);
            $result  = $service->deleteQuestion($questionId);

            if ($result['success']) {
                $this->notifySuccess($result['message']);
                $this->loadQuestions();
            } else {
                $this->notifyError($result['message']);
            }
        } catch (\Exception $e) {
            $this->notifyError('Erro ao remover pergunta.');
        }
    }

    // Paginação
    public function nextPage(): void
    {
        if ($this->offset + $this->perPage < $this->total) {
            $this->offset += $this->perPage;
            $this->loadQuestions();
        }
    }

    public function prevPage(): void
    {
        $this->offset = max(0, $this->offset - $this->perPage);
        $this->loadQuestions();
    }

    public function clearFilters(): void
    {
        $this->statusFilter = '';
        $this->itemIdFilter = '';
        $this->offset       = 0;
        $this->loadQuestions();
    }

    public function formatDate(string $date): string
    {
        try {
            return \Carbon\Carbon::parse($date)
                ->setTimezone(config('app.timezone', 'America/Sao_Paulo'))
                ->format('d/m/Y H:i');
        } catch (\Exception) {
            return $date;
        }
    }

    public function render()
    {
        return view('livewire.mercadolivre.questions')
            ->layout('components.layouts.app', [
                'title' => 'Perguntas – Mercado Livre',
            ]);
    }
}
