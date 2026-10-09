// Indonesian Region API & Fallback Data
const REGION_API_BASE = 'https://emsifa.github.io/api-wilayah-indonesia/api';

// Fallback provinces in case of offline/network issues
const FALLBACK_PROVINCES = [
    { id: '11', name: 'ACEH' },
    { id: '12', name: 'SUMATERA UTARA' },
    { id: '13', name: 'SUMATERA BARAT' },
    { id: '14', name: 'RIAU' },
    { id: '15', name: 'JAMBI' },
    { id: '16', name: 'SUMATERA SELATAN' },
    { id: '17', name: 'BENGKULU' },
    { id: '18', name: 'LAMPUNG' },
    { id: '19', name: 'KEPULAUAN BANGKA BELITUNG' },
    { id: '21', name: 'KEPULAUAN RIAU' },
    { id: '31', name: 'DKI JAKARTA' },
    { id: '32', name: 'JAWA BARAT' },
    { id: '33', name: 'JAWA TENGAH' },
    { id: '34', name: 'DI YOGYAKARTA' },
    { id: '35', name: 'JAWA TIMUR' },
    { id: '36', name: 'BANTEN' },
    { id: '51', name: 'BALI' },
    { id: '52', name: 'NUSA TENGGARA BARAT' },
    { id: '53', name: 'NUSA TENGGARA TIMUR' },
    { id: '61', name: 'KALIMANTAN BARAT' },
    { id: '62', name: 'KALIMANTAN TENGAH' },
    { id: '63', name: 'KALIMANTAN SELATAN' },
    { id: '64', name: 'KALIMANTAN TIMUR' },
    { id: '65', name: 'KALIMANTAN UTARA' },
    { id: '71', name: 'SULAWESI UTARA' },
    { id: '72', name: 'SULAWESI TENGAH' },
    { id: '73', name: 'SULAWESI SELATAN' },
    { id: '74', name: 'SULAWESI TENGGARA' },
    { id: '75', name: 'GORONTALO' },
    { id: '76', name: 'SULAWESI BARAT' },
    { id: '81', name: 'MALUKU' },
    { id: '82', name: 'MALUKU UTARA' },
    { id: '91', name: 'PAPUA BARAT' },
    { id: '92', name: 'PAPUA' },
    { id: '93', name: 'PAPUA SELATAN' },
    { id: '94', name: 'PAPUA TENGAH' },
    { id: '95', name: 'PAPUA PEGUNUNGAN' },
    { id: '96', name: 'PAPUA BARAT DAYA' }
];

document.addEventListener('DOMContentLoaded', () => {
    initWilayahDropdowns();
    initFileUploads();
});

// 1. Password Visibility Toggle
window.toggleRegisterPassword = function (inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(iconId);
    if (!input || !icon) return;

    if (input.type === 'password') {
        input.type = 'text';
        icon.innerHTML = `
            <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
            <line x1="1" y1="1" x2="23" y2="23"></line>
        `;
    } else {
        input.type = 'password';
        icon.innerHTML = `
            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
            <circle cx="12" cy="12" r="3"></circle>
        `;
    }
};

