<script setup>
import { computed, ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import BackBar from '@/Components/BackBar.vue';
import Field from '@/Components/Field.vue';

const props = defineProps({
    type: String,
    rucheId: Number,
    visiteId: Number,
    ruchers: Array,
    aujourdhui: String,
});

const TYPES = [['traitement', 'Traitement'], ['recolte', 'Récolte'], ['deplacement', 'Déplacement']];
const type = ref(props.type);

const toutes = computed(() => props.ruchers.flatMap((r) => r.ruches));
const rucherDeLaRuche = computed(() => props.ruchers.find((r) => r.ruches.some((h) => h.id === props.rucheId)) ?? props.ruchers[0]);
const rucherChoisi = ref(rucherDeLaRuche.value?.id ?? null);
const ruches = computed(() => props.ruchers.find((r) => r.id === rucherChoisi.value)?.ruches ?? []);

const form = useForm({
    ruche_ids: props.rucheId ? [props.rucheId] : [],
    visite_id: props.visiteId,
    date_traitement: props.aujourdhui,
    type_traitement: 'varroa',
    produit_utilise: '',
    dosage: '',
    numero_lot: '',
    date_recolte: props.aujourdhui,
    type_miel: 'toutes_fleurs',
    quantite_kg: '',
    date_deplacement: props.aujourdhui,
    vers_rucher_id: props.ruchers.find((r) => r.id !== rucherDeLaRuche.value?.id)?.id ?? null,
    notes: '',
});

const bascule = (id) => {
    const liste = form.ruche_ids;
    const position = liste.indexOf(id);
    if (position === -1) {
        liste.push(id);
    } else {
        liste.splice(position, 1);
    }
};
const toutesCochees = computed(() => ruches.value.length > 0 && ruches.value.every((h) => form.ruche_ids.includes(h.id)));
const basculeToutes = () => {
    form.ruche_ids = toutesCochees.value ? [] : ruches.value.map((h) => h.id);
};

const nombre = computed(() => form.ruche_ids.length);
const verbe = computed(() => ({ traitement: 'Noter le traitement', recolte: 'Noter la récolte', deplacement: 'Déplacer' }[type.value]));
const retour = computed(() => (props.rucheId ? route('ruches.show', props.rucheId) : route('dashboard')));

const CHAMPS = {
    traitement: ['date_traitement', 'type_traitement', 'produit_utilise', 'dosage', 'numero_lot', 'notes'],
    recolte: ['date_recolte', 'type_miel', 'quantite_kg'],
    deplacement: ['date_deplacement', 'vers_rucher_id', 'notes'],
};

function enregistrer() {
    form.transform((data) => {
        const envoi = { ruche_ids: data.ruche_ids, visite_id: data.visite_id };
        CHAMPS[type.value].forEach((champ) => { envoi[champ] = data[champ]; });

        return envoi;
    }).post(route('noter.store', type.value));
}
</script>

<template>
    <AppLayout title="Noter" sans-nav>
        <BackBar :href="retour" titre="Noter" />

        <div class="max-w-2xl">
            <div role="group" aria-label="Type de saisie" class="mx-4 grid grid-cols-3 gap-1 rounded-[18px] bg-soft p-1 lg:mx-0">
                <button
                    v-for="[valeur, texte] in TYPES"
                    :key="valeur"
                    type="button"
                    :aria-pressed="type === valeur"
                    class="min-h-[50px] rounded-[14px] text-sm font-semibold"
                    :class="type === valeur ? 'bg-forest text-white' : 'text-forest'"
                    @click="type = valeur"
                >{{ texte }}</button>
            </div>

            <form class="flex flex-col gap-3.5 px-4 pb-6 pt-3.5 lg:px-0" @submit.prevent="enregistrer">
                <fieldset class="m-0 border-0 p-0">
                    <legend class="bn-label p-0">Pour quelles ruches</legend>
                    <Field v-if="ruchers.length > 1" id="rucher" v-model="rucherChoisi" class="mb-2" label="Rucher" :options="ruchers.map((r) => ({ value: r.id, text: r.nom }))" />
                    <div class="grid grid-cols-3 gap-2 lg:grid-cols-5">
                        <button
                            v-for="ruche in ruches"
                            :key="ruche.id"
                            type="button"
                            :aria-pressed="form.ruche_ids.includes(ruche.id)"
                            class="min-h-[56px] rounded-2xl border-[1.5px] px-1 font-semibold"
                            :class="form.ruche_ids.includes(ruche.id) ? 'border-forest bg-forest text-white' : 'border-line bg-white text-ink'"
                            @click="bascule(ruche.id)"
                        >{{ ruche.nom }}</button>
                        <button type="button" :aria-pressed="toutesCochees" class="min-h-[56px] rounded-2xl border-[1.5px] font-semibold" :class="toutesCochees ? 'border-forest bg-forest text-white' : 'border-line bg-white text-ink'" @click="basculeToutes">Toutes</button>
                    </div>
                    <p v-if="form.errors.ruche_ids" class="mt-1 text-sm font-semibold text-rust">Choisissez au moins une ruche.</p>
                </fieldset>

                <template v-if="type === 'traitement'">
                    <Field id="produit" v-model="form.produit_utilise" label="Produit" placeholder="Ex. : acide oxalique" :erreur="form.errors.produit_utilise" required />
                    <Field id="type_traitement" v-model="form.type_traitement" label="Contre" :options="[{ value: 'varroa', text: 'Varroa' }, { value: 'nosema', text: 'Nosémose' }, { value: 'loque', text: 'Loque' }, { value: 'preventif', text: 'Préventif' }, { value: 'frelon_asiatique', text: 'Frelon asiatique' }, { value: 'autre', text: 'Autre' }]" :erreur="form.errors.type_traitement" />
                    <Field id="dose" v-model="form.dosage" label="Dose" placeholder="Ex. : 5 ml" :erreur="form.errors.dosage" />
                    <Field id="lot" v-model="form.numero_lot" label="Numéro de lot" :erreur="form.errors.numero_lot" />
                    <Field id="date_t" v-model="form.date_traitement" type="date" label="Date" :max="aujourdhui" :erreur="form.errors.date_traitement" required />
                    <p class="text-muted">Ces informations alimentent votre registre.</p>
                </template>

                <template v-else-if="type === 'recolte'">
                    <Field id="poids" v-model="form.quantite_kg" type="number" step="0.01" min="0.01" inputmode="decimal" :label="nombre > 1 ? 'Poids par ruche, en kg' : 'Poids en kg'" placeholder="Ex. : 14" :erreur="form.errors.quantite_kg" required />
                    <p v-if="nombre > 1 && Number(form.quantite_kg) > 0" class="-mt-2 rounded-2xl bg-cream p-3 font-semibold text-cream-ink" role="status">
                        {{ nombre }} ruches × {{ form.quantite_kg }} kg = {{ Math.round(nombre * Number(form.quantite_kg) * 100) / 100 }} kg au total
                    </p>
                    <Field id="miel" v-model="form.type_miel" label="Type de miel" :options="[{ value: 'toutes_fleurs', text: 'Toutes fleurs' }, { value: 'acacia', text: 'Acacia' }, { value: 'tilleul', text: 'Tilleul' }, { value: 'chataignier', text: 'Châtaignier' }, { value: 'lavande', text: 'Lavande' }, { value: 'autre', text: 'Autre' }]" :erreur="form.errors.type_miel" />
                    <Field id="date_r" v-model="form.date_recolte" type="date" label="Date" :max="aujourdhui" :erreur="form.errors.date_recolte" required />
                </template>

                <template v-else>
                    <Field v-if="ruchers.length > 1" id="vers" v-model="form.vers_rucher_id" label="Vers le rucher" :options="ruchers.map((r) => ({ value: r.id, text: r.nom }))" :erreur="form.errors.vers_rucher_id" />
                    <p v-else class="rounded-2xl bg-cream p-3 font-semibold text-cream-ink">Créez d'abord un autre rucher pour y déplacer une ruche.</p>
                    <Field id="date_d" v-model="form.date_deplacement" type="date" label="Date" :max="aujourdhui" :erreur="form.errors.date_deplacement" required />
                    <p class="text-muted">L'ancien emplacement reste dans l'historique.</p>
                </template>

                <button type="submit" class="bn-btn mt-1" :disabled="form.processing || !nombre || !toutes.length || (type === 'deplacement' && ruchers.length < 2)">
                    {{ verbe }}<template v-if="nombre > 1"> pour {{ nombre }} ruches</template>
                </button>
            </form>
        </div>
    </AppLayout>
</template>
