<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RateLimitWhatsApp
{
    public function handle(Request $request, Closure $next)
    {
        $limit = env('WHATSAPP_RATE_LIMIT', 30);
        $key = 'whatsapp_rate_limit:' . $request->ip();

        $current = cache()->get($key, 0);

        if ($current >= $limit) {
            return response()->json([
                'error' => 'Rate limit exceeded. Maximum ' . $limit . ' messages per minute.',
                'retry_after' => 60
            ], 429);
        }

        cache()->put($key, $current + 1, now()->addMinute());

        return $next($request);
    }
}
