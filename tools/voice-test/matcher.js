// Liste des phrases de la maquette et correspondance texte vers action, sans IA.
(() => {
const PHRASES = [
  ['ruche un', 'ruche:1'],
  ['ruche deux', 'ruche:2'],
  ['ruche trois', 'ruche:3'],
  ['ruche suivante', 'ruche:suivante'],
  ['ruche terminée', 'ruche:terminee'],
  ['fin du rucher', 'rucher:termine'],
  ['calme', 'humeur:calme'],
  ['agitée', 'humeur:agitee'],
  ['agressive', 'humeur:agressive'],
  ['colonie faible', 'force:faible'],
  ['colonie normale', 'force:normale'],
  ['colonie forte', 'force:forte'],
  ['reine vue', 'reine:vue'],
  ['reine pas vue', 'reine:pas_vue'],
  ['ponte vue', 'ponte:vue'],
  ['pas de ponte', 'ponte:non'],
  ['cellules royales', 'cellules:oui'],
  ['pas de cellules', 'cellules:non'],
  ['réserves bonnes', 'reserves:bonnes'],
  ['réserves faibles', 'reserves:faibles'],
  ['varroas présents', 'varroas:oui'],
  ['pas de varroas', 'varroas:non'],
  ['nourri', 'action:nourri'],
  ['traité', 'action:traite'],
  ['hausse posée', 'action:hausse_posee'],
  ['hausse enlevée', 'action:hausse_enlevee'],
  ['dans une semaine', 'prochaine:1'],
  ['dans deux semaines', 'prochaine:2'],
  ['dans trois semaines', 'prochaine:3'],
  ['pause', 'cmd:pause'],
  ['reprends', 'cmd:reprends'],
  ['annule', 'cmd:annule'],
  ['corrige', 'cmd:corrige'],
  ['efface', 'cmd:efface'],
  ['note', 'cmd:note'],
  ['aide', 'cmd:aide'],
  ["c'est bon", 'cmd:cest_bon'],
];

// « varroas » n'existe pas dans le vocabulaire du modèle : on accepte ce qu'il entend à la place.
const ALIASES = [
  ['barrois présents', 'varroas:oui', 'varroas présents'],
  ['varrot présent', 'varroas:oui', 'varroas présents'],
  ['varrot à présent', 'varroas:oui', 'varroas présents'],
  ['pas de barrois', 'varroas:non', 'pas de varroas'],
  ['pas de varrot', 'varroas:non', 'pas de varroas'],
  ['pas de varrot à', 'varroas:non', 'pas de varroas'],
];

function normalize(text) {
  return ` ${String(text)
    .toLowerCase()
    .normalize('NFD')
    .replace(/[̀-ͯ]/g, '')
    .replace(/[^a-z0-9]+/g, ' ')
    .trim()} `;
}

const MATCHERS = new Map([
  ...PHRASES.map(([phrase, intent]) => [normalize(phrase), { phrase, intent }]),
  ...ALIASES.map(([alias, intent, phrase]) => [normalize(alias), { phrase, intent }]),
]);

// La phrase doit être dite seule : la grammaire change toute conversation en mots de la liste,
// donc chercher une phrase à l'intérieur du texte produit de fausses détections.
function match(text) {
  return MATCHERS.get(normalize(text)) || null;
}

// Grammaire Vosk : uniquement ces phrases, le reste devient [unk].
function grammar() {
  return JSON.stringify([
    ...PHRASES.map(([phrase]) => phrase).filter((phrase) => !phrase.includes('varroas')),
    ...ALIASES.map(([alias]) => alias),
    '[unk]',
  ]);
}

globalThis.BeeMatcher = { PHRASES, ALIASES, normalize, match, grammar };
})();
