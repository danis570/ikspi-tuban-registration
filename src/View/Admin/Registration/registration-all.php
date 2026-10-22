<div class="p-4 sm:p-6 lg:p-8 space-y-6">

    <!-- ============================================ -->
    <!-- FLASH MESSAGE                                -->
    <!-- ============================================ -->
    <?php if (!empty($_SESSION['flash']['message'])): ?>
        <?php
        $flashType = $_SESSION['flash']['type'] ?? 'success';
        $isError = $flashType === 'error';
        $flashClass = $isError
            ? 'bg-danger-soft text-danger'
            : 'bg-success-soft text-success';
        $flashIcon = $isError ? 'alert-circle' : 'check-circle';
        ?>

        <div class="flex items-start gap-3 p-4 <?= $flashClass ?>
                border-4 border-text shadow-brutal-sm font-bold text-sm">
            <i data-lucide="<?= $flashIcon ?>" class="w-5 h-5 shrink-0 mt-0.5"></i>

            <span>
                <?= htmlspecialchars($_SESSION['flash']['message'], ENT_QUOTES, 'UTF-8') ?>
            </span>
        </div>

        <?php unset($_SESSION['flash']['message'], $_SESSION['flash']['type']); ?>
    <?php endif; ?>

    <!-- ============================================ -->
    <!-- HEADER                                       -->
    <!-- ============================================ -->
    <div class="space-y-2">
        <h1 class="font-display text-2xl sm:text-3xl uppercase tracking-tight text-text">
            Data Registrasi
        </h1>
    </div>


    <!-- ============================================ -->
    <!-- FILTER SECTION                               -->
    <!-- ============================================ -->
    <form id="registration_filter_form" method="GET" action="/admin/registration/all"
        class="bg-surface border-4 border-text shadow-brutal p-4 sm:p-5 flex flex-col md:flex-row items-end gap-4">

        <!-- Filter Tingkat -->
        <div class="w-full md:w-auto flex-1">
            <label for="filter_level" class="block mb-1.5 font-black uppercase text-xs tracking-wider text-text">
                Tingkat
            </label>
            <select id="filter_level" name="level"
                class="w-full px-4 py-2.5 bg-surface text-text border-4 border-text font-medium text-sm focus:outline-none focus:ring-4 focus:ring-primary-ring focus:border-primary transition-all">
                <option value="">Semua Tingkat</option>
                <option value="I" <?= $filterLevel === 'I' ? 'selected' : '' ?>>Tingkat I</option>
                <option value="II" <?= $filterLevel === 'II' ? 'selected' : '' ?>>Tingkat II</option>
            </select>
        </div>

        <!-- Filter Angkatan -->
        <div class="w-full md:w-auto flex-1">
            <label for="filter_batch" class="block mb-1.5 font-black uppercase text-xs tracking-wider text-text">
                Angkatan
            </label>
            <input id="filter_batch" type="number" min="1" name="batch"
                value="<?= htmlspecialchars($filterBatch, ENT_QUOTES, 'UTF-8') ?>" placeholder="Semua angkatan"
                class="w-full px-4 py-2.5 bg-surface text-text border-4 border-text font-medium text-sm focus:outline-none focus:ring-4 focus:ring-primary-ring focus:border-primary transition-all">
        </div>

        <!-- Filter Search -->
        <div class="w-full md:w-auto flex-1">
            <label for="filter_search" class="block mb-1.5 font-black uppercase text-xs tracking-wider text-text">
                Nama atau email
            </label>
            <input id="filter_search" type="search" name="search"
                value="<?= htmlspecialchars($filterSearch, ENT_QUOTES, 'UTF-8') ?>" placeholder="Cari peserta..."
                class="w-full px-4 py-2.5 bg-surface text-text border-4 border-text font-medium text-sm focus:outline-none focus:ring-4 focus:ring-primary-ring focus:border-primary transition-all">
        </div>

        <!-- Tombol Aksi -->
        <div class="flex items-center gap-3 w-full md:w-auto">
            <button type="submit"
                class="flex-1 md:flex-none inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-info-soft text-info border-4 border-text shadow-brutal-sm font-black uppercase text-sm tracking-wider hover:translate-x-[3px] hover:translate-y-[3px] hover:shadow-none transition-all duration-150">
                <i data-lucide="filter" class="w-4 h-4"></i>
                <span>Terapkan</span>
            </button>

            <?php if ($filterLevel !== '' || $filterBatch !== '' || $filterSearch !== ''): ?>
                <a href="/admin/registration/all"
                    class="inline-flex items-center justify-center px-4 py-2.5 bg-surface text-text-muted hover:text-text border-4 border-text shadow-brutal-sm font-black uppercase text-sm tracking-wider hover:translate-x-[3px] hover:translate-y-[3px] hover:shadow-none transition-all duration-150"
                    title="Reset Filter">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </a>
            <?php endif; ?>
        </div>
    </form>


    <!-- ============================================ -->
    <!-- TABLE + BULK ACTION                          -->
    <!-- ============================================ -->
    <?php if ($registrations === []): ?>

        <div class="bg-surface border-4 border-text shadow-brutal p-8 text-center">
            <i data-lucide="inbox" class="w-8 h-8 mx-auto mb-2 text-text-muted"></i>
            <p class="font-black uppercase tracking-wider text-text-muted">
                <?= ($filterLevel !== '' || $filterBatch !== '' || $filterSearch !== '')
                    ? 'Tidak ada data registrasi yang cocok dengan filter.'
                    : 'Belum ada data registrasi.' ?>
            </p>
        </div>

    <?php else: ?>

        <!-- Panel Aksi Massal -->
        <div
            class="bg-surface border-4 border-text shadow-brutal p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <p class="font-black uppercase text-sm text-text">
                    Peserta dipilih:
                    <span id="selected_count">0</span>
                </p>
                <p class="text-xs text-text-muted mt-1">
                    Pilih minimal satu peserta untuk melanjutkan.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <button type="button" id="bulk_generate_pdf_button" disabled
    class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-neutral-200 text-text border-4 border-text shadow-brutal-sm font-black uppercase text-xs tracking-wider disabled:opacity-40 disabled:cursor-not-allowed disabled:shadow-none disabled:translate-x-0 disabled:translate-y-0 enabled:hover:translate-x-[3px] enabled:hover:translate-y-[3px] enabled:hover:shadow-none transition-all duration-150">
    <i data-lucide="file-text" class="w-4 h-4"></i>
    <span>Generate PDF</span>
