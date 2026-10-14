window.initDashboardCharts = function (config) {
    if (!config) return;

    const { trenChartData, sppgCrPeriodMap, koreksiChartData } = config;

    // --- 1. Tren Pendaftaran SPPG Multi-line Chart ---
    const trenCanvas = document.getElementById('trenSppgChart');
    if (trenCanvas && trenChartData && typeof Chart !== 'undefined') {
        const trenCtx = trenCanvas.getContext('2d');
        const trenData = {
            labels: trenChartData.labels,
            datasets: [
                {
                    label: 'Disetujui',
                    data: trenChartData.disetujui,
                    borderColor: '#10B981',
                    backgroundColor: '#10B981',
                    borderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointBackgroundColor: '#10B981',
                    pointBorderColor: '#FFFFFF',
                    pointBorderWidth: 1.5,
                    tension: 0.35
                },
                {
                    label: 'Menunggu',
                    data: trenChartData.menunggu,
                    borderColor: '#3B82F6',
                    backgroundColor: '#3B82F6',
                    borderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointBackgroundColor: '#3B82F6',
                    pointBorderColor: '#FFFFFF',
                    pointBorderWidth: 1.5,
                    tension: 0.35
                },
                {
                    label: 'Ditolak',
                    data: trenChartData.ditolak,
                    borderColor: '#EF4444',
                    backgroundColor: '#EF4444',
                    borderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointBackgroundColor: '#EF4444',
                    pointBorderColor: '#FFFFFF',
                    pointBorderWidth: 1.5,
                    tension: 0.35
                },
                {
                    label: 'Koreksi',
                    data: trenChartData.koreksi,
                    borderColor: '#F59E0B',
                    backgroundColor: '#F59E0B',
                    borderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointBackgroundColor: '#F59E0B',
                    pointBorderColor: '#FFFFFF',
                    pointBorderWidth: 1.5,
                    tension: 0.35
                }
            ]
        };

        const trenChart = new Chart(trenCtx, {
            type: 'line',
            data: trenData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: '#0F172A',
                        titleFont: { family: 'Plus Jakarta Sans', size: 12 },
                        bodyFont: { family: 'Plus Jakarta Sans', size: 12 },
                        padding: 10,
                        cornerRadius: 8
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: '#64748B',
                            font: { family: 'Plus Jakarta Sans', size: 11 }
                        }
                    },
                    y: {
                        min: 0,
                        suggestedMax: 30,
                        ticks: {
                            stepSize: 10,
                            color: '#64748B',
                            font: { family: 'Plus Jakarta Sans', size: 11 }
                        },
                        grid: {
                            color: '#F1F5F9',
                            drawBorder: false
                        }
                    }
                }
            }
        });

        // Status & Periode filter on Tren SPPG Chart
        const statusFilter = document.getElementById('statusFilter');
        const periodeFilter = document.getElementById('periodeFilter');

        function applyStatusFilter() {
            const val = statusFilter ? statusFilter.value.toLowerCase() : 'all';
            trenChart.data.datasets.forEach(function (ds) {
                if (val === 'all') {
                    ds.hidden = false;
                } else {
                    ds.hidden = (ds.label.toLowerCase() !== val);
                }
            });
        }

        if (periodeFilter) {
            periodeFilter.addEventListener('change', function (e) {
                const periodKey = e.target.value;
                const periodData = (trenChartData.periods && trenChartData.periods[periodKey])
                    ? trenChartData.periods[periodKey]
                    : trenChartData;

                trenChart.data.labels = periodData.labels;
                trenChart.data.datasets[0].data = periodData.disetujui;
                trenChart.data.datasets[1].data = periodData.menunggu;
                trenChart.data.datasets[2].data = periodData.ditolak;
                trenChart.data.datasets[3].data = periodData.koreksi;

                applyStatusFilter();

                const allNums = [
                    ...periodData.disetujui,
                    ...periodData.menunggu,
                    ...periodData.ditolak,
                    ...periodData.koreksi
                ];
                const maxVal = Math.max(...allNums, 5);
                trenChart.options.scales.y.suggestedMax = Math.ceil((maxVal + 5) / 5) * 5;

                trenChart.update();
            });
        }

        if (statusFilter) {
            statusFilter.addEventListener('change', function () {
                applyStatusFilter();
                trenChart.update();
            });
        }
    }

    // --- 2. Status Pengajuan Koreksi Bar Chart ---
    const koreksiCanvas = document.getElementById('statusKoreksiChart');
    if (koreksiCanvas && koreksiChartData && sppgCrPeriodMap && typeof Chart !== 'undefined') {
        const koreksiCtx = koreksiCanvas.getContext('2d');

        const topLabelsPlugin = {
            id: 'topLabelsPlugin',
            afterDatasetsDraw(chart) {
                const { ctx } = chart;
                chart.data.datasets.forEach((dataset, i) => {
                    const meta = chart.getDatasetMeta(i);
                    meta.data.forEach((bar, index) => {
                        const value = dataset.data[index];
                        ctx.save();
                        ctx.fillStyle = '#0F172A';
                        ctx.font = 'bold 11px Plus Jakarta Sans, sans-serif';
                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'bottom';
                        ctx.fillText(value, bar.x, bar.y - 4);
                        ctx.restore();
                    });
                });
            }
        };

        const statusKoreksiChart = new Chart(koreksiCtx, {
            type: 'bar',
            data: {
                labels: koreksiChartData.labels,
                datasets: [{
                    data: koreksiChartData.counts,
                    backgroundColor: koreksiChartData.colors,
                    borderRadius: {
                        topLeft: 6,
                        topRight: 6
                    },
                    borderSkipped: false,
                    barPercentage: 0.35,
                    categoryPercentage: 0.8
                }]
            },
            plugins: [topLabelsPlugin],
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: '#0F172A',
                        titleFont: { family: 'Plus Jakarta Sans', size: 12 },
                        bodyFont: { family: 'Plus Jakarta Sans', size: 12 },
                        padding: 10,
                        cornerRadius: 8
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: '#64748B',
                            font: { family: 'Plus Jakarta Sans', size: 11 }
                        }
                    },
                    y: {
                        min: 0,
                        suggestedMax: 15,
                        ticks: {
                            stepSize: 5,
                            color: '#64748B',
                            font: { family: 'Plus Jakarta Sans', size: 11 }
                        },
                        grid: {
                            color: '#F1F5F9',
                            drawBorder: false
                        }
                    }
                }
            }
        });

        const koreksiPeriodeFilter = document.getElementById('koreksiPeriodeFilter');
        const sppgFilter = document.getElementById('sppgFilter');

        function updateStatusKoreksiChart() {
            const period = koreksiPeriodeFilter ? koreksiPeriodeFilter.value : 'current';
            const sppg = sppgFilter ? sppgFilter.value : 'all';

            const periodData = sppgCrPeriodMap[period] || sppgCrPeriodMap['current'] || {};
            const counts = periodData[sppg] || [0, 0, 0];

            statusKoreksiChart.data.datasets[0].data = counts;

            const maxVal = Math.max(...counts);
            statusKoreksiChart.options.scales.y.suggestedMax = maxVal > 10 ? Math.ceil((maxVal + 4) / 5) * 5 : 15;
            statusKoreksiChart.update();
        }

        if (koreksiPeriodeFilter) {
            koreksiPeriodeFilter.addEventListener('change', updateStatusKoreksiChart);
        }

        if (sppgFilter) {
            sppgFilter.addEventListener('change', updateStatusKoreksiChart);
        }
    }
};

document.addEventListener('DOMContentLoaded', function () {
    if (window.dashboardData) {
        window.initDashboardCharts(window.dashboardData);
    }
});
