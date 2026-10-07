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
    ];

    protected $casts = [
        'min_margin_percent'   => 'decimal:2',
        'suggest_min_discount' => 'decimal:2',
        'default_days'         => 'integer',
        'footer_catalog_link'  => 'boolean',
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
        ];
    }

    public function template(): string
    {
        return trim((string) $this->message_template) !== '' ? $this->message_template : self::DEFAULT_TEMPLATE;
    }
}
