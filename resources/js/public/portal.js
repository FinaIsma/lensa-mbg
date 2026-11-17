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

    // ========================================================
    // KOMPONEN CUSTOM DROPDOWN (SELALU BUKA KE BAWAH & MUDAH DIPILIH)
    // ========================================================
    function initCustomSelect(selectId, placeholderText) {
        const select = document.getElementById(selectId);
        if (!select) return;

        let container = select.closest('.custom-select-wrapper');
        if (!container) {
            container = select.parentElement;
            container.classList.add('relative');
        }

        // Sembunyikan select bawaan browser secara visual
        select.style.display = 'none';

        // Buat Tombol Trigger Dropdown
        let trigger = container.querySelector(`.custom-select-trigger[data-for="${selectId}"]`);
        if (!trigger) {
            trigger = document.createElement('button');
            trigger.type = 'button';
            trigger.className = 'custom-select-trigger w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl border text-xs font-semibold shadow-2xs transition select-none';
            trigger.dataset.for = selectId;
            container.appendChild(trigger);
        }

        // Buat Panel Dropdown (Diposisikan SELALU ke bawah dengan top-full mt-1.5)
        let menu = container.querySelector(`.custom-select-menu[data-for="${selectId}"]`);
        if (!menu) {
            menu = document.createElement('div');
            menu.className = 'custom-select-menu hidden absolute top-full left-0 right-0 mt-1.5 bg-white border border-slate-200/90 rounded-2xl shadow-xl z-50 overflow-hidden';
            menu.dataset.for = selectId;
            container.appendChild(menu);
        }

        function updateTriggerState() {
            const selectedOption = select.options[select.selectedIndex];
            const isDisabled = select.disabled;

            trigger.disabled = isDisabled;
            if (isDisabled) {
                trigger.className = 'custom-select-trigger w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-400 cursor-not-allowed text-xs font-medium shadow-2xs transition select-none';
            } else {
                trigger.className = 'custom-select-trigger w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-slate-800 hover:border-blue-400 focus:ring-2 focus:ring-blue-500 text-xs font-semibold shadow-2xs transition cursor-pointer select-none';
            }

            const label = (selectedOption && selectedOption.textContent.trim()) ? selectedOption.textContent.trim() : (placeholderText || 'Pilih');
            trigger.innerHTML = `
                <span class="truncate pr-2">${label}</span>
                <svg class="w-4 h-4 text-slate-400 shrink-0 transition-transform duration-200 ${!menu.classList.contains('hidden') ? 'rotate-180 text-blue-600' : ''}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            `;
        }

        function renderMenu(filterText = '') {
            menu.innerHTML = '';
            const options = Array.from(select.options);

            // Kotak pencarian jika item lebih dari 5 (sangat praktis untuk 30+ kecamatan)
            if (options.length > 5) {
                const searchWrap = document.createElement('div');
                searchWrap.className = 'p-2 border-b border-slate-100 bg-slate-50/80 sticky top-0 z-10';
                const searchInput = document.createElement('input');
                searchInput.type = 'text';
                searchInput.placeholder = 'Ketik nama untuk mencari...';
                searchInput.value = filterText;
                searchInput.className = 'w-full px-3 py-1.5 text-xs rounded-xl border border-slate-200 bg-white text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500';
                
                searchInput.addEventListener('input', function(e) {
                    const q = e.target.value.toLowerCase();
                    renderMenu(q);
                    const newSearch = menu.querySelector('input');
                    if (newSearch) {
                        newSearch.focus();
                        newSearch.setSelectionRange(newSearch.value.length, newSearch.value.length);
                    }
                });
                searchInput.addEventListener('click', (e) => e.stopPropagation());
                searchWrap.appendChild(searchInput);
                menu.appendChild(searchWrap);
            }

            const listWrap = document.createElement('div');
            listWrap.className = 'max-h-56 overflow-y-auto py-1 divide-y divide-slate-50 text-xs';

            let count = 0;
            options.forEach((opt, idx) => {
                if (opt.disabled && !opt.value) return; // Skip placeholder disabled
                const text = opt.textContent.trim();
                if (filterText && !text.toLowerCase().includes(filterText)) {
                    return;
                }
                count++;

                const isSelected = (select.selectedIndex === idx);
                const itemBtn = document.createElement('button');
                itemBtn.type = 'button';
                itemBtn.className = `w-full text-left px-3.5 py-2.5 text-xs flex items-center justify-between transition cursor-pointer ${
                    isSelected ? 'bg-blue-50 font-bold text-[#071E49]' : 'text-slate-700 hover:bg-slate-50 hover:text-blue-600 font-medium'
                }`;
                itemBtn.innerHTML = `
                    <span class="truncate pr-2">${text}</span>
                    ${isSelected ? '<svg class="w-3.5 h-3.5 text-blue-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>' : ''}
                `;

                itemBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    select.selectedIndex = idx;
                    select.value = opt.value;
                    closeAllCustomSelects();
                    updateTriggerState();
                    select.dispatchEvent(new Event('change', { bubbles: true }));
                });

                listWrap.appendChild(itemBtn);
            });

            if (count === 0) {
                const empty = document.createElement('div');
                empty.className = 'px-3.5 py-3 text-xs text-slate-400 text-center italic';
                empty.textContent = 'Tidak ada pilihan yang cocok';
                listWrap.appendChild(empty);
            }

            menu.appendChild(listWrap);
        }

        trigger.addEventListener('click', function(e) {
            e.stopPropagation();
            if (trigger.disabled) return;
            const isCurrentlyOpen = !menu.classList.contains('hidden');
            closeAllCustomSelects();
            if (!isCurrentlyOpen) {
                renderMenu('');
                menu.classList.remove('hidden');
                updateTriggerState();
                const searchInput = menu.querySelector('input');
                if (searchInput) {
                    setTimeout(() => searchInput.focus(), 50);
                }
            }
        });

        // Pantau perubahan pada select element asli via MutationObserver
        const observer = new MutationObserver(() => {
            updateTriggerState();
            if (!menu.classList.contains('hidden')) {
                renderMenu();
            }
        });
        observer.observe(select, { childList: true, attributes: true, subtree: true });

        updateTriggerState();
    }

    function closeAllCustomSelects() {
        document.querySelectorAll('.custom-select-menu').forEach(m => m.classList.add('hidden'));
        document.querySelectorAll('.custom-select-trigger').forEach(t => {
            const svg = t.querySelector('svg');
            if (svg) svg.classList.remove('rotate-180', 'text-blue-600');
        });
    }

    document.addEventListener('click', function(e) {
        if (!e.target.closest('.custom-select-wrapper')) {
            closeAllCustomSelects();
        }
    });

    // Inisialisasi untuk ke-4 dropdown pencarian
    initCustomSelect('select-provinsi', 'Pilih Provinsi');
    initCustomSelect('select-regency', 'Pilih provinsi dahulu');
    initCustomSelect('select-district', 'Pilih kota/kabupaten dahulu');
    initCustomSelect('select-instansi', 'Pilih kecamatan terlebih dahulu');

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

    // Event Interaktif Klik Kartu Tahapan (Petualangan Bekal)
    const tahapCards = document.querySelectorAll('.tahap-card');
    const tahapDetailPanel = document.getElementById('tahapDetailPanel');
    const tahapDetailBadge = document.getElementById('tahapDetailBadge');
    const tahapDetailTitle = document.getElementById('tahapDetailTitle');
    const tahapDetailSubtitle = document.getElementById('tahapDetailSubtitle');
    const tahapDetailImg = document.getElementById('tahapDetailImg');
    const tahapDetailCaption = document.getElementById('tahapDetailCaption');
    const tahapDetailDesc = document.getElementById('tahapDetailDesc');
    const tahapDetailWaktu = document.getElementById('tahapDetailWaktu');
    const tahapDetailKondisi = document.getElementById('tahapDetailKondisi');
    const tahapDetailBahan = document.getElementById('tahapDetailBahan');
    const tahapDetailPetugas = document.getElementById('tahapDetailPetugas');

    if (tahapCards.length > 0 && tahapDetailPanel) {
        tahapCards.forEach(card => {
            card.addEventListener('click', function () {
                const tahap = this.dataset.tahap;
                const title = this.dataset.title;
                const subtitle = this.dataset.subtitle;
                const photo = this.dataset.photo;
                const photoCaption = this.dataset.photoCaption;
                const desc = this.dataset.desc;
                const waktu = this.dataset.waktu;
                const kondisi = this.dataset.kondisi;
                const bahan = this.dataset.bahan;
                const petugas = this.dataset.petugas;

                // Reset kelas aktif pada semua kartu
                tahapCards.forEach(c => {
                    c.classList.remove('active', 'ring-2', 'ring-[#071E49]', 'bg-slate-50/50');
                    const footerLink = c.querySelector('.mt-3');
                    if (footerLink) {
                        footerLink.classList.remove('text-blue-700');
                        footerLink.classList.add('text-slate-600');
                    }
                });

                // Tambahkan kelas aktif pada kartu yang diklik
                this.classList.add('active', 'ring-2', 'ring-[#071E49]', 'bg-slate-50/50');
                const activeFooterLink = this.querySelector('.mt-3');
                if (activeFooterLink) {
                    activeFooterLink.classList.remove('text-slate-600');
                    activeFooterLink.classList.add('text-blue-700');
                }

                // Update konten detail panel
                if (tahapDetailBadge) tahapDetailBadge.textContent = `Tahap ${tahap}`;
                if (tahapDetailTitle) tahapDetailTitle.textContent = `Detail Perjalanan & Dokumentasi Riil: ${title}`;
                if (tahapDetailSubtitle) tahapDetailSubtitle.textContent = subtitle;
                if (tahapDetailImg && photo) tahapDetailImg.src = photo;
                if (tahapDetailCaption && photoCaption) tahapDetailCaption.textContent = photoCaption;
                if (tahapDetailDesc) tahapDetailDesc.textContent = desc;
                if (tahapDetailWaktu) tahapDetailWaktu.textContent = waktu;
                if (tahapDetailKondisi) tahapDetailKondisi.textContent = kondisi;
                if (tahapDetailBahan) tahapDetailBahan.textContent = bahan;
                if (tahapDetailPetugas) tahapDetailPetugas.textContent = petugas;

                // Animasi lembut saat berganti konten
                tahapDetailPanel.classList.add('ring-2', 'ring-blue-100');
                setTimeout(() => {
                    tahapDetailPanel.classList.remove('ring-2', 'ring-blue-100');
                }, 300);
            });
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
