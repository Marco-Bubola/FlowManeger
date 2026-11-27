<?php

use App\Support\Choices\ChoiceDefaults;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Coloca o mostruário de corinos (18 cores com código) na conta que usa o robô Stylo,
 * quando ela ainda não tem nenhuma lista de estofados. Não mexe em quem já cadastrou.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('choice_items') || ! Schema::hasTable('robo_tokens')) {
            return;
        }
        $now = now();
        foreach (DB::table('robo_tokens')->distinct()->pluck('user_id') as $uid) {
            if (! DB::table('users')->where('id', $uid)->exists()
                || DB::table('choice_catalogs')->where('user_id', $uid)->where('kind', 'estofado')->exists()) {
                continue;
            }
            $catId = DB::table('choice_catalogs')->insertGetId([
                'user_id' => $uid, 'name' => 'Estofados', 'kind' => 'estofado',
                'position' => (int) DB::table('choice_catalogs')->where('user_id', $uid)->max('position') + 1,
                'created_at' => $now, 'updated_at' => $now,
            ]);
            $pos = 0;
            foreach (ChoiceDefaults::CORINO as [$name, $code, $hex]) {
                DB::table('choice_items')->insert([
                    'user_id' => $uid, 'choice_catalog_id' => $catId, 'material' => 'Corino', 'name' => $name,
                    'code' => $code, 'hex' => $hex, 'extra_price' => 0, 'active' => true, 'position' => ++$pos,
                    'created_at' => $now, 'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
    }
};
