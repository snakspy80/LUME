<?php

namespace App\Controllers;

use App\Models\PostAssetModel;
use App\Models\PostModel;
use CodeIgniter\HTTP\Files\UploadedFile;

class PostController extends BaseController
{
    private function notifyFollowersForPublishedPost(int $postId, int $userId, string $title): void
    {
        if (! lume_social_ready()) {
            return;
        }

        $followers = db_connect()->table('user_follows')
            ->select('follower_user_id')
            ->where('followed_user_id', $userId)
            ->get()
            ->getResultArray();

        foreach ($followers as $row) {
            $followerId = (int) ($row['follower_user_id'] ?? 0);
            if ($followerId <= 0) {
                continue;
            }

            lume_notification_create(
                $followerId,
                'new_post',
                'A creator you follow published: ' . $title,
                '/course/' . $postId,
                $userId,
                'post',
                $postId
            );
        }
    }

    private function iniToBytes(string $value): int
    {
        $value = trim($value);
        if ($value === '') {
            return 0;
        }

        $unit = strtolower(substr($value, -1));
        $number = (int) $value;

        return match ($unit) {
            'g' => $number * 1024 * 1024 * 1024,
            'm' => $number * 1024 * 1024,
            'k' => $number * 1024,
            default => (int) $value,
        };
    }

    private function oversizedSubmissionError(): ?string
    {
        $contentLength = (int) ($this->request->getServer('CONTENT_LENGTH') ?? 0);
        if ($contentLength <= 0) {
            return null;
        }

        $postMax = $this->iniToBytes((string) ini_get('post_max_size'));
        if ($postMax > 0 && $contentLength > $postMax) {
            return 'Upload too large for server limit (post_max_size=' . ini_get('post_max_size') . '). Reduce file size or increase PHP upload limits.';
        }

        return null;
    }

    private function ensureLoggedIn()
    {
        if (! session()->get('user_id')) {
            return redirect()->to('/login')->with('error', 'Please login first.');
        }

        if (! session()->get('email_verified')) {
            return redirect()->to('/email/verify-notice')->with('error', 'Please verify your email to continue.');
        }

        return null;
    }

    private function postValidationRules(): array
    {
        return [
            'title' => 'required|min_length[3]|max_length[150]',
            'category' => 'permit_empty|max_length[100]',
            'content' => 'required|min_length[10]|max_length[2500000]',
            'video_url' => 'permit_empty|valid_url_strict[https]',
        ];
    }

    private function postValidationMessages(): array
    {
        return [
            'title' => [
                'required' => 'Title is required.',
                'min_length' => 'Title must be at least 3 characters.',
                'max_length' => 'Title cannot exceed 150 characters.',
            ],
            'category' => [
                'max_length' => 'Category cannot exceed 100 characters.',
            ],
            'content' => [
                'required' => 'Content is required.',
                'min_length' => 'Content must be at least 10 characters.',
                'max_length' => 'Content cannot exceed 2,500,000 characters (supports very long courses).',
            ],
            'video_url' => [
                'valid_url_strict' => 'Video URL must be a valid https URL.',
            ],
        ];
    }

    private function uploadsEnabled(): bool
    {
        return db_connect()->tableExists('post_assets');
    }

    private function uploadDir(string $folder): string
    {
        $relative = 'uploads/posts/' . $folder;
        $absolute = rtrim(FCPATH, '/') . '/' . $relative;
        if (! is_dir($absolute)) {
            mkdir($absolute, 0775, true);
        }

        return $relative;
    }

