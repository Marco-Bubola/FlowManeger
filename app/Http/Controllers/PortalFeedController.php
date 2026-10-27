<?php

namespace App\Http\Controllers;

use App\Services\Portal\ProductFeed;

/** Mapa do site e lista de produtos do catálogo para o Google e a Meta (Instagram/Facebook). */
class PortalFeedController extends Controller
{
    private function xml(string $body, int $minutes = 30)
    {
        return response($body, 200, ['Content-Type' => 'application/xml; charset=UTF-8', 'Cache-Control' => 'public, max-age=' . ($minutes * 60)]);
    }

    public function feed(int $userId)
    {
        return $this->xml(ProductFeed::xml($userId));
    }

    public function sitemap(int $userId)
    {
        abort_unless(ProductFeed::settingsFor($userId)['google'], 404);

        return $this->xml(ProductFeed::sitemap($userId), 60);
    }

    public function index()
    {
        return $this->xml(ProductFeed::index(), 60);
    }
}
