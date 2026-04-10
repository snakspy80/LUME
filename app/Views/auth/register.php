<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<section class="card" style="max-width:680px; margin:0 auto;">
    <h2 style="margin-top:0;">Create Creator Account</h2>
    <p class="subtitle">Join Lume and publish your own course content.</p>

    <?php $errors = session()->getFlashdata('errors') ?? []; ?>

    <form method="post" action="/register" style="margin-top:10px;">
        <?= csrf_field() ?>

        <label class="label" for="name">Full Name</label>
        <input class="input" type="text" id="name" name="name" value="<?= esc(old('name')) ?>" required>
        <?php if (isset($errors['name'])): ?><p class="field-error"><?= esc($errors['name']) ?></p><?php endif; ?>

        <label class="label" for="email">Email</label>
        <input class="input" type="email" id="email" name="email" value="<?= esc(old('email')) ?>" required>
        <?php if (isset($errors['email'])): ?><p class="field-error"><?= esc($errors['email']) ?></p><?php endif; ?>

        <label class="label" for="college">College (for community grouping)</label>
        <input class="input" type="text" id="college" name="college" value="<?= esc(old('college')) ?>" placeholder="Example: IIT Kharagpur">
        <?php if (isset($errors['college'])): ?><p class="field-error"><?= esc($errors['college']) ?></p><?php endif; ?>

        <label class="label" for="password">Password</label>
        <input class="input" type="password" id="password" name="password" required>
        <?php if (isset($errors['password'])): ?><p class="field-error"><?= esc($errors['password']) ?></p><?php endif; ?>

        <label class="label" for="password_confirm">Confirm Password</label>
        <input class="input" type="password" id="password_confirm" name="password_confirm" required>
        <?php if (isset($errors['password_confirm'])): ?><p class="field-error"><?= esc($errors['password_confirm']) ?></p><?php endif; ?>

        <button class="btn btn-brand" type="submit">Register</button>
    </form>
</section>
<?= $this->endSection() ?>