</button>

                <button type="button" id="bulk_generate_excel_button" disabled
                    class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-success text-text-invert border-4 border-text shadow-brutal-sm font-black uppercase text-xs tracking-wider disabled:opacity-40 disabled:cursor-not-allowed disabled:shadow-none disabled:translate-x-0 disabled:translate-y-0 enabled:hover:translate-x-[3px] enabled:hover:translate-y-[3px] enabled:hover:shadow-none transition-all duration-150">
                    <i data-lucide="file-spreadsheet" class="w-4 h-4"></i>
                    <span>Download Excel</span>
                </button>

                <button type="button" id="bulk_delete_button" disabled
                    class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-danger text-text-invert border-4 border-text shadow-brutal-sm font-black uppercase text-xs tracking-wider disabled:opacity-40 disabled:cursor-not-allowed disabled:shadow-none disabled:translate-x-0 disabled:translate-y-0 enabled:hover:translate-x-[3px] enabled:hover:translate-y-[3px] enabled:hover:shadow-none transition-all duration-150">
                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                    <span>Hapus Terpilih</span>
                </button>
            </div>
        </div>

        <!-- Form Tersembunyi: Generate PDF -->
        <form id="bulk_generate_pdf_form" method="POST" action="/admin/registration/generate/pdf" target="_blank"
            class="hidden">
            <div id="bulk_generate_pdf_container"></div>
        </form>

        <!-- Form Tersembunyi: Generate Excel -->
        <form id="bulk_generate_excel_form" method="POST" action="/admin/registration/generate/excel" class="hidden">
            <div id="bulk_generate_excel_container"></div>
        </form>

        <!-- Form Tersembunyi: Hapus Terpilih -->
        <form id="bulk_delete_form" method="POST" action="/admin/registration/delete-selected"
            onsubmit="return confirm('Yakin ingin menghapus semua data yang dipilih?')" class="hidden">
            <div id="bulk_delete_container"></div>
        </form>

        <!-- Tabel -->
        <div class="bg-surface border-4 border-text shadow-brutal overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-text text-text-invert">
                        <tr>
                            <th class="text-left font-black uppercase text-xs tracking-wider px-4 py-3">No</th>
                            <th class="text-left font-black uppercase text-xs tracking-wider px-4 py-3">Nama</th>
                            <th class="text-left font-black uppercase text-xs tracking-wider px-4 py-3">Email</th>
                            <th class="text-left font-black uppercase text-xs tracking-wider px-4 py-3">Tingkat</th>
                            <th class="text-left font-black uppercase text-xs tracking-wider px-4 py-3">Angkatan</th>
                            <th class="text-left font-black uppercase text-xs tracking-wider px-4 py-3">Aksi</th>
                            <th class="text-center font-black uppercase text-xs tracking-wider px-4 py-3">
                                <input type="checkbox" id="select_all_registrations"
                                    aria-label="Pilih semua registrasi pada halaman ini"
                                    class="w-5 h-5 accent-primary cursor-pointer">
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y-4 divide-text">
                        <?php foreach ($registrations as $index => $data): ?>
                            <?php
                            $registration = $data['registration'];
                            $period = $data['period'];
                            ?>

                            <tr class="hover:bg-surface-hover transition-colors">
                                <td class="px-4 py-3 font-bold text-text-muted">
                                    <?= $index + 1 ?>
                                </td>

                                <td class="px-4 py-3 font-bold text-text">
                                    <?= htmlspecialchars($registration->fullName, ENT_QUOTES, 'UTF-8') ?>
                                </td>

                                <td class="px-4 py-3 font-bold text-text">
                                    <?= htmlspecialchars($data['email'], ENT_QUOTES, 'UTF-8') ?>
                                </td>

                                <td class="px-4 py-3 font-bold text-text">
                                    <?= htmlspecialchars($period->level->value, ENT_QUOTES, 'UTF-8') ?>
                                </td>

                                <td class="px-4 py-3 font-bold text-text">
                                    <?= htmlspecialchars((string) $period->batch, ENT_QUOTES, 'UTF-8') ?>
                                </td>

                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <a href="/admin/registration/edit?id=<?= (int) $registration->id ?>"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-warning-soft text-black border-2 border-text shadow-brutal-sm font-black uppercase text-[10px] tracking-wider hover:translate-x-[2px] hover:translate-y-[2px] hover:shadow-none transition-all duration-150">
                                            <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                            <span>Edit</span>
                                        </a>

                                        <form method="POST" action="/admin/registration/delete" class="inline-block m-0"
                                            onsubmit="return confirm('Yakin ingin menghapus data registrasi ini?')">
                                            <input type="hidden" name="id" value="<?= (int) $registration->id ?>">
                                            <button type="submit"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-danger text-text-invert border-2 border-text shadow-brutal-sm font-black uppercase text-[10px] tracking-wider hover:translate-x-[2px] hover:translate-y-[2px] hover:shadow-none transition-all duration-150">
                                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                <span>Hapus</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>

                                <td class="px-4 py-3 text-center">
                                    <input type="checkbox" class="registration-checkbox w-5 h-5 accent-primary cursor-pointer"
                                        value="<?= (int) $registration->id ?>"
                                        aria-label="Pilih <?= htmlspecialchars($registration->fullName, ENT_QUOTES, 'UTF-8') ?>">
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ============================================ -->
        <!-- SUMMARY                                      -->
        <!-- ============================================ -->
        <div class="bg-surface border-4 border-text shadow-brutal p-4">
            <p class="font-black uppercase text-sm text-text">
                Total : <span class="text-primary">
                    <?= count($registrations) ?>
                </span>
            </p>
        </div>

    <?php endif; ?>

