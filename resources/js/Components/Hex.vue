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

const texte = computed(() => (props.etat === 'done' ? '✓' : props.mark));
</script>

<template>
    <svg aria-hidden="true" class="flex-none" :width="size" :height="Math.round(size * 1.083)" viewBox="0 0 48 52">
        <path d="M24 4l19 11v22L24 48 5 37V15z" :fill="style.fill" :stroke="style.stroke" :stroke-width="style.largeur" stroke-linejoin="round" />
        <text x="24" y="33" text-anchor="middle" font-family="Bricolage Grotesque Variable, Figtree, sans-serif" font-size="20" font-weight="700" :fill="style.encre">{{ texte }}</text>
        <circle v-if="couleur" cx="39" cy="10" r="6.5" :fill="couleur" stroke="#FFFFFF" stroke-width="2" />
    </svg>
</template>
