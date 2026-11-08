import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['bar', 'prevBtn', 'nextBtn'];

    connect() {
        this.isMouseDown = false;
        this.startX = 0;
        this.initialScrollLeft = 0;
        this.hasMoved = false;

        this.onWheel = this.handleWheel.bind(this);
        this.onPointerDown = this.handlePointerDown.bind(this);
        this.onPointerMove = this.handlePointerMove.bind(this);
        this.onPointerUp = this.handlePointerUp.bind(this);
        this.onScroll = this.updateScrollButtons.bind(this);
        this.onResize = this.updateScrollButtons.bind(this);
        this.onTabShown = this.handleTabShown.bind(this);

        this.bar = this.hasBarTarget ? this.barTarget : this.element.querySelector('.apple-segmented-bar');
        if (!this.bar) return;

        // Mouse wheel horizontal scroll (desktop mouse only)
        this.bar.addEventListener('wheel', this.onWheel, { passive: false });

        // Desktop mouse drag-to-scroll (filtered strictly to pointerType === 'mouse')
        this.bar.addEventListener('pointerdown', this.onPointerDown);
        window.addEventListener('pointermove', this.onPointerMove);
        window.addEventListener('pointerup', this.onPointerUp);
        window.addEventListener('pointercancel', this.onPointerUp);

        // Scroll listener for updating arrow button states
        this.bar.addEventListener('scroll', this.onScroll, { passive: true });
        window.addEventListener('resize', this.onResize, { passive: true });

        // Bootstrap 5 tab integration: scroll into view when a tab is shown
        this.bar.addEventListener('shown.bs.tab', this.onTabShown);

        // Initial check after DOM render
        requestAnimationFrame(() => {
            this.updateScrollButtons();
            const activeTab = this.bar.querySelector('.apple-segmented-item.active');
            if (activeTab) {
                this.scrollItemIntoView(activeTab, false);
            }
        });
    }

    disconnect() {
        if (!this.bar) return;

        this.bar.removeEventListener('wheel', this.onWheel);
        this.bar.removeEventListener('pointerdown', this.onPointerDown);
        window.removeEventListener('pointermove', this.onPointerMove);
        window.removeEventListener('pointerup', this.onPointerUp);
        window.removeEventListener('pointercancel', this.onPointerUp);
        this.bar.removeEventListener('scroll', this.onScroll);
        window.removeEventListener('resize', this.onResize);
        this.bar.removeEventListener('shown.bs.tab', this.onTabShown);
    }

    handleWheel(e) {
        if (this.bar.scrollWidth <= this.bar.clientWidth) return;

        // Translate vertical wheel scroll to horizontal scrolling
        if (Math.abs(e.deltaY) > Math.abs(e.deltaX)) {
            e.preventDefault();
            this.bar.scrollLeft += e.deltaY;
            this.updateScrollButtons();
        }
    }

    handlePointerDown(e) {
        // STRICT CHECK: Only activate drag logic for mouse devices.
        // Touch screens (phones/tablets) use native hardware-accelerated touch scrolling.
        if (e.pointerType !== 'mouse') return;
        if (e.button !== 0) return; // Only primary (left) button
        if (this.bar.scrollWidth <= this.bar.clientWidth) return; // Only if overflow exists

        this.isMouseDown = true;
        this.hasMoved = false;
        this.startX = e.pageX;
        this.initialScrollLeft = this.bar.scrollLeft;
    }

    handlePointerMove(e) {
        if (!this.isMouseDown || e.pointerType !== 'mouse') return;

        const delta = e.pageX - this.startX;
        // Require deliberate movement (> 12px) before treating as a drag
        if (Math.abs(delta) > 12) {
            this.hasMoved = true;
            this.bar.classList.add('is-dragging');
        }

        if (this.hasMoved) {
            this.bar.scrollLeft = this.initialScrollLeft - delta;
            this.updateScrollButtons();
        }
    }

    handlePointerUp(e) {
        if (!this.isMouseDown) return;
        this.isMouseDown = false;
        this.bar.classList.remove('is-dragging');

        // If user performed a deliberate drag with the mouse, prevent click triggering tab change
        if (this.hasMoved) {
            const captureClick = (ev) => {
                ev.stopPropagation();
                ev.preventDefault();
                window.removeEventListener('click', captureClick, true);
            };
            window.addEventListener('click', captureClick, true);
        }
    }

    handleTabShown(e) {
        if (e.target) {
            this.scrollItemIntoView(e.target, true);
        }
    }

    scrollItemIntoView(item, smooth = true) {
        if (!item || !this.bar) return;

        const barRect = this.bar.getBoundingClientRect();
        const itemRect = item.getBoundingClientRect();

        const isOffLeft = itemRect.left < barRect.left + 24;
        const isOffRight = itemRect.right > barRect.right - 24;

        if (isOffLeft || isOffRight) {
            const scrollOffset = (item.offsetLeft - (this.bar.clientWidth / 2)) + (item.clientWidth / 2);
            this.bar.scrollTo({
                left: Math.max(0, scrollOffset),
                behavior: smooth ? 'smooth' : 'auto'
            });
        }
    }

    scrollPrev() {
        if (!this.bar) return;
        this.bar.scrollBy({ left: -240, behavior: 'smooth' });
    }

    scrollNext() {
        if (!this.bar) return;
        this.bar.scrollBy({ left: 240, behavior: 'smooth' });
    }

    updateScrollButtons() {
        if (!this.bar) return;

        const maxScroll = this.bar.scrollWidth - this.bar.clientWidth;
        const hasOverflow = maxScroll > 4;

        if (this.hasPrevBtnTarget) {
            const showPrev = hasOverflow && this.bar.scrollLeft > 8;
            this.prevBtnTarget.classList.toggle('is-visible', showPrev);
        }

        if (this.hasNextBtnTarget) {
            const showNext = hasOverflow && this.bar.scrollLeft < maxScroll - 8;
            this.nextBtnTarget.classList.toggle('is-visible', showNext);
        }
    }
}
