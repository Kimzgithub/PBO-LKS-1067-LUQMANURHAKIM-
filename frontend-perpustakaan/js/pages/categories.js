// Halaman Kategori: tabel + modal CRUD.

const CATEGORY_FIELDS = [
    { name: 'nama', label: 'Nama kategori', type: 'text', required: true, maxlength: 100 },
    { name: 'deskripsi', label: 'Deskripsi', type: 'textarea', rows: 3 },
];

const CategoriesPage = {
    root: null,
    categories: [],

    async render(root, isCurrent = () => true) {
        this.root = root;
        renderLoading(root, 'Memuat daftar kategori...');

        const categories = await CategoryApi.list();
        if (!isCurrent()) return;

        this.categories = categories;
        this.draw();
    },

    draw() {
        const sorted = [...this.categories].sort((a, b) => a.id - b.id);

        const body = sorted.length
            ? sorted.map((category) => `
                <tr class="border-t border-slate-200">
                    <td class="px-4 py-3 text-slate-500">${escapeHtml(category.id)}</td>
                    <td class="px-4 py-3 font-medium">${escapeHtml(category.nama)}</td>
                    <td class="px-4 py-3 text-slate-600">${escapeHtml(category.deskripsi || '—')}</td>
                    <td class="whitespace-nowrap px-4 py-3 text-right">
                        <button type="button" data-action="edit" data-id="${category.id}"
                            class="rounded px-2 py-1 text-sm text-blue-700 hover:bg-blue-50">Ubah</button>
                        <button type="button" data-action="delete" data-id="${category.id}"
                            class="rounded px-2 py-1 text-sm text-red-700 hover:bg-red-50">Hapus</button>
                    </td>
                </tr>`).join('')
            : `<tr><td colspan="4" class="px-4 py-10 text-center text-slate-500">
                   Belum ada kategori. Klik “+ Tambah Kategori” untuk menambahkan.
               </td></tr>`;

        this.root.innerHTML = `
            <div class="flex items-center justify-between gap-4">
                <h1 class="text-2xl font-semibold tracking-tight">Kategori</h1>
                <button type="button" data-action="add"
                    class="rounded-md bg-orange-500 px-4 py-2 text-sm font-medium text-white hover:bg-orange-600">
                    + Tambah Kategori
                </button>
            </div>

            <div class="mt-6 overflow-x-auto rounded-lg border border-slate-200 bg-white">
                <table class="data-table w-full text-left text-sm">
                    <thead class="bg-slate-100 text-slate-600">
                        <tr>
                            <th class="px-4 py-3 font-medium">ID</th>
                            <th class="px-4 py-3 font-medium">Nama</th>
                            <th class="px-4 py-3 font-medium">Deskripsi</th>
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
            const category = this.categories.find((c) => c.id === id);

            if (button.dataset.action === 'add') this.openForm();
            if (button.dataset.action === 'edit' && category) this.openForm(category);
            if (button.dataset.action === 'delete' && category) this.confirmDelete(category);
        };
    },

    async refresh() {
        this.categories = await CategoryApi.list();
        this.draw();
    },

    openForm(category = null) {
        Modal.open({
            title: category ? 'Ubah kategori' : 'Tambah kategori',
            fields: CATEGORY_FIELDS,
            values: category || {},
            submitLabel: 'Simpan',
            onSubmit: async (data) => {
                if (category) {
                    await CategoryApi.update(category.id, data);
                    showToast('Kategori berhasil diperbarui');
                } else {
                    await CategoryApi.create(data);
                    showToast('Kategori berhasil dibuat');
                }
                await this.refresh();
            },
        });
    },

    async confirmDelete(category) {
        const ok = await Modal.confirm({
            title: 'Hapus kategori',
            message: `Hapus kategori “${category.nama}”? Tindakan ini tidak bisa dibatalkan.`,
            confirmLabel: 'Hapus',
        });
        if (!ok) return;

        try {
            await CategoryApi.remove(category.id);
            showToast('Kategori berhasil dihapus');
            await this.refresh();
        } catch (err) {
            showToast(err.message, 'error');
        }
    },
};
