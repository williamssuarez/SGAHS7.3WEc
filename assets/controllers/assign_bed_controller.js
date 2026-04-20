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
            didOpen: () => Swal.showLoading()
        });

        try {
            const response = await fetch(this.urlValue, {
                credentials: 'same-origin',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const htmlContent = await response.text();

            Swal.fire({
                title: 'Asignar Cama',
                html: htmlContent,
                showCancelButton: true,
                confirmButtonText: 'Asignar y Trasladar',
                cancelButtonText: 'Cancelar',
                didOpen: (popup) => {
                    $(popup).find('.srchSelect').select2({
                        theme: "bootstrap-5",
                        width: '100%',
                        language: "es",
                        dropdownParent: $(popup)
                    });
                },
                preConfirm: async () => {
                    const form = document.getElementById('assign-bed-form');
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
                            throw new Error(result.error || 'Error al asignar la cama.');
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
                        title: '¡Trasladado!',
                        text: result.value.message,
                        timer: 2000,
                        showConfirmButton: false
                    });

                    window.location.reload();
                }
            });

        } catch (error) {
            Swal.fire('Error', 'No se pudo cargar el formulario.', 'error');
        }
    }
}
