import axios from 'axios';
import { computed, reactive } from 'vue';
import { ACTIONS, ajouterJours, ajouterSemaines, compterNotes, jourLocal, resumeVisite } from './fields.js';

// Même valeur que RucherOverview::RAPPEL_JOURS côté serveur.
const RAPPEL_JOURS = 21;
import { refreshDonnees } from './prepare.js';
import { getItem, removeItem, setItem, updateItem } from './store.js';

// Champs de la file qui ne sont pas des observations : le reste redevient la visite si on la rouvre.
const META = ['client_uuid', 'ruche_id', 'date_visite', 'heure_debut', 'taches_faites', 'note', 'nom', 'creee_le', 'refusee', 'recue', 'noteRefusee', 'complement', 'deja'];
import { createVoskEngine, micSupported } from './voice/engine.js';
import { matchPhrase, normalize } from './voice/phrases.js';

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
    // Ce qui reprend après le récapitulatif : une autre ruche ou la fin du rucher.
    apres: null,
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

    // Fiche de l'académie ouverte pendant la visite, et l'écran où revenir ensuite.
    fiche: null,
    ficheRetour: null,
});

let engine = null;
let wakeLock = null;
let parole = null;
let historique = [];
let terrainAffiche = false;
let validationEnCours = false;
let nouvelEssai = null;

const VUES_DE_VISITE = ['visite', 'details', 'note', 'recap'];
const VUES = ['accueil', 'tournee', 'datee', 'synchro', 'scanner', 'aide', 'fin', 'fiche', ...VUES_DE_VISITE];
const VUES_ECRAN_ALLUME = ['tournee', 'aide', 'fiche', ...VUES_DE_VISITE];

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

// Même règle que RucherOverview côté serveur.
function etatRuche(item) {
    const jour = aujourdhui();

    if (item.statut && item.statut !== 'active') {
        return 'hors';
    }

    // Une visite encore dans la file d'envoi compte déjà : le téléphone a pu rester sans réseau plusieurs jours.
    const enFile = state.queue.filter((v) => v.ruche_id === item.id && !v.refusee).sort((a, b) => (a.date_visite < b.date_visite ? 1 : -1))[0];
    const derniere = enFile && (!item.derniere_visite || enFile.date_visite >= item.derniere_visite.date) ? enFile.date_visite : item.derniere_visite?.date;
    const prochaine = enFile && enFile.date_visite === derniere ? enFile.prochaine_visite_prevue ?? null : item.prochaine_visite;

    if (state.visitesDuJour.includes(item.id) || derniere === jour) {
        return 'done';
    }

    const dates = [prochaine, ...(item.taches ?? []).map((tache) => tache.date_prevue)].filter(Boolean);

    if (dates.some((date) => date < jour)) {
        return 'late';
    }

    const rappel = derniere && !prochaine ? ajouterJours(derniere, RAPPEL_JOURS) : null;

    return !derniere || dates.includes(jour) || (rappel && rappel <= jour) ? 'todo' : 'next';
}

function aDuContenu(visite) {
    return !!visite && (Object.keys(visite.valeurs).some((cle) => !cle.startsWith('_') && visite.valeurs[cle]) || !!visite.note || visite.taches_faites.length > 0);
}

// Ce qui reste à visiter dans un rucher, d'après le téléphone : la file d'envoi compte.
function aVisiterDans(item) {
    return (item?.ruches ?? []).filter((h) => ['todo', 'late'].includes(etatRuche(h))).length;
}

