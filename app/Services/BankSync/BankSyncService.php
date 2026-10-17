<?php

namespace App\Services\BankSync;

use App\Models\BankConnection;
use App\Models\BankConnectionAccount;
use App\Models\BankSyncTransaction;
use App\Models\FinanceCapture;
use App\Services\FinanceCapture\EntryWriter;
use App\Services\FinanceCapture\Institutions;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Bancos conectados: busca as contas e cartões do Open Finance e lança as
 * transações novas no livro caixa (conta) ou na fatura (cartão), sem duplicar.
 */
class BankSyncService
{
    public function __construct(private EntryWriter $writer)
    {
    }

    public function client(BankConnection $c): PluggyClient
    {
        return new PluggyClient($c);
    }

    /** Busca as contas de cada Item ID e sugere para onde cada uma vai. Devolve quantas achou. */
    public function discover(BankConnection $c): int
    {
        $client = $this->client($c);
        $found = 0;
        foreach ($c->itemIdList() as $itemId) {
            $item = $client->item($itemId);
            $connector = (string) data_get($item, 'connector.name', '');
            $institution = Institutions::detect($connector) ?? ($connector ?: null);

            foreach ($client->accounts($itemId) as $acc) {
                $found++;
                $row = BankConnectionAccount::firstOrNew(['user_id' => $c->user_id, 'provider_account_id' => (string) $acc['id']]);
                $isNew = ! $row->exists;
                $row->fill([
                    'item_id' => $itemId,
                    'institution' => $institution,
                    'name' => $acc['marketingName'] ?? $acc['name'] ?? null,
                    'number' => isset($acc['number']) ? mb_substr((string) $acc['number'], 0, 40) : null,
                    'type' => ($acc['type'] ?? 'BANK') === 'CREDIT' ? 'CREDIT' : 'BANK',
                    'subtype' => $acc['subtype'] ?? null,
                    'balance' => isset($acc['balance']) ? round((float) $acc['balance'], 2) : null,
                ]);
                if ($isNew) {
                    // Sugestão: cartão do mesmo banco / conta do mesmo banco; importa só daqui pra frente
                    if ($row->type === 'CREDIT' && ($bank = $this->writer->findBank($c->user_id, $institution))) {
                        $row->fill(['target_type' => 'bank', 'target_id' => $bank->id_bank]);
                    } elseif ($row->type === 'BANK' && ($account = $this->writer->findAccount($c->user_id, $institution))) {
                        $row->fill(['target_type' => 'account', 'target_id' => $account->id]);
                    }
                    $row->import_from = now()->toDateString();
                }
                $row->save();
            }
        }

        return $found;
    }

    /** @return array{created:int, linked:int, skipped:int, errors: string[]} */
    public function sync(BankConnection $c): array
    {
        $stats = ['created' => 0, 'linked' => 0, 'skipped' => 0, 'errors' => []];
        $client = $this->client($c);
        $accounts = BankConnectionAccount::where('user_id', $c->user_id)->where('target_type', '!=', 'none')
            ->whereNotNull('target_id')->whereIn('item_id', $c->itemIdList())->get();

        foreach ($accounts as $acc) {
            try {
                $importFrom = $acc->import_from ?? now()->startOfDay();
                // Revisita 10 dias para pegar o que o banco publicou atrasado
                $from = $acc->last_synced_at ? max($importFrom, $acc->last_synced_at->copy()->subDays(10)->startOfDay()) : $importFrom;
                $txs = $client->transactions($acc->provider_account_id, $from->toDateString(), now()->addDay()->toDateString());
                foreach ($txs as $tx) {
                    $r = $this->importOne($acc, $tx, $importFrom);
                    $stats[$r]++;
                }
                $acc->forceFill(['last_synced_at' => now(), 'imported_count' => BankSyncTransaction::where('connection_account_id', $acc->id)->whereNotNull('target_id')->count()])->save();
            } catch (\Throwable $e) {
                Log::warning('Bancos conectados: falha ao sincronizar', ['user' => $c->user_id, 'account' => $acc->id, 'error' => $e->getMessage()]);
                $stats['errors'][] = $acc->label() . ': ' . $e->getMessage();
            }
        }

        $c->forceFill(['last_sync_at' => now(), 'last_error' => $stats['errors'] ? mb_substr(implode(' | ', $stats['errors']), 0, 250) : null])->save();

        return $stats;
    }

