import { Controller } from '@hotwired/stimulus';
import Swal from 'sweetalert2';
import $ from 'jquery';
import 'select2';

export default class extends Controller {
    static values = { url: String }

    async openModal(event) {
        event.preventDefault();

        Swal.fire({
            title: 'Cargando camas...',
            didOpen: () => Swal.showLoading()
        });

        try {
            const response = await fetch(this.urlValue, {
                credentials: 'same-origin',
                cache: 'no-store', // <--- CRITICAL: Forces a fresh CSRF token every time
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
                    console.log('new controller loaded with select2 lmao');
                },
                preConfirm: async () => {
                    const form = document.getElementById('assign-bed-form');
                    const formData = new FormData(form);

                    // DEBUGGING: Let's prove the token is actually there!
                    // (Assuming your form name is 'asignar_cama'. Adjust if your Type class is named differently)
                    console.log('Token extraído:', formData.get('asignar_cama[_token]'));
                    console.log('Cama seleccionada:', formData.get('asignar_cama[camaActual]'));

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
                }
            });

        } catch (error) {
            Swal.fire('Error', 'No se pudo cargar el formulario.', 'error');
        }
    }
}
