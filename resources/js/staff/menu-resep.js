/**
 * Data Menu & Resep JavaScript - Staff SPPG
 * Mendukung 4 Panel Sesuai Mockup:
 * 1. Tabel Resep
 * 2. Right Drawer Detail Resep
 * 3. Modal Form Tambah/Ubah Resep (Bahan & Gizi Repeater)
 * 4. Sub-Modal Tambah Kategori Gizi Baru (AJAX)
 */

document.addEventListener('DOMContentLoaded', function () {
    // 1. Profile Dropdown in Navbar
    const profileBtn = document.getElementById('profileDropdownBtn');
    const profileMenu = document.getElementById('profileDropdownMenu');

    if (profileBtn && profileMenu) {
        profileBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            profileMenu.classList.toggle('show');
            profileBtn.setAttribute('aria-expanded', profileMenu.classList.contains('show'));
        });

        document.addEventListener('click', function (e) {
            if (!profileMenu.contains(e.target) && !profileBtn.contains(e.target)) {
                profileMenu.classList.remove('show');
                profileBtn.setAttribute('aria-expanded', 'false');
            }
        });
    }

    // Modal Helper functions
    window.openModal = function (modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('open');
            document.body.style.overflow = 'hidden';
        }
    };

    window.closeModal = function (modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('open');
            document.body.style.overflow = '';
        }
    };

    // Close on Escape key
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            const subModal = document.getElementById('modalAddNutritionType');
            if (subModal && subModal.style.display !== 'none') {
                closeModalAddNutritionType();
                return;
            }

            const recipeModal = document.getElementById('modalRecipeForm');
            if (recipeModal && recipeModal.classList.contains('open')) {
                closeRecipeModal();
                return;
            }

            const drawer = document.getElementById('recipeDrawer');
            if (drawer && drawer.classList.contains('open')) {
                closeRecipeDrawer();
                return;
            }

            document.querySelectorAll('.modal-backdrop.open').forEach(modal => {
                modal.classList.remove('open');
            });
            document.body.style.overflow = '';
        }
    });

    // 2. Edit Menu modal handler (untuk laman /staff/menu)
    document.querySelectorAll('[data-edit-menu]').forEach(button => {
        button.addEventListener('click', function () {
            const id = this.getAttribute('data-id');
            const nama = this.getAttribute('data-nama');
            const kategori = this.getAttribute('data-kategori');
            const deskripsi = this.getAttribute('data-deskripsi');
            const active = this.getAttribute('data-active') === '1';

            const form = document.getElementById('formEditMenu');
            if (form) {
                form.action = `/staff/menu/${id}`;
            }

            const editNama = document.getElementById('editMenuNama');
            if (editNama) editNama.value = nama || '';

            const editKategori = document.getElementById('editMenuKategori');
            if (editKategori) editKategori.value = kategori || '';

            const editDeskripsi = document.getElementById('editMenuDeskripsi');
            if (editDeskripsi) editDeskripsi.value = deskripsi || '';

            const editStatus = document.getElementById('editMenuStatus');
            if (editStatus) editStatus.value = active ? '1' : '0';

            window.openModal('modalEditMenu');
        });
    });

    // Support untuk modal detail resep di index overview (jika ada modalDetailRecipe)
    document.querySelectorAll('[data-detail-recipe]').forEach(button => {
        button.addEventListener('click', function () {
            const modalEl = document.getElementById('modalDetailRecipe');
            if (!modalEl) {
                // Jika tidak ada modalDetailRecipe di halaman ini, panggil drawer
                const id = this.getAttribute('data-id');
                if (id && window.openDetailDrawer) {
                    window.openDetailDrawer(id);
                }
                return;
            }

            const kode = this.getAttribute('data-kode');
            const menu = this.getAttribute('data-menu');
            const porsi = this.getAttribute('data-porsi');
            const version = this.getAttribute('data-version');
            const status = this.getAttribute('data-status');
            const items = JSON.parse(this.getAttribute('data-items') || '[]');

            const elKode = document.getElementById('detailRecipeKode');
            if (elKode) elKode.textContent = kode || '-';

            const elMenu = document.getElementById('detailRecipeMenu');
            if (elMenu) elMenu.textContent = menu || '-';

            const elPorsi = document.getElementById('detailRecipePorsi');
            if (elPorsi) elPorsi.textContent = porsi || '-';

            const elVer = document.getElementById('detailRecipeVersion');
            if (elVer) elVer.textContent = version || '1';

            const badgeEl = document.getElementById('detailRecipeStatus');
            if (badgeEl) {
                if (status === 'active') {
                    badgeEl.className = 'badge badge-active';
                    badgeEl.innerHTML = '<span class="badge-dot"></span> Active';
                } else {
                    badgeEl.className = 'badge badge-inactive';
                    badgeEl.innerHTML = '<span class="badge-dot"></span> Archived';
                }
            }

            const tbody = document.getElementById('detailRecipeTableBody');
            if (tbody) {
                tbody.innerHTML = '';
                if (items.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="4" class="text-center" style="color: #64748B; padding: 16px;">Tidak ada bahan terdaftar.</td></tr>';
                } else {
                    items.forEach((item, index) => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td>${index + 1}</td>
                            <td style="font-weight: 600; color: #071E49;">${escapeHtml(item.material_nama)}</td>
                            <td>${item.qty_per_porsi}</td>
                            <td>${escapeHtml(item.satuan)}</td>
                        `;
                        tbody.appendChild(tr);
                    });
                }
            }

            window.openModal('modalDetailRecipe');
        });
    });

    // Support untuk tambah bahan di modalAddRecipe index (jika ada btnAddIngredient)
    const btnAddIngredientOld = document.getElementById('btnAddIngredient');
    const ingredientListContainerOld = document.getElementById('ingredientListContainer');
    if (btnAddIngredientOld && ingredientListContainerOld) {
        btnAddIngredientOld.addEventListener('click', function () {
            const index = ingredientListContainerOld.children.length;
            const template = document.getElementById('ingredientRowTemplate');
            if (template) {
                const clone = template.content.cloneNode(true);
                clone.querySelectorAll('[name*="__INDEX__"]').forEach(input => {
                    input.name = input.name.replace('__INDEX__', index);
                });
                ingredientListContainerOld.appendChild(clone);
            }
        });
    }

    document.addEventListener('click', function (e) {
        if (e.target.closest('.btn-remove-ingredient')) {
            const row = e.target.closest('.ingredient-form-row');
            if (row && ingredientListContainerOld && ingredientListContainerOld.children.length > 1) {
                row.remove();
            }
        }
    });
});

/* ==========================================================================
   PANEL 2: RIGHT DRAWER DETAIL RESEP
   ========================================================================== */
window.activeDrawerRecipeId = null;

window.openDetailDrawer = async function (recipeId) {
    window.activeDrawerRecipeId = recipeId;
    const drawer = document.getElementById('recipeDrawer');
    const backdrop = document.getElementById('recipeDrawerBackdrop');

    if (!drawer || !backdrop) return;

    // Buka drawer dengan loading placeholder
    drawer.classList.add('open');
    backdrop.classList.add('open');
    drawer.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';

    document.getElementById('drawerRecipeTitle').textContent = 'Memuat data resep...';
    document.getElementById('drawerIdResep').textContent = '-';
    document.getElementById('drawerIdMenu').textContent = '-';
    document.getElementById('drawerNamaMenu').textContent = '-';
    document.getElementById('drawerPorsi').textContent = '-';
    document.getElementById('drawerVersion').textContent = '-';
    document.getElementById('drawerBahanList').innerHTML = '<div style="font-size: 13px; color: #94A3B8;">Memuat bahan...</div>';
    document.getElementById('drawerGiziList').innerHTML = '<div style="font-size: 13px; color: #94A3B8;">Memuat gizi...</div>';

    try {
        const response = await fetch(`/staff/resep/${recipeId}`, {
            headers: {
                'Accept': 'application/json'
            }
        });

        if (!response.ok) {
            throw new Error('Gagal memuat detail resep.');
        }

        const data = await response.json();

        // 1. Hero Info & Title
        document.getElementById('drawerRecipeTitle').textContent = `Resep ${data.nama_menu} - Porsi ${data.ukuran_porsi_label}`;
        
        const badge = document.getElementById('drawerBadgeStatus');
        const badgeText = document.getElementById('drawerBadgeStatusText');
        if (badge && badgeText) {
            if (data.status === 'active') {
                badge.className = 'badge badge-active';
                badgeText.textContent = 'Active';
            } else {
                badge.className = 'badge badge-inactive';
                badgeText.textContent = 'Archived';
            }
        }

        // 2. Card Informasi Resep
        document.getElementById('drawerIdResep').textContent = data.kode || '-';
        document.getElementById('drawerIdMenu').textContent = data.menu_kode || '-';
        document.getElementById('drawerNamaMenu').textContent = data.nama_menu || '-';
        document.getElementById('drawerPorsi').textContent = data.ukuran_porsi_label || '-';
        document.getElementById('drawerVersion').textContent = `v${data.version}`;

        // 3. Card Bahan Baku
        const bahanContainer = document.getElementById('drawerBahanList');
        if (data.items && data.items.length > 0) {
            bahanContainer.innerHTML = data.items.map(item => `
                <div class="drawer-item-row">
                    <span class="item-name">${escapeHtml(item.material_nama)}</span>
                    <span class="item-qty">${item.qty_per_porsi} ${escapeHtml(item.satuan)}</span>
                </div>
            `).join('');
        } else {
            bahanContainer.innerHTML = '<div style="font-size: 13px; color: #94A3B8;">Belum ada bahan terdaftar.</div>';
        }

        // 4. Card Kandungan Gizi
        const giziContainer = document.getElementById('drawerGiziList');
        if (data.nutritions && data.nutritions.length > 0) {
            giziContainer.innerHTML = data.nutritions.map(n => `
                <div class="drawer-item-row">
                    <span class="item-name">${escapeHtml(n.nama)}</span>
                    <span class="item-qty">${n.nilai} ${escapeHtml(n.satuan)}</span>
                </div>
            `).join('');
        } else {
            giziContainer.innerHTML = '<div style="font-size: 13px; color: #94A3B8;">Belum ada kandungan gizi terdaftar.</div>';
        }

        // 5. Action Buttons in Drawer
        const formToggle = document.getElementById('formDrawerToggleStatus');
        const btnToggle = document.getElementById('btnDrawerToggleStatus');
        const btnToggleText = document.getElementById('btnDrawerToggleStatusText');

        if (formToggle && btnToggle && btnToggleText) {
            formToggle.action = `/staff/resep/${data.id}/toggle-status`;
            if (data.status === 'active') {
                btnToggle.className = 'btn-drawer-danger';
                btnToggleText.textContent = 'Arsipkan';
                btnToggle.innerHTML = `
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                    <span>Arsipkan</span>
                `;
            } else {
                btnToggle.className = 'btn-drawer-activate';
                btnToggleText.textContent = 'Aktifkan';
                btnToggle.innerHTML = `
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    <span>Aktifkan</span>
                `;
            }
        }

    } catch (err) {
        console.error(err);
        document.getElementById('drawerRecipeTitle').textContent = 'Gagal memuat resep.';
    }
};

window.closeRecipeDrawer = function () {
    const drawer = document.getElementById('recipeDrawer');
    const backdrop = document.getElementById('recipeDrawerBackdrop');

    if (drawer) {
        drawer.classList.remove('open');
        drawer.setAttribute('aria-hidden', 'true');
    }
    if (backdrop) {
        backdrop.classList.remove('open');
    }
    document.body.style.overflow = '';
};

window.editFromDrawer = function () {
    if (window.activeDrawerRecipeId) {
        const id = window.activeDrawerRecipeId;
        closeRecipeDrawer();
        openEditRecipeModal(id);
    }
};

/* ==========================================================================
   PANEL 3: MODAL DIALOG TAMBAH / UBAH DATA RESEP
   ========================================================================== */
window.openAddRecipeModal = function () {
    const modal = document.getElementById('modalRecipeForm');
    const form = document.getElementById('recipeMainForm');
    if (!modal || !form) return;

    form.reset();
    document.getElementById('recipeFormMethod').value = 'POST';
    document.getElementById('recipeEditId').value = '';
    form.action = window.RECIPE_CONFIG ? window.RECIPE_CONFIG.storeUrl : '/staff/resep';

    // Header & Info
    document.getElementById('recipeModalTitle').textContent = 'Tambah Data Resep';
    document.getElementById('recipeModalSubtitle').textContent = 'Lengkapi formulir komposisi bahan dan nilai gizi resep';
    document.getElementById('recipeFormIdDisplay').value = 'Otomatis';
    document.getElementById('recipeBatchWarning').style.display = 'none';

    // Defaults
    const menuSelect = document.getElementById('recipeFormMenuId');
    if (menuSelect) {
        menuSelect.value = '';
        menuSelect.disabled = false;
    }
    document.getElementById('recipeFormPorsi').value = 'besar';
    document.getElementById('recipeFormPorsi').disabled = false;
    document.getElementById('recipeFormStatus').value = 'active';

    // Submit button
    document.getElementById('btnSubmitRecipeText').textContent = 'Simpan';

    // Clear and populate repeaters
    document.getElementById('recipeItemsContainer').innerHTML = '';
    document.getElementById('recipeNutritionsContainer').innerHTML = '';

    // Add 1 default ingredient and 1 default nutrition row
    addIngredientRow();
    addNutritionRow();

    window.openModal('modalRecipeForm');
};

window.openEditRecipeModal = async function (recipeId) {
    const modal = document.getElementById('modalRecipeForm');
    const form = document.getElementById('recipeMainForm');
    if (!modal || !form) return;

    form.reset();
    document.getElementById('recipeEditId').value = recipeId;
    document.getElementById('recipeItemsContainer').innerHTML = '';
    document.getElementById('recipeNutritionsContainer').innerHTML = '';

    try {
        const response = await fetch(`/staff/resep/${recipeId}`, {
            headers: { 'Accept': 'application/json' }
        });

        if (!response.ok) {
            throw new Error('Gagal memuat resep untuk diedit.');
        }

        const data = await response.json();

        document.getElementById('recipeFormIdDisplay').value = data.kode || `RS${data.id}`;

        const menuSelect = document.getElementById('recipeFormMenuId');
        if (menuSelect) {
            menuSelect.value = data.menu_id;
            // Kunci menu saat edit agar konsisten
            menuSelect.disabled = true;
        }

        const porsiSelect = document.getElementById('recipeFormPorsi');
        if (porsiSelect) {
            porsiSelect.value = data.ukuran_porsi;
        }

        const statusSelect = document.getElementById('recipeFormStatus');
        if (statusSelect) {
            statusSelect.value = data.status;
        }

        const batchAlert = document.getElementById('recipeBatchWarning');
        const submitBtnText = document.getElementById('btnSubmitRecipeText');

        // Aturan bisnis: Resep yang sudah dipakai batch tidak boleh diedit langsung.
        // Tombol beralih menjadi "Buat Versi Baru (vX)"
        if (data.is_used_in_batch) {
            form.action = `/staff/resep/${recipeId}/version`;
            document.getElementById('recipeFormMethod').value = 'POST';

            document.getElementById('recipeModalTitle').textContent = 'Buat Versi Baru Resep';
            document.getElementById('recipeModalSubtitle').textContent = `Membuat resep versi ${data.version + 1} karena versi saat ini sudah dipakai dalam batch produksi`;
            
            batchAlert.style.display = 'flex';
            document.getElementById('batchNextVersion').textContent = `v${data.version + 1}`;
            submitBtnText.textContent = `Buat Versi Baru (v${data.version + 1})`;

            // Porsi tidak dapat diubah saat buat versi baru
            if (porsiSelect) porsiSelect.disabled = true;
        } else {
            form.action = `/staff/resep/${recipeId}`;
            document.getElementById('recipeFormMethod').value = 'PUT';

            document.getElementById('recipeModalTitle').textContent = 'Ubah Data Resep';
            document.getElementById('recipeModalSubtitle').textContent = 'Perbarui formulir komposisi bahan dan nilai gizi resep';

            batchAlert.style.display = 'none';
            submitBtnText.textContent = 'Simpan Perubahan';

            if (porsiSelect) porsiSelect.disabled = false;
        }

        // Render Bahan
        if (data.items && data.items.length > 0) {
            data.items.forEach(item => {
                addIngredientRow(item.material_id, item.qty_per_porsi);
            });
        } else {
            addIngredientRow();
        }

        // Render Gizi
        if (data.nutritions && data.nutritions.length > 0) {
            data.nutritions.forEach(n => {
                addNutritionRow(n.nutrition_type_id, n.nilai);
            });
        } else {
            addNutritionRow();
        }

        window.openModal('modalRecipeForm');

    } catch (err) {
        console.error(err);
        alert('Terjadi kesalahan saat memuat data resep.');
    }
};

window.closeRecipeModal = function () {
    // Re-enable any disabled select before closing
    const menuSelect = document.getElementById('recipeFormMenuId');
    if (menuSelect) menuSelect.disabled = false;
    const porsiSelect = document.getElementById('recipeFormPorsi');
    if (porsiSelect) porsiSelect.disabled = false;

    window.closeModal('modalRecipeForm');
};

/* ==========================================================================
   REPEATER: BAHAN BAKU (MULTI BARIS)
   ========================================================================== */
window.addIngredientRow = function (selectedMaterialId = '', qty = '') {
    const container = document.getElementById('recipeItemsContainer');
    if (!container) return;

    const rowIndex = container.children.length;
    const materials = window.AVAILABLE_MATERIALS || [];

    const row = document.createElement('div');
    row.className = 'repeater-row ingredient-row';

    let optionsHtml = '<option value="">-- Pilih Bahan --</option>';
    let initialUnit = '-';

    materials.forEach(mat => {
        const isSelected = selectedMaterialId && Number(selectedMaterialId) === Number(mat.id);
        if (isSelected) {
            initialUnit = mat.satuan;
        }
        optionsHtml += `<option value="${mat.id}" data-satuan="${escapeHtml(mat.satuan)}" ${isSelected ? 'selected' : ''}>${escapeHtml(mat.nama)} (${escapeHtml(mat.satuan)})</option>`;
    });

    row.innerHTML = `
        <div>
            <select name="items[${rowIndex}][material_id]" class="form-select ingredient-material-select" required>
                ${optionsHtml}
            </select>
        </div>
        <div class="input-with-unit">
            <input type="number" step="0.0001" min="0.0001" name="items[${rowIndex}][qty_per_porsi]" value="${qty !== '' ? qty : ''}" class="form-input ingredient-qty-input" placeholder="Takaran/porsi" required>
            <span class="unit-tag ingredient-unit-label">${escapeHtml(initialUnit)}</span>
        </div>
        <div>
            <button type="button" class="btn-repeater-remove" title="Hapus bahan">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
        </div>
    `;

    // Event listener: update satuan otomatis & cek duplikasi bahan
    const selectEl = row.querySelector('.ingredient-material-select');
    const unitLabel = row.querySelector('.ingredient-unit-label');
    const removeBtn = row.querySelector('.btn-repeater-remove');

    selectEl.addEventListener('change', function () {
        const selectedOption = this.options[this.selectedIndex];
        const unit = selectedOption.getAttribute('data-satuan') || '-';
        unitLabel.textContent = unit;

        // Cek bahan ganda dalam resep
        if (this.value) {
            const allSelects = container.querySelectorAll('.ingredient-material-select');
            let duplicateFound = false;
            allSelects.forEach(otherSelect => {
                if (otherSelect !== this && otherSelect.value === this.value) {
                    duplicateFound = true;
                }
            });

            if (duplicateFound) {
                alert('Bahan ini sudah dipilih di baris lain. Setiap bahan hanya boleh dimasukkan satu kali dalam satu resep.');
                this.value = '';
                unitLabel.textContent = '-';
            }
        }
    });

    removeBtn.addEventListener('click', function () {
        if (container.children.length <= 1) {
            alert('Resep harus memiliki minimal 1 bahan baku.');
            return;
        }
        row.remove();
        reindexIngredientRows();
    });

    container.appendChild(row);
};

function reindexIngredientRows() {
    const container = document.getElementById('recipeItemsContainer');
    if (!container) return;

    Array.from(container.children).forEach((row, idx) => {
        const select = row.querySelector('.ingredient-material-select');
        const input = row.querySelector('.ingredient-qty-input');
        if (select) select.name = `items[${idx}][material_id]`;
        if (input) input.name = `items[${idx}][qty_per_porsi]`;
    });
}

/* ==========================================================================
   REPEATER: KANDUNGAN GIZI (MULTI BARIS)
   ========================================================================== */
window.addNutritionRow = function (selectedNutritionTypeId = '', nilai = '') {
    const container = document.getElementById('recipeNutritionsContainer');
    if (!container) return;

    const rowIndex = container.children.length;
    const nutritions = window.AVAILABLE_NUTRITION_TYPES || [];

    const row = document.createElement('div');
    row.className = 'repeater-row nutrition-row';

    let optionsHtml = '<option value="">-- Pilih Jenis Gizi --</option>';
    let initialUnit = '-';

    nutritions.forEach(nut => {
        const isSelected = selectedNutritionTypeId && Number(selectedNutritionTypeId) === Number(nut.id);
        if (isSelected) {
            initialUnit = nut.satuan;
        }
        optionsHtml += `<option value="${nut.id}" data-satuan="${escapeHtml(nut.satuan)}" ${isSelected ? 'selected' : ''}>${escapeHtml(nut.nama)} (${escapeHtml(nut.satuan)})</option>`;
    });

    row.innerHTML = `
        <div>
            <select name="nutritions[${rowIndex}][nutrition_type_id]" class="form-select nutrition-type-select">
                ${optionsHtml}
            </select>
        </div>
        <div class="input-with-unit">
            <input type="number" step="0.01" min="0" name="nutritions[${rowIndex}][nilai]" value="${nilai !== '' ? nilai : ''}" class="form-input nutrition-nilai-input" placeholder="Nilai gizi">
            <span class="unit-tag nutrition-unit-label">${escapeHtml(initialUnit)}</span>
        </div>
        <div>
            <button type="button" class="btn-repeater-remove" title="Hapus gizi">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
        </div>
    `;

    const selectEl = row.querySelector('.nutrition-type-select');
    const unitLabel = row.querySelector('.nutrition-unit-label');
    const removeBtn = row.querySelector('.btn-repeater-remove');

    selectEl.addEventListener('change', function () {
        const selectedOption = this.options[this.selectedIndex];
        const unit = selectedOption.getAttribute('data-satuan') || '-';
        unitLabel.textContent = unit;

        // Cek gizi ganda
        if (this.value) {
            const allSelects = container.querySelectorAll('.nutrition-type-select');
            let duplicateFound = false;
            allSelects.forEach(otherSelect => {
                if (otherSelect !== this && otherSelect.value === this.value) {
                    duplicateFound = true;
                }
            });

            if (duplicateFound) {
                alert('Jenis gizi ini sudah dipilih di baris lain. Setiap jenis gizi hanya boleh dimasukkan satu kali per resep.');
                this.value = '';
                unitLabel.textContent = '-';
            }
        }
    });

    removeBtn.addEventListener('click', function () {
        row.remove();
        reindexNutritionRows();
    });

    container.appendChild(row);
};

function reindexNutritionRows() {
    const container = document.getElementById('recipeNutritionsContainer');
    if (!container) return;

    Array.from(container.children).forEach((row, idx) => {
        const select = row.querySelector('.nutrition-type-select');
        const input = row.querySelector('.nutrition-nilai-input');
        if (select) select.name = `nutritions[${idx}][nutrition_type_id]`;
        if (input) input.name = `nutritions[${idx}][nilai]`;
    });
}

/* ==========================================================================
   VALIDASI SUBMIT FORM RESEP
   ========================================================================== */
window.validateAndSubmitRecipe = function (event) {
    const menuSelect = document.getElementById('recipeFormMenuId');
    const porsiSelect = document.getElementById('recipeFormPorsi');

    // Jika select disabled (saat edit), enable sebelum submit agar terkirim ke server
    if (menuSelect && menuSelect.disabled) {
        menuSelect.disabled = false;
    }
    if (porsiSelect && porsiSelect.disabled) {
        porsiSelect.disabled = false;
    }

    const itemsContainer = document.getElementById('recipeItemsContainer');
    const itemRows = itemsContainer.querySelectorAll('.ingredient-row');

    if (itemRows.length === 0) {
        event.preventDefault();
        alert('Resep wajib memiliki minimal 1 bahan baku.');
        return false;
    }

    let hasValidItem = false;
    let duplicateItemMap = {};

    for (let i = 0; i < itemRows.length; i++) {
        const select = itemRows[i].querySelector('.ingredient-material-select');
        const input = itemRows[i].querySelector('.ingredient-qty-input');

        if (!select.value) {
            event.preventDefault();
            alert(`Silakan pilih bahan baku pada baris ke-${i + 1}.`);
            select.focus();
            return false;
        }

        if (duplicateItemMap[select.value]) {
            event.preventDefault();
            alert('Terdapat bahan baku ganda. Setiap bahan hanya boleh dipilih satu kali.');
            select.focus();
            return false;
        }
        duplicateItemMap[select.value] = true;

        const val = parseFloat(input.value);
        if (isNaN(val) || val <= 0) {
            event.preventDefault();
            alert(`Takaran bahan pada baris ke-${i + 1} harus berupa angka lebih besar dari 0.`);
            input.focus();
            return false;
        }

        hasValidItem = true;
    }

    if (!hasValidItem) {
        event.preventDefault();
        alert('Resep harus memiliki minimal 1 bahan baku yang valid.');
        return false;
    }

    // Validasi Gizi (opsional, namun jika diisi harus angka >= 0)
    const nutritionsContainer = document.getElementById('recipeNutritionsContainer');
    const nutRows = nutritionsContainer.querySelectorAll('.nutrition-row');
    let duplicateNutMap = {};

    for (let i = 0; i < nutRows.length; i++) {
        const select = nutRows[i].querySelector('.nutrition-type-select');
        const input = nutRows[i].querySelector('.nutrition-nilai-input');

        if (select.value) {
            if (duplicateNutMap[select.value]) {
                event.preventDefault();
                alert('Terdapat jenis gizi ganda. Setiap jenis gizi hanya boleh dipilih satu kali.');
                select.focus();
                return false;
            }
            duplicateNutMap[select.value] = true;

            const val = parseFloat(input.value);
            if (input.value !== '' && (isNaN(val) || val < 0)) {
                event.preventDefault();
                alert(`Nilai gizi pada baris ke-${i + 1} harus berupa angka lebih besar atau sama dengan 0.`);
                input.focus();
                return false;
            }
        }
    }

    return true;
};

/* ==========================================================================
   PANEL 4: POP-UP MODAL KECIL TAMBAH KATEGORI / JENIS GIZI BARU (AJAX)
   ========================================================================== */
window.openModalAddNutritionType = function () {
    const modal = document.getElementById('modalAddNutritionType');
    const form = document.getElementById('formAddNutritionType');
    const errAlert = document.getElementById('nutritionTypeAlertError');

    if (form) form.reset();
    if (errAlert) {
        errAlert.style.display = 'none';
        errAlert.textContent = '';
    }

    if (modal) {
        modal.style.display = 'flex';
    }
};

window.closeModalAddNutritionType = function () {
    const modal = document.getElementById('modalAddNutritionType');
    if (modal) {
        modal.style.display = 'none';
    }
};

window.submitNutritionType = async function (event) {
    event.preventDefault();

    const nameInput = document.getElementById('newNutritionName');
    const unitSelect = document.getElementById('newNutritionUnit');
    const errAlert = document.getElementById('nutritionTypeAlertError');
    const submitBtn = document.getElementById('btnSaveNutritionType');

    const nama = nameInput.value.trim();
    const satuan = unitSelect.value;

    if (!nama) {
        nameInput.focus();
        return;
    }

    submitBtn.disabled = true;
    submitBtn.textContent = 'Menyimpan...';

    if (errAlert) {
        errAlert.style.display = 'none';
        errAlert.textContent = '';
    }

    try {
        const storeUrl = window.RECIPE_CONFIG ? window.RECIPE_CONFIG.nutritionTypeStoreUrl : '/staff/nutrition-types';
        const csrfToken = window.RECIPE_CONFIG ? window.RECIPE_CONFIG.csrfToken : '';

        const response = await fetch(storeUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ nama, satuan })
        });

        const result = await response.json();

        if (!response.ok) {
            const message = result.message || (result.errors ? Object.values(result.errors).flat().join(', ') : 'Gagal menyimpan jenis gizi.');
            throw new Error(message);
        }

        const newType = result.data;

        // 1. Tambahkan ke master data lokal JavaScript
        window.AVAILABLE_NUTRITION_TYPES = window.AVAILABLE_NUTRITION_TYPES || [];
        window.AVAILABLE_NUTRITION_TYPES.push({
            id: newType.id,
            nama: newType.nama,
            satuan: newType.satuan
        });

        // 2. Tambahkan opsi baru ke semua dropdown gizi di form
        const container = document.getElementById('recipeNutritionsContainer');
        if (container) {
            const allSelects = container.querySelectorAll('.nutrition-type-select');
            allSelects.forEach(select => {
                const opt = document.createElement('option');
                opt.value = newType.id;
                opt.setAttribute('data-satuan', newType.satuan);
                opt.textContent = `${newType.nama} (${newType.satuan})`;
                select.appendChild(opt);
            });

            // 3. Langsung pilih jenis gizi baru ini di baris terakhir yang kosong, atau tambahkan baris baru
            let selectedInExisting = false;
            for (let i = 0; i < allSelects.length; i++) {
                if (!allSelects[i].value) {
                    allSelects[i].value = newType.id;
                    const row = allSelects[i].closest('.nutrition-row');
                    if (row) {
                        const unitLabel = row.querySelector('.nutrition-unit-label');
                        if (unitLabel) unitLabel.textContent = newType.satuan;
                    }
                    selectedInExisting = true;
                    break;
                }
            }

            if (!selectedInExisting) {
                addNutritionRow(newType.id, '');
            }
        }

        // 4. Tutup modal kecil & reset
        closeModalAddNutritionType();

    } catch (err) {
        console.error(err);
        if (errAlert) {
            errAlert.style.display = 'block';
            errAlert.textContent = err.message || 'Gagal menyimpan jenis gizi baru.';
        }
    } finally {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Simpan';
    }
};

/**
 * Utility escape HTML
 */
function escapeHtml(text) {
    if (!text) return '';
    return String(text)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}
