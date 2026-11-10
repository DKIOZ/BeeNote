<script setup>
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import Icon from '@/Components/Icon.vue';
import FicheDetail from '@/Components/academie/FicheDetail.vue';
import FicheTerrain from '@/Components/academie/FicheTerrain.vue';
import { ficheParId, texteALire } from '@/academie/fiches.js';
import { useTerrain } from '@/terrain/useTerrain.js';

// Une fiche de l'académie au rucher : elle part avec l'appli, donc s'ouvre sans réseau.
const { state, fermerFiche, dire } = useTerrain();

const fiche = computed(() => ficheParId(state.fiche));
const plus = ref(false);
watch(() => state.fiche, () => { plus.value = false; });

const peutLire = 'speechSynthesis' in window;
const lireFiche = () => dire(texteALire(fiche.value), true);
onBeforeUnmount(() => window.speechSynthesis?.cancel());

const retour = computed(() => (state.visite ? 'Compris, je reprends la visite' : 'Compris'));
</script>

<template>
    <div v-if="fiche" class="pb-48">
        <section class="rounded-b-hero bg-forest px-5 pb-5 pt-3 text-white lg:rounded-hero">
            <div class="flex items-center justify-between">
                <button type="button" aria-label="Retour" class="-ml-3 flex h-12 w-12 items-center justify-center" @click="fermerFiche"><Icon nom="retour" /></button>
                <span class="rounded-xl bg-honey px-2.5 py-1 text-[13px] font-bold text-forest">Fiche terrain</span>
            </div>
            <h1 class="bn-title text-[28px] leading-tight">{{ fiche.titre }}</h1>
            <p class="mt-2 text-[17px] leading-snug text-sage-light">{{ fiche.enBref }}</p>
            <p v-if="state.voix.ecoute" class="mt-2 text-sm font-semibold text-sage">Dites « compris » pour revenir.</p>
        </section>

        <div class="px-4 pt-3 lg:px-0">
            <FicheTerrain :fiche="fiche" />

            <section v-if="plus" class="mt-6">
                <h2 class="bn-title mb-3 text-[26px] leading-tight text-forest">{{ fiche.titreDetail }}</h2>
                <FicheDetail :fiche="fiche" niveau="h3" />
            </section>
        </div>

        <div class="bn-safe-pb fixed bottom-0 left-0 right-0 z-30 bg-ground/95 px-4 pt-3 lg:left-60">
            <div class="mx-auto flex max-w-xl flex-col gap-2.5">
                <div class="flex gap-2.5">
                    <button v-if="peutLire" type="button" class="bn-btn-2 flex-1 gap-2 lg:w-full" @click="lireFiche"><Icon nom="lecture" :size="20" />Lire à voix haute</button>
                    <button type="button" class="bn-btn-2 flex-1 lg:w-full" :aria-expanded="plus" @click="plus = !plus">{{ plus ? 'Moins de détails' : 'En savoir plus' }}</button>
                </div>
                <button type="button" class="bn-btn w-full lg:w-full" @click="fermerFiche">{{ retour }}</button>
            </div>
        </div>
    </div>
</template>
