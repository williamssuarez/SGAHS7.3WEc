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
            title: '¿Anular Fallecimiento?',
            text: `¿Está seguro de revocar el estado de fallecimiento de ${this.patientNameValue}? Esto reactivará el expediente del paciente.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, Anular',
            cancelButtonText: 'Cancelar',
            reverseButtons: true
        });

        // If they click 'Cancelar' or click outside the modal, stop here.
        if (!confirmResult.isConfirmed) return;

        // 3. Step Two: The Reason Textarea
        const { value: reason } = await Swal.fire({
            title: 'Motivo de Revocación',
            text: 'Por favor, justifique el error médico o de sistema:',
            input: 'textarea',
            inputPlaceholder: 'Ej: El médico se equivocó al dar de alta al paciente...',
            showCancelButton: true,
            confirmButtonText: 'Confirmar Revocación',
            cancelButtonText: 'Cancelar',
            inputValidator: (value) => {
                if (!value || value.trim() === '') {
                    return 'Debe ingresar un motivo para poder revocar el estado.';
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
            hiddenInput.name = 'motivo_revocacion';
            hiddenInput.value = reason;

            this.element.appendChild(hiddenInput);
            this.element.submit();
        }
    }
}
