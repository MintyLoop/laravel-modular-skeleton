<?php

namespace App\Application\Http\Middlewares;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Sentry\Laravel\Integration;
use Sentry\State\Scope;

class ForceSetRequestId
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $request->headers->set('x-transaction-id', Str::uuid()->toString());

        if (app()->bound('sentry')) {
            \Sentry\configureScope(function (Scope $scope) use ($request) {
                $scope->setTag('request_id', $request->header('x-transaction-id'));
            });
        }

        return $next($request);
    }
}
