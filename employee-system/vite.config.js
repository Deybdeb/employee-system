import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import vue from "@vitejs/plugin-vue";
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
    // 1. Explicitly configure the server and HMR host
    // This tells Vite to listen on the same host the Laravel server is using (127.0.0.1:8000)
    server: {
        host: '127.0.0.1',
        port: 5173, // Default Vite port, but explicitly defined for clarity
        hmr: {
            host: '127.0.0.1',
            port: 5173,
        },
    },

    plugins: [
        tailwindcss(),
        laravel({
            input: ["resources/css/app.css", "resources/js/app.js"],
            // Ensure the refresh is set up for file types you edit often
            refresh: [
                'resources/views/**',
                'routes/**',
                'app/Http/Controllers/**',
            ],
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],

    // 2. Resolve issue where Vite might not load large libraries properly
    optimizeDeps: {
        include: [
            '@inertiajs/vue3',
            'vue',
            'axios',
            // Add any other large libraries if you run into further issues
        ],
    },
});