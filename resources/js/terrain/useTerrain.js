import axios from 'axios';
import { computed, reactive } from 'vue';
import { ACTIONS, ajouterSemaines, jourLocal } from './fields.js';
import { refreshDonnees } from './prepare.js';
import { getItem, removeItem, setItem, updateItem } from './store.js';
import { createVoskEngine, micSupported } from './voice/engine.js';
import { matchPhrase } from './voice/phrases.js';

const state = reactive({
    pret: false,
    vue: 'accueil',
    donnees: null,
    rucherId: null,
    visite: null,
    queue: [],
    enligne: navigator.onLine,
    envoi: 'repos',
    dernierEnvoi: null,
    visitesDuJour: [],
    // Changement de ruche demandé alors qu'une visite contient déjà des notes.
    changement: null,
    erreurStockage: false,

    voix: {
        ecoute: false,
        chargement: false,
        erreur: null,
        statut: null,
        entendu: null,
        echo: null,
        rate: false,
        echecs: 0,
        pause: false,
        dernier: null,
        coupee: false,
    },
});

let engine = null;
let wakeLock = null;
let parole = false;
let historique = [];
let terrainAffiche = false;
let validationEnCours = false;
let nouvelEssai = null;

const VUES_DE_VISITE = ['visite', 'details', 'note', 'recap'];
const VUES = ['accueil', 'tournee', 'datee', 'synchro', 'scanner', 'aide', 'fin', ...VUES_DE_VISITE];
const VUES_ECRAN_ALLUME = ['tournee', 'aide', ...VUES_DE_VISITE];

function lireReglage(cle, defaut = '1') {
    try {
        return (localStorage.getItem(cle) ?? defaut) !== '0';
    } catch {
        return defaut !== '0';
    }
}

function ecrireLocal(cle, valeur) {
    try {
        localStorage.setItem(cle, valeur);
    } catch {
        // Le réglage reste valable le temps de la session.
    }
}

function vibrer(motif) {
    if (lireReglage('bn-vibre')) {
        navigator.vibrate?.(motif);
    }
}

function uuid() {
    if (crypto.randomUUID) {
        return crypto.randomUUID();
    }

    return ([1e7] + -1e3 + -4e3 + -8e3 + -1e11).replace(/[018]/g, (c) =>
        (c ^ (crypto.getRandomValues(new Uint8Array(1))[0] & (15 >> (c / 4)))).toString(16),
    );
}

// Toujours la date de l'appareil : les données locales peuvent dater de plusieurs jours.
const aujourdhui = () => jourLocal();
const brut = (valeur) => JSON.parse(JSON.stringify(valeur));

const rucher = computed(() => state.donnees?.ruchers.find((r) => r.id === state.rucherId) ?? null);
const ruche = computed(() => {
    if (!state.visite) {
        return null;
    }

    for (const r of state.donnees?.ruchers ?? []) {
        const trouvee = r.ruches.find((h) => h.id === state.visite.ruche_id);
        if (trouvee) {
            return trouvee;
        }
    }

    return null;
});

function etatRuche(item) {
    const jour = aujourdhui();

    if (state.visitesDuJour.includes(item.id) || item.derniere_visite?.date === jour) {
        return 'done';
    }

    const dates = [item.prochaine_visite, ...(item.taches ?? []).map((tache) => tache.date_prevue)].filter(Boolean);

    if (dates.some((date) => date < jour)) {
        return 'late';
    }

    return !item.derniere_visite || dates.includes(jour) ? 'todo' : 'next';
}

function aDuContenu(visite) {
    return !!visite && (Object.keys(visite.valeurs).some((cle) => !cle.startsWith('_') && visite.valeurs[cle]) || !!visite.note || visite.taches_faites.length > 0);
}

