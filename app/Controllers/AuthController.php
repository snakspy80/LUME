<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\Database\Exceptions\DatabaseException;

class AuthController extends BaseController
{
    private const FOUNDER_EMAIL = 'rudrabiswas080808@gmail.com';

    private function throttleOrRedirect(string $key, int $maxRequests, int $perSeconds, string $message)
    {
        $throttler = service('throttler');
        $safeKey = preg_replace('/[^A-Za-z0-9._-]/', '_', $key) ?? '';
        if ($safeKey === '') {
            $safeKey = 'throttle_default';
        }

        if (! $throttler->check($safeKey, $maxRequests, $perSeconds)) {
            return redirect()->back()->withInput()->with('error', $message);
        }

        return null;
    }

    private function shouldRequireEmailVerification(): bool
    {
        return (bool) env('auth.requireEmailVerification', ENVIRONMENT === 'production');
    }

    private function completeLogin(array $user): void
    {
        session()->regenerate();
        session()->set([
            'user_id' => $user['id'],
            'user_name' => $user['name'],
            'is_logged_in' => true,
            'email_verified' => true,
            'user_college' => $user['college'] ?? null,
            'user_avatar' => $user['avatar'] ?? null,
        ]);
    }

    public function register()
    {
        if (session()->get('user_id')) {
            return redirect()->to('/dashboard');
        }

        return view('auth/register');
    }

