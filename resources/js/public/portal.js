/**
 * Portal Publik & Radar MBG Javascript Logic
 */

document.addEventListener('DOMContentLoaded', function () {
    const filterForm = document.getElementById('filterForm');
    const provSelect = document.getElementById('select-provinsi');
    const regencySelect = document.getElementById('select-regency');
    const districtSelect = document.getElementById('select-district');
    const instansiSelect = document.getElementById('select-instansi');

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
            trigger.className = 'custom-select-trigger w-full flex items-center justify-between px-3.5 py-2.5 rounded-lg border text-xs font-medium shadow-2xs transition select-none';
            trigger.dataset.for = selectId;
            container.appendChild(trigger);
        }

        // Buat Panel Dropdown (Diposisikan SELALU ke bawah dengan top-full mt-1)
        let menu = container.querySelector(`.custom-select-menu[data-for="${selectId}"]`);
        if (!menu) {
            menu = document.createElement('div');
            menu.className = 'custom-select-menu hidden absolute top-full left-0 right-0 mt-1 bg-white border border-slate-200 rounded-lg shadow-lg z-50 overflow-hidden';
            menu.dataset.for = selectId;
            container.appendChild(menu);
        }

        function updateTriggerState() {
            const selectedOption = select.options[select.selectedIndex];
            const isDisabled = select.disabled;

            trigger.disabled = isDisabled;
            if (isDisabled) {
                trigger.className = 'custom-select-trigger w-full flex items-center justify-between px-3.5 py-2.5 rounded-lg border border-slate-200 bg-slate-50 text-slate-400 cursor-not-allowed text-xs font-medium transition select-none';
            } else {
                trigger.className = 'custom-select-trigger w-full flex items-center justify-between px-3.5 py-2.5 rounded-lg border border-slate-300 bg-white text-slate-900 hover:border-slate-400 focus:outline-none focus:ring-1 focus:ring-[#071E49] text-xs font-medium shadow-2xs transition cursor-pointer select-none';
            }

            const label = (selectedOption && selectedOption.textContent.trim()) ? selectedOption.textContent.trim() : (placeholderText || 'Pilih');
            trigger.innerHTML = `
                <span class="truncate pr-2">${label}</span>
                <svg class="w-4 h-4 text-slate-400 shrink-0 transition-transform duration-150 ${!menu.classList.contains('hidden') ? 'rotate-180 text-[#071E49]' : ''}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            `;
        }

        function renderMenu(filterText = '') {
            menu.innerHTML = '';
            const options = Array.from(select.options);

            // Kotak pencarian jika item lebih dari 5
            if (options.length > 5) {
                const searchWrap = document.createElement('div');
                searchWrap.className = 'p-2 border-b border-slate-100 bg-slate-50/90 sticky top-0 z-10';
                const searchInput = document.createElement('input');
                searchInput.type = 'text';
                searchInput.placeholder = 'Ketik nama untuk mencari...';
                searchInput.value = filterText;
                searchInput.className = 'w-full px-3 py-1.5 text-xs rounded-md border border-slate-200 bg-white text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-[#071E49]';
                
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
                itemBtn.className = `w-full text-left px-3.5 py-2 text-xs flex items-center justify-between transition cursor-pointer ${
                    isSelected ? 'bg-slate-100 font-semibold text-[#071E49]' : 'text-slate-700 hover:bg-slate-50 hover:text-[#071E49] font-medium'
                }`;
                itemBtn.innerHTML = `
                    <span class="truncate pr-2">${text}</span>
                    ${isSelected ? '<svg class="w-3.5 h-3.5 text-[#071E49] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>' : ''}
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
                regencySelect.className = 'w-full px-3.5 py-2.5 rounded-lg border border-slate-300 bg-white text-xs font-medium text-slate-800 focus:outline-none focus:ring-1 focus:ring-[#071E49] transition';
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
                districtSelect.className = 'w-full px-3.5 py-2.5 rounded-lg border border-slate-300 bg-white text-xs font-medium text-slate-800 focus:outline-none focus:ring-1 focus:ring-[#071E49] transition';
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
                b.classList.remove('bg-[#071E49]', 'text-white', 'shadow-xs', 'shadow-2xs', 'font-bold', 'font-semibold', 'border-[#071E49]');
                b.classList.add('font-medium', 'text-slate-600', 'hover:text-slate-900', 'hover:bg-slate-200/60');
                const svg = b.querySelector('svg');
                if (svg) svg.classList.replace('text-emerald-400', 'text-slate-400');
            });

            this.classList.remove('font-medium', 'text-slate-600', 'hover:text-slate-900', 'hover:bg-slate-200/60');
            this.classList.add('font-semibold', 'bg-[#071E49]', 'text-white', 'shadow-2xs');
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
        regencySelect.className = 'w-full px-3.5 py-2.5 rounded-lg border border-slate-200 bg-slate-50 text-xs font-medium text-slate-400 focus:outline-none transition';
        resetDistricts();
    }

    function resetDistricts() {
        districtSelect.innerHTML = '<option value="">Pilih kota/kabupaten dahulu</option>';
        districtSelect.disabled = true;
        districtSelect.className = 'w-full px-3.5 py-2.5 rounded-lg border border-slate-200 bg-slate-50 text-xs font-medium text-slate-400 focus:outline-none transition';
        resetInstansi();
    }

    function resetInstansi() {
        instansiSelect.innerHTML = '<option value="">Pilih kecamatan terlebih dahulu</option>';
        instansiSelect.disabled = true;
        instansiSelect.className = 'w-full pl-10 pr-8 py-2.5 rounded-lg border border-slate-200 bg-slate-50 text-slate-400 cursor-not-allowed text-xs font-medium shadow-2xs transition';
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
                    instansiSelect.className = 'w-full pl-10 pr-8 py-2.5 rounded-lg border border-slate-200 bg-slate-50 text-slate-400 cursor-not-allowed text-xs font-medium shadow-2xs transition';
                } else {
                    instansiSelect.disabled = false;
                    instansiSelect.className = 'w-full pl-10 pr-8 py-2.5 rounded-lg border border-slate-300 bg-white text-slate-900 text-xs font-semibold focus:outline-none focus:ring-1 focus:ring-[#071E49] shadow-2xs transition';
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

    // Fungsi sinkronisasi tahap aktif (mendukung Desktop Cards & Mobile Step Tabs)
    function setActiveTahap(tahap) {
        const tahapStr = String(tahap);
        const desktopCard = document.querySelector(`.desktop-tahap-card[data-tahap="${tahapStr}"]`);
        const tahapDetailPanel = document.getElementById('tahapDetailPanel');
        if (!desktopCard || !tahapDetailPanel) return;

        // 1. Sinkronisasi status aktif Desktop Cards
        document.querySelectorAll('.desktop-tahap-card').forEach(c => {
            const isCurrent = c.dataset.tahap === tahapStr;
            if (isCurrent) {
                c.classList.remove('border-slate-200/90', 'bg-white');
                c.classList.add('active', 'ring-1', 'ring-[#071E49]', 'border-[#071E49]', 'bg-slate-50/70');
                const badge = c.querySelector('.tahap-badge');
                if (badge) {
                    badge.classList.remove('bg-slate-100', 'text-slate-700');
                    badge.classList.add('bg-[#071E49]', 'text-white');
                }
                const footerLink = c.querySelector('.tahap-footer');
                if (footerLink) {
                    footerLink.classList.remove('text-slate-600');
                    footerLink.classList.add('text-[#071E49]');
                }
            } else {
                c.classList.remove('active', 'ring-1', 'ring-[#071E49]', 'border-[#071E49]', 'bg-slate-50/70');
                c.classList.add('border-slate-200/90', 'bg-white');
                const badge = c.querySelector('.tahap-badge');
                if (badge) {
                    badge.classList.remove('bg-[#071E49]', 'text-white');
                    badge.classList.add('bg-slate-100', 'text-slate-700');
                }
                const footerLink = c.querySelector('.tahap-footer');
                if (footerLink) {
                    footerLink.classList.remove('text-[#071E49]');
                    footerLink.classList.add('text-slate-600');
                }
            }
        });

        // 2. Sinkronisasi status aktif Mobile Step Tabs
        document.querySelectorAll('.mobile-tahap-tab').forEach(tab => {
            const isCurrent = tab.dataset.tahap === tahapStr;
            const num = tab.querySelector('.tab-num');
            const time = tab.querySelector('.tab-time');
            if (isCurrent) {
                tab.classList.remove('bg-transparent', 'text-slate-600', 'hover:bg-slate-200/70');
                tab.classList.add('active', 'bg-[#071E49]', 'text-white', 'shadow-xs');
                if (num) {
                    num.classList.remove('bg-slate-200', 'text-slate-600');
                    num.classList.add('bg-white/20', 'text-white');
                }
                if (time) {
                    time.classList.remove('text-slate-400');
                    time.classList.add('text-slate-200');
                }
            } else {
                tab.classList.remove('active', 'bg-[#071E49]', 'text-white', 'shadow-xs');
                tab.classList.add('bg-transparent', 'text-slate-600', 'hover:bg-slate-200/70');
                if (num) {
                    num.classList.remove('bg-white/20', 'text-white');
                    num.classList.add('bg-slate-200', 'text-slate-600');
                }
                if (time) {
                    time.classList.remove('text-slate-200');
                    time.classList.add('text-slate-400');
                }
            }
        });

        // 3. Update Konten Detail Panel
        const title = desktopCard.dataset.title;
        const subtitle = desktopCard.dataset.subtitle;
        const photo = desktopCard.dataset.photo;
        const photoCaption = desktopCard.dataset.photoCaption;
        const desc = desktopCard.dataset.desc;
        const waktu = desktopCard.dataset.waktu;
        const kondisi = desktopCard.dataset.kondisi;
        const bahan = desktopCard.dataset.bahan;
        const petugas = desktopCard.dataset.petugas;

        const tahapDetailBadge = document.getElementById('tahapDetailBadge');
        const tahapDetailEyebrow = document.getElementById('tahapDetailEyebrow');
        const tahapDetailTitle = document.getElementById('tahapDetailTitle');
        const tahapDetailSubtitle = document.getElementById('tahapDetailSubtitle');
        const tahapDetailImg = document.getElementById('tahapDetailImg');
        const tahapDetailCaption = document.getElementById('tahapDetailCaption');
        const tahapDetailDesc = document.getElementById('tahapDetailDesc');
        const tahapDetailWaktu = document.getElementById('tahapDetailWaktu');
        const tahapDetailKondisi = document.getElementById('tahapDetailKondisi');
        const tahapDetailBahan = document.getElementById('tahapDetailBahan');
        const tahapDetailPetugas = document.getElementById('tahapDetailPetugas');

        const eyebrowMap = {
            '1': 'Titik Pasokan Bahan',
            '2': 'Dapur Higienis SPPG',
            '3': 'Quality Control & Uji Mutu',
            '4': 'Hilir & Serah Terima Sekolah'
        };

        if (tahapDetailBadge) tahapDetailBadge.textContent = `Tahap 0${tahapStr}`;
        if (tahapDetailEyebrow) tahapDetailEyebrow.textContent = eyebrowMap[tahapStr] || 'Proses MBG';
        if (tahapDetailTitle) tahapDetailTitle.textContent = title;
        if (tahapDetailSubtitle) tahapDetailSubtitle.textContent = subtitle;
        if (tahapDetailImg && photo) tahapDetailImg.src = photo;
        if (tahapDetailCaption && photoCaption) tahapDetailCaption.textContent = photoCaption;
        if (tahapDetailDesc) tahapDetailDesc.textContent = desc;
        if (tahapDetailWaktu) tahapDetailWaktu.textContent = waktu;
        if (tahapDetailKondisi) tahapDetailKondisi.textContent = kondisi;
        if (tahapDetailBahan) tahapDetailBahan.textContent = bahan;
        if (tahapDetailPetugas) tahapDetailPetugas.textContent = petugas;

        // 4. Update visibilitas galeri multi-foto sesuai tahap yang aktif
        ['1', '2', '3', '4'].forEach(t => {
            const gal = document.getElementById(`tahap${t}MultiGallery`);
            if (gal) {
                if (t === tahapStr) {
                    gal.classList.remove('hidden');
                } else {
                    gal.classList.add('hidden');
                }
            }
        });

        // 5. Animasi lembut panel
        tahapDetailPanel.classList.add('ring-1', 'ring-slate-300');
        setTimeout(() => {
            tahapDetailPanel.classList.remove('ring-1', 'ring-slate-300');
        }, 250);
    }
    window.setActiveTahap = setActiveTahap;

    // Pasang listener pada kartu desktop
    document.querySelectorAll('.desktop-tahap-card').forEach(card => {
        card.addEventListener('click', function () {
            setActiveTahap(this.dataset.tahap);
        });
    });

    // Pasang listener pada tab mobile
    document.querySelectorAll('.mobile-tahap-tab').forEach(tab => {
        tab.addEventListener('click', function () {
            setActiveTahap(this.dataset.tahap);
        });
    });
});

// Pilih & Buka Foto dari Strip Galeri Tahapan (Tahap 1, 2, 3, 4)
function selectTahapPhoto(tahap, idx, url, caption, btn) {
    const mainImg = document.getElementById('tahapDetailImg');
    const cap = document.getElementById('tahapDetailCaption');
    if (mainImg) mainImg.src = url;
    if (cap) cap.textContent = caption;

    document.querySelectorAll(`.tahap${tahap}-thumb-btn`).forEach(b => {
        b.classList.remove('ring-2', 'ring-[#071E49]', 'border-[#071E49]');
        b.classList.add('border-slate-200');
    });
    if (btn) {
        btn.classList.remove('border-slate-200');
        btn.classList.add('ring-2', 'ring-[#071E49]', 'border-[#071E49]');
    }

    const gallery = (window.tahapGalleries && window.tahapGalleries[tahap]) ? window.tahapGalleries[tahap] : null;
    openPhotoModal(url, caption, gallery, idx);
}
window.selectTahapPhoto = selectTahapPhoto;

// Backward compatibility untuk pemanggilan lama
function selectTahap1Photo(url, caption, btn) {
    selectTahapPhoto('1', 0, url, caption, btn);
}
window.selectTahap1Photo = selectTahap1Photo;

// Buka Modal Foto untuk Tahapan yang Sedang Aktif
function openActiveTahapPhoto() {
    const activeCard = document.querySelector('.desktop-tahap-card.active') || document.querySelector('.mobile-tahap-tab.active');
    const tahap = activeCard ? activeCard.dataset.tahap : '1';
    const gallery = (window.tahapGalleries && window.tahapGalleries[tahap]) ? window.tahapGalleries[tahap] : [];
    const currentSrc = document.getElementById('tahapDetailImg')?.src || '';
    const currentCap = document.getElementById('tahapDetailCaption')?.innerText || '';
    let idx = 0;
    if (gallery.length > 0) {
        const found = gallery.findIndex(g => g.url === currentSrc);
        if (found !== -1) idx = found;
    }
    openPhotoModal(currentSrc, currentCap, gallery, idx);
}
window.openActiveTahapPhoto = openActiveTahapPhoto;

// Tampilkan Loading State dengan Animasi Jelas
function showLoadingState() {
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
}
window.showLoadingState = showLoadingState;

// State Gallery untuk Lightbox Modal dengan Tombol Geser (Slider Navigasi)
let currentGallery = [];
let currentGalleryIndex = 0;

function updateModalView() {
    const img = document.getElementById('modalImg');
    const cap = document.getElementById('modalCaption');
    const counter = document.getElementById('modalCounter');
    const prevBtn = document.getElementById('modalPrevBtn');
    const nextBtn = document.getElementById('modalNextBtn');

    if (!currentGallery || currentGallery.length === 0) return;
    const current = currentGallery[currentGalleryIndex];
    if (!current) return;

    if (img) img.src = current.url;
    if (cap) cap.innerText = current.caption || 'Foto Verifikasi Porsi';

    if (currentGallery.length > 1) {
        if (counter) {
            counter.innerText = `${currentGalleryIndex + 1} / ${currentGallery.length}`;
            counter.classList.remove('hidden');
        }
        if (prevBtn) prevBtn.classList.remove('hidden');
        if (nextBtn) nextBtn.classList.remove('hidden');
    } else {
        if (counter) counter.classList.add('hidden');
        if (prevBtn) prevBtn.classList.add('hidden');
        if (nextBtn) nextBtn.classList.add('hidden');
    }
}

// Lightbox Modal Foto dengan Dukungan Slider Navigasi
function openPhotoModal(imgSrc, caption, galleryList = null, startIndex = 0) {
    const modal = document.getElementById('photoModal');
    const img = document.getElementById('modalImg');
    const cap = document.getElementById('modalCaption');
    if (!modal || !img || !cap) return;

    if (Array.isArray(galleryList) && galleryList.length > 0) {
        currentGallery = galleryList;
        currentGalleryIndex = Math.max(0, Math.min(startIndex, galleryList.length - 1));
    } else {
        currentGallery = [{ url: imgSrc, caption: caption || 'Foto Verifikasi Porsi' }];
        currentGalleryIndex = 0;
    }

    updateModalView();

    modal.classList.remove('hidden');
    setTimeout(() => {
        modal.classList.remove('opacity-0');
    }, 10);
}
window.openPhotoModal = openPhotoModal;

function prevPhotoModal(e) {
    if (e) e.stopPropagation();
    if (!currentGallery || currentGallery.length <= 1) return;
    currentGalleryIndex = (currentGalleryIndex - 1 + currentGallery.length) % currentGallery.length;
    updateModalView();
}
window.prevPhotoModal = prevPhotoModal;

function nextPhotoModal(e) {
    if (e) e.stopPropagation();
    if (!currentGallery || currentGallery.length <= 1) return;
    currentGalleryIndex = (currentGalleryIndex + 1) % currentGallery.length;
    updateModalView();
}
window.nextPhotoModal = nextPhotoModal;

function closePhotoModal() {
    const modal = document.getElementById('photoModal');
    if (!modal) return;

    modal.classList.add('opacity-0');
    setTimeout(() => {
        modal.classList.add('hidden');
    }, 150);
}
window.closePhotoModal = closePhotoModal;

// Navigasi Keyboard: Panah Kiri (Prev), Panah Kanan (Next), Escape (Tutup)
document.addEventListener('keydown', function (e) {
    const modal = document.getElementById('photoModal');
    if (!modal || modal.classList.contains('hidden')) return;

    if (e.key === 'ArrowLeft') {
        prevPhotoModal();
    } else if (e.key === 'ArrowRight') {
        nextPhotoModal();
    } else if (e.key === 'Escape') {
        closePhotoModal();
    }
});

// Dukungan Gestur Geser Layar Sentuh (Mobile Touch Swipe)
let touchStartX = 0;
let touchEndX = 0;
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('photoModal');
    if (modal) {
        modal.addEventListener('touchstart', function (e) {
            touchStartX = e.changedTouches[0].screenX;
        }, { passive: true });

        modal.addEventListener('touchend', function (e) {
            touchEndX = e.changedTouches[0].screenX;
            const diff = touchEndX - touchStartX;
            if (Math.abs(diff) > 40) {
                if (diff > 0) {
                    prevPhotoModal();
                } else {
                    nextPhotoModal();
                }
            }
        }, { passive: true });
    }
});
