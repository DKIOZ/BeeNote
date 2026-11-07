<script setup>
import { usePage } from '@inertiajs/vue3';
import { useTerrain } from '@/terrain/useTerrain.js';

const page = usePage();
const { state, ecouter } = useTerrain();

const CAUSES = {
    micro: {
        titre: 'Le micro est bloqué',
        texte: 'Touchez les boutons pour noter, rien n\'est perdu. Pour la voix, autorisez le micro dans les réglages du navigateur, puis réessayez.',
    },
    moteur: {
        titre: 'La voix n\'a pas pu se préparer',
        texte: 'Il faut une fois du réseau pour la charger sur ce téléphone. Continuez au toucher, rien n\'est perdu.',
    },
    indisponible: {
        titre: 'Ce téléphone ne permet pas la voix',
        texte: 'Le navigateur n\'y donne pas accès. Continuez au toucher, rien n\'est perdu.',
    },
};
</script>

<template>
    <section v-if="state.voix.coupee" role="alert" class="rounded-card bg-peach p-3.5">
        <p class="font-bold text-rust">Le micro s'est arrêté</p>
        <p class="mb-2.5 mt-1">Le téléphone l'a repris, après un appel ou le verrouillage par exemple.</p>
        <button type="button" class="bn-btn-2 w-full lg:w-full" :disabled="state.voix.chargement" @click="ecouter(page.props.voiceModelUrl)">Reprendre l'écoute</button>
    </section>
    <section v-else-if="state.voix.erreur" role="alert" class="rounded-card bg-peach p-3.5">
        <p class="font-bold text-rust">{{ CAUSES[state.voix.erreur].titre }}</p>
        <p class="mb-2.5 mt-1">{{ CAUSES[state.voix.erreur].texte }}</p>
        <button v-if="state.voix.erreur !== 'indisponible'" type="button" class="bn-btn-2 w-full lg:w-full" :disabled="state.voix.chargement" @click="ecouter(page.props.voiceModelUrl)">Réessayer</button>
    </section>
</template>
