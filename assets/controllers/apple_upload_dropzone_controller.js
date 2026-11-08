import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = [
        'input',
        'dropzone',
        'prompt',
        'preview',
        'fileName',
        'fileSize',
        'fileIcon',
        'nameInput'
    ];

    connect() {
        this.checkInitialState();
    }

    checkInitialState() {
        if (this.hasInputTarget && this.inputTarget.files && this.inputTarget.files.length > 0) {
            this.handleFileSelected(this.inputTarget.files[0]);
        } else {
            this.showPrompt();
        }
    }

    onDragOver(event) {
        event.preventDefault();
        event.stopPropagation();
        if (this.hasDropzoneTarget) {
            this.dropzoneTarget.classList.add('is-dragover');
        }
    }

    onDragLeave(event) {
        event.preventDefault();
        event.stopPropagation();
        if (this.hasDropzoneTarget) {
            this.dropzoneTarget.classList.remove('is-dragover');
        }
    }

    onDrop(event) {
        event.preventDefault();
        event.stopPropagation();
        if (this.hasDropzoneTarget) {
            this.dropzoneTarget.classList.remove('is-dragover');
        }

        const dt = event.dataTransfer;
        if (dt && dt.files && dt.files.length > 0) {
            const file = dt.files[0];
            // Assign to input files using DataTransfer if possible
            try {
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                this.inputTarget.files = dataTransfer.files;
            } catch (e) {
                // Fallback for browsers with strict file input setter restrictions
            }
            this.handleFileSelected(file);
        }
    }

    onInputChange() {
        if (this.hasInputTarget && this.inputTarget.files && this.inputTarget.files.length > 0) {
            this.handleFileSelected(this.inputTarget.files[0]);
        } else {
            this.showPrompt();
        }
    }

    handleFileSelected(file) {
        if (!file) return;

        // Update file name
        if (this.hasFileNameTarget) {
            this.fileNameTarget.textContent = file.name;
        }

        // Update file size
        if (this.hasFileSizeTarget) {
            this.fileSizeTarget.textContent = this.formatBytes(file.size);
        }

        // Update icon based on file type
        if (this.hasFileIconTarget) {
            const isPdf = file.type.includes('pdf') || file.name.toLowerCase().endsWith('.pdf');
            const isImage = file.type.startsWith('image/') || /\.(jpe?g|png)$/i.test(file.name);

            this.fileIconTarget.className = '';
            if (isPdf) {
                this.fileIconTarget.className = 'bi bi-file-earmark-pdf-fill text-danger';
            } else if (isImage) {
                this.fileIconTarget.className = 'bi bi-file-earmark-image-fill text-primary';
            } else {
                this.fileIconTarget.className = 'bi bi-file-earmark-text-fill text-secondary';
            }
        }

        // Auto-fill optional name input if currently empty
        if (this.hasNameInputTarget && !this.nameInputTarget.value.trim()) {
            const cleanName = file.name.replace(/\.[^/.]+$/, '');
            this.nameInputTarget.value = cleanName;
        }

        // Toggle views
        if (this.hasPromptTarget) {
            this.promptTarget.classList.add('d-none');
        }
        if (this.hasPreviewTarget) {
            this.previewTarget.classList.remove('d-none');
        }
    }

    showPrompt() {
        if (this.hasPromptTarget) {
            this.promptTarget.classList.remove('d-none');
        }
        if (this.hasPreviewTarget) {
            this.previewTarget.classList.add('d-none');
        }
    }

    clearFile(event) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }

        if (this.hasInputTarget) {
            this.inputTarget.value = '';
        }

        this.showPrompt();
    }

    triggerBrowse(event) {
        if (event) {
            // Prevent recursive click if clicked on the file input itself or on the clear button
            if (event.target === this.inputTarget || event.target.closest('[data-action*="clearFile"]')) {
                return;
            }
            event.preventDefault();
        }

        if (this.hasInputTarget) {
            this.inputTarget.click();
        }
    }

    formatBytes(bytes, decimals = 1) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const dm = decimals < 0 ? 0 : decimals;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
    }
}
