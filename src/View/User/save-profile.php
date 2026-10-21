<div class="p-4 sm:p-6 lg:p-8 space-y-6">

    <!-- ============================================ -->
    <!-- FLASH MESSAGE                                  -->
    <!-- ============================================ -->
    <?php if (!empty($_SESSION['flash']['message'])): ?>
        <div class="flex items-start gap-3 p-4 bg-success-soft text-success
                    border-4 border-text shadow-brutal-sm font-bold text-sm">
            <i data-lucide="check-circle" class="w-5 h-5 shrink-0 mt-0.5"></i>
            <span><?= htmlspecialchars($_SESSION['flash']['message']) ?></span>
        </div>
        <?php unset($_SESSION['flash']['message']); ?>
    <?php endif; ?>

    <!-- ============================================ -->
    <!-- ERROR                                          -->
    <!-- ============================================ -->
    <?php if (!empty($error)): ?>
        <div class="flex items-start gap-3 p-4 bg-danger-soft text-danger
                    border-4 border-text shadow-brutal-sm font-bold text-sm">
            <i data-lucide="alert-circle" class="w-5 h-5 shrink-0 mt-0.5"></i>
            <span><?= htmlspecialchars($error) ?></span>
        </div>
    <?php endif; ?>

    <!-- ============================================ -->
    <!-- HEADER                                         -->
    <!-- ============================================ -->
    <div>
        <h1 class="font-display text-2xl sm:text-3xl uppercase tracking-tight text-text">
            Update Profile
        </h1>
        <p class="mt-1 text-sm text-text-muted">Perbarui nama dan foto profil kamu.</p>
    </div>

    <!-- ============================================ -->
    <!-- FORM                                           -->
    <!-- ============================================ -->
    <form action="/profile/save" method="post" enctype="multipart/form-data"
        class="bg-surface border-4 border-text shadow-brutal p-6 sm:p-8 space-y-6 max-w-2xl">

        <!-- ============================================ -->
        <!-- AVATAR PREVIEW + UPLOAD                        -->
        <!-- ============================================ -->
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-5">

            <!-- Preview avatar -->
            <div class="w-20 h-20 shrink-0 overflow-hidden rounded-full border-4 border-text shadow-brutal-sm">
                <?php
                $avatarSrc = !empty($profile->avatar)
                    ? '/uploads/avatars/' . htmlspecialchars($profile->avatar)
                    : '/uploads/avatars/default-avatar.png';
                ?>
                <img id="avatar-preview" src="<?= $avatarSrc ?>"
                    alt="<?= htmlspecialchars($profile->name ?? 'Avatar') ?>" class="w-full h-full object-cover"
                    onerror="this.onerror=null; this.src='/uploads/avatars/default-avatar.png';">
            </div>

            <!-- Input file -->
            <div class="flex-1 w-full">
                <label for="avatar" class="block mb-1.5 font-black uppercase text-xs tracking-wider text-text">
                    Avatar
                </label>

                <input type="file" id="avatar" name="avatar" accept="image/*" class="block w-full text-sm text-text-body
                           file:mr-3 file:py-2 file:px-4
                           file:border-4 file:border-text
                           file:bg-primary file:text-text-invert
                           file:font-black file:uppercase file:text-xs file:tracking-wider
                           file:cursor-pointer
                           file:hover:bg-primary-hover
                           file:transition-colors
                           cursor-pointer">
                <p class="mt-1.5 text-xs text-text-muted">
                    Format: JPG, PNG, WEBP. Maks 2MB.
                </p>
            </div>
        </div>

        <!-- Divider -->
        <div class="border-t-2 border-dashed border-border-strong"></div>

        <!-- ============================================ -->
        <!-- NAMA                                           -->
        <!-- ============================================ -->
        <div>
            <label for="name" class="block mb-1.5 font-black uppercase text-xs tracking-wider text-text">
                Nama
            </label>
            <input type="text" id="name" name="name"
                value="<?= htmlspecialchars($_POST['name'] ?? $profile->name ?? '') ?>" placeholder="Nama lengkap"
                class="w-full px-4 py-3 bg-surface text-text
                       border-4 border-text
                       font-medium text-sm
                       placeholder:text-text-muted/60
                       focus:outline-none focus:ring-4 focus:ring-primary-ring focus:border-primary
                       transition-all">
        </div>

        <!-- ============================================ -->
        <!-- SUBMIT                                         -->
        <!-- ============================================ -->
        <div class="flex flex-col sm:flex-row gap-3">
            <button type="submit" class="inline-flex items-center justify-center gap-2
                           px-5 py-3 bg-cta text-cta-text
                           border-4 border-text shadow-brutal-sm
                           font-black uppercase text-sm tracking-wider
                           hover:translate-x-[3px] hover:translate-y-[3px] hover:shadow-none
                           transition-all duration-150">
                <i data-lucide="save" class="w-4 h-4"></i>
                <span>Simpan Profil</span>
            </button>

            <a href="/profile" class="inline-flex items-center justify-center gap-2
                      px-5 py-3 bg-surface text-text
                      border-4 border-text
                      font-black uppercase text-sm tracking-wider
                      hover:bg-surface-hover transition-colors">
                <span>Batal</span>
            </a>
        </div>

    </form>

</div>

<!-- ============================================ -->
<!-- SCRIPT: live preview avatar                    -->
<!-- ============================================ -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const input = document.getElementById('avatar');
        const preview = document.getElementById('avatar-preview');

        if (!input || !preview) return;

        input.addEventListener('change', function (e) {
            const file = e.target.files && e.target.files[0];
            if (!file) return;

            // Validasi tipe
            if (!file.type.startsWith('image/')) {
                alert('File harus berupa gambar.');
                input.value = '';
                return;
            }

            // Validasi ukuran (2MB)
            if (file.size > 2 * 1024 * 1024) {
                alert('Ukuran gambar maksimal 2MB.');
                input.value = '';
                return;
            }

            // Preview
            const reader = new FileReader();
            reader.onload = function (ev) {
                preview.src = ev.target.result;
            };
            reader.readAsDataURL(file);
        });
    });
</script>