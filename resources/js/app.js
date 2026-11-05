import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp, router } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import { prepareOffline, rafraichirSiAncien } from './terrain/prepare';

createInertiaApp({
    title: (title) => (title ? `${title} · BeeNote` : 'BeeNote'),
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .mount(el);
    },
    progress: {
        color: '#FFB81C',
    },
});

// Sans réseau, seule la page terrain existe : on y renvoie plutôt que d'afficher une erreur.
router.on('before', (event) => {
    const { method, url } = event.detail.visit;

    if (!navigator.onLine && method === 'get' && url.pathname !== '/terrain') {
        window.location.href = '/terrain';
        return false;
    }

    return true;
});

let utilisateur = JSON.parse(document.getElementById('app')?.dataset.page ?? '{}')?.props?.auth?.user?.id ?? null;

// Une connexion ne recharge pas la page : c'est ici que le téléphone se prépare pour le rucher.
router.on('navigate', (event) => {
    const id = event.detail.page.props?.auth?.user?.id ?? null;

    if (id && id !== utilisateur) {
        prepareOffline(id);
    } else if (id) {
        rafraichirSiAncien();
    }

    utilisateur = id;
});

if (import.meta.env.PROD && 'serviceWorker' in navigator) {
    // Sans service worker, l'appli fonctionne simplement en ligne.
    navigator.serviceWorker.register('/sw.js', { scope: '/' }).catch(() => {}).finally(() => prepareOffline(utilisateur));
} else {
    prepareOffline(utilisateur);
}
