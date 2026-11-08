<script setup>
import { ref } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import BackBar from '@/Components/BackBar.vue';
import ConfirmSheet from '@/Components/ConfirmSheet.vue';
import VisiteForm from '@/Components/VisiteForm.vue';
import { TOUS_LES_GROUPES, dateCourte, jourLocal } from '@/terrain/fields.js';

const props = defineProps({ visite: Object, ruchers: Array, modifiee: String, peutSupprimer: Boolean });

const v = props.visite;
const form = useForm({
    ruche_id: v.ruche_id,
    date_visite: v.date_visite,
    prochaine_visite_prevue: v.prochaine_visite_prevue ?? '',
    ...Object.fromEntries(TOUS_LES_GROUPES.map((g) => [g.id, v[g.id] ?? (g.id === 'type_nourrissement' ? '' : 'non_observe')])),
    nourrissement_effectue: !!v.nourrissement_effectue,
    traitement_applique: !!v.traitement_applique,
    hausse_ajoutee: !!v.hausse_ajoutee,
    hausse_retiree: !!v.hausse_retiree,
    actions_prevues: v.actions_prevues ?? '',
    notes_generales: v.notes_generales ?? '',
});

const confirmation = ref(false);
const aujourdhui = jourLocal();

const meteo = () => {
    const noms = { ensoleille: 'ensoleillé', nuageux: 'nuageux', pluvieux: 'pluvieux', orageux: 'orageux' };

    return v.meteo ? `${v.temperature ?? '?'}°, ${noms[v.meteo]}` : null;
};

function enregistrer() {
    form.transform((data) => ({
        ...data,
        prochaine_visite_prevue: data.prochaine_visite_prevue || null,
        type_nourrissement: data.type_nourrissement || null,
    })).put(route('visites.update', v.id));
}

const sous = () => [v.ruche.nom, meteo(), props.modifiee && `Modifiée le ${props.modifiee}`].filter(Boolean).join(' · ');
</script>

<template>
    <AppLayout :title="`Visite du ${dateCourte(v.date_visite)}`" sans-nav>
        <BackBar :href="route('ruches.show', v.ruche_id)" :titre="`Visite du ${dateCourte(v.date_visite)}`" :sous="sous()" retour="Retour à la ruche" />

        <div v-for="note in v.notes_audio" :key="note.numero" class="mx-4 mb-3 flex items-center gap-3 rounded-2xl bg-cream p-3 text-cream-ink lg:mx-0">
            <span class="font-semibold">Note vocale<template v-if="v.notes_audio.length > 1"> {{ note.numero + 1 }}</template><template v-if="note.duree"> · {{ note.duree }} s</template></span>
            <audio controls preload="none" class="min-w-0 flex-1" :src="route('visites.note', { visite: v.id, numero: note.numero })" />
        </div>

        <form class="px-4 pb-6 pt-2 lg:rounded-card lg:bg-white lg:p-6 lg:shadow-card" @submit.prevent="enregistrer">
            <VisiteForm :form="form" :ruchers="ruchers" :max="aujourdhui" />
            <div class="mt-5 flex flex-col gap-2 lg:flex-row lg:items-center lg:justify-between">
                <button v-if="peutSupprimer" type="button" class="bn-btn-danger order-2 lg:order-1" @click="confirmation = true">Mettre à la corbeille</button>
                <span v-else class="hidden lg:block" />
                <button type="submit" class="bn-btn order-1 lg:order-2" :disabled="form.processing">Enregistrer les corrections</button>
            </div>
        </form>

        <ConfirmSheet
            v-if="confirmation"
            titre="Mettre cette visite à la corbeille ?"
            texte="Elle disparaîtra du registre. Vous pourrez la remettre depuis la corbeille."
            garder="Garder la visite"
            confirmer="Mettre à la corbeille"
            @garder="confirmation = false"
            @confirmer="router.delete(route('visites.destroy', v.id))"
        />
    </AppLayout>
</template>
