import assert from 'node:assert/strict';
import { test } from 'node:test';
import './matcher.js';

const { PHRASES, ALIASES, match, grammar } = globalThis.BeeMatcher;

test('chaque phrase reconnaît sa propre action', () => {
  for (const [phrase, intent] of PHRASES) {
    assert.equal(match(phrase)?.intent, intent, phrase);
  }
});

test('les actions sont uniques', () => {
  assert.equal(new Set(PHRASES.map(([, intent]) => intent)).size, PHRASES.length);
});

test('la négation ne se transforme jamais en affirmation', () => {
  assert.equal(match('reine pas vue').intent, 'reine:pas_vue');
  assert.equal(match('pas de ponte').intent, 'ponte:non');
  assert.equal(match('pas de varroas').intent, 'varroas:non');
  assert.equal(match('pas de cellules').intent, 'cellules:non');
});

test('accents, majuscules et apostrophes sont ignorés', () => {
  assert.equal(match('Réserves Bonnes').intent, 'reserves:bonnes');
  assert.equal(match('reserves bonnes').intent, 'reserves:bonnes');
  assert.equal(match("C’est bon").intent, 'cmd:cest_bon');
});

test('finir une ruche et finir le rucher sont deux phrases éloignées', () => {
  assert.equal(match('ruche terminée').intent, 'ruche:terminee');
  assert.equal(match('fin du rucher').intent, 'rucher:termine');
  assert.equal(match('rucher terminé'), null);
});

test('un mot contenu dans un autre mot ne déclenche rien', () => {
  assert.equal(match('notez bien'), null);
  assert.equal(match('une pausette'), null);
  assert.equal(match('bonjour tout le monde'), null);
  assert.equal(match(''), null);
});

test('une phrase noyée dans une parole ne déclenche rien', () => {
  assert.equal(match('alors colonie forte voilà'), null);
});

test('les six fausses détections du premier essai sont rejetées', () => {
  const heard = [
    "vue c'est bon varrot à",
    'annule de cellules trois barrois hausse bon traité ruche une semaine varrot un de de cellules royales colonie calme hausse aide efface dans agitée terminée',
    'un dans dans efface réserves corrige vue cellules ruche réserves un',
    "réserves de à c'est pas une agitée semaines efface de bon de aide",
    'de bon traité note terminée de cellules dans faibles bonnes hausse posée',
    "colonie rucher reine vue hausse enlevée calme de agitée de un de c'est bonnes de enlevée de réserves de ruche un varrot à normale annule pas de barrois hausse c'est un de pas de cellules reine suivante un de bonnes c'est rucher varrot",
  ];
  heard.forEach((text) => assert.equal(match(text), null, text));
});

test('les équivalents de varroas donnent la bonne action', () => {
  for (const [alias, intent, phrase] of ALIASES) {
    assert.equal(match(alias).intent, intent, alias);
    assert.equal(match(alias).phrase, phrase, alias);
  }
});

test('la grammaire remplace varroas par ses équivalents et garde [unk]', () => {
  const list = JSON.parse(grammar());
  assert.equal(list.length, PHRASES.length - 2 + ALIASES.length + 1);
  assert.ok(list.includes('[unk]'));
  assert.ok(!list.some((phrase) => phrase.includes('varroas')));
});
