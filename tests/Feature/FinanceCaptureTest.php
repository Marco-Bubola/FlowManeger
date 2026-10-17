<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Bank;
use App\Models\BankConnection;
use App\Models\BankConnectionAccount;
use App\Models\Cashbook;
use App\Models\Category;
use App\Models\FinanceCapture;
use App\Models\Invoice;
use App\Models\User;
use App\Models\WhatsAppSetting;
use App\Services\BankSync\BankSyncService;
use App\Services\FinanceCapture\CaptureService;
use App\Services\FinanceCapture\NotificationParser;
use App\Services\FinanceCapture\PrintReader;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class FinanceCaptureTest extends TestCase
{
    private User $user;
    private Bank $nubank;
    private Bank $inter;
    private Account $contaNu;
    private int $outros;
    private int $salario;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-10-16 12:00:00');
        config(['cache.default' => 'array', 'services.gemini.api_key' => 'test-key', 'filesystems.disks.local.root' => sys_get_temp_dir() . '/fm-test-' . getmypid()]);

        Schema::create('users', function (Blueprint $t) {
            $t->id(); $t->string('name'); $t->string('email'); $t->string('password'); $t->string('phone')->nullable(); $t->text('preferences')->nullable(); $t->timestamps();
        });
        Schema::create('category', function (Blueprint $t) {
            $t->increments('id_category'); $t->string('name'); $t->unsignedBigInteger('user_id'); $t->string('type')->nullable(); $t->string('tipo')->nullable(); $t->integer('is_active')->default(1); $t->timestamps();
        });
        Schema::create('banks', function (Blueprint $t) {
            $t->increments('id_bank'); $t->string('name'); $t->string('description')->nullable(); $t->date('start_date')->nullable(); $t->date('end_date')->nullable(); $t->unsignedBigInteger('user_id'); $t->string('caminho_icone')->nullable(); $t->decimal('credit_limit', 10, 2)->nullable(); $t->integer('due_day')->nullable(); $t->timestamps();
        });
        Schema::create('invoice', function (Blueprint $t) {
            $t->increments('id_invoice'); $t->integer('id_bank'); $t->string('description'); $t->string('installments')->nullable(); $t->decimal('value', 10, 2); $t->unsignedBigInteger('user_id'); $t->integer('category_id'); $t->date('invoice_date'); $t->integer('client_id')->nullable(); $t->boolean('dividida')->default(false); $t->timestamps();
        });
        Schema::create('cashbook', function (Blueprint $t) {
            $t->increments('id'); $t->decimal('value', 10, 2); $t->string('description')->nullable(); $t->date('date'); $t->boolean('is_pending')->default(false); $t->unsignedBigInteger('user_id'); $t->integer('category_id'); $t->integer('type_id'); $t->string('note')->nullable(); $t->integer('id_bank')->nullable(); $t->integer('cofrinho_id')->nullable(); $t->integer('client_id')->nullable(); $t->integer('segment_id')->nullable(); $t->string('attachment')->nullable(); $t->dateTime('inc_datetime')->nullable(); $t->dateTime('edit_datetime')->nullable(); $t->timestamps();
        });
        Schema::create('invoice_category_learning', function (Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('user_id'); $t->string('description_pattern'); $t->integer('category_id'); $t->integer('confidence')->default(1); $t->timestamps();
        });
        Artisan::call('migrate', ['--path' => 'database/migrations/2026_10_07_000003_create_accounts_and_cashbook_account.php', '--force' => true]);
        Artisan::call('migrate', ['--path' => 'database/migrations/2026_10_16_000001_create_finance_capture_tables.php', '--force' => true]);

        $this->user = User::forceCreate(['name' => 'Marco', 'email' => 'm@m.test', 'password' => 'x']);
        $this->actingAs($this->user);
        $this->nubank = Bank::create(['name' => 'Nubank', 'description' => 'Roxinho', 'user_id' => $this->user->id]);
        $this->inter = Bank::create(['name' => 'Cartão Inter', 'description' => 'Laranja', 'user_id' => $this->user->id]);
        $this->contaNu = Account::create(['user_id' => $this->user->id, 'name' => 'Nubank conta', 'type' => 'corrente']);
        Account::create(['user_id' => $this->user->id, 'name' => 'Mercado Pago', 'type' => 'corrente']);
        $this->outros = Category::create(['name' => 'Outros', 'user_id' => $this->user->id, 'type' => 'transaction', 'tipo' => 'ambos'])->id_category;
        $this->salario = Category::create(['name' => 'Salário', 'user_id' => $this->user->id, 'type' => 'transaction', 'tipo' => 'receita'])->id_category;
    }

    private function capture(): CaptureService
    {
        return app(CaptureService::class);
    }

    public function test_entende_as_notificacoes_mais_comuns(): void
    {
        $nu = NotificationParser::parse('Nubank: Compra de R$ 45,90 APROVADA em IFOOD *IFOOD para o cartão com final 1234.');
        $this->assertSame(['card', 45.9, 'IFOOD *IFOOD', 'Nubank', '1234'], [$nu['kind'], $nu['amount'], $nu['description'], $nu['institution'], $nu['card_last4']]);

        $inter = NotificationParser::parse('Banco Inter: Pix recebido! Você recebeu R$ 1.250,00 de MARIA SILVA');
        $this->assertSame(['income', 1250.0, 'MARIA SILVA', 'Inter'], [$inter['kind'], $inter['amount'], $inter['description'], $inter['institution']]);

        $mp = NotificationParser::parse('Mercado Pago: Você pagou R$ 32,00 a PADARIA PAO QUENTE');
        $this->assertSame(['expense', 32.0, 'PADARIA PAO QUENTE', 'Mercado Pago'], [$mp['kind'], $mp['amount'], $mp['description'], $mp['institution']]);

        $itau = NotificationParser::parse('Itaú: Compra aprovada no ITAU UNICLASS VISA final 4321 - MAGAZINE LUIZA valor R$ 600,00 em 3x em 14/10, às 10h20.');
        $this->assertSame(['card', 600.0, 3, 'Itaú', '2026-10-14'], [$itau['kind'], $itau['amount'], $itau['installments_total'], $itau['institution'], $itau['date']]);

        // A loja "MERCADO LIVRE" não faz a compra ser do Mercado Pago
        $loja = NotificationParser::parse('Compra de R$ 99,90 APROVADA em MERCADO LIVRE');
        $this->assertNull($loja['institution']);

        $this->assertSame('expense', NotificationParser::parse('Compra no débito de R$ 12,00 em POSTO SHELL')['kind']);
        $this->assertNull(NotificationParser::parse('Sua fatura fecha amanhã'));
    }

    public function test_compra_no_cartao_vai_para_a_fatura_certa_e_nao_duplica(): void
    {
        $r = $this->capture()->fromText($this->user->id, 'Nubank: Compra de R$ 45,90 APROVADA em IFOOD', 'app', true);
        $c = $r['captures'][0];
        $this->assertSame('saved', $c->status);
        $inv = Invoice::find($c->target_id);
        $this->assertSame([$this->nubank->id_bank, 45.9, 'IFOOD', 'Compra à vista', '2026-10-16'], [$inv->id_bank, (float) $inv->value, $inv->description, $inv->installments, $inv->invoice_date]);

        // O mesmo print de novo não grava sozinho
        $again = $this->capture()->fromText($this->user->id, 'Nubank: Compra de R$ 45,90 APROVADA em IFOOD', 'app', true)['captures'][0];
        $this->assertSame('review', $again->status);
        $this->assertStringContainsString('mesmo lançamento', $again->error);
        $this->assertSame(1, Invoice::count());

        // Desfazer apaga a compra da fatura
        $this->capture()->undo($c->fresh());
        $this->assertSame(0, Invoice::count());
    }

    public function test_print_depois_do_extrato_so_liga(): void
    {
        $inv = Invoice::create(['id_bank' => $this->nubank->id_bank, 'description' => 'IFOOD *IFOOD', 'installments' => 'Compra à vista', 'value' => 45.9, 'user_id' => $this->user->id, 'category_id' => $this->outros, 'invoice_date' => '2026-10-15']);
        $c = $this->capture()->fromText($this->user->id, 'Nubank: Compra de R$ 45,90 APROVADA em IFOOD', 'app', true)['captures'][0];
        $this->assertSame(['saved', true, $inv->id_invoice], [$c->status, $c->linked_existing, $c->target_id]);
        $this->assertSame(1, Invoice::count());
        $this->capture()->undo($c);
        $this->assertSame(1, Invoice::count()); // o que já existia continua
    }

    public function test_parcelado_grava_a_parcela(): void
    {
        $c = $this->capture()->fromText($this->user->id, 'Inter: Compra aprovada no cartão final 9999 no valor de R$ 300,00 em 3x em LOJAS RENNER', 'app', true)['captures'][0];
        $inv = Invoice::find($c->target_id);
        $this->assertSame([$this->inter->id_bank, 100.0, '1 de 3', 'LOJAS RENNER'], [$inv->id_bank, (float) $inv->value, $inv->installments, $inv->description]);
    }

    public function test_entrada_e_saida_vao_para_o_livro_caixa_na_conta_do_banco(): void
    {
        $in = $this->capture()->fromText($this->user->id, 'Nubank: Você recebeu uma transferência de R$ 200,00 de JOAO', 'app', true)['captures'][0];
        $row = Cashbook::find($in->target_id);
        $this->assertSame([1, 200.0, $this->contaNu->id, 'JOAO'], [$row->type_id, (float) $row->value, $row->account_id, $row->description]);

        $out = $this->capture()->fromText($this->user->id, 'Pix enviado: R$ 50,00 para ANA', 'app', true)['captures'][0];
        $this->assertSame([2, null], [Cashbook::find($out->target_id)->type_id, Cashbook::find($out->target_id)->account_id]);
    }

    public function test_sem_saber_o_cartao_fica_para_conferir(): void
    {
        $c = $this->capture()->fromText($this->user->id, 'Compra de R$ 20,00 APROVADA em CAFE', 'app', true)['captures'][0];
        $this->assertSame(['review', 'cartão'], [$c->status, $this->capture()->missing($c)]);
        $this->assertSame(0, Invoice::count());
    }

    public function test_le_o_print_pelo_gemini(): void
    {
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response(['candidates' => [['content' => ['parts' => [['text' => json_encode([
            'raw_text' => 'Nubank agora Compra de R$ 89,90 APROVADA em SHOPEE',
            'items' => [
                ['kind' => 'card', 'amount' => 89.9, 'description' => 'SHOPEE', 'date' => '2026-10-15', 'installments_total' => 1, 'institution' => 'Nubank', 'card_last4' => null, 'confidence' => 0.95],
                ['kind' => 'income', 'amount' => '1.500,00', 'description' => 'EMPRESA X', 'date' => '2026-10-15', 'installments_total' => 1, 'institution' => 'Mercado Pago', 'confidence' => 0.9],
            ],
        ])]]]]]])]);

        $r = $this->capture()->fromImage($this->user->id, 'fake-png', 'image/png', 'app', true);
        $this->assertCount(2, $r['captures']);
        $this->assertSame('saved', $r['captures'][0]->status);
        $this->assertSame($this->nubank->id_bank, Invoice::first()->id_bank);
        $this->assertSame(1500.0, (float) Cashbook::first()->value);
        $this->assertSame('Mercado Pago', Account::find(Cashbook::first()->account_id)->name);
        Http::assertSent(fn ($req) => str_contains($req->body(), 'inline_data'));
    }

    public function test_decode_ignora_lixo(): void
    {
        $this->assertSame([], PrintReader::decode('não é json')['items']);
        $this->assertSame([], PrintReader::decode('{"items":[{"amount":0}]}')['items']);
    }

    private function waSetting(): WhatsAppSetting
    {
        $s = WhatsAppSetting::forUser($this->user->id);
        $s->fill(['enabled' => true, 'phone_number_id' => '111222333444', 'access_token' => str_repeat('A', 30), 'app_secret' => str_repeat('a', 32), 'allowed_numbers' => '5515999998888'])->save();

        return $s;
    }

    private function waPost(array $message, ?string $secret = null)
    {
        $body = json_encode(['object' => 'whatsapp_business_account', 'entry' => [['changes' => [['field' => 'messages', 'value' => [
            'metadata' => ['phone_number_id' => '111222333444'], 'messages' => [$message],
        ]]]]]]);
        $sig = 'sha256=' . hash_hmac('sha256', $body, $secret ?? str_repeat('a', 32));

        return $this->call('POST', "/whatsapp/{$this->user->id}/webhook", [], [], [], ['CONTENT_TYPE' => 'application/json', 'HTTP_X_HUB_SIGNATURE_256' => $sig], $body);
    }

    public function test_whatsapp_verifica_o_webhook(): void
    {
        $s = $this->waSetting();
        $this->get("/whatsapp/{$this->user->id}/webhook?hub.mode=subscribe&hub.verify_token={$s->verify_token}&hub.challenge=abc123")->assertOk()->assertSee('abc123');
        $this->get("/whatsapp/{$this->user->id}/webhook?hub.mode=subscribe&hub.verify_token=errado&hub.challenge=abc123")->assertForbidden();
    }

    public function test_whatsapp_recebe_print_lanca_e_responde(): void
    {
        $this->waSetting();
        Http::fake([
            'graph.facebook.com/v21.0/MEDIA1' => Http::response(['url' => 'https://lookaside.fbsbx.com/x', 'mime_type' => 'image/jpeg']),
            'lookaside.fbsbx.com/*' => Http::response('jpeg-bytes'),
            'generativelanguage.googleapis.com/*' => Http::response(['candidates' => [['content' => ['parts' => [['text' => '{"items":[{"kind":"card","amount":45.9,"description":"IFOOD","date":"2026-10-16","installments_total":1,"institution":"Nubank","confidence":0.9}]}']]]]]]),
            'graph.facebook.com/v21.0/111222333444/messages' => Http::response(['messages' => [['id' => 'out1']]]),
        ]);

        $this->waPost(['from' => '5515999998888', 'id' => 'wamid.A', 'type' => 'image', 'image' => ['id' => 'MEDIA1', 'mime_type' => 'image/jpeg']])->assertOk();

        $this->assertSame(1, Invoice::count());
        $this->assertSame('whatsapp', FinanceCapture::first()->source);
        Http::assertSent(fn ($r) => str_contains($r->url(), '/messages') && str_contains($r['text']['body'] ?? '', 'Lancei') && $r['to'] === '5515999998888');

        // Reenvio da Meta não lança de novo
        $this->waPost(['from' => '5515999998888', 'id' => 'wamid.A', 'type' => 'image', 'image' => ['id' => 'MEDIA1']]);
        $this->assertSame(1, Invoice::count());

        // "desfazer" apaga
        $this->waPost(['from' => '5515999998888', 'id' => 'wamid.B', 'type' => 'text', 'text' => ['body' => 'Desfazer']]);
        $this->assertSame(0, Invoice::count());
    }

    public function test_whatsapp_pergunta_o_cartao_e_usa_a_resposta(): void
    {
        $this->waSetting();
        Http::fake(['graph.facebook.com/*' => Http::response(['messages' => [['id' => 'x']]])]);

        // Número sem o 9 (como a Meta às vezes entrega) também vale
        $this->waPost(['from' => '551599998888', 'id' => 'wamid.C', 'type' => 'text', 'text' => ['body' => 'Compra de R$ 20,00 APROVADA em CAFE']]);
        $this->assertSame(0, Invoice::count());
        Http::assertSent(fn ($r) => str_contains($r['text']['body'] ?? '', 'Em qual cartão?') && str_contains($r['text']['body'], '2. Nubank'));

        $this->waPost(['from' => '551599998888', 'id' => 'wamid.D', 'type' => 'text', 'text' => ['body' => '2']]);
        $this->assertSame($this->nubank->id_bank, Invoice::first()?->id_bank);
    }

    public function test_whatsapp_ignora_numero_estranho_e_assinatura_errada(): void
    {
        $this->waSetting();
        Http::fake();
        $this->waPost(['from' => '5511911112222', 'id' => 'wamid.E', 'type' => 'text', 'text' => ['body' => 'Pix recebido R$ 10,00 de X']])->assertOk();
        $this->assertSame(0, FinanceCapture::count());
        Http::assertNothingSent();

        $this->waPost(['from' => '5515999998888', 'id' => 'wamid.F', 'type' => 'text', 'text' => ['body' => 'oi']], str_repeat('b', 32))->assertStatus(401);
    }

    public function test_banco_conectado_lanca_sem_duplicar(): void
    {
        BankConnection::create(['user_id' => $this->user->id, 'client_id' => 'cid', 'client_secret' => 'sec', 'item_ids' => 'item-nubank-0001']);
        // Compra que já entrou por print
        $this->capture()->fromText($this->user->id, 'Nubank: Compra de R$ 45,90 APROVADA em IFOOD', 'app', true);

        Http::fake([
            'api.pluggy.ai/auth' => Http::response(['apiKey' => 'k']),
            'api.pluggy.ai/items/*' => Http::response(['id' => 'item-nubank-0001', 'connector' => ['name' => 'Nubank']]),
            'api.pluggy.ai/accounts*' => Http::response(['results' => [
                ['id' => 'acc-bank', 'type' => 'BANK', 'subtype' => 'CHECKING_ACCOUNT', 'name' => 'Conta', 'number' => '0001-12345', 'balance' => 1000],
                ['id' => 'acc-card', 'type' => 'CREDIT', 'subtype' => 'CREDIT_CARD', 'name' => 'Ultravioleta', 'number' => '1234', 'balance' => 300],
            ]]),
            'api.pluggy.ai/transactions?accountId=acc-bank*' => Http::response(['totalPages' => 1, 'results' => [
                ['id' => 't1', 'description' => 'Pix recebido JOAO', 'amount' => 150, 'type' => 'CREDIT', 'date' => '2026-10-16T03:00:00.000Z', 'status' => 'POSTED'],
                ['id' => 't2', 'description' => 'Pagamento de boleto', 'amount' => -80, 'type' => 'DEBIT', 'date' => '2026-10-16T03:00:00.000Z', 'status' => 'POSTED'],
                ['id' => 't3', 'description' => 'Antigo', 'amount' => -10, 'type' => 'DEBIT', 'date' => '2026-10-01T03:00:00.000Z'],
                ['id' => 't4', 'description' => 'Pendente', 'amount' => -5, 'type' => 'DEBIT', 'date' => '2026-10-16T03:00:00.000Z', 'status' => 'PENDING'],
            ]]),
            'api.pluggy.ai/transactions?accountId=acc-card*' => Http::response(['totalPages' => 1, 'results' => [
                ['id' => 'c1', 'description' => 'IFOOD', 'amount' => 45.9, 'type' => 'DEBIT', 'date' => '2026-10-16T03:00:00.000Z'],
                ['id' => 'c2', 'description' => 'RENNER', 'amount' => 100, 'type' => 'DEBIT', 'date' => '2026-10-16T03:00:00.000Z', 'creditCardMetadata' => ['installmentNumber' => 2, 'totalInstallments' => 3]],
                ['id' => 'c3', 'description' => 'Pagamento recebido', 'amount' => -500, 'type' => 'CREDIT', 'date' => '2026-10-16T03:00:00.000Z'],
            ]]),
        ]);

        $sync = app(BankSyncService::class);
        $conn = BankConnection::first();
        $this->assertSame(2, $sync->discover($conn));
        $bankAcc = BankConnectionAccount::where('provider_account_id', 'acc-bank')->first();
        $cardAcc = BankConnectionAccount::where('provider_account_id', 'acc-card')->first();
        // Sugestão automática pelo nome do banco
        $this->assertSame(['account', $this->contaNu->id], [$bankAcc->target_type, $bankAcc->target_id]);
        $this->assertSame(['bank', $this->nubank->id_bank], [$cardAcc->target_type, $cardAcc->target_id]);
        $bankAcc->update(['import_from' => '2026-10-10']);
        $cardAcc->update(['import_from' => '2026-10-10']);

        $r = $sync->sync($conn);
        $this->assertSame([3, 1], [$r['created'], $r['linked']], json_encode($r));
        $this->assertSame(2, Cashbook::count());
        $this->assertSame(2, Invoice::count()); // IFOOD (ligado ao print) + RENNER 2 de 3
        $this->assertSame('2 de 3', Invoice::where('description', 'RENNER')->value('installments'));
        $this->assertSame(100.0, (float) Invoice::where('description', 'RENNER')->value('value'));

        $again = $sync->sync($conn->fresh());
        $this->assertSame([0, 0], [$again['created'], $again['linked']]);
        $this->assertSame(2, Cashbook::count());
    }
}
