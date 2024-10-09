import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';
import path from 'path';
import vue from "@vitejs/plugin-vue";

export default defineConfig(({ command, mode }) => {
    process.env = {...process.env, ...loadEnv(mode, path.join(__dirname, "../../"), '')}

    return {
        plugins: [
            vue({
                template: {
                    transformAssetUrls: {
                        base: null,
                        includeAbsolute: false,
                    },
                },
            }),
            laravel({
                hotFile: '../../storage/vite.hot',
                input: ['resources/css/logger-ui.css', 'resources/js/app.js'],
                refresh: true,
            }),
        ],
        resolve: {
            alias: {
                'vue': 'vue/dist/vue.esm-bundler.js',
                '@': path.resolve(__dirname, 'resources/js'),
            },
        },
        build: {
            outDir: path.join(__dirname, "public"),
            rollupOptions: {
                output: {
                    assetFileNames: 'assets/[ext]/[name][extname]',
                    chunkFileNames: 'assets/js/[name].[hash].js',
                    entryFileNames: 'assets/js/[name].js'
                }
            }
        }
    };
});
