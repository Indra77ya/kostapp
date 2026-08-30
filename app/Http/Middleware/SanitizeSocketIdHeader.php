<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SanitizeSocketIdHeader
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $socketId = $request->header('X-Socket-ID');

        if ($socketId !== null) {
            if ($socketId === 'undefined' || $socketId === 'null' || !preg_match('/^\d+\.\d+$/', $socketId)) {
                $request->headers->remove('X-Socket-ID');
                // Also remove from server array if set
                $request->server->remove('HTTP_X_SOCKET_ID');
            }
        }

        return $next($request);
    }
}
