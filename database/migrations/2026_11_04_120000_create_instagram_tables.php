<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Instagram: a conta conectada de cada loja (instagram_settings) e os posts
 * feitos ou agendados pelo app (instagram_posts).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('instagram_settings')) {
            Schema::create('instagram_settings', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('user_id')->unique();
                $t->string('app_id', 40)->nullable();
                $t->text('app_secret')->nullable();
                $t->text('access_token')->nullable();
                $t->timestamp('token_expires_at')->nullable();
                $t->string('ig_user_id', 40)->nullable();
                $t->string('username', 80)->nullable();
                $t->string('name', 150)->nullable();
                $t->text('picture_url')->nullable();
                $t->string('account_type', 30)->nullable();
                $t->timestamp('connected_at')->nullable();
                $t->timestamp('refreshed_at')->nullable();
                $t->text('caption_product')->nullable();
                $t->text('caption_promo')->nullable();
                $t->text('hashtags')->nullable();
                $t->string('last_error', 500)->nullable();
                $t->timestamps();
            });
        }

        if (! Schema::hasTable('instagram_posts')) {
            Schema::create('instagram_posts', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('user_id');
                $t->unsignedBigInteger('product_id')->nullable();
                $t->unsignedBigInteger('promotion_id')->nullable();
                $t->string('kind', 12)->default('feed'); // feed | story
                $t->string('format', 12)->default('quadrado'); // quadrado | retrato
                $t->text('caption')->nullable();
                $t->json('images'); // caminhos no disco public, na ordem
                $t->string('status', 12)->default('rascunho'); // agendado | postando | postado | erro | cancelado
                $t->timestamp('scheduled_at')->nullable();
                $t->timestamp('published_at')->nullable();
                $t->string('media_id', 60)->nullable();
                $t->string('permalink', 255)->nullable();
                $t->string('error', 500)->nullable();
                $t->unsignedTinyInteger('attempts')->default(0);
                $t->timestamps();
                $t->index(['user_id', 'status']);
                $t->index(['status', 'scheduled_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('instagram_posts');
        Schema::dropIfExists('instagram_settings');
    }
};
