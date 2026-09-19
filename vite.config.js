import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: false,
            buildDirectory: 'assets-compiled',
        }),
    ],
    build: {
        chunkSizeWarningLimit: 1200,
        rollupOptions: {
            output: {
                manualChunks(id) {
                    if (id.includes('node_modules/three')) return 'three';
                    if (id.includes('node_modules/gsap') || id.includes('node_modules/lenis')) return 'motion';
                },
            },
        },
    },
});
