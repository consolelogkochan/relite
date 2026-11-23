import inertia from '@inertiajs/vite';
import tailwindcss from '@tailwindcss/vite';
import react from '@vitejs/plugin-react';
import laravel from 'laravel-vite-plugin';
import { defineConfig, loadEnv } from 'vite';

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '');
    const vitePort = Number(env.VITE_PORT ?? 5173);

    return {
        plugins: [
            laravel({
                input: ['resources/css/app.css', 'resources/js/app.tsx'],
                refresh: true,
            }),
            inertia(),
            react(),
            tailwindcss(),
        ],
        server: {
            // Sail のコンテナの中で動かし、手元のブラウザから開くため
            host: '0.0.0.0',
            port: vitePort,
            strictPort: true,
            hmr: { host: 'localhost' },
            watch: {
                ignored: ['**/storage/framework/views/**'],
            },
        },
    };
});