    /** @return 'created'|'linked'|'skipped' */
    public function importOne(BankConnectionAccount $acc, array $tx, Carbon $importFrom): string
    {
        $txId = (string) ($tx['id'] ?? '');
        if ($txId === '' || BankSyncTransaction::where('user_id', $acc->user_id)->where('provider_tx_id', $txId)->exists()) {
            return 'skipped';
        }
        // Pendente pode mudar de valor ou sumir: espera o banco confirmar
        if (strtoupper((string) ($tx['status'] ?? 'POSTED')) === 'PENDING') {
            return 'skipped';
        }

        $date = Carbon::parse((string) ($tx['date'] ?? 'now'))->setTimezone('America/Sao_Paulo')->startOfDay();
        if ($date->lt($importFrom->copy()->startOfDay())) {
            return 'skipped';
        }

        $amount = abs(round((float) ($tx['amount'] ?? 0), 2));
        $type = strtoupper((string) ($tx['type'] ?? ''));
        $description = trim((string) ($tx['description'] ?? $tx['descriptionRaw'] ?? '')) ?: 'Transação do banco';

        return DB::transaction(function () use ($acc, $tx, $txId, $date, $amount, $type, $description) {
            $record = fn (?string $t, ?int $id, bool $linked) => BankSyncTransaction::create([
                'user_id' => $acc->user_id, 'connection_account_id' => $acc->id, 'provider_tx_id' => $txId,
                'target_type' => $t, 'target_id' => $id, 'linked_existing' => $linked,
            ]);

            if ($amount <= 0) {
                $record(null, null, false);

                return 'skipped';
            }

            if ($acc->isCard()) {
                // No cartão, DEBIT é compra; CREDIT é pagamento da fatura ou estorno
                $isPurchase = $type !== '' ? $type === 'DEBIT' : (float) $tx['amount'] > 0;
                if (! $isPurchase || $acc->target_type !== 'bank') {
                    $record(null, null, false);

                    return 'skipped';
                }
                $meta = (array) ($tx['creditCardMetadata'] ?? []);
                $number = (int) ($meta['installmentNumber'] ?? $tx['installmentNumber'] ?? 1);
                $total = (int) ($meta['totalInstallments'] ?? $tx['totalInstallments'] ?? 1);
                $installments = $total > 1 ? "{$number} de {$total}" : null;
                $kind = FinanceCapture::KIND_CARD;
                $bankId = (int) $acc->target_id;
                $accountId = null;
            } else {
                $kind = ($type !== '' ? $type === 'CREDIT' : (float) $tx['amount'] > 0) ? FinanceCapture::KIND_INCOME : FinanceCapture::KIND_EXPENSE;
                $bankId = null;
                $accountId = $acc->target_type === 'account' ? (int) $acc->target_id : null;
                [$number, $total, $installments] = [1, 1, null];
            }

            $existing = $this->writer->findExisting($acc->user_id, $kind, $amount, $date->toDateString(), $bankId, $accountId, $installments, true);
            if ($existing) {
                $record($existing[0], $existing[1], true);

                return 'linked';
            }

            [$t, $id] = $this->writer->create($acc->user_id, [
                'kind' => $kind,
                'amount' => $amount,
                'amount_is_installment' => true,
                'description' => $description,
                'date' => $date->toDateString(),
                'installments_total' => $total,
                'installment_number' => $number,
                'bank_id' => $bankId,
                'account_id' => $accountId,
                'category_id' => $this->writer->guessCategory($acc->user_id, $description, $kind === FinanceCapture::KIND_INCOME),
                'note' => 'Banco conectado (' . ($acc->institution ?: 'Open Finance') . ')',
            ]);
            $record($t, $id, false);

            return 'created';
        });
    }
}
