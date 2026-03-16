import { Controller } from '@hotwired/stimulus';
import $ from 'jquery';

export default class extends Controller {
    static values = { url: String }
    // Add all the targets for the different pipeline stages
    static targets = [
        "triageTableBody", "countTriage",
        "bedTableBody", "countBed",
        "treatmentTableBody", "countTreatment" // For later!
    ]

    connect() {
        this.eventSource = new EventSource(this.urlValue);

        this.eventSource.onmessage = (event) => {
            const data = JSON.parse(event.data);

            // 1. GLOBAL REMOVAL: Does this emergency already exist in ANY table?
            if (data.id) {
                const $existingRow = $(`#emergencia-${data.id}`);

                if ($existingRow.length > 0) {
                    // Find the DataTable it belongs to
                    const $table = $existingRow.closest('table');
                    const dtExisting = $table.DataTable();

                    // Remove it from the DataTable
                    dtExisting.row($existingRow).remove().draw(false);

                    // Decrement the correct badge
                    const targetName = $table.find('tbody').data('mercure-er-target');
                    if (targetName === 'triageTableBody') this.updateBadge(this.countTriageTarget, -1);
                    if (targetName === 'bedTableBody') this.updateBadge(this.countBedTarget, -1);
                    if (targetName === 'treatmentTableBody') this.updateBadge(this.countTreatmentTarget, -1);
                }
            }

            // 2. INSERTION: Where does the new row belong?
            const $newRow = $(data.html);

            if (data.estado === 'waiting_triage') {
                this.insertRowIntoTable(this.triageTableBodyTarget, $newRow);
                this.updateBadge(this.countTriageTarget, 1);
            }
            else if (data.estado === 'waiting_bed') {
                this.insertRowIntoTable(this.bedTableBodyTarget, $newRow);
                this.updateBadge(this.countBedTarget, 1);
            }
            else if (data.estado === 'in_treatment') {
                this.insertRowIntoTable(this.treatmentTableBodyTarget, $newRow);
                this.updateBadge(this.countTreatmentTarget, 1);
            }
        };
    }

    disconnect() {
        if (this.eventSource) this.eventSource.close();
    }

    // Helper method to keep the code DRY
    insertRowIntoTable(targetBody, $row) {
        const dt = $(targetBody).closest('table').DataTable();
        dt.row.add($row).draw(false);

        $row.addClass('table-warning');
        setTimeout(() => $row.removeClass('table-warning'), 3000);
    }

    // Helper method for badge math
    updateBadge(badgeTarget, changeAmount) {
        let currentCount = parseInt(badgeTarget.innerText) || 0;
        badgeTarget.innerText = Math.max(0, currentCount + changeAmount);
    }
}
