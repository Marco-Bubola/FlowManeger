<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lançar por print (tela e WhatsApp) e bancos conectados (Open Finance pela Pluggy).
 */
return new class extends Migration
{
    public function up(): void
    {
        // Cada print, texto ou transação do banco que virou (ou vai virar) lançamento
        if (! Schema::hasTable('finance_captures')) {
            Schema::create('finance_captures', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->index();
                $table->string('source', 20);                  // app | whatsapp
                $table->string('status', 20)->default('review'); // saved | review | undone | failed | discarded
                $table->string('kind', 20)->nullable();        // card | income | expense
                $table->decimal('amount', 12, 2)->nullable();
                $table->string('description')->nullable();
                $table->date('date')->nullable();
                $table->string('installments', 20)->nullable(); // "1 de 3"
                $table->string('institution', 60)->nullable(); // banco que o print mostra
                $table->string('target_type', 20)->nullable(); // invoice | cashbook
                $table->unsignedBigInteger('target_id')->nullable();
                $table->unsignedBigInteger('bank_id')->nullable();    // cartão escolhido
                $table->unsignedBigInteger('account_id')->nullable(); // conta escolhida
                $table->unsignedInteger('category_id')->nullable();
                $table->boolean('linked_existing')->default(false);   // já existia igual: não duplicou
                $table->string('image_path')->nullable();
                $table->text('raw_text')->nullable();
                $table->json('parsed')->nullable();
                $table->string('wa_from', 30)->nullable();
                $table->string('wa_message_id', 120)->nullable()->unique();
                $table->string('error')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('whatsapp_settings')) {
            Schema::create('whatsapp_settings', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->unique();
                $table->boolean('enabled')->default(false);
                $table->string('phone_number_id', 40)->nullable()->index();
                $table->text('access_token')->nullable(); // criptografado
                $table->text('app_secret')->nullable();   // criptografado
                $table->string('verify_token', 64);
                $table->string('allowed_numbers')->nullable(); // só esses números podem lançar
                $table->boolean('auto_save')->default(true);   // false: tudo fica para revisar no app
                $table->timestamp('last_message_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('bank_connections')) {
            Schema::create('bank_connections', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->unique();
                $table->string('provider', 20)->default('pluggy');
                $table->boolean('enabled')->default(true);
                $table->string('client_id')->nullable();
                $table->text('client_secret')->nullable(); // criptografado
                $table->text('item_ids')->nullable();      // um por linha ou separados por vírgula
                $table->timestamp('last_sync_at')->nullable();
                $table->string('last_error')->nullable();
                $table->timestamps();
            });
        }

        // Cada conta/cartão que o banco devolve e para onde ela vai no app
        if (! Schema::hasTable('bank_connection_accounts')) {
            Schema::create('bank_connection_accounts', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->index();
                $table->string('item_id', 64);
                $table->string('provider_account_id', 64);
                $table->string('institution', 80)->nullable();
                $table->string('name')->nullable();
                $table->string('number', 40)->nullable();
                $table->string('type', 20);             // BANK | CREDIT
                $table->string('subtype', 40)->nullable();
                $table->decimal('balance', 12, 2)->nullable();
                $table->string('target_type', 20)->default('none'); // account | bank | none
                $table->unsignedBigInteger('target_id')->nullable();
                $table->date('import_from')->nullable();
                $table->timestamp('last_synced_at')->nullable();
                $table->unsignedInteger('imported_count')->default(0);
                $table->timestamps();
                $table->unique(['user_id', 'provider_account_id']);
            });
        }

        // Transação do banco já tratada: nunca entra duas vezes
        if (! Schema::hasTable('bank_sync_transactions')) {
            Schema::create('bank_sync_transactions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('connection_account_id')->index();
                $table->string('provider_tx_id', 64);
                $table->string('target_type', 20)->nullable(); // invoice | cashbook | null (ignorada)
                $table->unsignedBigInteger('target_id')->nullable();
                $table->boolean('linked_existing')->default(false);
                $table->timestamps();
                $table->unique(['user_id', 'provider_tx_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_sync_transactions');
        Schema::dropIfExists('bank_connection_accounts');
        Schema::dropIfExists('bank_connections');
        Schema::dropIfExists('whatsapp_settings');
        Schema::dropIfExists('finance_captures');
    }
};
