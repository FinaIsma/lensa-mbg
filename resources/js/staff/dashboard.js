/**
 * Script Interaktivitas & Chart Dashboard Staff - Lensa MBG
 */

(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        if (typeof Chart === 'undefined') {
            console.warn('Chart.js belum dimuat.');
            return;
        }

        const config = window.staffDashboardChartConfig || {};
        initPorsiDonutChart(config.donutData);
    });

    function initPorsiDonutChart(donutData) {
        const canvas = document.getElementById('porsiDonutChart');
        if (!canvas) return;

        const ctx = canvas.getContext('2d');
        const d = donutData || { received: 0, in_transit: 0, planned: 0, cancelled: 0 };

        const totalActive = (d.received || 0) + (d.in_transit || 0) + (d.planned || 0) + (d.cancelled || 0);

        let dataValues = [d.received || 0, d.in_transit || 0, d.planned || 0, d.cancelled || 0];
        let bgColors = ['#10B981', '#F59E0B', '#071E49', '#EF4444'];
        let labels = ['Diterima', 'Dalam Pengiriman', 'Direncanakan', 'Dibatalkan'];

        // Jika data masih nol / kosong, tampilkan placeholder ring
        if (totalActive === 0) {
            dataValues = [1];
            bgColors = ['#E2E8F0'];
            labels = ['Belum ada pengiriman'];
        }

        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: dataValues,
                    backgroundColor: bgColors,
                    borderWidth: 2,
                    borderColor: '#FFFFFF',
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '74%',
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: '#071E49',
                        titleFont: { family: 'Poppins, sans-serif', size: 12, weight: 'bold' },
                        bodyFont: { family: 'Poppins, sans-serif', size: 11 },
                        padding: 10,
                        cornerRadius: 8,
                        callbacks: {
                            label: function (context) {
                                if (totalActive === 0) {
                                    return ' Belum ada data pengiriman hari ini';
                                }
                                const value = context.parsed;
                                return ` ${context.label}: ${new Intl.NumberFormat('id-ID').format(value)} porsi`;
                            }
                        }
                    }
                }
            }
        });
    }
})();

