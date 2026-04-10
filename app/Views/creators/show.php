<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
    $avatarPath = (string) ($creator['avatar'] ?? '');
    $initial = strtoupper(substr((string) ($creator['name'] ?? 'U'), 0, 1));
?>

<section class="card">
    <div class="profile-shell">
        <aside style="border:1px solid var(--line); border-radius:12px; background:rgba(255,255,255,.03); padding:14px;">
            <h2 style="margin:0 0 10px; font-size:18px;">Creator Profile</h2>

            <div style="display:flex; align-items:center; gap:12px;">
                <?php if ($avatarPath !== ''): ?>
                    <img src="/<?= esc($avatarPath) ?>" alt="avatar" style="width:72px; height:72px; border-radius:999px; object-fit:cover; border:1px solid var(--line);">
                <?php else: ?>
                    <div style="width:72px; height:72px; border-radius:999px; background:rgba(42,182,115,.14); color:#bdf3d6; display:grid; place-items:center; font-size:28px; font-weight:700; border:1px solid rgba(42,182,115,.28);"><?= esc($initial) ?></div>
                <?php endif; ?>
                <div>
                    <h1 class="title" style="font-size: clamp(24px, 3vw, 34px); margin:0;"><?= esc((string) ($creator['name'] ?? 'Creator')) ?></h1>
                    <p class="meta" style="margin:4px 0 0;"><?= esc((string) ($creator['college'] ?? 'Independent')) ?></p>
                    <?php if (session()->get('user_id') && (int) session()->get('user_id') !== (int) ($creator['id'] ?? 0)): ?>
                        <form method="post" action="/creator/<?= esc((string) ($creator['id'] ?? 0)) ?>/follow" style="margin-top:10px;">
                            <?= csrf_field() ?>
                            <button class="btn <?= ! empty($isFollowing) ? 'btn-light' : 'btn-brand' ?>" type="submit"><?= ! empty($isFollowing) ? 'Following' : 'Follow Creator' ?></button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>

            <div style="margin-top:12px; display:grid; gap:6px;">
                <?php if (! empty($creator['email'])): ?><div class="meta" style="margin:0;">Email: <?= esc((string) $creator['email']) ?></div><?php endif; ?>
                <?php if (! empty($creator['phone'])): ?><div class="meta" style="margin:0;">Phone: <?= esc((string) $creator['phone']) ?></div><?php endif; ?>
                <?php if (! empty($creator['created_at'])): ?><div class="meta" style="margin:0;">Joined: <?= esc((string) $creator['created_at']) ?></div><?php endif; ?>
            </div>
        </aside>

        <div>
            <h2 style="margin-top:0;">About</h2>
            <p class="subtitle"><?= esc((string) ($creator['bio'] ?? 'This creator has not added a bio yet.')) ?></p>

            <div class="stats" style="margin-top:14px;">
                <div class="stat"><div class="n"><?= esc((string) ($stats['courses'] ?? 0)) ?></div><div class="l">Courses</div></div>
                <div class="stat"><div class="n"><?= esc((string) ($stats['views'] ?? 0)) ?></div><div class="l">Views</div></div>
                <div class="stat"><div class="n"><?= esc((string) ($stats['bookmarks'] ?? 0)) ?></div><div class="l">Bookmarks</div></div>
                <div class="stat"><div class="n"><?= esc((string) ($stats['completions'] ?? 0)) ?></div><div class="l">Completions</div></div>
                <div class="stat"><div class="n"><?= esc((string) ($stats['followers'] ?? 0)) ?></div><div class="l">Followers</div></div>
            </div>
        </div>
    </div>
</section>

<section class="card">
    <h2 style="margin-top:0;">Published Courses</h2>
    <?php if (empty($posts)): ?>
        <p class="subtitle">No published courses from this creator yet.</p>
    <?php else: ?>
        <div class="grid grid-2">
            <?php foreach ($posts as $post): ?>
                <article style="border:1px solid var(--line); border-radius:12px; padding:12px; background:rgba(255,255,255,.03);">
                    <?php if (! empty($post['video_url'])): ?>
                        <div class="video-wrap" style="margin-bottom:10px;"><iframe src="<?= esc($post['video_url']) ?>" allowfullscreen loading="lazy"></iframe></div>
                    <?php endif; ?>
                    <h3 style="margin:0 0 6px;"><a href="/course/<?= esc((string) $post['id']) ?>" style="text-decoration:none; color:var(--ink);"><?= esc((string) $post['title']) ?></a></h3>
                    <div class="meta">Views: <?= esc((string) ($post['views_count'] ?? 0)) ?> | Bookmarks: <?= esc((string) ($post['bookmarks_count'] ?? 0)) ?> | Completed: <?= esc((string) ($post['completions_count'] ?? 0)) ?></div>
                    <?php if (! empty($post['category'])): ?><span class="badge"><?= esc((string) $post['category']) ?></span><?php endif; ?>
                    <p class="subtitle" style="margin-top:8px;"><?= esc(mb_strimwidth((string) ($post['content'] ?? ''), 0, 200, '...')) ?></p>
                    <div class="stack" style="margin-top:10px;">
                        <a class="btn btn-light" href="/course/<?= esc((string) $post['id']) ?>">Read More</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<?= $this->endSection() ?>
