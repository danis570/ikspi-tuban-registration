<div class="mx-auto max-w-4xl p-4 sm:p-6 lg:p-8">

    <!-- ============================================ -->
    <!-- ERROR                                        -->
    <!-- ============================================ -->
    <?php if (!empty($error)): ?>
        <div class="flex items-start gap-3 p-4 mb-6 bg-danger-soft text-danger
                    border-4 border-text shadow-brutal-sm font-bold text-sm">
            <i data-lucide="alert-circle" class="w-5 h-5 shrink-0 mt-0.5"></i>
            <span><?= htmlspecialchars($error) ?></span>
        </div>
    <?php endif; ?>

    <!-- Header Form -->
    <div class="mb-6">
        <h1 class="font-display text-2xl sm:text-3xl uppercase tracking-tight text-text">
            <?= $registration === null ? 'Formulir Pendaftaran Tingkat II' : 'Edit Data pendaftar | Tingkat II' ?>
        </h1>

        <p class="mt-1 text-sm text-text-muted font-bold">
            Data wajib lengkap | Angkatan
            <?= htmlspecialchars(
                (string) $registrationPeriod->batch
            ) ?>
        </p>
    </div>

    <form method="POST" enctype="multipart/form-data"
        class="bg-surface border-4 border-text shadow-brutal p-6 sm:p-8 space-y-8">

        <input type="hidden" name="id" value="<?= htmlspecialchars(
            (string) ($registration?->id ?? ''),
            ENT_QUOTES,
            'UTF-8'
        ) ?>">

        <!-- ============================================ -->
        <!-- DATA DIRI                                    -->
        <!-- ============================================ -->
        <div class="space-y-4">
            <h2
                class="font-black uppercase tracking-wider text-text border-b-2 border-dashed border-border-strong pb-2">
                Data Diri
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Nama Lengkap -->
                <div class="md:col-span-2">
                    <label for="full_name"
                        class="block mb-1.5 font-black uppercase text-xs tracking-wider text-text">Nama Lengkap</label>
                    <input id="full_name" type="text" name="full_name" value="<?= htmlspecialchars(
                        $_POST['full_name'] ?? $registration?->fullName ?? '',
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"
                        class="w-full px-4 py-2.5 bg-surface text-text border-4 border-text font-medium text-sm focus:outline-none focus:ring-4 focus:ring-primary-ring focus:border-primary transition-all"
                        required>
                </div>

                <!-- Tempat Lahir -->
                <div>
                    <label for="birth_place"
                        class="block mb-1.5 font-black uppercase text-xs tracking-wider text-text">Tempat Lahir</label>
                    <input id="birth_place" type="text" name="birth_place" value="<?= htmlspecialchars(
                        $_POST['birth_place'] ?? $registration?->birthPlace ?? '',
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"
                        class="w-full px-4 py-2.5 bg-surface text-text border-4 border-text font-medium text-sm focus:outline-none focus:ring-4 focus:ring-primary-ring focus:border-primary transition-all"
                        required>
                </div>

                <!-- Tanggal Lahir -->
                <div>
                    <label for="birth_date"
                        class="block mb-1.5 font-black uppercase text-xs tracking-wider text-text">Tanggal Lahir</label>
                    <input id="birth_date" type="date" name="birth_date" value="<?= htmlspecialchars(
                        $_POST['birth_date'] ?? (
                            $registration?->birthDate instanceof \DateTimeInterface
                            ? $registration->birthDate->format('Y-m-d')
                            : ($registration?->birthDate ?? '')
                        ),
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"
                        class="w-full px-4 py-2.5 bg-surface text-text border-4 border-text font-medium text-sm focus:outline-none focus:ring-4 focus:ring-primary-ring focus:border-primary transition-all"
                        required>
                </div>

                <!-- Pekerjaan -->
                <div>
                    <label for="occupation"
                        class="block mb-1.5 font-black uppercase text-xs tracking-wider text-text">Pekerjaan</label>
                    <input id="occupation" type="text" name="occupation" value="<?= htmlspecialchars(
                        $_POST['occupation'] ?? $registration?->occupation ?? '',
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"
                        class="w-full px-4 py-2.5 bg-surface text-text border-4 border-text font-medium text-sm focus:outline-none focus:ring-4 focus:ring-primary-ring focus:border-primary transition-all"
                        required>
                </div>

                <!-- Tempat Latihan -->
                <div>
                    <label for="training_location"
                        class="block mb-1.5 font-black uppercase text-xs tracking-wider text-text">Tempat
                        Latihan</label>
                    <input id="training_location" type="text" name="training_location" value="<?= htmlspecialchars(
                        $_POST['training_location'] ?? $registration?->trainingLocation ?? '',
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"
                        class="w-full px-4 py-2.5 bg-surface text-text border-4 border-text font-medium text-sm focus:outline-none focus:ring-4 focus:ring-primary-ring focus:border-primary transition-all"
                        required>
                </div>
            </div>
        </div>

        <!-- ============================================ -->
        <!-- ALAMAT                                       -->
        <!-- ============================================ -->
        <div class="space-y-4">
            <h2
                class="font-black uppercase tracking-wider text-text border-b-2 border-dashed border-border-strong pb-2">
                Alamat Lengkap
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Provinsi -->
                <div>
                    <label for="province_code"
                        class="block mb-1.5 font-black uppercase text-xs tracking-wider text-text">Provinsi</label>
                    <select id="province_code" name="province_code"
                        class="w-full px-4 py-2.5 bg-surface text-text border-4 border-text font-medium text-sm focus:outline-none focus:ring-4 focus:ring-primary-ring focus:border-primary transition-all"
                        required>
                        <option value="">Memuat provinsi...</option>
                    </select>
                </div>

                <!-- Kabupaten/Kota -->
                <div>
                    <label for="regency_code"
                        class="block mb-1.5 font-black uppercase text-xs tracking-wider text-text">Kabupaten/Kota</label>
                    <select id="regency_code" name="regency_code"
                        class="w-full px-4 py-2.5 bg-surface text-text border-4 border-text font-medium text-sm focus:outline-none focus:ring-4 focus:ring-primary-ring focus:border-primary transition-all"
                        required disabled>
                        <option value="">Pilih provinsi terlebih dahulu</option>
                    </select>
                </div>

                <!-- Kecamatan -->
                <div>
                    <label for="district_code"
                        class="block mb-1.5 font-black uppercase text-xs tracking-wider text-text">Kecamatan</label>
                    <select id="district_code" name="district_code"
                        class="w-full px-4 py-2.5 bg-surface text-text border-4 border-text font-medium text-sm focus:outline-none focus:ring-4 focus:ring-primary-ring focus:border-primary transition-all"
                        required disabled>
                        <option value="">Pilih kabupaten terlebih dahulu</option>
                    </select>
                </div>

                <!-- Desa/Kelurahan -->
                <div>
                    <label for="village_code"
                        class="block mb-1.5 font-black uppercase text-xs tracking-wider text-text">Desa/Kelurahan</label>
                    <select id="village_code" name="village_code"
                        class="w-full px-4 py-2.5 bg-surface text-text border-4 border-text font-medium text-sm focus:outline-none focus:ring-4 focus:ring-primary-ring focus:border-primary transition-all"
                        required disabled>
                        <option value="">Pilih kecamatan terlebih dahulu</option>
                    </select>
                </div>

                <!-- RT -->
                <div>
                    <label for="rt"
                        class="block mb-1.5 font-black uppercase text-xs tracking-wider text-text">RT</label>
                    <input id="rt" type="text" name="rt" value="<?= htmlspecialchars(
                        $_POST['rt'] ?? $registration?->rt ?? '',
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"
                        class="w-full px-4 py-2.5 bg-surface text-text border-4 border-text font-medium text-sm focus:outline-none focus:ring-4 focus:ring-primary-ring focus:border-primary transition-all">
                </div>

                <!-- RW -->
                <div>
                    <label for="rw"
                        class="block mb-1.5 font-black uppercase text-xs tracking-wider text-text">RW</label>
                    <input id="rw" type="text" name="rw" value="<?= htmlspecialchars(
                        $_POST['rw'] ?? $registration?->rw ?? '',
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"
                        class="w-full px-4 py-2.5 bg-surface text-text border-4 border-text font-medium text-sm focus:outline-none focus:ring-4 focus:ring-primary-ring focus:border-primary transition-all">
                </div>
            </div>
        </div>

        <!-- ============================================ -->
        <!-- DATA KELUARGA & PELATIH                      -->
        <!-- ============================================ -->
        <div class="space-y-4">
            <h2
                class="font-black uppercase tracking-wider text-text border-b-2 border-dashed border-border-strong pb-2">
                Data Keluarga & Pelatih
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Nama Ayah -->
                <div>
                    <label for="father_name"
                        class="block mb-1.5 font-black uppercase text-xs tracking-wider text-text">Nama Ayah</label>
                    <input id="father_name" type="text" name="father_name" value="<?= htmlspecialchars(
                        $_POST['father_name'] ?? $registration?->fatherName ?? '',
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"
                        class="w-full px-4 py-2.5 bg-surface text-text border-4 border-text font-medium text-sm focus:outline-none focus:ring-4 focus:ring-primary-ring focus:border-primary transition-all"
                        required>
                </div>

                <!-- Nama Ibu -->
                <div>
                    <label for="mother_name"
                        class="block mb-1.5 font-black uppercase text-xs tracking-wider text-text">Nama Ibu</label>
                    <input id="mother_name" type="text" name="mother_name" value="<?= htmlspecialchars(
                        $_POST['mother_name'] ?? $registration?->motherName ?? '',
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"
                        class="w-full px-4 py-2.5 bg-surface text-text border-4 border-text font-medium text-sm focus:outline-none focus:ring-4 focus:ring-primary-ring focus:border-primary transition-all"
                        required>
                </div>

                <!-- Nama Pelatih -->
                <div class="md:col-span-2">
                    <label for="coach_name"
                        class="block mb-1.5 font-black uppercase text-xs tracking-wider text-text">Nama Pelatih</label>
                    <input id="coach_name" type="text" name="coach_name" value="<?= htmlspecialchars(
                        $_POST['coach_name'] ?? $registration?->coachName ?? '',
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"
                        class="w-full px-4 py-2.5 bg-surface text-text border-4 border-text font-medium text-sm focus:outline-none focus:ring-4 focus:ring-primary-ring focus:border-primary transition-all"
                        required>
                </div>

                <!-- Alamat Pelatih -->
                <div class="md:col-span-2">
                    <label for="coach_address"
                        class="block mb-1.5 font-black uppercase text-xs tracking-wider text-text">Alamat
                        Pelatih</label>
                    <textarea id="coach_address" name="coach_address"
                        class="w-full px-4 py-2.5 bg-surface text-text border-4 border-text font-medium text-sm focus:outline-none focus:ring-4 focus:ring-primary-ring focus:border-primary transition-all"
                        rows="3" required><?= htmlspecialchars(
                            $_POST['coach_address'] ?? $registration?->coachAddress ?? '',
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?></textarea>
                </div>
            </div>
        </div>

        <!-- ============================================ -->
        <!-- UPLOAD FOTO                                  -->
        <!-- ============================================ -->
        <div class="space-y-4">
            <h2
                class="font-black uppercase tracking-wider text-text border-b-2 border-dashed border-border-strong pb-2">
                Berkas Foto
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Foto Kecil (3x3) -->
                <div>
                    <label for="small_photo"
                        class="block mb-1.5 font-black uppercase text-xs tracking-wider text-text">Foto Kecil
                        (3x3)</label>

                    <?php if (!empty($registration?->smallPhoto)): ?>
                        <div class="mb-3">
                            <img id="small_photo_preview"
                                src="/uploads/registrations/<?= rawurlencode(basename($registration->smallPhoto)) ?>"
                                alt="Preview foto kecil"
                                class="h-[113px] w-[113px] rounded border-4 border-text object-cover shadow-brutal-sm">
                        </div>
                    <?php else: ?>
                        <img id="small_photo_preview" alt="Preview foto kecil"
                            class="mb-3 hidden h-[113px] w-[113px] rounded border-4 border-text object-cover shadow-brutal-sm">
                    <?php endif; ?>

                    <input id="small_photo" type="file" name="small_photo" accept="image/jpeg,image/png,image/webp"
                        class="block w-full text-sm text-text-body file:mr-3 file:py-2 file:px-4 file:border-4 file:border-text file:bg-primary file:text-text-invert file:font-black file:uppercase file:text-xs file:tracking-wider file:cursor-pointer file:hover:bg-primary-hover file:transition-colors cursor-pointer"
                        <?= $registration === null ? 'required' : '' ?>>
                </div>

                <!-- Foto Besar (7x10) -->
                <div>
                    <label for="large_photo"
                        class="block mb-1.5 font-black uppercase text-xs tracking-wider text-text">Foto Besar
                        (7x10)</label>

                    <?php if (!empty($registration?->largePhoto)): ?>
                        <div class="mb-3">
                            <img id="large_photo_preview"
                                src="/uploads/registrations/<?= rawurlencode(basename($registration->largePhoto)) ?>"
                                alt="Preview foto besar"
                                class="h-[378px] w-[265px] rounded border-4 border-text object-cover shadow-brutal-sm">
                        </div>
                    <?php else: ?>
                        <img id="large_photo_preview" alt="Preview foto besar"
                            class="mb-3 hidden h-[378px] w-[265px] rounded border-4 border-text object-cover shadow-brutal-sm">
                    <?php endif; ?>

                    <input id="large_photo" type="file" name="large_photo" accept="image/jpeg,image/png,image/webp"
                        class="block w-full text-sm text-text-body file:mr-3 file:py-2 file:px-4 file:border-4 file:border-text file:bg-primary file:text-text-invert file:font-black file:uppercase file:text-xs file:tracking-wider file:cursor-pointer file:hover:bg-primary-hover file:transition-colors cursor-pointer"
                        <?= $registration === null ? 'required' : '' ?>>
                </div>
            </div>
        </div>

        <!-- ============================================ -->
        <!-- TOMBOL SUBMIT                                -->
        <!-- ============================================ -->
        <div
            class="pt-4 flex flex-col sm:flex-row items-center gap-4 border-t-2 border-dashed border-border-strong mt-8">

            <!-- Tombol Kembali -->
            <a href="/registration/all" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3
              bg-surface text-text border-4 border-text shadow-brutal-sm
              font-black uppercase text-sm tracking-wider
              hover:translate-x-[3px] hover:translate-y-[3px] hover:shadow-none hover:bg-surface-hover
              transition-all duration-150">
                <i data-lucide="arrow-left" class="w-5 h-5"></i>
                <span>Kembali</span>
            </a>

            <!-- Tombol Simpan / Submit -->
            <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-3
                   bg-cta text-cta-text border-4 border-text shadow-brutal
                   font-black uppercase text-sm tracking-wider
                   hover:translate-x-[4px] hover:translate-y-[4px] hover:shadow-none
                   transition-all duration-150 sm:ml-auto">
                <i data-lucide="save" class="w-5 h-5"></i>
                <span><?= $registration === null ? 'Kirim Pendaftaran' : 'Simpan Perubahan' ?></span>
            </button>

        </div>

    </form>
</div>

<!-- ============================================ -->
<!-- JAVASCRIPT (TIDAK DIUBAH)                    -->
<!-- ============================================ -->
<script>
    document.getElementById('small_photo').addEventListener('change', function () {
        const file = this.files[0];
        const preview = document.getElementById('small_photo_preview');

        if (!file) return;
        if (!file.type.startsWith('image/')) return;

        preview.src = URL.createObjectURL(file);
        preview.classList.remove('hidden');
    });
</script>

<script>
    document.getElementById('large_photo').addEventListener('change', function () {
        const file = this.files[0];
        const preview = document.getElementById('large_photo_preview');

        if (!file) return;
        if (!file.type.startsWith('image/')) return;

        preview.src = URL.createObjectURL(file);
        preview.classList.remove('hidden');
    });
</script>

<script>
    const initialAddress = <?= json_encode(
        $initialAddress ?? null,
        JSON_HEX_TAG |
        JSON_HEX_APOS |
        JSON_HEX_AMP |
        JSON_HEX_QUOT
    ) ?>;
</script>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const provinceSelect = document.getElementById('province_code');
        const regencySelect = document.getElementById('regency_code');
        const districtSelect = document.getElementById('district_code');
        const villageSelect = document.getElementById('village_code');

        const endpoints = {
            provinces: '/api/administrative-areas/provinces',
            regencies: '/api/administrative-areas/regencies',
            districts: '/api/administrative-areas/districts',
            villages: '/api/administrative-areas/villages',
        };

        function resetSelect(select, placeholder) {
            select.replaceChildren(new Option(placeholder, ''));
            select.disabled = true;
        }

        async function loadAreas(select, url, placeholder) {
            resetSelect(select, 'Memuat data wilayah...');

            try {
                const response = await fetch(url);

                if (!response.ok) {
                    throw new Error('Gagal mengambil data wilayah.');
                }

                const areas = await response.json();

                if (!Array.isArray(areas)) {
                    throw new Error('Format data wilayah tidak valid.');
                }

                select.replaceChildren(new Option(placeholder, ''));

                for (const area of areas) {
                    select.add(new Option(area.name, area.code));
                }

                if (areas.length === 0) {
                    resetSelect(select, 'Data wilayah tidak tersedia');
                    return false;
                }

                select.disabled = false;
                return true;
            } catch (error) {
                console.error(error);
                resetSelect(select, 'Gagal memuat data wilayah');
                return false;
            }
        }

        async function loadProvinces() {
            return await loadAreas(
                provinceSelect,
                endpoints.provinces,
                'Pilih provinsi'
            );
        }

        async function initializeAddress() {
            const loaded = await loadProvinces();

            if (!loaded || !initialAddress) {
                return;
            }

            const {
                province_code,
                regency_code,
                district_code
            } = initialAddress;

            if (!province_code || !regency_code || !district_code) {
                return;
            }

            provinceSelect.value = province_code;

            if (provinceSelect.value !== province_code) {
                return;
            }

            const regenciesLoaded = await loadAreas(
                regencySelect,
                `${endpoints.regencies}?province_code=${encodeURIComponent(province_code)}`,
                'Pilih kabupaten/kota'
            );

            if (!regenciesLoaded) {
                return;
            }

            regencySelect.value = regency_code;

            if (regencySelect.value !== regency_code) {
                return;
            }

            const districtsLoaded = await loadAreas(
                districtSelect,
                `${endpoints.districts}?regency_code=${encodeURIComponent(regency_code)}`,
                'Pilih kecamatan'
            );

            if (!districtsLoaded) {
                return;
            }

            districtSelect.value = district_code;

            if (districtSelect.value !== district_code) {
                return;
            }

            const villagesLoaded = await loadAreas(
                villageSelect,
                `${endpoints.villages}?district_code=${encodeURIComponent(district_code)}`,
                'Pilih desa/kelurahan'
            );

            if (!villagesLoaded) {
                return;
            }

            const villageCode = initialAddress.village_code;

            if (villageCode) {
                villageSelect.value = villageCode;
            }
        }

        provinceSelect.addEventListener('change', async () => {
            resetSelect(regencySelect, 'Pilih kabupaten/kota');
            resetSelect(districtSelect, 'Pilih kecamatan terlebih dahulu');
            resetSelect(villageSelect, 'Pilih desa/kelurahan terlebih dahulu');

            if (!provinceSelect.value) {
                return;
            }

            await loadAreas(
                regencySelect,
                `${endpoints.regencies}?province_code=${encodeURIComponent(provinceSelect.value)}`,
                'Pilih kabupaten/kota'
            );
        });

        regencySelect.addEventListener('change', async () => {
            resetSelect(districtSelect, 'Pilih kecamatan');
            resetSelect(villageSelect, 'Pilih kecamatan terlebih dahulu');

            if (!regencySelect.value) {
                return;
            }

            await loadAreas(
                districtSelect,
                `${endpoints.districts}?regency_code=${encodeURIComponent(regencySelect.value)}`,
                'Pilih kecamatan'
            );
        });

        districtSelect.addEventListener('change', async () => {
            resetSelect(villageSelect, 'Pilih desa/kelurahan');

            if (!districtSelect.value) {
                return;
            }

            await loadAreas(
                villageSelect,
                `${endpoints.villages}?district_code=${encodeURIComponent(districtSelect.value)}`,
                'Pilih desa/kelurahan'
            );
        });

        initializeAddress();
    });
</script>