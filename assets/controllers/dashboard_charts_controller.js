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
        if (this.endpointValue) {
            this.loadDashboardData();
        } else {
            console.error("Dashboard endpoint is missing.");
        }
    }

    async loadDashboardData() {
        try {
            const response = await fetch(this.endpointValue);
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            
            const data = await response.json();
            
            // 1. Update metric cards if they exist in HTML
            if (this.hasCard1Target) this.card1Target.innerText = data.cards.card1;
            if (this.hasCard2Target) this.card2Target.innerText = data.cards.card2;
            if (this.hasCard3Target) this.card3Target.innerText = data.cards.card3;
            if (this.hasCard4Target) this.card4Target.innerText = data.cards.card4;
            
            // 2. Hide loaders
            if (this.hasBarLoadingTarget) this.barLoadingTarget.classList.replace('d-flex', 'd-none');
            if (this.hasBarChartTarget) this.barChartTarget.classList.remove('d-none');
            
            if (this.hasPieLoadingTarget) this.pieLoadingTarget.classList.replace('d-flex', 'd-none');

            // 3. Render Bar/Area Chart
            if (this.hasBarChartTarget && data.charts && data.charts.bar) {
                this.initBarChart(data.charts.bar);
            }

            // 4. Render Pie Chart (or empty state)
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
        const ctx = this.barChartTarget.getContext('2d');
        new Chart(ctx, {
            type: 'line', // Area chart
            data: {
                labels: chartData.labels,
                datasets: chartData.datasets // Server provides everything: label, colors, data, tension, fill
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        grid: { display: false }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: { precision: 0 }
                    }
                },
                plugins: {
                    legend: { position: 'top' }
                }
            }
        });
    }

    initPieChart(chartData) {
        const ctx = this.pieChartTarget.getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: chartData.labels,
                datasets: chartData.datasets // Server provides data and backgroundColor array
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'right' }
                }
            }
        });
    }
}