const ruchesDuRucher = computed(() => (rucher.value?.ruches ?? []).map((item) => ({ ...item, etat: etatRuche(item) })));
// Au rucher, on suit la rangée : la prochaine ruche est le numéro suivant qui reste à faire.
// À visiter : à faire ou en retard, dans l'ordre des numéros. Une ruche « à venir » n'est pas due.
const aVisiter = computed(() => ruchesDuRucher.value.filter((h) => h.etat === 'todo' || h.etat === 'late'));
const prochaineRuche = computed(() => aVisiter.value[0] ?? null);
// La ruche qui vient après celle en cours, dans l'ordre des numéros, en repartant du début.
const rucheApres = computed(() => {
    const courante = ruchesDuRucher.value.find((h) => h.id === state.visite?.ruche_id);
    const encore = aVisiter.value.filter((h) => h.id !== courante?.id);

    return encore.find((h) => courante && h.numero > courante.numero) ?? encore[0] ?? null;
});
const enAttente = computed(() => state.queue.filter((item) => !item.refusee && !item.noteRefusee));
const refusees = computed(() => state.queue.filter((item) => item.refusee || item.noteRefusee));
const notes = computed(() => (state.visite ? compterNotes(state.visite.valeurs) : 0));

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
    if (vue === 'fiche' && !state.fiche) {
        return state.visite ? 'visite' : rucher.value ? 'tournee' : 'accueil';
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

function nomDeRuche(id) {
    return state.donnees?.ruchers.flatMap((r) => r.ruches).find((h) => h.id === id)?.nom ?? 'Ruche';
}

function nouveauBrouillon(id, date = null, complement = null) {
    state.visite = {
        client_uuid: uuid(),
        ruche_id: id,
        // Le nom reste lisible même si la ruche disparaît des données entre-temps.
        nom: nomDeRuche(id),
        // Cette ruche a déjà sa visite du jour : ce qui est noté s'y ajoute.
        complement: complement !== null,
        deja: complement,
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

        // À la voix aussi, la ruche en cours passe par son récapitulatif : ses tâches y sont proposées.
        passerParLeRecap({ type: 'ruche', id, date });
        return 'recap';
    }

    state.rucherId = rucherDeLaRuche(id);

    // Revenir sur une ruche déjà faite aujourd'hui ouvre un complément : la visite du matin reste
    // intacte dans la file ou sur le serveur, et le serveur fusionne les deux en une seule visite.
    if (date === null && ruchesDuRucher.value.find((h) => h.id === id)?.etat === 'done') {
        nouveauBrouillon(id, null, dejaNote(id));
    } else {
        nouveauBrouillon(id, date);
    }

    aller('visite');

    return true;
}

/**
 * Ce qui a déjà été noté aujourd'hui sur cette ruche, pour l'afficher pendant le complément.
 */
function dejaNote(id) {
    const jour = aujourdhui();
    const enFile = state.queue.filter((v) => v.ruche_id === id && v.date_visite === jour && !v.refusee);
    const lignes = enFile.flatMap((v) => resumeVisite(Object.fromEntries(Object.entries(v).filter(([cle]) => !META.includes(cle)))).map((l) => l.value));
    const serveur = state.donnees?.ruchers.flatMap((r) => r.ruches).find((h) => h.id === id)?.derniere_visite;

    if (lignes.length) {
        return { resume: lignes.join(', '), prochaine: enFile.some((v) => v.prochaine_visite_prevue) };
    }

    return serveur?.date === jour ? { resume: serveur.resume, prochaine: false } : { resume: '', prochaine: false };
}

async function confirmerChangement() {
    const demande = state.changement;
    state.changement = null;

    if (demande) {
        passerParLeRecap({ type: 'ruche', id: demande.id, date: demande.date });
    }
}

/**
 * Toute visite remplie passe par son récapitulatif avant d'être enregistrée :
 * c'est là que l'apiculteur vérifie et coche les tâches. Ensuite, on reprend la demande.
 */
function passerParLeRecap(suite) {
    state.apres = suite;
    aller('recap');
    dire(`${notes.value} notés, c'est bon ?`);
}

function corriger() {
    state.apres = null;
    aller('visite');
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

    // Le jaune dit « je viens de noter » : il cède la place au vert de ce qui est noté.
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

// « demande » : lecture voulue par l'apiculteur (une fiche), faite même sans l'écho des mots.
function dire(texte, demande = false) {
    if (!('speechSynthesis' in window) || (!demande && !lireReglage('bn-echo'))) {
        return;
    }

    const message = new SpeechSynthesisUtterance(texte);
    message.lang = 'fr-FR';
    message.rate = 1.1;
    // L'appli ne doit pas se comprendre elle-même : on retient ce qu'elle dit.
    // Le téléphone ne signale pas toujours la fin de la lecture, d'où un délai de secours.
    const enCours = { texte: normalize(texte) };
    parole = enCours;
    const liberer = (delai) => setTimeout(() => { if (parole === enCours) parole = null; }, delai);
    message.onend = () => liberer(300);
    message.onerror = () => liberer(0);
    liberer(1500 + texte.length * 90);
    // Gardée en mémoire, sinon certains navigateurs l'oublient avant la fin.
    enCours.message = message;
    window.speechSynthesis.cancel();
    window.speechSynthesis.speak(message);
}

function surPhrase(texte) {
    // Pendant que l'appli parle, seul l'écho de sa phrase est ignoré : on peut enchaîner.
    const entendu = normalize(texte);
    if (parole && entendu && ` ${parole.texte} `.includes(` ${entendu} `)) {
        return;
    }

    const action = matchPhrase(texte);

    // Pendant une note, on parle librement : seule « fin de note » garde un sens.
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
                commencer(cible.id, null, true).then((fait) => fait === true && confirmer(cible.nom, true));
            } else {
                dire('Cette ruche n\'existe pas ici');
            }
            break;
        }
        case 'ruche_suivante':
            if (rucheApres.value) {
                const cible = rucheApres.value;
                commencer(cible.id, null, true).then((fait) => fait === true && confirmer(cible.nom, true));
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
                corriger();
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
            } else if (state.vue === 'fiche') {
                fermerFiche();
            }
            break;
        default:
    }
}

