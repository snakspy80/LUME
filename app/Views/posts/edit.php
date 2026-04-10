<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php $assetUrl = static fn(string $path): string => base_url(ltrim($path, '/')); ?>
<section class="card" style="max-width:820px; margin:0 auto;">
    <h2 style="margin-top:0;">Edit Course</h2>
    <p class="meta">Server upload limits: upload_max_filesize=<?= esc((string) ini_get('upload_max_filesize')) ?>, post_max_size=<?= esc((string) ini_get('post_max_size')) ?>.</p>

    <?php $errors = session()->getFlashdata('errors') ?? []; ?>

    <form method="post" action="/posts/update/<?= esc((string) $post['id']) ?>" enctype="multipart/form-data">
        <?= csrf_field() ?>

        <label class="label" for="title">Course Title</label>
        <input class="input" type="text" id="title" name="title" value="<?= esc(old('title') ?: $post['title']) ?>" required>
        <?php if (isset($errors['title'])): ?><p class="field-error"><?= esc($errors['title']) ?></p><?php endif; ?>

        <label class="label" for="category">Category</label>
        <input class="input" type="text" id="category" name="category" value="<?= esc(old('category') ?: ($post['category'] ?? '')) ?>">
        <?php if (isset($errors['category'])): ?><p class="field-error"><?= esc($errors['category']) ?></p><?php endif; ?>

        <label class="label" for="video_url">Video Embed URL (https)</label>
        <input class="input" type="url" id="video_url" name="video_url" value="<?= esc(old('video_url') ?: ($post['video_url'] ?? '')) ?>">
        <?php if (isset($errors['video_url'])): ?><p class="field-error"><?= esc($errors['video_url']) ?></p><?php endif; ?>

        <label class="label" for="video_file">Upload/Replace Your Video (max 200MB)</label>
        <?php if (! empty($post['video_file'])): ?>
            <video controls style="width:100%; border:1px solid var(--line); border-radius:10px; margin-bottom:8px;">
                <source src="<?= esc($assetUrl((string) $post['video_file'])) ?>">
            </video>
            <label style="display:flex; align-items:center; gap:8px; font-size:14px; margin:2px 0 10px;">
                <input type="checkbox" name="remove_video_file" value="1">
                Remove current uploaded video
            </label>
        <?php endif; ?>
        <input class="input" type="file" id="video_file" name="video_file" accept="video/mp4,video/webm,video/ogg,video/quicktime">

        <label class="label" for="note_files">Add More Notes & Resources</label>
        <input class="input" type="file" id="note_files" name="note_files[]" multiple accept=".pdf,.png,.jpg,.jpeg,.webp,.gif,.svg,.txt,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.csv,.zip,.mp3,.wav,.mp4,.webm,image/*,audio/*,video/*">
        <p class="meta" style="margin-top:-4px;">PDF max 150MB, other files max 50MB each.</p>

        <?php if (! empty($assets ?? [])): ?>
            <div style="margin-bottom:8px;">
                <label class="label">Existing Notes/Resources</label>
                <div style="display:grid; gap:8px;">
                    <?php foreach (($assets ?? []) as $asset): ?>
                        <label style="display:flex; align-items:center; gap:8px; border:1px solid var(--line); border-radius:10px; padding:8px; background:rgba(255,255,255,.03);">
                            <input type="checkbox" name="delete_asset_ids[]" value="<?= esc((string) ($asset['id'] ?? '')) ?>">
                            <a href="<?= esc($assetUrl((string) ($asset['file_path'] ?? ''))) ?>" target="_blank" rel="noopener"><?= esc((string) ($asset['file_name'] ?? 'Asset')) ?></a>
                            <span class="meta" style="margin:0;">(tick to delete)</span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="stack" style="justify-content:space-between; align-items:center;">
            <label class="label" for="content" style="margin:0;">Course Content</label>
            <button class="btn btn-light" type="button" id="pasteContentBtn">Paste from Clipboard</button>
        </div>
        <textarea id="content" name="content" rows="10" required><?= esc(old('content') ?: $post['content']) ?></textarea>
        <?php if (isset($errors['content'])): ?><p class="field-error"><?= esc($errors['content']) ?></p><?php endif; ?>
        <p class="meta" id="pasteStatus" style="margin-top:-4px;">Tip: You can also use right-click paste or Shift+Insert.</p>

        <label style="display:flex; align-items:center; gap:8px; margin:8px 0 14px;">
            <?php $oldPublished = old('is_published', null); ?>
            <input type="checkbox" name="is_published" value="1" <?= ($oldPublished !== null ? (bool) $oldPublished : ((int) ($post['is_published'] ?? 0) === 1)) ? 'checked' : '' ?>>
            Publish on home feed
        </label>

        <div class="stack">
            <button class="btn btn-brand" type="submit">Update Course</button>
            <a class="btn btn-light" href="/dashboard">Cancel</a>
        </div>
    </form>
</section>
<script>
(() => {
    const btn = document.getElementById('pasteContentBtn');
    const field = document.getElementById('content');
    const status = document.getElementById('pasteStatus');
    if (!btn || !field || !status) return;

    btn.addEventListener('click', async () => {
        if (!navigator.clipboard || !navigator.clipboard.readText) {
            status.textContent = 'Clipboard API is not available in this browser. Use right-click paste.';
            return;
        }

        try {
            const text = await navigator.clipboard.readText();
            const start = field.selectionStart ?? field.value.length;
            const end = field.selectionEnd ?? field.value.length;
            field.value = field.value.slice(0, start) + text + field.value.slice(end);
            const caret = start + text.length;
            field.setSelectionRange(caret, caret);
            field.focus();
            status.textContent = 'Pasted into Course Content.';
        } catch (e) {
            status.textContent = 'Clipboard permission blocked. Please allow clipboard access or use right-click paste.';
        }
    });
})();
</script>
<?= $this->endSection() ?>
