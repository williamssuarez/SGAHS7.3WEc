import { Controller } from '@hotwired/stimulus';
import Swal from 'sweetalert2';
import $ from 'jquery';
import 'select2';

export default class extends Controller {
    static values = { url: String }

    async openModal(event) {
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
                title: 'Vincular Paciente',
                html: htmlContent,
                showCancelButton: true,
                confirmButtonText: 'Vincular y guardar',
                cancelButtonText: 'Cancelar',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: (popup) => {
                    $(popup).find('.ajaxSrchSelect').select2({
                        theme: "bootstrap-5",
                        width: '100%',
                        language: "es",
                        dropdownParent: $(popup),
                        ajax: {
                            url: '/paciente/autocomplete-paciente', // Match the route name/path
                            dataType: 'json',
                            delay: 250, // Wait 250ms after typing stops before sending request
                            data: function (params) {
                                return {
                                    q: params.term // search term
                                };
                            },
                            processResults: function (data) {
                                return {
                                    results: data.results
                                };
                            },
                            cache: true
                        },
                        minimumInputLength: 3, // Only search after 3 characters
                    });
                },
                preConfirm: async () => {
                    const form = document.getElementById('associate-patient-form');
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
                            throw new Error(result.error || 'Error al asignar el paciente: '+result.message);
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
                        title: '¡Vinculado!',
                        text: result.value.message,
                        timer: 1500,
                        showConfirmButton: false
                    });

                    // 1. Check if we are on the Dashboard page by looking for the header ID
                    const patientNameHeader = document.getElementById('patientName');

                    if (patientNameHeader) {
                        // Inject the new HTML using the data from our PHP JSON response
                        patientNameHeader.innerHTML = `
                            ${result.value.paciente_nombre}
                            <a class="btn btn-outline-primary" target="_blank" href="${result.value.paciente_url}">
                                <i class="fa-solid fa-address-book"></i>
                                <i class="fa-solid fa-up-right-from-square"></i>
                            </a>

                            <button type="button"
                                    class="btn btn-outline-danger"
                                    data-controller="unlink-patient"
                                    data-unlink-patient-url-value="${result.value.unlink_url}"
                                    data-action="click->unlink-patient#unlink"
                                    title="Desvincular paciente por error">
                                <i class="bi bi-person-x-fill"></i>
                            </button>
                        `;
                    }

                    // 2. Remove the button that was clicked so it can't be clicked again
                    const patientIdentifierBtn = document.getElementById('assignPatientBtn');
                    if (patientIdentifierBtn) {
                        console.log('boton encontrado');
                        patientIdentifierBtn.innerHTML = '<i class="bi bi-person-lines-fill"></i> Cambiar Paciente';
                    } else {
                        console.log('boton no encontrado');
                    }
                }
            });

        } catch (error) {
            Swal.fire('Error', 'No se pudo cargar el formulario.', 'error');
        }
    }
}
