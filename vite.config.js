import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/sources/admin/style.scss',
            ],
            refresh: true,
            fonts: [
                // Body + UI + tiêu đề cấp item (card, widget) — sans thiết kế riêng cho tiếng Việt
                bunny('Be Vietnam Pro', {
                    weights: [400, 500, 600, 700],
                    subsets: ['latin', 'vietnamese'],
                }),
                // Display serif — hero, H1, tiêu đề section, số liệu lớn
                bunny('Fraunces', {
                    weights: [600, 700],
                    subsets: ['latin', 'vietnamese'],
                }),
                // Script accent — eyebrow/điểm nhấn cảm xúc, dùng tiết chế
                bunny('Dancing Script', {
                    weights: [600, 700],
                    subsets: ['latin', 'vietnamese'],
                }),
            ],
        }),
        tailwindcss(),
    ],
    server: {
        host: '0.0.0.0',
        port: 5173,
        strictPort: true,
        cors: true,
        origin: 'http://localhost:5173',
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
