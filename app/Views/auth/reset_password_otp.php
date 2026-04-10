<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<section class="panel auth-shell">
    <h2 class="headline" style="font-size: clamp(28px, 5vw, 42px);">Reset with OTP</h2>
    <p class="subhead" style="margin-bottom: 18px;">Enter the 6-digit OTP from your email and set a new password.</p>

    <?php $errors = session()->getFlashdata('errors') ?? []; ?>

    <form method="post" action="/reset-password-otp">
        <?= csrf_field() ?>

        <label class="label" for="email">Email</label>
        <input class="input <?= isset($errors['email']) ? 'is-invalid' : '' ?>" type="email" id="email" name="email" value="<?= esc(old('email')) ?>" required>
        <?php if (isset($errors['email'])): ?>
            <p class="field-error"><?= esc($errors['email']) ?></p>
        <?php endif; ?>

        <label class="label" for="otp">OTP (6 digits)</label>
        <input class="input <?= isset($errors['otp']) ? 'is-invalid' : '' ?>" type="text" id="otp" name="otp" maxlength="6" inputmode="numeric" pattern="[0-9]{6}" value="<?= esc(old('otp')) ?>" required>
        <?php if (isset($errors['otp'])): ?>
            <p class="field-error"><?= esc($errors['otp']) ?></p>
        <?php endif; ?>

        <label class="label" for="password">New Password</label>
        <input class="input <?= isset($errors['password']) ? 'is-invalid' : '' ?>" type="password" id="password" name="password" required>
        <?php if (isset($errors['password'])): ?>
            <p class="field-error"><?= esc($errors['password']) ?></p>
        <?php endif; ?>

        <label class="label" for="password_confirm">Confirm Password</label>
        <input class="input <?= isset($errors['password_confirm']) ? 'is-invalid' : '' ?>" type="password" id="password_confirm" name="password_confirm" required>
        <?php if (isset($errors['password_confirm'])): ?>
            <p class="field-error"><?= esc($errors['password_confirm']) ?></p>
        <?php endif; ?>

        <div class="stack">
            <button class="btn" type="submit">Reset Password</button>
            <a class="btn btn-outline" href="/forgot-password">Back</a>
        </div>
    </form>
</section>
<?= $this->endSection() ?>
