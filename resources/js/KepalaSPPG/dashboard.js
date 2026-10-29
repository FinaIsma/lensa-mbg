/**
 * Dashboard Kepala SPPG - Lensa MBG
 * Handles 7-day Production & Distribution Bar Chart
 */
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        init7HariChart();
    });

    function init7HariChart() {
        const canvas = document.getElementById('dashboard7HariChart');
        if (!canvas) return;

        if (typeof Chart === 'undefined') {
            console.warn('Chart.js is not loaded.');
            return;
        }

        const config = window.dashboardChartConfig || {
            labels: ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'],
            produksi: [0, 0, 0, 0, 300, 250, 0],
            terkirim: [0, 0, 0, 0, 288, 210, 0]
        };

        const ctx = canvas.getContext('2d');

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: config.labels,
                datasets: [
                    {
                        label: 'Porsi diproduksi',
                        data: config.produksi,
                        backgroundColor: '#071E49',
                        borderRadius: 6,
                        borderSkipped: false,
                        barPercentage: 0.7,
                        categoryPercentage: 0.6
                    },
                    {
                        label: 'Porsi terkirim',
                        data: config.terkirim,
                        backgroundColor: '#38BDF8',
                        borderRadius: 6,
                        borderSkipped: false,
                        barPercentage: 0.7,
                        categoryPercentage: 0.6
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false // Using custom HTML legend below canvas
                    },
                    tooltip: {
                        backgroundColor: '#071E49',
                        titleFont: { family: 'Poppins, Plus Jakarta Sans, sans-serif', size: 12, weight: 'bold' },
                        bodyFont: { family: 'Poppins, Plus Jakarta Sans, sans-serif', size: 12 },
                        padding: 10,
                        cornerRadius: 8,
                        callbacks: {
                            label: function (ctx) {
                                return ` ${ctx.dataset.label}: ${ctx.raw} porsi`;
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
                            font: { family: 'Poppins, Plus Jakarta Sans, sans-serif', size: 11, weight: '600' }
                        }
                    },
                    y: {
                        beginAtZero: true,
                        suggestedMax: 350,
                        grid: {
                            color: '#F1F5F9',
                            drawBorder: false
                        },
                        ticks: {
                            stepSize: 100,
                            color: '#94A3B8',
                            font: { family: 'Poppins, Plus Jakarta Sans, sans-serif', size: 11 }
                        }
                    }
                }
            }
        });
    }
})();
