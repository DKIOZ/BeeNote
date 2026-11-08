<script setup>
import { computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import BackBar from '@/Components/BackBar.vue';
import VisiteForm from '@/Components/VisiteForm.vue';
import { TOUS_LES_GROUPES } from '@/terrain/fields.js';

const props = defineProps({ ruchers: Array, rucheId: Number, aujourdhui: String });

// Aucune ruche choisie d'office : on choisit celle qu'on a visitée.
const premiere = computed(() => props.rucheId ?? null);

const form = useForm({
    ruche_id: premiere.value,
    date_visite: props.aujourdhui,
    prochaine_visite_prevue: '',
    ...Object.fromEntries(TOUS_LES_GROUPES.map((g) => [g.id, g.id === 'type_nourrissement' ? '' : 'non_observe'])),
    nourrissement_effectue: false,
    traitement_applique: false,
    hausse_ajoutee: false,
    hausse_retiree: false,
    actions_prevues: '',
    notes_generales: '',
});

function enregistrer() {
    form.transform((data) => ({
        ...data,
        prochaine_visite_prevue: data.prochaine_visite_prevue || null,
        type_nourrissement: data.type_nourrissement || null,
    })).post(route('visites.store'));
}
</script>

<template>
    <AppLayout title="Noter une visite déjà faite" sans-nav>
        <BackBar :href="route('dashboard')" titre="Noter une visite déjà faite" sous="Au rucher, notez plutôt sur le téléphone, à la voix ou au toucher." />

        <form class="px-4 pb-6 pt-2 lg:rounded-card lg:bg-white lg:p-6 lg:shadow-card" @submit.prevent="enregistrer">
            <VisiteForm :form="form" :ruchers="ruchers" :max="aujourdhui" />
            <div class="mt-5 flex justify-end">
                <button type="submit" class="bn-btn" :disabled="form.processing || !form.ruche_id">Enregistrer la visite</button>
            </div>
        </form>
    </AppLayout>
</template>
