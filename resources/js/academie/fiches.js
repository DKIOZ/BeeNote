// L'académie vit dans le code : les fiches partent avec l'appli et restent lisibles sans réseau.
import bases from './fiches/bases.js';
import colonie from './fiches/colonie.js';
import sante from './fiches/sante.js';

export const THEMES = [
    { id: 'bases', titre: 'Les bases', texte: 'Ouvrir, poser une hausse, s\'équiper' },
    { id: 'colonie', titre: 'Reine et colonie', texte: 'Ponte, essaimage, force, humeur' },
    { id: 'sante', titre: 'Santé et réserves', texte: 'Varroa, frelon, teigne, nourrissement' },
    { id: 'annee', titre: 'L\'année et la loi', texte: 'Hivernage, déclaration, registre' },
];

export const MOIS = ['janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];

export const FICHES = [...colonie, ...sante, ...bases];

export function ficheParId(id) {
    return FICHES.find((fiche) => fiche.id === id) ?? null;
}

// Les fiches qui répondent à ce qui a été noté pendant la visite, dans l'ordre des fiches.
export function fichesPourValeurs(valeurs = {}) {
    return FICHES.filter((fiche) => fiche.signes.some((signe) => {
        const [champ, valeur] = signe.split(':');
        return valeurs[champ] === valeur;
    }));
}

// mois : 1 à 12.
export function fichesDuMois(mois) {
    return FICHES.filter((fiche) => fiche.mois.includes(mois));
}

export function fichesDuTheme(theme) {
    return FICHES.filter((fiche) => fiche.theme === theme);
}

const sansAccents = (texte) => texte.normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase();

// Recherche simple, sans accents ni majuscules, sur les mots de la fiche.
export function chercher(texte) {
    const mots = sansAccents(texte).split(/\s+/).filter(Boolean);
    if (!mots.length) {
        return [];
    }

    return FICHES.filter((fiche) => {
        const corps = sansAccents([fiche.titre, fiche.titreDetail, fiche.voir ?? '', fiche.enBref, ...fiche.gestes].join(' '));
        return mots.every((mot) => corps.includes(mot));
    });
}

// Ce qui est lu à voix haute : le court, dans l'ordre de l'écran.
export function texteALire(fiche) {
    return [
        fiche.titre,
        fiche.enBref,
        fiche.urgent ? `Urgent ${fiche.urgent}` : null,
        ...fiche.gestes.map((geste, index) => `${index + 1}. ${geste}`),
        fiche.eviter ? `À éviter : ${fiche.eviter}` : null,
    ].filter(Boolean).join(' ');
}
