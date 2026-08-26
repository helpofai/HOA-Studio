<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Notification Center Bell Component
|--------------------------------------------------------------------------
|
| Copyright (c) 2026 Rajib Adhikary. All Rights Reserved.
|
| Author      : Rajib Adhikary
| Organization: HelpOfAi (HOA)
| Website     : https://helpofai.com
| Location    : Basta Purba Para, Aranghata, Nadia, West Bengal, India
|
|--------------------------------------------------------------------------
*/

namespace App\Features\Admin\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class NotificationBell extends Component
{
    public bool $open = false;

    protected $listeners = [
        'refreshNotifications' => '$refresh',
        'echo:notifications,NotificationCreated' => '$refresh',
    ];

    public function markAsRead(string $notificationId)
    {
        $user = Auth::user();
        if ($user) {
            $user->notifications()->where('id', $notificationId)->update(['read_at' => now()]);
        }
    }

    public function markAllAsRead()
    {
        $user = Auth::user();
        if ($user) {
            $user->unreadNotifications->markAsRead();
        }
    }

    public function clearAll()
    {
        $user = Auth::user();
        if ($user) {
            $user->notifications()->delete();
        }
    }

    public function render()
    {
        $user = Auth::user();
        $unreadCount = $user ? $user->unreadNotifications()->count() : 0;
        $notifications = $user ? $user->notifications()->take(10)->get() : collect();

        return view('admin.notifications.bell', [
            'unreadCount' => $unreadCount,
            'notifications' => $notifications,
        ]);
    }
}
