<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php $assetUrl = static fn(string $path): string => base_url(ltrim($path, '/')); ?>
<article class="card">
    <h1 class="title" style="font-size: clamp(28px, 4vw, 46px);"><?= esc($course['title']) ?></h1>
    <div class="meta">Instructor: <?= esc($course['author_name'] ?? 'Unknown') ?> | College: <?= esc($course['author_college'] ?? 'Independent') ?></div>
    <div class="meta">Views: <?= esc((string) ($course['views_count'] ?? 0)) ?> | Bookmarks: <?= esc((string) ($course['bookmarks_count'] ?? 0)) ?> | Completed: <?= esc((string) ($course['completions_count'] ?? 0)) ?></div>
    <?php if (! empty($course['category'])): ?><span class="badge"><?= esc($course['category']) ?></span><?php endif; ?>
    <?php if (session()->get('user_id')): ?>
    <div class="stack" style="margin-top:10px;">
        <form action="/course/<?= esc((string) $course['id']) ?>/bookmark" method="post"><?= csrf_field() ?><button class="btn btn-light" type="submit"><?= !empty($course['is_bookmarked']) ? 'Remove Bookmark' : 'Bookmark Course' ?></button></form>
        <form action="/course/<?= esc((string) $course['id']) ?>/progress" method="post"><?= csrf_field() ?><button class="btn btn-brand" type="submit"><?= !empty($course['is_completed']) ? 'Mark Incomplete' : 'Mark Completed' ?></button></form>
    </div>
    <?php endif; ?>
</article>
<?php if (! empty($course['video_file']) || ! empty($course['video_url'])): ?>
<section class="card">
    <h2 style="margin-top:0;">Course Video</h2>
    <?php if (! empty($course['video_file'])): ?>
        <video controls style="width:100%; border:1px solid var(--line); border-radius:10px; background:#000;">
            <source src="<?= esc($assetUrl((string) $course['video_file'])) ?>">
            Your browser does not support the video tag.
        </video>
    <?php elseif (! empty($course['video_url'])): ?>
        <div class="video-wrap" style="padding-top: 50%;"><iframe src="<?= esc($course['video_url']) ?>" allowfullscreen loading="lazy"></iframe></div>
    <?php endif; ?>
</section>
<?php endif; ?>
<section class="card"><h2 style="margin-top:0;">Course Content</h2><p class="subtitle" style="white-space: pre-wrap;"><?= esc($course['content']) ?></p></section>
<?php if (! empty($assets ?? [])): ?>
<section class="card">
    <h3 style="margin-top:0;">Notes & Resources</h3>
    <div style="display:grid; gap:8px;">
        <?php foreach (($assets ?? []) as $asset): ?>
            <?php
                $path = (string) ($asset['file_path'] ?? '');
                $url = $assetUrl($path);
                $mime = strtolower((string) ($asset['mime_type'] ?? ''));
                $isImage = str_starts_with($mime, 'image/');
                $isPdf = $mime === 'application/pdf' || str_ends_with(strtolower((string) ($asset['file_name'] ?? '')), '.pdf');
            ?>
            <div style="border:1px solid var(--line); border-radius:10px; padding:10px; background:rgba(255,255,255,.03);">
                <?php if ($isImage): ?>
                    <a href="<?= esc($url) ?>" class="js-inline-image" data-image-src="<?= esc($url) ?>" data-image-name="<?= esc((string) ($asset['file_name'] ?? 'Image')) ?>" style="display:block; max-width:520px;">
                        <img src="<?= esc($url) ?>" alt="<?= esc((string) ($asset['file_name'] ?? 'Image')) ?>" style="display:block; width:100%; max-height:260px; object-fit:cover; border-radius:8px; border:1px solid var(--line); background:rgba(255,255,255,.04);">
                    </a>
                <?php elseif ($isPdf): ?>
                    <button type="button" class="js-inline-pdf" data-pdf-src="<?= esc($url) ?>" data-pdf-name="<?= esc((string) ($asset['file_name'] ?? 'PDF')) ?>" style="display:flex; align-items:center; justify-content:space-between; gap:8px; width:100%; max-width:520px; border:1px solid var(--line); border-radius:8px; background:rgba(255,255,255,.04); color:var(--ink); padding:10px; cursor:pointer;">
                        <span style="text-align:left;"><?= esc((string) ($asset['file_name'] ?? 'PDF file')) ?></span>
                        <span class="badge">Open PDF</span>
                    </button>
                <?php endif; ?>
                <div style="display:flex; align-items:center; justify-content:space-between; gap:8px; margin-top:<?= $isImage ? '10px' : '0' ?>;">
                    <span><?= esc((string) ($asset['file_name'] ?? 'Resource')) ?></span>
                    <a class="badge" href="<?= esc($url) ?>" download="<?= esc((string) ($asset['file_name'] ?? 'resource')) ?>" style="text-decoration:none;"><?= esc((string) ($asset['asset_kind'] ?? 'note')) ?> | Download</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>
