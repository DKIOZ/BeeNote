<script setup>
import { computed } from 'vue';
import Icon from '@/Components/Icon.vue';
import { DETAILS } from '@/terrain/fields.js';
import { useTerrain } from '@/terrain/useTerrain.js';

const { state, ruche, aller, noter, effacerGroupe } = useTerrain();
const valeurs = computed(() => state.visite?.valeurs ?? {});
</script>

<template>
    <div class="pb-28">
        <header class="flex items-center gap-1 px-2 pt-2 lg:px-0">
            <button type="button" aria-label="Retour à la visite" class="flex h-12 w-12 flex-none items-center justify-center text-forest" @click="aller('visite')"><Icon nom="retour" /></button>
            <div>
                <h1 class="bn-title text-[28px] leading-tight">Plus de détails</h1>
                <p class="bn-sub">{{ ruche?.nom }} · facultatif · couvain et teigne sans la voix</p>
            </div>
        </header>

        <div class="flex flex-col gap-4 px-4 pt-3 lg:px-0">
            <fieldset v-for="groupe in DETAILS" :key="groupe.id" :aria-labelledby="`d-${groupe.id}`" class="m-0 border-0 p-0">
                <div class="mb-1 flex min-h-[28px] items-center justify-between">
                    <span :id="`d-${groupe.id}`" class="text-sm font-semibold text-muted">{{ groupe.label }}</span>
                    <button v-if="valeurs[groupe.id]" type="button" class="-my-2 min-h-[44px] min-w-[76px] text-sm font-semibold text-muted underline" @click="effacerGroupe([groupe.id])">Effacer</button>
                    <span v-else class="text-sm text-muted">Pas noté</span>
                </div>
                <div class="grid gap-2" :class="groupe.options.length === 3 ? 'grid-cols-3' : 'grid-cols-2'">
                    <button
                        v-for="option in groupe.options"
                        :key="option.value"
                        type="button"
                        :aria-pressed="valeurs[groupe.id] === option.value"
                        class="min-h-[60px] rounded-2xl border-[1.5px] px-1 font-semibold leading-tight"
                        :class="valeurs[groupe.id] === option.value ? 'border-forest bg-forest text-white' : 'border-line bg-white text-ink'"
                        @click="noter({ type: 'choix', champ: groupe.id, valeur: option.value, texte: option.text })"
                    >{{ option.text }}</button>
                </div>
            </fieldset>
        </div>

        <div class="fixed bottom-0 left-0 right-0 z-30 bg-ground/95 px-4 pb-4 pt-3 lg:left-60">
            <div class="mx-auto max-w-xl">
                <button type="button" class="bn-btn w-full lg:w-full" @click="aller('visite')">Revenir à la visite</button>
            </div>
        </div>
    </div>
</template>