const ruchesDuRucher = computed(() => (rucher.value?.ruches ?? []).map((item) => ({ ...item, etat: etatRuche(item) })));
const prochaineRuche = computed(() => {
    const encore = ruchesDuRucher.value.filter((h) => h.etat !== 'done' && h.statut !== 'morte');

    return encore.find((h) => h.etat === 'late') ?? encore.find((h) => h.etat === 'todo') ?? encore[0] ?? null;
});
// La ruche qui vient après celle en cours : la visite en cours ne compte plus comme à faire.
const rucheApres = computed(() => {
    const encore = ruchesDuRucher.value.filter((h) => h.etat !== 'done' && h.statut !== 'morte' && h.id !== state.visite?.ruche_id);

    return encore.find((h) => h.etat === 'late') ?? encore.find((h) => h.etat === 'todo') ?? encore[0] ?? null;
});
const enAttente = computed(() => state.queue.filter((item) => !item.refusee && !item.noteRefusee));
const refusees = computed(() => state.queue.filter((item) => item.refusee || item.noteRefusee));
const notes = computed(() => (state.visite ? Object.keys(state.visite.valeurs).filter((k) => !k.startsWith('_') && state.visite.valeurs[k]).length : 0));

async function sauverBrouillon() {
    try {
        if (state.visite) {
            await setItem('brouillon', JSON.parse(JSON.stringify(state.visite)));
        } else {
            await removeItem('brouillon');
        }
    } catch {
        signalerStockage();
    }
}

function signalerStockage() {
    state.erreurStockage = true;
    dire('Le téléphone n\'arrive plus à enregistrer');
}

/**
 * Une visite reprise un autre jour : vide, elle disparaît ; remplie, elle garde sa date,
 * affichée à l'écran. Rien n'est daté en silence.
 */
function actualiserBrouillon() {
    const visite = state.visite;

    if (!visite || visite.apres_coup || visite.date_visite === aujourdhui()) {
        return;
    }

    if (aDuContenu(visite)) {
        visite.apres_coup = true;
    } else {
        state.visite = null;
    }

    sauverBrouillon();
}

function aller(vue, remplacer = false) {
    const memeVue = state.vue === vue;
    state.vue = vue;
    ajusterEcran();
    if (!memeVue && window.history?.pushState) {
        // L'état d'Inertia reste présent : sans lui, le retour du téléphone rechargerait la page.
        window.history[remplacer ? 'replaceState' : 'pushState']({ ...window.history.state, vue }, '', `#${vue}`);
    }
    window.scrollTo?.(0, 0);
}

function vueValide(demandee) {
    // Revenir sur l'écran de note relancerait le micro.
    const vue = demandee === 'note' ? 'visite' : demandee;

    if (!VUES.includes(vue)) {
        return 'accueil';
    }
    if (VUES_DE_VISITE.includes(vue) && !state.visite) {
        return rucher.value ? 'tournee' : 'accueil';
    }

    return vue === 'tournee' && !rucher.value ? 'accueil' : vue;
}

function surRetour(event) {
    if (!terrainAffiche) {
        return;
    }

    // Tout se passe dans la page : Inertia n'a rien à recharger, avec ou sans réseau.
    event.stopImmediatePropagation();
    state.vue = vueValide(event.state?.vue ?? window.location.hash.slice(1));
    ajusterEcran();
}

function signalerAffichage(affiche) {
    terrainAffiche = affiche;
    ajusterEcran();
}

function nouveauBrouillon(id, date = null) {
    state.visite = {
        client_uuid: uuid(),
        ruche_id: id,
        date_visite: date ?? aujourdhui(),
        // Une visite notée après coup n'a pas d'heure : la météo prend alors le milieu de journée.
        heure_debut: date === null ? new Date().toTimeString().slice(0, 5) : null,
        valeurs: {},
        taches_faites: [],
        note: null,
        apres_coup: date !== null,
    };
    historique = [];
    // Rien de la ruche précédente ne reste à l'écran.
    Object.assign(state.voix, { echo: null, dernier: null, entendu: null, rate: false, echecs: 0 });
    sauverBrouillon();
}

function rucherDeLaRuche(id) {
    return state.donnees?.ruchers.find((r) => r.ruches.some((h) => h.id === id))?.id ?? state.rucherId;
}

