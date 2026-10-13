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

        if (activeRow) activeRow.classList.remove('cr-sel');
        activeRow = row;
        if (row) row.classList.add('cr-sel');

        // Bind decision buttons inside cloned template
        bindDecisionButtons(id);

        drawer.focus();
    }

    function closeDetail() {
        drawer.classList.remove('open');
        backdrop.classList.remove('open');
        drawer.setAttribute('aria-hidden', 'true');

        if (activeRow) {
            activeRow.classList.remove('cr-sel');
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

    // Filter Date Popover
    var dateBtn    = document.getElementById('dateBtn');
    var datePop    = document.getElementById('datePop');
    var dateClear  = document.getElementById('dateClear');
    var filterForm = document.getElementById('filterForm');

    function toggleDate(open) {
        if (!datePop || !dateBtn) return;
        var show = typeof open === 'boolean' ? open : !datePop.classList.contains('open');
        datePop.classList.toggle('open', show);
        dateBtn.setAttribute('aria-expanded', show ? 'true' : 'false');
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
            var d = document.getElementById('dari');
            var s = document.getElementById('sampai');
            if (d) d.value = '';
            if (s) s.value = '';
            filterForm.submit();
        });
    }

    // Approve & Reject Modals
    var approveModal = document.getElementById('approveModal');
    var rejectModal  = document.getElementById('rejectModal');
    var approveForm  = document.getElementById('approveForm');
    var rejectForm   = document.getElementById('rejectForm');

    function bindDecisionButtons(id) {
        var approveBtn = body.querySelector('[data-action-approve]');
        var rejectBtn  = body.querySelector('[data-action-reject]');

        if (approveBtn) {
            approveBtn.addEventListener('click', function () {
                var url = approveBtn.getAttribute('data-action-approve');
                var code = approveBtn.getAttribute('data-code');
                document.getElementById('approveTargetCode').textContent = code;
                approveForm.setAttribute('action', url);
                approveModal.classList.add('open');
            });
        }

        if (rejectBtn) {
            rejectBtn.addEventListener('click', function () {
                var url = rejectBtn.getAttribute('data-action-reject');
                var code = rejectBtn.getAttribute('data-code');
                document.getElementById('rejectTargetCode').textContent = code;
                rejectForm.setAttribute('action', url);
                rejectModal.classList.add('open');
            });
        }
    }

    // Close Modals
    document.querySelectorAll('[data-close-modal]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            if (approveModal) approveModal.classList.remove('open');
            if (rejectModal) rejectModal.classList.remove('open');
        });
    });

    if (approveModal) {
        approveModal.addEventListener('click', function (e) {
            if (e.target === approveModal) approveModal.classList.remove('open');
        });
    }

    if (rejectModal) {
        rejectModal.addEventListener('click', function (e) {
            if (e.target === rejectModal) rejectModal.classList.remove('open');
        });
    }

    // Escape listener
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeDetail();
            toggleDate(false);
            if (approveModal) approveModal.classList.remove('open');
            if (rejectModal) rejectModal.classList.remove('open');
        }
    });
})();
