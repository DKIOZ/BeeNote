// Chaque bouton porte la phrase exacte à dire : dire un mot revient à toucher le bouton.
const obs = (id, label, options) => ({ id, label, options: options.map(([value, text]) => ({ value, text })) });

export const FAMILLES = [
    {
        titre: 'La colonie',
        groupes: [
            obs('humeur_colonie', 'Humeur', [['calme', 'Calme'], ['agitee', 'Agitée'], ['agressive', 'Agressive']]),
            obs('estimation_population', 'Force', [['faible', 'Colonie faible'], ['normale', 'Colonie normale'], ['forte', 'Colonie forte']]),
        ],
    },
    {
        titre: 'Reine et ponte',
        groupes: [
            obs('reine_vue', 'Reine', [['ok', 'Reine vue'], ['non_ok', 'Reine pas vue']]),
            obs('ponte_observee', 'Ponte', [['ok', 'Ponte vue'], ['non_ok', 'Pas de ponte']]),
            obs('cellules_royales', 'Cellules royales', [['non_ok', 'Pas de cellules'], ['ok', 'Cellules royales']]),
        ],
    },
    {
        titre: 'Santé',
        groupes: [
            obs('reserves_miel', 'Réserves de miel', [['suffisantes', 'Réserves bonnes'], ['insuffisantes', 'Réserves faibles']]),
            obs('varroas_observes', 'Varroas', [['ok', 'Varroas présents'], ['non_ok', 'Pas de varroas']]),
        ],
    },
];

export const DETAILS = [
    obs('surface_couvain', 'Couvain', [['faible', 'Couvain faible'], ['normale', 'Couvain normal'], ['importante', 'Couvain fort']]),
    obs('frelons_asiatiques', 'Frelons asiatiques', [['absents', 'Pas de frelons'], ['quelques_uns', 'Quelques frelons'], ['nombreux', 'Beaucoup de frelons']]),
    obs('fausse_teigne', 'Fausse teigne', [['non_ok', 'Pas de teigne'], ['ok', 'Teigne présente']]),
    obs('type_nourrissement', 'Nourriture donnée', [['sirop', 'Sirop'], ['candi', 'Candi'], ['pate', 'Pâte']]),
];

export const ACTIONS = [
    { id: 'nourrissement_effectue', text: 'Nourri' },
    { id: 'traitement_applique', text: 'Traité' },
    { id: 'hausse_ajoutee', text: 'Hausse posée' },
    { id: 'hausse_retiree', text: 'Hausse enlevée' },
];

export const PROCHAINES = [
    { semaines: 1, text: 'Dans 1 semaine', dire: 'dans une semaine' },
    { semaines: 2, text: 'Dans 2 semaines', dire: 'dans deux semaines' },
    { semaines: 3, text: 'Dans 3 semaines', dire: 'dans trois semaines' },
];

export const TOUS_LES_GROUPES = [...FAMILLES.flatMap((f) => f.groupes), ...DETAILS];

// Date locale, toujours en AAAA-MM-JJ : le format d'une langue change selon le navigateur.
export function jourLocal(date = new Date()) {
    return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
}

export function ajouterJours(dateIso, jours) {
    const date = new Date(`${dateIso}T12:00:00`);
    date.setDate(date.getDate() + jours);

    return jourLocal(date);
}

export function ajouterSemaines(dateIso, semaines) {
    return ajouterJours(dateIso, semaines * 7);
}

/**
 * Résumé lisible pour le récapitulatif.
 */
export function resumeVisite(valeurs) {
    const lignes = [];

    for (const groupe of TOUS_LES_GROUPES) {
        const option = groupe.options.find((o) => o.value === valeurs[groupe.id]);
        if (option) {
            lignes.push({ label: groupe.label, value: option.text });
        }
    }

    const faites = ACTIONS.filter((a) => valeurs[a.id]).map((a) => a.text);
    if (faites.length) {
        lignes.push({ label: "J'ai fait", value: faites.join(', ') });
    }

    if (valeurs.prochaine_visite_prevue) {
        lignes.push({ label: 'Prochaine visite', value: dateFr(valeurs.prochaine_visite_prevue) });
    }

    return lignes;
}

const FORMAT_LONG = new Intl.DateTimeFormat('fr-FR', { weekday: 'long', day: 'numeric', month: 'long', timeZone: 'UTC' });
const FORMAT_COURT = new Intl.DateTimeFormat('fr-FR', { day: 'numeric', month: 'short', timeZone: 'UTC' });

export function dateFr(iso) {
    const texte = FORMAT_LONG.format(new Date(`${iso}T00:00:00Z`));

    return texte.charAt(0).toUpperCase() + texte.slice(1);
}

export function dateCourte(iso) {
    return FORMAT_COURT.format(new Date(`${iso}T00:00:00Z`));
}
