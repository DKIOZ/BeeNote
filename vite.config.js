import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import { VitePWA } from 'vite-plugin-pwa';

export default defineConfig({
    plugins: [
        laravel({
            input: 'resources/js/app.js',
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        VitePWA({
            // L'enregistrement est fait dans app.js : /sw.js est servi depuis la racine par Laravel.
            injectRegister: false,
            includeManifestIcons: false,
            registerType: 'autoUpdate',
            manifest: {
                id: '/',
                name: 'BeeNote',
                short_name: 'BeeNote',
                description: 'Le carnet de vos ruches, à la voix et sans réseau',
                lang: 'fr',
                theme_color: '#17382C',
                background_color: '#F6F5EF',
                display: 'standalone',
                orientation: 'portrait',
                scope: '/',
                start_url: '/terrain',
                icons: [
                    { src: '/icons/icon-192.png', sizes: '192x192', type: 'image/png' },
                    { src: '/icons/icon-512.png', sizes: '512x512', type: 'image/png' },
                    { src: '/icons/icon-maskable-512.png', sizes: '512x512', type: 'image/png', purpose: 'maskable' },
                ],
            },
            workbox: {
                inlineWorkboxRuntime: true,
                clientsClaim: true,
                skipWaiting: true,
                cleanupOutdatedCaches: true,
                globPatterns: ['**/*.{js,css,woff2}'],
                // Le moteur vocal doit rester disponible sans réseau.
                maximumFileSizeToCacheInBytes: 12 * 1024 * 1024,
                modifyURLPrefix: { '': '/build/' },
                navigateFallback: null,
                runtimeCaching: [
                    {
                        urlPattern: ({ request, url }) => request.mode === 'navigate' || url.pathname === '/terrain',
                        handler: 'NetworkFirst',
                        options: {
                            cacheName: 'bn-pages',
                            networkTimeoutSeconds: 4,
                            plugins: [
                                {
                                    // Seule la page terrain est gardée : aucune autre page privée n'est stockée.
                                    cacheWillUpdate: async ({ request, response }) =>
                                        new URL(request.url).pathname === '/terrain' && response.status === 200 && !response.redirected && (response.headers.get('content-type') ?? '').includes('text/html')
                                            ? response
                                            : null,
                                    handlerDidError: async () => (await caches.match('/terrain')) || Response.error(),
                                },
                            ],
                        },
                    },
                    {
                        urlPattern: ({ url }) => /\.tar\.gz$/.test(url.pathname),
                        handler: 'CacheFirst',
                        options: {
                            cacheName: 'bn-voix',
                            cacheableResponse: { statuses: [200] },
                        },
                    },
                    {
                        urlPattern: ({ url }) => url.pathname.startsWith('/icons/'),
                        handler: 'CacheFirst',
                        options: { cacheName: 'bn-icones' },
                    },
                ],
            },
        }),
    ],
    build: {
        chunkSizeWarningLimit: 7000,
    },
});
