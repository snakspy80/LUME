<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="auth-shell">
    <section class="panel">
        <h2 class="headline" style="font-size: clamp(28px, 5vw, 42px);">Login OTP</h2>
        <p class="subhead" style="margin-bottom: 18px;">Enter the OTP sent to your email to complete login.</p>

        <?php $errors = session()->getFlashdata('errors') ?? []; ?>

        <form method="post" action="/login-otp">
            <?= csrf_field() ?>

            <label class="label" for="email">Email</label>
            <input class="input <?= isset($errors['email']) ? 'is-invalid' : '' ?>" type="email" id="email" name="email" value="<?= esc(old('email') ?: ($email ?? '')) ?>" required>
            <?php if (isset($errors['email'])): ?><p class="field-error"><?= esc($errors['email']) ?></p><?php endif; ?>

            <label class="label" for="otp">OTP</label>
            <input
                class="input <?= isset($errors['otp']) ? 'is-invalid' : '' ?>"
                type="text"
                id="otp"
                name="otp"
                maxlength="6"
                inputmode="numeric"
                autocomplete="one-time-code"
                pattern="[0-9]{6}"
                placeholder="123456"
                value="<?= esc(old('otp')) ?>"
                style="max-width:220px; text-align:center; letter-spacing:0.12em; font-size:22px; font-weight:700;"
                oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,6)"
                required
            >
            <p class="meta" style="margin-top:-4px;">Enter the 6-digit OTP from your email.</p>
            <?php if (isset($errors['otp'])): ?><p class="field-error"><?= esc($errors['otp']) ?></p><?php endif; ?>

            <div class="stack">
                <button class="btn" type="submit">Verify OTP</button>
                <a class="btn btn-outline" href="/login">Back to Login</a>
            </div>
        </form>
    </section>
</div>
<?= $this->endSection() ?>
