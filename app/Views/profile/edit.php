<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
    $errors = session()->getFlashdata('errors') ?? [];
    $avatarPath = (string) ($user['avatar'] ?? '');
    $initial = strtoupper(substr((string) ($user['name'] ?? 'U'), 0, 1));
?>
<section class="card" style="max-width:980px; margin:0 auto;">
    <div class="profile-shell">
        <aside style="border:1px solid var(--line); border-radius:12px; background:rgba(255,255,255,.03); padding:14px;">
            <h2 style="margin:0 0 8px; font-size:18px;">Profile Snapshot</h2>
            <p class="subtitle" style="font-size:13px;">Keep your account complete so learners can trust your content.</p>

            <div style="margin-top:12px; display:flex; align-items:center; gap:10px;">
                <?php if ($avatarPath !== ''): ?>
                    <img src="/<?= esc($avatarPath) ?>" alt="avatar" style="width:64px; height:64px; border-radius:999px; object-fit:cover; border:1px solid var(--line);">
                <?php else: ?>
                    <div style="width:64px; height:64px; border-radius:999px; background:rgba(42,182,115,.14); color:#bdf3d6; display:grid; place-items:center; font-size:26px; font-weight:700; border:1px solid rgba(42,182,115,.28);"><?= esc($initial) ?></div>
                <?php endif; ?>
                <div style="min-width:0;">
                    <div style="font-weight:700; line-height:1.2;"><?= esc((string) ($user['name'] ?? 'User')) ?></div>
                    <div class="meta" style="margin:2px 0 0; overflow-wrap:anywhere; word-break:break-word;"><?= esc((string) ($user['email'] ?? '')) ?></div>
                </div>
            </div>

            <div style="margin-top:14px;">
                <div style="display:flex; justify-content:space-between; align-items:center; font-size:12px; margin-bottom:6px;">
                    <strong>Completion</strong>
                    <span><?= esc((string) ($profileCompletion ?? 0)) ?>%</span>
                </div>
                <div style="height:10px; background:rgba(255,255,255,.05); border-radius:999px; overflow:hidden; border:1px solid rgba(255,255,255,.08);">
                    <div style="height:100%; width:<?= esc((string) ($profileCompletion ?? 0)) ?>%; background:linear-gradient(90deg, #1f8c5f, #36b37e);"></div>
                </div>
            </div>
        </aside>

        <div>
            <h1 class="title" style="font-size: clamp(28px, 4vw, 40px);">Edit Profile</h1>
            <p class="subtitle">Update your public identity, contact, and creator details.</p>

            <form method="post" action="/profile" enctype="multipart/form-data" style="margin-top:12px;">
                <?= csrf_field() ?>

                <div class="grid grid-2">
                    <div>
                        <label class="label" for="name">Full Name</label>
                        <input class="input" id="name" name="name" type="text" value="<?= esc(old('name') ?: ($user['name'] ?? '')) ?>" required>
                        <?php if (isset($errors['name'])): ?><p class="field-error"><?= esc($errors['name']) ?></p><?php endif; ?>
                    </div>
                    <div>
                        <label class="label" for="email">Email</label>
                        <input class="input" id="email" name="email" type="email" value="<?= esc(old('email') ?: ($user['email'] ?? '')) ?>" required>
                        <?php if (isset($errors['email'])): ?><p class="field-error"><?= esc($errors['email']) ?></p><?php endif; ?>
                    </div>
                    <div>
                        <label class="label" for="phone">Phone</label>
                        <input class="input" id="phone" name="phone" type="text" value="<?= esc(old('phone') ?: ($user['phone'] ?? '')) ?>" placeholder="+91 98765 43210">
                        <?php if (isset($errors['phone'])): ?><p class="field-error"><?= esc($errors['phone']) ?></p><?php endif; ?>
                    </div>
                    <div>
                        <label class="label" for="college">College</label>
                        <input class="input" id="college" name="college" type="text" value="<?= esc(old('college') ?: ($user['college'] ?? '')) ?>">
                        <?php if (isset($errors['college'])): ?><p class="field-error"><?= esc($errors['college']) ?></p><?php endif; ?>
                    </div>
                </div>

                <label class="label" for="bio">Bio</label>
                <textarea id="bio" name="bio" rows="5"><?= esc(old('bio') ?: ($user['bio'] ?? '')) ?></textarea>
                <?php if (isset($errors['bio'])): ?><p class="field-error"><?= esc($errors['bio']) ?></p><?php endif; ?>

                <label class="label" for="avatar">Avatar (JPG/PNG/WEBP, max 2MB)</label>
                <input class="input" id="avatar" name="avatar" type="file" accept="image/*">
                <?php if (isset($errors['avatar'])): ?><p class="field-error"><?= esc($errors['avatar']) ?></p><?php endif; ?>

                <?php if ($avatarPath !== ''): ?>
                    <label style="display:flex; align-items:center; gap:8px; font-size:14px; margin:4px 0 10px;">
                        <input type="checkbox" name="remove_avatar" value="1">
                        Remove current avatar
                    </label>
                <?php endif; ?>

                <div class="stack" style="margin-top:10px;">
                    <button class="btn btn-brand" type="submit">Save Profile</button>
                    <a class="btn btn-light" href="/dashboard">Back to Dashboard</a>
                </div>
            </form>
        </div>
    </div>
</section>
<?= $this->endSection() ?>
