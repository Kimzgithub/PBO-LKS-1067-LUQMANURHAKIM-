// Halaman Buku: tabel + modal CRUD.

const BOOK_FIELDS = [
    { name: 'judul', label: 'Judul', type: 'text', required: true, maxlength: 255 },
    { name: 'penulis', label: 'Penulis', type: 'text', required: true, maxlength: 255 },
    { name: 'tahun', label: 'Tahun terbit', type: 'number', required: true, min: 1000, max: 2100 },
    { name: 'stok', label: 'Stok', type: 'number', required: true, min: 0 },
];

const BooksPage = {
    root: null,
    books: [],

    async render(root, isCurrent = () => true) {
        this.root = root;
        renderLoading(root, 'Memuat daftar buku...');

        const books = await BookApi.list();
        if (!isCurrent()) return;

        this.books = books;
        this.draw();
    },

    draw() {
        const sorted = [...this.books].sort((a, b) => a.id - b.id);

        const body = sorted.length
            ? sorted.map((book) => `
                <tr class="border-t border-slate-200">
                    <td class="px-4 py-3 text-slate-500">${escapeHtml(book.id)}</td>
                    <td class="px-4 py-3 font-medium">${escapeHtml(book.judul)}</td>
                    <td class="px-4 py-3">${escapeHtml(book.penulis)}</td>
                    <td class="px-4 py-3">${escapeHtml(book.tahun)}</td>
                    <td class="px-4 py-3">
                        ${book.stok > 0
                            ? escapeHtml(book.stok)
                            : '<span class="rounded bg-red-100 px-2 py-0.5 text-xs font-medium text-red-700">Habis</span>'}
                    </td>
                    <td class="whitespace-nowrap px-4 py-3 text-right">
                        <button type="button" data-action="edit" data-id="${book.id}"
                            class="rounded px-2 py-1 text-sm text-blue-700 hover:bg-blue-50">Ubah</button>
                        <button type="button" data-action="delete" data-id="${book.id}"
                            class="rounded px-2 py-1 text-sm text-red-700 hover:bg-red-50">Hapus</button>
                    </td>
                </tr>`).join('')
            : `<tr><td colspan="6" class="px-4 py-10 text-center text-slate-500">
                   Belum ada buku. Klik “+ Tambah Buku” untuk menambahkan.
               </td></tr>`;

        this.root.innerHTML = `
            <div class="flex items-center justify-between gap-4">
                <h1 class="text-2xl font-semibold tracking-tight">Buku</h1>
                <button type="button" data-action="add"
                    class="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                    + Tambah Buku
                </button>
            </div>

            <div class="mt-6 overflow-x-auto rounded-lg border border-slate-200 bg-white">
                <table class="data-table w-full text-left text-sm">
                    <thead class="bg-slate-100 text-slate-600">
                        <tr>
                            <th class="px-4 py-3 font-medium">ID</th>
                            <th class="px-4 py-3 font-medium">Judul</th>
                            <th class="px-4 py-3 font-medium">Penulis</th>
                            <th class="px-4 py-3 font-medium">Tahun</th>
                            <th class="px-4 py-3 font-medium">Stok</th>
                            <th class="px-4 py-3 text-right font-medium">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>${body}</tbody>
                </table>
            </div>`;

        this.root.onclick = (event) => {
            const button = event.target.closest('[data-action]');
            if (!button) return;

            const id = Number(button.dataset.id);
            const book = this.books.find((b) => b.id === id);

            if (button.dataset.action === 'add') this.openForm();
            if (button.dataset.action === 'edit' && book) this.openForm(book);
            if (button.dataset.action === 'delete' && book) this.confirmDelete(book);
        };
    },

    async refresh() {
        this.books = await BookApi.list();
        this.draw();
    },

    openForm(book = null) {
        Modal.open({
            title: book ? 'Ubah buku' : 'Tambah buku',
            fields: BOOK_FIELDS,
            values: book || { stok: 1 },
            submitLabel: 'Simpan',
            onSubmit: async (data) => {
                if (book) {
                    await BookApi.update(book.id, data);
                    showToast('Buku berhasil diperbarui');
                } else {
                    await BookApi.create(data);
                    showToast('Buku berhasil dibuat');
                }
                await this.refresh();
            },
        });
    },

    async confirmDelete(book) {
        const ok = await Modal.confirm({
            title: 'Hapus buku',
            message: `Hapus “${book.judul}”? Tindakan ini tidak bisa dibatalkan.`,
            confirmLabel: 'Hapus',
        });
        if (!ok) return;

        try {
            await BookApi.remove(book.id);
            showToast('Buku berhasil dihapus');
            await this.refresh();
        } catch (err) {
            showToast(err.message, 'error');
        }
    },
};
