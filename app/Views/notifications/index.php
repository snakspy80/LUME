<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<section class="card">
    <div class="stack" style="justify-content:space-between; align-items:center;">
        <div>
            <h1 class="title" style="font-size: clamp(28px, 4vw, 42px);">Notifications</h1>
            <p class="subtitle">Follow creators and stay updated on posts, reviews, and problem replies.</p>
        </div>
        <form method="post" action="/notifications/read">
            <?= csrf_field() ?>
            <button class="btn btn-light" type="submit">Mark All Read</button>
        </form>
    </div>
</section>

<section class="card">
    <?php if (empty($notifications)): ?>
        <p class="subtitle">No notifications yet.</p>
    <?php else: ?>
        <div style="display:grid; gap:10px;">
            <?php foreach ($notifications as $notification): ?>
                <article style="border:1px solid var(--line); border-radius:12px; padding:12px; background:<?= empty($notification['read_at']) ? 'rgba(42,182,115,.08)' : 'rgba(255,255,255,.03)' ?>;">
                    <div class="stack" style="justify-content:space-between; align-items:center;">
                        <div>
                            <div style="font-weight:600;"><?= esc((string) ($notification['message'] ?? 'Notification')) ?></div>
                            <div class="meta" style="margin:4px 0 0;"><?= esc((string) ($notification['created_at'] ?? '')) ?></div>
                        </div>
                        <?php if (! empty($notification['link'])): ?>
                            <a class="btn btn-light" href="<?= esc((string) $notification['link']) ?>">Open</a>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<?= $this->endSection() ?>
