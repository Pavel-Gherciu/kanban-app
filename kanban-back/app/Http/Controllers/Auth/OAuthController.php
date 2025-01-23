<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class OAuthController extends Controller
{
    public function redirect($provider)
    {
        // Dynamically determine the host and scheme (http or https)
        $redirectUri = request()->getSchemeAndHttpHost() . '/auth/callback/' . $provider;

        // Set the dynamically determined redirect URI
        Socialite::driver($provider)->redirectUrl($redirectUri);

        // Redirect the user to Google's OAuth page
        return Socialite::driver($provider)->redirect();
    }

    public function callback($provider)
    {
        //  i know it's bad
        $socialiteDriver = Socialite::driver($provider)->setHttpClient(new \GuzzleHttp\Client([
            'verify' => false,
        ]));

        $socialUser = $socialiteDriver->stateless()->user();

        $user = User::firstOrCreate(
            ['email' => $socialUser->getEmail()],
            [
                'name' => $socialUser->getName() ?? $socialUser->getNickname(),
                'password' => Hash::make(Str::random(24)),
            ]
        );

        Auth::login($user);

        return redirect(config('app.frontend_url') . '/auth/callback');
    }

}

