// assets/controllers/citagroupdaychart_controller.js
import { Controller } from '@hotwired/stimulus';
import Chart from 'chart.js/auto';

export default class extends Controller {
    static values = {
        labels: Array,
        datasets: Array, // Changed from 'values' to 'datasets'
        stacking: Boolean
    }

    connect() {
        const ctx = this.element.querySelector('canvas').getContext('2d');

        new Chart(ctx, {
            type: 'bar', // 'bar' often looks better for stacked state data
            data: {
                labels: this.labelsValue,
                datasets: this.datasetsValue
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: { stacked: this.stackingValue }, // Optional: stacks bars on top of each other
                    y: { stacked: this.stackingValue, beginAtZero: true }
                },
                plugins: {
                    legend: { position: 'top' }
                }
            }
        });
    }
}