    public function store()
    {
        $input = [
            'name' => trim((string) $this->request->getPost('name')),
            'email' => strtolower(trim((string) $this->request->getPost('email'))),
            'college' => trim((string) $this->request->getPost('college')),
            'password' => (string) $this->request->getPost('password'),
            'password_confirm' => (string) $this->request->getPost('password_confirm'),
        ];

        $rules = [
            'name' => 'required|min_length[2]|max_length[100]',
            'email' => 'required|valid_email|is_unique[users.email]',
            'college' => 'permit_empty|max_length[150]',
            'password' => 'required|min_length[8]|max_length[72]',
            'password_confirm' => 'required|matches[password]',
        ];

        if (! $this->validateData($input, $rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $userModel = new UserModel();
        $requireVerification = $this->shouldRequireEmailVerification();

        try {
            $userId = $userModel->insert([
                'name' => $input['name'],
                'email' => $input['email'],
                'phone' => null,
                'college' => $input['college'],
                'password' => password_hash($input['password'], PASSWORD_DEFAULT),
                'email_verified_at' => $requireVerification ? null : date('Y-m-d H:i:s'),
            ]);
        } catch (DatabaseException $e) {
            log_message('error', 'Registration failed for {email}: {message}', [
                'email' => $input['email'],
                'message' => $e->getMessage(),
            ]);

            if (str_contains($e->getMessage(), 'users_phone_unique')) {
                return redirect()->back()->withInput()->with('error', 'Account creation is blocked by the current phone field setup. Run the latest migration, then try again.');
            }

            if (str_contains($e->getMessage(), 'users_email_unique')) {
                return redirect()->back()->withInput()->with('errors', [
                    'email' => 'That email address is already registered.',
                ]);
            }

            return redirect()->back()->withInput()->with('error', 'Could not create account right now.');
        }

        if (! $userId) {
            return redirect()->back()->withInput()->with('error', 'Could not create account right now.');
        }

        if ($requireVerification) {
            $user = $userModel->find($userId);
            $otp = $this->issueOtpToken($user, 15, 'email_verify_otp');
            $this->sendOtpEmail($user, 'email_verify_otp', 15, $otp);
            session()->setTempdata('verify_email', $input['email'], 900);

            return redirect()->to('/email/verify-notice')->with('success', 'Account created. Enter the OTP sent to your email to verify your account.');
        }

        return redirect()->to('/login')->with('success', 'Account created. Please login.');
    }

    public function login()
    {
        if (session()->get('user_id')) {
            return redirect()->to('/dashboard');
        }

        return view('auth/login');
    }

    public function authenticate()
    {
        $ip = (string) $this->request->getIPAddress();
        if ($redirect = $this->throttleOrRedirect('login-ip:' . sha1($ip), 20, 60, 'Too many login attempts. Please wait a minute and try again.')) {
            return $redirect;
        }

        $rules = [
            'email' => 'required|valid_email',
            'password' => 'required',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $email = strtolower(trim((string) $this->request->getPost('email')));
        $password = (string) $this->request->getPost('password');
        if ($redirect = $this->throttleOrRedirect('login-email:' . sha1($email), 8, 300, 'Too many attempts for this account. Please wait a few minutes.')) {
            return $redirect;
        }

        $userModel = new UserModel();
        $user = $userModel->where('email', $email)->first();

        if (! $user || ! password_verify($password, $user['password'])) {
            return redirect()->back()->withInput()->with('error', 'Invalid email or password.');
        }

        if ($this->shouldRequireEmailVerification() && empty($user['email_verified_at'])) {
            $otp = $this->issueOtpToken($user, 15, 'email_verify_otp');
            $this->sendOtpEmail($user, 'email_verify_otp', 15, $otp);
            session()->setTempdata('verify_email', $user['email'], 900);

            return redirect()->to('/email/verify-notice')->with('error', 'Please verify your email with OTP before login.');
        }

        if (password_needs_rehash($user['password'], PASSWORD_DEFAULT)) {
            $userModel->update($user['id'], ['password' => password_hash($password, PASSWORD_DEFAULT)]);
        }

        $otp = $this->issueOtpToken($user, 10, 'login_otp');
        $sent = $this->sendOtpEmail($user, 'login_otp', 10, $otp);
        if (ENVIRONMENT === 'production' && ! $sent) {
            return redirect()->back()->withInput()->with('error', 'Unable to send login OTP right now. Please try again shortly.');
        }

        session()->setTempdata('login_otp_email', $user['email'], 600);

        return redirect()->to('/login-otp')->with('success', 'Enter the OTP sent to your email to complete login.');
    }

    public function verifyNotice()
    {
        if (! $this->shouldRequireEmailVerification()) {
            return redirect()->to('/login')->with('success', 'Email verification is currently optional.');
        }

        $email = session()->getTempdata('verify_email');

        if (! $email && session()->get('user_id')) {
            $userModel = new UserModel();
            $user = $userModel->find(session()->get('user_id'));
            if ($user && empty($user['email_verified_at'])) {
                $email = $user['email'];
            }
        }

        return view('auth/verify_notice', ['email' => $email]);
    }

    public function verifyEmailOtp()
    {
        $rules = [
            'email' => 'required|valid_email',
            'otp' => 'required|exact_length[6]|numeric',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $email = strtolower(trim((string) $this->request->getPost('email')));
        $otp = trim((string) $this->request->getPost('otp'));
        $row = $this->findValidToken($otp, 'email_verify_otp', $email);

        if (! $row) {
            return redirect()->back()->withInput()->with('error', 'Invalid or expired verification OTP.');
        }

        $userModel = new UserModel();
        $user = $userModel->where('email', $email)->first();
        if (! $user) {
            return redirect()->to('/login')->with('error', 'Account not found.');
        }

        $now = date('Y-m-d H:i:s');
        $userModel->update($user['id'], ['email_verified_at' => $now]);

        $this->dbTokenBuilder()
            ->where('email', $email)
            ->where('type', 'email_verify_otp')
            ->set('used_at', $now)
            ->update();

        return redirect()->to('/login')->with('success', 'Email verified successfully. Please login to continue.');
    }

    public function resendVerification()
    {
        $ip = (string) $this->request->getIPAddress();
        if ($redirect = $this->throttleOrRedirect('verify-resend-ip:' . sha1($ip), 10, 600, 'Too many verification requests. Please try later.')) {
            return $redirect;
        }

        if (! $this->shouldRequireEmailVerification()) {
            return redirect()->to('/login')->with('success', 'Email verification is currently optional.');
        }

        $email = strtolower(trim((string) $this->request->getPost('email')));
        if ($email === '') {
            $email = (string) session()->getTempdata('verify_email');
        }

        if ($email === '') {
            return redirect()->back()->with('error', 'No email found for verification resend.');
        }

        $userModel = new UserModel();
        $user = $userModel->where('email', $email)->first();

        if (! $user) {
            return redirect()->back()->with('error', 'User not found for this email.');
        }

        if (! empty($user['email_verified_at'])) {
            return redirect()->to('/login')->with('success', 'Email is already verified.');
        }

        $otp = $this->issueOtpToken($user, 15, 'email_verify_otp');
        $sent = $this->sendOtpEmail($user, 'email_verify_otp', 15, $otp);
        session()->setTempdata('verify_email', $email, 900);

        if (ENVIRONMENT === 'production' && ! $sent) {
            return redirect()->back()->with('error', 'Unable to send verification OTP right now.');
        }

        return redirect()->back()->with('success', 'Verification OTP resent.');
    }

    public function loginOtp()
    {
        if (session()->get('user_id')) {
            return redirect()->to('/dashboard');
        }

        return view('auth/login_otp', [
            'email' => session()->getTempdata('login_otp_email'),
        ]);
    }

    public function verifyLoginOtp()
    {
        $rules = [
            'email' => 'required|valid_email',
            'otp' => 'required|exact_length[6]|numeric',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $email = strtolower(trim((string) $this->request->getPost('email')));
        $otp = trim((string) $this->request->getPost('otp'));
        $row = $this->findValidToken($otp, 'login_otp', $email);

        if (! $row) {
            return redirect()->back()->withInput()->with('error', 'Invalid or expired login OTP.');
        }

        $userModel = new UserModel();
        $user = $userModel->where('email', $email)->first();
        if (! $user) {
            return redirect()->to('/login')->with('error', 'Account not found.');
        }

        $this->dbTokenBuilder()
            ->where('email', $email)
            ->where('type', 'login_otp')
            ->where('used_at', null)
            ->set('used_at', date('Y-m-d H:i:s'))
            ->update();

        $this->completeLogin($user);

        return redirect()->to('/dashboard')->with('success', 'Login successful.');
    }

    public function verifyEmail(string $token)
    {
        return redirect()->to('/email/verify-notice')->with('error', 'Link-based verification is disabled. Please use the OTP sent to your email.');
    }

    public function forgotPassword()
    {
        return view('auth/forgot_password');
    }

    public function sendResetLink()
    {
        $ip = (string) $this->request->getIPAddress();
        if ($redirect = $this->throttleOrRedirect('password-reset-ip:' . sha1($ip), 10, 600, 'Too many reset requests. Please try again later.')) {
            return $redirect;
        }

        $rules = ['email' => 'required|valid_email'];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $email = strtolower(trim((string) $this->request->getPost('email')));
        if ($redirect = $this->throttleOrRedirect('password-reset-email:' . sha1($email), 5, 900, 'Too many reset attempts for this account. Please wait and try again.')) {
            return $redirect;
        }

        $userModel = new UserModel();
        $user = $userModel->where('email', $email)->first();

        if ($user) {
            $otp = $this->issueOtpToken($user, 15);
            $sent = $this->issueAndSendToken($user, 'password_reset', 60, $otp);

            if (ENVIRONMENT === 'production' && ! $sent) {
                return redirect()->back()->withInput()->with('error', 'Unable to send reset email right now. Please try again shortly.');
            }
        }

        return redirect()->back()->with('success', 'If that email exists, a reset link and OTP have been sent.');
    }

    public function resetPasswordOtp()
    {
        return view('auth/reset_password_otp');
    }

    public function updatePasswordWithOtp()
    {
        $ip = (string) $this->request->getIPAddress();
        if ($redirect = $this->throttleOrRedirect('password-reset-otp-ip:' . sha1($ip), 12, 600, 'Too many OTP attempts. Please try again later.')) {
            return $redirect;
        }

        $rules = [
            'email' => 'required|valid_email',
            'otp' => 'required|exact_length[6]|numeric',
            'password' => 'required|min_length[8]|max_length[72]',
            'password_confirm' => 'required|matches[password]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $email = strtolower(trim((string) $this->request->getPost('email')));
        if ($redirect = $this->throttleOrRedirect('password-reset-otp-email:' . sha1($email), 8, 900, 'Too many OTP attempts for this account. Please wait and try again.')) {
            return $redirect;
        }

        $otp = trim((string) $this->request->getPost('otp'));
        $row = $this->findValidToken($otp, 'password_reset_otp', $email);
        if (! $row) {
            return redirect()->back()->withInput()->with('error', 'Invalid or expired OTP.');
        }

        $userModel = new UserModel();
        $user = $userModel->where('email', $email)->first();
        if (! $user) {
            return redirect()->back()->withInput()->with('error', 'Account not found.');
        }

        $userModel->update($user['id'], [
            'password' => password_hash((string) $this->request->getPost('password'), PASSWORD_DEFAULT),
        ]);

        $now = date('Y-m-d H:i:s');
        $this->dbTokenBuilder()
            ->where('email', $user['email'])
            ->whereIn('type', ['password_reset', 'password_reset_otp'])
            ->set('used_at', $now)
            ->update();

        return redirect()->to('/login')->with('success', 'Password updated using OTP. You can login now.');
    }

    public function resetPassword(string $token)
    {
        $row = $this->findValidToken($token, 'password_reset');

        if (! $row) {
            return redirect()->to('/forgot-password')->with('error', 'Invalid or expired reset link.');
        }

        return view('auth/reset_password', ['token' => $token]);
    }

    public function updatePassword()
    {
        $rules = [
            'token' => 'required',
            'password' => 'required|min_length[8]|max_length[72]',
            'password_confirm' => 'required|matches[password]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $token = (string) $this->request->getPost('token');
        $row = $this->findValidToken($token, 'password_reset');

        if (! $row) {
            return redirect()->to('/forgot-password')->with('error', 'Invalid or expired reset link.');
        }

        $userModel = new UserModel();
        $user = $userModel->where('email', $row['email'])->first();

        if (! $user) {
            return redirect()->to('/forgot-password')->with('error', 'Account not found.');
        }

        $userModel->update($user['id'], [
            'password' => password_hash((string) $this->request->getPost('password'), PASSWORD_DEFAULT),
        ]);

        $this->dbTokenBuilder()
            ->where('email', $user['email'])
            ->where('type', 'password_reset')
            ->set('used_at', date('Y-m-d H:i:s'))
            ->update();

        return redirect()->to('/login')->with('success', 'Password updated. You can login now.');
    }

    public function logout()
    {
        session()->destroy();

        return redirect()->to('/')->with('success', 'You have been logged out.');
    }

    private function dbTokenBuilder()
    {
        return db_connect()->table('auth_tokens');
    }

    private function issueAndSendToken(array $user, string $type, int $minutes, ?string $otpCode = null): bool
    {
        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);
        $expiresAt = date('Y-m-d H:i:s', time() + ($minutes * 60));
        $now = date('Y-m-d H:i:s');

        $this->dbTokenBuilder()
            ->where('email', $user['email'])
            ->where('type', $type)
            ->where('used_at', null)
            ->set('used_at', $now)
            ->update();

        $this->dbTokenBuilder()->insert([
            'user_id' => $user['id'] ?? null,
            'email' => $user['email'],
            'type' => $type,
            'token_hash' => $tokenHash,
            'expires_at' => $expiresAt,
            'created_at' => $now,
        ]);

        $path = $type === 'email_verify' ? '/email/verify/' : '/reset-password/';
        $url = site_url($path . $token);
        $subject = $type === 'email_verify' ? 'Verify your Lume email' : 'Reset your Lume password';
        $message = $type === 'email_verify'
            ? "Hi {$user['name']},\n\nPlease verify your email using this link:\n{$url}\n\nThis link expires in {$minutes} minutes."
            : "Hi {$user['name']},\n\nReset your password using this link:\n{$url}\n\nThis link expires in {$minutes} minutes.";

        if ($type === 'password_reset' && $otpCode !== null) {
            $message .= "\n\nOr use this OTP code: {$otpCode}\nOTP expires in 15 minutes.";
        }

        $sent = false;

        try {
            $email = service('email');
            if (config('Email')->fromEmail !== '') {
                $email->setFrom(config('Email')->fromEmail, config('Email')->fromName);
            }
            $email->setTo($user['email']);
            $email->setSubject($subject);
            $email->setMessage($message);
            $sent = $email->send();
            if (! $sent) {
                log_message(
                    'error',
                    sprintf(
                        'Email send failed [%s] to %s: %s',
                        $type,
                        $user['email'],
                        trim($email->printDebugger(['headers', 'subject']))
                    )
                );
            }
        } catch (\Throwable $e) {
            log_message('error', sprintf('Email send exception [%s] to %s: %s', $type, $user['email'], $e->getMessage()));
        }

        return $sent;
    }

    private function issueOtpToken(array $user, int $minutes, string $type = 'password_reset_otp'): string
    {
        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $now = date('Y-m-d H:i:s');
        $expiresAt = date('Y-m-d H:i:s', time() + ($minutes * 60));

        $this->dbTokenBuilder()
            ->where('email', $user['email'])
            ->where('type', $type)
            ->where('used_at', null)
            ->set('used_at', $now)
            ->update();

        $this->dbTokenBuilder()->insert([
            'user_id' => $user['id'] ?? null,
            'email' => $user['email'],
            'type' => $type,
            'token_hash' => hash('sha256', $otp),
            'expires_at' => $expiresAt,
            'created_at' => $now,
        ]);

        return $otp;
    }

    private function sendOtpEmail(array $user, string $type, int $minutes, string $otp): bool
    {
        $subject = match ($type) {
            'email_verify_otp' => 'Verify your Lume email',
            'login_otp' => 'Your Lume login OTP',
            default => 'Your Lume OTP',
        };

        $message = match ($type) {
            'email_verify_otp' => "Hi {$user['name']},\n\nUse this OTP to verify your Lume account:\n{$otp}\n\nThis OTP expires in {$minutes} minutes.",
            'login_otp' => "Hi {$user['name']},\n\nUse this OTP to complete your login:\n{$otp}\n\nThis OTP expires in {$minutes} minutes.",
            default => "Hi {$user['name']},\n\nYour OTP is:\n{$otp}\n\nThis OTP expires in {$minutes} minutes.",
        };

        $sent = false;
        try {
            $email = service('email');
            if (config('Email')->fromEmail !== '') {
                $email->setFrom(config('Email')->fromEmail, config('Email')->fromName);
            }
            $email->setTo($user['email']);
            $email->setSubject($subject);
            $email->setMessage($message);
            $sent = $email->send();
            if (! $sent) {
                log_message('error', sprintf('Email send failed [%s] to %s', $type, $user['email']));
            }
        } catch (\Throwable $e) {
            log_message('error', sprintf('Email send exception [%s] to %s: %s', $type, $user['email'], $e->getMessage()));
        }

        return $sent;
    }

    private function findValidToken(string $rawToken, string $type, ?string $email = null): ?array
    {
        $tokenHash = hash('sha256', $rawToken);

        $builder = $this->dbTokenBuilder()
            ->where('type', $type)
            ->where('token_hash', $tokenHash)
            ->where('used_at', null);

        if ($email !== null) {
            $builder->where('email', $email);
        }

        $row = $builder->get()->getRowArray();

        if (! $row) {
            return null;
        }

        if (strtotime((string) $row['expires_at']) < time()) {
            return null;
        }

        return $row;
    }
}
