<?php

namespace App\Controllers;

class SearchController extends BaseController
{
    public function index(): string
    {
        $q = trim((string) $this->request->getGet('q'));

        $users = [];
        $courses = [];

        if ($q !== '') {
            $users = db_connect()->table('users')
                ->select('id, name, email, college, avatar, bio')
                ->groupStart()
                ->like('name', $q)
                ->orLike('email', $q)
                ->orLike('college', $q)
                ->groupEnd()
                ->orderBy('name', 'ASC')
                ->limit(12)
                ->get()
                ->getResultArray();

            $courses = db_connect()->table('posts p')
                ->select('p.id, p.title, p.category, p.video_url, p.content, u.name as author_name, COUNT(cv.id) AS views_count')
                ->join('users u', 'u.id = p.user_id', 'left')
                ->join('course_views cv', 'cv.post_id = p.id', 'left')
                ->where('p.is_published', 1)
                ->groupStart()
                ->like('p.title', $q)
                ->orLike('p.content', $q)
                ->orLike('p.category', $q)
                ->orLike('u.name', $q)
                ->groupEnd()
                ->groupBy('p.id')
                ->orderBy('views_count', 'DESC')
                ->limit(24)
                ->get()
                ->getResultArray();

            foreach ($courses as &$course) {
                $course['video_url'] = lume_normalize_video_url((string) ($course['video_url'] ?? ''));
            }
            unset($course);
        }

        return view('search/index', [
            'q' => $q,
            'users' => $users,
            'courses' => $courses,
        ]);
    }

    public function creator(int $id)
    {
        $creator = db_connect()->table('users')
            ->select('id, name, email, college, phone, bio, avatar, created_at')
            ->where('id', $id)
            ->get()
            ->getRowArray();

        if (! $creator) {
            return redirect()->to('/search')->with('error', 'Creator not found.');
        }

        $posts = db_connect()->table('posts p')
            ->select('p.*, COUNT(DISTINCT cv.id) AS views_count, COUNT(DISTINCT cb.id) AS bookmarks_count, COUNT(DISTINCT cp.id) AS completions_count')
            ->join('course_views cv', 'cv.post_id = p.id', 'left')
            ->join('course_bookmarks cb', 'cb.post_id = p.id', 'left')
            ->join('course_progress cp', 'cp.post_id = p.id', 'left')
            ->where('p.user_id', $id)
            ->where('p.is_published', 1)
            ->groupBy('p.id')
            ->orderBy('p.id', 'DESC')
            ->get()
            ->getResultArray();

        foreach ($posts as &$post) {
            $post['video_url'] = lume_normalize_video_url((string) ($post['video_url'] ?? ''));
        }
        unset($post);

        $stats = [
            'courses' => count($posts),
            'views' => array_sum(array_map(static fn(array $post): int => (int) ($post['views_count'] ?? 0), $posts)),
            'bookmarks' => array_sum(array_map(static fn(array $post): int => (int) ($post['bookmarks_count'] ?? 0), $posts)),
            'completions' => array_sum(array_map(static fn(array $post): int => (int) ($post['completions_count'] ?? 0), $posts)),
        ];

        $stats['followers'] = lume_social_ready()
            ? (int) db_connect()->table('user_follows')->where('followed_user_id', $id)->countAllResults()
            : 0;

        $viewerId = (int) (session()->get('user_id') ?? 0);
        $isFollowing = false;
        if ($viewerId > 0 && $viewerId !== $id && lume_social_ready()) {
            $isFollowing = db_connect()->table('user_follows')
                ->where('follower_user_id', $viewerId)
                ->where('followed_user_id', $id)
                ->countAllResults() > 0;
        }

        return view('creators/show', [
            'creator' => $creator,
            'posts' => $posts,
            'stats' => $stats,
            'isFollowing' => $isFollowing,
        ]);
    }
}
