<script setup>
import { computed } from 'vue';
import Comb from '@/Components/Comb.vue';
import Icon from '@/Components/Icon.vue';
import { compterNotes } from '@/terrain/fields.js';
import { useTerrain } from '@/terrain/useTerrain.js';

const { state, ruche, enAttente, aller, choisirRucher, commencer, aVisiterDans } = useTerrain();
const ruchers = computed(() => state.donnees?.ruchers ?? []);
const notes = computed(() => compterNotes(state.visite?.valeurs));
</script>

<template>
    <div class="px-4 pb-4 pt-4 lg:px-0 lg:pt-0">
        <h1 class="bn-title px-1 text-[28px]">Noter une visite</h1>

        <section v-if="state.visite && ruche" class="mt-3 rounded-card bg-forest p-4 text-white">
            <p class="text-sm font-semibold text-sage">Visite en cours</p>
            <h2 class="bn-title mt-0.5 text-[28px]">{{ ruche.nom }}</h2>
            <p class="mb-3.5 text-sage">{{ notes }} {{ notes > 1 ? 'points notés' : 'point noté' }}</p>
            <button type="button" class="bn-btn-flat w-full lg:w-full" @click="commencer(state.visite.ruche_id)">Reprendre</button>
        </section>

        <button v-if="enAttente.length" type="button" class="mt-3 flex min-h-[56px] w-full items-center justify-between rounded-2xl bg-cream px-4 font-semibold text-cream-ink" @click="aller('synchro')">
            {{ enAttente.length }} {{ enAttente.length > 1 ? 'visites à envoyer' : 'visite à envoyer' }}
            <Icon nom="suite" :size="20" />
        </button>

        <section class="mt-5">
            <h2 class="bn-title mb-2 px-1 text-[22px]">{{ state.visite && ruche ? 'Ou choisir un rucher' : 'Choisir un rucher' }}</h2>

            <div v-if="!state.donnees" class="bn-card p-4">
                <p class="font-semibold">Vos ruchers ne sont pas encore sur ce téléphone.</p>
                <p class="mt-1 text-muted">Ouvrez l'appli une fois avec du réseau pour les récupérer.</p>
            </div>
            <div v-else-if="!ruchers.length" class="bn-card p-4">
                <p class="font-semibold">Pas encore de rucher.</p>
                <a href="/ruchers/create" class="bn-btn mt-3">Créer mon rucher</a>
            </div>

            <div class="flex flex-col gap-2.5">
                <button v-for="rucher in ruchers" :key="rucher.id" type="button" class="bn-card flex w-full items-center justify-between gap-3 p-3.5 text-left" @click="choisirRucher(rucher.id)">
                    <span class="min-w-0">
                        <span class="block text-lg font-semibold">{{ rucher.nom }}</span>
                        <span class="block text-sm text-muted">{{ rucher.ruches.length }} {{ rucher.ruches.length > 1 ? 'ruches' : 'ruche' }}<template v-if="aVisiterDans(rucher)"> · {{ aVisiterDans(rucher) }} à visiter</template></span>
                        <Comb class="mt-2" :ruches="rucher.ruches.slice(0, 6)" :size="36" :par-ligne="6" />
                    </span>
                    <Icon nom="suite" :size="20" />
                </button>
            </div>

            <div v-if="ruchers.length" class="mt-3 grid grid-cols-2 gap-2 lg:hidden">
                <button type="button" class="bn-btn-2" @click="aller('scanner')">Scanner un QR</button>
                <button type="button" class="bn-btn-2" @click="aller('datee')">Visite déjà faite</button>
            </div>
            <a v-if="ruchers.length && state.enligne" href="/visites/create" class="bn-btn-2 mt-3 hidden lg:inline-flex lg:w-full">Noter une visite déjà faite</a>
        </section>
    </div>
</template>
