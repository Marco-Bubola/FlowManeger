<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Camurça marrom e sépia (usadas nas fichas PTU e PAR) na lista de estofados da conta do robô Stylo. */
return new class extends Migration
{
    private const CAMURCA = [['Marrom', '#6b4a33'], ['Sépia', '#cdb79a']];

    public function up(): void
    {
        if (! Schema::hasTable('choice_items') || ! Schema::hasTable('robo_tokens')) {
            return;
        }
        $now = now();
        foreach (DB::table('robo_tokens')->distinct()->pluck('user_id') as $uid) {
            $cat = DB::table('choice_catalogs')->where('user_id', $uid)->where('kind', 'estofado')->orderBy('position')->orderBy('id')->first();
            if (! $cat) {
                continue;
            }
            $pos = (int) DB::table('choice_items')->where('choice_catalog_id', $cat->id)->max('position');
            foreach (self::CAMURCA as [$name, $hex]) {
                $exists = DB::table('choice_items')->where('choice_catalog_id', $cat->id)->where('material', 'Camurça')
                    ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])->exists();
                if (! $exists) {
                    DB::table('choice_items')->insert([
                        'user_id' => $uid, 'choice_catalog_id' => $cat->id, 'material' => 'Camurça', 'name' => $name,
                        'hex' => $hex, 'extra_price' => 0, 'active' => true, 'position' => ++$pos,
                        'created_at' => $now, 'updated_at' => $now,
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
    }
};
