<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Lista "Tampo" (branco, preto e MDF imitando madeira) na conta do robô Stylo, para mesas com tampo. */
return new class extends Migration
{
    private const TAMPO = [['Branco', '#f4f3ef'], ['Preto', '#1c1c1c'], ['MDF madeira', '#b48a5a']];

    public function up(): void
    {
        if (! Schema::hasTable('choice_items') || ! Schema::hasTable('robo_tokens')) {
            return;
        }
        $now = now();
        foreach (DB::table('robo_tokens')->distinct()->pluck('user_id') as $uid) {
            if (! DB::table('users')->where('id', $uid)->exists()) {
                continue;
            }
            $cat = DB::table('choice_catalogs')->where('user_id', $uid)->whereRaw('LOWER(name) IN (?, ?)', ['tampo', 'tampos'])->first();
            $catId = $cat->id ?? DB::table('choice_catalogs')->insertGetId([
                'user_id' => $uid, 'name' => 'Tampo', 'kind' => 'outro',
                'position' => (int) DB::table('choice_catalogs')->where('user_id', $uid)->max('position') + 1,
                'created_at' => $now, 'updated_at' => $now,
            ]);
            $pos = (int) DB::table('choice_items')->where('choice_catalog_id', $catId)->max('position');
            foreach (self::TAMPO as [$name, $hex]) {
                if (! DB::table('choice_items')->where('choice_catalog_id', $catId)->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])->exists()) {
                    DB::table('choice_items')->insert([
                        'user_id' => $uid, 'choice_catalog_id' => $catId, 'name' => $name, 'hex' => $hex,
                        'extra_price' => 0, 'active' => true, 'position' => ++$pos, 'created_at' => $now, 'updated_at' => $now,
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
    }
};
