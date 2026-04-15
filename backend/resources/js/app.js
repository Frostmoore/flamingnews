import './bootstrap';
import { createApp } from 'vue';
import Alpine from 'alpinejs';

// Componenti Vue
import Analytics from './components/Analytics.vue';
import ArticleDetail from './components/ArticleDetail.vue';
import CoverageComparative from './components/CoverageComparative.vue';
import NewsFeed from './components/NewsFeed.vue';

// Alpine.js — micro-interazioni
window.Alpine = Alpine;
Alpine.start();

// Mount Vue selettivo: ogni pagina Blade espone un div con data-vue-component
// I data-* del div vengono passati come props al componente
const vueComponents = { Analytics, ArticleDetail, CoverageComparative, NewsFeed };

document.querySelectorAll('[data-vue-component]').forEach((el) => {
    const name = el.dataset.vueComponent;

    // Raccoglie tutti i data-* tranne data-vue-component come props
    const props = {};
    Object.entries(el.dataset).forEach(([key, value]) => {
        if (key !== 'vueComponent') props[key] = value;
    });

    if (vueComponents[name]) {
        createApp(vueComponents[name], props).mount(el);
    }
});
