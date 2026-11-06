<?php

namespace App\Services\Instagram;

use App\Models\InstagramSetting;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

/**
 * API do Instagram com login do Instagram (graph.instagram.com).
 * Conta profissional (Empresa ou Criador); não precisa de Página do Facebook.
 */
class InstagramClient
{
    public const VERSION = 'v23.0';
    public const SCOPES = 'instagram_business_basic,instagram_business_content_publish,instagram_business_manage_insights';

    public function __construct(private InstagramSetting $setting)
    {
    }

    public static function authorizeUrl(InstagramSetting $s, string $redirect, string $state): string
    {
        return 'https://www.instagram.com/oauth/authorize?' . http_build_query([
            'enable_fb_login' => 0,
            'force_authentication' => 1,
            'client_id' => $s->app_id,
            'redirect_uri' => $redirect,
            'response_type' => 'code',
            'scope' => self::SCOPES,
            'state' => $state,
        ]);
    }

    /** Troca o código do login por um acesso de 60 dias e guarda a conta. */
    public function connect(string $code, string $redirect): void
    {
        $short = Http::asForm()->timeout(20)->post('https://api.instagram.com/oauth/access_token', [
            'client_id' => $this->setting->app_id,
            'client_secret' => $this->setting->app_secret,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $redirect,
            'code' => $code,
        ]);
        $data = $this->json($short);
        $data = $data['data'][0] ?? $data;
        $token = $data['access_token'] ?? null;
        if (! $token) {
            throw new \RuntimeException('O Instagram não devolveu o acesso. Tente conectar de novo.');
        }

        $long = $this->json(Http::timeout(20)->get('https://graph.instagram.com/access_token', [
            'grant_type' => 'ig_exchange_token',
            'client_secret' => $this->setting->app_secret,
            'access_token' => $token,
        ]));

        $this->setting->access_token = $long['access_token'] ?? $token;
        $this->setting->token_expires_at = now()->addSeconds((int) ($long['expires_in'] ?? 3600));
        $this->setting->refreshed_at = now();
        $this->setting->connected_at = now();
        $this->setting->last_error = null;
        $this->setting->save();

        $this->loadProfile();
    }

    public function loadProfile(): array
    {
        $me = $this->get('me', ['fields' => 'user_id,username,name,profile_picture_url,account_type']);
        $this->setting->fill([
            'ig_user_id' => (string) ($me['user_id'] ?? $me['id'] ?? ''),
            'username' => $me['username'] ?? null,
            'name' => $me['name'] ?? null,
            'picture_url' => $me['profile_picture_url'] ?? null,
            'account_type' => $me['account_type'] ?? null,
        ])->save();

        return $me;
    }

    /** Renova o acesso por mais 60 dias (a Meta só renova acesso com mais de 24 horas). */
    public function refresh(): void
    {
        $r = $this->json(Http::timeout(20)->get('https://graph.instagram.com/refresh_access_token', [
            'grant_type' => 'ig_refresh_token',
            'access_token' => $this->setting->access_token,
        ]));
        $this->setting->access_token = $r['access_token'] ?? $this->setting->access_token;
        $this->setting->token_expires_at = now()->addSeconds((int) ($r['expires_in'] ?? 5184000));
        $this->setting->refreshed_at = now();
        $this->setting->save();
    }

    /** Cria o "container" de uma foto. $mode: '' (post de uma foto), 'item' (do carrossel) ou 'story'. */
    public function createImage(string $imageUrl, string $mode = '', ?string $caption = null): string
    {
        $params = ['image_url' => $imageUrl];
        if ($mode === 'item') {
            $params['is_carousel_item'] = 'true';
        } elseif ($mode === 'story') {
            $params['media_type'] = 'STORIES';
        } elseif ($caption !== null && $caption !== '') {
            $params['caption'] = $caption;
        }

        return (string) $this->post($this->setting->ig_user_id . '/media', $params)['id'];
    }

    /** Container do Reels: a Meta baixa o vídeo e processa (pode levar alguns minutos). */
    public function createReel(string $videoUrl, string $caption, ?string $coverUrl = null, bool $shareToFeed = true): string
    {
        $params = ['media_type' => 'REELS', 'video_url' => $videoUrl, 'caption' => $caption, 'share_to_feed' => $shareToFeed ? 'true' : 'false'];
        if ($coverUrl) {
            $params['cover_url'] = $coverUrl;
        }

        return (string) $this->post($this->setting->ig_user_id . '/media', $params)['id'];
    }

