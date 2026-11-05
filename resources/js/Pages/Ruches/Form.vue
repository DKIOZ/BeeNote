<script setup>
import { ref } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import BackBar from '@/Components/BackBar.vue';
import ConfirmSheet from '@/Components/ConfirmSheet.vue';
import Field from '@/Components/Field.vue';

const props = defineProps({
    rucher: Object,
    ruche: { type: Object, default: null },
    suggestion: { type: String, default: '' },
    peutSupprimer: { type: Boolean, default: false },
});

const COULEURS = [['Bleu', '#2F6DB5'], ['Jaune', '#E8A400'], ['Vert', '#6DB58A'], ['Gris', '#6E7A73'], ['Orange', '#C2570F']];

const form = useForm({
    nom: props.ruche?.nom ?? props.suggestion,
    couleur: props.ruche?.couleur ?? '#2F6DB5',
    type: props.ruche?.type ?? 'dadant',
    statut: props.ruche?.statut ?? 'active',
    annee_reine: props.ruche?.annee_reine ?? '',
    couleur_marquage_reine: props.ruche?.couleur_marquage_reine ?? '',
    date_installation: props.ruche?.date_installation?.slice(0, 10) ?? '',
    notes: props.ruche?.notes ?? '',
});

const confirmation = ref(false);

function enregistrer() {
    form.transform((data) => ({
        ...data,
        annee_reine: data.annee_reine || null,
        couleur_marquage_reine: data.couleur_marquage_reine || null,
        date_installation: data.date_installation || null,
    }));

    if (props.ruche) {
        form.put(route('ruches.update', props.ruche.id));
    } else {
        form.post(route('ruches.store', props.rucher.id));
    }
}
</script>

<template>
    <AppLayout :title="ruche ? 'Modifier la ruche' : 'Ajouter une ruche'" sans-nav>
        <BackBar :href="ruche ? route('ruches.show', ruche.id) : route('ruchers.show', rucher.id)" :titre="ruche ? 'Modifier la ruche' : 'Ajouter une ruche'" :sous="rucher.nom" />

        <form class="grid gap-4 px-4 pb-6 pt-2 lg:grid-cols-2 lg:rounded-card lg:bg-white lg:p-6 lg:shadow-card" @submit.prevent="enregistrer">
            <Field id="nom" v-model="form.nom" label="Nom" :erreur="form.errors.nom" required />

            <fieldset class="m-0 border-0 p-0">
                <legend class="bn-label p-0">Couleur de la ruche</legend>
                <div class="flex gap-2">
                    <button
                        v-for="[nom, valeur] in COULEURS"
                        :key="valeur"
                        type="button"
                        :aria-label="nom"
                        :aria-pressed="form.couleur === valeur"
                        class="flex h-[60px] w-[60px] items-center justify-center rounded-2xl border-[3px] lg:h-11 lg:w-11 lg:rounded-xl"
                        :class="form.couleur === valeur ? 'border-forest' : 'border-transparent'"
                        @click="form.couleur = valeur"
                    >
                        <svg aria-hidden="true" width="44" height="48" viewBox="0 0 48 52" class="lg:h-7 lg:w-7"><path d="M24 4l19 11v22L24 48 5 37V15z" :fill="valeur" :stroke="valeur" stroke-width="5" stroke-linejoin="round" /></svg>
                    </button>
                </div>
            </fieldset>

            <Field id="type" v-model="form.type" label="Type de ruche" :erreur="form.errors.type" :options="[{ value: 'dadant', text: 'Dadant' }, { value: 'langstroth', text: 'Langstroth' }, { value: 'warre', text: 'Warré' }, { value: 'ktbh', text: 'Kényane' }, { value: 'autre', text: 'Autre' }]" />
            <Field id="statut" v-model="form.statut" label="État" :erreur="form.errors.statut" :options="[{ value: 'active', text: 'Active' }, { value: 'inactive', text: 'Inactive' }, { value: 'morte', text: 'Morte' }]" />
            <Field id="reine" v-model="form.annee_reine" label="Année de la reine" inputmode="numeric" placeholder="2025" :erreur="form.errors.annee_reine" />
            <Field id="marquage" v-model="form.couleur_marquage_reine" label="Marquage de la reine" :erreur="form.errors.couleur_marquage_reine" :options="[{ value: '', text: 'Pas de marquage' }, { value: 'blanc', text: 'Blanc' }, { value: 'jaune', text: 'Jaune' }, { value: 'rouge', text: 'Rouge' }, { value: 'vert', text: 'Vert' }, { value: 'bleu', text: 'Bleu' }]" />
            <Field id="installation" v-model="form.date_installation" type="date" label="Date d'installation" :erreur="form.errors.date_installation" />

            <div class="flex flex-col gap-2 lg:col-span-2 lg:flex-row lg:items-center lg:justify-between">
                <button v-if="ruche && peutSupprimer" type="button" class="bn-btn-danger order-2 lg:order-1" @click="confirmation = true">Mettre à la corbeille</button>
                <span v-else class="hidden lg:block" />
                <button type="submit" class="bn-btn order-1 lg:order-2" :disabled="form.processing">Enregistrer la ruche</button>
            </div>
        </form>

        <ConfirmSheet
            v-if="confirmation"
            titre="Mettre cette ruche à la corbeille ?"
            texte="Elle disparaîtra du rucher et du registre, avec ses visites et traitements. Passé 30 jours dans la corbeille, tout sera effacé définitivement."
            garder="Garder la ruche"
            confirmer="Mettre à la corbeille"
            @garder="confirmation = false"
            @confirmer="router.delete(route('ruches.destroy', ruche.id))"
        />
    </AppLayout>
</template>
