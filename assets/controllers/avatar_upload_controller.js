import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['input', 'preview', 'placeholder', 'removeBtn', 'errorMsg'];
    static values = {
        maxSizeMb: { type: Number, default: 2 }
    };

    connect() {
        this.validateCurrentState();
    }

    clickAvatar(e) {
        // Evitar que el clic en el botón de remover vuelva a abrir el selector
        if (e.target.closest('[data-avatar-upload-target="removeBtn"]')) {
            return;
        }
        if (this.hasInputTarget) {
            this.inputTarget.click();
        }
    }

    fileChanged(e) {
        const file = this.inputTarget.files && this.inputTarget.files[0];
        if (!file) {
            return;
        }

        // 1. Validar Tipo MIME / Extensión
        const validTypes = ['image/jpeg', 'image/png', 'image/jpg'];
        if (!validTypes.includes(file.type)) {
            this.showError('Por favor selecciona un archivo JPG o PNG válido.');
            this.clearInput();
            return;
        }

        // 2. Validar Tamaño Máximo
        const maxBytes = this.maxSizeMbValue * 1024 * 1024;
        if (file.size > maxBytes) {
            this.showError(`El archivo supera el tamaño máximo permitido (${this.maxSizeMbValue} MB).`);
            this.clearInput();
            return;
        }

        this.clearError();

        // 3. Vista Previa Instantánea
        const reader = new FileReader();
        reader.onload = (event) => {
            if (this.hasPreviewTarget) {
                this.previewTarget.src = event.target.result;
                this.previewTarget.classList.remove('d-none');
            }
            if (this.hasPlaceholderTarget) {
                this.placeholderTarget.classList.add('d-none');
            }
            if (this.hasRemoveBtnTarget) {
                this.removeBtnTarget.classList.remove('d-none');
            }
        };
        reader.readAsDataURL(file);
    }

    remove(e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }
        this.clearInput();
        this.clearError();
    }

    clearInput() {
        if (this.hasInputTarget) {
            this.inputTarget.value = '';
        }
        if (this.hasPreviewTarget) {
            this.previewTarget.src = '';
            this.previewTarget.classList.add('d-none');
        }
        if (this.hasPlaceholderTarget) {
            this.placeholderTarget.classList.remove('d-none');
        }
        if (this.hasRemoveBtnTarget) {
            this.removeBtnTarget.classList.add('d-none');
        }
    }

    showError(msg) {
        if (this.hasErrorMsgTarget) {
            this.errorMsgTarget.textContent = msg;
            this.errorMsgTarget.classList.remove('d-none');
        } else {
            alert(msg);
        }
    }

    clearError() {
        if (this.hasErrorMsgTarget) {
            this.errorMsgTarget.textContent = '';
            this.errorMsgTarget.classList.add('d-none');
        }
    }

    validateCurrentState() {
        if (this.hasInputTarget && this.inputTarget.files && this.inputTarget.files[0]) {
            this.fileChanged();
        }
    }
}
