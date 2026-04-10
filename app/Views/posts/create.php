<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<section class="card" style="max-width:820px; margin:0 auto;">
    <h2 style="margin-top:0;">Create New Course</h2>
    <p class="meta">Server upload limits: upload_max_filesize=<?= esc((string) ini_get('upload_max_filesize')) ?>, post_max_size=<?= esc((string) ini_get('post_max_size')) ?>.</p>

    <?php $errors = session()->getFlashdata('errors') ?? []; ?>

    <form method="post" action="/posts" enctype="multipart/form-data">
        <?= csrf_field() ?>

        <label class="label" for="title">Course Title</label>
        <input class="input" type="text" id="title" name="title" value="<?= esc(old('title')) ?>" required>
        <?php if (isset($errors['title'])): ?><p class="field-error"><?= esc($errors['title']) ?></p><?php endif; ?>

        <label class="label" for="category">Category</label>
        <input class="input" type="text" id="category" name="category" value="<?= esc(old('category')) ?>" placeholder="Web Development / Data Science / Aptitude">
        <?php if (isset($errors['category'])): ?><p class="field-error"><?= esc($errors['category']) ?></p><?php endif; ?>

        <label class="label" for="video_url">Video Embed URL (https)</label>
        <input class="input" type="url" id="video_url" name="video_url" value="<?= esc(old('video_url')) ?>" placeholder="https://www.youtube.com/embed/...">
        <?php if (isset($errors['video_url'])): ?><p class="field-error"><?= esc($errors['video_url']) ?></p><?php endif; ?>

        <label class="label" for="video_file">Or Upload Your Own Video (max 200MB)</label>
        <input class="input" type="file" id="video_file" name="video_file" accept="video/mp4,video/webm,video/ogg,video/quicktime">
        <p class="meta" style="margin-top:-4px;">You can keep both embed URL and uploaded video. Uploaded video will be shown first.</p>

        <label class="label" for="note_files">Notes & Resources (PDF/images/docs/media, multiple)</label>
        <input class="input" type="file" id="note_files" name="note_files[]" multiple accept=".pdf,.png,.jpg,.jpeg,.webp,.gif,.svg,.txt,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.csv,.zip,.mp3,.wav,.mp4,.webm,image/*,audio/*,video/*">
        <p class="meta" style="margin-top:-4px;">Upload notes/resources. PDF max 150MB, other files max 50MB each.</p>

        <div class="stack" style="justify-content:space-between; align-items:center;">
            <label class="label" for="content" style="margin:0;">Course Content</label>
            <button class="btn btn-light" type="button" id="pasteContentBtn">Paste from Clipboard</button>
        </div>
        <textarea id="content" name="content" rows="10" required><?= esc(old('content')) ?></textarea>
        <?php if (isset($errors['content'])): ?><p class="field-error"><?= esc($errors['content']) ?></p><?php endif; ?>
        <p class="meta" id="pasteStatus" style="margin-top:-4px;">Tip: You can also use right-click paste or Shift+Insert.</p>

        <label style="display:flex; align-items:center; gap:8px; margin:8px 0 14px;">
            <input type="checkbox" name="is_published" value="1" <?= old('is_published') ? 'checked' : '' ?>>
            Publish now (visible on home feed)
        </label>

        <div class="stack">
            <button class="btn btn-brand" type="submit">Save Course</button>
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
