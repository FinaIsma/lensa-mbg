/**
 * Profile & Ubah Kata Sandi Modals Interaction - Kepala SPPG
 */

document.addEventListener('DOMContentLoaded', function () {
    const profilModal = document.getElementById('profilModal');
    const passwordModal = document.getElementById('passwordModal');
    const lightboxModal = document.getElementById('docLightboxModal');

    const viewContainer = document.getElementById('profilViewContainer');
    const editContainer = document.getElementById('profilEditContainer');
    const formEditProfil = document.getElementById('formEditProfil');
    const formUbahPassword = document.getElementById('formUbahPassword');

    const editProvinsi = document.getElementById('editProvinsi');
    const editKabupaten = document.getElementById('editKabupaten');
    const editKecamatan = document.getElementById('editKecamatan');

    // Global open/close functions attached to window
    window.openProfilModal = function () {
        if (!profilModal) return;
        setProfilMode('view');
        profilModal.style.display = 'flex';
        profilModal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';

        // Close navbar dropdown if open
        const profileDropdownWrapper = document.getElementById('profileDropdownWrapper');
        if (profileDropdownWrapper) profileDropdownWrapper.classList.remove('open');
    };

    window.closeProfilModal = function () {
        if (!profilModal) return;
        profilModal.style.display = 'none';
        profilModal.classList.add('hidden');
        document.body.style.overflow = '';
    };

    window.openPasswordModal = function () {
        if (!passwordModal) return;
        if (formUbahPassword) formUbahPassword.reset();
        clearFormErrors(formUbahPassword);
        passwordModal.style.display = 'flex';
        passwordModal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';

        const profileDropdownWrapper = document.getElementById('profileDropdownWrapper');
        if (profileDropdownWrapper) profileDropdownWrapper.classList.remove('open');
    };

    window.closePasswordModal = function () {
        if (!passwordModal) return;
        passwordModal.style.display = 'none';
        passwordModal.classList.add('hidden');
        document.body.style.overflow = '';
    };

    window.setProfilMode = function (mode) {
        if (mode === 'edit') {
            if (viewContainer) viewContainer.style.display = 'none';
            if (editContainer) editContainer.style.display = 'block';
            initWilayahDropdowns();
        } else {
            if (editContainer) editContainer.style.display = 'none';
            if (viewContainer) viewContainer.style.display = 'block';
        }
    };

    // Close on backdrop click
    [profilModal, passwordModal, lightboxModal].forEach(function (modal) {
        if (!modal) return;
        modal.addEventListener('click', function (e) {
            if (e.target === modal) {
                modal.style.display = 'none';
                modal.classList.add('hidden');
                document.body.style.overflow = '';
            }
        });
    });

    // Close on Escape key
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            if (lightboxModal && !lightboxModal.classList.contains('hidden')) {
                closeLightbox();
                return;
            }
            if (profilModal && !profilModal.classList.contains('hidden')) {
                closeProfilModal();
            }
            if (passwordModal && !passwordModal.classList.contains('hidden')) {
                closePasswordModal();
            }
        }
    });

    // Lightbox for Document Preview
    window.previewDocument = function (title, url) {
        if (!url || url === '#' || url === 'null') {
            alert('Dokumen belum diunggah atau tidak tersedia.');
            return;
        }

        const lightboxTitle = document.getElementById('lightboxTitle');
        const lightboxContent = document.getElementById('lightboxContent');
        if (!lightboxModal || !lightboxContent) return;

        if (lightboxTitle) lightboxTitle.textContent = title;

        const isPdf = url.toLowerCase().endsWith('.pdf');
        if (isPdf) {
            lightboxContent.innerHTML = `<iframe src="${url}" title="${title}"></iframe>`;
        } else {
            lightboxContent.innerHTML = `<img src="${url}" alt="${title}">`;
        }

        lightboxModal.style.display = 'flex';
        lightboxModal.classList.remove('hidden');
    };

    window.closeLightbox = function () {
        if (!lightboxModal) return;
        lightboxModal.style.display = 'none';
        lightboxModal.classList.add('hidden');
        const lightboxContent = document.getElementById('lightboxContent');
        if (lightboxContent) lightboxContent.innerHTML = '';
    };

    // File input change preview
    window.handleFileSelect = function (input, previewId) {
        const file = input.files[0];
        if (!file) return;

        if (file.size > 5 * 1024 * 1024) {
            alert('Ukuran file maksimal 5 MB!');
            input.value = '';
            return;
        }

        const previewContainer = document.getElementById(previewId);
        if (!previewContainer) return;

        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function (e) {
                previewContainer.innerHTML = `<img src="${e.target.result}" alt="Preview" style="width: 100%; height: 100%; object-fit: cover;">`;
            };
            reader.readAsDataURL(file);
        } else if (file.type === 'application/pdf') {
            previewContainer.innerHTML = `<div style="display:flex; flex-direction:column; align-items:center; gap:6px; color:#DC2626;">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                <span style="font-size:11px; font-weight:600; text-align:center;">${file.name}</span>
            </div>`;
        }
    };

    // Wilayah API Handling
    let provincesData = [];
    async function initWilayahDropdowns() {
        if (!editProvinsi) return;
        if (provincesData.length > 0) return; // already loaded

        try {
            const res = await fetch('/api/wilayah/provinces');
            provincesData = await res.json();

            const currentProv = editProvinsi.getAttribute('data-current') || '';
            editProvinsi.innerHTML = '<option value="">Pilih Provinsi</option>';
            provincesData.forEach((prov) => {
                const isSelected = prov.name.toLowerCase() === currentProv.toLowerCase();
                const opt = document.createElement('option');
                opt.value = prov.name;
                opt.dataset.id = prov.id;
                opt.textContent = prov.name;
                if (isSelected) opt.selected = true;
                editProvinsi.appendChild(opt);
            });

            if (currentProv) {
                const selectedOpt = editProvinsi.options[editProvinsi.selectedIndex];
                if (selectedOpt && selectedOpt.dataset.id) {
                    await loadRegencies(selectedOpt.dataset.id);
                }
            }
        } catch (err) {
            console.error('Gagal memuat provinsi:', err);
        }
    }

    async function loadRegencies(provId) {
        if (!editKabupaten) return;
        editKabupaten.innerHTML = '<option value="">Pilih Kota/Kabupaten</option>';
        if (editKecamatan) editKecamatan.innerHTML = '<option value="">Pilih Kecamatan</option>';

        if (!provId) return;

        try {
            const res = await fetch(`/api/wilayah/regencies/${provId}`);
            const regencies = await res.json();
            const currentKab = editKabupaten.getAttribute('data-current') || '';

            regencies.forEach((reg) => {
                const isSelected = reg.name.toLowerCase() === currentKab.toLowerCase();
                const opt = document.createElement('option');
                opt.value = reg.name;
                opt.dataset.id = reg.id;
                opt.textContent = reg.name;
                if (isSelected) opt.selected = true;
                editKabupaten.appendChild(opt);
            });

            if (currentKab) {
                const selectedOpt = editKabupaten.options[editKabupaten.selectedIndex];
                if (selectedOpt && selectedOpt.dataset.id) {
                    await loadDistricts(selectedOpt.dataset.id);
                }
            }
        } catch (err) {
            console.error('Gagal memuat kota/kabupaten:', err);
        }
    }

    async function loadDistricts(regId) {
        if (!editKecamatan) return;
        editKecamatan.innerHTML = '<option value="">Pilih Kecamatan</option>';

        if (!regId) return;

        try {
            const res = await fetch(`/api/wilayah/districts/${regId}`);
            const districts = await res.json();
            const currentKec = editKecamatan.getAttribute('data-current') || '';

            districts.forEach((dist) => {
                const isSelected = dist.name.toLowerCase() === currentKec.toLowerCase();
                const opt = document.createElement('option');
                opt.value = dist.name;
                opt.textContent = dist.name;
                if (isSelected) opt.selected = true;
                editKecamatan.appendChild(opt);
            });
        } catch (err) {
            console.error('Gagal memuat kecamatan:', err);
        }
    }

    if (editProvinsi) {
        editProvinsi.addEventListener('change', function () {
            const selectedOpt = this.options[this.selectedIndex];
            const provId = selectedOpt ? selectedOpt.dataset.id : null;
            editKabupaten.setAttribute('data-current', '');
            if (editKecamatan) editKecamatan.setAttribute('data-current', '');
            loadRegencies(provId);
        });
    }

    if (editKabupaten) {
        editKabupaten.addEventListener('change', function () {
            const selectedOpt = this.options[this.selectedIndex];
            const regId = selectedOpt ? selectedOpt.dataset.id : null;
            if (editKecamatan) editKecamatan.setAttribute('data-current', '');
            loadDistricts(regId);
        });
    }

    // Helper: Clear Form Errors
    function clearFormErrors(form) {
        if (!form) return;
        form.querySelectorAll('.pm-field-error').forEach((el) => el.remove());
        form.querySelectorAll('.pm-input, .pm-select').forEach((el) => {
            el.style.borderColor = '#E2E8F0';
        });
    }

    // Helper: Show Form Errors
    function showFormErrors(form, errors) {
        clearFormErrors(form);
        for (const field in errors) {
            const input = form.querySelector(`[name="${field}"]`);
            if (input) {
                input.style.borderColor = '#DC2626';
                const errorDiv = document.createElement('div');
                errorDiv.className = 'pm-field-error';
                errorDiv.textContent = errors[field][0];
                input.parentNode.appendChild(errorDiv);
            }
        }
    }

    // Form Submit: Ubah Kata Sandi
    if (formUbahPassword) {
        formUbahPassword.addEventListener('submit', async function (e) {
            e.preventDefault();
            clearFormErrors(formUbahPassword);

            const submitBtn = formUbahPassword.querySelector('button[type="submit"]');
            const originalBtnHtml = submitBtn ? submitBtn.innerHTML : '';
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span>Menyimpan...</span>';
            }

            const formData = new FormData(formUbahPassword);

            try {
                const res = await fetch(formUbahPassword.action, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                    body: formData,
                });

                const data = await res.json();

                if (!res.ok) {
                    if (data.errors) {
                        showFormErrors(formUbahPassword, data.errors);
                    } else {
                        alert(data.message || 'Gagal mengubah kata sandi.');
                    }
                    return;
                }

                alert(data.message || 'Kata sandi berhasil diperbarui.');
                formUbahPassword.reset();
                closePasswordModal();
            } catch (err) {
                console.error(err);
                alert('Terjadi kesalahan jaringan.');
            } finally {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnHtml;
                }
            }
        });
    }

    // Form Submit: Edit Profil SPPG (Ajukan Perubahan)
    if (formEditProfil) {
        formEditProfil.addEventListener('submit', async function (e) {
            e.preventDefault();
            clearFormErrors(formEditProfil);

            const submitBtn = formEditProfil.querySelector('button[type="submit"]');
            const originalBtnHtml = submitBtn ? submitBtn.innerHTML : '';
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span>Mengajukan...</span>';
            }

            const formData = new FormData(formEditProfil);

            try {
                const res = await fetch(formEditProfil.action, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                    body: formData,
                });

                const result = await res.json();

                if (!res.ok) {
                    if (result.errors) {
                        showFormErrors(formEditProfil, result.errors);
                    } else {
                        alert(result.message || 'Gagal mengajukan perubahan data.');
                    }
                    return;
                }

                alert(result.message || 'Pengajuan perubahan data berhasil dikirim!');

                // Update View Mode values with new data
                if (result.data) {
                    const d = result.data;
                    updateElementText('viewNamaKepala', d.nama_lengkap);
                    updateElementText('viewEmailKepala', d.email);
                    updateElementText('viewTeleponKepala', d.no_telepon);
                    updateElementText('viewNamaSppg', d.nama_sppg);
                    updateElementText('viewAlamatSppg', d.alamat_sppg);
                    updateElementText('viewProvinsiSppg', d.provinsi);
                    updateElementText('viewKabupatenSppg', d.kabupaten_kota);
                    updateElementText('viewKecamatanSppg', d.kecamatan);

                    // Update entity headers
                    document.querySelectorAll('.pm-entity-name').forEach((el) => {
                        el.textContent = d.nama_sppg;
                    });

                    // Update top navbar user name
                    const navUserName = document.querySelector('.user-name');
                    if (navUserName) navUserName.textContent = d.nama_lengkap;
                }

                setProfilMode('view');
            } catch (err) {
                console.error(err);
                alert('Terjadi kesalahan koneksi saat mengirim pengajuan.');
            } finally {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnHtml;
                }
            }
        });
    }

    function updateElementText(id, text) {
        const el = document.getElementById(id);
        if (el && text) el.textContent = text;
    }
});
