<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Configurações da área de Promoções, uma linha por usuário.
 *
 * min_margin_percent: lucro mínimo sobre o "a pagar" (price). O preço de
 * promoção nunca pode ficar abaixo de price × (1 + min_margin_percent / 100).
 */
class PromotionSetting extends Model
{
    public const DEFAULT_TEMPLATE = "🔥 *PROMOÇÃO* 🔥\n*{nome}*\n~De {de}~ por apenas *{por}* ({desconto} OFF)\n{validade}\nMe chama para garantir o seu! 💜";

    /** Modelos prontos de mensagem: chave => [nome, ícone, texto]. */
    public const TEMPLATES = [
        'classico' => ['Clássico', 'bi-fire', self::DEFAULT_TEMPLATE],
        'relampago' => ['Relâmpago', 'bi-lightning-charge-fill', "⚡ *OFERTA RELÂMPAGO* ⚡\n*{nome}*\nDe ~{de}~ por *{por}*\nVocê economiza {economia}! 😱\n{validade}\nCorre que acaba rápido! 🏃‍♀️"],
        'ultimas' => ['Últimas unidades', 'bi-hourglass-split', "⏳ *ÚLTIMAS UNIDADES* ⏳\n*{nome}*\nSó restam {estoque} com {desconto} OFF!\n~{de}~ ➜ *{por}*\nGaranta o seu antes que acabe 💜"],
        'semana' => ['Fim de semana', 'bi-calendar-heart', "🎉 *PROMO DE FIM DE SEMANA* 🎉\n*{nome}*\nDe ~{de}~ por apenas *{por}* ({desconto} OFF)\n{validade}\nAproveite! 🛍️"],
        'queima' => ['Queima de estoque', 'bi-tags-fill', "🔥🔥 *QUEIMA DE ESTOQUE* 🔥🔥\n*{nome}*\nAntes ~{de}~\nAgora *{por}* 🤑\n{validade}\nChama no privado! 📲"],
        'vip' => ['Cliente VIP', 'bi-gem', "💎 *Oferta exclusiva para você* 💎\nSeparei *{nome}* com {desconto} de desconto:\n~{de}~ por *{por}*\n{validade}\nQuer que eu reserve? 💜"],
        'presente' => ['Presente', 'bi-gift-fill', "🎁 *Dica de presente* 🎁\n*{nome}*\nDe ~{de}~ por *{por}*\nVocê economiza {economia} e ainda acerta no presente! 😍\n{validade}"],
        'curto' => ['Curto e direto', 'bi-chat-dots-fill', "*{nome}*\n~{de}~ ➜ *{por}* ({desconto} OFF) 🔥"],
    ];

    /** Cobranças: modelos de lembrete de parcela (vencendo / vencida). */
    public const COLLECTION_DUE_TEMPLATE = "Oi, {cliente}! Tudo bem? 😊\nPassando para lembrar que a parcela de *{valor}* ({descricao}) vence em *{vencimento}*.\nSe já pagou, pode desconsiderar. Obrigada! 💜\n{loja}";

    public const COLLECTION_OVERDUE_TEMPLATE = "Oi, {cliente}! Tudo bem?\nNotei que a parcela de *{valor}* ({descricao}), com vencimento em *{vencimento}*, está em aberto há {dias} dia(s).\nConsegue verificar para mim? Se já pagou, me envia o comprovante, por favor. 🙏\n{loja}";

    public const COLLECTION_TEMPLATES = [
        'due' => ['Cobrança - parcela vencendo', 'bi-calendar-check', 'collection_due_template', self::COLLECTION_DUE_TEMPLATE],
        'overdue' => ['Cobrança - parcela vencida', 'bi-exclamation-octagon', 'collection_overdue_template', self::COLLECTION_OVERDUE_TEMPLATE],
    ];

    public const COLLECTION_VARIABLES = [
        '{cliente}'    => 'Primeiro nome do cliente',
        '{valor}'      => 'Valor da parcela',
        '{vencimento}' => 'Data de vencimento',
        '{descricao}'  => 'Venda ou consórcio e parcela',
        '{dias}'       => 'Dias de atraso / até vencer',
        '{loja}'       => 'Nome da loja',
    ];

    /** Final do preço de promoção. */
    public const PRICE_ENDINGS = [
        'none' => ['Como calcular', 'R$ 36,47'],
        '90'   => ['Terminar em ,90', 'R$ 35,90'],
        '99'   => ['Terminar em ,99', 'R$ 35,99'],
        '00'   => ['Valor inteiro', 'R$ 36,00'],
    ];

    public const VARIABLES = [
        '{nome}'     => 'Nome do produto',
        '{de}'       => 'Preço original',
        '{por}'      => 'Preço da promoção',
        '{desconto}' => 'Desconto em %',
        '{economia}' => 'Quanto o cliente economiza',
        '{validade}' => 'Validade e estoque',
        '{estoque}'  => 'Quantidade em estoque',
        '{cliente}'  => 'Primeiro nome do cliente',
        '{link}'     => 'Link do catálogo',
    ];

    protected $fillable = [
        'user_id', 'min_margin_percent', 'suggest_min_discount', 'default_days',
        'message_template', 'footer', 'footer_catalog_link',
        'default_discount', 'price_ending', 'auto_end_out_of_stock', 'greet_client',
        'collection_due_template', 'collection_overdue_template',
    ];

    protected $casts = [
        'min_margin_percent'   => 'decimal:2',
        'suggest_min_discount' => 'decimal:2',
        'default_days'         => 'integer',
        'footer_catalog_link'  => 'boolean',
        'default_discount'     => 'decimal:2',
        'auto_end_out_of_stock' => 'boolean',
        'greet_client'         => 'boolean',
    ];

    /** Configuração do usuário, com os padrões quando ainda não salvou nada. */
    public static function forUser(?int $userId): self
    {
        if (!$userId) {
            return new self(self::defaults());
        }

        return static::firstOrNew(['user_id' => $userId], self::defaults());
    }

    public static function defaults(): array
    {
        return [
            'min_margin_percent'   => 5,
            'suggest_min_discount' => 10,
            'default_days'         => 7,
            'message_template'     => self::DEFAULT_TEMPLATE,
            'footer'               => "Pix, cartão ou parcelado. 💳",
            'footer_catalog_link'  => true,
            'default_discount'     => 10,
            'price_ending'         => 'none',
            'auto_end_out_of_stock' => true,
            'greet_client'         => true,
            'collection_due_template'     => self::COLLECTION_DUE_TEMPLATE,
            'collection_overdue_template' => self::COLLECTION_OVERDUE_TEMPLATE,
        ];
    }

    public function template(): string
    {
        return trim((string) $this->message_template) !== '' ? $this->message_template : self::DEFAULT_TEMPLATE;
    }

    /** Modelo de cobrança ('due' = vencendo, 'overdue' = vencida). */
    public function collectionTemplate(string $kind): string
    {
        [, , $column, $default] = self::COLLECTION_TEMPLATES[$kind] ?? self::COLLECTION_TEMPLATES['due'];
        $text = (string) ($this->getAttribute($column) ?? '');

        return trim($text) !== '' ? $text : $default;
    }
}
