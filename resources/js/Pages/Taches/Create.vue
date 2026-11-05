<script setup>
import { computed, ref } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import BackBar from '@/Components/BackBar.vue';
import Field from '@/Components/Field.vue';
import { ajouterSemaines } from '@/terrain/fields.js';

const props = defineProps({ ruchers: Array, rucheId: Number, aujourdhui: String });

const choix = computed(() => props.ruchers.flatMap((rucher) => [
    { value: `rucher:${rucher.id}`, text: `Tout le rucher : ${rucher.nom}` },
    ...rucher.ruches.map((ruche) => ({ value: `ruche:${ruche.id}`, text: `${ruche.nom} · ${rucher.nom}` })),
]));

const ou = ref(props.rucheId ? `ruche:${props.rucheId}` : choix.value[0]?.value ?? '');
const form = useForm({ titre: '', date_prevue: ajouterSemaines(props.aujourdhui, 1), ruche_id: null, rucher_id: null });

function enregistrer() {
    const [type, id] = ou.value.split(':');
    form.ruche_id = type === 'ruche' ? Number(id) : null;
    form.rucher_id = type === 'rucher' ? Number(id) : null;
    form.post(route('taches.store'));
}
</script>

<template>
    <AppLayout title="Ajouter une tâche" sans-nav>
        <BackBar :href="route('dashboard')" titre="Ajouter une tâche" />

        <div v-if="!choix.length" class="bn-card mx-4 mt-3 p-5 lg:mx-0">
            <p class="font-semibold">Une tâche se rattache à un rucher.</p>
            <p class="mt-1 text-muted">Créez-en un d'abord.</p>
            <Link :href="route('ruchers.create')" class="bn-btn mt-3">Créer mon rucher</Link>
        </div>

        <form v-else class="flex max-w-xl flex-col gap-4 px-4 pb-6 pt-3 lg:rounded-card lg:bg-white lg:p-6 lg:shadow-card" @submit.prevent="enregistrer">
            <Field id="titre" v-model="form.titre" label="À faire" placeholder="Ex. : contrôler la reine" :erreur="form.errors.titre" required />
            <Field id="ou" v-model="ou" label="Où" :options="choix" :erreur="form.errors.ruche_id" />

            <fieldset class="m-0 border-0 p-0">
                <legend class="bn-label p-0">Quand</legend>
                <div class="grid grid-cols-3 gap-2">
                    <button v-for="n in [1, 2, 3]" :key="n" type="button" class="min-h-[60px] rounded-2xl border-[1.5px] px-1 font-semibold" :aria-pressed="form.date_prevue === ajouterSemaines(aujourdhui, n)" :class="form.date_prevue === ajouterSemaines(aujourdhui, n) ? 'border-forest bg-forest text-white' : 'border-line bg-white'" @click="form.date_prevue = ajouterSemaines(aujourdhui, n)">Dans {{ n }} {{ n > 1 ? 'semaines' : 'semaine' }}</button>
                </div>
            </fieldset>
            <Field id="date" v-model="form.date_prevue" type="date" label="Autre date" :erreur="form.errors.date_prevue" required />

            <button type="submit" class="bn-btn" :disabled="form.processing || !choix.length">Ajouter la tâche</button>
        </form>
    </AppLayout>
</template>
