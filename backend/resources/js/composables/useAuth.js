import { ref, computed } from 'vue';
import axios from 'axios';

const user = ref(JSON.parse(localStorage.getItem('fn_user') || 'null'));
const token = ref(localStorage.getItem('fn_token') || null);

// Configura axios con il token Sanctum
if (token.value) {
    axios.defaults.headers.common['Authorization'] = `Bearer ${token.value}`;
}

export function useAuth() {
    const isAuthenticated = computed(() => !!token.value);
    const isPremium = computed(() => user.value?.is_premium ?? false);

    async function login(email, password) {
        const res = await axios.post('/api/auth/login', { email, password });
        _setSession(res.data);
        return res.data;
    }

    async function register(name, email, password, passwordConfirmation) {
        const res = await axios.post('/api/auth/register', {
            name,
            email,
            password,
            password_confirmation: passwordConfirmation,
        });
        _setSession(res.data);
        return res.data;
    }

    async function logout() {
        try {
            await axios.post('/api/auth/logout');
        } finally {
            _clearSession();
        }
    }

    function _setSession(data) {
        user.value = data.user;
        token.value = data.token;
        localStorage.setItem('fn_user', JSON.stringify(data.user));
        localStorage.setItem('fn_token', data.token);
        axios.defaults.headers.common['Authorization'] = `Bearer ${data.token}`;
    }

    function _clearSession() {
        user.value = null;
        token.value = null;
        localStorage.removeItem('fn_user');
        localStorage.removeItem('fn_token');
        delete axios.defaults.headers.common['Authorization'];
    }

    return { user, token, isAuthenticated, isPremium, login, register, logout };
}