// 2. Dynamic Region Cascading (Provinsi -> Kabupaten/Kota -> Kecamatan)
function initWilayahDropdowns() {
    const provSelect = document.getElementById('provinsi');
    const kabSelect = document.getElementById('kabupaten_kota');
    const kecSelect = document.getElementById('kecamatan');

    if (!provSelect || !kabSelect || !kecSelect) return;

    const oldProv = provSelect.dataset.old || '';
    const oldKab = kabSelect.dataset.old || '';
    const oldKec = kecSelect.dataset.old || '';

    // 1. Load Provinces via local backend endpoint (zero CORS/blocking)
    provSelect.innerHTML = '<option value="">Memuat data provinsi...</option>';
    
    fetch('/api/wilayah/provinces')
        .then(res => res.json())
        .then(provinces => populateProvinces(provinces))
        .catch(() => {
            // Backup direct fetch
            fetch('https://emsifa.github.io/api-wilayah-indonesia/api/provinces.json')
                .then(res => res.json())
                .then(provinces => populateProvinces(provinces))
                .catch(() => populateProvinces(FALLBACK_PROVINCES));
        });

    function populateProvinces(provinces) {
        provSelect.innerHTML = '<option value="">Pilih Provinsi</option>';
        const list = Array.isArray(provinces) ? provinces : (provinces.value || []);
        list.forEach(p => {
            const opt = document.createElement('option');
            opt.value = formatTitleCase(p.name);
            opt.dataset.id = p.id;
            opt.textContent = formatTitleCase(p.name);
            opt.title = opt.textContent;
            if (oldProv && (p.name.toUpperCase() === oldProv.toUpperCase() || opt.value === oldProv)) {
                opt.selected = true;
            }
            provSelect.appendChild(opt);
        });

        provSelect.title = provSelect.value || 'Pilih Provinsi';
        if (provSelect.value) {
            handleProvinceChange();
        }
    }

    provSelect.addEventListener('change', () => {
        provSelect.title = provSelect.value || 'Pilih Provinsi';
        handleProvinceChange();
    });
    
    kabSelect.addEventListener('change', () => {
        kabSelect.title = kabSelect.value || 'Pilih Kab/Kota';
        handleKabupatenChange();
    });

    kecSelect.addEventListener('change', () => {
        kecSelect.title = kecSelect.value || 'Pilih Kecamatan';
    });

    function handleProvinceChange() {
        const selectedOption = provSelect.options[provSelect.selectedIndex];
        const provId = selectedOption ? selectedOption.dataset.id : null;

        kabSelect.innerHTML = '<option value="">Pilih Kab/Kota</option>';
        kecSelect.innerHTML = '<option value="">Pilih Kecamatan</option>';
        kabSelect.disabled = true;
        kecSelect.disabled = true;

        if (!provId) return;

        kabSelect.disabled = false;
        kabSelect.innerHTML = '<option value="">Memuat Kab/Kota...</option>';

        fetch(`/api/wilayah/regencies/${provId}`)
            .then(res => res.json())
            .then(regencies => {
                const list = Array.isArray(regencies) ? regencies : (regencies.value || []);
                if (list.length === 0) throw new Error('Empty');
                renderRegencies(list);
            })
            .catch(() => {
                fetch(`https://emsifa.github.io/api-wilayah-indonesia/api/regencies/${provId}.json`)
                    .then(res => res.json())
                    .then(regencies => {
                        const list = Array.isArray(regencies) ? regencies : (regencies.value || []);
                        renderRegencies(list);
                    })
                    .catch(err => {
                        console.error('Error fetching regencies:', err);
                        kabSelect.innerHTML = '<option value="">Gagal memuat kota</option>';
                    });
            });

        function renderRegencies(regencies) {
            kabSelect.innerHTML = '<option value="">Pilih Kab/Kota</option>';
            const list = Array.isArray(regencies) ? regencies : (regencies.value || []);
            list.forEach(r => {
                const opt = document.createElement('option');
                opt.value = formatTitleCase(r.name);
                opt.dataset.id = r.id;
                opt.textContent = formatTitleCase(r.name);
                opt.title = opt.textContent;
                if (oldKab && (r.name.toUpperCase() === oldKab.toUpperCase() || opt.value === oldKab)) {
                    opt.selected = true;
                }
                kabSelect.appendChild(opt);
            });

            kabSelect.title = kabSelect.value || 'Pilih Kab/Kota';
            if (kabSelect.value) {
                handleKabupatenChange();
            }
        }
    }

    function handleKabupatenChange() {
        const selectedOption = kabSelect.options[kabSelect.selectedIndex];
        const regencyId = selectedOption ? selectedOption.dataset.id : null;

        kecSelect.innerHTML = '<option value="">Pilih Kecamatan</option>';
        kecSelect.disabled = true;

        if (!regencyId) return;

        kecSelect.disabled = false;
        kecSelect.innerHTML = '<option value="">Memuat Kecamatan...</option>';

        // 1. Try local API proxy
        fetch(`/api/wilayah/districts/${regencyId}`)
            .then(res => res.json())
            .then(districts => {
                const list = Array.isArray(districts) ? districts : (districts.value || []);
                if (list && list.length > 0) {
                    renderDistricts(list);
                } else {
                    throw new Error('Local API returned empty');
                }
            })
            .catch(() => {
                // 2. Direct fetch from primary CDN
                fetch(`https://emsifa.github.io/api-wilayah-indonesia/api/districts/${regencyId}.json`)
                    .then(res => res.json())
                    .then(districts => {
                        const list = Array.isArray(districts) ? districts : (districts.value || []);
                        if (list && list.length > 0) {
                            renderDistricts(list);
                        } else {
                            throw new Error('CDN returned empty');
                        }
                    })
                    .catch(() => {
                        // 3. Direct fetch from backup CDN
                        fetch(`https://kanglerian.github.io/api-wilayah-indonesia/api/districts/${regencyId}.json`)
                            .then(res => res.json())
                            .then(districts => {
                                const list = Array.isArray(districts) ? districts : (districts.value || []);
                                renderDistricts(list);
                            })
                            .catch(err => {
                                console.error('Error fetching districts:', err);
                                kecSelect.innerHTML = '<option value="">Pilih Kecamatan</option>';
                            });
                    });
            });

        function renderDistricts(districts) {
            kecSelect.innerHTML = '<option value="">Pilih Kecamatan</option>';
            const list = Array.isArray(districts) ? districts : (districts.value || []);
            list.forEach(d => {
                const opt = document.createElement('option');
                opt.value = formatTitleCase(d.name);
                opt.dataset.id = d.id;
                opt.textContent = formatTitleCase(d.name);
                opt.title = opt.textContent;
                if (oldKec && (d.name.toUpperCase() === oldKec.toUpperCase() || opt.value === oldKec)) {
                    opt.selected = true;
                }
                kecSelect.appendChild(opt);
            });

            kecSelect.title = kecSelect.value || 'Pilih Kecamatan';
        }
    }
}

