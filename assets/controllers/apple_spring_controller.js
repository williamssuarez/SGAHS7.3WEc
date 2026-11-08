import { Controller } from '@hotwired/stimulus';
import { animate } from 'motion';

export default class extends Controller {
    static values = {
        scale: { type: Number, default: 0.97 },
        duration: { type: Number, default: 0.35 },
        bounce: { type: Number, default: 0.15 }
    }

    connect() {
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            return;
        }

        this.onPointerDown = this.pressStart.bind(this);
        this.onPointerUp = this.pressEnd.bind(this);
        this.onPointerCancel = this.pressEnd.bind(this);

        this.element.addEventListener('pointerdown', this.onPointerDown);
        this.element.addEventListener('pointerup', this.onPointerUp);
        this.element.addEventListener('pointerleave', this.onPointerCancel);
        this.element.addEventListener('pointercancel', this.onPointerCancel);
    }

    disconnect() {
        if (this.onPointerDown) {
            this.element.removeEventListener('pointerdown', this.onPointerDown);
            this.element.removeEventListener('pointerup', this.onPointerUp);
            this.element.removeEventListener('pointerleave', this.onPointerCancel);
            this.element.removeEventListener('pointercancel', this.onPointerCancel);
        }
    }

    pressStart() {
        animate(
            this.element,
            { scale: this.scaleValue },
            { type: 'spring', bounce: 0, duration: 0.2 }
        );
    }

    pressEnd() {
        animate(
            this.element,
            { scale: 1 },
            { type: 'spring', bounce: this.bounceValue, duration: this.durationValue }
        );
    }
}
