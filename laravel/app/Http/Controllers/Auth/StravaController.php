<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class StravaController extends Controller
{
    public function redirectToStrava()
    {
        $clientId = config('services.strava.client_id');
        $redirectUri = config('services.strava.redirect');
        $scope = 'read,activity:read';

        $url = "https://www.strava.com/oauth/authorize?client_id={$clientId}&response_type=code&redirect_uri={$redirectUri}&approval_prompt=force&scope={$scope}";
        return redirect($url);
    }

    public function handleStravaCallback()
    {
        $code = request('code');

        // Intercambiar el código por el token
        $response = Http::asForm()->post('https://www.strava.com/oauth/token', [
            'client_id' => config('services.strava.client_id'),
            'client_secret' => config('services.strava.client_secret'),
            'code' => $code,
            'grant_type' => 'authorization_code',
        ]);

        $data = $response->json();

        $athlete = $data['athlete'];
        $accessToken = $data['access_token'];
        $refreshToken = $data['refresh_token'];
        $expiresAt = $data['expires_at'];

        // Buscar o crear el usuario
        $user = \App\Models\User::updateOrCreate(
            ['strava_id' => $athlete['id']],
            [
                'username' => $athlete['username'] ?? '',
                'firstname' => $athlete['firstname'] ?? '',
                'lastname' => $athlete['lastname'] ?? '',
                'city' => $athlete['city'] ?? '',
                'country' => $athlete['country'] ?? '',
                'sex' => $athlete['sex'] ?? '',
                'profile_picture' => str_starts_with($athlete['profile'] ?? '', 'http') ? $athlete['profile'] : null,
            ]
        );

        // Guardar tokens
        DB::table('user_tokens')->updateOrInsert(
            ['strava_user_id' => $athlete['id']],
            [
                'access_token' => $accessToken,
                'refresh_token' => $refreshToken,
                'expires_at' => $expiresAt
            ]
        );

        // Autenticar al usuario directamente
        Auth::login($user);

        // Redirigir a la home o donde quieras
        return redirect('/home')->with('success', 'Inicio de sesión correcto con Strava');
    }

    // NUEVA FUNCIÓN PARA RENOVAR EL TOKEN AUTOMÁTICAMENTE
    public static function getValidAccessToken($userId)
    {
        $token = DB::table('user_tokens')->where('strava_user_id', $userId)->first();

        if (!$token) return null;

        if ($token->expires_at > time()) {
            return $token->access_token;
        }

        $response = Http::asForm()->post('https://www.strava.com/oauth/token', [
            'client_id' => config('services.strava.client_id'),
            'client_secret' => config('services.strava.client_secret'),
            'grant_type' => 'refresh_token',
            'refresh_token' => $token->refresh_token,
        ]);

        $new = $response->json();

        DB::table('user_tokens')->updateOrInsert(
            ['strava_user_id' => $userId],
            [
                'access_token' => $new['access_token'],
                'refresh_token' => $new['refresh_token'],
                'expires_at' => $new['expires_at'],
            ]
        );

        return $new['access_token'];
    }
}
