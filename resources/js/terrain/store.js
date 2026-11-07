// Stockage local du mode terrain : données, visites en attente, brouillon et notes vocales.
const DB_NAME = 'beenote';
const STORE = 'kv';

let opening = null;

function open() {
    if (!opening) {
        opening = new Promise((resolve, reject) => {
            const request = indexedDB.open(DB_NAME, 1);
            request.onupgradeneeded = () => request.result.createObjectStore(STORE);
            request.onsuccess = () => {
                // Le système peut couper la base en arrière-plan : la prochaine écriture la rouvrira.
                request.result.onclose = () => { opening = null; };
                resolve(request.result);
            };
            request.onerror = () => {
                opening = null;
                reject(request.error);
            };
        });
    }

    return opening;
}

async function avecBase(action) {
    try {
        return await action(await open());
    } catch (error) {
        if (error?.name !== 'InvalidStateError') {
            throw error;
        }

        opening = null;

        return action(await open());
    }
}

function run(mode, action) {
    return avecBase((db) => new Promise((resolve, reject) => {
        const tx = db.transaction(STORE, mode);
        const request = action(tx.objectStore(STORE));
        tx.oncomplete = () => resolve(request?.result);
        tx.onerror = () => reject(tx.error);
        tx.onabort = () => reject(tx.error);
    }));
}

export const getItem = (key) => run('readonly', (store) => store.get(key));
export const setItem = (key, value) => run('readwrite', (store) => store.put(value, key));
export const removeItem = (key) => run('readwrite', (store) => store.delete(key));

/**
 * Lit puis réécrit une valeur dans une même transaction : un autre onglet ne peut pas s'intercaler.
 */
export function updateItem(key, changer) {
    return avecBase((db) => new Promise((resolve, reject) => {
        const tx = db.transaction(STORE, 'readwrite');
        const store = tx.objectStore(STORE);
        const lecture = store.get(key);
        let valeur;

        lecture.onsuccess = () => {
            valeur = changer(lecture.result);
            store.put(valeur, key);
        };
        tx.oncomplete = () => resolve(valeur);
        tx.onerror = () => reject(tx.error);
        tx.onabort = () => reject(tx.error);
    }));
}

/**
 * Efface ce que ce compte a laissé sur le téléphone. Les visites mises de côté pour un autre
 * compte, et leurs notes vocales, restent : elles repartiront quand il se reconnectera.
 */
export async function clearAll() {
    const gardees = new Set();
    const cles = await run('readonly', (store) => store.getAllKeys());

    for (const cle of cles.filter((c) => String(c).startsWith('compte:'))) {
        gardees.add(cle);
        const mis = await getItem(cle);
        [...(mis?.file ?? []), mis?.brouillon].filter(Boolean).forEach((v) => gardees.add(`note:${v.client_uuid}`));
    }

    await run('readwrite', (store) => {
        cles.filter((c) => !gardees.has(c)).forEach((c) => store.delete(c));
    });

    if ('caches' in window) {
        await caches.delete('bn-pages');
    }
}

/**
 * Ce qu'une déconnexion ferait perdre : file d'envoi, visites refusées et visite en cours.
 */
export async function aPerdre() {
    const file = (await getItem('queue').catch(() => [])) || [];
    const brouillon = await getItem('brouillon').catch(() => null);
    const brouillonRempli = brouillon && (brouillon.note || Object.keys(brouillon.valeurs ?? {}).some((cle) => !cle.startsWith('_')));

    return file.length + (brouillonRempli ? 1 : 0);
}

const PROPRES_AU_COMPTE = ['donnees', 'queue', 'brouillon', 'visitesDuJour', 'dernierEnvoi'];

/**
 * Sur un téléphone partagé, le nouveau compte ne voit rien du précédent, et rien n'est perdu :
 * ce qui restait à envoyer est mis de côté et revient quand son propriétaire se reconnecte.
 */
export async function changerDeCompte(ancien, nouveau) {
    const file = (await getItem('queue')) ?? [];
    const brouillon = (await getItem('brouillon')) ?? null;

    if (file.length || brouillon) {
        await setItem(`compte:${ancien}`, { file, brouillon });
    }

    await Promise.all(PROPRES_AU_COMPTE.map((cle) => removeItem(cle)));
    if ('caches' in window) {
        await caches.delete('bn-pages');
    }

    const retrouve = await getItem(`compte:${nouveau}`);
    if (retrouve) {
        await setItem('queue', retrouve.file);
        if (retrouve.brouillon) {
            await setItem('brouillon', retrouve.brouillon);
        }
        await removeItem(`compte:${nouveau}`);
    }
}

export async function pendingCount() {
    return ((await getItem('queue').catch(() => [])) || []).filter((item) => !item.refusee && !item.noteRefusee).length;
}
