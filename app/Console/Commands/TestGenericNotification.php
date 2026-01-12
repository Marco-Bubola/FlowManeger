<?php

namespace App\Console\Commands;

use App\Models\ConsortiumNotification;
use App\Models\User;
use Illuminate\Console\Command;

class TestGenericNotification extends Command
{
    protected $signature = 'notification:test-generic';
    protected $description = 'Cria notificações de teste para diferentes módulos';

    public function handle()
    {
        $this->info('🧪 Criando notificações de teste...');
        $this->newLine();

        $user = User::first();

        if (!$user) {
            $this->error('❌ Nenhum usuário encontrado no sistema.');
            return 1;
        }

        $this->info("👤 Usuário: {$user->name} (ID: {$user->id})");
        $this->newLine();

        $notifications = [];

        // Teste 1: Notificação de Venda
        $notifications[] = ConsortiumNotification::createGeneric(
            module: 'sale',
            type: 'sale_pending',
            userId: $user->id,
            title: '🛒 Nova Venda Pendente',
            message: 'Venda #1234 criada para o cliente João Silva. Aguardando aprovação.',
            options: [
                'entity_type' => 'Sale',
                'entity_id' => 1234,
                'priority' => 'medium',
                'action_url' => route('sales.index'),
                'data' => [
                    'sale_id' => 1234,
                    'amount' => 1500.00,
                    'client_name' => 'João Silva',
                ]
            ]
        );

        // Teste 2: Notificação de Pagamento Atrasado
        $notifications[] = ConsortiumNotification::createGeneric(
            module: 'payment',
            type: 'payment_overdue',
            userId: $user->id,
            title: '⚠️ Pagamento Atrasado',
            message: 'Pagamento #567 está 5 dias em atraso. Cliente: Maria Santos.',
            options: [
                'entity_type' => 'Payment',
                'entity_id' => 567,
                'priority' => 'high',
                'action_url' => route('sales.index'),
                'data' => [
                    'payment_id' => 567,
                    'amount' => 850.00,
                    'due_date' => now()->subDays(5)->format('Y-m-d'),
                    'days_overdue' => 5,
                ]
            ]
        );

        // Teste 3: Notificação de Cliente Novo
        $notifications[] = ConsortiumNotification::createGeneric(
            module: 'client',
            type: 'client_new',
            userId: $user->id,
            title: '👤 Novo Cliente Cadastrado',
            message: 'Cliente Pedro Oliveira foi cadastrado com sucesso no sistema.',
            options: [
                'entity_type' => 'Client',
                'entity_id' => 789,
                'priority' => 'low',
                'action_url' => route('clients.index'),
                'data' => [
                    'client_id' => 789,
                    'client_name' => 'Pedro Oliveira',
                    'registration_date' => now()->format('Y-m-d H:i:s'),
                ]
            ]
        );

        // Teste 4: Notificação de Venda Completa
        $notifications[] = ConsortiumNotification::createGeneric(
            module: 'sale',
            type: 'sale_completed',
            userId: $user->id,
            title: '✅ Venda Concluída',
            message: 'Venda #5678 foi concluída com sucesso! Valor total: R$ 2.500,00',
            options: [
                'entity_type' => 'Sale',
                'entity_id' => 5678,
                'priority' => 'medium',
                'action_url' => route('sales.index'),
                'data' => [
                    'sale_id' => 5678,
                    'amount' => 2500.00,
                    'completion_date' => now()->format('Y-m-d H:i:s'),
                ]
            ]
        );

        // Teste 5: Notificação de Aniversário de Cliente
        $notifications[] = ConsortiumNotification::createGeneric(
            module: 'client',
            type: 'client_birthday',
            userId: $user->id,
            title: '🎂 Aniversário de Cliente',
            message: 'Hoje é aniversário da cliente Ana Costa! Que tal enviar uma mensagem?',
            options: [
                'entity_type' => 'Client',
                'entity_id' => 321,
                'priority' => 'low',
                'action_url' => route('clients.index'),
                'data' => [
                    'client_id' => 321,
                    'client_name' => 'Ana Costa',
                    'birthday' => now()->format('d/m'),
                ]
            ]
        );

        $this->newLine();
        $this->info('✅ Notificações de teste criadas:');
        $this->newLine();

        $table = [];
        foreach ($notifications as $notification) {
            $table[] = [
                'ID' => $notification->id,
                'Módulo' => strtoupper($notification->module),
                'Tipo' => $notification->type,
                'Título' => $notification->title,
                'Prioridade' => strtoupper($notification->priority),
            ];
        }

        $this->table(
            ['ID', 'Módulo', 'Tipo', 'Título', 'Prioridade'],
            $table
        );

        $this->newLine();
        $this->info('🎉 Total de notificações criadas: ' . count($notifications));
        $this->info('📱 Acesse o sistema para visualizar as notificações na sidebar!');

        return 0;
    }
}
