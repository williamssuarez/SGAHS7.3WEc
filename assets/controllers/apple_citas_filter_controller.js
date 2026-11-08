import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['button', 'card', 'emptyNotice', 'count'];
    static values = {
        currentFilter: { type: String, default: 'all' }
    };

    connect() {
        this.filter(this.currentFilterValue);
    }

    select(event) {
        event.preventDefault();
        const button = event.currentTarget;
        const filterValue = button.dataset.filter || 'all';

        this.currentFilterValue = filterValue;
        this.filter(filterValue);
        this.scrollButtonIntoView(button);
    }

    scrollButtonIntoView(button) {
        if (!button) return;
        const bar = button.closest('.apple-segmented-bar');
        if (!bar || bar.scrollWidth <= bar.clientWidth) return;

        const scrollOffset = (button.offsetLeft - (bar.clientWidth / 2)) + (button.clientWidth / 2);
        bar.scrollTo({
            left: Math.max(0, scrollOffset),
            behavior: 'smooth'
        });
    }

    filter(filterValue) {
        // Update active class on buttons
        this.buttonTargets.forEach(btn => {
            const isActive = btn.dataset.filter === filterValue;
            btn.classList.toggle('active', isActive);
            btn.setAttribute('aria-selected', isActive ? 'true' : 'false');
        });

        let visibleCount = 0;

        this.cardTargets.forEach(card => {
            const status = card.dataset.status;
            let show = false;

            if (filterValue === 'all') {
                show = true;
            } else if (filterValue === 'pending') {
                show = status === 'pending' || status === 'processing';
            } else if (filterValue === 'scheduled') {
                show = status === 'scheduled';
            } else if (filterValue === 'canceled') {
                show = status === 'canceled' || status === 'rejected';
            }

            if (show) {
                card.classList.remove('d-none');
                visibleCount++;
            } else {
                card.classList.add('d-none');
            }
        });

        // Toggle empty notice for current filter
        if (this.hasEmptyNoticeTarget) {
            this.emptyNoticeTarget.classList.toggle('d-none', visibleCount > 0);
        }
    }
}
