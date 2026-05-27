// assets/controllers/assign_doctor_controller.js
import { Controller } from '@hotwired/stimulus';
import Swal from 'sweetalert2';
import * as bootstrap from 'bootstrap'; // Add this if you get a Bootstrap error!

export default class extends Controller {
    static targets = ["modal", "form", "select"]
    static values = { updateUrl: String }

    connect() {
        // We use Bootstrap's JS API to handle the modal
        this.modalInstance = new bootstrap.Modal(this.modalTarget);

        // Pro-Tip: Select2 inside Bootstrap modals requires 'dropdownParent'
        // to prevent z-index bugs where the dropdown hides behind the modal.
        $(this.selectTarget).select2({
            theme: 'bootstrap-5',
            dropdownParent: $(this.modalTarget)
        });
    }

    openModal(event) {
        // Grab the ID from the button that was clicked
        const button = event.currentTarget;
        const hospitalizacionId = button.dataset.id;

        // Store it so the form submission knows where to send the request
        this.currentHospitalizacionId = hospitalizacionId;

        this.modalInstance.show();
    }

    async submitForm(event) {
        event.preventDefault();

        const medicoId = this.selectTarget.value;
        if (!medicoId) {
            Swal.fire('Atención', 'Debe seleccionar un médico', 'warning');
            return;
        }

        // Build the dynamic URL
        const url = `/hospitalizacion/${this.currentHospitalizacionId}/asignar-medico-ajax`;

        // Prepare data as FormData
        const formData = new FormData();
        formData.append('medico_id', medicoId);

        try {
            const response = await fetch(url, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (response.ok) {
                this.modalInstance.hide();

                await Swal.fire({
                    icon: 'success',
                    title: '¡Asignado!',
                    text: 'El médico fue asignado correctamente.',
                    timer: 1500,
                    showConfirmButton: false
                });

                // Reload to reflect changes in the table
                window.location.reload();
            } else {
                throw new Error('Error en el servidor');
            }
        } catch (error) {
            Swal.fire('Error', 'No se pudo asignar el médico.', 'error');
        }
    }
}
