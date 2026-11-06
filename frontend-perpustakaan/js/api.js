// Klien API: ApiClient (fetch) + service per resource.

class ApiError extends Error {
    constructor(message, status = 0, payload = null) {
        super(message);
        this.name = 'ApiError';
        this.status = status;
        this.payload = payload;
    }
}

class ApiClient {
    constructor(baseUrl) {
        this.baseUrl = baseUrl.replace(/\/$/, '');
    }

    async request(method, path, body) {
        const hasBody = body !== undefined;
        let response;

        try {
            response = await fetch(this.baseUrl + path, {
                method,
                headers: {
                    Accept: 'application/json',
                    ...(hasBody ? { 'Content-Type': 'application/json' } : {}),
                },
                body: hasBody ? JSON.stringify(body) : undefined,
            });
        } catch (networkError) {
            throw new ApiError(
                `Tidak dapat terhubung ke server API. Pastikan backend berjalan di ${this.baseUrl}`
            );
        }

        let payload = null;
        try {
            payload = await response.json();
        } catch (parseError) {
            // Response bukan JSON; ditangani di bawah.
        }

        if (!response.ok || (payload && payload.success === false)) {
            throw new ApiError(
                (payload && payload.message) || `Terjadi kesalahan (HTTP ${response.status})`,
                response.status,
                payload
            );
        }

        return payload;
    }

    get(path) { return this.request('GET', path); }
    post(path, body) { return this.request('POST', path, body); }
    put(path, body) { return this.request('PUT', path, body); }
    delete(path) { return this.request('DELETE', path); }
}

const api = new ApiClient(APP_CONFIG.BASE_URL);

// Service buku: setiap method mengembalikan data (bukan seluruh response).
const BookApi = {
    list: async () => (await api.get('/books')).data,
    find: async (id) => (await api.get(`/books/${id}`)).data,
    create: async (data) => (await api.post('/books', data)).data,
    update: async (id, data) => (await api.put(`/books/${id}`, data)).data,
    remove: async (id) => { await api.delete(`/books/${id}`); },
};

// Service kategori
const CategoryApi = {
    list: async () => (await api.get('/categories')).data,
    find: async (id) => (await api.get(`/categories/${id}`)).data,
    create: async (data) => (await api.post('/categories', data)).data,
    update: async (id, data) => (await api.put(`/categories/${id}`, data)).data,
    remove: async (id) => { await api.delete(`/categories/${id}`); },
};
