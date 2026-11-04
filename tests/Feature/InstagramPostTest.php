<?php

namespace Tests\Feature;

use App\Models\InstagramPost;
use App\Models\InstagramSetting;
use App\Models\User;
use App\Services\Instagram\CaptionBuilder;
use App\Services\Instagram\InstagramClient;
use App\Services\Instagram\InstagramImages;
use App\Services\Instagram\InstagramPublisher;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class InstagramPostTest extends TestCase
{
    private User $user;
    private InstagramSetting $setting;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-11-04 15:00:00');
        Storage::fake('public');
        InstagramPublisher::$sleeper = fn () => null;

        Schema::create('users', function (Blueprint $t) {
            $t->id(); $t->string('name'); $t->string('email'); $t->string('password'); $t->string('phone')->nullable(); $t->text('preferences')->nullable(); $t->timestamps();
        });
        Artisan::call('migrate', ['--path' => 'database/migrations/2026_11_04_120000_create_instagram_tables.php', '--force' => true]);

        $this->user = User::forceCreate(['name' => 'Loja', 'email' => 'a@b.c', 'password' => 'x']);
        $this->setting = InstagramSetting::forUser($this->user->id);
        $this->setting->fill([
            'app_id' => '1234567890123456', 'app_secret' => str_repeat('a', 32), 'access_token' => 'IGTOKEN',
            'token_expires_at' => now()->addDays(50), 'ig_user_id' => '1789', 'username' => 'loja',
        ])->save();
    }

    private function makePost(array $images, string $kind = 'feed', array $extra = []): InstagramPost
    {
        foreach ($images as $p) {
            Storage::disk('public')->put($p, 'x');
        }

        return InstagramPost::create($extra + [
            'user_id' => $this->user->id, 'kind' => $kind, 'format' => 'quadrado', 'caption' => 'Oi #a',
            'images' => $images, 'status' => InstagramPost::AGENDADO, 'scheduled_at' => now(),
        ]);
    }

    public function test_single_photo_is_published_with_caption(): void
    {
        Http::fake([
            'graph.instagram.com/*/1789/media_publish' => Http::response(['id' => 'M1']),
            'graph.instagram.com/*/1789/media' => Http::response(['id' => 'C1']),
            'graph.instagram.com/*/C1*' => Http::response(['status_code' => 'FINISHED']),
            'graph.instagram.com/*/M1*' => Http::response(['permalink' => 'https://instagram.com/p/abc']),
        ]);

        $post = (new InstagramPublisher())->publish($this->makePost(['instagram/1/a.jpg']));

        $this->assertSame(InstagramPost::POSTADO, $post->status);
        $this->assertSame('M1', $post->media_id);
        $this->assertSame('https://instagram.com/p/abc', $post->permalink);
        Http::assertSent(fn ($r) => str_ends_with($r->url(), '/1789/media') && $r['caption'] === 'Oi #a' && str_contains($r['image_url'], 'instagram/1/a.jpg'));
    }

    public function test_carousel_creates_children_then_carousel(): void
    {
        $n = 0;
        Http::fake(function ($r) use (&$n) {
            $url = $r->url();
            if (str_ends_with($url, '/1789/media_publish')) {
                return Http::response(['id' => 'M9']);
            }
            if (str_ends_with($url, '/1789/media')) {
                return Http::response(['id' => ($r['media_type'] ?? '') === 'CAROUSEL' ? 'CAR' : 'K' . (++$n)]);
            }

            return Http::response(['status_code' => 'FINISHED', 'permalink' => 'https://instagram.com/p/x']);
        });

        $post = (new InstagramPublisher())->publish($this->makePost(['instagram/1/a.jpg', 'instagram/1/b.jpg', 'instagram/1/c.jpg']));

        $this->assertSame(InstagramPost::POSTADO, $post->status);
        Http::assertSent(fn ($r) => ($r['media_type'] ?? '') === 'CAROUSEL' && $r['children'] === 'K1,K2,K3' && $r['caption'] === 'Oi #a');
        Http::assertSent(fn ($r) => ($r['is_carousel_item'] ?? '') === 'true' && ! isset($r['caption']));
    }

    public function test_story_has_no_caption(): void
    {
        Http::fake([
            'graph.instagram.com/*/1789/media_publish' => Http::response(['id' => 'S1']),
            'graph.instagram.com/*/1789/media' => Http::response(['id' => 'C1']),
            'graph.instagram.com/*' => Http::response(['status_code' => 'FINISHED']),
        ]);

        $post = (new InstagramPublisher())->publish($this->makePost(['instagram/1/s.jpg'], 'story', ['caption' => null]));

        $this->assertSame(InstagramPost::POSTADO, $post->status);
        Http::assertSent(fn ($r) => ($r['media_type'] ?? '') === 'STORIES' && ! isset($r['caption']));
    }

    public function test_meta_error_is_saved_in_portuguese(): void
    {
        Http::fake(['graph.instagram.com/*' => Http::response(['error' => ['code' => 190, 'message' => 'Invalid OAuth access token']], 400)]);

        $post = (new InstagramPublisher())->publish($this->makePost(['instagram/1/a.jpg']));

        $this->assertSame(InstagramPost::ERRO, $post->status);
        $this->assertStringContainsString('Conecte a conta de novo', $post->error);
    }

    public function test_not_connected_fails_without_calling_meta(): void
    {
        Http::fake();
        $this->setting->update(['token_expires_at' => now()->subDay()]);

        $post = (new InstagramPublisher())->publish($this->makePost(['instagram/1/a.jpg']));

        $this->assertSame(InstagramPost::ERRO, $post->status);
        Http::assertNothingSent();
    }

    public function test_command_publishes_only_due_posts(): void
    {
        Http::fake([
            'graph.instagram.com/*/1789/media_publish' => Http::response(['id' => 'M1']),
            'graph.instagram.com/*/1789/media' => Http::response(['id' => 'C1']),
            'graph.instagram.com/*' => Http::response(['status_code' => 'FINISHED']),
        ]);
        $due = $this->makePost(['instagram/1/a.jpg']);
        $later = $this->makePost(['instagram/1/b.jpg'], 'feed', ['scheduled_at' => now()->addHour()]);

        Artisan::call('instagram:publicar');

        $this->assertSame(InstagramPost::POSTADO, $due->fresh()->status);
        $this->assertSame(InstagramPost::AGENDADO, $later->fresh()->status);
    }

    public function test_command_renews_tokens_close_to_expiring(): void
    {
        $this->setting->update(['token_expires_at' => now()->addDays(10)]);
        Http::fake(['graph.instagram.com/refresh_access_token*' => Http::response(['access_token' => 'NEW', 'expires_in' => 5184000])]);

        Artisan::call('instagram:publicar', ['--renovar' => true]);

        $s = $this->setting->fresh();
        $this->assertSame('NEW', $s->access_token);
        $this->assertTrue($s->token_expires_at->gt(now()->addDays(59)));
    }

    public function test_connect_exchanges_code_for_long_token_and_loads_profile(): void
    {
        $this->setting->update(['access_token' => null, 'ig_user_id' => null, 'username' => null]);
        Http::fake([
            'api.instagram.com/oauth/access_token' => Http::response(['access_token' => 'SHORT', 'user_id' => 1789]),
            'graph.instagram.com/access_token*' => Http::response(['access_token' => 'LONG', 'expires_in' => 5184000]),
            'graph.instagram.com/*/me*' => Http::response(['user_id' => '1789', 'username' => 'moveis.stylo', 'account_type' => 'BUSINESS']),
        ]);

        (new InstagramClient($this->setting->fresh()))->connect('CODE', 'https://x/instagram/retorno');

        $s = $this->setting->fresh();
        $this->assertSame('LONG', $s->access_token);
        $this->assertSame('moveis.stylo', $s->username);
        $this->assertTrue($s->connected());
        Http::assertSent(fn ($r) => str_contains($r->url(), 'api.instagram.com') && $r['code'] === 'CODE' && $r['redirect_uri'] === 'https://x/instagram/retorno');
    }

    public function test_callback_rejects_wrong_state(): void
    {
        Http::fake();
        $this->actingAs($this->user)
            ->withSession(['instagram_state' => 'certo'])
            ->get('/instagram/retorno?code=abc&state=errado')
            ->assertRedirect(route('settings.instagram'))
            ->assertSessionHas('ig_error');
        Http::assertNothingSent();
    }

    public function test_images_are_resized_to_instagram_sizes(): void
    {
        $img = imagecreatetruecolor(400, 900);
        $file = tempnam(sys_get_temp_dir(), 'ig') . '.png';
        imagepng($img, $file);

        foreach (['quadrado' => [1080, 1080], 'retrato' => [1080, 1350], 'story' => [1080, 1920]] as $size => [$w, $h]) {
            $path = InstagramImages::prepare($this->user->id, $file, $size, 'inteira');
            [$rw, $rh, $type] = getimagesizefromstring(Storage::disk('public')->get($path));
            $this->assertSame([$w, $h, IMAGETYPE_JPEG], [$rw, $rh, $type], $size);
        }
        @unlink($file);
    }

    public function test_caption_helpers(): void
    {
        $this->assertSame('#moveis #decoracao #ferro', CaptionBuilder::hashtags('moveis, #decoracao  #ferro #moveis'));
        $this->assertSame("a\n\nb", CaptionBuilder::clean("a\n\n\n\n\nb\n"));
        $this->assertSame(2, CaptionBuilder::countHashtags('oi #a e #b'));
    }
}
