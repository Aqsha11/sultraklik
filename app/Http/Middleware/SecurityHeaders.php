<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        foreach ((array) config('security.headers', []) as $header => $value) {
            if (! $response->headers->has($header)) {
                $response->headers->set($header, $value);
            }
        }

        $policy = implode('; ', (array) config('security.content_security_policy', []));

        if ($policy !== '' && ! $response->headers->has('Content-Security-Policy')) {
            $response->headers->set('Content-Security-Policy', $policy);
        }

        return $response;
    }
}