async function commencer(id, date = null, depuisVoix = false) {
    actualiserBrouillon();

    const enCours = state.visite;

    if (enCours && enCours.ruche_id === id && (date === null || date === enCours.date_visite)) {
        state.rucherId = rucherDeLaRuche(id);
        aller('visite');
        return true;
    }

    // Au toucher, un geste malheureux ne doit rien perdre. À la voix, gants aux mains,
    // nommer une autre ruche enregistre celle en cours.
    if (aDuContenu(enCours)) {
        if (!depuisVoix) {
            state.changement = { id, date, depuis: enCours.ruche_id };
            return false;
        }
        if (!(await enregistrerVisite())) {
            return false;
        }
    }

    state.rucherId = rucherDeLaRuche(id);
    nouveauBrouillon(id, date);
    aller('visite');

    return true;
}

async function confirmerChangement() {
    const demande = state.changement;
    state.changement = null;

    if (demande && (await enregistrerVisite())) {
        await commencer(demande.id, demande.date);
    }
}

function annulerChangement() {
    state.changement = null;
    if (state.visite) {
        state.rucherId = rucherDeLaRuche(state.visite.ruche_id);
        aller('visite');
    }
}

function choisirRucher(id) {
    state.rucherId = id;
    aller('tournee');
}

function noter(action, depuisVoix = false) {
    if (!state.visite) {
        return;
    }

    const valeurs = state.visite.valeurs;
    historique.push(JSON.stringify(valeurs));

    if (action.type === 'choix') {
        valeurs[action.champ] = action.valeur;
        state.voix.dernier = `${action.champ}:${action.valeur}`;
    } else if (action.type === 'action') {
        valeurs[action.champ] = true;
        state.voix.dernier = `${action.champ}:true`;
    } else if (action.type === 'prochaine') {
        valeurs.prochaine_visite_prevue = ajouterSemaines(state.visite.date_visite, action.semaines);
        valeurs._semaines = action.semaines;
        state.voix.dernier = `prochaine:${action.semaines}`;
    }

    sauverBrouillon();
    confirmer(`${ruche.value?.nom ?? ''}, ${String(action.texte).toLowerCase()}`, depuisVoix);

    // Le jaune dit « je viens de noter » : il cède la place au vert de ce qui est noté.
    const noteA = state.voix.dernier;
    setTimeout(() => {
        if (state.voix.dernier === noteA) {
            state.voix.dernier = null;
        }
    }, 2000);
}

function effacerGroupe(champs) {
    if (!state.visite) {
        return;
    }

    historique.push(JSON.stringify(state.visite.valeurs));
    champs.forEach((champ) => delete state.visite.valeurs[champ]);
    state.voix.dernier = null;
    sauverBrouillon();
}

function annulerDernier() {
    const precedent = historique.pop();
    if (precedent && state.visite) {
        state.visite.valeurs = JSON.parse(precedent);
        state.voix.dernier = null;
        sauverBrouillon();
        confirmer('Annulé', true);
    }
}

function confirmer(texte, aVoixHaute) {
    state.voix.echo = texte;
    state.voix.rate = false;
    vibrer(80);

    if (aVoixHaute) {
        dire(texte);
    }
}

function dire(texte) {
    if (!('speechSynthesis' in window) || !lireReglage('bn-echo')) {
        return;
    }

    const message = new SpeechSynthesisUtterance(texte);
    message.lang = 'fr-FR';
    message.rate = 1.1;
    // L'appli ne doit pas se comprendre elle-même.
    parole = true;
    message.onend = () => setTimeout(() => { parole = false; }, 300);
    message.onerror = () => { parole = false; };
    window.speechSynthesis.cancel();
    window.speechSynthesis.speak(message);
}

