<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class GoogleFirebaseController extends Controller
{
    /**
     * Authenticate user via Firebase Google ID token.
     * The token is verified by calling Google's tokeninfo endpoint.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'id_token' => ['required', 'string', 'min:10'],
        ]);

        $idToken = $request->input('id_token');

        // Verify the Firebase ID token via Google's public endpoint
        $payload = $this->verifyFirebaseToken($idToken);

        if (! $payload) {
            return response()->json(['error' => 'Token inválido ou expirado.'], 401);
        }

        // Ensure the token was issued for our Firebase project
        $projectId = config('services.firebase.project_id');
        if ($projectId && isset($payload['aud']) && $payload['aud'] !== $projectId) {
            return response()->json(['error' => 'Token não autorizado para este projeto.'], 401);
        }

        $googleId = $payload['user_id'] ?? $payload['sub'] ?? null;
        $email    = $payload['email'] ?? null;
        $name     = $payload['name'] ?? $payload['display_name'] ?? 'Usuário Google';
        $avatar   = $payload['picture'] ?? null;
        $verified = $payload['email_verified'] ?? false;

        if (! $email || ! $googleId) {
            return response()->json(['error' => 'Dados insuficientes no token.'], 422);
        }

        // Find or create user
        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name'              => $name,
                'password'          => Hash::make(Str::random(32)),
                'google_id'         => $googleId,
                'avatar'            => $avatar,
                'email_verified_at' => $verified ? now() : null,
            ]
        );

        // Update Google ID if missing and sync avatar
        if (! $user->google_id) {
            $user->update([
                'google_id' => $googleId,
                'avatar'    => $avatar ?? $user->avatar,
            ]);
        }

        // Auto-verify email if Google says it's verified
        if ($verified && ! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        Auth::login($user, remember: true);
        $request->session()->regenerate();

        return response()->json([
            'redirect' => route('dashboard', absolute: true),
        ]);
    }

    /**
     * Verify a Firebase ID token using Google's tokeninfo API.
     * Returns the decoded payload or null if invalid.
     */
    private function verifyFirebaseToken(string $idToken): ?array
    {
        $url = 'https://identitytoolkit.googleapis.com/v1/accounts:lookup?key=' . config('services.firebase.api_key');

        $context = stream_context_create([
            'http' => [
                'method'  => 'POST',
                'header'  => 'Content-Type: application/json',
                'content' => json_encode(['idToken' => $idToken]),
                'timeout' => 8,
            ],
        ]);

        $response = @file_get_contents($url, false, $context);

        if ($response === false) {
            // Fallback: try tokeninfo endpoint (simpler, no API key needed)
            return $this->verifyViaTokenInfo($idToken);
        }

        $data = json_decode($response, true);

        if (empty($data['users'][0])) {
            return null;
        }

        $user = $data['users'][0];

        return [
            'sub'            => $user['localId'] ?? null,
            'user_id'        => $user['localId'] ?? null,
            'email'          => $user['email'] ?? null,
            'email_verified' => ($user['emailVerified'] ?? false) === true,
            'name'           => $user['displayName'] ?? null,
            'picture'        => $user['photoUrl'] ?? null,
        ];
    }

    /**
     * Fallback: verify via Google's OAuth2 tokeninfo endpoint.
     */
    private function verifyViaTokenInfo(string $idToken): ?array
    {
        $url = 'https://oauth2.googleapis.com/tokeninfo?id_token=' . urlencode($idToken);

        $context = stream_context_create([
            'http' => ['timeout' => 8],
        ]);

        $response = @file_get_contents($url, false, $context);

        if ($response === false) {
            return null;
        }

        $payload = json_decode($response, true);

        if (empty($payload['sub']) || empty($payload['email'])) {
            return null;
        }

        // Check token expiry
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            return null;
        }

        return $payload;
    }
}
