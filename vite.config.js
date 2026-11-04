import {
    defineConfig
} from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/css/upload-animations.css',
                'resources/js/upload-interactions.js',
                'resources/css/portal.css',
                'resources/js/hover-photos.js',
                'resources/js/portal-motion.js',
                'resources/js/portal-carousel.js',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        cors: true,
    },
});
