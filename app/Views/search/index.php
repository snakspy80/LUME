<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<article class="card hero-card">
    <h1 class="title">Search</h1>
    <p class="subtitle">Find creators and courses instantly.</p>
    <form method="get" action="/search" class="search-row" style="margin-top:12px;">
        <input class="input" style="max-width:360px; margin:0;" type="text" name="q" placeholder="Search by creator, topic, course..." value="<?= esc($q ?? '') ?>">
        <button class="btn btn-brand" type="submit">Search</button>
    </form>
</article>

<?php if (($q ?? '') === ''): ?>
    <article class="card"><p class="subtitle">Type a keyword to discover creators and courses.</p></article>
<?php else: ?>
    <section class="card">
        <h3 style="margin-top:0;">Creators (<?= esc((string) count($users ?? [])) ?>)</h3>
        <?php if (empty($users)): ?>
            <p class="subtitle">No creators found.</p>
        <?php else: ?>
            <div class="stack">
                <?php foreach ($users as $user): ?>
                    <a href="/creator/<?= esc((string) $user['id']) ?>" style="display:flex; align-items:center; gap:10px; border:1px solid var(--line); border-radius:12px; padding:8px 10px; background:rgba(255,255,255,.03); min-width:260px; text-decoration:none;">
                        <?php if (! empty($user['avatar'])): ?>
                            <img src="/<?= esc($user['avatar']) ?>" alt="avatar" style="width:42px; height:42px; border-radius:999px; object-fit:cover; border:1px solid var(--line);">
                        <?php else: ?>
                            <div style="width:42px; height:42px; border-radius:999px; background:rgba(255,255,255,.06); display:flex; align-items:center; justify-content:center; font-weight:700;"><?= esc(strtoupper(substr((string) $user['name'], 0, 1))) ?></div>
                        <?php endif; ?>
                        <div>
                            <div style="font-weight:600;"><?= esc($user['name']) ?></div>
                            <div class="meta" style="margin:0;"><?= esc($user['college'] ?: 'Independent') ?></div>
                            <div class="meta" style="margin:2px 0 0;">View creator profile</div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <section class="card">
        <h3 style="margin-top:0;">Courses (<?= esc((string) count($courses ?? [])) ?>)</h3>
        <?php if (empty($courses)): ?>
            <p class="subtitle">No courses found.</p>
        <?php else: ?>
            <div class="grid" style="grid-template-columns:repeat(auto-fill,minmax(260px,1fr)); gap:10px;">
                <?php foreach ($courses as $course): ?>
                    <article style="border:1px solid var(--line); border-radius:12px; padding:10px; background:rgba(255,255,255,.03);">
                        <?php if (! empty($course['video_url'])): ?>
                            <div class="video-wrap" style="margin-bottom:8px;"><iframe src="<?= esc($course['video_url']) ?>" allowfullscreen loading="lazy"></iframe></div>
                        <?php endif; ?>
                        <h4 style="margin:0 0 4px;"><a href="/course/<?= esc((string) $course['id']) ?>" style="text-decoration:none; color:var(--ink);"><?= esc($course['title']) ?></a></h4>
                        <div class="meta">by <?= esc($course['author_name'] ?? 'Unknown') ?> | <?= esc((string) ($course['views_count'] ?? 0)) ?> views</div>
                        <?php if (! empty($course['category'])): ?><span class="badge"><?= esc($course['category']) ?></span><?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
<?php endif; ?>

<?= $this->endSection() ?>
