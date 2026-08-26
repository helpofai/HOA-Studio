<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Studio Token Auth Middleware
|--------------------------------------------------------------------------
|
| Copyright (c) 2026 Rajib Adhikary. All Rights Reserved.
|
| Author      : Rajib Adhikary
| Organization: HelpOfAi (HOA)
| Website     : https://helpofai.com
| Location    : Basta Purba Para, Aranghata, Nadia, West Bengal, India
|
|--------------------------------------------------------------------------
*/

namespace App\Http\Middleware;

use App\Features\Auth\Models\UserStudioToken;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateStudioToken
{
    /**
     * Handle an incoming API request by validating the user's Studio Connect Token.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $tokenString = $request->bearerToken() ?: $request->header('X-HOA-Connect-Key');

        if (!$tokenString && $request->has('api_token')) {
            $tokenString = $request->input('api_token');
        }

        if (empty($tokenString)) {
            return response()->json([
                'success' => false,
                'error' => 'Authentication Required. Please provide your HOA Studio Connect Key in Authorization Bearer or X-HOA-Connect-Key header.',
            ], 401);
        }

        $studioToken = UserStudioToken::findToken($tokenString);

        if (!$studioToken || !$studioToken->user || !$studioToken->user->is_active) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid, expired, or revoked Studio Connect Key. Please generate a new key in HOA Studio Settings.',
            ], 401);
        }

        // Set the active user on Laravel's Auth guard
        Auth::setUser($studioToken->user);
        $request->setUserResolver(fn () => $studioToken->user);

        // Extract domain from Referer, Origin, or Host header
        $origin = $request->header('Origin') ?: $request->header('Referer') ?: $request->getHost();
        $domain = $origin ? parse_url($origin, PHP_URL_HOST) ?: $origin : null;
        $ip = $request->ip();

        // Update last used timestamp and connected domain
        $studioToken->touchUsage($domain, $ip);

        return $next($request);
    }
}
