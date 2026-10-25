window.initDashboardCharts = function (config) {
    if (!config) return;

    const { trenChartData, distribusiStatusData } = config;

    // 1. Chart 1: Tren Pendaftaran SPPG (Smooth Line Chart with Year Switcher)
    const trenCanvas = document.getElementById('trenSppgChart');
    if (trenCanvas && trenChartData && typeof Chart !== 'undefined') {
        const trenCtx = trenCanvas.getContext('2d');

        // Linear gradient for fill under the line
        const gradient = trenCtx.createLinearGradient(0, 0, 0, 260);
        gradient.addColorStop(0, 'rgba(37, 99, 235, 0.28)');
        gradient.addColorStop(0.7, 'rgba(37, 99, 235, 0.05)');
        gradient.addColorStop(1, 'rgba(37, 99, 235, 0.0)');

        const currentYr = trenChartData.currentYear || '2026';
        const initialCounts = (trenChartData.years && trenChartData.years[currentYr])
            ? trenChartData.years[currentYr]
            : [1, 4, 3, 5, 2, 8, 3, 7, 8, 9, 8];

        const trenChart = new Chart(trenCtx, {
            type: 'line',
            data: {
                labels: trenChartData.labels,
                datasets: [
                    {
                        label: 'Pendaftaran SPPG',
                        data: initialCounts,
                        borderColor: '#2563EB',
                        backgroundColor: gradient,
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
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: '#0F172A',
                        titleFont: { family: 'Plus Jakarta Sans', size: 12, weight: 'bold' },
                        bodyFont: { family: 'Plus Jakarta Sans', size: 12 },
                        padding: 10,
                        cornerRadius: 8,
                        displayColors: false,
                        callbacks: {
                            label: function (ctx) {
                                return ` Pendaftaran: ${ctx.parsed.y} SPPG`;
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
                            font: { family: 'Plus Jakarta Sans', size: 11, weight: '500' }
                        }
                    },
                    y: {
                        min: 0,
                        suggestedMax: 10,
                        ticks: {
                            stepSize: 2,
                            color: '#64748B',
                            font: { family: 'Plus Jakarta Sans', size: 11, weight: '500' }
                        },
                        grid: {
                            color: '#F1F5F9',
                            drawBorder: false
                        }
                    }
                }
            }
        });

        const trenPeriodeFilter = document.getElementById('trenPeriodeFilter');
        if (trenPeriodeFilter) {
            trenPeriodeFilter.addEventListener('change', function (e) {
                const yr = e.target.value;
                if (trenChartData.years && trenChartData.years[yr]) {
                    trenChart.data.datasets[0].data = trenChartData.years[yr];
                    const maxVal = Math.max(...trenChartData.years[yr], 5);
                    trenChart.options.scales.y.suggestedMax = Math.ceil((maxVal + 2) / 2) * 2;
                    trenChart.update();
                }
            });
        }
    }

    // 2. Chart 2: Distribusi Status SPPG (Switchable Donut & Bar Chart)
    const distCanvas = document.getElementById('distribusiStatusChart');
    if (distCanvas && distribusiStatusData && typeof Chart !== 'undefined') {
        let distribusiChart = null;

        const chartColors = distribusiStatusData.colors || ['#2563EB', '#F59E0B', '#EF4444'];
        const chartLabels = distribusiStatusData.labels || ['Aktif', 'Menunggu Verifikasi', 'Nonaktif'];
        const chartCounts = distribusiStatusData.counts || [104, 15, 9];

        function renderDistribusiChart(mode) {
            if (distribusiChart) {
                distribusiChart.destroy();
            }

            const distCtx = distCanvas.getContext('2d');

            if (mode === 'bar') {
                distribusiChart = new Chart(distCtx, {
                    type: 'bar',
                    data: {
                        labels: chartLabels,
                        datasets: [
                            {
                                data: chartCounts,
                                backgroundColor: chartColors,
                                borderRadius: {
                                    topLeft: 8,
                                    topRight: 8
                                },
                                borderSkipped: false,
                                barPercentage: 0.45,
                                categoryPercentage: 0.8
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
                                backgroundColor: '#0F172A',
                                titleFont: { family: 'Plus Jakarta Sans', size: 12, weight: 'bold' },
                                bodyFont: { family: 'Plus Jakarta Sans', size: 12 },
                                padding: 10,
                                cornerRadius: 8,
                                displayColors: true,
                                callbacks: {
                                    title: function (items) {
                                        return items[0].label;
                                    },
                                    label: function (item) {
                                        const val = item.raw;
                                        const total = chartCounts.reduce((a, b) => a + b, 0);
                                        const pct = total > 0 ? Math.round((val / total) * 100) : 0;
                                        return ` ${item.label}: ${val} (${pct}%)`;
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
                                    font: { family: 'Plus Jakarta Sans', size: 11, weight: '500' }
                                }
                            },
                            y: {
                                min: 0,
                                ticks: {
                                    color: '#64748B',
                                    font: { family: 'Plus Jakarta Sans', size: 11, weight: '500' }
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
                // Donut Chart Mode
                distribusiChart = new Chart(distCtx, {
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
                                backgroundColor: '#0F172A',
                                titleFont: { family: 'Plus Jakarta Sans', size: 12, weight: 'bold' },
                                bodyFont: { family: 'Plus Jakarta Sans', size: 12 },
                                padding: 10,
                                cornerRadius: 8,
                                displayColors: true,
                                callbacks: {
                                    title: function (items) {
                                        return items[0].label;
                                    },
                                    label: function (item) {
                                        const val = item.raw;
                                        const total = chartCounts.reduce((a, b) => a + b, 0);
                                        const pct = total > 0 ? Math.round((val / total) * 100) : 0;
                                        return ` ${item.label}: ${val} (${pct}%)`;
                                    }
                                }
                            }
                        }
                    }
                });
            }
        }

        // Default to Donut
        renderDistribusiChart('donut');

        const visualisasiFilter = document.getElementById('distribusiVisualisasiFilter');
        if (visualisasiFilter) {
            visualisasiFilter.addEventListener('change', function (e) {
                renderDistribusiChart(e.target.value);
            });
        }
    }
};

document.addEventListener('DOMContentLoaded', function () {
    if (window.dashboardData) {
        window.initDashboardCharts(window.dashboardData);
    }
});