</div>


<script>
    document.addEventListener('DOMContentLoaded', () => {
        const selectAll = document.getElementById('select_all_registrations');
        const checkboxes = [...document.querySelectorAll('.registration-checkbox')];
        const selectedCountEl = document.getElementById('selected_count');

        const bulkDeleteButton = document.getElementById('bulk_delete_button');
        const bulkPdfButton = document.getElementById('bulk_generate_pdf_button');
        const bulkExcelButton = document.getElementById('bulk_generate_excel_button');

        const bulkDeleteForm = document.getElementById('bulk_delete_form');
        const bulkDeleteCont = document.getElementById('bulk_delete_container');
        const bulkPdfForm = document.getElementById('bulk_generate_pdf_form');
        const bulkPdfCont = document.getElementById('bulk_generate_pdf_container');
        const bulkExcelForm = document.getElementById('bulk_generate_excel_form');
        const bulkExcelCont = document.getElementById('bulk_generate_excel_container');

        // ============================================
        // HELPERS
        // ============================================
        const getSelectedIds = () =>
            checkboxes.filter(cb => cb.checked).map(cb => cb.value);

        const syncHiddenInputs = (container, name, ids) => {
            if (!container) return;
            container.replaceChildren();
            ids.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = name;
                input.value = id;
                container.appendChild(input);
            });
        };

        // ============================================
        // UPDATE UI
        // ============================================
        const updateSelection = () => {
            const ids = getSelectedIds();
            const count = ids.length;

            if (selectAll) {
                selectAll.checked = checkboxes.length > 0 && count === checkboxes.length;
                selectAll.indeterminate = count > 0 && count < checkboxes.length;
            }

            if (selectedCountEl) selectedCountEl.textContent = count;

            [bulkDeleteButton, bulkPdfButton, bulkExcelButton].forEach(btn => {
                if (btn) btn.disabled = count === 0;
            });
        };

        // ============================================
        // EVENTS
        // ============================================
        selectAll?.addEventListener('change', () => {
            checkboxes.forEach(cb => { cb.checked = selectAll.checked; });
            updateSelection();
        });

        checkboxes.forEach(cb => cb.addEventListener('change', updateSelection));

        bulkDeleteButton?.addEventListener('click', () => {
            const ids = getSelectedIds();
            if (ids.length === 0) return;
            syncHiddenInputs(bulkDeleteCont, 'ids[]', ids);
            bulkDeleteForm?.requestSubmit();
        });

        bulkPdfButton?.addEventListener('click', () => {
            const ids = getSelectedIds();
            if (ids.length === 0) return;
            syncHiddenInputs(bulkPdfCont, 'selected_registration_ids[]', ids);
            bulkPdfForm?.requestSubmit();
        });

        bulkExcelButton?.addEventListener('click', () => {
            const ids = getSelectedIds();
            if (ids.length === 0) return;
            syncHiddenInputs(bulkExcelCont, 'selected_registration_ids[]', ids);
            bulkExcelForm?.requestSubmit();
        });

        // ============================================
        // INITIAL
        // ============================================
        updateSelection();
    });
</script>