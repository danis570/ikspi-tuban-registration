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

        $flashIcon = $isError
            ? 'alert-circle'
            : 'check-circle';
        ?>

        <div class="flex items-start gap-3 p-4 <?= $flashClass ?>
                border-4 border-text shadow-brutal-sm font-bold text-sm">
            <i data-lucide="<?= $flashIcon ?>" class="w-5 h-5 shrink-0 mt-0.5"></i>

            <span>
                <?= htmlspecialchars(
                    $_SESSION['flash']['message'],
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </span>
        </div>

        <?php unset($_SESSION['flash']['message'], $_SESSION['flash']['type']); ?>
    <?php endif; ?>


    <!-- ============================================ -->
    <!-- HEADER                                       -->
    <!-- ============================================ -->
    <div class="space-y-3">
        <h1 class="font-display text-2xl sm:text-3xl uppercase tracking-tight text-text">
            Data Registrasi
        </h1>
    </div>

    <!-- ============================================ -->
    <!-- FILTER SECTION                               -->
    <!-- ============================================ -->
    <form id="registration_filter_form" method="GET" action=""
        class="bg-surface border-4 border-text shadow-brutal p-4 sm:p-5 flex flex-col md:flex-row items-end gap-4">

        <!-- Filter Tingkat -->
        <div class="w-full md:w-auto flex-1">
            <label for="filter_level" class="block mb-1.5 font-black uppercase text-xs tracking-wider text-text">Filter
                Tingkat</label>
            <select id="filter_level" name="level"
                class="w-full px-4 py-2.5 bg-surface text-text border-4 border-text font-medium text-sm focus:outline-none focus:ring-4 focus:ring-primary-ring focus:border-primary transition-all">
                <option value="">Semua Tingkat</option>
                <option value="I" <?= ($_GET['level'] ?? '') === 'I' ? 'selected' : '' ?>>Tingkat I</option>
                <option value="II" <?= ($_GET['level'] ?? '') === 'II' ? 'selected' : '' ?>>Tingkat II</option>
            </select>
        </div>

        <!-- Filter Angkatan -->
        <div class="w-full md:w-auto flex-1">
            <label for="filter_batch" class="block mb-1.5 font-black uppercase text-xs tracking-wider text-text">Filter
                Angkatan</label>
            <input id="filter_batch" type="number" name="batch" placeholder="Contoh: 141"
                value="<?= htmlspecialchars($_GET['batch'] ?? '') ?>"
                class="w-full px-4 py-2.5 bg-surface text-text border-4 border-text font-medium text-sm focus:outline-none focus:ring-4 focus:ring-primary-ring focus:border-primary transition-all">
        </div>

        <!-- Tombol Aksi Filter -->
        <div class="flex items-center gap-3 w-full md:w-auto">
            <button type="submit"
                class="flex-1 md:flex-none inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-info-soft text-info border-4 border-text shadow-brutal-sm font-black uppercase text-sm tracking-wider hover:translate-x-[3px] hover:translate-y-[3px] hover:shadow-none transition-all duration-150">
                <i data-lucide="filter" class="w-4 h-4"></i>
                <span>Terapkan</span>
            </button>

            <!-- Tombol Reset (Hanya muncul jika ada filter aktif) -->
            <?php if (!empty($_GET['level']) || !empty($_GET['batch'])): ?>
                <a id="reset_registration_filter" href="?"
                    class="inline-flex items-center justify-center px-4 py-2.5 bg-surface text-text-muted hover:text-text border-4 border-text shadow-brutal-sm font-black uppercase text-sm tracking-wider hover:translate-x-[3px] hover:translate-y-[3px] hover:shadow-none transition-all duration-150"
                    title="Reset Filter">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </a>
            <?php endif; ?>
        </div>

    </form>

    <!-- ============================================ -->
    <!-- TABLE                                        -->
    <!-- ============================================ -->
    <?php if (!empty($registrations)): ?>

        <form id="selected_registration_form" action="/registration/generate" method="POST"
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

            <input type="hidden" id="selected_registration_ids" name="selected_registration_ids" value="[]">

            <button type="submit" id="continue_generate_button" disabled class="inline-flex items-center justify-center gap-2 px-4 py-2.5
           bg-primary text-white border-4 border-text shadow-brutal-sm
           font-black uppercase text-xs tracking-wider
           disabled:opacity-40 disabled:cursor-not-allowed
           disabled:shadow-none disabled:translate-x-0 disabled:translate-y-0
           enabled:hover:translate-x-[3px] enabled:hover:translate-y-[3px]
           enabled:hover:shadow-none transition-all duration-150">
                <i data-lucide="file-check" class="w-4 h-4"></i>
                <span>Lanjutkan Generate</span>
            </button>
        </form>
        <div class="bg-surface border-4 border-text shadow-brutal overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-text text-text-invert">
                        <tr>
                            <th class="text-left font-black uppercase text-xs tracking-wider px-4 py-3">No</th>
                            <th class="text-left font-black uppercase text-xs tracking-wider px-4 py-3">Nama</th>
                            <th class="text-left font-black uppercase text-xs tracking-wider px-4 py-3">Tingkat</th>
                            <th class="text-left font-black uppercase text-xs tracking-wider px-4 py-3">Angkatan</th>
                            <th class="text-left font-black uppercase text-xs tracking-wider px-4 py-3">Action</th>
                            <th class="text-center font-black uppercase text-xs tracking-wider px-4 py-3">
                                <div class="flex items-center justify-center gap-2">

                                    <input type="checkbox" id="select_all_registrations"
                                        aria-label="Pilih semua registrasi pada halaman ini"
                                        class="w-5 h-5 accent-primary cursor-pointer">
                                </div>
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

                                <!-- No -->
                                <td class="px-4 py-3 font-bold text-text-muted">
                                    <?= $index + 1 ?>
                                </td>

                                <!-- Nama -->
                                <td class="px-4 py-3 font-bold text-text">
                                    <?= htmlspecialchars($registration->fullName) ?>
                                </td>

                                <!-- Tingkat -->
                                <td class="px-4 py-3 font-bold text-text">
                                    <?= htmlspecialchars($period->level->value) ?>
                                </td>

                                <!-- Angkatan -->
                                <td class="px-4 py-3 font-bold text-text">
                                    <?= htmlspecialchars((string) $period->batch) ?>
                                </td>

                                <!-- Action -->
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2 flex-wrap">

                                        <!-- Asumsi route edit: /registration/edit?id=... -->
                                        <a href="/registration/edit?id=<?= (int) $registration->id ?>"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-warning-soft text-black border-2 border-text shadow-brutal-sm font-black uppercase text-[10px] tracking-wider hover:translate-x-[2px] hover:translate-y-[2px] hover:shadow-none transition-all duration-150">
                                            <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                            <span>Edit</span>
                                        </a>

                                        <!-- Asumsi route hapus: /registration/delete -->
                                        <form action="/registration/delete" method="POST" class="inline-block m-0"
                                            onsubmit="return confirm('Yakin ingin menghapus data registrasi ini?')">
                                            <input type="hidden" name="id" value="<?= (int) $registration->id ?>">

                                            <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5
                                                           bg-danger text-text-invert
                                                           border-2 border-text shadow-brutal-sm
                                                           font-black uppercase text-[10px] tracking-wider
                                                           hover:translate-x-[2px] hover:translate-y-[2px]
                                                           hover:shadow-none transition-all duration-150">
                                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                <span>Delete</span>
                                            </button>
                                        </form>

                                    </div>
                                </td>
                                <!-- Pilih -->
                                <td class="px-4 py-3 text-center">
                                    <input type="checkbox" class="registration-checkbox w-5 h-5 accent-primary cursor-pointer"
                                        value="<?= (int) $registration->id ?>"
                                        aria-label="Pilih <?= htmlspecialchars($registration->fullName) ?>">
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

        <!-- ============================================ -->
        <!-- EMPTY STATE (Jika Data Kosong)               -->
        <!-- ============================================ -->
    <?php else: ?>
        <div class="bg-surface border-4 border-text shadow-brutal p-8 text-center">
            <i data-lucide="inbox" class="w-8 h-8 mx-auto mb-2 text-text-muted"></i>
            <p class="font-black uppercase tracking-wider text-text-muted">
                <?= (!empty($_GET['level']) || !empty($_GET['batch']))
                    ? 'Tidak ada data registrasi yang cocok dengan filter.'
                    : 'Belum ada data registrasi.' ?>
            </p>
        </div>
    <?php endif; ?>

</div>



<script>
    document.addEventListener('DOMContentLoaded', function () {
        const storageKey = 'selectedRegistrationIds';

        sessionStorage.removeItem(storageKey);

        const filterForm = document.getElementById(
            'registration_filter_form'
        );

        const resetFilter = document.getElementById(
            'reset_registration_filter'
        );

        function clearSelection() {
            sessionStorage.removeItem(storageKey);
        }

        if (filterForm) {
            filterForm.addEventListener('submit', clearSelection);
        }

        if (resetFilter) {
            resetFilter.addEventListener('click', clearSelection);
        }

        const selectAll = document.getElementById(
            'select_all_registrations'
        );

        const checkboxes = document.querySelectorAll(
            '.registration-checkbox'
        );

        const selectedCount = document.getElementById(
            'selected_count'
        );

        const selectedIdsInput = document.getElementById(
            'selected_registration_ids'
        );

        const continueButton = document.getElementById(
            'continue_generate_button'
        );

        const selectionForm = document.getElementById(
            'selected_registration_form'
        );

        let selectedIds = [];

        try {
            const storedIds = JSON.parse(
                sessionStorage.getItem(storageKey) || '[]'
            );

            if (Array.isArray(storedIds)) {
                selectedIds = storedIds.map(String);
            }
        } catch {
            selectedIds = [];
        }

        // Hilangkan ID duplikat.
        selectedIds = [...new Set(selectedIds)];

        function saveSelection() {
            sessionStorage.setItem(
                storageKey,
                JSON.stringify(selectedIds)
            );
        }

        function updateUI() {
            checkboxes.forEach(function (checkbox) {
                checkbox.checked = selectedIds.includes(
                    checkbox.value
                );
            });

            const checkedOnPage = Array.from(checkboxes).filter(
                checkbox => checkbox.checked
            ).length;

            if (selectAll) {
                selectAll.checked =
                    checkboxes.length > 0 &&
                    checkedOnPage === checkboxes.length;

                selectAll.indeterminate =
                    checkedOnPage > 0 &&
                    checkedOnPage < checkboxes.length;
            }

            if (selectedCount) {
                selectedCount.textContent = selectedIds.length;
            }

            if (selectedIdsInput) {
                selectedIdsInput.value = JSON.stringify(selectedIds);
            }

            if (continueButton) {
                continueButton.disabled = selectedIds.length === 0;
            }
        }

        checkboxes.forEach(function (checkbox) {
            checkbox.addEventListener('change', function () {
                if (checkbox.checked) {
                    if (!selectedIds.includes(checkbox.value)) {
                        selectedIds.push(checkbox.value);
                    }
                } else {
                    selectedIds = selectedIds.filter(
                        id => id !== checkbox.value
                    );
                }

                saveSelection();
                updateUI();
            });
        });

        if (selectAll) {
            selectAll.addEventListener('change', function () {
                checkboxes.forEach(function (checkbox) {
                    if (selectAll.checked) {
                        if (!selectedIds.includes(checkbox.value)) {
                            selectedIds.push(checkbox.value);
                        }
                    } else {
                        selectedIds = selectedIds.filter(
                            id => id !== checkbox.value
                        );
                    }
                });

                saveSelection();
                updateUI();
            });
        }

        if (selectionForm) {
            selectionForm.addEventListener('submit', function (event) {
                if (selectedIds.length === 0) {
                    event.preventDefault();
                    updateUI();
                    return;
                }

                selectedIdsInput.value = JSON.stringify(selectedIds);
            });
        }

        updateUI();
    });
</script>