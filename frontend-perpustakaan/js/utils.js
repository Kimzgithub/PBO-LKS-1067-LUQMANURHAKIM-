// Fungsi bantu: escapeHtml, toast, modal, status memuat/error.

/** Mengamankan teks sebelum disisipkan ke innerHTML (mencegah XSS). */
function escapeHtml(value) {
    const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' };
    return String(value ?? '').replace(/[&<>"']/g, (ch) => map[ch]);
}

/** Notifikasi singkat di kanan atas. type: 'success' | 'error' | 'info'. */
function showToast(message, type = 'success') {
    const container = document.getElementById('toast-container');
    if (!container) return;

    const styles = {
        success: 'bg-emerald-600',
        error: 'bg-red-600',
        info: 'bg-slate-700',
    };

    const toast = document.createElement('div');
    toast.className = `toast rounded-md px-4 py-3 text-sm text-white shadow-lg ${styles[type] || styles.info}`;
    toast.setAttribute('role', type === 'error' ? 'alert' : 'status');
    toast.textContent = message;
    container.appendChild(toast);

    const remove = () => {
        toast.classList.add('is-leaving');
        setTimeout(() => toast.remove(), 180);
    };
    setTimeout(remove, type === 'error' ? 5000 : 3000);
    toast.addEventListener('click', remove);
}

/** Tampilan sementara saat data sedang diambil dari API. */
function renderLoading(root, text = 'Memuat data...') {
    root.innerHTML = `
        <div class="flex items-center gap-3 text-slate-500">
            <div class="spinner" aria-hidden="true"></div>
            <span>${escapeHtml(text)}</span>
        </div>`;
}

/** Tampilan error dengan tombol coba lagi. */
function renderError(root, message, onRetry) {
    root.innerHTML = `
        <div class="rounded-lg border border-red-200 bg-red-50 p-5 text-red-800">
            <p class="font-semibold">Data gagal dimuat</p>
            <p class="mt-1 text-sm">${escapeHtml(message)}</p>
            <button type="button" data-retry
                class="mt-4 rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">
                Coba lagi
            </button>
        </div>`;

    const retry = root.querySelector('[data-retry]');
    if (retry && typeof onRetry === 'function') {
        retry.addEventListener('click', onRetry);
    }
}

/** Modal form dan modal konfirmasi. */
const Modal = {
    _onKeydown: null,

    /**
     * Menampilkan modal berisi form.
     * fields: [{ name, label, type, required, min, max, maxlength, rows, placeholder }]
     * onSubmit(data): dipanggil dengan objek nilai form; lempar error untuk menahan modal.
     */
    open({ title, fields, values = {}, submitLabel = 'Simpan', onSubmit }) {
        const inputs = fields.map((f) => {
            const value = escapeHtml(values[f.name] ?? '');
            const common = `id="field-${f.name}" name="${f.name}" ${f.required ? 'required' : ''}
                placeholder="${escapeHtml(f.placeholder || '')}"
                class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2 text-sm"`;

            const control = f.type === 'textarea'
                ? `<textarea ${common} rows="${f.rows || 3}">${value}</textarea>`
                : `<input ${common} type="${f.type || 'text'}" value="${value}"
                        ${f.min !== undefined ? `min="${f.min}"` : ''}
                        ${f.max !== undefined ? `max="${f.max}"` : ''}
                        ${f.maxlength ? `maxlength="${f.maxlength}"` : ''}>`;

            return `
                <div>
                    <label for="field-${f.name}" class="text-sm font-medium text-slate-700">${escapeHtml(f.label)}</label>
                    ${control}
                </div>`;
        }).join('');

        this._mount(`
            <form id="modal-form" class="space-y-4" novalidate>
                <h2 class="text-lg font-semibold">${escapeHtml(title)}</h2>
                ${inputs}
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" data-close
                        class="rounded-md border border-slate-300 px-4 py-2 text-sm hover:bg-slate-50">Batal</button>
                    <button type="submit"
                        class="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-60">
                        ${escapeHtml(submitLabel)}
                    </button>
                </div>
            </form>`);

        const form = document.getElementById('modal-form');
        const first = form.querySelector('input, textarea');
        if (first) first.focus();

        form.addEventListener('submit', async (event) => {
            event.preventDefault();

            const data = {};
            fields.forEach((f) => {
                const raw = form.elements[f.name].value;
                data[f.name] = f.type === 'number' && raw !== '' ? Number(raw) : raw;
            });

            const button = form.querySelector('[type="submit"]');
            button.disabled = true;
            try {
                await onSubmit(data);
                this.close();
            } catch (err) {
                showToast(err.message || 'Terjadi kesalahan', 'error');
                button.disabled = false;
            }
        });
    },

    /** Modal konfirmasi; mengembalikan Promise<boolean>. */
    confirm({ title, message, confirmLabel = 'Ya, lanjutkan' }) {
        return new Promise((resolve) => {
            this._mount(`
                <div class="space-y-4">
                    <h2 class="text-lg font-semibold">${escapeHtml(title)}</h2>
                    <p class="text-sm text-slate-600">${escapeHtml(message)}</p>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" data-close
                            class="rounded-md border border-slate-300 px-4 py-2 text-sm hover:bg-slate-50">Batal</button>
                        <button type="button" data-confirm
                            class="rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">
                            ${escapeHtml(confirmLabel)}
                        </button>
                    </div>
                </div>`, () => resolve(false));

            const ok = document.querySelector('#modal-root [data-confirm]');
            ok.focus();
            ok.addEventListener('click', () => {
                this._dismiss();
                resolve(true);
            });
        });
    },

    close() {
        this._dismiss();
    },

    _mount(innerHtml, onCancel) {
        this._dismiss();

        const root = document.getElementById('modal-root');
        root.innerHTML = `
            <div class="modal-overlay fixed inset-0 z-40 flex items-center justify-center bg-slate-900/50 p-4">
                <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-xl" role="dialog" aria-modal="true">
                    ${innerHtml}
                </div>
            </div>`;

        const cancel = () => {
            this._dismiss();
            if (onCancel) onCancel();
        };

        root.querySelectorAll('[data-close]').forEach((el) => el.addEventListener('click', cancel));
        root.firstElementChild.addEventListener('mousedown', (event) => {
            if (event.target === root.firstElementChild) cancel();
        });

        this._onKeydown = (event) => {
            if (event.key === 'Escape') cancel();
        };
        document.addEventListener('keydown', this._onKeydown);
    },

    _dismiss() {
        if (this._onKeydown) {
            document.removeEventListener('keydown', this._onKeydown);
            this._onKeydown = null;
        }
        const root = document.getElementById('modal-root');
        if (root) root.innerHTML = '';
    },
};
