<script setup>
import { computed, onMounted } from 'vue';
import { useTerrain } from '@/terrain/useTerrain.js';

const { state, rucher, ruchesDuRucher, enAttente, aller, arreterEcoute } = useTerrain();

const faites = computed(() => ruchesDuRucher.value.filter((r) => r.etat === 'done').length);
const total = computed(() => ruchesDuRucher.value.filter((r) => r.etat !== 'hors').length);
const reste = computed(() => ruchesDuRucher.value.filter((r) => r.etat === 'todo' || r.etat === 'late'));
// Les tâches cochées pendant la tournée ne sont pas encore parties : elles ne sont plus « à retenir ».
const cochees = computed(() => new Set(state.queue.flatMap((item) => item.taches_faites ?? [])));
const titre = computed(() => {
    if (!faites.value) {
        return 'Aucune ruche notée';
    }

    return reste.value.length ? `${faites.value} ${faites.value > 1 ? 'ruches notées' : 'ruche notée'} sur ${total.value}` : 'Rucher terminé !';
});
const aRetenir = computed(() => [
    ...(rucher.value?.taches ?? []).filter((t) => !cochees.value.has(t.id)).map((t) => ({ id: t.id, ruche: 'Tout le rucher', titre: t.titre, retard: t.retard })),
    ...ruchesDuRucher.value.flatMap((r) => r.taches.filter((t) => !cochees.value.has(t.id)).map((t) => ({ id: t.id, ruche: r.nom, titre: t.titre, retard: t.retard }))),
]);

onMounted(() => arreterEcoute());
</script>

<template>
    <div class="pb-28">
        <section class="rounded-b-hero bg-forest px-5 pb-6 pt-8 text-center text-white lg:rounded-hero">
            <div aria-hidden="true" class="flex flex-wrap justify-center">
                <svg v-for="n in Math.min(faites, 6)" :key="n" class="animate-bn-pop" :style="{ animationDelay: `${(n - 1) * 0.15}s` }" width="84" height="91" viewBox="0 0 48 52"><path d="M24 4l19 11v22L24 48 5 37V15z" fill="#DDEFE3" stroke="#DDEFE3" stroke-width="5" stroke-linejoin="round" /><path d="M15 26l6 6 12-13" fill="none" stroke="#17382C" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" /></svg>
            </div>
            <h1 class="bn-title mt-3.5 text-4xl leading-tight">{{ titre }}</h1>
            <p class="mt-1.5 text-lg text-sage">{{ rucher?.nom }}<template v-if="reste.length"> · reste {{ reste.map((r) => r.nom).join(', ') }}</template></p>
        </section>

        <p class="mx-5 mt-5 text-lg lg:mx-1" role="status">
            <template v-if="enAttente.length && !state.enligne">Tout est gardé sur le téléphone et sera envoyé au retour du réseau.</template>
            <template v-else-if="state.envoi === 'encours'">Envoi en cours…</template>
            <template v-else-if="enAttente.length">{{ enAttente.length }} {{ enAttente.length > 1 ? 'visites restent' : 'visite reste' }} à envoyer.</template>
            <template v-else-if="faites">Tout est envoyé.</template>
            <template v-else>Rien n'a été noté sur ce rucher aujourd'hui.</template>
        </p>

        <section v-if="aRetenir.length" class="mx-4 mt-5 lg:mx-0">
            <h2 class="bn-title mb-2 ml-1 text-[22px]">À retenir</h2>
            <div class="flex flex-col gap-2">
                <p v-for="tache in aRetenir" :key="tache.id" class="m-0 rounded-card px-4 py-3" :class="tache.retard ? 'bg-peach text-rust' : 'bg-white shadow-card'"><strong>{{ tache.ruche }}</strong> : {{ tache.titre }}<template v-if="tache.retard">, en retard</template></p>
            </div>
        </section>

        <button v-if="enAttente.length" type="button" class="bn-btn-2 mx-4 mt-3 w-[calc(100%-2rem)] lg:mx-0 lg:w-full" @click="aller('synchro')">Voir ce qui reste à envoyer</button>

        <div class="fixed bottom-0 left-0 right-0 z-30 bg-ground/95 px-4 pb-4 pt-3 lg:left-60">
            <div class="mx-auto max-w-xl">
                <a v-if="state.enligne" href="/dashboard" class="bn-btn w-full lg:w-full">Terminer</a>
                <button v-else type="button" class="bn-btn w-full lg:w-full" @click="aller('accueil')">Terminer</button>
            </div>
        </div>
    </div>
</template>
