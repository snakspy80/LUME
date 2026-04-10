<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<section class="card">
    <h1 class="title" style="font-size: clamp(28px, 4vw, 42px);">Creator Dashboard</h1>
    <p class="subtitle">Manage your courses, videos, and publishing status.</p>

    <div class="stats" style="margin-top: 10px;">
        <div class="stat"><div class="n"><?= esc((string) ($analytics['total_courses'] ?? 0)) ?></div><div class="l">Total Courses</div></div>
        <div class="stat"><div class="n"><?= esc((string) ($analytics['published_courses'] ?? 0)) ?></div><div class="l">Published</div></div>
        <div class="stat"><div class="n"><?= esc((string) ($analytics['total_bookmarks'] ?? 0)) ?></div><div class="l">Bookmarks</div></div>
        <div class="stat"><div class="n"><?= esc((string) ($analytics['total_views'] ?? 0)) ?></div><div class="l">Views</div></div>
    </div>

    <form method="get" action="/dashboard" class="search-row" style="margin-top: 12px;">
        <input class="input" style="max-width: 280px; margin:0;" type="text" name="q" placeholder="Search title/content/category" value="<?= esc($q ?? '') ?>">
        <select name="status" style="max-width: 170px; margin:0;"><option value="">All status</option><option value="published" <?= (($status ?? '') === 'published') ? 'selected' : '' ?>>Published</option><option value="draft" <?= (($status ?? '') === 'draft') ? 'selected' : '' ?>>Draft</option></select>
        <select name="category" style="max-width: 220px; margin:0;"><option value="">All categories</option><?php foreach (($categories ?? []) as $item): ?><option value="<?= esc($item) ?>" <?= (($category ?? '') === $item) ? 'selected' : '' ?>><?= esc($item) ?></option><?php endforeach; ?></select>
        <select name="sort" style="max-width: 170px; margin:0;">
            <option value="latest" <?= (($sort ?? 'latest') === 'latest') ? 'selected' : '' ?>>Latest</option>
            <option value="oldest" <?= (($sort ?? '') === 'oldest') ? 'selected' : '' ?>>Oldest</option>
            <option value="title_asc" <?= (($sort ?? '') === 'title_asc') ? 'selected' : '' ?>>Title A-Z</option>
            <option value="title_desc" <?= (($sort ?? '') === 'title_desc') ? 'selected' : '' ?>>Title Z-A</option>
        </select>
        <input class="input" style="max-width: 160px; margin:0;" type="date" name="from" value="<?= esc($from ?? '') ?>" title="Created from">
        <input class="input" style="max-width: 160px; margin:0;" type="date" name="to" value="<?= esc($to ?? '') ?>" title="Created to">
        <select name="per_page" style="max-width: 150px; margin:0;">
            <?php foreach ([6, 12, 24, 48] as $size): ?>
                <option value="<?= $size ?>" <?= ((int) ($perPage ?? 12) === $size) ? 'selected' : '' ?>><?= $size ?> / page</option>
            <?php endforeach; ?>
        </select>
        <button class="btn btn-brand" type="submit">Apply</button>
        <a class="btn btn-light" href="/dashboard">Reset</a>
        <a class="btn btn-brand" href="/posts/create">Create New Course</a>
    </form>

    <p class="meta" style="margin-top:10px;">Total matching posts: <?= esc((string) ($total ?? 0)) ?></p>
</section>

<section class="grid grid-2">
    <?php if (empty($posts)): ?>
        <article class="card"><p class="subtitle">No courses found for this filter.</p></article>
    <?php else: foreach ($posts as $post): ?>
        <article class="card">
            <h3 class="course-title"><?= esc($post['title']) ?></h3>
            <div class="meta">Status: <?= (int) ($post['is_published'] ?? 0) === 1 ? 'Published' : 'Draft' ?></div>
            <?php if (! empty($post['category'])): ?><span class="badge"><?= esc($post['category']) ?></span><?php endif; ?>
            <p class="subtitle" style="margin-top: 8px;"><?= esc(mb_strimwidth($post['content'] ?? '', 0, 140, '...')) ?></p>
            <div class="stack" style="margin-top: 12px;">
                <?php if ((int) ($post['is_published'] ?? 0) === 1): ?><a class="btn btn-light" href="/course/<?= esc((string) $post['id']) ?>">View</a><?php endif; ?>
                <a class="btn btn-light" href="/posts/edit/<?= esc((string) $post['id']) ?>">Edit</a>
                <form action="/posts/delete/<?= esc((string) $post['id']) ?>" method="post" style="display:inline;" onsubmit="return confirm('Delete this course?');"><?= csrf_field() ?><button class="btn btn-danger" type="submit">Delete</button></form>
            </div>
        </article>
    <?php endforeach; endif; ?>
