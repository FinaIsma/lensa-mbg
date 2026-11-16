/**
 * Portal Publik & Radar MBG Javascript Logic
 */

document.addEventListener('DOMContentLoaded', function () {
    const filterForm = document.getElementById('filterForm');
    const provSelect = document.getElementById('select-provinsi');
    const regencySelect = document.getElementById('select-regency');
    const districtSelect = document.getElementById('select-district');
    const instansiSelect = document.getElementById('select-instansi');
    const submitBtn = document.getElementById('submitBtn');

    if (!provSelect) return;

    const currentProv = filterForm?.dataset?.provinsi || '';
    const currentKab = filterForm?.dataset?.kabupaten || '';
    const currentKec = filterForm?.dataset?.kecamatan || '';
    const currentDest = filterForm?.dataset?.destination || '';

    // 1. Muat Daftar Provinsi dari API
    fetch('/api/wilayah/provinces')
        .then(res => res.json())
        .then(data => {
            provSelect.innerHTML = '<option value="">Pilih Provinsi</option>';
            data.forEach(item => {
                const opt = document.createElement('option');
                opt.value = item.name;
                opt.dataset.id = item.id;
                opt.textContent = item.name;
                if (currentProv && (currentProv.toLowerCase() === item.name.toLowerCase())) {
                    opt.selected = true;
                }
                provSelect.appendChild(opt);
            });

            // Jika ada query param dari URL, muat kota/kabupaten
            if (currentProv && provSelect.selectedOptions[0]?.dataset?.id) {
                loadRegencies(provSelect.selectedOptions[0].dataset.id, currentKab, currentKec);
            }
        })
        .catch(err => console.error('Error load provinces:', err));

    // Event Provinsi Change -> Muat Kota / Kabupaten
    provSelect.addEventListener('change', function () {
        const selectedOpt = this.options[this.selectedIndex];
        const provId = selectedOpt ? selectedOpt.dataset.id : null;
        const provName = this.value;

        resetRegencies();
        resetDistricts();
        resetInstansi();

        if (!provId) {
            return;
        }

        loadRegencies(provId, '', '');
    });

    // Helper Load Regencies
    function loadRegencies(provId, preselectKab, preselectKec) {
        regencySelect.innerHTML = '<option value="">Memuat...</option>';
        regencySelect.disabled = true;

        fetch(`/api/wilayah/regencies/${provId}`)
            .then(res => res.json())
            .then(data => {
                regencySelect.disabled = false;
                regencySelect.className = 'w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-xs font-medium text-slate-800 focus:ring-2 focus:ring-blue-500 transition';
                regencySelect.innerHTML = '<option value="">Pilih Kota / Kabupaten</option>';
                data.forEach(item => {
                    const opt = document.createElement('option');
                    opt.value = item.name;
                    opt.dataset.id = item.id;
                    opt.textContent = item.name;
                    if (preselectKab && (preselectKab.toLowerCase() === item.name.toLowerCase())) {
                        opt.selected = true;
                    }
                    regencySelect.appendChild(opt);
                });

                if (preselectKab && regencySelect.selectedOptions[0]?.dataset?.id) {
                    loadDistricts(regencySelect.selectedOptions[0].dataset.id, preselectKec);
                }
            })
            .catch(() => {
                resetRegencies();
            });
    }

    // Event Regency Change -> Muat Kecamatan
    regencySelect.addEventListener('change', function () {
        const selectedOpt = this.options[this.selectedIndex];
        const regencyId = selectedOpt ? selectedOpt.dataset.id : null;

        resetDistricts();
        resetInstansi();

        if (!regencyId) {
            return;
        }

        loadDistricts(regencyId, '');
    });

    // Helper Load Districts
    function loadDistricts(regencyId, preselectKec) {
        districtSelect.innerHTML = '<option value="">Memuat...</option>';
        districtSelect.disabled = true;

        fetch(`/api/wilayah/districts/${regencyId}`)
            .then(res => res.json())
            .then(data => {
                districtSelect.disabled = false;
                districtSelect.className = 'w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-xs font-medium text-slate-800 focus:ring-2 focus:ring-blue-500 transition';
                districtSelect.innerHTML = '<option value="">Pilih Kecamatan</option>';
                data.forEach(item => {
                    const opt = document.createElement('option');
                    opt.value = item.name;
                    opt.dataset.id = item.id;
                    opt.textContent = item.name;
                    if (preselectKec && (preselectKec.toLowerCase() === item.name.toLowerCase())) {
                        opt.selected = true;
                    }
                    districtSelect.appendChild(opt);
                });

                // Jika ada preselect kecamatan (dari filter form refresh)
                if (preselectKec && districtSelect.value) {
                    filterInstansiDropdown(provSelect.value, regencySelect.value, districtSelect.value);
                }
            })
            .catch(() => {
                resetDistricts();
            });
    }

    // Event District Change -> Filter Instansi
    districtSelect.addEventListener('change', function () {
        const provName = provSelect.value;
        const regencyName = regencySelect.value;
        const districtName = this.value;

        if (!districtName) {
            resetInstansi();
            return;
        }

        filterInstansiDropdown(provName, regencyName, districtName);
    });

    // Event Submit Form -> Tampilkan Loading Animation
    if (filterForm) {
        filterForm.addEventListener('submit', function () {
            if (instansiSelect && instansiSelect.value) {
                showLoadingState();
            }
        });
    }

    // Auto load data saat sekolah / instansi dipilih
    if (instansiSelect) {
        instansiSelect.addEventListener('change', function () {
            if (this.value) {
                showLoadingState();
                filterForm.submit();
            }
        });
    }

    // Event Klik Tombol Jadwal (Hari Ini, Kemarin, 7 Hari Lalu)
    const jadwalBtns = document.querySelectorAll('.jadwal-btn');
    const inputJadwal = document.getElementById('inputJadwal');

    jadwalBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            const selectedJadwal = this.dataset.jadwal;
            if (inputJadwal) {
                inputJadwal.value = selectedJadwal;
            }

            // Update class visual aktif pada tombol
            jadwalBtns.forEach(b => {
                b.classList.remove('bg-[#071E49]', 'text-white', 'shadow-xs', 'font-bold');
                b.classList.add('font-medium', 'text-slate-600', 'hover:text-slate-900', 'hover:bg-slate-100');
                const svg = b.querySelector('svg');
                if (svg) svg.classList.replace('text-emerald-400', 'text-slate-400');
            });

            this.classList.remove('font-medium', 'text-slate-600', 'hover:text-slate-900', 'hover:bg-slate-100');
            this.classList.add('font-bold', 'bg-[#071E49]', 'text-white', 'shadow-xs');
            const activeSvg = this.querySelector('svg');
            if (activeSvg) activeSvg.classList.replace('text-slate-400', 'text-emerald-400');

            // Jika sekolah sudah dipilih, auto submit form untuk muat data jadwal
            if (instansiSelect && instansiSelect.value && filterForm) {
                showLoadingState();
                filterForm.submit();
            }
        });
    });

    function resetRegencies() {
        regencySelect.innerHTML = '<option value="">Pilih provinsi dahulu</option>';
        regencySelect.disabled = true;
        regencySelect.className = 'w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-xs font-medium text-slate-500 focus:ring-2 focus:ring-blue-500 transition';
        resetDistricts();
    }

    function resetDistricts() {
        districtSelect.innerHTML = '<option value="">Pilih kota/kabupaten dahulu</option>';
        districtSelect.disabled = true;
        districtSelect.className = 'w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-xs font-medium text-slate-500 focus:ring-2 focus:ring-blue-500 transition';
        resetInstansi();
    }

    function resetInstansi() {
        instansiSelect.innerHTML = '<option value="">Pilih kecamatan terlebih dahulu</option>';
        instansiSelect.disabled = true;
        instansiSelect.className = 'w-full pl-10 pr-8 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-400 cursor-not-allowed text-xs font-bold shadow-2xs transition';
    }

    // Helper untuk memfilter isi dropdown Instansi via AJAX
    function filterInstansiDropdown(prov, kab, kec) {
        if (!kec) {
            resetInstansi();
            return;
        }

        let params = new URLSearchParams();
        if (prov) params.append('provinsi', prov);
        if (kab) params.append('kabupaten_kota', kab);
        if (kec) params.append('kecamatan', kec);

        instansiSelect.disabled = true;
        instansiSelect.innerHTML = '<option value="">Memuat instansi sasaran...</option>';

        fetch(`/api/public/destinations?${params.toString()}`)
            .then(res => res.json())
            .then(data => {
                instansiSelect.innerHTML = '<option value="">-- Pilih Sekolah / Instansi Sasaran --</option>';
                if (!data || data.length === 0) {
                    const opt = document.createElement('option');
                    opt.value = '';
                    opt.textContent = 'Tidak ada instansi terdaftar di wilayah ini';
                    opt.disabled = true;
                    instansiSelect.appendChild(opt);
                    instansiSelect.disabled = true;
                    instansiSelect.className = 'w-full pl-10 pr-8 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-400 cursor-not-allowed text-xs font-bold shadow-2xs transition';
                } else {
                    instansiSelect.disabled = false;
                    instansiSelect.className = 'w-full pl-10 pr-8 py-2.5 rounded-xl border border-slate-300 bg-white text-slate-800 text-xs font-bold focus:ring-2 focus:ring-blue-500 shadow-2xs transition';
                    data.forEach(item => {
                        const opt = document.createElement('option');
                        opt.value = item.id;
                        opt.textContent = `${item.nama} (${item.sppg_nama})`;
                        if (currentDest && String(currentDest) === String(item.id)) {
                            opt.selected = true;
                        }
                        instansiSelect.appendChild(opt);
                    });
                }
            })
            .catch(err => {
                console.error('Error load destinations:', err);
                resetInstansi();
            });
    }
});

