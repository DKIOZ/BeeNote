<script setup>
import { computed } from 'vue';

const props = defineProps({
    etat: { type: String, default: 'next' }, // done, todo, late, next
    mark: { type: [String, Number], default: '' },
    size: { type: Number, default: 50 },
    couleur: { type: String, default: null },
    surVert: { type: Boolean, default: false },
});

const STYLES = {
    done: { fill: '#DDEFE3', stroke: '#DDEFE3', largeur: 5, encre: '#17382C' },
    todo: { fill: '#FFB81C', stroke: '#FFB81C', largeur: 5, encre: '#17382C' },
    late: { fill: '#C2570F', stroke: '#C2570F', largeur: 5, encre: '#FFFFFF' },
    hors: { fill: '#E8E8DF', stroke: '#6E7A73', largeur: 2.5, encre: '#3D4F47' },
};

const style = computed(() => {
    // Sur fond clair, la menthe se confond avec le fond ; sur le vert, la rouille avec le vert.
    if (props.etat === 'done' && !props.surVert) {
        return { ...STYLES.done, stroke: '#174A32', largeur: 2.5 };
    }
    if (props.etat === 'late' && props.surVert) {
        return { ...STYLES.late, stroke: '#FFFFFF', largeur: 2.5 };
    }

    const trouve = STYLES[props.etat];
    if (trouve) {
        return trouve;
    }

    return props.surVert
        ? { fill: 'none', stroke: '#C9D8D0', largeur: 2.5, encre: '#FFFFFF' }
        : { fill: 'none', stroke: '#6E7A73', largeur: 2.5, encre: '#17382C' };
});

// Une ruche faite garde son numéro : la coche s'ajoute à côté, elle ne le remplace pas.
const texte = computed(() => (props.etat === 'done' && props.mark === '' ? '✓' : props.mark));
const coche = computed(() => props.etat === 'done' && props.mark !== '');
</script>

<template>
    <svg aria-hidden="true" class="flex-none" :width="size" :height="Math.round(size * 1.083)" viewBox="0 0 48 52">
        <path d="M24 4l19 11v22L24 48 5 37V15z" :fill="style.fill" :stroke="style.stroke" :stroke-width="style.largeur" stroke-linejoin="round" />
        <text x="24" y="33" text-anchor="middle" font-family="Bricolage Grotesque Variable, Figtree, sans-serif" font-size="20" font-weight="700" :fill="style.encre">{{ texte }}</text>
        <g v-if="coche">
            <circle cx="38" cy="41" r="8" fill="#174A32" stroke="#FFFFFF" stroke-width="2" />
            <path d="M34.5 41l2.5 2.5 4.5-5" fill="none" stroke="#FFFFFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
        </g>
        <circle v-if="couleur" cx="39" cy="10" r="6.5" :fill="couleur" stroke="#FFFFFF" stroke-width="2" />
    </svg>
</template>