</section>

<section class="card">
    <h2 style="margin-top:0;">Learner Problems Inbox</h2>
    <p class="subtitle">Only problem-type comments are shown here. Reply with solutions directly.</p>

    <div style="margin-top:10px; display:grid; gap:10px;">
        <?php if (empty($problemRows ?? [])): ?>
            <p class="subtitle">No learner problems yet.</p>
        <?php else: ?>
            <?php foreach (($problemRows ?? []) as $problem): ?>
                <?php $pid = (int) ($problem['id'] ?? 0); $solution = ($solutionMap[$pid] ?? null); ?>
                <article style="border:1px solid var(--line); border-radius:12px; background:rgba(255,255,255,.03); padding:10px;">
                    <div class="meta" style="margin:0 0 6px;">
                        <strong><?= esc($problem['learner_name'] ?? 'Learner') ?></strong> on
                        <a href="/course/<?= esc((string) ($problem['post_id'] ?? 0)) ?>"><?= esc($problem['course_title'] ?? 'Course') ?></a>
                    </div>
                    <p class="subtitle" style="white-space:pre-wrap;"><?= esc((string) ($problem['content'] ?? '')) ?></p>

                    <?php if ($solution): ?>
                        <div style="margin-top:8px; border:1px solid rgba(42,182,115,.25); background:rgba(42,182,115,.08); border-radius:10px; padding:8px;">
                            <div class="meta" style="margin:0 0 4px;"><strong>Your solution</strong></div>
                            <p class="subtitle" style="margin:0; white-space:pre-wrap;"><?= esc((string) ($solution['content'] ?? '')) ?></p>
                        </div>
                    <?php endif; ?>

                    <form method="post" action="/dashboard/problems/<?= esc((string) $pid) ?>/reply" style="margin-top:8px;">
                        <?= csrf_field() ?>
                        <textarea name="content" rows="3" placeholder="<?= $solution ? 'Update your solution...' : 'Write solution for this learner...' ?>" required></textarea>
                        <button class="btn btn-brand" type="submit"><?= $solution ? 'Update Solution' : 'Post Solution' ?></button>
                    </form>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<?php if (! empty($isFounderAdmin)): ?>
