import { Controller } from '@hotwired/stimulus';
import * as bootstrap from 'bootstrap';

/**
 * apple_popover_controller.js
 * Orquestador de animación de entrada y salida fluida estilo Apple para popovers/dropdowns.
 * Intercepta hide.bs.dropdown para reproducir la animación de salida (160ms) antes de que Bootstrap
 * remueva la visibilidad del menú.
 */
export default class extends Controller {
    connect() {
        this.allowHide = false;
        this.isClosing = false;
        this.timeoutId = null;

        this.onHide = this.handleHide.bind(this);
        this.onShow = this.handleShow.bind(this);

        this.element.addEventListener('hide.bs.dropdown', this.onHide);
        this.element.addEventListener('show.bs.dropdown', this.onShow);
    }

    disconnect() {
        if (this.timeoutId) {
            clearTimeout(this.timeoutId);
            this.timeoutId = null;
        }
        this.element.removeEventListener('hide.bs.dropdown', this.onHide);
        this.element.removeEventListener('show.bs.dropdown', this.onShow);
    }

    handleShow() {
        if (this.timeoutId) {
            clearTimeout(this.timeoutId);
            this.timeoutId = null;
        }
        this.isClosing = false;
        this.allowHide = false;
        this.element.classList.remove('is-closing');
        const popover = this.element.querySelector('.apple-user-popover');
        if (popover) {
            popover.classList.remove('is-closing');
        }
    }

    handleHide(event) {
        if (this.allowHide) {
            return;
        }

        if (this.isClosing) {
            event.preventDefault();
            return;
        }

        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            return;
        }

        const trigger = this.element.querySelector('[data-bs-toggle="dropdown"]') || event.target;
        const popover = this.element.querySelector('.apple-user-popover');

        if (!trigger || !popover) {
            return;
        }

        // Prevenir el cierre abrupto e instantáneo de Bootstrap
        event.preventDefault();
        this.isClosing = true;

        // Añadir estado de cierre para ejecutar transiciones CSS
        this.element.classList.add('is-closing');
        popover.classList.add('is-closing');

        // Permitir 160ms para que la curva de repliegue elástica de Apple se ejecute
        this.timeoutId = setTimeout(() => {
            this.allowHide = true;
            this.isClosing = false;

            const Dropdown = bootstrap.Dropdown || window.bootstrap?.Dropdown;
            const dropdown = Dropdown ? (Dropdown.getInstance(trigger) || Dropdown.getOrCreateInstance(trigger)) : null;

            if (dropdown) {
                dropdown.hide();
            }

            this.element.classList.remove('is-closing');
            popover.classList.remove('is-closing');
            this.allowHide = false;
            this.timeoutId = null;
        }, 160);
    }
}
