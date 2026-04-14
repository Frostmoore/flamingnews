import { ref } from 'vue';
import axios from 'axios';

export function useTopics() {
    const topics = ref([]);
    const topic = ref(null);
    const meta = ref({});
    const loading = ref(false);
    const isAnalyzing = ref(false);
    const error = ref(null);

    async function fetchTopics({ page = 1, perPage = 15 } = {}) {
        loading.value = true;
        error.value = null;
        try {
            const res = await axios.get('/api/topics', { params: { page, per_page: perPage } });
            topics.value = res.data.data;
            meta.value = res.data.meta;
        } catch (e) {
            error.value = e.response?.data?.message || 'Errore nel caricamento dei topic.';
        } finally {
            loading.value = false;
        }
    }

    async function fetchTopic(id) {
        loading.value = true;
        error.value = null;
        try {
            const res = await axios.get(`/api/topics/${id}`);
            topic.value = res.data;
            return res.data;
        } catch (e) {
            error.value = e.response?.data?.message || 'Topic non trovato.';
            return null;
        } finally {
            loading.value = false;
        }
    }

    async function generateAnalysis(id) {
        isAnalyzing.value = true;
        error.value = null;
        try {
            const res = await axios.post(`/api/topics/${id}/analyze`);
            if (topic.value) {
                topic.value.ai_analysis = res.data.ai_analysis;
                topic.value.ai_generated_at = res.data.ai_generated_at;
            }
            return res.data.ai_analysis;
        } catch (e) {
            error.value = e.response?.data?.message || 'Errore nella generazione dell\'analisi AI.';
            return null;
        } finally {
            isAnalyzing.value = false;
        }
    }

    return { topics, topic, meta, loading, isAnalyzing, error, fetchTopics, fetchTopic, generateAnalysis };
}
