<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<article class="card">
    <h1 class="title">My Learning</h1>
    <p class="subtitle">Track bookmarks and completed courses in one place.</p>
</article>

<section class="card">
    <h3 style="margin-top:0;">Bookmarked Courses</h3>
    <?php if (empty($bookmarked)): ?>
        <p class="subtitle">No bookmarks yet.</p>
    <?php else: ?>
        <?php foreach ($bookmarked as $c): ?>
            <article style="padding:10px 0; border-bottom:1px solid var(--line);">
                <h4 style="margin:0 0 4px;"><a href="/course/<?= esc((string) $c['id']) ?>" style="text-decoration:none;color:var(--ink);"><?= esc($c['title']) ?></a></h4>
                <div class="meta">Saved on <?= esc((string) $c['created_at']) ?> <?php if (! empty($c['category'])): ?>| <?= esc($c['category']) ?><?php endif; ?></div>
            </article>
        <?php endforeach; ?>
    <?php endif; ?>
</section>

<section class="card">
    <h3 style="margin-top:0;">Completed Courses</h3>
    <?php if (empty($completed)): ?>
        <p class="subtitle">No completed courses yet.</p>
    <?php else: ?>
        <?php foreach ($completed as $c): ?>
            <article style="padding:10px 0; border-bottom:1px solid var(--line);">
                <h4 style="margin:0 0 4px;"><a href="/course/<?= esc((string) $c['id']) ?>" style="text-decoration:none;color:var(--ink);"><?= esc($c['title']) ?></a></h4>
                <div class="meta">Completed on <?= esc((string) $c['completed_at']) ?> <?php if (! empty($c['category'])): ?>| <?= esc($c['category']) ?><?php endif; ?></div>
            </article>
        <?php endforeach; ?>
    <?php endif; ?>
</section>
<?= $this->endSection() ?>
