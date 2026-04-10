<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<section class="panel auth-shell">
    <h2 class="headline" style="font-size: clamp(28px, 5vw, 42px);">Set New Password</h2>
    <p class="subhead" style="margin-bottom: 18px;">Choose a strong password for your account.</p>

    <?php $errors = session()->getFlashdata('errors') ?? []; ?>

    <form method="post" action="/reset-password">
        <?= csrf_field() ?>
        <input type="hidden" name="token" value="<?= esc($token) ?>">

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

        <button class="btn" type="submit">Update Password</button>
    </form>
</section>
<?= $this->endSection() ?>
