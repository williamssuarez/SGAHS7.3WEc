import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['display', 'form', 'label', 'input', 'saveBtn'];
    static values = {
        url: String
    };

    start(e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }

        if (this.hasDisplayTarget && this.hasFormTarget) {
            this.displayTarget.classList.add('d-none');
            this.formTarget.classList.remove('d-none');

            if (this.hasInputTarget && this.hasLabelTarget) {
                this.inputTarget.value = this.labelTarget.textContent.trim();
                setTimeout(() => {
                    this.inputTarget.focus();
                    this.inputTarget.select();
                }, 50);
            }
        }
    }

    cancel(e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }

        if (this.hasDisplayTarget && this.hasFormTarget) {
            this.formTarget.classList.add('d-none');
            this.displayTarget.classList.remove('d-none');
        }
    }

    handleKeydown(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            e.stopPropagation();
            this.save(e);
        } else if (e.key === 'Escape') {
            e.preventDefault();
            e.stopPropagation();
            this.cancel(e);
        }
    }

    async save(e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }

        if (!this.hasInputTarget || !this.hasLabelTarget) return;

        const newName = this.inputTarget.value.trim();
        const currentName = this.labelTarget.textContent.trim();

        if (!newName) {
            this.inputTarget.focus();
            return;
        }

        if (newName === currentName) {
            this.cancel();
            return;
        }

        const originalBtnHtml = this.hasSaveBtnTarget ? this.saveBtnTarget.innerHTML : '';
        if (this.hasSaveBtnTarget) {
            this.saveBtnTarget.disabled = true;
            this.saveBtnTarget.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span>';
        }

        const formData = new FormData();
        formData.append('nombre', newName);

        try {
            const response = await fetch(this.urlValue, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json();

            if (data.success) {
                this.labelTarget.textContent = data.nombre;
                this.labelTarget.title = data.nombre;

                // Update filter target if present
                const filterItem = this.element.closest('[data-apple-tree-filter-target="item"]');
                if (filterItem) {
                    filterItem.dataset.name = data.nombre;
                }

                this.cancel();

                // Subtle feedback animation
                this.labelTarget.classList.add('text-success');
                setTimeout(() => {
                    this.labelTarget.classList.remove('text-success');
                }, 1200);
            } else {
                alert(data.message || 'Error al actualizar nombre.');
            }
        } catch (error) {
            alert('Error de conexión al renombrar.');
        } finally {
            if (this.hasSaveBtnTarget) {
                this.saveBtnTarget.disabled = false;
                this.saveBtnTarget.innerHTML = originalBtnHtml;
            }
        }
    }
}