<div id="imageLightbox" style="position:fixed; inset:0; background:rgba(8,10,14,.82); display:none; align-items:center; justify-content:center; z-index:9999; padding:16px;">
    <button type="button" id="imageLightboxClose" aria-label="Close image" style="position:absolute; top:16px; right:16px; border:0; border-radius:999px; width:38px; height:38px; background:rgba(255,255,255,.08); color:#fff; font-size:22px; line-height:1; cursor:pointer;">&times;</button>
    <figure style="margin:0; max-width:min(920px, 94vw); max-height:88vh; display:flex; flex-direction:column; align-items:center; gap:10px;">
        <img id="imageLightboxPreview" src="" alt="" style="max-width:100%; max-height:72vh; object-fit:contain; border-radius:12px; box-shadow:0 20px 50px rgba(0,0,0,.35);">
        <figcaption id="imageLightboxCaption" style="color:#f8f9fb; font-size:14px;"></figcaption>
    </figure>
</div>
<div id="pdfLightbox" style="position:fixed; inset:0; background:rgba(8,10,14,.82); display:none; align-items:center; justify-content:center; z-index:9999; padding:16px;">
    <button type="button" id="pdfLightboxClose" aria-label="Close PDF" style="position:absolute; top:16px; right:16px; border:0; border-radius:999px; width:38px; height:38px; background:rgba(255,255,255,.08); color:#fff; font-size:22px; line-height:1; cursor:pointer;">&times;</button>
    <div style="width:min(980px, 95vw); height:84vh; background:#0b1210; border-radius:12px; overflow:hidden; box-shadow:0 20px 50px rgba(0,0,0,.35);">
        <div id="pdfLightboxCaption" style="padding:10px 12px; font-size:14px; border-bottom:1px solid var(--line); color:var(--ink);"></div>
        <iframe id="pdfLightboxFrame" src="" title="PDF Preview" style="width:100%; height:calc(84vh - 44px); border:0;"></iframe>
    </div>
</div>
<section class="card"><h3 style="margin-top:0;">About The Instructor</h3><p class="subtitle"><?= esc($course['author_bio'] ?? 'No bio added yet.') ?></p><div style="margin-top:10px;"><a class="btn btn-light" href="/search">Explore More Courses</a></div></section>
<section class="card">
    <h3 style="margin-top:0;">Questions & Reviews</h3>
    <p class="subtitle">Learners can ask problems or leave course reviews.</p>

    <?php $errors = session()->getFlashdata('errors') ?? []; ?>
    <?php if (session()->get('user_id')): ?>
        <form method="post" action="/course/<?= esc((string) $course['id']) ?>/comments" style="margin-top:10px;">
            <?= csrf_field() ?>
            <div class="search-row">
                <select name="type" style="max-width:180px; margin:0;">
                    <option value="problem" <?= old('type') === 'problem' ? 'selected' : '' ?>>Problem</option>
                    <option value="review" <?= old('type') === 'review' ? 'selected' : '' ?>>Review</option>
                </select>
            </div>
            <textarea name="content" rows="4" placeholder="Share your problem or review..." required><?= esc(old('content')) ?></textarea>
            <?php if (isset($errors['type'])): ?><p class="field-error"><?= esc($errors['type']) ?></p><?php endif; ?>
            <?php if (isset($errors['content'])): ?><p class="field-error"><?= esc($errors['content']) ?></p><?php endif; ?>
            <button class="btn btn-brand" type="submit">Post Comment</button>
        </form>
    <?php else: ?>
        <p class="meta" style="margin-top:10px;">Please <a href="/login">login</a> to post a question or review.</p>
    <?php endif; ?>

    <div style="margin-top:14px; display:grid; gap:10px;">
        <?php if (empty($comments ?? [])): ?>
            <p class="subtitle">No comments yet. Be the first to ask or review.</p>
        <?php else: ?>
            <?php foreach (($comments ?? []) as $comment): ?>
                <article style="border:1px solid var(--line); border-radius:12px; padding:10px; background:rgba(255,255,255,.03);">
                    <div class="meta" style="margin-bottom:4px;">
                        <strong><?= esc($comment['user_name'] ?? 'Learner') ?></strong>
                        <span class="badge" style="margin-left:6px;"><?= esc(ucfirst((string) ($comment['type'] ?? 'review'))) ?></span>
                    </div>
                    <p class="subtitle" style="white-space:pre-wrap;"><?= esc((string) ($comment['content'] ?? '')) ?></p>
                    <?php $replies = ($repliesByParent[(int) ($comment['id'] ?? 0)] ?? []); ?>
                    <?php if (! empty($replies)): ?>
                        <div style="margin-top:8px; padding:8px; border-radius:10px; background:rgba(42,182,115,.08); border:1px solid rgba(42,182,115,.25);">
                            <?php foreach ($replies as $reply): ?>
                                <div class="meta" style="margin:0 0 4px;"><strong><?= esc($reply['user_name'] ?? 'Instructor') ?></strong> replied</div>
                                <p class="subtitle" style="margin:0 0 6px; white-space:pre-wrap;"><?= esc((string) ($reply['content'] ?? '')) ?></p>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>
