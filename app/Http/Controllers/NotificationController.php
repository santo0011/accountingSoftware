<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** In-app notifications; the portal and admin panel only differ in layout. */
abstract class NotificationController extends Controller
{
    abstract protected function area(): string;

    public function index(Request $request): View
    {
        $notifications = $request->user()->notifications()->latest()->paginate(20);

        return view('shared.notifications', ['notifications' => $notifications, 'area' => $this->area()]);
    }

    /** Mark as read and follow the notification's link. */
    public function open(Request $request, string $id): RedirectResponse
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        $url = $notification->data['url'] ?? null;

        // Only follow links to this application.
        return $url && str_starts_with($url, url('/'))
            ? redirect()->to($url)
            : redirect()->route($this->area().'.notifications.index');
    }

    public function readAll(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return back()->with('success', 'All notifications marked as read.');
    }
}
