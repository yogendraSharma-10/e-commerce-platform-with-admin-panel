import { createApp } from 'vue';
import router from './router'; // Vue Router instance for navigation
import store from './store';   // Vuex store instance for state management
import axios from 'axios';     // HTTP client for API requests

// Import global components that might be used across the application
import ProductCard from './components/shared/ProductCard.vue';

// Import global styles for the application
import '../sass/app.scss';

/**
 * Configure Axios for making HTTP requests to the backend API.
 * This includes setting a base URL, common headers, and handling credentials.
 */
axios.defaults.baseURL = import.meta.env.VITE_API_BASE_URL || '/api/v1';
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
axios.defaults.headers.common['Accept'] = 'application/json';
axios.defaults.withCredentials = true; // Ensures cookies (like session IDs) are sent with requests

/**
 * Axios Request Interceptor:
 * This interceptor is used to attach an authentication token (e.g., JWT)
 * to every outgoing request if it exists in local storage.
 * This is a common pattern for API authentication.
 */
axios.interceptors.request.use(config => {
    const token = localStorage.getItem('authToken'); // Retrieve token from local storage
    if (token) {
        config.headers.Authorization = `Bearer ${token}`; // Add Authorization header
    }
    return config;
}, error => {
    // Handle request errors
    return Promise.reject(error);
});

/**
 * Axios Response Interceptor:
 * This interceptor can be used to handle global errors, like 401 Unauthorized responses,
 * to redirect the user to a login page or refresh tokens.
 */
axios.interceptors.response.use(response => response, error => {
    if (error.response && error.response.status === 401) {
        // Example: If unauthorized, clear token and redirect to login
        localStorage.removeItem('authToken');
        // router.push('/login'); // Uncomment if you have a login route
        console.warn('Unauthorized access. Please log in again.');
    }
    return Promise.reject(error);
});

/**
 * Cross-Project Context: Microservice URLs
 * Define base URLs for other interconnected services. These URLs are typically
 * loaded from environment variables to allow for different deployments (dev, staging, prod).
 * Making them available globally can simplify integration with these services from Vue components.
 */
const MICROSERVICE_URLS = {
    ANALYTICS_DASHBOARD_SERVICE: import.meta.env.VITE_ANALYTICS_SERVICE_URL || 'http://localhost:8002/api/v1/analytics',
    CODE_SNIPPET_MANAGER_SERVICE: import.meta.env.VITE_SNIPPET_SERVICE_URL || 'http://localhost:8003/api/v1/snippets',
    // Add other services as needed, e.g., for user profiles, notifications, etc.
    // DEVELOPER_PORTFOLIO_SERVICE: import.meta.env.VITE_PORTFOLIO_SERVICE_URL || 'http://localhost:8004/api/v1/portfolios',
};

/**
 * The root Vue component for the application.
 * In a typical setup, this would be imported from a separate App.vue file.
 * For this project structure, we define a minimal inline component that primarily
 * renders the router view, acting as the main layout for the single-page application.
 */
const App = {
    template: `
        <div id="app-container" class="min-h-screen flex flex-col">
            <!-- The router-view component renders the component mapped to the current route -->
            <router-view></router-view>
        </div>
    `,
    data() {
        return {
            // Global application state or configuration can be defined here
            appName: 'E-commerce Platform',
            // Example: isUserLoggedIn: false,
        };
    },
    mounted() {
        console.log(`${this.appName} Vue application mounted successfully!`);
        // Log microservice URLs for debugging/verification
        console.log('Microservice Integrations:', MICROSERVICE_URLS);
    }
};

/**
 * Create and mount the Vue application instance.
 * This is the entry point where Vue takes control of the DOM element.
 */
const app = createApp(App);

// Register global components that can be used anywhere without explicit import
app.component('ProductCard', ProductCard);

// Use Vue Router for client-side routing
app.use(router);

// Use Vuex store for centralized state management
app.use(store);

// Make Axios instance available globally via `this.$axios` or `this.$http` in components
app.config.globalProperties.$axios = axios;
app.config.globalProperties.$http = axios; // Provide an alias for convenience

// Make microservice URLs available globally via `this.$microservices` in components
app.config.globalProperties.$microservices = MICROSERVICE_URLS;

// Mount the application to the DOM element with id 'app'
// This element is expected to be present in `resources/views/app.blade.php`
app.mount('#app');

console.log('Vue application bootstrapping complete and mounted to #app.');