import { Controller } from '@hotwired/stimulus';
import Swal from 'sweetalert2';

export default class extends Controller {
    static values = {
        patientName: String
    }

    async confirm(event) {
        // 1. Stop the form from submitting
        event.preventDefault();

        // 2. Step One: The "Are you sure?" confirmation
        const confirmResult = await Swal.fire({
            title: '¿Cancelar la cita?',
            text: `¿Está seguro de cancelar la cita de ${this.patientNameValue}? El paciente deberá solicitar una nueva.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, Cancelar',
            cancelButtonText: 'Volver',
            reverseButtons: true
        });

        // If they click 'Volver' or click outside the modal, stop here.
        if (!confirmResult.isConfirmed) return;

        // 3. Step Two: The Reason Textarea
        const { value: reason } = await Swal.fire({
            title: 'Motivo de cancelación',
            text: 'Por favor, indique el motivo:',
            input: 'textarea',
            inputPlaceholder: 'Ej: El paciente llamó para informar que no puede asistir...',
            showCancelButton: true,
            confirmButtonText: 'Confirmar Cancelación',
            cancelButtonText: 'Cancelar',
            inputValidator: (value) => {
                if (!value || value.trim() === '') {
                    return 'Debe ingresar un motivo para poder cancelar la cita.';
                }
            }
        });

        // 4. If they provided a reason, append it to the form and ~submit
        if (reason) {
            Swal.fire({
                title: 'Cargando...',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen() {
                    Swal.showLoading();
                }
            });
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'motivo_cancelacion';
            hiddenInput.value = reason;

            this.element.appendChild(hiddenInput);
            this.element.submit();
        }
    }
}