    private function validateVideoUpload(?UploadedFile $file): ?string
    {
        if (! $file || ! $file->isValid() || $file->getError() === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if ($file->getSizeByUnit('kb') > 204800) { // 200 MB
            return 'Uploaded video is too large. Max size is 200MB.';
        }

        $ext = strtolower((string) $file->getExtension());
        $allowedExt = ['mp4', 'webm', 'ogg', 'mov', 'm4v'];
        if (! in_array($ext, $allowedExt, true)) {
            return 'Video file must be mp4/webm/ogg/mov/m4v.';
        }

        return null;
    }

    private function normalizeNoteFiles(mixed $input): array
    {
        if ($input instanceof UploadedFile) {
            return [$input];
        }
        if (is_array($input)) {
            return array_values(array_filter($input, static fn($f) => $f instanceof UploadedFile));
        }

        return [];
    }

    private function validateNoteUploads(array $files): ?string
    {
        $allowedExt = ['pdf', 'png', 'jpg', 'jpeg', 'webp', 'gif', 'svg', 'txt', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'csv', 'zip', 'mp3', 'wav', 'mp4', 'webm'];

        foreach ($files as $file) {
            if (! $file->isValid() || $file->getError() === UPLOAD_ERR_NO_FILE) {
                continue;
            }

            $ext = strtolower((string) $file->getExtension());
            if (! in_array($ext, $allowedExt, true)) {
                return 'Some attached notes/media files use unsupported format.';
            }

            $maxKb = $ext === 'pdf' ? 153600 : 51200; // PDF 150MB, others 50MB
            if ($file->getSizeByUnit('kb') > $maxKb) {
                return $ext === 'pdf'
                    ? 'PDF file is too large. Max PDF size is 150MB.'
                    : 'Each notes/media file must be 50MB or smaller.';
            }
        }

        return null;
    }

    private function saveNoteUploads(array $files, int $postId, int $userId): void
    {
        if (! $this->uploadsEnabled()) {
            return;
        }

        $dir = $this->uploadDir('notes');
        $assetModel = new PostAssetModel();

        foreach ($files as $file) {
            if (! $file->isValid() || $file->getError() === UPLOAD_ERR_NO_FILE) {
                continue;
            }

            $mime = (string) ($file->getClientMimeType() ?? '');
            $size = (int) $file->getSize();
            $storedName = $file->getRandomName();
            try {
                $file->move(rtrim(FCPATH, '/') . '/' . $dir, $storedName, true);
            } catch (\Throwable $e) {
                log_message('error', 'Asset upload move failed: ' . $e->getMessage());
                continue;
            }
            $path = $dir . '/' . $storedName;
            $kind = str_starts_with($mime, 'image/') ? 'image' : (str_starts_with($mime, 'video/') ? 'media' : (str_starts_with($mime, 'audio/') ? 'media' : 'note'));

            $assetModel->insert([
                'post_id' => $postId,
                'user_id' => $userId,
                'file_path' => $path,
                'file_name' => $file->getClientName(),
                'mime_type' => $mime !== '' ? $mime : null,
                'file_size' => $size > 0 ? $size : null,
                'asset_kind' => $kind,
            ]);
        }
    }

    private function deletePhysicalFile(?string $relativePath): void
    {
        $relativePath = trim((string) $relativePath);
        if ($relativePath === '') {
            return;
        }
        $full = rtrim(FCPATH, '/') . '/' . ltrim($relativePath, '/');
        if (is_file($full)) {
            @unlink($full);
        }
    }

    public function create()
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        return view('posts/create');
    }

    public function store()
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        if ($oversize = $this->oversizedSubmissionError()) {
            return redirect()->back()->with('error', $oversize);
        }

        if (! $this->validate($this->postValidationRules(), $this->postValidationMessages())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $videoFile = $this->request->getFile('video_file');
        $videoError = $this->validateVideoUpload($videoFile);
        if ($videoError !== null) {
            return redirect()->back()->withInput()->with('error', $videoError);
        }

        $userId = (int) session()->get('user_id');

        $uploaded = $this->request->getFiles();
        $noteFiles = $this->normalizeNoteFiles($uploaded['note_files'] ?? []);
        $noteError = $this->validateNoteUploads($noteFiles);
        if ($noteError !== null) {
            return redirect()->back()->withInput()->with('error', $noteError);
        }

        $postModel = new PostModel();
        $videoUrl = lume_normalize_video_url((string) $this->request->getPost('video_url'));
        $videoFilePath = null;

        if ($videoFile && $videoFile->isValid() && $videoFile->getError() !== UPLOAD_ERR_NO_FILE) {
            $dir = $this->uploadDir('videos');
            $storedName = $videoFile->getRandomName();
            $videoFile->move(rtrim(FCPATH, '/') . '/' . $dir, $storedName, true);
            $videoFilePath = $dir . '/' . $storedName;
        }

        $saved = $postModel->insert([
            'user_id' => $userId,
            'title' => trim((string) $this->request->getPost('title')),
            'category' => trim((string) $this->request->getPost('category')),
            'content' => trim((string) $this->request->getPost('content')),
            'video_url' => $videoUrl,
            'video_file' => $videoFilePath,
            'is_published' => $this->request->getPost('is_published') ? 1 : 0,
        ]);

        if (! $saved) {
            $this->deletePhysicalFile($videoFilePath);
            return redirect()->back()->withInput()->with('error', 'Could not create post. Please try again.');
        }

        $postId = (int) $postModel->getInsertID();
        if ($postId > 0) {
            $this->saveNoteUploads($noteFiles, $postId, $userId);
            if ((int) $this->request->getPost('is_published') === 1) {
                $this->notifyFollowersForPublishedPost($postId, $userId, trim((string) $this->request->getPost('title')));
            }
        }

        return redirect()->to('/dashboard')->with('success', 'Course post created successfully.');
    }

    public function edit(int $id)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $postModel = new PostModel();
        $post = $postModel
            ->where('id', $id)
            ->where('user_id', (int) session()->get('user_id'))
            ->first();