function revenir() {
    aller(state.visite ? 'visite' : rucher.value ? 'tournee' : 'accueil');
}

function ouvrirFiche(id) {
    state.fiche = id;
    state.ficheRetour = state.vue === 'fiche' ? state.ficheRetour : state.vue;
    aller('fiche');
}

function fermerFiche() {
    window.speechSynthesis?.cancel();
    const retour = vueValide(state.ficheRetour ?? '');
    if (retour === 'fiche') {
        revenir();
    } else {
        aller(retour);
    }
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
    // Finir le rucher vaut pour la ruche en cours : elle passe d'abord par son récapitulatif.
    if (aDuContenu(state.visite)) {
        passerParLeRecap({ type: 'fin' });
        return;
    }

    if (state.visite) {
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
            nom: ruche.value?.nom ?? visite.nom,
            complement: visite.complement ?? false,
            deja: visite.deja ?? null,
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
    const suite = state.apres;
    // Le bouton a annoncé cette ruche : c'est elle qu'on ouvre, pas une autre.
    const annoncee = rucheApres.value;

    if (!(await enregistrerVisite())) {
        return;
    }

    state.apres = null;

    if (suite?.type === 'fin') {
        await terminerRucher();
        return;
    }

    if (suite?.type === 'ruche') {
        const cible = state.donnees?.ruchers.flatMap((r) => r.ruches).find((h) => h.id === suite.id);
        await commencer(suite.id, suite.date);
        confirmer(cible?.nom ?? '', state.voix.ecoute);
        return;
    }

    if (apresCoup) {
        aller('accueil');
        return;
    }

    // Le bouton annonce la ruche suivante : au toucher comme à la voix, on y passe.
    const suivante = annoncee;
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
    state.apres = null;
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

    // Rucher ou ruche tout juste créés : absents des données gardées, on attend les données fraîches.
    const connu = (id, cle) => (state.donnees?.ruchers ?? []).some((item) => (cle === 'rucher' ? item.id === id : item.ruches.some((h) => h.id === id)));
    if (navigator.onLine && ((rucherDemande && !connu(rucherDemande, 'rucher')) || (rucheDemandee && !connu(rucheDemandee, 'ruche')))) {
        await refreshDonnees().then((donnees) => { state.donnees = donnees; }).catch(() => {});
    }

    const rucherConnu = connu(rucherDemande, 'rucher');

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
        aVisiter,
        aVisiterDans,
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
        ouvrirFiche,
        fermerFiche,
        dire,
        ajouterNote,
        basculerTache,
        envoyer,
        supprimerRefusee,
        reessayerRefusee,
        confirmerChangement,
        corriger,
        annulerChangement,
        signalerAffichage,
        initialiser,
        ouvrirParJeton,
    };
}
