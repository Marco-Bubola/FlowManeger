<?php

namespace App\Services\MercadoLivre;

use App\Services\MercadoLivre\AuthService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Service para gerenciar perguntas de compradores no Mercado Livre.
 *
 * API referenciada:
 *   GET  /questions/search    – busca perguntas do vendedor
 *   POST /answers             – responder uma pergunta
 *   DELETE /questions/{id}    – deletar pergunta (somente sem resposta)
 */
class QuestionService extends MercadoLivreService
{
    protected function getToken(): ?\App\Models\MercadoLivreToken
    {
        $userId = Auth::id();
        if (!$userId) {
            return null;
        }
        return (new AuthService())->getActiveToken($userId);
    }

    // ---------------------------------------------------------------
    // Busca de Perguntas
    // ---------------------------------------------------------------

    /**
     * Busca as perguntas do vendedor.
     *
     * @param array $filters  status (UNANSWERED|ANSWERED), item_id, offset, limit
     */
    public function getQuestions(array $filters = []): array
    {
        try {
            $token = $this->getToken();
            if (!$token || !$token->ml_user_id) {
                return ['success' => false, 'message' => 'Token ML não encontrado.', 'questions' => [], 'total' => 0];
            }

            $params = array_filter([
                'seller_id' => $token->ml_user_id,
                'status'    => $filters['status'] ?? null,
                'item_id'   => $filters['item_id'] ?? null,
                'limit'     => $filters['limit'] ?? 50,
                'offset'    => $filters['offset'] ?? 0,
                'sort'      => $filters['sort'] ?? 'date_desc',
            ]);

            $query    = http_build_query($params);
            $response = $this->makeRequest('GET', "/questions/search?{$query}", [], $token->access_token, Auth::id());

            if ($response['success'] ?? false) {
                $data = $response['data'] ?? [];
                return [
                    'success'   => true,
                    'questions' => $data['questions'] ?? [],
                    'total'     => $data['total'] ?? 0,
                    'paging'    => $data['paging'] ?? [],
                ];
            }

            return ['success' => false, 'message' => $response['message'] ?? 'Erro ao buscar perguntas.', 'questions' => [], 'total' => 0];

        } catch (\Exception $e) {
            Log::error('QuestionService::getQuestions', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Erro interno ao buscar perguntas.', 'questions' => [], 'total' => 0];
        }
    }

    // ---------------------------------------------------------------
    // Contagem de não respondidas
    // ---------------------------------------------------------------

    public function countUnanswered(): int
    {
        $result = $this->getQuestions(['status' => 'UNANSWERED', 'limit' => 1]);
        return (int)($result['total'] ?? 0);
    }

    // ---------------------------------------------------------------
    // Responder pergunta  POST /answers
    // ---------------------------------------------------------------

    public function answerQuestion(int $questionId, string $text): array
    {
        try {
            $token = $this->getToken();
            if (!$token) {
                return ['success' => false, 'message' => 'Token ML não encontrado.'];
            }

            if (empty(trim($text))) {
                return ['success' => false, 'message' => 'A resposta não pode estar vazia.'];
            }

            $response = $this->makeRequest('POST', '/answers', [
                'question_id' => $questionId,
                'text'        => trim($text),
            ], $token->access_token, Auth::id());

            if ($response['success'] ?? false) {
                return ['success' => true, 'message' => 'Pergunta respondida com sucesso!', 'data' => $response['data'] ?? []];
            }

            return ['success' => false, 'message' => $response['message'] ?? 'Erro ao responder pergunta.'];

        } catch (\Exception $e) {
            Log::error('QuestionService::answerQuestion', ['id' => $questionId, 'error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Erro interno ao responder pergunta.'];
        }
    }

    // ---------------------------------------------------------------
    // Deletar pergunta (só sem resposta)  DELETE /questions/{id}
    // ---------------------------------------------------------------

    public function deleteQuestion(int $questionId): array
    {
        try {
            $token = $this->getToken();
            if (!$token) {
                return ['success' => false, 'message' => 'Token ML não encontrado.'];
            }

            $response = $this->makeRequest('DELETE', "/questions/{$questionId}", [], $token->access_token, Auth::id());

            if ($response['success'] ?? false) {
                return ['success' => true, 'message' => 'Pergunta removida.'];
            }

            return ['success' => false, 'message' => $response['message'] ?? 'Erro ao remover pergunta.'];

        } catch (\Exception $e) {
            Log::error('QuestionService::deleteQuestion', ['id' => $questionId, 'error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Erro interno ao remover pergunta.'];
        }
    }
}
