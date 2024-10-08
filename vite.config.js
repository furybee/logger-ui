import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import path from 'path';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        vue(),
        laravel({
            buildDirectory: 'vendor/logger-ui',
            input: ['resources/css/logger-ui.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
    resolve: {
        alias: {
            '@': path.resolve(__dirname, 'resources/js'),
        },
    },
    build: {
        outDir: 'public',
        // rollupOptions: {
        //     output: {
        //         manualChunks: undefined,
        //     },
        // },
    },
});