    /**
     * Resultados de um post: curtidas e comentários (campos do post) e alcance, visualizações,
     * salvos, compartilhados (insights). Métrica que a Meta não aceita para aquele tipo é pulada.
     */
    public function results(string $mediaId, string $kind): array
    {
        $out = [];
        try {
            $f = $this->get($mediaId, ['fields' => 'like_count,comments_count,permalink']);
            $out['likes'] = (int) ($f['like_count'] ?? 0);
            $out['comments'] = (int) ($f['comments_count'] ?? 0);
            if (! empty($f['permalink'])) {
                $out['permalink'] = $f['permalink'];
            }
        } catch (\Throwable $e) {
            if (str_contains($e->getMessage(), 'venceu')) {
                throw $e;
            }
        }

        $wanted = $kind === 'story' ? ['reach', 'views', 'replies', 'shares'] : ['reach', 'views', 'saved', 'shares'];
        foreach ([$wanted, ...array_map(fn ($m) => [$m], $wanted)] as $i => $set) {
            try {
                $r = $this->get($mediaId . '/insights', ['metric' => implode(',', $set)]);
                foreach ($r['data'] ?? [] as $row) {
                    $out[$row['name']] = (int) ($row['values'][0]['value'] ?? $row['total_value']['value'] ?? 0);
                }
                if ($i === 0) {
                    break; // todas de uma vez deu certo
                }
            } catch (\Throwable $e) {
                if (str_contains($e->getMessage(), 'permissão') && $i === 0) {
                    $out['_no_insights'] = true;
                    break;
                }
            }
        }

        return $out;
    }

    public function createCarousel(array $children, string $caption): string
    {
        return (string) $this->post($this->setting->ig_user_id . '/media', [
            'media_type' => 'CAROUSEL',
            'children' => implode(',', $children),
            'caption' => $caption,
        ])['id'];
    }

    /** FINISHED, IN_PROGRESS, ERROR, EXPIRED ou PUBLISHED. */
    public function containerStatus(string $id): string
    {
        return (string) ($this->get($id, ['fields' => 'status_code'])['status_code'] ?? 'FINISHED');
    }

    public function publish(string $creationId): string
    {
        return (string) $this->post($this->setting->ig_user_id . '/media_publish', ['creation_id' => $creationId])['id'];
    }

    public function permalink(string $mediaId): ?string
    {
        try {
            return $this->get($mediaId, ['fields' => 'permalink'])['permalink'] ?? null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /** Quantos posts pela API nas últimas 24 horas (limite da Meta: 100). */
    public function usage(): ?int
    {
        try {
            $r = $this->get($this->setting->ig_user_id . '/content_publishing_limit', ['fields' => 'quota_usage']);

            return isset($r['data'][0]['quota_usage']) ? (int) $r['data'][0]['quota_usage'] : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function get(string $path, array $query = []): array
    {
        return $this->json(Http::timeout(30)->get($this->url($path), $query + ['access_token' => $this->setting->access_token]));
    }

    private function post(string $path, array $params): array
    {
        return $this->json(Http::asForm()->timeout(60)->post($this->url($path), $params + ['access_token' => $this->setting->access_token]));
    }

    private function url(string $path): string
    {
        return 'https://graph.instagram.com/' . self::VERSION . '/' . ltrim($path, '/');
    }

    private function json(Response $r): array
    {
        $data = $r->json() ?? [];
        if ($r->failed() || isset($data['error']) || isset($data['error_type'])) {
            throw new \RuntimeException(self::friendlyError($data, $r->status()));
        }

        return $data;
    }

    /** Erros da Meta em português. */
    public static function friendlyError(array $data, int $status = 0): string
    {
        $e = $data['error'] ?? $data;
        $code = (int) ($e['code'] ?? 0);
        $sub = (int) ($e['error_subcode'] ?? 0);
        $msg = (string) ($e['error_user_msg'] ?? $e['message'] ?? $e['error_message'] ?? '');

        return match (true) {
            $code === 190 => 'O acesso ao Instagram venceu ou foi removido. Conecte a conta de novo em Configurações › Instagram.',
            in_array($code, [4, 9, 17, 32, 613], true) || $sub === 2207042 => 'O Instagram chegou no limite de posts por agora (100 por dia). Tente mais tarde.',
            $sub === 2207004 || $sub === 2207052 => 'O Instagram não conseguiu baixar a foto. Confira se o site está no ar e tente de novo.',
            $sub === 2207009 => 'Formato de foto não aceito pelo Instagram (precisa ser entre retrato 4:5 e paisagem 1.91:1).',
            $code === 10 || $code === 200 => 'O app da Meta não tem permissão para isso. Confira se o Instagram está como testador e conecte de novo.',
            $sub === 2207026 || str_contains(strtolower($msg), 'video') => 'O Instagram não aceitou o vídeo. Use MP4 ou MOV vertical (9:16), de 3 segundos a 15 minutos.',
            str_contains(strtolower($msg), 'redirect_uri') => 'O endereço de retorno não confere. Cole na Meta o "Endereço de retorno" que aparece em Configurações › Instagram.',
            str_contains(strtolower($msg), 'client_secret') || str_contains(strtolower($msg), 'invalid platform app') => 'O ID ou a chave secreta do app do Instagram não conferem. Copie de novo da Meta.',
            default => 'O Instagram recusou' . ($status ? " ({$status})" : '') . ($msg !== '' ? ': ' . $msg : '.'),
        };
    }
}
