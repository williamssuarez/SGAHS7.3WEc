// assets/controllers/auto_refresh_controller.js
import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = { interval: Number }

    connect() {
        if (this.intervalValue > 0) {
            this.timer = setInterval(() => {
                window.location.reload();
            }, this.intervalValue);
        }
    }

    disconnect() {
        if (this.timer) clearInterval(this.timer);
    }
}
