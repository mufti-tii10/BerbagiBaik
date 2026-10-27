<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = $request->user()->appNotifications()->latest()->limit(100)->get();

        // Simpan dulu mana yang baru, lalu tandai semuanya sudah dibaca
        $unreadIds = $notifications->where('is_read', false)->pluck('id')->all();
        if ($unreadIds) {
            $request->user()->appNotifications()->whereIn('id', $unreadIds)->update(['is_read' => true]);
        }

        return view('notifications.index', compact('notifications', 'unreadIds'));
    }
}
