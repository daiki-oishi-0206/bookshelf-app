<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(): View
    {
        $notifications = auth()->user()->notifications;
        return view('notifications.index', compact('notifications'));
    }

    public function markAsRead(DatabaseNotification $notification): RedirectResponse
    {
        $notification->markAsRead();
        
        return redirect()
            ->route('notifications.index');
    }
}
