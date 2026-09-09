<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function marquerLu(Request $request, string $id)
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return redirect($notification->data['url'] ?? route('dashboard'));
    }

    public function marquerToutLu(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return redirect()->route('dashboard')->with('success', 'Notifications marquées comme lues.');
    }
}