<?php if (! empty($related)): ?><section class="card"><h3 style="margin-top:0;">Related Courses</h3><div class="stack"><?php foreach ($related as $r): ?><a class="btn btn-light" href="/course/<?= esc((string) $r['id']) ?>"><?= esc($r['title']) ?></a><?php endforeach; ?></div></section><?php endif; ?>
<script>
(() => {
    const modal = document.getElementById('imageLightbox');
    const closeBtn = document.getElementById('imageLightboxClose');
    const preview = document.getElementById('imageLightboxPreview');
    const caption = document.getElementById('imageLightboxCaption');
    const triggers = document.querySelectorAll('.js-inline-image');
    const pdfModal = document.getElementById('pdfLightbox');
    const pdfCloseBtn = document.getElementById('pdfLightboxClose');
    const pdfFrame = document.getElementById('pdfLightboxFrame');
    const pdfCaption = document.getElementById('pdfLightboxCaption');
    const pdfTriggers = document.querySelectorAll('.js-inline-pdf');
    if ((!modal || !closeBtn || !preview || !caption) && (!pdfModal || !pdfCloseBtn || !pdfFrame || !pdfCaption)) return;

    const closeImage = () => {
        if (!modal || !preview || !caption) return;
        modal.style.display = 'none';
        preview.src = '';
        preview.alt = '';
        caption.textContent = '';
        document.body.style.overflow = '';
    };

    const openImage = (src, name) => {
        if (!modal || !preview || !caption) return;
        preview.src = src;
        preview.alt = name || 'Image';
        caption.textContent = name || '';
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    };

    const closePdf = () => {
        if (!pdfModal || !pdfFrame || !pdfCaption) return;
        pdfModal.style.display = 'none';
        pdfFrame.src = '';
        pdfCaption.textContent = '';
        document.body.style.overflow = '';
    };

    const openPdf = (src, name) => {
        if (!pdfModal || !pdfFrame || !pdfCaption) return;
        pdfFrame.src = src;
        pdfCaption.textContent = name || 'PDF Preview';
        pdfModal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    };

    triggers.forEach((el) => {
        el.addEventListener('click', (event) => {
            event.preventDefault();
            openImage(el.dataset.imageSrc || '', el.dataset.imageName || '');
        });
    });

    pdfTriggers.forEach((el) => {
        el.addEventListener('click', (event) => {
            event.preventDefault();
            openPdf(el.dataset.pdfSrc || '', el.dataset.pdfName || '');
        });
    });

    closeBtn?.addEventListener('click', closeImage);
    modal?.addEventListener('click', (event) => {
        if (event.target === modal) closeImage();
    });
    pdfCloseBtn?.addEventListener('click', closePdf);
    pdfModal?.addEventListener('click', (event) => {
        if (event.target === pdfModal) closePdf();
    });

    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape') return;
        if (modal && modal.style.display === 'flex') closeImage();
        if (pdfModal && pdfModal.style.display === 'flex') closePdf();
    });
})();
</script>
<?= $this->endSection() ?>
