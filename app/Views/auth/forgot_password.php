<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<section class="panel auth-shell">
    <h2 class="headline" style="font-size: clamp(28px, 5vw, 42px);">Forgot Password</h2>
    <p class="subhead" style="margin-bottom: 18px;">Enter your email and we will send a secure reset link.</p>

    <?php $errors = session()->getFlashdata('errors') ?? []; ?>

    <form method="post" action="/forgot-password">
        <?= csrf_field() ?>

        <label class="label" for="email">Email</label>
        <input class="input <?= isset($errors['email']) ? 'is-invalid' : '' ?>" type="email" id="email" name="email" value="<?= esc(old('email')) ?>" required>
        <?php if (isset($errors['email'])): ?>
            <p class="field-error"><?= esc($errors['email']) ?></p>
        <?php endif; ?>

        <div class="stack">
            <button class="btn" type="submit">Send Reset Link</button>
            <a class="btn btn-outline" href="/reset-password-otp">Reset with OTP</a>
            <a class="btn btn-outline" href="/login">Back to Login</a>
        </div>
    </form>
</section>
<?= $this->endSection() ?>
