<?php

namespace App\Http\Controllers\Robo;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\RoboFicha;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Fichas do Robô Móveis Stylo no banco do site. Não tem tela: só o robô usa.
 *
 * GET  /robo/conectar             (logado no site) devolve a chave do robô
 * GET  /robo-api/fichas           lista
 * GET  /robo-api/fichas/ver       ?pasta=  conteúdo + arquivos
 * GET  /robo-api/fichas/arquivo   ?pasta=&nome=
 * POST /robo-api/fichas/salvar    pasta, conteudo, situacao, arquivos (json [{nome, sha1}]), versao_base, editada_em
 * POST /robo-api/fichas/arquivo   pasta, nome, arquivo (um por vez)
 * POST /robo-api/fichas/excluir   pasta  (não apaga: some da lista, os arquivos ficam)
 * GET  /robo-api/estoque          ?codigos=A,B,C  estoque no site de cada código
 * POST /robo-api/estoque/somar    itens (json [{codigo, somar}])  soma (ou tira) do estoque do site
 *
 * Nada é apagado de verdade: arquivo tirado da ficha vai para _lixeira e o conteúdo anterior fica em historico.
 */
class FichasController extends Controller
{
    private const EXTS = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'md', 'txt', 'json', 'csv', 'pdf'];

    private const MAX_KB = 30720;

    public function conectar(Request $request): JsonResponse
    {
        $chave = Str::random(56);
        $nome = Str::limit((string) $request->query('pc', 'Robô Móveis Stylo'), 110, '');
        // Um PC conectando de novo troca a chave: a antiga para de funcionar.
        DB::table('robo_tokens')->where('user_id', $request->user()->id)->where('nome', $nome)->delete();
        DB::table('robo_tokens')->insert([
            'user_id' => $request->user()->id,
            'token_hash' => hash('sha256', $chave),
            'nome' => $nome,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['robo_chave' => $chave, 'usuario' => $request->user()->name])
            ->header('Cache-Control', 'no-store');
    }

    public function eu(Request $request): JsonResponse
    {
        return response()->json(['usuario' => $request->user()->name, 'fichas' => $this->query($request)->count()]);
    }

    public function index(Request $request): JsonResponse
    {
        $fichas = $this->query($request)->withTrashed()->orderBy('pasta')->get()->map(fn (RoboFicha $f) => $this->resumo($f));

        return response()->json(['fichas' => $fichas]);
    }

    public function ver(Request $request): JsonResponse
    {
        $f = $this->achar($request, $request->query('pasta'));
        abort_unless($f, 404, 'Ficha não encontrada.');

        return response()->json($this->resumo($f) + ['conteudo' => $f->conteudo]);
    }

    public function arquivo(Request $request)
    {
        $f = $this->achar($request, $request->query('pasta'));
        $nome = $this->nomeArquivo($request->query('nome'));
        abort_unless($f && $nome && collect($f->arquivos ?? [])->contains('nome', $nome), 404, 'Arquivo não encontrado.');
        $caminho = $f->dir() . '/' . $nome;
        abort_unless(Storage::disk('local')->exists($caminho), 404, 'Arquivo não encontrado.');

        return response()->file(Storage::disk('local')->path($caminho), ['Cache-Control' => 'no-store']);
    }

    public function salvar(Request $request): JsonResponse
    {
        $dados = $request->validate([
            'pasta' => ['required', 'string', 'max:191'],
            'conteudo' => ['nullable', 'string', 'max:2000000'],
            'situacao' => ['nullable', 'in:aberta,feita'],
            'arquivos' => ['nullable', 'string'],
            'versao_base' => ['nullable', 'integer'],
            'editada_em' => ['nullable', 'date'],
        ]);
        $pasta = $this->nomePasta($dados['pasta']);
        abort_unless($pasta, 422, 'Nome de pasta inválido.');
        $lista = collect(json_decode($dados['arquivos'] ?? '[]', true) ?: [])
            ->map(fn ($a) => ['nome' => $this->nomeArquivo($a['nome'] ?? null), 'sha1' => strtolower((string) ($a['sha1'] ?? ''))])
            ->filter(fn ($a) => $a['nome'] && preg_match('/^[a-f0-9]{40}$/', $a['sha1']))
            ->unique('nome')->values();

        $f = $this->query($request)->withTrashed()->where('pasta', $pasta)->first()
            ?? new RoboFicha(['user_id' => $request->user()->id, 'pasta' => $pasta, 'arquivos' => [], 'historico' => []]);
        $conflito = $f->exists && isset($dados['versao_base']) && (int) $dados['versao_base'] < $f->versao;

        if ($f->exists && $f->conteudo !== null && $f->conteudo !== ($dados['conteudo'] ?? null)) {
            $hist = $f->historico ?? [];
            array_unshift($hist, ['em' => now()->toIso8601String(), 'versao' => $f->versao, 'conteudo' => $f->conteudo]);
            $f->historico = array_slice($hist, 0, 10);
        }
        $info = $this->infoDaFicha($dados['conteudo'] ?? '');
        $f->fill([
            'conteudo' => $dados['conteudo'] ?? null,
            'situacao' => $dados['situacao'] ?? 'aberta',
            'codigo' => $info['codigo'],
            'nome' => $info['nome'] ?: $pasta,
            'editada_em' => isset($dados['editada_em']) ? \Illuminate\Support\Carbon::parse($dados['editada_em']) : now(),
        ]);
        $f->deleted_at = null;
        $f->versao = $f->versao + 1;
        $f->save();

        // Arquivos: os que saíram da ficha vão para _lixeira; os novos/mudados o robô manda depois.
        $atuais = collect($f->arquivos ?? [])->keyBy('nome');
        $disco = Storage::disk('local');
        foreach ($atuais as $nome => $a) {
            if (! $lista->contains('nome', $nome) && $disco->exists($f->dir() . '/' . $nome)) {
                $disco->move($f->dir() . '/' . $nome, $f->dir() . '/_lixeira/' . now()->format('Ymd-His') . '-' . $nome);
            }
        }
        $faltam = [];
        $novos = [];
        foreach ($lista as $a) {
            $tem = $atuais->get($a['nome']);
            if ($tem && ($tem['sha1'] ?? '') === $a['sha1'] && $disco->exists($f->dir() . '/' . $a['nome'])) {
                $novos[] = $tem;
            } else {
                $faltam[] = $a['nome'];
                $novos[] = ['nome' => $a['nome'], 'sha1' => $a['sha1'], 'tamanho' => null, 'pendente' => true];
            }
        }
        $f->arquivos = $novos;
        $f->completa = $faltam === [];
        $f->save();

        return response()->json($this->resumo($f) + ['faltam' => $faltam, 'conflito' => $conflito]);
    }

    public function enviarArquivo(Request $request): JsonResponse
    {
        $request->validate([
            'pasta' => ['required', 'string'],
            'nome' => ['required', 'string'],
            'arquivo' => ['required', 'file', 'max:' . self::MAX_KB],
        ]);
        $f = $this->achar($request, $request->input('pasta'));
        $nome = $this->nomeArquivo($request->input('nome'));
        abort_unless($f && $nome, 404, 'Ficha não encontrada.');
        $lista = collect($f->arquivos ?? []);
        $pos = $lista->search(fn ($a) => ($a['nome'] ?? null) === $nome);
        abort_if($pos === false, 422, 'Esse arquivo não está na ficha. Salve a ficha antes.');

        $arq = $request->file('arquivo');
        $sha1 = sha1_file($arq->getRealPath());
        $esperado = $lista[$pos]['sha1'] ?? null;
        abort_if($esperado && $esperado !== $sha1, 422, 'O arquivo chegou diferente (internet?). Tente de novo.');
        Storage::disk('local')->putFileAs($f->dir(), $arq, $nome);

        $novos = $lista->all();
        $novos[$pos] = ['nome' => $nome, 'sha1' => $sha1, 'tamanho' => $arq->getSize()];
        $f->arquivos = $novos;
        $f->completa = ! collect($novos)->contains(fn ($a) => ! empty($a['pendente']));
        $f->save();

        return response()->json($this->resumo($f));
    }

    public function excluir(Request $request): JsonResponse
    {
        $f = $this->achar($request, $request->input('pasta'));
        if ($f) {
            $f->versao = $f->versao + 1;
            $f->save();
            $f->delete();
        }

        return response()->json(['ok' => true]);
    }

    // ------------------------------------------------------------------

    /** Estoque no site dos códigos da ficha (só os produtos desta conta). */
    public function estoque(Request $request): JsonResponse
    {
        $codigos = collect(explode(',', (string) $request->query('codigos', '')))
            ->map(fn ($c) => trim($c))->filter()->unique()->take(300)->values();
        $achados = $this->produtosPorCodigo($request, $codigos->all());

        return response()->json(['produtos' => $achados->map(fn ($ps) => $this->infoEstoque($ps))]);
    }

    /** Soma (ou tira, com número negativo) do estoque do site. Kit não tem estoque próprio. */
    public function somarEstoque(Request $request): JsonResponse
    {
        $itens = json_decode((string) $request->input('itens', '[]'), true);
        abort_unless(is_array($itens) && count($itens) <= 300, 422, 'Lista inválida.');
        $pedidos = [];
        foreach ($itens as $i) {
            $cod = trim((string) ($i['codigo'] ?? ''));
            $n = (int) ($i['somar'] ?? 0);
            if ($cod !== '' && $n !== 0) {
                $pedidos[mb_strtolower($cod)] = ['codigo' => $cod, 'somar' => max(-10000, min(10000, $n))];
            }
        }
        $achados = $this->produtosPorCodigo($request, array_column($pedidos, 'codigo'));
        $res = [];
        foreach ($pedidos as $k => $ped) {
            $ps = $achados->get($k);
            if (! $ps) {
                $res[$k] = ['codigo' => $ped['codigo'], 'erro' => 'Não está no site.'];
                continue;
            }
            /** @var Product $p */
            $p = $ps->first();
            if ($p->isKit()) {
                $res[$k] = $this->infoEstoque($ps) + ['erro' => 'É kit: o estoque vem dos itens.'];
                continue;
            }
            $antes = (int) $p->stock_quantity;
            $p->adjustStock(max($ped['somar'], -$antes));    // nunca fica negativo
            $res[$k] = $this->infoEstoque(collect([$p->refresh()])->merge($ps->slice(1))) + ['antes' => $antes];
        }

        return response()->json(['produtos' => $res]);
    }

    /** código em minúsculas → produtos com esse código (o mais antigo primeiro). */
    private function produtosPorCodigo(Request $request, array $codigos)
    {
        if (! $codigos) {
            return collect();
        }

        return Product::withoutGlobalScope('team_visibility')
            ->where('user_id', $request->user()->id)
            ->whereIn('product_code', $codigos)
            ->orderBy('id')
            ->get()
            ->groupBy(fn (Product $p) => mb_strtolower((string) $p->product_code));
    }

    private function infoEstoque($ps): array
    {
        /** @var Product $p */
        $p = $ps->first();

        return [
            'id' => $p->id,
            'codigo' => $p->product_code,
            'nome' => $p->name,
            'estoque' => $p->isKit() ? $p->availableStock() : (int) $p->stock_quantity,
            'kit' => $p->isKit(),
            'encomenda' => (bool) $p->made_to_order,
            'venda' => (float) $p->price_sale,
            'repetido' => $ps->count() > 1,
        ];
    }

    private function query(Request $request)
    {
        return RoboFicha::query()->where('user_id', $request->user()->id);
    }

    private function achar(Request $request, $pasta): ?RoboFicha
    {
        $pasta = $this->nomePasta($pasta);

        return $pasta ? $this->query($request)->where('pasta', $pasta)->first() : null;
    }

    private function resumo(RoboFicha $f): array
    {
        return [
            'pasta' => $f->pasta,
            'situacao' => $f->situacao,
            'codigo' => $f->codigo,
            'nome' => $f->nome,
            'versao' => $f->versao,
            'completa' => (bool) $f->completa,
            'excluida' => $f->trashed(),
            'atualizada' => optional($f->updated_at)->toIso8601String(),
            'editada_em' => optional($f->editada_em ?? $f->updated_at)->toIso8601String(),
            'arquivos' => collect($f->arquivos ?? [])->map(fn ($a) => ['nome' => $a['nome'], 'sha1' => $a['sha1'] ?? null, 'tamanho' => $a['tamanho'] ?? null])->values(),
        ];
    }

    private function nomePasta($p): ?string
    {
        $p = trim((string) $p);
        if ($p === '' || mb_strlen($p) > 191 || str_starts_with($p, '.') || preg_match('#[/\\\\:*?"<>|\x00-\x1f]#', $p)) {
            return null;
        }

        return $p;
    }

    private function nomeArquivo($n): ?string
    {
        $n = trim((string) $n);
        if ($n === '' || mb_strlen($n) > 180 || str_starts_with($n, '.') || preg_match('#[/\\\\:*?"<>|\x00-\x1f]#', $n)) {
            return null;
        }

        return in_array(strtolower(pathinfo($n, PATHINFO_EXTENSION)), self::EXTS, true) ? $n : null;
    }

    /** Lê nome e código do JSON dentro do ficha.md (só para a lista). */
    private function infoDaFicha(string $texto): array
    {
        $json = null;
        if (preg_match('/```json\s*(\{.*\})\s*```/s', $texto, $m)) {
            $json = json_decode($m[1], true);
        }
        $json ??= json_decode($texto, true);

        return [
            'nome' => is_array($json) ? Str::limit((string) ($json['nome'] ?? ''), 250, '') : '',
            'codigo' => is_array($json) ? (Str::limit((string) ($json['codigo'] ?? ''), 58, '') ?: null) : null,
        ];
    }

    /**
     * Tabela de materiais e horas (a mesma de Orçamentos › Materiais e horas).
     * Formato do robô: {cat, nome, unid, preco, nota, est}.
     */
    public function materiais(Request $request): JsonResponse
    {
        $uid = (int) $request->user()->id;
        $itens = \App\Services\Quotes\QuoteService::materials($uid)->map(fn ($m) => [
            'id' => $m->id, 'cat' => $m->category, 'nome' => $m->name, 'unid' => $m->unit,
            'preco' => (float) $m->price, 'nota' => (string) $m->note, 'est' => (bool) $m->estimated,
        ])->values();
        $quando = \App\Models\QuoteMaterial::where('user_id', $uid)->max('updated_at');

        return response()->json(['itens' => $itens, 'atualizado_em' => $quando ? \Illuminate\Support\Carbon::parse($quando)->toIso8601String() : null]);
    }

    /** O robô salvou a tabela: o site fica igual (muda, cria e tira o que saiu). */
    public function salvarMateriais(Request $request): JsonResponse
    {
        $uid = (int) $request->user()->id;
        $itens = json_decode((string) $request->input('itens', '[]'), true);
        abort_unless(is_array($itens) && count($itens) <= 2000, 422, 'Lista inválida.');
        $atual = \App\Models\QuoteMaterial::where('user_id', $uid)->get();
        $porId = $atual->keyBy('id');
        $porNome = $atual->keyBy(fn ($m) => mb_strtolower($m->name));
        $vistos = [];
        DB::transaction(function () use ($itens, $uid, $porId, $porNome, &$vistos) {
            foreach (array_values($itens) as $pos => $i) {
                $nome = mb_substr(trim((string) ($i['nome'] ?? '')), 0, 160);
                if ($nome === '') {
                    continue;
                }
                $m = (!empty($i['id']) ? $porId->get((int) $i['id']) : null) ?? $porNome->get(mb_strtolower($nome)) ?? new \App\Models\QuoteMaterial(['user_id' => $uid]);
                $m->fill([
                    'category' => mb_substr(trim((string) ($i['cat'] ?? 'Outros')) ?: 'Outros', 0, 80),
                    'name' => $nome,
                    'unit' => mb_substr(trim((string) ($i['unid'] ?? 'un')) ?: 'un', 0, 20),
                    'price' => max(0, round((float) ($i['preco'] ?? 0), 2)),
                    'note' => mb_substr(trim((string) ($i['nota'] ?? '')), 0, 200) ?: null,
                    'estimated' => (bool) ($i['est'] ?? false),
                    'position' => ($pos + 1) * 10,
                ]);
                $m->user_id = $uid;
                $m->save();
                $vistos[] = $m->id;
            }
            \App\Models\QuoteMaterial::where('user_id', $uid)->whereNotIn('id', $vistos ?: [0])->delete();
        });

        return $this->materiais($request);
    }

    // ---------------------------------------------------------------- catálogo de estofados e cores
    // O mesmo de Orçamentos › Estofados e cores. O robô mostra em "Minhas cores" e cadastra/apaga por aqui.

    public function escolhas(Request $request): JsonResponse
    {
        $uid = (int) $request->user()->id;
        $listas = \App\Services\Choices\ChoiceService::catalogs($uid)->map(fn ($c) => [
            'id' => $c->id, 'nome' => $c->name, 'tipo' => $c->kind,
            'itens' => $c->items->map(fn ($i) => [
                'id' => $i->id, 'tecido' => $i->material, 'nome' => $i->name, 'codigo' => $i->code, 'hex' => $i->hex,
                'foto' => $i->imageUrl(), 'extra' => (float) $i->extra_price, 'ativo' => (bool) $i->active,
            ])->values(),
        ])->values();

        return response()->json(['listas' => $listas, 'ok' => \App\Services\Choices\ChoiceService::ready()]);
    }

    /** O robô cadastrou uma cor: entra na lista do tipo (cria a lista se ainda não existe). */
    public function salvarEscolha(Request $request): JsonResponse
    {
        $uid = (int) $request->user()->id;
        abort_unless(\App\Services\Choices\ChoiceService::ready(), 422, 'O site ainda não tem o catálogo de estofados e cores.');
        $v = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'tipo' => 'required|in:estofado,ferragem,outro',
            'lista' => 'nullable|string|max:80',
            'tecido' => 'nullable|string|max:80',
            'nome' => 'required|string|max:80',
            'codigo' => 'nullable|string|max:30',
            'hex' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ]);
        abort_if($v->fails(), 422, $v->errors()->first());
        $d = $v->validated();
        $nomeLista = trim((string) ($d['lista'] ?? '')) ?: ($d['tipo'] === 'ferragem' ? 'Cores de ferragem' : ($d['tipo'] === 'estofado' ? 'Estofados' : 'Outras cores'));
        $cat = \App\Models\ChoiceCatalog::where('user_id', $uid)->where('kind', $d['tipo'])
            ->when($d['tipo'] === 'outro', fn ($q) => $q->where('name', $nomeLista))->orderBy('position')->orderBy('id')->first()
            ?? \App\Models\ChoiceCatalog::create(['user_id' => $uid, 'name' => $nomeLista, 'kind' => $d['tipo'],
                'position' => (int) \App\Models\ChoiceCatalog::where('user_id', $uid)->max('position') + 1]);
        $tecido = trim((string) ($d['tecido'] ?? '')) ?: null;
        $item = \App\Models\ChoiceItem::where('choice_catalog_id', $cat->id)->whereRaw('LOWER(name) = ?', [mb_strtolower(trim($d['nome']))])
            ->when($tecido, fn ($q) => $q->where('material', $tecido), fn ($q) => $q->whereNull('material'))->first();
        if (! $item) {
            // Mesmo tecido: o valor a mais das outras cores dele vale para a nova.
            $extra = $tecido ? (float) \App\Models\ChoiceItem::where('choice_catalog_id', $cat->id)->where('material', $tecido)->max('extra_price') : 0;
            $item = \App\Models\ChoiceItem::create(['user_id' => $uid, 'choice_catalog_id' => $cat->id, 'material' => $tecido,
                'name' => trim($d['nome']), 'extra_price' => $extra, 'active' => true,
                'position' => (int) \App\Models\ChoiceItem::where('choice_catalog_id', $cat->id)->max('position') + 1]);
        }
        $item->fill(array_filter(['hex' => $d['hex'] ?? null, 'code' => $d['codigo'] ?? null]))->save();

        return $this->escolhas($request);
    }

    public function excluirEscolha(Request $request): JsonResponse
    {
        $uid = (int) $request->user()->id;
        if ($item = \App\Models\ChoiceItem::where('user_id', $uid)->find((int) $request->input('id'))) {
            if ($item->image && \App\Models\ChoiceItem::where('image', $item->image)->count() === 1) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($item->image);
            }
            $item->delete();
        }

        return $this->escolhas($request);
    }
}
