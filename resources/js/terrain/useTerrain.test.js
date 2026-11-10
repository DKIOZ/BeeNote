// @vitest-environment jsdom
import 'fake-indexeddb/auto';
import { IDBFactory } from 'fake-indexeddb';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';

const axiosMock = vi.hoisted(() => ({ get: vi.fn(), post: vi.fn() }));
const moteur = vi.hoisted(() => ({ phrase: null, coupure: null }));

vi.mock('axios', () => ({ default: axiosMock }));
vi.mock('./voice/engine.js', () => ({
    micSupported: () => true,
    createVoskEngine: () => ({
        start: async (surPhrase, surCoupure) => {
            moteur.phrase = surPhrase;
            moteur.coupure = surCoupure;
        },
        stop: () => {},
    }),
}));

const jour = (d) => `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
const AUJOURDHUI = jour(new Date());
const AVANT_HIER = jour(new Date(Date.now() - 2 * 864e5));

const ruche = (id, numero, extra = {}) => ({
    id,
    numero,
    nom: `Ruche ${numero}`,
    statut: 'active',
    qr_token: null,
    etat: 'todo',
    // Par défaut, la ruche est à visiter aujourd'hui.
    prochaine_visite: AUJOURDHUI,
    derniere_visite: { date: AVANT_HIER, resume: '' },
    taches: [],
    ...extra,
});

const DONNEES = {
    aujourdhui: AVANT_HIER,
    ruchers: [{ id: 1, nom: 'Le verger', ruches: [ruche(11, 1), ruche(12, 2), ruche(13, 3)], a_visiter: 3, taches: [] }],
};

const attendre = () => new Promise((resolve) => setTimeout(resolve, 20));
const reponses = (visites) => ({ data: { resultats: visites.map((v) => ({ client_uuid: v.client_uuid, statut: 'recue' })) } });

async function charger({ url = '/terrain', local = DONNEES, enLigne = true, reseau = DONNEES } = {}) {
    vi.resetModules();
    globalThis.indexedDB = globalThis.indexedDB ?? new IDBFactory();
    window.history.replaceState(null, '', url);
    Object.defineProperty(navigator, 'onLine', { configurable: true, get: () => enLigne });

    const store = await import('./store.js');
    if (local) {
        await store.setItem('donnees', JSON.parse(JSON.stringify(local)));
    }

    axiosMock.get.mockImplementation(reseau === 'muet' ? () => new Promise(() => {}) : async () => ({ data: reseau }));

    const { useTerrain } = await import('./useTerrain.js');
    const terrain = useTerrain();
    await terrain.initialiser();

    return { terrain, store };
}

beforeEach(() => {
    globalThis.indexedDB = new IDBFactory();
    localStorage.clear();
    axiosMock.get.mockReset();
    axiosMock.post.mockReset();
    axiosMock.post.mockImplementation(async (url, corps) => (url === '/terrain/sync' ? reponses(corps.visites) : { data: {} }));
    window.speechSynthesis = { cancel: vi.fn(), speak: vi.fn() };
    window.SpeechSynthesisUtterance = class {};
    window.scrollTo = () => {};
    // L'écho vocal est coupé : l'appli ignore ce qu'elle entend pendant qu'elle parle.
    localStorage.setItem('bn-echo', '0');
    vi.stubGlobal('FormData', class { append() {} });
});

afterEach(() => vi.useRealTimers());

const CALME = { type: 'choix', champ: 'humeur_colonie', valeur: 'calme', texte: 'Calme' };

describe('date de visite', () => {
    it("prend la date de l'appareil, pas celle des données locales", async () => {
        const { terrain } = await charger({ enLigne: false });

        await terrain.commencer(11);

        expect(terrain.state.visite.date_visite).toBe(AUJOURDHUI);
    });

    it('garde la ruche faite après un rechargement, hors ligne', async () => {
        const { terrain } = await charger({ enLigne: false });
        await terrain.commencer(11);
        terrain.noter(CALME);
        await terrain.valider();

        const relance = await charger({ enLigne: false });
        relance.terrain.choisirRucher(1);

        expect(relance.terrain.ruchesDuRucher.value.find((r) => r.id === 11).etat).toBe('done');
    });

    it('ne donne pas d\'heure à une visite notée après coup', async () => {
        const { terrain } = await charger();
        await terrain.commencer(11, AVANT_HIER);

        expect(terrain.state.visite.heure_debut).toBeNull();
    });
});

describe('envoi', () => {
    it('une note refusée ne retient ni la visite ni les suivantes', async () => {
        const { terrain, store } = await charger();
        axiosMock.post.mockImplementation(async (url, corps) => {
            if (url === '/terrain/sync') {
                return reponses(corps.visites);
            }
            throw Object.assign(new Error('refus'), { response: { status: 422 } });
        });

        for (const id of [11, 12]) {
            await terrain.commencer(id);
            terrain.noter(CALME);
            await terrain.ajouterNote(new Blob(['son'], { type: 'audio/mp4' }), 3);
            await terrain.valider();
            await attendre();
        }

        expect(terrain.enAttente.value).toHaveLength(0);
        expect(terrain.refusees.value.every((item) => item.noteRefusee && item.recue)).toBe(true);
        // Le son reste sur le téléphone tant que la personne n'a pas décidé.
        const uuid = terrain.refusees.value[0].client_uuid;
        expect(await store.getItem(`note:${uuid}`)).toBeTruthy();
    });

    it("n'envoie pas deux fois une visite déjà reçue quand seule la note échoue", async () => {
        const { terrain } = await charger();
        axiosMock.post.mockImplementation(async (url, corps) => {
            if (url === '/terrain/sync') {
                return reponses(corps.visites);
            }
            throw Object.assign(new Error('coupure'), {});
        });

        await terrain.commencer(11);
        await terrain.ajouterNote(new Blob(['son'], { type: 'audio/webm' }), 3);
        await terrain.valider();
        await attendre();
        await terrain.envoyer();
        await attendre();

        const envois = axiosMock.post.mock.calls.filter(([url]) => url === '/terrain/sync');
        expect(envois).toHaveLength(1);
        expect(terrain.state.envoi).toBe('echec');
        expect(terrain.enAttente.value).toHaveLength(1);
    });

    it('une visite refusée peut être renvoyée après restauration de la ruche', async () => {
        const { terrain } = await charger();
        axiosMock.post.mockResolvedValueOnce({ data: { resultats: [] } });
        axiosMock.post.mockImplementationOnce(async (url, corps) => ({ data: { resultats: corps.visites.map((v) => ({ client_uuid: v.client_uuid, statut: 'refusee' })) } }));

        await terrain.commencer(11);
        terrain.noter(CALME);
        await terrain.valider();
        await attendre();
        await terrain.envoyer();
        await attendre();

        const refus = terrain.refusees.value[0];
        expect(refus.refusee).toBe(true);

        await terrain.reessayerRefusee(refus.client_uuid);
        await attendre();

        expect(terrain.state.queue).toHaveLength(0);
    });

    it('relie les tâches cochées à leur visite', async () => {
        const { terrain } = await charger();
        await terrain.commencer(11);
        terrain.basculerTache(5);
        await terrain.valider();
        await attendre();

        const [, corps] = axiosMock.post.mock.calls.find(([url]) => url === '/terrain/sync');
        expect(corps.visites[0].taches_faites).toEqual([5]);
    });

    it('deux onglets hors ligne gardent chacun leur visite', async () => {
        const premier = await charger({ enLigne: false });
        await premier.terrain.commencer(11);
        premier.terrain.noter(CALME);

        // Le second onglet écrit dans la même base pendant que le premier a une file périmée.
        await premier.store.updateItem('queue', (file) => [...(file ?? []), { client_uuid: 'autre-onglet', ruche_id: 13 }]);
        await premier.terrain.valider();

        const file = await premier.store.getItem('queue');
        expect(file.map((item) => item.ruche_id).sort()).toEqual([11, 13]);
    });
});

describe('changement de ruche', () => {
    it('au toucher, demande avant de quitter une visite commencée', async () => {
        const { terrain } = await charger();
        await terrain.commencer(11);
        terrain.noter(CALME);

        await terrain.commencer(12);

        expect(terrain.state.changement).toMatchObject({ id: 12, depuis: 11 });
        expect(terrain.state.visite.ruche_id).toBe(11);
        expect(terrain.state.visite.valeurs.humeur_colonie).toBe('calme');
    });

    it('confirmer passe par le récapitulatif, puis ouvre la ruche demandée', async () => {
        const { terrain } = await charger({ enLigne: false });
        await terrain.commencer(11);
        terrain.noter(CALME);
        await terrain.commencer(12);

        await terrain.confirmerChangement();
        expect(terrain.state.vue).toBe('recap');
        expect(terrain.state.queue).toHaveLength(0);

        await terrain.valider();

        expect(terrain.state.visite.ruche_id).toBe(12);
        expect(terrain.state.queue.map((item) => item.ruche_id)).toEqual([11]);
    });

    it('à la voix, nommer une autre ruche passe par le récapitulatif de celle en cours', async () => {
        const { terrain } = await charger({ enLigne: false });
        await terrain.ecouter('modele');
        terrain.choisirRucher(1);
        await terrain.commencer(11);
        moteur.phrase('calme');
        moteur.phrase('ruche deux');
        await attendre();

        expect(terrain.state.vue).toBe('recap');
        moteur.phrase("c'est bon");
        await attendre();

        expect(terrain.state.visite.ruche_id).toBe(12);
        expect(terrain.state.queue[0]).toMatchObject({ ruche_id: 11, humeur_colonie: 'calme' });
    });

    it('« ruche suivante » suit les numéros, même si une ruche plus loin est en retard', async () => {
        const enRetard = { ruchers: [{ id: 1, nom: 'A', ruches: [ruche(11, 1), ruche(12, 2), ruche(13, 3, { prochaine_visite: AVANT_HIER })], taches: [] }] };
        const { terrain } = await charger({ local: enRetard, reseau: enRetard, enLigne: false });
        terrain.choisirRucher(1);
        await terrain.commencer(11);

        expect(terrain.rucheApres.value.id).toBe(12);
    });

    it('« ruche suivante » passe bien à une autre ruche', async () => {
        const { terrain } = await charger({ enLigne: false });
        await terrain.ecouter('modele');
        terrain.choisirRucher(1);
        await terrain.commencer(11);
        moteur.phrase('ruche suivante');
        await attendre();

        expect(terrain.state.visite.ruche_id).toBe(12);
    });

    it('une visite vide est simplement remplacée', async () => {
        const { terrain } = await charger();
        await terrain.commencer(11);
        await terrain.commencer(12);

        expect(terrain.state.changement).toBeNull();
        expect(terrain.state.visite.ruche_id).toBe(12);
        expect(terrain.state.queue).toHaveLength(0);
    });
});

describe('reprise', () => {
    it("retire la ruche demandée de l'adresse après usage", async () => {
        const { terrain } = await charger({ url: '/terrain?ruche=11' });
        await attendre();
        await terrain.valider();

        expect(window.location.search).toBe('');
    });

    it('reprend le brouillon en cours après rechargement', async () => {
        const { terrain } = await charger({ url: '/terrain?ruche=11', enLigne: false });
        await attendre();
        await terrain.valider();
        await terrain.commencer(13);
        terrain.noter(CALME);
        await attendre();

        const relance = await charger({ url: window.location.pathname + window.location.search, enLigne: false });

        expect(relance.terrain.state.visite.ruche_id).toBe(13);
    });

    it('un réseau muet ne bloque pas les données locales', async () => {
        const { terrain } = await charger({ reseau: 'muet' });

        expect(terrain.state.pret).toBe(true);
        expect(terrain.state.donnees.ruchers).toHaveLength(1);
    });
});

describe('contre-recette', () => {
    it('jette un brouillon vide d\'un autre jour, et date du jour la nouvelle visite', async () => {
        const { store } = await charger({ enLigne: false });
        await store.setItem('brouillon', { client_uuid: 'a', ruche_id: 12, date_visite: AVANT_HIER, valeurs: {}, taches_faites: [], note: null, apres_coup: false });

        const { terrain } = await charger({ enLigne: false });
        expect(terrain.state.visite).toBeNull();

        await terrain.commencer(12);
        expect(terrain.state.visite.date_visite).toBe(AUJOURDHUI);
    });

    it('garde la date d\'un brouillon rempli un autre jour, et la rend visible', async () => {
        const { store } = await charger({ enLigne: false });
        await store.setItem('brouillon', { client_uuid: 'a', ruche_id: 12, date_visite: AVANT_HIER, valeurs: { humeur_colonie: 'calme' }, taches_faites: [], note: null, apres_coup: false });

        const { terrain } = await charger({ enLigne: false });

        expect(terrain.state.visite.date_visite).toBe(AVANT_HIER);
        expect(terrain.state.visite.apres_coup).toBe(true);
    });

    it('rejoue l\'envoi après un jeton expiré, sans demander de se reconnecter', async () => {
        const { terrain } = await charger();
        let essais = 0;
        axiosMock.post.mockImplementation(async (url, corps) => {
            essais += 1;
            if (essais === 1) {
                throw Object.assign(new Error('jeton'), { response: { status: 419 } });
            }
            return reponses(corps.visites);
        });

        await terrain.commencer(11);
        terrain.noter(CALME);
        await terrain.valider();
        await attendre();

        expect(terrain.state.queue).toHaveLength(0);
        expect(terrain.state.envoi).not.toBe('session');
    });

    it('une session terminée ne retire rien du téléphone', async () => {
        const { terrain, store } = await charger();
        axiosMock.get.mockRejectedValue(Object.assign(new Error('session'), { response: { status: 401 } }));
        const { refreshDonnees } = await import('./prepare.js');

        await expect(refreshDonnees()).rejects.toThrow();

        expect(await store.getItem('donnees')).toBeTruthy();
        expect(terrain.state.donnees).toBeTruthy();
    });

    it('un autre compte ne voit rien, et le premier retrouve ses visites', async () => {
        const { terrain, store } = await charger({ enLigne: false });
        await terrain.commencer(11);
        terrain.noter(CALME);
        await terrain.valider();
        await attendre();
        const file = await store.getItem('queue');
        const visites = file.filter((item) => item.ruche_id === 11);
        expect(visites).toHaveLength(1);

        await store.changerDeCompte(1, 2);
        expect(await store.getItem('queue')).toBeUndefined();
        expect(await store.getItem('brouillon')).toBeUndefined();
        expect(await store.getItem('donnees')).toBeUndefined();

        await store.changerDeCompte(2, 1);
        expect((await store.getItem('queue')).map((item) => item.ruche_id)).toEqual([11]);
    });

    it('le retour ne rouvre jamais l\'écran de note', async () => {
        const { terrain } = await charger({ enLigne: false });
        await terrain.commencer(11);
        terrain.signalerAffichage(true);
        terrain.aller('note');

        window.dispatchEvent(new PopStateEvent('popstate', { state: { vue: 'note' } }));

        expect(terrain.state.vue).toBe('visite');
    });

    it('n\'annonce pas une autre ruche si la visite n\'a pas pu être enregistrée', async () => {
        const { terrain, store } = await charger({ enLigne: false });
        await terrain.commencer(11);
        terrain.noter(CALME);
        await attendre();
        const espion = vi.spyOn(IDBObjectStore.prototype, 'put').mockImplementation(() => { throw new DOMException('plein', 'QuotaExceededError'); });

        const fait = await terrain.commencer(12, null, true);
        expect(fait).toBe('recap');
        await terrain.valider();

        espion.mockRestore();
        expect(terrain.state.visite.ruche_id).toBe(11);
        expect(terrain.state.erreurStockage).toBe(true);
        expect(store).toBeTruthy();
    });

    it('la date locale reste au format AAAA-MM-JJ', async () => {
        const { jourLocal, ajouterSemaines } = await import('./fields.js');

        expect(jourLocal(new Date(2026, 10, 5))).toBe('2026-11-05');
        expect(ajouterSemaines('2026-12-28', 1)).toBe('2027-01-04');
    });
});

describe('panel', () => {
    it('ne laisse rien de la ruche précédente à l\'écran', async () => {
        const { terrain } = await charger({ enLigne: false });
        terrain.choisirRucher(1);
        await terrain.commencer(11);
        terrain.noter(CALME);
        expect(terrain.state.voix.echo).toContain('calme');

        await terrain.valider();

        expect(terrain.state.visite.ruche_id).toBe(12);
        expect(terrain.state.voix.echo).toBeNull();
    });
});

describe('enchaînement', () => {
    it('après « c\'est bon », le toucher ouvre la ruche suivante comme la voix', async () => {
        const { terrain } = await charger({ enLigne: false });
        terrain.choisirRucher(1);
        await terrain.commencer(11);
        terrain.noter(CALME);

        await terrain.valider();

        expect(terrain.state.vue).toBe('visite');
        expect(terrain.state.visite.ruche_id).toBe(12);
    });
});

describe('finir le rucher', () => {
    it('garde la visite en cours, après son récapitulatif', async () => {
        const { terrain } = await charger({ enLigne: false });
        await terrain.commencer(11);
        terrain.noter(CALME);

        await terrain.terminerRucher();
        expect(terrain.state.vue).toBe('recap');

        await terrain.valider();

        expect(terrain.state.visite).toBeNull();
        expect(terrain.state.queue).toHaveLength(1);
        expect(terrain.state.vue).toBe('fin');
    });

    it('ne crée rien pour une visite vide', async () => {
        const { terrain } = await charger({ enLigne: false });
        await terrain.commencer(11);

        await terrain.terminerRucher();

        expect(terrain.state.queue).toHaveLength(0);
    });
});

describe('commandes selon l\'écran', () => {
    it('pendant une note, seule « fin de note » agit', async () => {
        const { terrain } = await charger({ enLigne: false });
        await terrain.ecouter('modele');
        terrain.choisirRucher(1);
        await terrain.commencer(11);
        terrain.aller('note');
        const ecouteur = vi.fn();
        window.addEventListener('bn-fin-note', ecouteur);

        moteur.phrase('calme');
        moteur.phrase('aide');
        moteur.phrase('ruche deux');
        moteur.phrase('pas compris du tout');

        expect(terrain.state.vue).toBe('note');
        expect(terrain.state.visite.ruche_id).toBe(11);
        expect(terrain.state.visite.valeurs.humeur_colonie).toBeUndefined();
        expect(terrain.state.voix.echecs).toBe(0);

        moteur.phrase('fin de note');
        expect(ecouteur).toHaveBeenCalledOnce();
    });

    it('enchaîne deux mots pendant que l\'appli répète le premier', async () => {
        localStorage.setItem('bn-echo', '1');
        // Le téléphone ne signale pas toujours la fin de la phrase lue.
        window.SpeechSynthesisUtterance = class { constructor(text) { this.text = text; } };
        const { terrain } = await charger({ enLigne: false });
        await terrain.ecouter('modele');
        terrain.choisirRucher(1);
        await terrain.commencer(11);

        moteur.phrase('reine vue');
        // Le micro entend l'appli répéter : ce n'est pas un nouveau mot.
        moteur.phrase('reine vue');
        moteur.phrase('calme');

        expect(terrain.state.visite.valeurs.reine_vue).toBe('ok');
        expect(terrain.state.visite.valeurs.humeur_colonie).toBe('calme');
        expect(terrain.state.voix.echecs).toBe(0);
    });

    it('n\'obéit pas à un mot que l\'appli dit elle-même', async () => {
        localStorage.setItem('bn-echo', '1');
        window.SpeechSynthesisUtterance = class { constructor(text) { this.text = text; } };
        const { terrain } = await charger({ enLigne: false });
        await terrain.ecouter('modele');
        terrain.choisirRucher(1);
        await terrain.commencer(11);

        moteur.phrase('pause');
        moteur.phrase('reprends');

        expect(terrain.state.voix.pause).toBe(true);
    });

    it('signale un micro coupé par le téléphone', async () => {
        const { terrain } = await charger({ enLigne: false });
        await terrain.ecouter('modele');

        moteur.coupure();

        expect(terrain.state.voix.ecoute).toBe(false);
        expect(terrain.state.voix.coupee).toBe(true);
    });
});

describe('état des ruches', () => {
    it('une ruche morte ou inactive est hors tournée', async () => {
        const donnees = { ruchers: [{ id: 1, nom: 'A', ruches: [ruche(11, 1, { statut: 'morte' }), ruche(12, 2, { derniere_visite: null })], taches: [] }] };
        const { terrain } = await charger({ local: donnees, reseau: donnees, enLigne: false });
        terrain.choisirRucher(1);

        expect(terrain.ruchesDuRucher.value[0].etat).toBe('hors');
        expect(terrain.prochaineRuche.value.id).toBe(12);
    });

    it('une ruche sans prochaine visite revient à visiter au bout de trois semaines', async () => {
        const vieille = jour(new Date(Date.now() - 30 * 864e5));
        const donnees = { ruchers: [{ id: 1, nom: 'A', ruches: [ruche(11, 1, { derniere_visite: { date: vieille, resume: '' } })], taches: [] }] };
        const { terrain } = await charger({ local: donnees, reseau: donnees, enLigne: false });
        terrain.choisirRucher(1);

        expect(terrain.ruchesDuRucher.value[0].etat).toBe('todo');
    });

    it('une ruche jamais visitée est à faire', async () => {
        const jamais = { ruchers: [{ id: 1, nom: 'A', ruches: [ruche(11, 1, { derniere_visite: null })], taches: [] }] };
        const { terrain } = await charger({ local: jamais, reseau: jamais, enLigne: false });
        terrain.choisirRucher(1);

        expect(terrain.ruchesDuRucher.value[0].etat).toBe('todo');
    });

    it('une date dépassée depuis le dernier chargement passe en retard', async () => {
        const donnees = { ruchers: [{ id: 1, nom: 'A', ruches: [ruche(11, 1, { prochaine_visite: AVANT_HIER })], taches: [] }] };
        const { terrain } = await charger({ local: donnees, enLigne: false });
        terrain.choisirRucher(1);

        expect(terrain.ruchesDuRucher.value[0].etat).toBe('late');
    });
});

describe('panel 2 : tour 1', () => {
    it('retoucher une ruche faite ouvre un complément sans toucher à la visite du matin', async () => {
        const { terrain } = await charger({ enLigne: false });
        terrain.choisirRucher(1);
        await terrain.commencer(11);
        terrain.noter(CALME);
        await terrain.valider();
        expect(terrain.state.queue).toHaveLength(1);

        await terrain.abandonnerVisite();
        await terrain.commencer(11);

        expect(terrain.state.visite.complement).toBe(true);
        expect(terrain.state.visite.deja.resume).toContain('Calme');
        expect(terrain.state.queue).toHaveLength(1);

        // Abandonner le complément n'efface que le complément.
        await terrain.abandonnerVisite();
        expect(terrain.state.queue).toHaveLength(1);
        expect(terrain.ruchesDuRucher.value.find((r) => r.id === 11).etat).toBe('done');
    });

    it('une ruche déjà envoyée aujourd\'hui se complète, annoncé à l\'écran', async () => {
        const visitee = { ruchers: [{ id: 1, nom: 'A', ruches: [ruche(11, 1, { derniere_visite: { date: AUJOURDHUI, resume: '' } }), ruche(12, 2)], taches: [] }] };
        const { terrain } = await charger({ local: visitee, reseau: visitee, enLigne: false });
        terrain.choisirRucher(1);

        await terrain.commencer(11);

        expect(terrain.state.visite.complement).toBe(true);
    });

    it('« c\'est bon » ouvre la ruche annoncée par le bouton', async () => {
        const { terrain } = await charger({ enLigne: false });
        terrain.choisirRucher(1);
        await terrain.commencer(12);
        const annoncee = terrain.rucheApres.value.id;

        await terrain.valider();

        expect(terrain.state.visite.ruche_id).toBe(annoncee);
        expect(annoncee).toBe(13);
    });

    it('une visite encore dans la file compte le lendemain, hors ligne', async () => {
        const { terrain, store } = await charger({ enLigne: false });
        await store.updateItem('queue', () => [{ client_uuid: 'x', ruche_id: 11, date_visite: AVANT_HIER, prochaine_visite_prevue: null }]);
        const relance = await charger({ enLigne: false });
        relance.terrain.choisirRucher(1);

        expect(relance.terrain.ruchesDuRucher.value.find((r) => r.id === 11).etat).toBe('next');
        expect(terrain).toBeTruthy();
    });

    it('la déconnexion garde les visites mises de côté pour un autre compte', async () => {
        const { store } = await charger({ enLigne: false });
        await store.setItem('compte:7', { file: [{ client_uuid: 'abc' }], brouillon: null });
        await store.setItem('note:abc', { duree: 3 });
        await store.setItem('queue', [{ client_uuid: 'moi' }]);

        await store.clearAll();

        expect(await store.getItem('compte:7')).toBeTruthy();
        expect(await store.getItem('note:abc')).toBeTruthy();
        expect(await store.getItem('queue')).toBeUndefined();
    });

    it('attend les données fraîches pour un rucher tout juste créé', async () => {
        const nouveau = { ruchers: [...DONNEES.ruchers, { id: 2, nom: 'Neuf', ruches: [ruche(21, 4)], taches: [] }] };
        const { terrain } = await charger({ url: '/terrain?rucher=2', reseau: nouveau });

        expect(terrain.state.vue).toBe('tournee');
        expect(terrain.state.rucherId).toBe(2);
    });
});

describe('panel 2 : tour 2', () => {
    it('rend les visites mises de côté, même après une déconnexion', async () => {
        const { store } = await charger({ enLigne: false });
        await store.setItem('compte:1', { file: [{ client_uuid: 'gardee', ruche_id: 11 }], brouillon: null });

        const { prepareOffline } = await import('./prepare.js');
        await prepareOffline(1);

        expect((await store.getItem('queue')).map((v) => v.client_uuid)).toEqual(['gardee']);
        expect(await store.getItem('compte:1')).toBeUndefined();
    });
});

describe('fiche de l\'académie pendant la visite', () => {
    it('s\'ouvre depuis le récapitulatif et y revient, même sans réseau', async () => {
        const { terrain } = await charger({ enLigne: false });
        terrain.choisirRucher(1);
        await terrain.commencer(11);
        terrain.aller('recap');

        terrain.ouvrirFiche('essaimage');
        expect(terrain.state.vue).toBe('fiche');
        expect(terrain.state.fiche).toBe('essaimage');

        terrain.fermerFiche();
        expect(terrain.state.vue).toBe('recap');
        expect(terrain.state.visite.ruche_id).toBe(11);
    });

    it('se referme quand on dit « compris »', async () => {
        const { terrain } = await charger({ enLigne: false });
        await terrain.ecouter('modele');
        terrain.choisirRucher(1);
        await terrain.commencer(11);
        terrain.ouvrirFiche('varroa');

        moteur.phrase('compris');

        expect(terrain.state.vue).toBe('visite');
    });
});
