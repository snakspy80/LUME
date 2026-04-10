<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<article class="card hero-card">
    <h1 class="title">Learn Beautifully. Build Boldly.</h1>
    <p class="subtitle">A creative learning studio where tutorials feel alive with video lessons, trends, and progress tracking.</p>
    <form method="get" action="/" class="search-row" style="margin-top: 18px; padding:14px; border:1px solid rgba(255,255,255,.08); border-radius:18px; background:rgba(255,255,255,.04); backdrop-filter:blur(8px); box-shadow:0 14px 30px rgba(0,0,0,.2);">
        <input class="input" style="max-width: 320px; margin:0;" type="text" name="q" placeholder="Search tutorials, creators, topics" value="<?= esc($q ?? '') ?>">
        <select name="category" style="max-width: 220px; margin:0;"><option value="">All categories</option><?php foreach (($categories ?? []) as $item): ?><option value="<?= esc($item) ?>" <?= (($category ?? '') === $item) ? 'selected' : '' ?>><?= esc($item) ?></option><?php endforeach; ?></select>
        <select name="sort" style="max-width: 160px; margin:0;"><option value="latest" <?= (($sort ?? 'latest') === 'latest') ? 'selected' : '' ?>>Latest</option><option value="popular" <?= (($sort ?? 'latest') === 'popular') ? 'selected' : '' ?>>Popular</option></select>
        <button class="btn btn-brand" type="submit">Apply</button>
        <a class="btn btn-light" href="/">Reset</a>
    </form>
    <p class="meta" style="margin-top:8px;">Showing <?= esc((string) ($total ?? 0)) ?> tutorials</p>
</article>

<?php if (empty($posts)): ?>
<article class="card"><p class="subtitle">No tutorials found.</p></article>
<?php else: ?>
<section class="grid grid-2">
    <?php foreach ($posts as $post): ?>
    <article class="card course-card">
        <?php if (! empty($post['video_url'])): ?><div class="video-wrap"><iframe src="<?= esc($post['video_url']) ?>" allowfullscreen loading="lazy"></iframe></div><?php endif; ?>
        <h3><a href="/course/<?= esc((string) $post['id']) ?>"><?= esc($post['title']) ?></a></h3>
        <div class="meta">By <?= esc($post['author_name'] ?? 'Unknown') ?> | <?= esc($post['author_college'] ?? 'Independent') ?></div>
        <div class="meta">Views: <?= esc((string) ($post['views_count'] ?? 0)) ?> | Bookmarks: <?= esc((string) ($post['bookmarks_count'] ?? 0)) ?> | Completed: <?= esc((string) ($post['completions_count'] ?? 0)) ?></div>
        <?php if (! empty($post['category'])): ?><span class="badge"><?= esc($post['category']) ?></span><?php endif; ?>
        <p class="subtitle" style="margin-top:8px;"><?= esc(mb_strimwidth($post['content'] ?? '', 0, 220, '...')) ?></p>
        <div class="stack" style="margin-top:10px;">
            <a class="btn btn-light" href="/course/<?= esc((string) $post['id']) ?>">Read More</a>
            <?php if (session()->get('user_id')): ?>
            <form action="/course/<?= esc((string) $post['id']) ?>/bookmark" method="post" style="display:inline;"><?= csrf_field() ?><button class="btn btn-light" type="submit"><?= !empty($post['is_bookmarked']) ? 'Bookmarked' : 'Bookmark' ?></button></form>
            <?php endif; ?>
        </div>
    </article>
    <?php endforeach; ?>
</section>
<?php endif; ?>

<?php if (($totalPages ?? 1) > 1): $query = ['q' => $q ?? '', 'category' => $category ?? '', 'sort' => $sort ?? 'latest']; ?>
<article class="card"><div class="stack"><?php for ($i = 1; $i <= (int) $totalPages; $i++): $query['page'] = $i; ?><a class="btn <?= ((int) ($page ?? 1) === $i) ? 'btn-brand' : 'btn-light' ?>" href="/?<?= esc(http_build_query($query)) ?>"><?= esc((string) $i) ?></a><?php endfor; ?></div></article>
<?php endif; ?>
<?= $this->endSection() ?>
