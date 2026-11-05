<script setup>
import Icon from '@/Components/Icon.vue';
import MotsListe from '@/Components/MotsListe.vue';
import { useTerrain } from '@/terrain/useTerrain.js';

const { revenir } = useTerrain();
</script>

<template>
    <div class="pb-28">
        <section class="rounded-b-hero bg-forest px-5 py-4 text-white lg:rounded-hero">
            <button type="button" aria-label="Retour" class="-ml-3 flex h-12 w-12 items-center justify-center" @click="revenir"><Icon nom="retour" /></button>
            <h1 class="bn-title text-[28px] leading-tight">Ce que vous pouvez dire</h1>
            <p class="mt-1 text-sage">Lu à voix haute. Dites la phrase seule, puis « compris » pour revenir.</p>
        </section>
        <div class="px-4 pt-2 lg:px-0"><MotsListe /></div>
        <div class="fixed bottom-0 left-0 right-0 z-30 bg-ground/95 px-4 pb-4 pt-3 lg:left-60">
            <div class="mx-auto max-w-xl">
                <button type="button" class="bn-btn w-full lg:w-full" @click="revenir">Compris</button>
            </div>
        </div>
    </div>
</template>
