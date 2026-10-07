<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;

abstract class Controller
{
    use AuthorizesRequests;

    /** Redirect to a list page as the user last saw it (filters, search, page) — see RememberListUrl. */
    protected function toList(string $indexRoute): RedirectResponse
    {
        return redirect()->to(session('list_url.'.$indexRoute, route($indexRoute)));
    }
}
