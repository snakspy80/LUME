<?php

namespace App\Controllers;

class LearningController extends BaseController
{
    private function requireAuth()
    {
        if (! session()->get('user_id')) {
            return redirect()->to('/login')->with('error', 'Please login first.');
        }

        return null;
    }

    public function bookmark(int $postId)
    {
        if ($redirect = $this->requireAuth()) {
            return $redirect;
        }

        $userId = (int) session()->get('user_id');
        $bookmarks = db_connect()->table('course_bookmarks');

        $exists = $bookmarks->where('user_id', $userId)->where('post_id', $postId)->get()->getRowArray();
        if ($exists) {
            $bookmarks->where('id', $exists['id'])->delete();
            return redirect()->back()->with('success', 'Removed from bookmarks.');
        }

        $bookmarks->insert([
            'user_id' => $userId,
            'post_id' => $postId,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->back()->with('success', 'Added to bookmarks.');
    }

    public function progress(int $postId)
    {
        if ($redirect = $this->requireAuth()) {
            return $redirect;
        }

        $userId = (int) session()->get('user_id');
        $progress = db_connect()->table('course_progress');

        $exists = $progress->where('user_id', $userId)->where('post_id', $postId)->get()->getRowArray();
        if ($exists) {
            $progress->where('id', $exists['id'])->delete();
            return redirect()->back()->with('success', 'Marked as not completed.');
        }

        $progress->insert([
            'user_id' => $userId,
            'post_id' => $postId,
            'completed_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->back()->with('success', 'Marked as completed.');
    }

    public function myLearning()
    {
        if ($redirect = $this->requireAuth()) {
            return $redirect;
        }

        $userId = (int) session()->get('user_id');

        $bookmarked = db_connect()->table('course_bookmarks cb')
            ->select('p.id, p.title, p.category, p.video_url, p.content, cb.created_at')
            ->join('posts p', 'p.id = cb.post_id', 'inner')
            ->where('cb.user_id', $userId)
            ->where('p.is_published', 1)
            ->orderBy('cb.created_at', 'DESC')
            ->get()
            ->getResultArray();

        $completed = db_connect()->table('course_progress cp')
            ->select('p.id, p.title, p.category, p.video_url, p.content, cp.completed_at')
            ->join('posts p', 'p.id = cp.post_id', 'inner')
            ->where('cp.user_id', $userId)
            ->where('p.is_published', 1)
            ->orderBy('cp.completed_at', 'DESC')
            ->get()
            ->getResultArray();

        foreach ($bookmarked as &$item) {
            $item['video_url'] = lume_normalize_video_url((string) ($item['video_url'] ?? ''));
        }
        unset($item);

        foreach ($completed as &$item) {
            $item['video_url'] = lume_normalize_video_url((string) ($item['video_url'] ?? ''));
        }
        unset($item);

        return view('learning/my_learning', [
            'bookmarked' => $bookmarked,
            'completed' => $completed,
        ]);
    }
}
