(function () {
    'use strict';

    var drawer   = document.getElementById('detailDrawer');
    var body     = document.getElementById('detailBody');
    var scroller = document.getElementById('detailScroll');
    var backdrop = document.getElementById('detailBackdrop');
    var closeBtn = document.getElementById('detailClose');
    var activeRow = null;

    function openDetail(id, row) {
        var tpl = document.getElementById('detail-' + id);
        if (!tpl || !body || !drawer) return;

        body.innerHTML = '';
        body.appendChild(tpl.content.cloneNode(true));
        if (scroller) scroller.scrollTop = 0;
        drawer.scrollTop = 0;

        drawer.classList.add('open');
        if (backdrop) backdrop.classList.add('open');
        drawer.setAttribute('aria-hidden', 'false');

        if (activeRow) activeRow.classList.remove('au-sel');
        activeRow = row;
        if (row) row.classList.add('au-sel');

        drawer.focus();
    }

    function closeDetail() {
        if (!drawer) return;
        drawer.classList.remove('open');
        if (backdrop) backdrop.classList.remove('open');
        drawer.setAttribute('aria-hidden', 'true');

        if (activeRow) {
            activeRow.classList.remove('au-sel');
            activeRow = null;
        }
    }

    document.querySelectorAll('[data-detail]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            openDetail(btn.getAttribute('data-detail'), btn.closest('tr'));
        });
    });

    if (closeBtn) closeBtn.addEventListener('click', closeDetail);
    if (backdrop) backdrop.addEventListener('click', closeDetail);

    var dateBtn   = document.getElementById('dateBtn');
    var datePop   = document.getElementById('datePop');
    var dateClear = document.getElementById('dateClear');
    var filterForm = document.getElementById('filterForm');

    function toggleDate(open) {
        if (!datePop) return;
        var show = typeof open === 'boolean' ? open : !datePop.classList.contains('open');
        datePop.classList.toggle('open', show);
        if (dateBtn) dateBtn.setAttribute('aria-expanded', show ? 'true' : 'false');
    }

    if (dateBtn) {
        dateBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            toggleDate();
        });
    }

    if (datePop) {
        datePop.addEventListener('click', function (e) {
            e.stopPropagation();
        });
    }

    document.addEventListener('click', function () {
        toggleDate(false);
    });

    if (dateClear && filterForm) {
        dateClear.addEventListener('click', function () {
            var dari = document.getElementById('dari');
            var sampai = document.getElementById('sampai');
            if (dari) dari.value = '';
            if (sampai) sampai.value = '';
            filterForm.submit();
        });
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeDetail();
            toggleDate(false);
        }
    });
})();