<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Kit: opções que o cliente escolhe (cor do laço, tamanho…) e o que mais ele pode pedir (fotos, data). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('portal_kit_types', function (Blueprint $table) {
            if (! Schema::hasColumn('portal_kit_types', 'options')) {
                $table->json('options')->nullable();
            }
            if (! Schema::hasColumn('portal_kit_types', 'settings')) {
                $table->json('settings')->nullable();
            }
        });

        // Kits que já existiam ganham as opções prontas do seu estilo, que a loja pode mudar depois.
        foreach (\Illuminate\Support\Facades\DB::table('portal_kit_types')->whereNull('options')->get(['id', 'style']) as $t) {
            \Illuminate\Support\Facades\DB::table('portal_kit_types')->where('id', $t->id)->update([
                'options' => json_encode(array_map(fn ($k) => \App\Services\Portal\KitService::optionIdea($k), \App\Services\Portal\KitService::STARTER_OPTIONS[$t->style] ?? ['laco'])),
                'settings' => json_encode(['photos' => in_array($t->style, ['balao', 'caneca'], true) ? 4 : 0, 'date' => true]),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('portal_kit_types', function (Blueprint $table) {
            $table->dropColumn(['options', 'settings']);
        });
    }
};
