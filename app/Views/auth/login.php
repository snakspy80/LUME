<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="auth-shell">
    <section class="panel">
        <h2 class="headline" style="font-size: clamp(28px, 5vw, 42px);">Welcome Back</h2>
        <p class="subhead" style="margin-bottom: 18px;">Log in to continue building with Lume.</p>

        <?php $errors = session()->getFlashdata('errors') ?? []; ?>

        <form method="post" action="/login">
            <?= csrf_field() ?>

            <label class="label" for="email">Email</label>
            <input class="input <?= isset($errors['email']) ? 'is-invalid' : '' ?>" type="email" id="email" name="email" value="<?= esc(old('email')) ?>" required>
            <?php if (isset($errors['email'])): ?>
                <p class="field-error"><?= esc($errors['email']) ?></p>
            <?php endif; ?>

            <label class="label" for="password">Password</label>
            <input class="input <?= isset($errors['password']) ? 'is-invalid' : '' ?>" type="password" id="password" name="password" required>
            <?php if (isset($errors['password'])): ?>
                <p class="field-error"><?= esc($errors['password']) ?></p>
            <?php endif; ?>

            <div class="stack">
            <button class="btn" type="submit">Login</button>
            <a class="btn btn-outline" href="/forgot-password">Forgot Password</a>
        </div>
        </form>
    </section>
</div>
<?= $this->endSection() ?>
