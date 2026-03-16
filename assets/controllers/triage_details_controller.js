import { Controller } from '@hotwired/stimulus';
import Swal from 'sweetalert2';

export default class extends Controller {
    static values = { url: String }

    async fetchAndShow(event) {
        // Prevent the button from doing any default action
        event.preventDefault();

        // 1. Instantly show a loading state
        Swal.fire({
            title: 'Cargando detalles...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        try {
            // 2. Fetch the HTML snippet from Symfony
            const response = await fetch(this.urlValue);
            if (!response.ok) throw new Error('Error en la red');

            const htmlContent = await response.text();

            // 3. Update the SweetAlert with the fetched data
            Swal.fire({
                title: 'Detalles de Triage',
                html: htmlContent,
                icon: 'info',
                width: '600px', // Make the modal slightly wider to fit the grid
                confirmButtonText: 'Cerrar',
                confirmButtonColor: '#6c757d' // Secondary gray color
            });

        } catch (error) {
            console.error("Error fetching triage details:", error);
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'No se pudieron cargar los detalles del triage.'
            });
        }
    }
}
