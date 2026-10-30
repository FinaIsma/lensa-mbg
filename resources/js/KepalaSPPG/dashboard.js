/**
 * Dashboard Kepala SPPG - Lensa MBG
 * Visualisasi Grafik Tren & Kesehatan Stok (Ala Dashboard Sistem)
 */
(function () {
    'use strict';

    let trenChart = null;
    let stokChart = null;

    document.addEventListener('DOMContentLoaded', function () {
        if (typeof Chart === 'undefined') {
            console.warn('Chart.js belum dimuat.');
            return;
        }

        const config = window.dashboardChartConfig || {};
        initTrenProduksiDistribusi(config.trenChartData);
        initKesehatanStok(config.stokChartData);
    });

    // 1. Grafik 1: Tren Produksi & Distribusi (Smooth Area Line Chart)
    function initTrenProduksiDistribusi(data) {
        const canvas = document.getElementById('trenProduksiDistribusiChart');
        if (!canvas || !data) return;

        const ctx = canvas.getContext('2d');

        const gradientProduksi = ctx.createLinearGradient(0, 0, 0, 260);
        gradientProduksi.addColorStop(0, 'rgba(7, 30, 73, 0.22)');
        gradientProduksi.addColorStop(0.7, 'rgba(7, 30, 73, 0.04)');
        gradientProduksi.addColorStop(1, 'rgba(7, 30, 73, 0.0)');

        const gradientDistribusi = ctx.createLinearGradient(0, 0, 0, 260);
        gradientDistribusi.addColorStop(0, 'rgba(37, 99, 235, 0.20)');
        gradientDistribusi.addColorStop(0.7, 'rgba(37, 99, 235, 0.03)');
        gradientDistribusi.addColorStop(1, 'rgba(37, 99, 235, 0.0)');

        let currentPeriod = data.currentPeriod || '7hari';
        let currentPeriodData = data.periods[currentPeriod] || data.periods['7hari'];

        trenChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: currentPeriodData.labels,
                datasets: [
                    {
                        label: 'Porsi Diproduksi',
                        data: currentPeriodData.produksi,
                        borderColor: '#071E49',
                        backgroundColor: gradientProduksi,
                        borderWidth: 2.5,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        pointBackgroundColor: '#071E49',
                        pointBorderColor: '#FFFFFF',
                        pointBorderWidth: 2,
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: 'Porsi Terdistribusi',
                        data: currentPeriodData.distribusi,
                        borderColor: '#2563EB',
                        backgroundColor: gradientDistribusi,
                        borderWidth: 2.5,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        pointBackgroundColor: '#2563EB',
                        pointBorderColor: '#FFFFFF',
                        pointBorderWidth: 2,
                        tension: 0.4,
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: '#071E49',
                        titleFont: { family: 'Poppins, Plus Jakarta Sans, sans-serif', size: 12, weight: 'bold' },
                        bodyFont: { family: 'Poppins, Plus Jakarta Sans, sans-serif', size: 12 },
                        padding: 10,
                        cornerRadius: 8,
                        callbacks: {
                            label: function (ctx) {
                                return ` ${ctx.dataset.label}: ${ctx.parsed.y} porsi`;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: '#64748B',
                            font: { family: 'Poppins, Plus Jakarta Sans, sans-serif', size: 11, weight: '500' }
                        }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            color: '#64748B',
                            font: { family: 'Poppins, Plus Jakarta Sans, sans-serif', size: 11, weight: '500' }
                        },
                        grid: {
                            color: '#F1F5F9',
                            drawBorder: false
                        }
                    }
                }
            }
        });

        // Filter Periode Listener
        const filterSelect = document.getElementById('trenPeriodeFilter');
        if (filterSelect) {
            filterSelect.addEventListener('change', function (e) {
                const selected = e.target.value;
                const newData = data.periods[selected];
                if (newData && trenChart) {
                    trenChart.data.labels = newData.labels;
                    trenChart.data.datasets[0].data = newData.produksi;
                    trenChart.data.datasets[1].data = newData.distribusi;
                    trenChart.update();
                }
            });
        }
    }

    // 2. Grafik 2: Kondisi Kesehatan Stok Bahan (Donut & Bar Switcher)
    function initKesehatanStok(data) {
        const canvas = document.getElementById('kesehatanStokChart');
        if (!canvas || !data) return;

        const ctx = canvas.getContext('2d');
        const chartLabels = data.labels || ['Stok Aman', 'Mendekati Minimum', 'Kritis / Expired'];
        const chartCounts = data.counts || [24, 6, 3];
        const chartColors = data.colors || ['#16A34A', '#F59E0B', '#EF4444'];

        function renderChart(type) {
            if (stokChart) {
                stokChart.destroy();
            }

            if (type === 'bar') {
                stokChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: chartLabels,
                        datasets: [
                            {
                                data: chartCounts,
                                backgroundColor: chartColors,
                                borderRadius: 8,
                                barPercentage: 0.55
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                backgroundColor: '#071E49',
                                callbacks: {
                                    label: function (ctx) {
                                        return ` ${ctx.label}: ${ctx.raw} bahan`;
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    color: '#64748B',
                                    font: { family: 'Poppins, Plus Jakarta Sans, sans-serif', size: 11, weight: '500' }
                                }
                            },
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 5,
                                    color: '#64748B',
                                    font: { family: 'Poppins, Plus Jakarta Sans, sans-serif', size: 11, weight: '500' }
                                },
                                grid: {
                                    color: '#F1F5F9',
                                    drawBorder: false
                                }
                            }
                        }
                    }
                });
            } else {
                stokChart = new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: chartLabels,
                        datasets: [
                            {
                                data: chartCounts,
                                backgroundColor: chartColors,
                                borderColor: '#FFFFFF',
                                borderWidth: 3,
                                hoverOffset: 6
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '68%',
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                backgroundColor: '#071E49',
                                titleFont: { family: 'Poppins, Plus Jakarta Sans, sans-serif', size: 12, weight: 'bold' },
                                bodyFont: { family: 'Poppins, Plus Jakarta Sans, sans-serif', size: 12 },
                                padding: 10,
                                cornerRadius: 8,
                                callbacks: {
                                    label: function (item) {
                                        const val = item.raw;
                                        const total = chartCounts.reduce((a, b) => a + b, 0);
                                        const pct = total > 0 ? Math.round((val / total) * 100) : 0;
                                        return ` ${item.label}: ${val} bahan (${pct}%)`;
                                    }
                                }
                            }
                        }
                    }
                });
            }
        }

        renderChart('donut');

        const modeFilter = document.getElementById('stokVisualisasiFilter');
        if (modeFilter) {
            modeFilter.addEventListener('change', function (e) {
                renderChart(e.target.value);
            });
        }
    }
})();
