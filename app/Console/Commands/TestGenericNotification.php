<?php

namespace App\Console\Commands;

use App\Models\ConsortiumNotification;
use App\Models\User;
use Illuminate\Console\Command;

class TestGenericNotification extends Command
{
    protected $signature = 'notification:test-generic {--user= : ID ou e-mail do usuário (padrão: primeiro usuário)}';
    protected $description = 'Cria notificações de teste (uma por categoria) para conferir o sino e a central';

    public function handle(): int
    {
        $opt = $this->option('user');
        $user = $opt
            ? User::where('id', $opt)->orWhere('email', $opt)->first()
            : User::first();

        if (!$user) {
            $this->error('Nenhum usuário encontrado.');
            return self::FAILURE;
        }

        $this->info("Usuário: {$user->name} (ID: {$user->id})");

        $url = fn (string $name) => rescue(fn () => route($name, [], false), null, false);

        $samples = [
            ['sale', 'sale_completed', 'Venda concluída', 'Venda #5678 concluída. Total: R$ 2.500,00.', 'medium', $url('sales.index')],
            ['estoque', 'low_stock', 'Estoque baixo', 'Camiseta básica (M): restam 2 un. (≈ 4 dias). Sugestão: repor 20 un.', 'high', $url('gestao.restock')],
            ['estoque', 'out_of_stock', 'Produto esgotado', 'Caneca personalizada: esgotado.', 'high', $url('gestao.restock')],
            ['mercadolivre', 'question_received', 'Nova pergunta no Mercado Livre', 'Tem pronta entrega na cor azul?', 'high', $url('mercadolivre.questions')],
            ['shopee', 'order_received', 'Novo pedido na Shopee', 'Pedido 240901ABC recebido.', 'medium', null],
            ['consortium', 'draw_available', 'Sorteio disponível', 'O consórcio "Grupo Teste" está pronto para um novo sorteio.', 'high', $url('consortiums.index')],
            ['payment', 'payment_due', 'Pagamento a vencer', 'Parcela de Maria Santos vence amanhã (R$ 150,00).', 'medium', $url('sales.index')],
            ['system', 'system_update', 'Novidade no FlowManager', 'Central de notificações com filtros por categoria.', 'low', null],
        ];

        $rows = [];
        foreach ($samples as [$module, $type, $title, $message, $priority, $action]) {
            $n = ConsortiumNotification::createGeneric($module, $type, $user->id, $title, $message, [
                'priority' => $priority,
                'action_url' => $action,
                'data' => ['test' => true],
            ]);
            $rows[] = $n
                ? [$n->id, $n->category_label, $type, $title, $priority]
                : ['-', ConsortiumNotification::CATEGORIES[ConsortiumNotification::categoryFor($module, $type)]['label'], $type, '(desligada nas preferências)', $priority];
        }

        $this->table(['ID', 'Categoria', 'Tipo', 'Título', 'Prioridade'], $rows);
        $this->info('Abra /notificacoes para conferir.');

        return self::SUCCESS;
    }
}
