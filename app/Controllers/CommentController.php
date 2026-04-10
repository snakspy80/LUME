<?php

namespace App\Controllers;

use App\Models\CourseCommentModel;
use App\Models\PostModel;

class CommentController extends BaseController
{
    private function requireAuth()
    {
        if (! session()->get('user_id')) {
            return redirect()->to('/login')->with('error', 'Please login first.');
        }

        return null;
    }

    public function store(int $postId)
    {
        if ($redirect = $this->requireAuth()) {
            return $redirect;
        }

        if (! db_connect()->tableExists('course_comments')) {
            return redirect()->back()->with('error', 'Comments are not ready yet. Please run database migrations.');
        }

        $post = (new PostModel())
            ->where('id', $postId)
            ->where('is_published', 1)
            ->first();

        if (! $post) {
            return redirect()->back()->with('error', 'Course not found.');
        }

        $rules = [
            'type' => 'required|in_list[problem,review]',
            'content' => 'required|min_length[3]|max_length[5000]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        (new CourseCommentModel())->insert([
            'post_id' => $postId,
            'user_id' => (int) session()->get('user_id'),
            'parent_id' => null,
            'type' => trim((string) $this->request->getPost('type')),
            'content' => trim((string) $this->request->getPost('content')),
        ]);

        if (lume_social_ready() && (int) ($post['user_id'] ?? 0) > 0 && (int) ($post['user_id'] ?? 0) !== (int) session()->get('user_id')) {
            $type = trim((string) $this->request->getPost('type'));
            lume_notification_create(
                (int) $post['user_id'],
                'course_' . $type,
                'New ' . $type . ' on your course: ' . ($post['title'] ?? 'Course'),
                '/course/' . $postId,
                (int) session()->get('user_id'),
                'post',
                $postId
            );
        }

        return redirect()->to('/course/' . $postId)->with('success', 'Your comment was posted.');
    }

    public function replyProblem(int $commentId)
    {
        if ($redirect = $this->requireAuth()) {
            return $redirect;
        }

        if (! db_connect()->tableExists('course_comments')) {
            return redirect()->back()->with('error', 'Comments are not ready yet. Please run database migrations.');
        }

        $userId = (int) session()->get('user_id');
        $commentModel = new CourseCommentModel();

        $problem = $commentModel
            ->where('id', $commentId)
            ->where('type', 'problem')
            ->where('parent_id', null)
            ->first();

        if (! $problem) {
            return redirect()->back()->with('error', 'Problem comment not found.');
        }

        $ownsCourse = db_connect()->table('posts')
            ->where('id', (int) $problem['post_id'])
            ->where('user_id', $userId)
            ->countAllResults() > 0;

        if (! $ownsCourse) {
            return redirect()->back()->with('error', 'You can only reply to problems on your own courses.');
        }

        $rules = ['content' => 'required|min_length[3]|max_length[5000]'];
        if (! $this->validate($rules)) {
            return redirect()->back()->with('error', 'Reply must be at least 3 characters.');
        }

        $replyText = trim((string) $this->request->getPost('content'));

        $existing = $commentModel
            ->where('parent_id', $commentId)
            ->where('type', 'solution')
            ->first();

        if ($existing) {
            $commentModel->update((int) $existing['id'], ['content' => $replyText]);
            return redirect()->back()->with('success', 'Solution reply updated.');
        }

        $commentModel->insert([
            'post_id' => (int) $problem['post_id'],
            'user_id' => $userId,
            'parent_id' => $commentId,
            'type' => 'solution',
            'content' => $replyText,
        ]);

        if (lume_social_ready() && (int) ($problem['user_id'] ?? 0) > 0 && (int) ($problem['user_id'] ?? 0) !== $userId) {
            lume_notification_create(
                (int) $problem['user_id'],
                'problem_reply',
                'Your problem received a solution reply.',
                '/course/' . (int) $problem['post_id'],
                $userId,
                'comment',
                $commentId
            );
        }

        return redirect()->back()->with('success', 'Solution reply posted.');
    }
}
