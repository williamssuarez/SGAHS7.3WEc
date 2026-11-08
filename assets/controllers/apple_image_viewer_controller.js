import { Controller } from '@hotwired/stimulus';
import Viewer from 'viewerjs';

export default class extends Controller {
    static values = {
        url: String,
        title: String,
        downloadUrl: String
    };

    static targets = ['thumb'];

    open(event) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }

        const imageUrl = this.urlValue || (this.hasThumbTarget ? this.thumbTarget.src : '');
        if (!imageUrl) return;

        const imageTitle = this.titleValue || (this.hasThumbTarget ? this.thumbTarget.alt : 'Documento Médico');

        // Create an off-screen image element for Viewer.js instance
        const img = document.createElement('img');
        img.src = imageUrl;
        img.alt = imageTitle;

        const downloadTargetUrl = this.downloadUrlValue || 
            (imageUrl + (imageUrl.includes('?') ? '&download=1' : '?download=1'));

        const viewer = new Viewer(img, {
            inline: false,
            button: true,
            navbar: false,
            zIndex: 99999,
            transition: true,
            title: [
                1,
                (image, imageData) => {
                    const dimensions = imageData && imageData.naturalWidth ? ` (${imageData.naturalWidth} × ${imageData.naturalHeight}px)` : '';
                    return `${imageTitle}${dimensions}`;
                }
            ],
            toolbar: {
                zoomIn: true,
                zoomOut: true,
                oneToOne: true,
                reset: true,
                rotateLeft: true,
                rotateRight: true,
                flipHorizontal: true,
                flipVertical: true,
                download: {
                    show: true,
                    title: 'Descargar imagen',
                    content: '<i class="bi bi-download"></i>',
                    click: () => {
                        const a = document.createElement('a');
                        a.href = downloadTargetUrl;
                        a.download = imageTitle;
                        document.body.appendChild(a);
                        a.click();
                        document.body.removeChild(a);
                    }
                }
            },
            hidden() {
                viewer.destroy();
            }
        });

        viewer.show();
    }
}
