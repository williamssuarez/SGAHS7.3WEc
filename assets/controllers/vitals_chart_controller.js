// assets/controllers/vitals_chart_controller.js
import { Controller } from '@hotwired/stimulus';
import Chart from 'chart.js/auto';

export default class extends Controller {
    static values = {
        labels: Array,
        temp: Array,
        fc: Array
    }

    connect() {
        const ctx = this.element.querySelector('canvas').getContext('2d');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: this.labelsValue,
                datasets: [
                    {
                        label: 'Temperatura (°C)',
                        data: this.tempValue,
                        borderColor: '#ffc107', // Warning Yellow
                        backgroundColor: '#ffc107',
                        yAxisID: 'yTemp',
                        tension: 0.3
                    },
                    {
                        label: 'Frec. Cardíaca (lpm)',
                        data: this.fcValue,
                        borderColor: '#dc3545', // Danger Red
                        backgroundColor: '#dc3545',
                        yAxisID: 'yFC',
                        tension: 0.3
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        grid: { display: false }
                    },
                    yTemp: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: { display: true, text: 'Temperatura °C' },
                        suggestedMin: 35,
                        suggestedMax: 40
                    },
                    yFC: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: { display: true, text: 'Latidos por min' },
                        grid: { drawOnChartArea: false }, // Don't overlap grid lines
                        suggestedMin: 40,
                        suggestedMax: 150
                    }
                }
            }
        });
    }
}
