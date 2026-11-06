// Halaman Dashboard: kartu statistik + 5 buku terbaru.

const DashboardPage = {
    async render(root, isCurrent = () => true) {
        renderLoading(root, 'Memuat dashboard...');

        const [books, categories] = await Promise.all([BookApi.list(), CategoryApi.list()]);
        if (!isCurrent()) return;

        // "Terbaru" = ID terbesar.
        const latest = [...books].sort((a, b) => b.id - a.id).slice(0, 5);

        const rows = latest.length
            ? latest.map((book) => `
                <li class="flex items-center justify-between gap-4 px-5 py-3">
                    <div class="min-w-0">
                        <p class="truncate font-medium">${escapeHtml(book.judul)}</p>
                        <p class="truncate text-sm text-slate-500">${escapeHtml(book.penulis)} · ${escapeHtml(book.tahun)}</p>
                    </div>
                    <span class="shrink-0 text-sm text-slate-600">Stok ${escapeHtml(book.stok)}</span>
                </li>`).join('')
            : `<li class="px-5 py-8 text-center text-sm text-slate-500">
                   Belum ada buku. Tambahkan buku pertama di menu Buku.
               </li>`;

        root.innerHTML = `
            <h1 class="text-2xl font-semibold tracking-tight">Dashboard</h1>

            <div class="mt-6 grid gap-4 sm:grid-cols-2">
                <div class="rounded-lg bg-blue-600 p-6 text-white">
                    <p class="text-sm text-blue-100">Total Buku</p>
                    <p class="mt-2 text-4xl font-bold">${books.length}</p>
                </div>
                <div class="rounded-lg bg-orange-500 p-6 text-white">
                    <p class="text-sm text-orange-100">Kategori</p>
                    <p class="mt-2 text-4xl font-bold">${categories.length}</p>
                </div>
            </div>

            <section class="mt-8">
                <h2 class="text-lg font-semibold">Buku terbaru</h2>
                <ul class="mt-3 divide-y divide-slate-200 rounded-lg border border-slate-200 bg-white">
                    ${rows}
                </ul>
            </section>`;
    },
};
