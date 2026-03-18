<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiRateLimit
{
    /**
     * Rate limits per endpoint category (requests per minute).
     */
    protected const LIMITS = [
        'auth'     => 5,
        'transfer' => 10,
        'general'  => 60,
    ];

    public function __construct(protected RateLimiter $limiter)
    {
    }

    /**
     * Per-IP and per-token rate limiting.
     *
     * Category is passed as middleware parameter:
     *   api.ratelimit:auth   → 5 req/min
     *   api.ratelimit:transfer → 10 req/min
     *   api.ratelimit (default) → 60 req/min
     */
    public function handle(Request $request, Closure $next, string $category = 'general'): Response
    {
        $maxAttempts = self::LIMITS[$category] ?? self::LIMITS['general'];

        // Build a compound key: IP + token ID (if authenticated)
        $tokenId = optional($request->user()?->currentAccessToken())->id ?? 'guest';
        $key     = "api_rl:{$category}:{$request->ip()}:{$tokenId}";

        if ($this->limiter->tooManyAttempts($key, $maxAttempts)) {
            $retryAfter = $this->limiter->availableIn($key);

            return response()->json([
                'status'  => 'error',
                'message' => 'Too many requests. Please try again later.',
                'retry_after_seconds' => $retryAfter,
            ], 429, ['Retry-After' => $retryAfter]);
        }

        $this->limiter->hit($key, 60); // 60-second decay window

        $response = $next($request);

        $remaining = max(0, $maxAttempts - $this->limiter->attempts($key));

        $response->headers->set('X-RateLimit-Limit',     $maxAttempts);
        $response->headers->set('X-RateLimit-Remaining', $remaining);

        return $response;
    }
}
