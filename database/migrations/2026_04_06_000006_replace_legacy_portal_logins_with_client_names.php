<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $reserved = [];

        DB::table('clients')
            ->select('id', 'name', 'portal_login')
            ->orderBy('id')
            ->chunkById(100, function ($clients) use (&$reserved): void {
                foreach ($clients as $client) {
                    $currentLogin = (string) ($client->portal_login ?? '');

                    if ($currentLogin !== '' && ! str_starts_with($currentLogin, 'cli-')) {
                        $reserved[strtolower($currentLogin)] = true;
                        continue;
                    }

                    $base = Str::slug(Str::ascii((string) $client->name), '-');
                    $base = preg_replace('/-+/', '-', trim((string) $base, '-'));
                    $base = $base !== '' ? Str::limit($base, 40, '') : 'cliente';

                    $candidate = $base;
                    $suffix = 2;

                    while (isset($reserved[strtolower($candidate)]) || DB::table('clients')
                        ->where('id', '!=', $client->id)
                        ->where('portal_login', $candidate)
                        ->exists()) {
                        $trimmedBase = Str::limit($base, max(1, 40 - strlen((string) $suffix) - 1), '');
                        $trimmedBase = rtrim($trimmedBase, '-');
                        $candidate = $trimmedBase . '-' . $suffix;
                        $suffix++;
                    }

                    DB::table('clients')
                        ->where('id', $client->id)
                        ->update(['portal_login' => $candidate]);

                    $reserved[strtolower($candidate)] = true;
                }
            });
    }

    public function down(): void
    {
        // Sem reversao automatica: os logins amigaveis passam a ser o formato oficial.
    }
};