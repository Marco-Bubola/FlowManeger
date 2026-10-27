<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pontos de fidelidade do catálogo (portal_points: cada linha ganha ou gasta pontos)
 * e, no pedido, os pontos usados e o desconto do Pix (guardados na hora do pedido).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('portal_points')) {
            Schema::create('portal_points', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('user_id');
                $t->unsignedBigInteger('client_id');
                $t->unsignedBigInteger('quote_id')->nullable();
                $t->integer('points');
                $t->string('kind', 12); // ganho | uso | estorno | bonus | ajuste | expirou
                $t->string('note', 160)->nullable();
                $t->timestamps();
                $t->index(['user_id', 'client_id']);
                $t->index(['quote_id', 'kind']);
            });
        }

        if (Schema::hasTable('client_quote_requests')) {
            Schema::table('client_quote_requests', function (Blueprint $t) {
                if (! Schema::hasColumn('client_quote_requests', 'points_used')) {
                    $t->integer('points_used')->nullable();
                    $t->decimal('points_discount', 10, 2)->nullable();
                    $t->decimal('pix_pct', 5, 2)->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('portal_points');
        if (Schema::hasColumn('client_quote_requests', 'points_used')) {
            Schema::table('client_quote_requests', fn (Blueprint $t) => $t->dropColumn(['points_used', 'points_discount', 'pix_pct']));
        }
    }
};
