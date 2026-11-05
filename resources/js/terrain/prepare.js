import axios from 'axios';
import { changerDeCompte, getItem, setItem } from './store';

let dernierRafraichissement = 0;

export async function refreshDonnees() {
    // Une session terminée ne retire rien : l'apiculteur doit pouvoir travailler au rucher,
    // et se reconnecter au retour du réseau. La purge vient de la déconnexion.
    const { data } = await axios.get('/terrain/donnees', { timeout: 10000 });
    await setItem('donnees', data);

    return data;
}

export async function rafraichirSiAncien(delai = 30000) {
    if (navigator.onLine && Date.now() - dernierRafraichissement > delai) {
        dernierRafraichissement = Date.now();
        await refreshDonnees().catch(() => {});
    }
}

async function garderPageTerrain() {
    if (!('serviceWorker' in navigator) || !(await navigator.serviceWorker.getRegistration())) {
        return;
    }

    await navigator.serviceWorker.ready;

    if (!navigator.serviceWorker.controller) {
        await new Promise((resolve) => {
            navigator.serviceWorker.addEventListener('controllerchange', resolve, { once: true });
            setTimeout(resolve, 30000);
        });
    }

    // Passe par le service worker, qui garde cette page pour l'ouvrir sans réseau.
    await fetch('/terrain', { credentials: 'same-origin', headers: { Accept: 'text/html' } });
}

/**
 * À chaque ouverture avec du réseau, garde à jour ce qu'il faut pour travailler sans réseau.
 * Si un autre compte se connecte, rien de l'ancien compte ne reste sur le téléphone.
 */
export async function prepareOffline(userId) {
    if (!('indexedDB' in window)) {
        return;
    }

    try {
        const proprietaire = await getItem('proprietaire');

        if (!userId) {
            return;
        }

        if (proprietaire && proprietaire !== userId) {
            await changerDeCompte(proprietaire, userId);
        }

        await setItem('proprietaire', userId);

        if (navigator.onLine) {
            dernierRafraichissement = Date.now();
            await refreshDonnees();
            await garderPageTerrain();
        }
    } catch {
        // La préparation sera refaite à la prochaine ouverture.
    }
}