// Tampilkan Loading State dengan Animasi Jelas
window.showLoadingState = function () {
    const emptyState = document.getElementById('emptyState');
    const loadingState = document.getElementById('loadingState');
    const transparencyResult = document.getElementById('transparencyResult');
    const nationalStats = document.getElementById('nationalStats');
    const transparencySteps = document.getElementById('transparencySteps');
    const submitBtn = document.getElementById('submitBtn');

    if (emptyState) emptyState.classList.add('hidden');
    if (transparencyResult) transparencyResult.classList.add('hidden');
    if (nationalStats) nationalStats.classList.add('hidden');
    if (transparencySteps) transparencySteps.classList.add('hidden');
    if (loadingState) {
        loadingState.classList.remove('hidden');
    }
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.classList.add('opacity-80', 'cursor-not-allowed');
        submitBtn.innerHTML = `
            <svg class="w-4 h-4 text-emerald-400 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
            </svg>
            <span>Memuat Data...</span>
        `;
    }
};

// Lightbox Modal Foto
window.openPhotoModal = function (imgSrc, caption) {
    const modal = document.getElementById('photoModal');
    const img = document.getElementById('modalImg');
    const cap = document.getElementById('modalCaption');
    if (!modal || !img || !cap) return;

    img.src = imgSrc;
    cap.innerText = caption || 'Foto Verifikasi Porsi';
    modal.classList.remove('hidden');
    setTimeout(() => {
        modal.classList.remove('opacity-0');
    }, 10);
};

window.closePhotoModal = function () {
    const modal = document.getElementById('photoModal');
    if (!modal) return;

    modal.classList.add('opacity-0');
    setTimeout(() => {
        modal.classList.add('hidden');
    }, 200);
};
