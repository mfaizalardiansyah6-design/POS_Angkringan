const onReady = (callback) => {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', callback, { once: true });
    } else {
        callback();
    }
};

const setupSidebar = () => {
    const sidebar = document.querySelector('[data-sidebar]');
    const backdrop = document.querySelector('[data-sidebar-backdrop]');
    const openButton = document.querySelector('[data-sidebar-toggle]');
    const closeButtons = document.querySelectorAll('[data-sidebar-close]');

    if (!sidebar || !openButton) {
        return;
    }

    const setOpen = (open) => {
        sidebar.classList.toggle('is-open', open);
        backdrop?.classList.toggle('is-open', open);
        openButton.setAttribute('aria-expanded', String(open));
        document.body.style.overflow = open ? 'hidden' : '';
    };

    openButton.addEventListener('click', () => setOpen(!sidebar.classList.contains('is-open')));
    closeButtons.forEach((button) => button.addEventListener('click', () => setOpen(false)));
    backdrop?.addEventListener('click', () => setOpen(false));

    sidebar.querySelectorAll('a').forEach((link) => link.addEventListener('click', () => setOpen(false)));

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && sidebar.classList.contains('is-open')) {
            setOpen(false);
        }
    });

    const desktop = window.matchMedia('(min-width: 1024px)');
    desktop.addEventListener('change', (event) => {
        if (event.matches) {
            setOpen(false);
        }
    });
};

const setupDialogs = () => {
    document.querySelectorAll('[data-dialog-open]').forEach((trigger) => {
        trigger.addEventListener('click', () => {
            const dialog = document.getElementById(trigger.dataset.dialogOpen);
            if (dialog?.showModal) {
                dialog.showModal();
            }
        });
    });

    document.querySelectorAll('dialog').forEach((dialog) => {
        dialog.querySelectorAll('[data-dialog-close]').forEach((button) => {
            button.addEventListener('click', () => dialog.close());
        });

        dialog.addEventListener('click', (event) => {
            if (event.target === dialog) {
                dialog.close();
            }
        });
    });

    document.querySelectorAll('[data-print]').forEach((button) => {
        button.addEventListener('click', () => window.print());
    });
};

const setupConfirmForms = () => {
    const dialog = document.getElementById('app-confirm');
    const title = document.querySelector('[data-confirm-title]');
    const text = document.querySelector('[data-confirm-text]');
    const accept = document.querySelector('[data-confirm-accept]');
    const cancel = document.querySelector('[data-confirm-cancel]');
    let pendingForm = null;

    if (!dialog || !title || !text || !accept) {
        return;
    }

    const close = () => {
        pendingForm = null;
        dialog.close();
    };

    document.querySelectorAll('form[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (form.dataset.confirmed === '1') {
                delete form.dataset.confirmed;
                return;
            }

            event.preventDefault();
            pendingForm = form;
            title.textContent = form.dataset.confirmTitle || 'Konfirmasi tindakan';
            text.textContent = form.dataset.confirm || 'Lanjutkan tindakan ini?';
            accept.classList.toggle('btn--danger', form.dataset.confirmDanger === '1');
            accept.classList.toggle('btn--primary', form.dataset.confirmDanger !== '1');
            dialog.showModal();
        });
    });

    accept.addEventListener('click', () => {
        if (!pendingForm) {
            close();
            return;
        }

        const form = pendingForm;
        pendingForm = null;
        dialog.close();
        form.dataset.confirmed = '1';
        form.requestSubmit();
    });

    cancel?.addEventListener('click', close);
    dialog.querySelectorAll('[data-dialog-close]').forEach((button) => button.addEventListener('click', close));
};

const setupToasts = () => {
    document.querySelectorAll('[data-toast]').forEach((toast) => {
        const remove = () => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(-4px)';
            toast.style.transition = 'opacity 180ms ease, transform 180ms ease';
            window.setTimeout(() => toast.remove(), 190);
        };

        toast.querySelector('[data-toast-close]')?.addEventListener('click', remove);
        window.setTimeout(remove, 6000);
    });
};

const setupFilePreviews = () => {
    document.querySelectorAll('[data-preview-input]').forEach((input) => {
        input.addEventListener('change', () => {
            const target = document.querySelector(input.dataset.previewInput);
            const file = input.files?.[0];

            if (!target || !file || !file.type.startsWith('image/')) {
                return;
            }

            const url = URL.createObjectURL(file);
            target.innerHTML = '';

            const image = document.createElement('img');
            image.src = url;
            image.alt = 'Pratinjau gambar';
            image.onload = () => URL.revokeObjectURL(url);
            target.appendChild(image);
        });
    });
};

onReady(() => {
    setupSidebar();
    setupDialogs();
    setupConfirmForms();
    setupToasts();
    setupFilePreviews();
});
