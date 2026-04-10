<?php

namespace App\Controllers;

class DashboardController extends BaseController
{
    private const FOUNDER_EMAIL = 'rudrabiswas080808@gmail.com';

    public function index()
    {
        if (! session()->get('user_id')) {
            return redirect()->to('/login')->with('error', 'Please login first.');
        }

        if (! session()->get('email_verified')) {
            return redirect()->to('/email/verify-notice')->with('error', 'Please verify your email to continue.');
        }

        $userId = (int) session()->get('user_id');
        $currentUser = db_connect()->table('users')
            ->select('id, email')
            ->where('id', $userId)
            ->get()
            ->getRowArray();

        $isFounderAdmin = strtolower((string) ($currentUser['email'] ?? '')) === self::FOUNDER_EMAIL;

        $q = trim((string) $this->request->getGet('q'));
        $status = trim((string) $this->request->getGet('status'));
        $category = trim((string) $this->request->getGet('category'));
        $from = trim((string) $this->request->getGet('from'));
        $to = trim((string) $this->request->getGet('to'));
        $sort = trim((string) $this->request->getGet('sort'));
        $perPage = (int) $this->request->getGet('per_page');
        $page = max(1, (int) $this->request->getGet('page'));
        $allowedPerPage = [6, 12, 24, 48];
        if (! in_array($perPage, $allowedPerPage, true)) {
            $perPage = 12;
        }

        $sortMap = [
            'latest' => ['id', 'DESC'],
            'oldest' => ['id', 'ASC'],
            'title_asc' => ['title', 'ASC'],
            'title_desc' => ['title', 'DESC'],
        ];

        if (! isset($sortMap[$sort])) {
            $sort = 'latest';
        }

        $baseBuilder = db_connect()->table('posts')
            ->where('user_id', $userId);

        if ($q !== '') {
            $baseBuilder->groupStart()
                ->like('title', $q)
                ->orLike('content', $q)
                ->orLike('category', $q)
                ->groupEnd();
        }

        if ($status === 'published') {
            $baseBuilder->where('is_published', 1);
        } elseif ($status === 'draft') {
            $baseBuilder->where('is_published', 0);
        }

        if ($category !== '') {
            $baseBuilder->where('category', $category);
        }

        if ($from !== '') {
            $baseBuilder->where('DATE(created_at) >=', $from);
        }

        if ($to !== '') {
            $baseBuilder->where('DATE(created_at) <=', $to);
        }

        $countBuilder = clone $baseBuilder;
        $total = (int) $countBuilder->countAllResults();
        $totalPages = max(1, (int) ceil($total / $perPage));
        if ($page > $totalPages) {
            $page = $totalPages;
        }

        $listBuilder = clone $baseBuilder;
        [$sortColumn, $sortDirection] = $sortMap[$sort];
        $posts = $listBuilder
            ->orderBy($sortColumn, $sortDirection)
            ->limit($perPage, ($page - 1) * $perPage)
            ->get()
            ->getResultArray();

        $categories = db_connect()->table('posts')
            ->select('category')
            ->where('user_id', $userId)
            ->where('category IS NOT NULL')
            ->where('category !=', '')
            ->groupBy('category')
            ->orderBy('category', 'ASC')
            ->get()
            ->getResultArray();

        $analytics = [
            'total_courses' => (int) db_connect()->table('posts')->where('user_id', $userId)->countAllResults(),
            'published_courses' => (int) db_connect()->table('posts')->where('user_id', $userId)->where('is_published', 1)->countAllResults(),
            'total_bookmarks' => (int) db_connect()->query('SELECT COUNT(*) AS c FROM course_bookmarks cb JOIN posts p ON p.id = cb.post_id WHERE p.user_id = ?', [$userId])->getRow()->c,
            'total_views' => (int) db_connect()->query('SELECT COUNT(*) AS c FROM course_views cv JOIN posts p ON p.id = cv.post_id WHERE p.user_id = ?', [$userId])->getRow()->c,
        ];

        $problemRows = [];
        if (db_connect()->tableExists('course_comments')) {
            $problemRows = db_connect()->table('course_comments cc')
                ->select('cc.id, cc.post_id, cc.content, cc.created_at, p.title AS course_title, u.name AS learner_name')
                ->join('posts p', 'p.id = cc.post_id', 'inner')
                ->join('users u', 'u.id = cc.user_id', 'left')
                ->where('p.user_id', $userId)
                ->where('cc.type', 'problem')
                ->where('cc.parent_id', null)
                ->orderBy('cc.id', 'DESC')
                ->limit(50)
                ->get()
                ->getResultArray();
        }

        $problemIds = array_map(static fn($row) => (int) $row['id'], $problemRows);
        $solutionMap = [];
        if ($problemIds !== []) {
            $solutions = db_connect()->table('course_comments')
                ->select('id, parent_id, content, created_at')
                ->whereIn('parent_id', $problemIds)
                ->where('type', 'solution')
                ->orderBy('id', 'DESC')
                ->get()
                ->getResultArray();

            foreach ($solutions as $sol) {
                $pid = (int) ($sol['parent_id'] ?? 0);
                if (! isset($solutionMap[$pid])) {
                    $solutionMap[$pid] = $sol;
                }
            }
        }

        $userOverview = null;
        $allUsers = [];
        if ($isFounderAdmin) {
            $userOverview = [
                'total_users' => (int) db_connect()->table('users')->countAllResults(),
                'verified_users' => (int) db_connect()->table('users')->where('email_verified_at IS NOT NULL')->countAllResults(),
                'creators_with_posts' => (int) db_connect()->query('SELECT COUNT(DISTINCT user_id) AS c FROM posts')->getRow()->c,
            ];

            $allUsers = db_connect()->table('users u')
                ->select([
                    'u.id',
                    'u.name',
                    'u.email',
                    'u.phone',
                    'u.college',
                    'u.avatar',
                    'u.email_verified_at',
                    'u.created_at',
                    'COUNT(DISTINCT p.id) AS total_posts',
                    'COUNT(DISTINCT CASE WHEN p.is_published = 1 THEN p.id END) AS published_posts',
                ])
                ->join('posts p', 'p.user_id = u.id', 'left')
                ->groupBy('u.id')
                ->orderBy('u.id', 'DESC')
                ->get()
                ->getResultArray();
        }

        return view('dashboard', [
            'posts' => $posts,
            'q' => $q,
            'status' => $status,
            'category' => $category,
            'from' => $from,
            'to' => $to,
            'sort' => $sort,
            'perPage' => $perPage,
            'categories' => array_map(static fn($row) => $row['category'], $categories),
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
            'analytics' => $analytics,
            'problemRows' => $problemRows,
            'solutionMap' => $solutionMap,
            'isFounderAdmin' => $isFounderAdmin,
            'userOverview' => $userOverview,
            'allUsers' => $allUsers,
        ]);
    }
}
