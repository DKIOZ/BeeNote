<script setup>
import { computed, onBeforeUnmount, onMounted } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import ConfirmSheet from '@/Components/ConfirmSheet.vue';
import Accueil from '@/Components/terrain/Accueil.vue';
import Aide from '@/Components/terrain/Aide.vue';
import Datee from '@/Components/terrain/Datee.vue';
import Details from '@/Components/terrain/Details.vue';
import Fiche from '@/Components/terrain/Fiche.vue';
import Fin from '@/Components/terrain/Fin.vue';
import Note from '@/Components/terrain/Note.vue';
import Recap from '@/Components/terrain/Recap.vue';
import Scanner from '@/Components/terrain/Scanner.vue';
import Synchro from '@/Components/terrain/Synchro.vue';
import Tournee from '@/Components/terrain/Tournee.vue';
import Visite from '@/Components/terrain/Visite.vue';
import { useTerrain } from '@/terrain/useTerrain.js';

const { state, initialiser, arreterEcoute, confirmerChangement, annulerChangement, signalerAffichage } = useTerrain();

const VUES = { accueil: Accueil, tournee: Tournee, visite: Visite, details: Details, note: Note, aide: Aide, recap: Recap, fin: Fin, datee: Datee, synchro: Synchro, scanner: Scanner, fiche: Fiche };

const vue = computed(() => VUES[state.vue] ?? Accueil);
const sansNav = computed(() => !['accueil', 'tournee'].includes(state.vue));

const nomDe = (id) => state.donnees?.ruchers.flatMap((r) => r.ruches).find((h) => h.id === id)?.nom ?? 'Ruche';

onMounted(() => {
    // Revenue par l'historique dans le document d'une autre page, la voix n'aurait pas ses droits.
    const document_ = performance.getEntriesByType('navigation')[0]?.name;
    if (document_ && !/^\/(terrain|scan\/)/.test(new URL(document_).pathname)) {
        window.location.reload();
        return;
    }

    signalerAffichage(true);
    initialiser();
});

onBeforeUnmount(() => {
    signalerAffichage(false);
    arreterEcoute();
});
</script>

<template>
    <AppLayout title="Au rucher" :sans-nav="sansNav">
        <div class="mx-auto max-w-xl">
            <p v-if="state.erreurStockage" role="alert" class="mx-4 mt-3 rounded-2xl bg-peach p-3 font-semibold text-rust lg:mx-0">Le téléphone n'arrive plus à enregistrer. Libérez de la place, sans fermer cette page.</p>
            <p v-if="!state.pret" class="p-6 text-center text-muted" role="status">Chargement…</p>
            <component :is="vue" v-else />
            <ConfirmSheet
                v-if="state.changement"
                :titre="`${nomDe(state.changement.depuis)} est en cours`"
                :texte="`Enregistrez ce que vous avez noté avant de passer à ${nomDe(state.changement.id)}.`"
                :garder="`Revenir à ${nomDe(state.changement.depuis)}`"
                :confirmer="`Enregistrer et passer à ${nomDe(state.changement.id)}`"
                :danger="false"
                @garder="annulerChangement"
                @confirmer="confirmerChangement"
            />
        </div>
    </AppLayout>
</template>
