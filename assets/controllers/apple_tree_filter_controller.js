import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['input', 'item', 'noResults', 'count'];

    connect() {
        this.updateCount();
    }

    filter() {
        const query = this.hasInputTarget ? this.inputTarget.value.trim().toLowerCase() : '';
        let visibleCount = 0;

        this.itemTargets.forEach(item => {
            const name = item.dataset.name ? item.dataset.name.toLowerCase() : '';
            if (!query || name.includes(query)) {
                item.classList.remove('d-none');
                visibleCount++;
            } else {
                item.classList.add('d-none');
            }
        });

        if (this.hasNoResultsTarget) {
            this.noResultsTarget.classList.toggle('d-none', visibleCount > 0);
        }

        this.updateCount(visibleCount, query);
    }

    clear() {
        if (this.hasInputTarget) {
            this.inputTarget.value = '';
            this.filter();
            this.inputTarget.focus();
        }
    }

    updateCount(visibleCount, query) {
        if (!this.hasCountTarget) return;

        const total = this.itemTargets.length;
        if (query && query.length > 0) {
            this.countTarget.textContent = `${visibleCount} de ${total}`;
        } else {
            this.countTarget.textContent = `${total} estados`;
        }
    }
}
