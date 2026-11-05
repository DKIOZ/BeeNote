import assert from 'node:assert/strict';
import { test } from 'node:test';
import { grammaire, listePhrases, matchPhrase } from './phrases.js';

test('chaque phrase déclenche une action', () => {
    for (const phrase of listePhrases()) {
        assert.ok(matchPhrase(phrase), phrase);
    }
});

test('les négations ne deviennent jamais des affirmations', () => {
    assert.equal(matchPhrase('reine pas vue').valeur, 'non_ok');
    assert.equal(matchPhrase('reine vue').valeur, 'ok');
    assert.equal(matchPhrase('pas de ponte').valeur, 'non_ok');
    assert.equal(matchPhrase('pas de varroas').valeur, 'non_ok');
    assert.equal(matchPhrase('pas de cellules').valeur, 'non_ok');
});

test('finir une ruche et finir le rucher sont distincts', () => {
    assert.equal(matchPhrase('ruche terminée').type, 'ruche_terminee');
    assert.equal(matchPhrase('fin du rucher').type, 'fin_rucher');
});

test('une conversation ne déclenche rien', () => {
    assert.equal(matchPhrase('alors colonie forte voilà'), null);
    assert.equal(matchPhrase("vue c'est bon varrot à"), null);
    assert.equal(matchPhrase('de bon traité note terminée de cellules dans faibles bonnes hausse posée'), null);
    assert.equal(matchPhrase(''), null);
});

test('les équivalents de varroas fonctionnent', () => {
    assert.equal(matchPhrase('varrot à présent').champ, 'varroas_observes');
    assert.equal(matchPhrase('pas de barrois').valeur, 'non_ok');
});

test('les champs inconnus du modèle restent au toucher', () => {
    assert.equal(matchPhrase('couvain faible'), null);
    assert.equal(matchPhrase('teigne présente'), null);
    assert.ok(!JSON.parse(grammaire()).some((phrase) => /couvain|teigne/.test(phrase)));
});

test('les ruches se désignent par leur numéro', () => {
    assert.equal(matchPhrase('ruche deux').numero, 2);
    assert.equal(matchPhrase('ruche vingt').numero, 20);
});

test('la grammaire exclut les mots inconnus du modèle et garde [unk]', () => {
    const liste = JSON.parse(grammaire());
    assert.ok(liste.includes('[unk]'));
    assert.ok(!liste.some((phrase) => phrase.includes('varroas')));
});
