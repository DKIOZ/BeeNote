<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import Hex from '@/Components/Hex.vue';
import Icon from '@/Components/Icon.vue';
import VoixProbleme from '@/Components/terrain/VoixProbleme.vue';
import { dateCourte } from '@/terrain/fields.js';
import { useTerrain } from '@/terrain/useTerrain.js';

const page = usePage();
const { state, rucher, ruchesDuRucher, prochaineRuche, aller, commencer, ecouter, arreterEcoute, terminerRucher } = useTerrain();

const faites = computed(() => ruchesDuRucher.value.filter((r) => r.etat === 'done').length);
const voixActive = computed(() => {
    try {
        return localStorage.getItem('bn-voix') !== '0';
    } catch {
        return true;
    }
});
// Au-delà de six ruches, les alvéoles se resserrent pour garder « Commencer » à portée.
const serree = computed(() => ruchesDuRucher.value.length > 6);
const STATUTS = { done: 'Faite', todo: 'À faire', late: 'En retard', next: 'À venir' };
const etatSuivante = computed(() => {
    const ruche = prochaineRuche.value;
    if (!ruche) {
        return null;
    }

    return ruche.taches[0]?.titre ?? (ruche.derniere_visite ? `Dernière visite le ${dateCourte(ruche.derniere_visite.date)} : ${ruche.derniere_visite.resume}` : 'Pas encore de visite');
});

const basculerEcoute = () => (state.voix.ecoute ? arreterEcoute() : ecouter(page.props.voiceModelUrl));
</script>

<template>
    <div v-if="rucher">
        <section class="rounded-b-hero bg-forest px-5 pb-5 pt-2 text-white lg:rounded-hero">
            <button type="button" aria-label="Retour" class="-ml-3 flex h-12 w-12 items-center justify-center" @click="aller('accueil')"><Icon nom="retour" /></button>
            <h1 class="bn-title text-4xl leading-tight">{{ rucher.nom }}</h1>
            <p class="mb-3 mt-1 text-sage">{{ faites }} {{ faites > 1 ? 'ruches' : 'ruche' }} sur {{ ruchesDuRucher.length }} {{ faites > 1 ? 'visitées' : 'visitée' }}</p>

            <div role="group" aria-label="Ruches du rucher" class="flex flex-wrap gap-1">
                <button v-for="ruche in ruchesDuRucher" :key="ruche.id" type="button" :aria-label="`${ruche.nom}, ${STATUTS[ruche.etat]}`" class="flex flex-col items-center gap-0.5 text-sm font-semibold" :class="serree ? 'w-[76px]' : 'w-[104px]'" @click="commencer(ruche.id)">
                    <Hex :etat="prochaineRuche?.id === ruche.id && ruche.etat === 'next' ? 'todo' : ruche.etat" :mark="ruche.numero" :size="serree ? 60 : 96" sur-vert :class="prochaineRuche?.id === ruche.id && 'animate-bn-pulse'" />
                    {{ prochaineRuche?.id === ruche.id ? 'Maintenant' : STATUTS[ruche.etat] }}
                </button>
            </div>
        </section>

        <section v-if="prochaineRuche" class="bn-card mx-4 mt-3 p-4 lg:mx-0">
            <h2 class="bn-title text-[28px]">{{ prochaineRuche.nom }}</h2>
            <p class="mb-2.5 mt-0.5" :class="prochaineRuche.etat === 'late' ? 'font-semibold text-rust' : 'text-muted'">{{ etatSuivante }}</p>
            <button type="button" class="bn-btn w-full lg:w-full" @click="commencer(prochaineRuche.id)">Commencer {{ prochaineRuche.nom }}</button>
        </section>
        <p v-else-if="ruchesDuRucher.length" class="bn-card mx-4 mt-3 p-4 font-semibold lg:mx-0">Toutes les ruches sont visitées.</p>
        <div v-else class="bn-card mx-4 mt-3 p-4 lg:mx-0">
            <p class="font-semibold">Ce rucher n'a pas encore de ruche.</p>
            <a v-if="state.enligne" :href="`/ruchers/${rucher.id}/ruches/create`" class="bn-btn-2 mt-3">Ajouter une ruche</a>
            <p v-else class="mt-1 text-muted">Ajoutez-en une au retour du réseau.</p>
        </div>

        <button v-if="voixActive" type="button" :aria-pressed="state.voix.ecoute" :disabled="state.voix.chargement" class="mx-4 mt-3 flex min-h-[64px] w-[calc(100%-2rem)] items-center gap-3 rounded-2xl border-[1.5px] border-line px-3.5 py-1.5 text-left font-semibold lg:mx-0 lg:w-full" :class="state.voix.ecoute ? 'bg-forest text-white' : 'bg-white text-forest'" @click="basculerEcoute">
            <Icon nom="micro" :size="26" />
            <span>
                <template v-if="state.voix.chargement">Préparation de la voix…</template>
                <template v-else-if="state.voix.ecoute">J'écoute pour tout le rucher. Dites « {{ prochaineRuche ? `ruche ${prochaineRuche.numero}` : 'fin du rucher' }} » ou « pause ».</template>
                <template v-else>Écouter pour tout le rucher.<span class="lg:hidden"> À lancer avant d'enfiler vos gants.</span></template>
            </span>
        </button>
        <VoixProbleme class="mx-4 mt-2 lg:mx-0" />

        <button type="button" class="mt-2 flex min-h-[48px] w-full items-center justify-center font-bold text-forest" @click="terminerRucher">Fin du rucher</button>
    </div>
</template>
