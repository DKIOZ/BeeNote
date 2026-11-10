import { describe, expect, it } from 'vitest';
import { TOUS_LES_GROUPES } from '../terrain/fields.js';
import { FICHES, THEMES, chercher, ficheParId, fichesDuMois, fichesPourValeurs, texteALire } from './fiches.js';

describe('les fiches de l\'académie', () => {
    it('ont un identifiant unique et lisible dans une adresse', () => {
        const ids = FICHES.map((fiche) => fiche.id);
        expect(new Set(ids).size).toBe(ids.length);
        ids.forEach((id) => expect(id).toMatch(/^[a-z0-9-]+$/));
    });

    it('ont toujours les deux niveaux : le terrain et le détail', () => {
        for (const fiche of FICHES) {
            expect(fiche.titre, fiche.id).toBeTruthy();
            expect(fiche.enBref, fiche.id).toBeTruthy();
            expect(fiche.gestes.length, fiche.id).toBeGreaterThanOrEqual(2);
            expect(fiche.gestes.length, fiche.id).toBeLessThanOrEqual(5);
            expect(fiche.detail.length, fiche.id).toBeGreaterThan(0);
            fiche.detail.forEach((section) => expect(section.texte?.length || section.liste?.length, `${fiche.id} · ${section.titre}`).toBeTruthy());
        }
    });

    it('rangent chaque fiche dans un thème connu, avec des mois valides', () => {
        const themes = THEMES.map((theme) => theme.id);
        for (const fiche of FICHES) {
            expect(themes).toContain(fiche.theme);
            fiche.mois.forEach((mois) => expect(mois).toBeGreaterThanOrEqual(1));
            fiche.mois.forEach((mois) => expect(mois).toBeLessThanOrEqual(12));
        }
    });

    it('ne renvoient qu\'à des observations qui existent dans la visite', () => {
        for (const fiche of FICHES) {
            for (const signe of fiche.signes) {
                const [champ, valeur] = signe.split(':');
                const groupe = TOUS_LES_GROUPES.find((g) => g.id === champ);
                expect(groupe, `${fiche.id} : ${champ}`).toBeTruthy();
                expect(groupe.options.map((o) => o.value), `${fiche.id} : ${signe}`).toContain(valeur);
            }
            if (fiche.signes.length) {
                expect(fiche.voir, fiche.id).toBeTruthy();
            }
        }
    });

    it('ne renvoient qu\'à des fiches qui existent', () => {
        for (const fiche of FICHES) {
            (fiche.liees ?? []).forEach((id) => expect(ficheParId(id), `${fiche.id} → ${id}`).toBeTruthy());
        }
    });
});

describe('trouver la bonne fiche', () => {
    it('relie les observations inquiétantes de la visite à leur fiche', () => {
        const fiches = fichesPourValeurs({ cellules_royales: 'ok', reine_vue: 'ok', varroas_observes: 'ok' });
        expect(fiches.map((f) => f.id)).toEqual(['essaimage', 'varroa']);
    });

    it('ne propose rien quand tout va bien', () => {
        expect(fichesPourValeurs({ cellules_royales: 'non_ok', ponte_observee: 'ok', reserves_miel: 'suffisantes' })).toEqual([]);
        expect(fichesPourValeurs()).toEqual([]);
    });

    it('donne les fiches de saison', () => {
        expect(fichesDuMois(11).map((f) => f.id)).toEqual(expect.arrayContaining(['hivernage', 'declaration']));
        expect(fichesDuMois(5).map((f) => f.id)).toContain('essaimage');
    });

    it('cherche sans tenir compte des accents ni des majuscules', () => {
        expect(chercher('ESSAIM').map((f) => f.id)).toContain('essaimage');
        expect(chercher('reserves').map((f) => f.id)).toContain('nourrir');
        expect(chercher('   ')).toEqual([]);
    });

    it('lit à voix haute le résumé terrain, pas le détail', () => {
        const texte = texteALire(ficheParId('essaimage'));
        expect(texte).toContain('1. Regardez dedans');
        expect(texte).toContain('À éviter');
        expect(texte).not.toContain('Demaree');
    });
});
