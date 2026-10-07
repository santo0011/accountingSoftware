<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Remembers the last URL of every list page (routes named *.index), including its
 * filters, search and page, so forms can return there after saving (Controller::toList()).
 */
class RememberListUrl
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $name = (string) $request->route()?->getName();
        if ($request->isMethod('GET') && str_ends_with($name, '.index') && $response->isSuccessful()) {
            $request->session()->put('list_url.'.$name, $request->fullUrl());
        }

        return $response;
    }
}
