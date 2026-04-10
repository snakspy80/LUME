<?php

namespace App\Controllers;

class LeaderboardController extends BaseController
{
    public function index()
    {
        $leaders = db_connect()->table('users u')
            ->select([
                'u.id',
                'u.name',
                'u.college',
                'u.bio',
                'u.avatar',
                'COUNT(DISTINCT p.id) AS published_courses',
                'COUNT(DISTINCT cv.id) AS total_views',
                'COUNT(DISTINCT cb.id) AS total_bookmarks',
                'COUNT(DISTINCT cp.id) AS total_completions',
            ])
            ->join('posts p', 'p.user_id = u.id AND p.is_published = 1', 'left')
            ->join('course_views cv', 'cv.post_id = p.id', 'left')
            ->join('course_bookmarks cb', 'cb.post_id = p.id', 'left')
            ->join('course_progress cp', 'cp.post_id = p.id', 'left')
            ->groupBy('u.id')
            ->having('published_courses >', 0)
            ->get()
            ->getResultArray();

        foreach ($leaders as &$leader) {
            $courses = (int) ($leader['published_courses'] ?? 0);
            $views = (int) ($leader['total_views'] ?? 0);
            $bookmarks = (int) ($leader['total_bookmarks'] ?? 0);
            $completions = (int) ($leader['total_completions'] ?? 0);

            $leader['score'] = ($courses * 100) + ($views * 1) + ($bookmarks * 8) + ($completions * 12);
        }
        unset($leader);

        usort($leaders, static function (array $a, array $b): int {
            return [
                (int) ($b['score'] ?? 0),
                (int) ($b['published_courses'] ?? 0),
                (int) ($b['total_completions'] ?? 0),
                (int) ($b['total_views'] ?? 0),
            ] <=> [
                (int) ($a['score'] ?? 0),
                (int) ($a['published_courses'] ?? 0),
                (int) ($a['total_completions'] ?? 0),
                (int) ($a['total_views'] ?? 0),
            ];
        });

        foreach ($leaders as $index => &$leader) {
            $leader['rank'] = $index + 1;
        }
        unset($leader);

        return view('leaderboard/index', [
            'leaders' => $leaders,
        ]);
    }
}
