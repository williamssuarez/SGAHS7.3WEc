import { Controller } from '@hotwired/stimulus';
import Swal from 'sweetalert2';
import $ from "jquery";

export default class extends Controller {
    static values = { url: String, redirect: String }

    async openModal(event) {
        event.preventDefault();

        Swal.fire({ title: 'Cargando...', didOpen: () => Swal.showLoading() });

        try {
            const response = await fetch(this.urlValue, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            const htmlContent = await response.text();

            Swal.fire({
                title: 'Resumen de Alta Médica',
                html: htmlContent,
                width: '600px', // Wider modal for easier typing
                showCancelButton: true,
                confirmButtonText: 'Confirmar Alta y Liberar Cama',
                confirmButtonColor: '#198754', // Bootstrap Success Green
                cancelButtonText: 'Cancelar',
                didOpen: (popup) => {
                    $(popup).find('.noSrchSelect').select2({
                        theme: "bootstrap-5",
                        width: '100%',
                        language: "es",
                        minimumResultsForSearch: Infinity,
                        dropdownParent: $(popup)
                    });
                },
                preConfirm: async () => {
                    const form = document.getElementById('discharge-form');
                    const formData = new FormData(form);

                    try {
                        const postResponse = await fetch(form.action, {
                            method: 'POST',
                            body: formData,
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        });
                        const result = await postResponse.json();
                        if (!result.success) throw new Error(result.error || 'Error al procesar el alta: '+result.message);
                        return result;
                    } catch (error) {
                        Swal.showValidationMessage(error.message);
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Alta procesada!',
                        text: result.value.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        // KICK THE DOCTOR BACK TO THE MAIN DASHBOARD
                        window.location.href = this.redirectValue;
                    });
                }
            });
        } catch (error) {
            Swal.fire('Error', 'No se pudo cargar el formulario.', 'error');
        }
    }
}
