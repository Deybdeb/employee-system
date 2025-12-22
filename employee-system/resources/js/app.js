import { createApp, h } from "vue";
import { createInertiaApp } from "@inertiajs/vue3";

// Simple route helper function
const route = (name, params = {}) => {
    const routes = {
        'dashboard': '/dashboard',
        'my-info.index': '/my-info',
        'leave-requests.index': '/leave-requests',
        'leave-requests.create': '/leave-requests/create',
        'leave-requests.store': '/leave-requests',
        'leave-requests.admin': '/leave-requests/admin',
        'logout': '/logout',
        'login': '/login',
        'register': '/register',
        'password.request': '/forgot-password',
        'password.email': '/forgot-password',
        'password.verify': '/verify-otp',
        'password.verify.submit': '/verify-otp',
        'password.reset': '/reset-password',
        'password.update': '/reset-password',
    };
    
    // Handle routes with parameters
    if (typeof params === 'number' || typeof params === 'string') {
        if (name === 'leave-requests.approve') return `/leave-requests/${params}/approve`;
        if (name === 'leave-requests.decline') return `/leave-requests/${params}/decline`;
        if (name === 'leave-requests.cancel') return `/leave-requests/${params}/cancel`;
        if (name === 'leave-requests.destroy') return `/leave-requests/${params}`;
    }
    
    return routes[name] || `/${name.replace(/\./g, '/')}`;
};

// Make it available globally
window.route = route;

createInertiaApp({
    resolve: (name) => {
        const pages = import.meta.glob('./Pages/**/*.vue', { eager: true });
        return pages[`./Pages/${name}.vue`];
    },

    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) })
            .use(plugin);
        
        // Make route available in all Vue components
        app.config.globalProperties.route = route;
        
        app.mount(el);
    },
});