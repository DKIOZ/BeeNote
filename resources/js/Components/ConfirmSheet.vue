<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue';

defineProps({
    titre: { type: String, required: true },
    texte: { type: String, required: true },
    garder: { type: String, required: true },
    confirmer: { type: String, required: true },
    danger: { type: Boolean, default: true },
});

const emit = defineEmits(['garder', 'confirmer']);

const feuille = ref(null);
const premier = ref(null);
let precedent = null;

function surTouche(event) {
    if (event.key === 'Escape') {
        emit('garder');
        return;
    }

    if (event.key !== 'Tab') {
        return;
    }

    // Le focus reste dans la feuille tant qu'elle est ouverte.
    const elements = [...feuille.value.querySelectorAll('button, a[href]')];
    const debut = elements[0];
    const fin = elements[elements.length - 1];

    if (event.shiftKey && document.activeElement === debut) {
        event.preventDefault();
        fin.focus();
    } else if (!event.shiftKey && document.activeElement === fin) {
        event.preventDefault();
        debut.focus();
    }
}

onMounted(() => {
    precedent = document.activeElement;
    premier.value?.focus();
    document.addEventListener('keydown', surTouche);
});

onBeforeUnmount(() => {
    document.removeEventListener('keydown', surTouche);
    precedent?.focus?.();
});
</script>

<template>
    <div role="dialog" aria-modal="true" :aria-label="titre" class="fixed inset-0 z-50 flex items-end justify-center bg-forest/55 lg:items-center" @click.self="$emit('garder')">
        <div ref="feuille" class="bn-safe-pb-sheet w-full max-w-md rounded-t-hero bg-white px-4 pt-6 lg:rounded-hero">
            <p class="bn-title text-[22px]">{{ titre }}</p>
            <p class="mb-4 mt-1.5 text-muted">{{ texte }}</p>
            <button ref="premier" type="button" class="bn-btn w-full lg:w-full" @click="$emit('garder')">{{ garder }}</button>
            <button type="button" class="mt-2 inline-flex min-h-[56px] w-full items-center justify-center font-bold" :class="danger ? 'text-rust' : 'text-forest'" @click="$emit('confirmer')">{{ confirmer }}</button>
            <slot />
        </div>
    </div>
</template>
