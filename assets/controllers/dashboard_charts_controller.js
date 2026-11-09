import { Controller } from '@hotwired/stimulus';
import Chart from 'chart.js/auto';

export default class extends Controller {
    static values = {
        endpoint: String
    }

    static targets = [
        "card1", "card2", "card3", "card4",
        "barLoading", "barChart",
        "pieLoading", "pieEmpty", "pieChart"
    ]

    connect() {
        this.barChartInstance = null;
        this.pieChartInstance = null;

        // Escuchar cambios de tema (Dark / Light) en tiempo real
        this.themeListener = () => this.applyThemeToCharts();
        window.addEventListener('theme:changed', this.themeListener);

        if (this.endpointValue) {
            this.loadDashboardData();
        } else {
            console.error("Dashboard endpoint is missing.");
        }
    }

    disconnect() {
        if (this.themeListener) {
            window.removeEventListener('theme:changed', this.themeListener);
        }
        if (this.barChartInstance) {
            this.barChartInstance.destroy();
            this.barChartInstance = null;
        }
        if (this.pieChartInstance) {
            this.pieChartInstance.destroy();
            this.pieChartInstance = null;
        }
    }

    isDarkMode() {
        return document.documentElement.getAttribute('data-bs-theme') === 'dark';
    }

    getThemeColors() {
        const isDark = this.isDarkMode();
        return {
            textColor: isDark ? '#a1a1a6' : '#6e6e73',
            gridColor: isDark ? 'rgba(255, 255, 255, 0.08)' : 'rgba(0, 0, 0, 0.05)',
            doughnutBorder: isDark ? '#1c1c1e' : '#ffffff',
            tooltipBg: isDark ? 'rgba(30, 30, 32, 0.95)' : 'rgba(255, 255, 255, 0.96)',
            tooltipTitle: isDark ? '#ffffff' : '#1d1d1f',
            tooltipBody: isDark ? '#e5e5ea' : '#3a3a3c',
            tooltipBorder: isDark ? 'rgba(255, 255, 255, 0.12)' : 'rgba(0, 0, 0, 0.08)',
        };
    }

    applyThemeToCharts() {
        const theme = this.getThemeColors();

        if (this.barChartInstance) {
            this.barChartInstance.options.scales.x.ticks.color = theme.textColor;
            this.barChartInstance.options.scales.y.ticks.color = theme.textColor;
            this.barChartInstance.options.scales.y.grid.color = theme.gridColor;
            this.barChartInstance.options.plugins.legend.labels.color = theme.textColor;
            this.barChartInstance.options.plugins.tooltip.backgroundColor = theme.tooltipBg;
            this.barChartInstance.options.plugins.tooltip.titleColor = theme.tooltipTitle;
            this.barChartInstance.options.plugins.tooltip.bodyColor = theme.tooltipBody;
            this.barChartInstance.options.plugins.tooltip.borderColor = theme.tooltipBorder;
            this.barChartInstance.update();
        }

        if (this.pieChartInstance) {
            this.pieChartInstance.data.datasets.forEach(ds => {
                ds.borderColor = theme.doughnutBorder;
            });
            this.pieChartInstance.options.plugins.legend.labels.color = theme.textColor;
            this.pieChartInstance.options.plugins.tooltip.backgroundColor = theme.tooltipBg;
            this.pieChartInstance.options.plugins.tooltip.titleColor = theme.tooltipTitle;
            this.pieChartInstance.options.plugins.tooltip.bodyColor = theme.tooltipBody;
            this.pieChartInstance.options.plugins.tooltip.borderColor = theme.tooltipBorder;
            this.pieChartInstance.update();
        }
    }

    async loadDashboardData() {
        try {
            const response = await fetch(this.endpointValue);
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            
            const data = await response.json();
            
            // 1. Actualizar valores de tarjetas métricas
            if (this.hasCard1Target) this.card1Target.innerText = data.cards.card1;
            if (this.hasCard2Target) this.card2Target.innerText = data.cards.card2;
            if (this.hasCard3Target) this.card3Target.innerText = data.cards.card3;
            if (this.hasCard4Target) this.card4Target.innerText = data.cards.card4;
            
            // 2. Ocultar spinners de carga
            if (this.hasBarLoadingTarget) this.barLoadingTarget.classList.replace('d-flex', 'd-none');
            if (this.hasBarChartTarget) this.barChartTarget.classList.remove('d-none');
            
            if (this.hasPieLoadingTarget) this.pieLoadingTarget.classList.replace('d-flex', 'd-none');

            // 3. Renderizar Gráfico de Líneas/Área
            if (this.hasBarChartTarget && data.charts && data.charts.bar) {
                this.initBarChart(data.charts.bar);
            }

            // 4. Renderizar Gráfico Doughnut / Pie
            if (this.hasPieChartTarget && data.charts && data.charts.pie) {
                if (data.charts.pie.totalHoy > 0) {
                    this.pieChartTarget.classList.remove('d-none');
                    this.initPieChart(data.charts.pie);
                } else if (this.hasPieEmptyTarget) {
                    this.pieEmptyTarget.classList.replace('d-none', 'd-flex');
                }
            }

        } catch (error) {
            console.error('Error loading dashboard data:', error);
            if (this.hasCard1Target) this.card1Target.innerText = '-';
            if (this.hasCard2Target) this.card2Target.innerText = '-';
            if (this.hasCard3Target) this.card3Target.innerText = '-';
            if (this.hasCard4Target) this.card4Target.innerText = '-';
        }
    }

    initBarChart(chartData) {
        if (this.barChartInstance) {
            this.barChartInstance.destroy();
        }

        const ctx = this.barChartTarget.getContext('2d');
        const theme = this.getThemeColors();

        this.barChartInstance = new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartData.labels,
                datasets: chartData.datasets.map(ds => ({
                    ...ds,
                    pointRadius: 3,
                    pointHoverRadius: 6,
                    borderWidth: 2.5
                }))
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: theme.textColor, font: { size: 11.5 } }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: { precision: 0, color: theme.textColor, font: { size: 11.5 } },
                        grid: { color: theme.gridColor }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            color: theme.textColor,
                            usePointStyle: true,
                            boxWidth: 8,
                            padding: 16,
                            font: { size: 12, weight: '500' }
                        }
                    },
                    tooltip: {
                        backgroundColor: theme.tooltipBg,
                        titleColor: theme.tooltipTitle,
                        bodyColor: theme.tooltipBody,
                        borderColor: theme.tooltipBorder,
                        borderWidth: 1,
                        padding: 10,
                        cornerRadius: 10,
                        usePointStyle: true
                    }
                }
            }
        });
    }

    initPieChart(chartData) {
        if (this.pieChartInstance) {
            this.pieChartInstance.destroy();
        }

        const ctx = this.pieChartTarget.getContext('2d');
        const theme = this.getThemeColors();

        this.pieChartInstance = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: chartData.labels,
                datasets: chartData.datasets.map(ds => ({
                    ...ds,
                    borderWidth: 2,
                    borderColor: theme.doughnutBorder
                }))
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            color: theme.textColor,
                            usePointStyle: true,
                            boxWidth: 8,
                            padding: 12,
                            font: { size: 12, weight: '500' }
                        }
                    },
                    tooltip: {
                        backgroundColor: theme.tooltipBg,
                        titleColor: theme.tooltipTitle,
                        bodyColor: theme.tooltipBody,
                        borderColor: theme.tooltipBorder,
                        borderWidth: 1,
                        padding: 10,
                        cornerRadius: 10
                    }
                }
            }
        });
    }
}
