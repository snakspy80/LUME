<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index(): string
    {
        $q = trim((string) $this->request->getGet('q'));
        $category = trim((string) $this->request->getGet('category'));
        $sort = trim((string) $this->request->getGet('sort'));
        if (! in_array($sort, ['latest', 'popular'], true)) {
            $sort = 'latest';
        }

        $page = max(1, (int) $this->request->getGet('page'));
        $perPage = 9;

        $baseBuilder = db_connect()->table('posts p')
            ->select('p.*, u.name AS author_name, COALESCE(u.college, "Independent") AS author_college, COUNT(DISTINCT cv.id) AS views_count, COUNT(DISTINCT cb.id) AS bookmarks_count, COUNT(DISTINCT cp.id) AS completions_count')
            ->join('users u', 'u.id = p.user_id', 'left')
            ->join('course_views cv', 'cv.post_id = p.id', 'left')
            ->join('course_bookmarks cb', 'cb.post_id = p.id', 'left')
            ->join('course_progress cp', 'cp.post_id = p.id', 'left')
            ->where('p.is_published', 1)
            ->groupBy('p.id');

        if ($q !== '') {
            $baseBuilder->groupStart()
                ->like('p.title', $q)
                ->orLike('p.content', $q)
                ->orLike('u.name', $q)
                ->groupEnd();
        }

        if ($category !== '') {
            $baseBuilder->where('p.category', $category);
        }

        $countBuilder = db_connect()->table('posts p')->where('p.is_published', 1);
        if ($q !== '') {
            $countBuilder->groupStart()->like('p.title', $q)->orLike('p.content', $q)->groupEnd();
        }
        if ($category !== '') {
            $countBuilder->where('p.category', $category);
        }

        $total = (int) $countBuilder->countAllResults();
        $totalPages = max(1, (int) ceil($total / $perPage));
        if ($page > $totalPages) {
            $page = $totalPages;
        }

        if ($sort === 'popular') {
            $baseBuilder->orderBy('views_count', 'DESC');
            $baseBuilder->orderBy('bookmarks_count', 'DESC');
        } else {
            $baseBuilder->orderBy('p.id', 'DESC');
        }

        $posts = $baseBuilder
            ->limit($perPage, ($page - 1) * $perPage)
            ->get()
            ->getResultArray();

        $userId = (int) (session()->get('user_id') ?? 0);
        $bookmarkedIds = [];
        if ($userId > 0 && $posts !== []) {
            $ids = array_map(static fn($p) => (int) $p['id'], $posts);
            $rows = db_connect()->table('course_bookmarks')
                ->select('post_id')
                ->where('user_id', $userId)
                ->whereIn('post_id', $ids)
                ->get()
                ->getResultArray();
            $bookmarkedIds = array_map(static fn($r) => (int) $r['post_id'], $rows);
        }

        foreach ($posts as &$post) {
            $post['video_url'] = lume_normalize_video_url((string) ($post['video_url'] ?? ''));
            $post['is_bookmarked'] = in_array((int) $post['id'], $bookmarkedIds, true);
        }
        unset($post);

        $categories = db_connect()->table('posts')
            ->select('category')
            ->where('is_published', 1)
            ->where('category IS NOT NULL')
            ->where('category !=', '')
            ->groupBy('category')
            ->orderBy('category', 'ASC')
            ->get()
            ->getResultArray();

        return view('home', [
            'posts' => $posts,
            'categories' => array_map(static fn($row) => $row['category'], $categories),
            'q' => $q,
            'category' => $category,
            'sort' => $sort,
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
        ]);
    }

    public function course(int $id)
    {
        $course = db_connect()->table('posts p')
            ->select('p.*, u.name AS author_name, u.college AS author_college, u.bio AS author_bio, COUNT(DISTINCT cv.id) AS views_count, COUNT(DISTINCT cb.id) AS bookmarks_count, COUNT(DISTINCT cp.id) AS completions_count')
            ->join('users u', 'u.id = p.user_id', 'left')
            ->join('course_views cv', 'cv.post_id = p.id', 'left')
            ->join('course_bookmarks cb', 'cb.post_id = p.id', 'left')
            ->join('course_progress cp', 'cp.post_id = p.id', 'left')
            ->where('p.id', $id)
            ->where('p.is_published', 1)
            ->groupBy('p.id')
            ->get()
            ->getRowArray();

        if (! $course) {
            return redirect()->to('/')->with('error', 'Course not found.');
        }

        db_connect()->table('course_views')->insert([
            'user_id' => session()->get('user_id') ? (int) session()->get('user_id') : null,
            'post_id' => $id,
            'ip_address' => (string) $this->request->getIPAddress(),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $course['video_url'] = lume_normalize_video_url((string) ($course['video_url'] ?? ''));

        $userId = (int) (session()->get('user_id') ?? 0);
        $course['is_bookmarked'] = false;
        $course['is_completed'] = false;
        if ($userId > 0) {
            $course['is_bookmarked'] = (bool) db_connect()->table('course_bookmarks')->where('user_id', $userId)->where('post_id', $id)->countAllResults();
            $course['is_completed'] = (bool) db_connect()->table('course_progress')->where('user_id', $userId)->where('post_id', $id)->countAllResults();
        }

        $related = db_connect()->table('posts')
            ->select('id, title, category')
            ->where('is_published', 1)
            ->where('id !=', $id)
            ->where('category', (string) ($course['category'] ?? ''))
            ->orderBy('id', 'DESC')
            ->limit(4)
            ->get()
            ->getResultArray();

        $comments = [];
        if (db_connect()->tableExists('course_comments')) {
            $comments = db_connect()->table('course_comments cc')
                ->select('cc.id, cc.post_id, cc.user_id, cc.parent_id, cc.type, cc.content, cc.created_at, u.name AS user_name')
                ->join('users u', 'u.id = cc.user_id', 'left')
                ->where('cc.post_id', $id)
                ->where('cc.parent_id', null)
                ->whereIn('cc.type', ['problem', 'review'])
                ->orderBy('cc.id', 'DESC')
                ->get()
                ->getResultArray();
        }

        $repliesByParent = [];
        if ($comments !== [] && db_connect()->tableExists('course_comments')) {
            $commentIds = array_map(static fn($row) => (int) $row['id'], $comments);
            $replies = db_connect()->table('course_comments cc')
                ->select('cc.id, cc.parent_id, cc.content, cc.created_at, u.name AS user_name')
                ->join('users u', 'u.id = cc.user_id', 'left')
                ->whereIn('cc.parent_id', $commentIds)
                ->where('cc.type', 'solution')
                ->orderBy('cc.id', 'ASC')
                ->get()
                ->getResultArray();

            foreach ($replies as $reply) {
                $parent = (int) ($reply['parent_id'] ?? 0);
                $repliesByParent[$parent][] = $reply;
            }
        }

        $assets = [];
        if (db_connect()->tableExists('post_assets')) {
            $assets = db_connect()->table('post_assets')
                ->select('id, file_path, file_name, mime_type, file_size, asset_kind, created_at')
                ->where('post_id', $id)
                ->orderBy('id', 'DESC')
                ->get()
                ->getResultArray();
        }

        return view('courses/show', [
            'course' => $course,
            'related' => $related,
            'comments' => $comments,
            'repliesByParent' => $repliesByParent,
            'assets' => $assets,
        ]);
    }
}
