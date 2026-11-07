<script setup>
import { computed } from 'vue';
import Hex from '@/Components/Hex.vue';

const props = defineProps({
    ruches: { type: Array, default: () => [] },
    size: { type: Number, default: 56 },
    parLigne: { type: Number, default: 4 },
    surVert: { type: Boolean, default: false },
    // Au-delà, les alvéoles sont résumées par « +N » : aucune ruche ne disparaît en silence.
    max: { type: Number, default: null },
});

const visibles = computed(() => (props.max ? props.ruches.slice(0, props.max) : props.ruches));
const cachees = computed(() => props.ruches.length - visibles.value.length);

// Les lignes paires sont décalées d'une demi-alvéole pour former un vrai rayon.
const lignes = computed(() => {
    const resultat = [];
    for (let i = 0; i < visibles.value.length; i += props.parLigne) {
        resultat.push(visibles.value.slice(i, i + props.parLigne));
    }

    return resultat;
});

const description = computed(() => {
    const etats = { done: 'faite', todo: 'à faire', late: 'en retard', next: 'à venir', hors: 'hors tournée' };

    return props.ruches.map((r) => `${r.nom}, ${etats[r.etat] ?? 'à venir'}`).join(' ; ');
});
</script>

<template>
    <div role="img" :aria-label="description">
        <div
            v-for="(ligne, index) in lignes"
            :key="index"
            class="flex gap-0.5"
            :style="{ marginLeft: index % 2 ? `${size / 2 + 1}px` : 0, marginTop: index ? `${-size * 0.26}px` : 0 }"
        >
            <Hex v-for="ruche in ligne" :key="ruche.id" :etat="ruche.etat" :mark="ruche.numero" :size="size" :sur-vert="surVert" />
        </div>
        <p v-if="cachees > 0" aria-hidden="true" class="mt-1 text-sm font-semibold" :class="surVert ? 'text-sage' : 'text-muted'">+ {{ cachees }} {{ cachees > 1 ? 'autres ruches' : 'autre ruche' }}</p>
    </div>
</template>
