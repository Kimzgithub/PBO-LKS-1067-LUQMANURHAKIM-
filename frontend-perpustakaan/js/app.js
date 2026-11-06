// Router berbasis hash (#/dashboard, #/books, #/categories).

const ROUTES = {
    '/dashboard': DashboardPage,
    '/books': BooksPage,
    '/categories': CategoriesPage,
};

let navigationToken = 0;

function highlightMenu(path) {
    document.querySelectorAll('#main-nav [data-route]').forEach((link) => {
        if (link.dataset.route === path) {
            link.setAttribute('aria-current', 'page');
        } else {
            link.removeAttribute('aria-current');
        }
    });
}

async function navigate() {
    const path = location.hash.replace(/^#/, '') || '/dashboard';

    // Hash tidak dikenal -> arahkan ke dashboard.
    if (!ROUTES[path]) {
        location.hash = '#/dashboard';
        return;
    }

    const token = ++navigationToken;
    const isCurrent = () => token === navigationToken;
    const root = document.getElementById('app');

    root.onclick = null;
    Modal.close();
    highlightMenu(path);

    try {
        await ROUTES[path].render(root, isCurrent);
    } catch (err) {
        if (isCurrent()) {
            renderError(root, err.message || 'Terjadi kesalahan', navigate);
        }
    }
}

window.addEventListener('hashchange', navigate);
document.addEventListener('DOMContentLoaded', navigate);
