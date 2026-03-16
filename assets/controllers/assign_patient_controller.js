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
                    const form = document.getElementById('assign-patient-form');
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
                            throw new Error(result.error || 'Error al asignar el paciente.');
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
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            });

        } catch (error) {
            Swal.fire('Error', 'No se pudo cargar el formulario.', 'error');
        }
    }
}