// 3. Document Upload Handlers & Previews
function initFileUploads() {
    const uploadCards = [
        { id: 'foto_ktp', dropzoneId: 'dropzone_ktp', previewId: 'preview_ktp' },
        { id: 'foto_kantor_sppg', dropzoneId: 'dropzone_kantor', previewId: 'preview_kantor' },
        { id: 'foto_surat_resmi', dropzoneId: 'dropzone_surat', previewId: 'preview_surat' }
    ];

    uploadCards.forEach(item => {
        const fileInput = document.getElementById(item.id);
        const dropzone = document.getElementById(item.dropzoneId);
        const preview = document.getElementById(item.previewId);

        if (!fileInput || !dropzone) return;

        dropzone.addEventListener('click', () => fileInput.click());

        // Drag & drop
        ['dragenter', 'dragover'].forEach(eventName => {
            dropzone.addEventListener(eventName, (e) => {
                e.preventDefault();
                dropzone.classList.add('dragover');
            });
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropzone.addEventListener(eventName, (e) => {
                e.preventDefault();
                dropzone.classList.remove('dragover');
            });
        });

        dropzone.addEventListener('drop', (e) => {
            if (e.dataTransfer.files && e.dataTransfer.files.length > 0) {
                fileInput.files = e.dataTransfer.files;
                updateFileDisplay(fileInput, dropzone, preview);
            }
        });

        fileInput.addEventListener('change', () => {
            updateFileDisplay(fileInput, dropzone, preview);
        });
    });

    function updateFileDisplay(input, dropzone, preview) {
        if (!input.files || !input.files[0]) {
            input.value = '';
            dropzone.classList.remove('has-file');
            if (preview) preview.innerHTML = '';
            return;
        }

        const file = input.files[0];
        const sizeInMB = (file.size / (1024 * 1024)).toFixed(2);

        if (file.size > 5 * 1024 * 1024) {
            alert(`Ukuran file ${file.name} melebihi 5 MB (${sizeInMB} MB). Harap pilih file yang lebih kecil.`);
            input.value = '';
            dropzone.classList.remove('has-file');
            if (preview) preview.innerHTML = '';
            return;
        }

        const isImage = file.type.startsWith('image/') || /\.(jpe?g|png)$/i.test(file.name);
        const isPdf = file.type === 'application/pdf' || /\.pdf$/i.test(file.name);

        if (isImage) {
            const reader = new FileReader();
            reader.onload = (e) => {
                renderPreview(e.target.result, true);
            };
            reader.readAsDataURL(file);
        } else {
            renderPreview(null, false);
        }

        function renderPreview(imageSrc, isImg) {
            dropzone.classList.add('has-file');
            if (!preview) return;

            let mediaHtml = '';
            if (isImg && imageSrc) {
                mediaHtml = `<img src="${imageSrc}" alt="Preview" class="preview-thumb-img">`;
            } else if (isPdf) {
                mediaHtml = `
                    <div class="preview-pdf-box">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="16" y1="13" x2="8" y2="13"></line>
                            <line x1="16" y1="17" x2="8" y2="17"></line>
                            <polyline points="10 9 9 9 8 9"></polyline>
                        </svg>
                    </div>
                `;
            }

            preview.innerHTML = `
                <div class="preview-container">
                    <button type="button" class="preview-remove-btn" title="Hapus file" aria-label="Hapus file">&times;</button>
                    ${mediaHtml}
                    <div class="preview-info">
                        <span class="preview-filename" title="${file.name}">✓ ${file.name}</span>
                        <span class="preview-filesize">${sizeInMB} MB</span>
                        <span class="preview-change-btn">Ganti File</span>
                    </div>
                </div>
            `;

            const removeBtn = preview.querySelector('.preview-remove-btn');
            if (removeBtn) {
                removeBtn.addEventListener('click', (ev) => {
                    ev.stopPropagation();
                    input.value = '';
                    dropzone.classList.remove('has-file');
                    preview.innerHTML = '';
                });
            }
        }
    }
}

function formatTitleCase(str) {
    if (!str) return '';
    return str.toLowerCase().replace(/\b\w/g, s => s.toUpperCase());
}
