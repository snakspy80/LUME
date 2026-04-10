<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\Database\Exceptions\DatabaseException;

class ProfileController extends BaseController
{
    private function requireAuth()
    {
        if (! session()->get('user_id')) {
            return redirect()->to('/login')->with('error', 'Please login first.');
        }

        return null;
    }

    public function edit()
    {
        if ($redirect = $this->requireAuth()) {
            return $redirect;
        }

        $user = (new UserModel())->find((int) session()->get('user_id'));

        if (! $user) {
            session()->destroy();
            return redirect()->to('/login')->with('error', 'Session expired. Please login again.');
        }

        $fields = [
            (string) ($user['name'] ?? ''),
            (string) ($user['email'] ?? ''),
            (string) ($user['phone'] ?? ''),
            (string) ($user['college'] ?? ''),
            (string) ($user['bio'] ?? ''),
            (string) ($user['avatar'] ?? ''),
        ];

        $filled = 0;
        foreach ($fields as $field) {
            if (trim($field) !== '') {
                $filled++;
            }
        }

        $profileCompletion = (int) round(($filled / count($fields)) * 100);

        return view('profile/edit', [
            'user' => $user,
            'profileCompletion' => $profileCompletion,
        ]);
    }

    public function update()
    {
        if ($redirect = $this->requireAuth()) {
            return $redirect;
        }

        $userId = (int) session()->get('user_id');
        $userModel = new UserModel();
        $current = $userModel->find($userId);

        if (! $current) {
            session()->destroy();
            return redirect()->to('/login')->with('error', 'Session expired. Please login again.');
        }

        $rules = [
            'name' => 'required|min_length[2]|max_length[100]',
            'email' => "required|valid_email|is_unique[users.email,id,{$userId}]",
            'phone' => "permit_empty|max_length[25]|regex_match[/^[0-9+()\\-\\s]{7,25}$/]|is_unique[users.phone,id,{$userId}]",
            'college' => 'permit_empty|max_length[150]',
            'bio' => 'permit_empty|max_length[2000]',
            'avatar' => 'permit_empty|uploaded[avatar]|is_image[avatar]|max_size[avatar,2048]|mime_in[avatar,image/jpg,image/jpeg,image/png,image/webp]',
        ];

        $input = [
            'name' => trim((string) $this->request->getPost('name')),
            'email' => strtolower(trim((string) $this->request->getPost('email'))),
            'phone' => trim((string) $this->request->getPost('phone')),
            'college' => trim((string) $this->request->getPost('college')),
            'bio' => trim((string) $this->request->getPost('bio')),
        ];

        $avatarFile = $this->request->getFile('avatar');
        if (! $avatarFile || ! $avatarFile->isValid() || $avatarFile->getError() === UPLOAD_ERR_NO_FILE) {
            unset($rules['avatar']);
        }

        if (! $this->validateData($input, $rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'name' => $input['name'],
            'email' => $input['email'],
            'phone' => $input['phone'] !== '' ? $input['phone'] : null,
            'college' => $input['college'],
            'bio' => $input['bio'],
        ];

        $removeAvatar = (bool) $this->request->getPost('remove_avatar');
        if ($removeAvatar) {
            if (! empty($current['avatar'])) {
                $oldPath = FCPATH . ltrim((string) $current['avatar'], '/');
                if (is_file($oldPath)) {
                    @unlink($oldPath);
                }
            }
            $data['avatar'] = null;
        }

        if ($avatarFile && $avatarFile->isValid() && $avatarFile->getError() !== UPLOAD_ERR_NO_FILE) {
            $uploadDir = FCPATH . 'uploads/avatars';
            if (! is_dir($uploadDir)) {
                mkdir($uploadDir, 0775, true);
            }

            $fileName = $avatarFile->getRandomName();
            $avatarFile->move($uploadDir, $fileName, true);
            $data['avatar'] = 'uploads/avatars/' . $fileName;

            if (! empty($current['avatar'])) {
                $oldPath = FCPATH . ltrim((string) $current['avatar'], '/');
                if (is_file($oldPath)) {
                    @unlink($oldPath);
                }
            }
        }

        try {
            $userModel->update($userId, $data);
        } catch (DatabaseException $e) {
            log_message('error', 'Profile update failed for user {userId}: {message}', [
                'userId' => $userId,
                'message' => $e->getMessage(),
            ]);

            if (str_contains($e->getMessage(), 'users_phone_unique')) {
                return redirect()->back()->withInput()->with('errors', [
                    'phone' => 'That phone number is already being used by another account.',
                ]);
            }

            if (str_contains($e->getMessage(), 'users_email_unique')) {
                return redirect()->back()->withInput()->with('errors', [
                    'email' => 'That email address is already registered.',
                ]);
            }

            return redirect()->back()->withInput()->with('error', 'Could not update your profile right now.');
        }

        session()->set([
            'user_name' => $data['name'],
            'user_college' => $data['college'],
            'user_avatar' => $data['avatar'] ?? ($removeAvatar ? null : ($current['avatar'] ?? null)),
        ]);

        return redirect()->to('/profile')->with('success', 'Profile updated successfully.');
    }
}
