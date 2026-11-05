<script setup>
import { jourLocal } from '@/terrain/fields.js';
import { computed, ref } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import BackBar from '@/Components/BackBar.vue';
import ConfirmSheet from '@/Components/ConfirmSheet.vue';
import Field from '@/Components/Field.vue';

const props = defineProps({ type: String, fiche: Object, modifiee: String, peutSupprimer: Boolean });

const TITRES = { traitement: 'Traitement', recolte: 'Récolte', deplacement: 'Déplacement' };
const f = props.fiche;

const form = useForm(
    props.type === 'traitement'
        ? { date_traitement: f.date_traitement, type_traitement: f.type_traitement, produit_utilise: f.produit_utilise, dosage: f.dosage ?? '', numero_lot: f.numero_lot ?? '', notes: f.notes ?? '' }
        : props.type === 'recolte'
            ? { date_recolte: f.date_recolte, type_miel: f.type_miel, quantite_kg: f.quantite_kg }
            : { date_deplacement: f.date_deplacement, notes: f.notes ?? '' },
);

const aujourdhui = jourLocal();
const confirmation = ref(false);
const sous = computed(() => [f.ruche?.nom, props.modifiee && `Modifié le ${props.modifiee}`].filter(Boolean).join(' · '));
</script>

<template>
    <AppLayout :title="TITRES[type]" sans-nav>
        <BackBar :href="route('ruches.show', f.ruche_id)" :titre="TITRES[type]" :sous="sous" retour="Retour à la ruche" />

        <form class="flex max-w-xl flex-col gap-3.5 px-4 pb-6 pt-3 lg:px-0" @submit.prevent="form.put(route('fiches.update', [type, f.id]))">
            <template v-if="type === 'traitement'">
                <Field id="produit" v-model="form.produit_utilise" label="Produit" :erreur="form.errors.produit_utilise" required />
                <Field id="contre" v-model="form.type_traitement" label="Contre" :options="[{ value: 'varroa', text: 'Varroa' }, { value: 'nosema', text: 'Nosémose' }, { value: 'loque', text: 'Loque' }, { value: 'preventif', text: 'Préventif' }, { value: 'frelon_asiatique', text: 'Frelon asiatique' }, { value: 'autre', text: 'Autre' }]" :erreur="form.errors.type_traitement" />
                <Field id="dose" v-model="form.dosage" label="Dose" :erreur="form.errors.dosage" />
                <Field id="lot" v-model="form.numero_lot" label="Numéro de lot" :erreur="form.errors.numero_lot" />
                <Field id="date" v-model="form.date_traitement" type="date" label="Date" :max="aujourdhui" :erreur="form.errors.date_traitement" required />
            </template>
            <template v-else-if="type === 'recolte'">
                <Field id="poids" v-model="form.quantite_kg" type="number" step="0.01" min="0.01" inputmode="decimal" label="Poids en kg" :erreur="form.errors.quantite_kg" required />
                <Field id="miel" v-model="form.type_miel" label="Type de miel" :options="[{ value: 'toutes_fleurs', text: 'Toutes fleurs' }, { value: 'acacia', text: 'Acacia' }, { value: 'tilleul', text: 'Tilleul' }, { value: 'chataignier', text: 'Châtaignier' }, { value: 'lavande', text: 'Lavande' }, { value: 'autre', text: 'Autre' }]" :erreur="form.errors.type_miel" />
                <Field id="date" v-model="form.date_recolte" type="date" label="Date" :max="aujourdhui" :erreur="form.errors.date_recolte" required />
            </template>
            <template v-else>
                <p class="bn-card p-4 font-semibold">{{ f.depuis?.nom ?? '?' }} vers {{ f.vers?.nom ?? '?' }}</p>
                <Field id="date" v-model="form.date_deplacement" type="date" label="Date" :max="aujourdhui" :erreur="form.errors.date_deplacement" required />
                <Field id="notes" v-model="form.notes" type="textarea" label="Notes" :erreur="form.errors.notes" />
            </template>

            <button type="submit" class="bn-btn mt-1" :disabled="form.processing">Enregistrer</button>
            <button v-if="peutSupprimer" type="button" class="bn-btn-danger" @click="confirmation = true">Mettre à la corbeille</button>
        </form>

        <ConfirmSheet
            v-if="confirmation"
            titre="Mettre à la corbeille ?"
            :texte="type === 'deplacement' ? 'La ruche reviendra dans le rucher d\'où elle est partie. Vous pourrez refaire ce déplacement depuis la corbeille.' : 'Cet élément disparaîtra du registre. Vous pourrez le remettre depuis la corbeille.'"
            garder="Garder"
            confirmer="Mettre à la corbeille"
            @garder="confirmation = false"
            @confirmer="router.delete(route('fiches.destroy', [type, f.id]))"
        />
    </AppLayout>
</template>
