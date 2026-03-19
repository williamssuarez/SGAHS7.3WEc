import { Controller } from '@hotwired/stimulus';
import Swal from 'sweetalert2';
import $ from 'jquery';
import 'select2';

export default class extends Controller {
    static values = { url: String }

    async editModal(event) {
        event.preventDefault();

        Swal.fire({
            title: 'Cargando...',
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => Swal.showLoading()
        });

        try {
            const response = await fetch(this.urlValue, {
                credentials: 'same-origin',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const htmlContent = await response.text();

            Swal.fire({
                title: 'Editar descripcion de paciente',
                html: htmlContent,
                showCancelButton: true,
                confirmButtonText: 'Guardar',
                cancelButtonText: 'Cancelar',
                allowOutsideClick: false,
                allowEscapeKey: false,
                preConfirm: async () => {
                    const form = document.getElementById('edit-patient-form');
                    const formData = new FormData(form);

                    try {
                        const postResponse = await fetch(form.action, {
                            method: 'POST',
                            body: formData,
                            credentials: 'same-origin',
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        });

                        const result = await postResponse.json();

                        if (!result.success) {
                            throw new Error(result.error || 'Error al guardar el nombre temporal paciente: '+result.message);
                        }
                        return result;
                    } catch (error) {
                        Swal.showValidationMessage(error.message);
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Guardado!',
                        text: result.value.message,
                        timer: 1500,
                        showConfirmButton: false
                    });

                    // 1. Check if we are on the Dashboard page by looking for the header ID
                    const patientNameHeader = document.getElementById('patientName');

                    if (patientNameHeader) {
                        // Inject the new HTML using the data from our PHP JSON response
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
                }
            });

        } catch (error) {
            Swal.fire('Error', 'No se pudo cargar el formulario.', 'error');
        }
    }
}
