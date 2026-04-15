import { ref } from 'vue';
import axios from 'axios';

const ALL_CATEGORIES = [
    'politica', 'economia', 'esteri', 'tecnologia',
    'sport', 'cultura', 'generale', 'scienza', 'salute',
    'ambiente', 'istruzione', 'cibo', 'viaggi',
];

const PER_CATEGORY = 6; // articoli per categoria in modalità "Tutte"

export function useArticles() {
    const articles = ref([]);
    const meta = ref({ current_page: 1, last_page: 1, per_page: 10, total: 0 });
    const loading = ref(false);
    const loadingMore = ref(false);
    const error = ref(null);

    const hasMore = () => meta.value.current_page < meta.value.last_page;

    async function fetchArticles({ category = null, page = 1, perPage = 10, q = '' } = {}) {
        const appending = page > 1;
        if (appending) {
            loadingMore.value = true;
        } else {
            loading.value = true;
        }
        error.value = null;

        try {
            // Ricerca: singola chiamata con parametro q
            if (q) {
                const params = { q, page, per_page: perPage };
                if (category) params.category = category;
                const res = await axios.get('/api/articles', { params });
                articles.value = appending ? [...articles.value, ...res.data.data] : res.data.data;
                meta.value = res.data.meta;
                return;
            }

            if (!category) {
                // Tab "Temi": singola chiamata paginata, il backend filtra per multi-testata
                const res = await axios.get('/api/articles', {
                    params: { page, per_page: perPage },
                });
                articles.value = appending ? [...articles.value, ...res.data.data] : res.data.data;
                meta.value = res.data.meta;
            } else {
                // Singola categoria: lazy load con paginazione
                const res = await axios.get('/api/articles', {
                    params: { category, page, per_page: perPage },
                });
                articles.value = appending ? [...articles.value, ...res.data.data] : res.data.data;
                meta.value = res.data.meta;
            }
        } catch (e) {
            error.value = e.response?.data?.message || 'Errore nel caricamento degli articoli.';
        } finally {
            loading.value = false;
            loadingMore.value = false;
        }
    }

    async function toggleLike(article) {
        try {
            const res = await axios.post(`/api/articles/${article.id}/like`);
            article.liked       = res.data.liked;
            article.likes_count = res.data.likes_count;
        } catch (e) {
            // non autenticato o errore silenzioso
        }
    }

    async function fetchArticle(id) {
        loading.value = true;
        error.value = null;
        try {
            const res = await axios.get(`/api/articles/${id}`);
            return res.data;
        } catch (e) {
            error.value = e.response?.data?.message || 'Articolo non trovato.';
            return null;
        } finally {
            loading.value = false;
        }
    }

    return { articles, meta, loading, loadingMore, hasMore, error, fetchArticles, fetchArticle, toggleLike };
}
