<script setup>
import { computed, ref, watch } from 'vue';
import Icon from '@/Components/Icon.vue';
import { ajouterJours, jourLocal } from '@/terrain/fields.js';
import { useTerrain } from '@/terrain/useTerrain.js';

const { state, aller, commencer, abandonnerVisite } = useTerrain();

const auj = jourLocal();
const hier = ajouterJours(auj, -1);

const ruchers = computed(() => state.donnees?.ruchers ?? []);
const rucherId = ref(state.rucherId ?? ruchers.value[0]?.id ?? null);
const ruches = computed(() => ruchers.value.find((r) => r.id === rucherId.value)?.ruches ?? []);
const rucheId = ref(ruches.value[0]?.id ?? null);
const date = ref(auj);

watch(rucherId, () => { rucheId.value = ruches.value[0]?.id ?? null; });

async function continuer() {
    if (state.visite) {
        await abandonnerVisite();
    }
    state.rucherId = rucherId.value;
    commencer(rucheId.value, date.value);
}
</script>

<template>
    <div class="pb-28">
        <header class="flex items-center gap-1 px-2 pt-2 lg:px-0">
            <button type="button" aria-label="Retour" class="flex h-12 w-12 flex-none items-center justify-center text-forest" @click="aller('accueil')"><Icon nom="retour" /></button>
            <h1 class="bn-title text-[28px]">Noter après coup</h1>
        </header>
        <p class="mx-5 text-muted lg:mx-1">Pour une visite déjà faite, depuis chez vous.</p>
        <p v-if="state.visite" class="mx-4 mt-3 rounded-2xl bg-cream p-3 font-semibold text-cream-ink lg:mx-0">Une visite est en cours : elle sera remplacée.</p>

        <div class="flex flex-col gap-4 px-4 pt-4 lg:px-0">
            <fieldset class="m-0 border-0 p-0">
                <legend class="bn-label p-0">Quand</legend>
                <div class="grid grid-cols-2 gap-2">
                    <button v-for="[valeur, texte] in [[auj, 'Aujourd\'hui'], [hier, 'Hier']]" :key="valeur" type="button" :aria-pressed="date === valeur" class="min-h-[60px] rounded-2xl border-[1.5px] font-semibold" :class="date === valeur ? 'border-forest bg-forest text-white' : 'border-line bg-white'" @click="date = valeur">{{ texte }}</button>
                </div>
                <label for="autre" class="bn-label mt-2.5">Autre date</label>
                <input id="autre" v-model="date" type="date" :max="auj" class="bn-input">
            </fieldset>

            <fieldset v-if="ruchers.length > 1" class="m-0 border-0 p-0">
                <legend class="bn-label p-0">Rucher</legend>
                <div class="grid grid-cols-2 gap-2">
                    <button v-for="rucher in ruchers" :key="rucher.id" type="button" :aria-pressed="rucherId === rucher.id" class="min-h-[60px] rounded-2xl border-[1.5px] px-1 font-semibold leading-tight" :class="rucherId === rucher.id ? 'border-forest bg-forest text-white' : 'border-line bg-white'" @click="rucherId = rucher.id">{{ rucher.nom }}</button>
                </div>
            </fieldset>

            <fieldset class="m-0 border-0 p-0">
                <legend class="bn-label p-0">Ruche</legend>
                <div class="grid grid-cols-3 gap-2">
                    <button v-for="ruche in ruches" :key="ruche.id" type="button" :aria-pressed="rucheId === ruche.id" class="min-h-[60px] rounded-2xl border-[1.5px] px-1 font-semibold leading-tight" :class="rucheId === ruche.id ? 'border-forest bg-forest text-white' : 'border-line bg-white'" @click="rucheId = ruche.id">{{ ruche.nom }}</button>
                </div>
            </fieldset>
        </div>

        <div class="fixed bottom-0 left-0 right-0 z-30 bg-ground/95 px-4 pb-4 pt-3 lg:left-60">
            <div class="mx-auto max-w-xl">
                <button type="button" class="bn-btn w-full lg:w-full" :disabled="!rucheId || !date" @click="continuer">Noter cette visite</button>
            </div>
        </div>
    </div>
</template>