        if (! $post) {
            return redirect()->to('/dashboard')->with('error', 'Post not found or access denied.');
        }

        $assets = [];
        if ($this->uploadsEnabled()) {
            $assets = (new PostAssetModel())
                ->where('post_id', $id)
                ->where('user_id', (int) session()->get('user_id'))
                ->orderBy('id', 'DESC')
                ->findAll();
        }

        return view('posts/edit', ['post' => $post, 'assets' => $assets]);
    }

    public function update(int $id)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        if ($oversize = $this->oversizedSubmissionError()) {
            return redirect()->back()->with('error', $oversize);
        }

        $postModel = new PostModel();
        $post = $postModel
            ->where('id', $id)
            ->where('user_id', (int) session()->get('user_id'))
            ->first();

        if (! $post) {
            return redirect()->to('/dashboard')->with('error', 'Post not found or access denied.');
        }

        if (! $this->validate($this->postValidationRules(), $this->postValidationMessages())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $videoFile = $this->request->getFile('video_file');
        $videoError = $this->validateVideoUpload($videoFile);
        if ($videoError !== null) {
            return redirect()->back()->withInput()->with('error', $videoError);
        }

        $userId = (int) session()->get('user_id');

        $uploaded = $this->request->getFiles();
        $noteFiles = $this->normalizeNoteFiles($uploaded['note_files'] ?? []);
        $noteError = $this->validateNoteUploads($noteFiles);
        if ($noteError !== null) {
            return redirect()->back()->withInput()->with('error', $noteError);
        }

        $videoFilePath = $post['video_file'] ?? null;
        if ((bool) $this->request->getPost('remove_video_file')) {
            $this->deletePhysicalFile((string) $videoFilePath);
            $videoFilePath = null;
        }

        if ($videoFile && $videoFile->isValid() && $videoFile->getError() !== UPLOAD_ERR_NO_FILE) {
            $dir = $this->uploadDir('videos');
            $storedName = $videoFile->getRandomName();
            $videoFile->move(rtrim(FCPATH, '/') . '/' . $dir, $storedName, true);
            $newPath = $dir . '/' . $storedName;
            $this->deletePhysicalFile((string) $videoFilePath);
            $videoFilePath = $newPath;
        }

        $updated = $postModel->update($id, [
            'title' => trim((string) $this->request->getPost('title')),
            'category' => trim((string) $this->request->getPost('category')),
            'content' => trim((string) $this->request->getPost('content')),
            'video_url' => lume_normalize_video_url((string) $this->request->getPost('video_url')),
            'video_file' => $videoFilePath,
            'is_published' => $this->request->getPost('is_published') ? 1 : 0,
        ]);

        if (! $updated) {
            return redirect()->back()->withInput()->with('error', 'Could not update post. Please try again.');
        }

        if ((int) ($post['is_published'] ?? 0) !== 1 && (int) $this->request->getPost('is_published') === 1) {
            $this->notifyFollowersForPublishedPost($id, $userId, trim((string) $this->request->getPost('title')));
        }

        if ($this->uploadsEnabled()) {
            $assetModel = new PostAssetModel();
            $deleteAssetIds = $this->request->getPost('delete_asset_ids');
            if (is_array($deleteAssetIds)) {
                foreach ($deleteAssetIds as $assetId) {
                    $asset = $assetModel
                        ->where('id', (int) $assetId)
                        ->where('post_id', $id)
                        ->where('user_id', $userId)
                        ->first();
                    if ($asset) {
                        $this->deletePhysicalFile((string) ($asset['file_path'] ?? ''));
                        $assetModel->delete((int) $asset['id']);
                    }
                }
            }
        }

        $this->saveNoteUploads($noteFiles, $id, $userId);

        return redirect()->to('/dashboard')->with('success', 'Course post updated successfully.');
    }

    public function delete(int $id)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $postModel = new PostModel();
        $post = $postModel
            ->where('id', $id)
            ->where('user_id', (int) session()->get('user_id'))
            ->first();

        if (! $post) {
            return redirect()->to('/dashboard')->with('error', 'Post not found or access denied.');
        }

        $this->deletePhysicalFile((string) ($post['video_file'] ?? ''));
        if ($this->uploadsEnabled()) {
            $assetModel = new PostAssetModel();
            $assets = $assetModel
                ->where('post_id', $id)
                ->where('user_id', (int) session()->get('user_id'))
                ->findAll();

            foreach ($assets as $asset) {
                $this->deletePhysicalFile((string) ($asset['file_path'] ?? ''));
            }

            $assetModel->where('post_id', $id)->where('user_id', (int) session()->get('user_id'))->delete();
        }

        $postModel->delete($id);

        return redirect()->to('/dashboard')->with('success', 'Course post deleted successfully.');
    }
}
