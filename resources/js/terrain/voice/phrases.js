import { ACTIONS, DETAILS, FAMILLES, PROCHAINES } from '../fields.js';

const UNITES = ['', 'un', 'deux', 'trois', 'quatre', 'cinq', 'six', 'sept', 'huit', 'neuf', 'dix', 'onze', 'douze', 'treize', 'quatorze', 'quinze', 'seize', 'dix sept', 'dix huit', 'dix neuf'];
const DIZAINES = ['', '', 'vingt', 'trente', 'quarante', 'cinquante', 'soixante'];

// Nombres de 1 à 99, écrits comme on les dit : « vingt et un », « soixante et onze », « quatre vingt dix ».
function nombreEnMots(n) {
    if (n < 20) {
        return UNITES[n];
    }

    const base = n < 80 ? Math.floor(n / 10) * 10 - (n >= 70 ? 10 : 0) : 80;
    const reste = n - base;
    const mot = base === 80 ? 'quatre vingt' : DIZAINES[base / 10];

    if (reste === 0) {
        return mot;
    }
    if (reste === 1 && base !== 80) {
        return `${mot} et un`;
    }
    if (reste === 11 && base === 60) {
        return `${mot} et onze`;
    }

    return `${mot} ${UNITES[reste]}`;
}

export const NOMBRES = Array.from({ length: 99 }, (_, index) => nombreEnMots(index + 1));

export const COMMANDES = [
    ['ruche suivante', { type: 'ruche_suivante' }],
    ['ruche terminée', { type: 'ruche_terminee' }],
    ['fin du rucher', { type: 'fin_rucher' }],
    ['pause', { type: 'pause' }],
    ['reprends', { type: 'reprends' }],
    ['annule', { type: 'annule' }],
    ['corrige', { type: 'annule' }],
    ['efface', { type: 'annule' }],
    ['note', { type: 'note' }],
    ['fin de note', { type: 'fin_note' }],
    ['aide', { type: 'aide' }],
    ['compris', { type: 'compris' }],
    ["c'est bon", { type: 'valider' }],
];

// Le modèle français ne connaît pas certains mots : on accepte ce qu'il entend à la place.
export const EQUIVALENTS = [
    ['barrois présents', 'varroas présents'],
    ['varrot présent', 'varroas présents'],
    ['varrot à présent', 'varroas présents'],
    ['pas de barrois', 'pas de varroas'],
    ['pas de varrot', 'pas de varroas'],
    ['pas de varrot à', 'pas de varroas'],
];

// Mots absents du vocabulaire : la grammaire les remplace par leurs équivalents.
const HORS_VOCABULAIRE = ['varroas'];

// Sans équivalent fiable, ces champs restent au toucher : « couvain faible » était compris
// « colonie faible », et noter une fausse valeur est pire que ne rien noter.
const TOUCHER_SEULEMENT = ['surface_couvain', 'fausse_teigne'];

export function normalize(text) {
    return String(text)
        .toLowerCase()
        .normalize('NFD')
        .replace(/[̀-ͯ]/g, '')
        .replace(/[^a-z0-9]+/g, ' ')
        .trim();
}

function construire() {
    const phrases = new Map();
    const ajouter = (texte, action) => phrases.set(texte.toLowerCase(), action);

    for (const groupe of [...FAMILLES.flatMap((f) => f.groupes), ...DETAILS]) {
        if (TOUCHER_SEULEMENT.includes(groupe.id)) {
            continue;
        }

        for (const option of groupe.options) {
            ajouter(option.text, { type: 'choix', champ: groupe.id, valeur: option.value, texte: option.text });
        }
    }

    for (const action of ACTIONS) {
        ajouter(action.text, { type: 'action', champ: action.id, texte: action.text });
    }

    for (const prochaine of PROCHAINES) {
        ajouter(prochaine.dire, { type: 'prochaine', semaines: prochaine.semaines, texte: prochaine.text });
    }

    NOMBRES.forEach((nombre, index) => ajouter(`ruche ${nombre}`, { type: 'ruche', numero: index + 1 }));
    COMMANDES.forEach(([texte, action]) => ajouter(texte, action));

    return phrases;
}

const PHRASES = construire();
const INDEX = new Map([...PHRASES].map(([texte, action]) => [normalize(texte), action]));

for (const [equivalent, phrase] of EQUIVALENTS) {
    INDEX.set(normalize(equivalent), PHRASES.get(phrase));
}

/**
 * La phrase doit être dite seule : la grammaire change toute conversation en mots de la liste,
 * donc chercher une phrase à l'intérieur du texte produirait de fausses détections.
 */
export function matchPhrase(text) {
    return INDEX.get(normalize(text)) ?? null;
}

export function grammaire() {
    const liste = [...PHRASES.keys()].filter((texte) => !HORS_VOCABULAIRE.some((mot) => texte.includes(mot)));

    return JSON.stringify([...liste, ...EQUIVALENTS.map(([equivalent]) => equivalent), '[unk]']);
}

export const listePhrases = () => [...PHRASES.keys()];