function surPhrase(texte) {
    if (parole) {
        return;
    }

    const action = matchPhrase(texte);

    // Pendant une note, on parle librement : seule « fin de note » garde un sens.
    if (state.vue === 'note') {
        if (action?.type === 'fin_note') {
            executer(action);
        }
        return;
    }

    state.voix.entendu = texte;

    if (state.voix.pause) {
        if (action?.type === 'reprends') {
            state.voix.pause = false;
            confirmer("J'écoute", true);
        }
        return;
    }

    if (!action) {
        state.voix.rate = true;
        state.voix.echo = null;
        state.voix.echecs += 1;
        vibrer([80, 80, 80]);
        return;
    }

    state.voix.echecs = 0;
    executer(action);
}

function executer(action) {
    switch (action.type) {
        case 'choix':
        case 'action':
        case 'prochaine':
            if (state.vue === 'visite' || state.vue === 'details') {
                noter(action, true);
            } else {
                dire('Commencez une ruche');
            }
            break;
        case 'ruche': {
            const cible = rucher.value?.ruches.find((h) => h.numero === action.numero);
            if (cible) {
                commencer(cible.id, null, true).then((fait) => fait && confirmer(cible.nom, true));
            } else {
                dire('Cette ruche n\'existe pas ici');
            }
            break;
        }
        case 'ruche_suivante':
            if (rucheApres.value) {
                const cible = rucheApres.value;
                commencer(cible.id, null, true).then((fait) => fait && confirmer(cible.nom, true));
            } else {
                dire('Plus de ruche à visiter');
            }
            break;
        case 'ruche_terminee':
            if (state.visite) {
                aller('recap');
                dire(`${notes.value} notés, c'est bon ?`);
            }
            break;
        case 'valider':
            if (state.vue === 'recap') {
                valider();
            }
            break;
        case 'fin_rucher':
            terminerRucher();
            break;
        case 'pause':
            state.voix.pause = true;
            confirmer('En pause, dites reprends', true);
            break;
        case 'annule':
            if (state.vue === 'recap') {
                aller('visite');
            } else if (state.vue === 'visite' || state.vue === 'details') {
                annulerDernier();
            }
            break;
        case 'note':
            if (state.visite) {
                aller('note');
            }
            break;
        case 'fin_note':
            window.dispatchEvent(new CustomEvent('bn-fin-note'));
            break;
        case 'aide':
            aller('aide');
            dire('Voici les mots possibles');
            break;
        case 'compris':
            if (state.vue === 'aide') {
                revenir();
            }
            break;
        default:
    }
}

function revenir() {
    aller(state.visite ? 'visite' : rucher.value ? 'tournee' : 'accueil');
}

async function ecouter(modelUrl) {
    state.voix.erreur = null;
    state.voix.echecs = 0;

    if (!micSupported()) {
        state.voix.erreur = 'indisponible';
        return false;
    }

    state.voix.chargement = true;

    try {
        engine = engine ?? createVoskEngine(modelUrl);
        await engine.start(surPhrase, surMicroCoupe);
        state.voix.ecoute = true;
        state.voix.pause = false;
        state.voix.coupee = false;
        ecrireLocal('bn-voix-prete', '1');

        return true;
    } catch (error) {
        state.voix.erreur = error?.name === 'NotAllowedError' ? 'micro' : 'moteur';
        state.voix.ecoute = false;

        return false;
    } finally {
        state.voix.chargement = false;
    }
}

function arreterEcoute() {
    engine?.stop();
    state.voix.ecoute = false;
    state.voix.pause = false;
    state.voix.coupee = false;
}

// Le système a repris le micro : appel, verrouillage, autre application.
function surMicroCoupe() {
    engine?.stop();
    state.voix.ecoute = false;
    state.voix.pause = false;
    state.voix.coupee = true;
    vibrer([80, 80, 80]);
}

function ajusterEcran() {
    if (terrainAffiche && VUES_ECRAN_ALLUME.includes(state.vue) && lireReglage('bn-ecran')) {
        garderEcran();
    } else {
        relacherEcran();
    }
}

