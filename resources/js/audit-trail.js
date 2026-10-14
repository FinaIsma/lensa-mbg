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
        if (!tpl) return;

        body.innerHTML = '';
        body.appendChild(tpl.content.cloneNode(true));
        scroller.scrollTop = 0;

        drawer.classList.add('open');
        backdrop.classList.add('open');
        drawer.setAttribute('aria-hidden', 'false');

        if (activeRow) activeRow.classList.remove('au-sel');
        activeRow = row;
        if (row) row.classList.add('au-sel');

        drawer.focus();
    }

    function closeDetail() {
        drawer.classList.remove('open');
        backdrop.classList.remove('open');
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

    closeBtn.addEventListener('click', closeDetail);
    backdrop.addEventListener('click', closeDetail);

    var dateBtn   = document.getElementById('dateBtn');
    var datePop   = document.getElementById('datePop');
    var dateClear = document.getElementById('dateClear');
    var filterForm = document.getElementById('filterForm');

    function toggleDate(open) {
        var show = typeof open === 'boolean' ? open : !datePop.classList.contains('open');
        datePop.classList.toggle('open', show);
        dateBtn.setAttribute('aria-expanded', show ? 'true' : 'false');
    }

    dateBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        toggleDate();
    });

    datePop.addEventListener('click', function (e) {
        e.stopPropagation();
    });

    document.addEventListener('click', function () {
        toggleDate(false);
    });

    dateClear.addEventListener('click', function () {
        document.getElementById('dari').value = '';
        document.getElementById('sampai').value = '';
        filterForm.submit();
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeDetail();
            toggleDate(false);
        }
    });
})();