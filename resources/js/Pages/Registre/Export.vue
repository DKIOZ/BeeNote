<script setup>
import { ajouterJours, jourLocal } from '@/terrain/fields.js';
import { computed, onMounted, ref } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import BackBar from '@/Components/BackBar.vue';
import Field from '@/Components/Field.vue';
import { pendingCount } from '@/terrain/store.js';

const props = defineProps({ ruchers: Array, annee: Number });

const auj = jourLocal();
const ilYaUnAn = ajouterJours(auj, -365);

const depuis = ref(ilYaUnAn);
const jusqu = ref(auj);
const rucher = ref('');
const format = ref('pdf');
const attente = ref(0);

onMounted(async () => { attente.value = await pendingCount().catch(() => 0); });

const url = computed(() => route('registre.export', {
    format: format.value,
    depuis: depuis.value,
    jusqu: jusqu.value,
    ...(rucher.value ? { rucher_id: rucher.value } : {}),
}));
</script>

<template>
    <AppLayout title="Télécharger le registre" sans-nav>
        <BackBar :href="route('registre.index')" titre="Télécharger le registre" retour="Retour au registre" />

        <div class="flex max-w-xl flex-col gap-4 px-4 pb-6 pt-3 lg:px-0">
            <div class="grid grid-cols-2 gap-2">
                <Field id="depuis" v-model="depuis" type="date" label="Du" :erreur="$page.props.errors.depuis" />
                <Field id="jusqu" v-model="jusqu" type="date" label="Au" :min="depuis" :erreur="$page.props.errors.jusqu || (jusqu < depuis ? 'La date de fin doit être après la date de début.' : null)" />
            </div>
            <Field id="rucher" v-model="rucher" label="Ruchers" :options="[{ value: '', text: 'Tous les ruchers' }, ...ruchers.map((r) => ({ value: r.id, text: r.nom }))]" />

            <fieldset class="m-0 border-0 p-0">
                <legend class="bn-label p-0">Format</legend>
                <div class="grid grid-cols-2 gap-2">
                    <button v-for="[valeur, texte] in [['pdf', 'PDF à imprimer'], ['csv', 'Tableur']]" :key="valeur" type="button" :aria-pressed="format === valeur" class="min-h-[60px] rounded-2xl border-[1.5px] font-semibold" :class="format === valeur ? 'border-forest bg-forest text-white' : 'border-line bg-white'" @click="format = valeur">{{ texte }}</button>
                </div>
            </fieldset>

            <p v-if="attente" class="rounded-2xl bg-cream p-3.5 text-cream-ink">{{ attente }} {{ attente > 1 ? 'visites ne sont' : 'visite n\'est' }} pas encore {{ attente > 1 ? 'envoyées' : 'envoyée' }} : absente{{ attente > 1 ? 's' : '' }} de ce registre.</p>

            <p v-if="$page.props.errors.format" role="alert" class="rounded-2xl bg-peach p-3 font-semibold text-rust">{{ $page.props.errors.format }}</p>
            <a :href="jusqu < depuis ? undefined : url" :aria-disabled="jusqu < depuis" class="bn-btn" :class="jusqu < depuis && 'pointer-events-none bg-soft text-muted shadow-none'">Télécharger</a>
            <p class="text-sm text-muted">Le téléchargement demande du réseau.</p>
        </div>
    </AppLayout>
</template>
