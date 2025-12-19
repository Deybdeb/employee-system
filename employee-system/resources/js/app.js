import { createApp, h } from "vue";
import { createInertiaApp } from "@inertiajs/vue3";
// Use Ziggy from npm package
import { ZiggyVue } from "ziggy-js";


createInertiaApp({
    // Component Resolver (Confirmed Correct)
    resolve: (name) => {
        const pages = import.meta.glob('./Pages/**/*.vue', { eager: true });
        return pages[`./Pages/${name}.vue`]; 
    },

    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            // Register Ziggy with routes from server props, or empty object as fallback
            .use(ZiggyVue, props.initialPage.props.ziggy || {})
            .mount(el);
    },
});