async function garderEcran() {
    if (wakeLock && !wakeLock.released) {
        return;
    }

    try {
        wakeLock = (await navigator.wakeLock?.request('screen')) ?? null;
    } catch {
        wakeLock = null;
    }
}

function relacherEcran() {
    wakeLock?.release?.();
    wakeLock = null;
}

async function terminerRucher() {
    // Finir le rucher vaut pour la ruche en cours : ce qui est noté est gardé.
    if (aDuContenu(state.visite)) {
        if (!(await enregistrerVisite())) {
            return;
        }
    } else if (state.visite) {
        state.visite = null;
        await sauverBrouillon();
    }

    aller('fin');
    dire('Rucher terminé');
    envoyer();
}

async function ajouterNote(blob, duree) {
    // Un enregistrement vide serait refusé par le serveur.
    if (!state.visite || !blob.size) {
        return;
    }

    await setItem(`note:${state.visite.client_uuid}`, { blob, duree, type: blob.type });
    state.visite.note = { duree };
    await sauverBrouillon();
}

function basculerTache(id) {
    if (!state.visite) {
        return;
    }

    const liste = state.visite.taches_faites;
    const position = liste.indexOf(id);
    if (position === -1) {
        liste.push(id);
    } else {
        liste.splice(position, 1);
    }
    sauverBrouillon();
}

/**
 * Passe la visite en cours dans la file d'envoi. Le brouillon n'est effacé qu'une fois la file
 * écrite. La file est relue dans la même transaction : deux onglets ne s'écrasent pas.
 */
async function enregistrerVisite() {
    if (!state.visite || validationEnCours) {
        return false;
    }

    validationEnCours = true;
    state.erreurStockage = false;

    try {
        const visite = state.visite;
        const { _semaines, ...valeurs } = visite.valeurs;
        const element = brut({
            client_uuid: visite.client_uuid,
            ruche_id: visite.ruche_id,
            date_visite: visite.date_visite,
            heure_debut: visite.heure_debut,
            ...valeurs,
            taches_faites: visite.taches_faites,
            note: visite.note,
            nom: ruche.value?.nom,
            creee_le: new Date().toISOString(),
        });

        state.queue = await updateItem('queue', (file) => [...(file ?? []).filter((item) => item.client_uuid !== element.client_uuid), element]);

        if (visite.date_visite === aujourdhui()) {
            const jour = aujourdhui();
            const duJour = await updateItem('visitesDuJour', (valeur) => ({
                date: jour,
                ids: [...new Set([...(valeur?.date === jour ? valeur.ids : []), visite.ruche_id])],
            }));
            state.visitesDuJour = duJour.ids;
        }

        state.visite = null;
        await sauverBrouillon();
        envoyer();

        return true;
    } catch {
        // Le brouillon reste en place : rien n'est perdu, et l'écran le signale.
        signalerStockage();

        return false;
    } finally {
        validationEnCours = false;
    }
}

async function valider() {
    const apresCoup = state.visite?.apres_coup;

    if (!(await enregistrerVisite())) {
        return;
    }

    if (apresCoup) {
        aller('accueil');
        return;
    }

    // Le bouton annonce la ruche suivante : au toucher comme à la voix, on y passe.
    const suivante = prochaineRuche.value;
    if (!suivante) {
        terminerRucher();
    } else {
        nouveauBrouillon(suivante.id);
        aller('visite');
        if (state.voix.ecoute) {
            dire(suivante.nom);
        }
    }
}

async function quitterVisite() {
    aller(rucher.value ? 'tournee' : 'accueil');
}

async function abandonnerVisite() {
    if (state.visite) {
        await removeItem(`note:${state.visite.client_uuid}`);
    }
    state.visite = null;
    await sauverBrouillon();
    aller(rucher.value ? 'tournee' : 'accueil');
}

let envoiEnCours = false;

const majFile = async (changer) => {
    state.queue = await updateItem('queue', (file) => changer(file ?? []));
};
const majElement = (uuidVisite, champs) => majFile((file) => file.map((item) => (item.client_uuid === uuidVisite ? { ...item, ...champs } : item)));
const retirerElement = (uuidVisite) => majFile((file) => file.filter((item) => item.client_uuid !== uuidVisite));

