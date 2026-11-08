import { Controller } from '@hotwired/stimulus';
import autoAnimate from '@formkit/auto-animate';

export default class extends Controller {
    static values = {
        duration: { type: Number, default: 250 },
        easing: { type: String, default: 'cubic-bezier(0.25, 1, 0.5, 1)' }
    }

    connect() {
        // Principio 14 de Apple Design: Respetar prefers-reduced-motion
        const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        if (prefersReducedMotion) {
            return;
        }

        autoAnimate(this.element, {
            duration: this.durationValue,
            easing: this.easingValue
        });
    }
}
