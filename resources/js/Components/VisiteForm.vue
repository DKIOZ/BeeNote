<script setup>
import { computed } from 'vue';
import Field from '@/Components/Field.vue';
import { ACTIONS, DETAILS, FAMILLES } from '@/terrain/fields.js';

const props = defineProps({
    form: { type: Object, required: true },
    ruchers: { type: Array, required: true },
    max: { type: String, required: true },
});

const groupes = computed(() => [...FAMILLES.flatMap((famille) => famille.groupes), ...DETAILS]);
const ruches = computed(() => props.ruchers.flatMap((rucher) => rucher.ruches.map((ruche) => ({
    value: ruche.id,
    text: `${ruche.nom} · ${rucher.nom}`,
}))));
const options = (groupe) => [{ value: 'non_observe', text: 'Pas noté' }, ...groupe.options.map((o) => ({ value: o.value, text: o.text }))];
const optionsNourriture = (groupe) => [{ value: '', text: 'Pas noté' }, ...groupe.options.map((o) => ({ value: o.value, text: o.text }))];
</script>

<template>
    <div class="grid gap-4 lg:grid-cols-3">
        <Field id="ruche_id" v-model="form.ruche_id" label="Ruche" :options="ruches" :erreur="form.errors.ruche_id" />
        <Field id="date_visite" v-model="form.date_visite" type="date" label="Date" :max="max" :erreur="form.errors.date_visite" required />
        <Field id="prochaine" v-model="form.prochaine_visite_prevue" type="date" label="Prochaine visite" :erreur="form.errors.prochaine_visite_prevue" />

        <Field
            v-for="groupe in groupes"
            :id="groupe.id"
            :key="groupe.id"
            v-model="form[groupe.id]"
            :label="groupe.aide ? `${groupe.label} · ${groupe.aide}` : groupe.label"
            :options="groupe.id === 'type_nourrissement' ? optionsNourriture(groupe) : options(groupe)"
            :erreur="form.errors[groupe.id]"
        />

        <fieldset class="m-0 border-0 p-0 lg:col-span-3">
            <legend class="bn-label p-0">J'ai fait</legend>
            <div class="grid grid-cols-2 gap-2 lg:grid-cols-4">
                <label v-for="action in ACTIONS" :key="action.id" class="flex min-h-[56px] cursor-pointer items-center gap-3 rounded-2xl border-[1.5px] border-line bg-white px-3 font-semibold lg:min-h-[44px]">
                    <input v-model="form[action.id]" type="checkbox" class="h-6 w-6 rounded border-line text-forest focus:ring-honey">
                    {{ action.text }}
                </label>
            </div>
        </fieldset>

        <div class="lg:col-span-3">
            <Field id="actions_prevues" v-model="form.actions_prevues" type="textarea" label="À faire ensuite" :erreur="form.errors.actions_prevues" />
        </div>
        <div class="lg:col-span-3">
            <Field id="notes_generales" v-model="form.notes_generales" type="textarea" label="Notes" :erreur="form.errors.notes_generales" />
        </div>
    </div>
</template>
