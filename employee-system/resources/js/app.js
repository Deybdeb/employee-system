import { createApp, h } from "vue";
import { createInertiaApp } from "@inertiajs/vue3";

// Simple route helper function
const route = (name, params = {}) => {
    const routes = {
        'dashboard': '/dashboard',
        'my-info.index': '/my-info',
        'my-info.personal': '/my-info/personal',
        'my-info.contact': '/my-info/contact',
        'my-info.password': '/my-info/password',
        'directory.index': '/directory',
        'leave-requests.index': '/leave-requests',
        'leave-requests.create': '/leave-requests/create',
        'leave-requests.store': '/leave-requests',
        'leave-requests.admin': '/leave-requests/admin',
        'overtime-requests.index': '/overtime-requests',
        'overtime-requests.store': '/overtime-requests',
        'overtime-requests.admin': '/overtime-requests/admin',
        'timesheets.index': '/timesheets',
        'timesheets.store': '/timesheets',
        'timesheets.admin': '/timesheets/admin',
        'attendance.index': '/attendance',
        'attendance.admin': '/attendance/admin',
        'admin.time-logs.index': '/admin/time-logs',
        'admin.time-logs.store': '/admin/time-logs',
        'admin.time-logs.export-csv': '/admin/time-logs/export/csv',
        'admin.time-logs.show': (id) => `/admin/time-logs/${id}`,
        'admin.time-logs.photo': (id) => `/admin/time-logs/${id}/photo`,
        'admin.time-logs.update': (id) => `/admin/time-logs/${id}`,
        'admin.time-logs.destroy': (id) => `/admin/time-logs/${id}`,
        'admin.time-logs.stats': (userId) => `/admin/time-logs/${userId}/stats`,
        'time-logs.store': '/time-logs',
        'time-logs.latest': '/time-logs/latest',
        'time-logs.myLogs': '/time-logs/my-logs',
        'time-logs.photo': (id) => `/time-logs/${id}/photo`,
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
    
    // Handle routes that are functions
    if (typeof routes[name] === 'function') {
        return routes[name](params);
    }
    
    // Handle routes with parameters
    if (typeof params === 'number' || typeof params === 'string') {
        if (name === 'leave-requests.approve') return `/leave-requests/${params}/approve`;
        if (name === 'leave-requests.decline') return `/leave-requests/${params}/decline`;
        if (name === 'leave-requests.cancel') return `/leave-requests/${params}/cancel`;
        if (name === 'leave-requests.destroy') return `/leave-requests/${params}`;
        if (name === 'overtime-requests.approve') return `/overtime-requests/${params}/approve`;
        if (name === 'overtime-requests.decline') return `/overtime-requests/${params}/decline`;
        if (name === 'overtime-requests.cancel') return `/overtime-requests/${params}/cancel`;
        if (name === 'timesheets.submit') return `/timesheets/${params}/submit`;
        if (name === 'timesheets.approve') return `/timesheets/${params}/approve`;
        if (name === 'timesheets.reject') return `/timesheets/${params}/reject`;
        if (name === 'attendance.employee') return `/attendance/employee/${params}`;
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