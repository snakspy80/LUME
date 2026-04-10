<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<section class="card">
    <h1 class="title" style="font-size: clamp(28px, 4vw, 42px);">Creator Leaderboard</h1>
    <p class="subtitle">See who is contributing the most to Lume. Ranking is based on published courses, completions, bookmarks, and views.</p>
</section>

<section class="card">
    <?php if (empty($leaders)): ?>
        <p class="subtitle">No contributors have published courses yet.</p>
    <?php else: ?>
        <div style="display:grid; gap:12px;">
            <?php foreach ($leaders as $leader): ?>
                <?php
                    $avatar = (string) ($leader['avatar'] ?? '');
                    $initial = strtoupper(substr((string) ($leader['name'] ?? 'U'), 0, 1));
                    $rank = (int) ($leader['rank'] ?? 0);
                ?>
                <article style="display:grid; grid-template-columns:auto minmax(0, 1fr) auto; gap:14px; align-items:center; border:1px solid var(--line); border-radius:14px; padding:14px; background:rgba(255,255,255,.03);">
                    <div style="display:grid; place-items:center; width:52px; height:52px; border-radius:14px; background:<?= $rank <= 3 ? 'linear-gradient(135deg, rgba(255,201,71,.22), rgba(42,182,115,.18))' : 'rgba(255,255,255,.04)' ?>; border:1px solid rgba(255,255,255,.08); font-weight:700; font-size:18px;">
                        #<?= esc((string) $rank) ?>
                    </div>

                    <div style="display:flex; align-items:center; gap:12px; min-width:0;">
                        <a href="/creator/<?= esc((string) $leader['id']) ?>" style="text-decoration:none;">
                            <?php if ($avatar !== ''): ?>
                                <img src="/<?= esc($avatar) ?>" alt="avatar" style="width:56px; height:56px; border-radius:999px; object-fit:cover; border:1px solid var(--line);">
                            <?php else: ?>
                                <div style="width:56px; height:56px; border-radius:999px; background:rgba(42,182,115,.14); color:#bdf3d6; display:grid; place-items:center; font-size:22px; font-weight:700; border:1px solid rgba(42,182,115,.28);"><?= esc($initial) ?></div>
                            <?php endif; ?>
                        </a>
                        <div style="min-width:0;">
                            <h3 style="margin:0 0 4px;"><a href="/creator/<?= esc((string) $leader['id']) ?>" style="text-decoration:none; color:var(--ink);"><?= esc((string) ($leader['name'] ?? 'Creator')) ?></a></h3>
                            <div class="meta" style="margin:0;"><?= esc((string) ($leader['college'] ?: 'Independent')) ?></div>
                            <?php if (! empty($leader['bio'])): ?>
                                <p class="subtitle" style="margin-top:6px;"><?= esc(mb_strimwidth((string) $leader['bio'], 0, 140, '...')) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div style="display:grid; gap:6px; justify-items:end; text-align:right;">
                        <div class="meta" style="margin:0;">Courses: <?= esc((string) ($leader['published_courses'] ?? 0)) ?></div>
                        <div class="meta" style="margin:0;">Views: <?= esc((string) ($leader['total_views'] ?? 0)) ?></div>
                        <div class="meta" style="margin:0;">Bookmarks: <?= esc((string) ($leader['total_bookmarks'] ?? 0)) ?></div>
                        <div class="meta" style="margin:0;">Completions: <?= esc((string) ($leader['total_completions'] ?? 0)) ?></div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<?= $this->endSection() ?>