<section class="card">
    <h2 style="margin-top:0;">Users & Emails</h2>
    <p class="subtitle">Founder-only account overview.</p>

    <div class="stats" style="margin-top:10px;">
        <div class="stat"><div class="n"><?= esc((string) ($userOverview['total_users'] ?? 0)) ?></div><div class="l">Total Users</div></div>
        <div class="stat"><div class="n"><?= esc((string) ($userOverview['verified_users'] ?? 0)) ?></div><div class="l">Verified Users</div></div>
        <div class="stat"><div class="n"><?= esc((string) ($userOverview['creators_with_posts'] ?? 0)) ?></div><div class="l">Creators With Posts</div></div>
        <div class="stat"><div class="n"><?= esc((string) count($allUsers ?? [])) ?></div><div class="l">Accounts Listed</div></div>
    </div>

    <div style="margin-top:12px; display:grid; gap:10px;">
        <?php foreach (($allUsers ?? []) as $account): ?>
            <?php
                $avatar = (string) ($account['avatar'] ?? '');
                $initial = strtoupper(substr((string) ($account['name'] ?? 'U'), 0, 1));
            ?>
            <article style="display:grid; grid-template-columns:minmax(0, 1.4fr) repeat(4, auto); gap:14px; align-items:center; border:1px solid var(--line); border-radius:12px; background:rgba(255,255,255,.03); padding:12px;">
                <div style="display:flex; align-items:center; gap:10px; min-width:0;">
                    <?php if ($avatar !== ''): ?>
                        <img src="/<?= esc($avatar) ?>" alt="avatar" style="width:46px; height:46px; border-radius:999px; object-fit:cover; border:1px solid var(--line);">
                    <?php else: ?>
                        <div style="width:46px; height:46px; border-radius:999px; background:rgba(42,182,115,.14); color:#bdf3d6; display:grid; place-items:center; font-size:18px; font-weight:700; border:1px solid rgba(42,182,115,.28);"><?= esc($initial) ?></div>
                    <?php endif; ?>
                    <div style="min-width:0;">
                        <div style="font-weight:700;"><?= esc((string) ($account['name'] ?? 'User')) ?></div>
                        <div class="meta" style="margin:2px 0 0;"><?= esc((string) ($account['email'] ?? '')) ?></div>
                        <div class="meta" style="margin:2px 0 0;">
                            <?= esc((string) ($account['college'] ?: 'Independent')) ?>
                            <?php if (! empty($account['phone'])): ?> | <?= esc((string) $account['phone']) ?><?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="meta" style="margin:0; text-align:right;">Joined<br><strong style="color:var(--ink);"><?= esc((string) ($account['created_at'] ?? '-')) ?></strong></div>
                <div class="meta" style="margin:0; text-align:right;">Verified<br><strong style="color:var(--ink);"><?= ! empty($account['email_verified_at']) ? 'Yes' : 'No' ?></strong></div>
                <div class="meta" style="margin:0; text-align:right;">Posts<br><strong style="color:var(--ink);"><?= esc((string) ($account['total_posts'] ?? 0)) ?></strong></div>
                <div class="meta" style="margin:0; text-align:right;">Published<br><strong style="color:var(--ink);"><?= esc((string) ($account['published_posts'] ?? 0)) ?></strong></div>
            </article>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<?php if (($totalPages ?? 1) > 1): ?>
<?php
    $query = [
        'q' => $q ?? '',
        'status' => $status ?? '',
        'category' => $category ?? '',
        'sort' => $sort ?? 'latest',
        'from' => $from ?? '',
        'to' => $to ?? '',
        'per_page' => $perPage ?? 12,
    ];
    $currentPage = (int) ($page ?? 1);
    $lastPage = (int) ($totalPages ?? 1);
    $start = max(1, $currentPage - 2);
    $end = min($lastPage, $currentPage + 2);
?>
<section class="card">
    <div class="stack" style="justify-content:space-between; align-items:center;">
        <div class="stack">
            <?php if ($currentPage > 1): ?>
                <?php $query['page'] = $currentPage - 1; ?>
                <a class="btn btn-light" href="/dashboard?<?= esc(http_build_query($query)) ?>">Prev</a>
            <?php endif; ?>

            <?php if ($start > 1): ?>
                <?php $query['page'] = 1; ?>
                <a class="btn <?= $currentPage === 1 ? 'btn-brand' : 'btn-light' ?>" href="/dashboard?<?= esc(http_build_query($query)) ?>">1</a>
                <?php if ($start > 2): ?><span class="meta" style="align-self:center;">...</span><?php endif; ?>
            <?php endif; ?>

            <?php for ($i = $start; $i <= $end; $i++): ?>
                <?php $query['page'] = $i; ?>
                <a class="btn <?= $currentPage === $i ? 'btn-brand' : 'btn-light' ?>" href="/dashboard?<?= esc(http_build_query($query)) ?>"><?= esc((string) $i) ?></a>
            <?php endfor; ?>

            <?php if ($end < $lastPage): ?>
                <?php if ($end < $lastPage - 1): ?><span class="meta" style="align-self:center;">...</span><?php endif; ?>
                <?php $query['page'] = $lastPage; ?>
                <a class="btn <?= $currentPage === $lastPage ? 'btn-brand' : 'btn-light' ?>" href="/dashboard?<?= esc(http_build_query($query)) ?>"><?= esc((string) $lastPage) ?></a>
            <?php endif; ?>

            <?php if ($currentPage < $lastPage): ?>
                <?php $query['page'] = $currentPage + 1; ?>
                <a class="btn btn-light" href="/dashboard?<?= esc(http_build_query($query)) ?>">Next</a>
            <?php endif; ?>
        </div>
        <div class="meta" style="margin:0;">Page <?= esc((string) $currentPage) ?> of <?= esc((string) $lastPage) ?></div>
    </div>
</section>
<?php endif; ?>
<?= $this->endSection() ?>
