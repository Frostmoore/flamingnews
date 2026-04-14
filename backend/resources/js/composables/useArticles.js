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
    const meta = ref({ current_page: 1, last_page: 1, per_page: 20, total: 0 });
    const loading = ref(false);
    const error = ref(null);

    async function fetchArticles({ category = null, page = 1, perPage = 20 } = {}) {
        loading.value = true;
        error.value = null;

        try {
            if (!category) {
                // Una chiamata per ogni categoria in parallelo
                const responses = await Promise.all(
                    ALL_CATEGORIES.map(cat =>
                        axios.get('/api/articles', {
                            params: { category: cat, page: 1, per_page: PER_CATEGORY },
                        })
                    )
                );

                // Unisci e ordina per data
                const all = responses.flatMap(r => r.data.data);
                all.sort((a, b) => new Date(b.published_at) - new Date(a.published_at));

                articles.value = all;
                meta.value = {
                    current_page: 1,
                    last_page: 1,
                    per_page: all.length,
                    total: all.length,
                };
            } else {
                // Singola categoria: comportamento normale con paginazione
                const res = await axios.get('/api/articles', {
                    params: { category, page, per_page: perPage },
                });
                articles.value = res.data.data;
                meta.value = res.data.meta;
            }
        } catch (e) {
            error.value = e.response?.data?.message || 'Errore nel caricamento degli articoli.';
        } finally {
            loading.value = false;
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

    return { articles, meta, loading, error, fetchArticles, fetchArticle, toggleLike };
}