// La session et le jeton expirent pendant une longue absence, pas la connexion mémorisée :
// une lecture renouvelle le jeton et l'envoi repart sans rien demander.
async function poster(url, corps, options) {
    try {
        return await axios.post(url, corps, options);
    } catch (error) {
        if (error?.response?.status !== 419) {
            throw error;
        }

        await axios.get('/terrain/donnees', { timeout: 10000 });

        return axios.post(url, corps, options);
    }
}

async function envoyer() {
    if (envoiEnCours || !navigator.onLine) {
        return;
    }

    envoiEnCours = true;
    clearTimeout(nouvelEssai);

    // Un autre onglet a pu ajouter des visites à la file.
    state.queue = (await getItem('queue').catch(() => null)) ?? state.queue;

    const restantes = () => enAttente.value.filter((item) => !item.recue).length;
    const avant = restantes();

    if (enAttente.value.length === 0) {
        envoiEnCours = false;
        return;
    }

    state.envoi = 'encours';
    let noteEnEchec = false;

    try {
        const lot = enAttente.value.filter((item) => !item.recue).slice(0, 50);

        if (lot.length) {
            const { data } = await poster('/terrain/sync', {
                visites: lot.map(({ note, nom, creee_le, refusee, recue, noteRefusee, ...visite }) => visite),
            }, { timeout: 30000 });

            for (const resultat of data.resultats) {
                await majElement(resultat.client_uuid, resultat.statut === 'refusee' ? { refusee: true } : { recue: true });
            }
        }

        // La visite est reçue : l'échec d'une note ne doit jamais la retenir ni bloquer les suivantes.
        for (const item of enAttente.value.filter((element) => element.recue)) {
            try {
                await envoyerNote(item);
                await retirerElement(item.client_uuid);
            } catch (error) {
                const code = error?.response?.status;

                if (code === 401 || code === 419) {
                    throw error;
                }
                if (code >= 400 && code < 500 && code !== 429) {
                    await majElement(item.client_uuid, { noteRefusee: true });
                } else {
                    noteEnEchec = true;
                }
            }
        }

        state.dernierEnvoi = new Date().toISOString();
        await setItem('dernierEnvoi', state.dernierEnvoi);
        state.envoi = noteEnEchec ? 'echec' : refusees.value.length ? 'partiel' : 'reussi';
        refreshDonnees().then((donnees) => { state.donnees = donnees; }).catch(() => {});
    } catch (error) {
        const code = error?.response?.status;
        state.envoi = code === 401 || code === 419 ? 'session' : 'echec';
    } finally {
        envoiEnCours = false;
    }

    if (state.envoi === 'echec') {
        nouvelEssai = setTimeout(envoyer, 60000);
    } else if (state.envoi !== 'session' && restantes() > 0 && restantes() < avant) {
        envoyer();
    }
}

async function envoyerNote(item) {
    const note = await getItem(`note:${item.client_uuid}`);
    if (!note) {
        return;
    }

    const formulaire = new FormData();
    formulaire.append('note', note.blob, 'note');
    if (note.duree) {
        formulaire.append('duree', String(note.duree));
    }

    await poster(`/terrain/visites/${item.client_uuid}/note`, formulaire, { timeout: 120000 });
    await removeItem(`note:${item.client_uuid}`);
}

async function supprimerRefusee(uuidVisite) {
    await retirerElement(uuidVisite);
    await removeItem(`note:${uuidVisite}`);
}

// Après restauration d'une ruche, le serveur accepte de nouveau la visite.
async function reessayerRefusee(uuidVisite) {
    await majElement(uuidVisite, { refusee: false, noteRefusee: false });
    await envoyer();
}

