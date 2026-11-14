import { Controller } from '@hotwired/stimulus';
import Swal from 'sweetalert2';

const ALLOWED_ROLE_CLASSES = new Set([
    'text-bg-dark',
    'text-bg-primary',
    'text-bg-info',
    'text-bg-success',
    'text-bg-warning',
    'text-bg-danger',
    'text-bg-secondary',
]);

export default class extends Controller {
    static values = {
        name: String,
        initials: String,
        avatarUrl: String,
        roleLabel: String,
        roles: { type: Array, default: [] },
        doc: String,
        phone: String,
        email: String,
        verified: Boolean,
        specialties: { type: Array, default: [] },
        showUrl: String,
    };

    open(event) {
        if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || event.button === 1) {
            return;
        }

        event.preventDefault();

        const safeUrl = this.isSafeRelativeUrl(this.showUrlValue) ? this.showUrlValue : '';

        Swal.fire({
            html: this.buildModalContent(),
            width: '430px',
            showCloseButton: true,
            showCancelButton: true,
            cancelButtonText: 'Cerrar',
            showConfirmButton: Boolean(safeUrl),
            confirmButtonText: 'Más detalles',
            reverseButtons: true,
            focusCancel: false,
            returnFocus: true,
            didOpen: (popup) => {
                const heading = popup.querySelector('#apple-doctor-modal-title');
                if (heading) {
                    popup.setAttribute('aria-labelledby', heading.id);
                }
            },
        }).then((result) => {
            if (result.isConfirmed && safeUrl) {
                window.location.assign(safeUrl);
            }
        });
    }

    fallbackAvatar(event) {
        const img = event.currentTarget;
        const fallback = img.nextElementSibling;
        img.hidden = true;
        if (fallback) {
            fallback.hidden = false;
        }
    }

    buildModalContent() {
        const root = this.el('div', 'apple-doctor-modal');
        const initials = (this.initialsValue || 'DR').trim() || 'DR';

        // Header: avatar + name + role pills
        const header = this.el('div', 'apple-doctor-modal__header');
        const avatar = this.el('div', 'apple-doctor-avatar apple-doctor-avatar--lg');
        avatar.setAttribute('aria-hidden', 'true');

        if (this.hasAvatarUrlValue && this.isSafeImageUrl(this.avatarUrlValue)) {
            const img = this.el('img', 'apple-doctor-avatar__img');
            img.src = this.avatarUrlValue;
            img.alt = '';
            img.addEventListener('error', () => {
                img.replaceWith(this.el('span', 'apple-doctor-avatar__initials', initials));
            }, { once: true });
            avatar.appendChild(img);
        } else {
            avatar.appendChild(this.el('span', 'apple-doctor-avatar__initials', initials));
        }

        const heading = this.el('h4', 'apple-doctor-modal__name', this.nameValue || 'Médico');
        heading.id = 'apple-doctor-modal-title';

        const rolesRow = this.el('div', 'apple-doctor-modal__roles');
        const badges = Array.isArray(this.rolesValue) && this.rolesValue.length > 0
            ? this.rolesValue
            : (this.hasRoleLabelValue && this.roleLabelValue.trim()
                ? [{ label: this.roleLabelValue.trim(), class: 'text-bg-primary' }]
                : []);

        for (const badge of badges) {
            if (!badge || !badge.label) {
                continue;
            }
            const pill = this.el('span', 'apple-role-pill', String(badge.label));
            const colorClass = String(badge.class || '')
                .split(/\s+/)
                .find((token) => ALLOWED_ROLE_CLASSES.has(token));
            pill.classList.add(colorClass || 'text-bg-secondary');
            rolesRow.appendChild(pill);
        }

        header.append(avatar, heading);
        if (rolesRow.childElementCount > 0) {
            header.appendChild(rolesRow);
        }
        root.appendChild(header);

        // Grouped contact card
        const detailsList = this.el('dl', 'apple-doctor-modal__list');
        const emailText = this.hasEmailValue && this.emailValue.trim() ? this.emailValue.trim() : '';
        let verifiedBadge = null;
        if (emailText) {
            verifiedBadge = this.el(
                'span',
                `apple-doctor-modal__verified ${this.verifiedValue ? 'is-verified' : 'is-pending'}`,
                this.verifiedValue ? 'Verificado' : 'Pendiente'
            );
        }

        detailsList.append(
            this.buildDetailRow('bi-person-vcard', 'Documento', this.docValue || 'No registrado'),
            this.buildDetailRow('bi-telephone', 'Teléfono', this.phoneValue || 'No registrado'),
            this.buildDetailRow('bi-envelope', 'Correo', emailText || 'No registrado', verifiedBadge)
        );
        root.appendChild(detailsList);

        // Active specialties
        if (Array.isArray(this.specialtiesValue) && this.specialtiesValue.length > 0) {
            const section = this.el('div', 'apple-doctor-modal__section');
            section.appendChild(this.el('div', 'apple-doctor-modal__label', 'Especialidades activas'));

            const chips = this.el('div', 'apple-doctor-modal__chips');
            for (const specName of this.specialtiesValue) {
                if (!specName) {
                    continue;
                }
                const chip = this.el('span', 'apple-doctor-modal__chip');
                const icon = this.el('i', 'bi bi-heart-pulse');
                icon.setAttribute('aria-hidden', 'true');
                chip.append(icon, document.createTextNode(String(specName)));
                chips.appendChild(chip);
            }
            if (chips.childElementCount > 0) {
                section.appendChild(chips);
                root.appendChild(section);
            }
        }

        return root;
    }

    buildDetailRow(iconClass, label, value, extraBadge = null) {
        const row = this.el('div', 'apple-doctor-modal__row');
        const iconWrap = this.el('span', 'apple-doctor-modal__row-icon');
        iconWrap.setAttribute('aria-hidden', 'true');
        iconWrap.appendChild(this.el('i', `bi ${iconClass}`));

        const body = this.el('div', 'apple-doctor-modal__row-body');
        const dt = this.el('dt', 'apple-doctor-modal__dt', label);
        const dd = this.el('dd', 'apple-doctor-modal__dd');
        dd.appendChild(document.createTextNode(value));
        if (extraBadge) {
            dd.appendChild(extraBadge);
        }

        body.append(dt, dd);
        row.append(iconWrap, body);
        return row;
    }

    el(tag, className = '', text = null) {
        const node = document.createElement(tag);
        if (className) {
            node.className = className;
        }
        if (text !== null) {
            node.textContent = text;
        }
        return node;
    }

    isSafeRelativeUrl(url) {
        return typeof url === 'string' && url.startsWith('/') && !url.startsWith('//');
    }

    isSafeImageUrl(url) {
        if (typeof url !== 'string') {
            return false;
        }
        const trimmed = url.trim();
        return trimmed.startsWith('/') && !trimmed.startsWith('//')
            || trimmed.startsWith('https://')
            || trimmed.startsWith('http://');
    }
}
