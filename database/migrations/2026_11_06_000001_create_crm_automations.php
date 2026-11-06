<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * CRM, etapa 3: automações de relacionamento (aniversário, pós-venda, cliente
 * sumido). Cada automação cria uma tarefa com a mensagem pronta; o registro
 * evita criar a mesma duas vezes. E o "não quer receber ofertas" do cliente.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('crm_automation_logs')) {
            Schema::create('crm_automation_logs', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('user_id');
                $t->unsignedBigInteger('client_id');
                $t->string('kind', 20); // aniversario | posvenda | sumido
                $t->string('ref', 40);  // ano, id da venda ou data da última compra
                $t->unsignedBigInteger('task_id')->nullable();
                $t->timestamps();
                $t->unique(['user_id', 'kind', 'client_id', 'ref'], 'crm_auto_unique');
            });
        }

        if (Schema::hasTable('client_tasks')) {
            Schema::table('client_tasks', function (Blueprint $t) {
                if (! Schema::hasColumn('client_tasks', 'auto')) {
                    $t->string('auto', 20)->nullable();
                    $t->text('message')->nullable();
                }
            });
        }

        if (Schema::hasTable('clients') && ! Schema::hasColumn('clients', 'marketing_opt_out')) {
            Schema::table('clients', function (Blueprint $t) {
                $t->boolean('marketing_opt_out')->default(false);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_automation_logs');
    }
};
