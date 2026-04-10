<?php

namespace App\Controllers;

class SocialController extends BaseController
{
    private function requireAuth()
    {
        if (! session()->get('user_id')) {
            return redirect()->to('/login')->with('error', 'Please login first.');
        }

        return null;
    }

    public function follow(int $userId)
    {
        if ($redirect = $this->requireAuth()) {
            return $redirect;
        }

        if (! lume_social_ready()) {
            return redirect()->back()->with('error', 'Follow system is not ready yet. Please run migrations.');
        }

        $currentUserId = (int) session()->get('user_id');
        if ($currentUserId === $userId) {
            return redirect()->back()->with('error', 'You cannot follow yourself.');
        }

        $users = db_connect()->table('users');
        $target = $users->select('id, name')->where('id', $userId)->get()->getRowArray();
        if (! $target) {
            return redirect()->back()->with('error', 'User not found.');
        }

        $follows = db_connect()->table('user_follows');
        $existing = $follows
            ->where('follower_user_id', $currentUserId)
            ->where('followed_user_id', $userId)
            ->get()
            ->getRowArray();

        if ($existing) {
            $follows->where('id', $existing['id'])->delete();
            return redirect()->back()->with('success', 'Unfollowed ' . ($target['name'] ?? 'user') . '.');
        }

        $follows->insert([
            'follower_user_id' => $currentUserId,
            'followed_user_id' => $userId,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->back()->with('success', 'Now following ' . ($target['name'] ?? 'user') . '.');
    }

    public function notifications()
    {
        if ($redirect = $this->requireAuth()) {
            return $redirect;
        }

        $userId = (int) session()->get('user_id');
        $notifications = lume_social_ready()
            ? db_connect()->table('notifications')
                ->where('user_id', $userId)
                ->orderBy('id', 'DESC')
                ->limit(50)
                ->get()
                ->getResultArray()
            : [];

        return view('notifications/index', [
            'notifications' => $notifications,
        ]);
    }

    public function markNotificationsRead()
    {
        if ($redirect = $this->requireAuth()) {
            return $redirect;
        }

        if (lume_social_ready()) {
            db_connect()->table('notifications')
                ->where('user_id', (int) session()->get('user_id'))
                ->where('read_at', null)
                ->set('read_at', date('Y-m-d H:i:s'))
                ->update();
        }

        return redirect()->back()->with('success', 'Notifications marked as read.');
    }
}
