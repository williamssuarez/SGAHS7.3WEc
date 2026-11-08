import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['icon', 'label'];

    connect() {
        this.updateUI(this.getCurrentTheme());

        // Escuchar cambios en el esquema del sistema operativo si no hay preferencia manual fija
        this.systemThemeListener = (e) => {
            if (!localStorage.getItem('theme')) {
                const newTheme = e.matches ? 'dark' : 'light';
                this.applyTheme(newTheme, false);
            }
        };

        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', this.systemThemeListener);
    }

    disconnect() {
        if (this.systemThemeListener) {
            window.matchMedia('(prefers-color-scheme: dark)').removeEventListener('change', this.systemThemeListener);
        }
    }

    getCurrentTheme() {
        return document.documentElement.getAttribute('data-bs-theme') ||
               localStorage.getItem('theme') ||
               (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
    }

    toggle(event) {
        if (event) {
            event.preventDefault();
        }

        const currentTheme = this.getCurrentTheme();
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';

        this.applyTheme(newTheme, true);
    }

    applyTheme(theme, save = true) {
        document.documentElement.setAttribute('data-bs-theme', theme);

        if (save) {
            localStorage.setItem('theme', theme);
        }

        // Notificar globalmente a todos los controladores de tema en la página
        window.dispatchEvent(new CustomEvent('theme:changed', { detail: { theme } }));
        this.updateUI(theme);
    }

    updateUI(theme) {
        const isDark = theme === 'dark';

        // Actualizar todos los iconos en la página vinculados
        document.querySelectorAll('[data-theme-icon]').forEach(el => {
            el.className = isDark ? 'bi bi-moon-stars-fill text-warning' : 'bi bi-sun-fill text-warning';
        });

        // Actualizar etiquetas si existen
        document.querySelectorAll('[data-theme-label]').forEach(el => {
            el.textContent = isDark ? 'Modo Oscuro' : 'Modo Claro';
        });

        // Actualizar atributos de accesibilidad
        document.querySelectorAll('[data-theme-toggle-btn]').forEach(el => {
            el.setAttribute('aria-label', isDark ? 'Cambiar a modo claro' : 'Cambiar a modo oscuro');
            el.setAttribute('title', isDark ? 'Cambiar a modo claro' : 'Cambiar a modo oscuro');
        });
    }
}
