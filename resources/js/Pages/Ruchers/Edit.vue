<script setup>
import { ref } from 'vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import BackBar from '@/Components/BackBar.vue';
import ConfirmSheet from '@/Components/ConfirmSheet.vue';
import Field from '@/Components/Field.vue';

const props = defineProps({ rucher: Object, peutSupprimer: Boolean });

const form = useForm({
    nom: props.rucher.nom,
    localisation: props.rucher.localisation,
    latitude: props.rucher.latitude,
    longitude: props.rucher.longitude,
    description: props.rucher.description,
});
const positionMessage = ref(props.rucher.latitude ? 'Position enregistrée : elle sert à ajouter la météo aux visites.' : null);
const confirmation = ref(false);

function position() {
    if (!navigator.geolocation) {
        positionMessage.value = 'Votre appareil ne donne pas sa position.';
        return;
    }

    positionMessage.value = 'Recherche de la position…';
    navigator.geolocation.getCurrentPosition(
        ({ coords }) => {
            form.latitude = Number(coords.latitude.toFixed(6));
            form.longitude = Number(coords.longitude.toFixed(6));
            positionMessage.value = 'Position trouvée. Enregistrez pour la garder.';
        },
        () => { positionMessage.value = 'Position non disponible.'; },
        { timeout: 10000 },
    );
}

const supprimer = () => router.delete(route('ruchers.destroy', props.rucher.id));
</script>

<template>
    <AppLayout title="Modifier le rucher" sans-nav>
        <BackBar :href="route('ruchers.show', rucher.id)" titre="Modifier le rucher" retour="Retour au rucher" />

        <form class="flex max-w-xl flex-col gap-4 px-4 pb-6 pt-3 lg:px-0" @submit.prevent="form.put(route('ruchers.update', rucher.id))">
            <Field id="nom" v-model="form.nom" label="Nom du rucher" :erreur="form.errors.nom" required />

            <div class="rounded-card bg-mint p-4">
                <p v-if="positionMessage" class="mb-2.5 font-semibold text-mint-ink" role="status">{{ positionMessage }}</p>
                <button type="button" class="bn-btn-2" @click="position">Utiliser ma position actuelle</button>
            </div>

            <Link :href="route('etiquettes.index')" class="bn-btn-2">Imprimer les QR codes</Link>
            <button type="submit" class="bn-btn" :disabled="form.processing">Enregistrer</button>
            <button v-if="peutSupprimer" type="button" class="bn-btn-danger" @click="confirmation = true">Mettre le rucher à la corbeille</button>
        </form>

        <ConfirmSheet
            v-if="confirmation"
            titre="Mettre ce rucher à la corbeille ?"
            texte="Ses ruches partent avec lui. Visites et récoltes seront effacées au bout de 30 jours. Traitements et déplacements restent au registre."
            garder="Garder le rucher"
            confirmer="Mettre à la corbeille"
            @garder="confirmation = false"
            @confirmer="supprimer"
        />
    </AppLayout>
</template>
