<?php

namespace App\Http\Controllers;

use App\Models\FinanceCapture;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class FinanceCaptureController extends Controller
{
    /** Print guardado (fica fora da pasta pública: só o dono vê). */
    public function image(int $capture)
    {
        $c = FinanceCapture::where('user_id', Auth::id())->findOrFail($capture);
        abort_unless($c->image_path && Storage::disk('local')->exists($c->image_path), 404);

        return Storage::disk('local')->response($c->image_path, null, ['Cache-Control' => 'private, max-age=86400']);
    }
}
