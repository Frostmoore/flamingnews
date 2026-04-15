import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
// Manda cookie di sessione con ogni richiesta (necessario per Sanctum SPA auth)
window.axios.defaults.withCredentials = true;