async function initialiser() {
    if (state.pret) {
        return;
    }

    state.donnees = (await getItem('donnees').catch(() => null)) ?? null;

    // Un téléphone resté sans réseau ne garde pas indéfiniment les données du compte.
    if (state.donnees?.aujourdhui && Date.now() - new Date(state.donnees.aujourdhui).getTime() > 30 * 864e5) {
        state.donnees = null;
        await removeItem('donnees').catch(() => {});
    }

    state.queue = (await getItem('queue').catch(() => [])) ?? [];
    state.visite = (await getItem('brouillon').catch(() => null)) ?? null;
    actualiserBrouillon();
    state.dernierEnvoi = (await getItem('dernierEnvoi').catch(() => null)) ?? null;

    const duJour = await getItem('visitesDuJour').catch(() => null);
    state.visitesDuJour = duJour && duJour.date === aujourdhui() ? duJour.ids : [];

    // Demande au navigateur de ne pas effacer les visites en attente quand la place manque.
    navigator.storage?.persist?.().catch?.(() => {});

    // Les données locales s'affichent tout de suite : un réseau muet ne bloque jamais le rucher.
    if (navigator.onLine) {
        const rafraichissement = refreshDonnees()
            .then((donnees) => { state.donnees = donnees; })
            .catch(() => {});

        if (!state.donnees) {
            await rafraichissement;
        }
    }

    if (state.visite) {
        state.rucherId = rucherDeLaRuche(state.visite.ruche_id);
    }

    const parametres = new URLSearchParams(window.location.search);
    const rucherDemande = Number(parametres.get('rucher'));
    const rucheDemandee = Number(parametres.get('ruche'));

    const rucherConnu = (state.donnees?.ruchers ?? []).some((item) => item.id === rucherDemande);

    if (rucherConnu) {
        state.rucherId = rucherDemande;
    }

    window.addEventListener('popstate', surRetour, true);

    const scan = window.location.pathname.match(/^\/scan\/([A-Za-z0-9]{32})$/);
    if (scan) {
        ouvrirParJeton(scan[1]);
    } else if (rucheDemandee) {
        ouvrirRuche(rucheDemandee);
    } else if (rucherConnu) {
        state.vue = 'tournee';
    } else if (window.location.hash === '#scanner') {
        state.vue = 'scanner';
    } else if (window.location.hash === '#synchro') {
        state.vue = 'synchro';
    }

    // La demande est servie : la garder dans l'adresse la rejouerait à chaque rechargement.
    window.history.replaceState({ ...window.history.state, vue: state.vue }, '', `/terrain#${state.vue}`);

    window.addEventListener('online', () => {
        state.enligne = true;
        envoyer();
    });
    window.addEventListener('offline', () => {
        state.enligne = false;
    });
    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible') {
            ajusterEcran();
        }
    });

    state.pret = true;
    envoyer();
}

function ouvrirRuche(id) {
    for (const item of state.donnees?.ruchers ?? []) {
        if (item.ruches.some((h) => h.id === id)) {
            state.rucherId = item.id;
            commencer(id);
            return true;
        }
    }

    return false;
}

function ouvrirParJeton(jeton) {
    for (const item of state.donnees?.ruchers ?? []) {
        const trouvee = item.ruches.find((h) => h.qr_token === jeton);
        if (trouvee) {
            state.rucherId = item.id;
            commencer(trouvee.id);
            return true;
        }
    }

    return false;
}

export function useTerrain() {
    return {
        state,
        rucher,
        ruche,
        ruchesDuRucher,
        prochaineRuche,
        rucheApres,
        enAttente,
        refusees,
        ACTIONS,
        aller,
        choisirRucher,
        commencer,
        noter,
        effacerGroupe,
        ecouter,
        arreterEcoute,
        valider,
        quitterVisite,
        abandonnerVisite,
        terminerRucher,
        revenir,
        ajouterNote,
        basculerTache,
        envoyer,
        supprimerRefusee,
        reessayerRefusee,
        confirmerChangement,
        annulerChangement,
        signalerAffichage,
        initialiser,
        ouvrirParJeton,
    };
}
