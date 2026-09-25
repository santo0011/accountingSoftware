<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/** /dashboard sends each user type to the right home. */
class DashboardRedirectController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        return $request->user()->isBackoffice()
            ? redirect()->route('admin.dashboard')
            : redirect()->route('portal.dashboard');
    }
}
