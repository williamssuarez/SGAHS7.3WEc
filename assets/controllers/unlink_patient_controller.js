import { Controller } from '@hotwired/stimulus';
import Swal from 'sweetalert2';

export default class extends Controller {
    static values = { url: String }

    async unlink(event) {
        event.preventDefault();

        Swal.fire({
            title: '¿Desvincular paciente?',
            text: 'Esto regresará el registro al nombre temporal original.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545', // Bootstrap Danger Red
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, desvincular',
            cancelButtonText: 'Cancelar',
            showLoaderOnConfirm: true,
            preConfirm: async () => {
                try {
                    // Send a POST request to the server
                    const response = await fetch(this.urlValue, {
                        method: 'POST',
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    });

                    const result = await response.json();

                    if (!result.success) {
                        throw new Error(result.error || 'Error al desvincular el paciente: '+result.message);
                    }
                    return result;
                } catch (error) {
                    Swal.showValidationMessage(error.message);
                }
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    icon: 'success',
                    title: '¡Desvinculado!',
                    text: result.value.message,
                    timer: 1500,
                    showConfirmButton: false
                });

                const patientNameHeader = document.getElementById('patientName');
                if (patientNameHeader) {
                    patientNameHeader.innerHTML = `
                            <span class="text-danger"><i class="bi bi-incognito"></i> ${result.value.paciente_nombre}</span>

                            <button type="button"
                                    class="btn btn-outline-primary"
                                    data-controller="edit-patient"
                                    data-edit-patient-url-value="${result.value.paciente_url}"
                                    data-action="click->edit-patient#editModal"
                                    title="Editar nombre temporal">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </button>
                        `;
                }

                this.element.remove();

                // Note: If you want the "Identificar Paciente" button to instantly reappear
                // without refreshing, you would inject it back into the DOM here!
                const patientIdentifierBtn = document.getElementById('assignPatientBtn');
                if (patientIdentifierBtn) {
                    patientIdentifierBtn.innerHTML = '<i class="bi bi-person-lines-fill"></i> Identificar Paciente';
                }
            }
        });
    }
}
