<script setup>
import { computed } from 'vue';
import { resumeVisite, TOUS_LES_GROUPES } from '@/terrain/fields.js';
import { useTerrain } from '@/terrain/useTerrain.js';

const { state, ruche, rucheApres, valider, basculerTache, corriger, abandonnerVisite } = useTerrain();

const valeurs = computed(() => state.visite?.valeurs ?? {});
const lignes = computed(() => resumeVisite(valeurs.value));
const manquants = computed(() => ['reine_vue', 'ponte_observee', 'reserves_miel', 'varroas_observes', 'humeur_colonie']
    .filter((champ) => !valeurs.value[champ])
    .map((champ) => TOUS_LES_GROUPES.find((g) => g.id === champ).label.toLowerCase()));

const tacheReine = computed(() => (valeurs.value.reine_vue === 'ok'
    ? ruche.value?.taches?.find((t) => /reine/i.test(t.titre))
    : null));
const autresTaches = computed(() => (ruche.value?.taches ?? []).filter((t) => t.id !== tacheReine.value?.id));
const faite = (tache) => state.visite?.taches_faites.includes(tache.id);
const bonneReponse = () => {
    if (tacheReine.value && !faite(tacheReine.value)) {
        basculerTache(tacheReine.value.id);
    }
};

const nomDe = (id) => state.donnees?.ruchers.flatMap((r) => r.ruches).find((h) => h.id === id)?.nom ?? null;
const bouton = computed(() => {
    if (state.apres?.type === 'fin') {
        return 'C\'est bon, finir le rucher';
    }
    const suivante = state.apres?.type === 'ruche' ? nomDe(state.apres.id) : rucheApres.value?.nom;

    return suivante ? `C'est bon, ${suivante}` : 'C\'est bon';
});
// Rien de noté : on le dit, plutôt que d'enregistrer une visite vide par un toucher machinal.
const vide = computed(() => !lignes.value.length && !state.visite?.note && !state.visite?.taches_faites.length);
</script>

<template>
    <div v-if="state.visite" class="pb-40">
        <section class="rounded-b-hero bg-forest px-5 pb-4 pt-3 text-white lg:rounded-hero">
            <p v-if="state.voix.ecoute" class="flex items-center gap-2 text-sm font-semibold text-sage">
                <span aria-hidden="true" class="h-2 w-2 rounded-full bg-honey" />
                J'écoute
            </p>
            <h1 class="bn-title mt-1 text-[28px] leading-tight">{{ ruche?.nom }}, c'est bon ?</h1>
            <p class="mt-1 text-sage">
                {{ lignes.length }} {{ lignes.length > 1 ? 'points notés' : 'point noté' }}.
                <template v-if="state.voix.ecoute">Dites « c'est bon », ou « corrige » puis la bonne phrase.</template>
            </p>
        </section>

        <dl v-if="lignes.length" class="bn-card m-0 mx-4 mt-3 px-4 lg:mx-0">
            <div v-for="ligne in lignes" :key="ligne.label" class="flex min-h-[44px] items-center justify-between gap-3 border-b border-hairline py-1 last:border-0">
                <dt class="text-sm text-muted">{{ ligne.label }}</dt>
                <dd class="m-0 text-right font-semibold">{{ ligne.value }}</dd>
            </div>
        </dl>
        <div v-else class="bn-card mx-4 mt-3 p-4 lg:mx-0">
            <p class="font-semibold">Rien n'est noté pour cette ruche.</p>
            <p class="mt-1 text-muted">Si tout allait bien, enregistrez « rien à signaler ». Sinon, ne l'enregistrez pas.</p>
        </div>

        <p v-if="state.visite.note" class="mx-5 mt-2 font-semibold text-cream-ink lg:mx-1">Note vocale jointe · {{ state.visite.note.duree }} s</p>
        <p v-if="manquants.length" class="mx-5 mt-2 text-sm text-muted lg:mx-1"><strong class="text-ink">Pas noté :</strong> {{ manquants.join(', ') }}</p>

        <section v-if="tacheReine" class="mx-4 mt-2 rounded-2xl bg-cream p-3 lg:mx-0">
            <p class="mb-2 font-semibold text-cream-ink">Reine vue : « {{ tacheReine.titre }} » est fait ?</p>
            <p v-if="faite(tacheReine)" class="font-semibold text-cream-ink" role="status">Marquée comme faite. <button type="button" class="ml-1 min-h-[44px] font-semibold underline" @click="basculerTache(tacheReine.id)">Garder la tâche</button></p>
            <button v-else type="button" class="bn-btn-2" @click="bonneReponse">Oui, c'est fait</button>
        </section>

        <section v-for="tache in autresTaches" :key="tache.id" class="mx-4 mt-2 flex items-center justify-between gap-2 rounded-2xl bg-white p-3 shadow-card lg:mx-0">
            <span class="min-w-0 font-semibold">{{ tache.titre }}</span>
            <button type="button" :aria-pressed="faite(tache)" class="min-h-[48px] flex-none rounded-xl border-[1.5px] border-line px-3 font-semibold" :class="faite(tache) && 'border-forest bg-forest text-white'" @click="basculerTache(tache.id)">{{ faite(tache) ? 'Faite' : 'Marquer faite' }}</button>
        </section>

        <div class="fixed bottom-0 left-0 right-0 z-30 bg-ground/95 px-4 pb-4 pt-3 lg:left-60">
            <div class="mx-auto flex max-w-xl flex-col gap-2">
                <button type="button" class="bn-btn w-full lg:w-full" @click="valider">{{ vide ? 'Rien à signaler, enregistrer' : bouton }}</button>
                <button type="button" class="bn-btn-2 lg:w-full" @click="corriger">{{ vide ? 'Revenir à la visite' : 'Corriger' }}</button>
                <button v-if="vide" type="button" class="min-h-[48px] font-semibold text-rust underline" @click="abandonnerVisite">Ne pas enregistrer</button>
            </div>
        </div>
    </div>
</template>
