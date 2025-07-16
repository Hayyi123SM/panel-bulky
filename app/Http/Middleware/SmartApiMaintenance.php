<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class SmartApiMaintenance
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $route = $request->route();
        $routeName = $route?->getName();

        if (Cache::get('global_api_maintenance', false)) {
            $whitelist = []; // route name yang tetap aktif meski global maintenance
            if (!in_array($routeName, $whitelist)) {
                return response()->json([
                    'status' => 'maintenance',
                    'type' => 'global',
                    'message' => 'API sedang dalam pemeliharaan (global).',
                ], 503);
            }
        }

        if ($routeName && Cache::get("route_maintenance:$routeName", false)) {
            return response()->json([
                'status' => 'maintenance',
                'type' => 'route',
                'route' => $routeName,
                'message' => "Fitur ini sedang dalam pemeliharaan.",
            ], 503);
        }

        return $next($request);
    }
}
