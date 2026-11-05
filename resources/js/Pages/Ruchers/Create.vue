<script setup>
import { computed, ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import BackBar from '@/Components/BackBar.vue';
import Field from '@/Components/Field.vue';
import Hex from '@/Components/Hex.vue';

const form = useForm({ nom: '', nombre_ruches: 3, latitude: null, longitude: null });
const positionMessage = ref(null);

const alveoles = computed(() => Math.min(form.nombre_ruches, 12));
const changer = (delta) => { form.nombre_ruches = Math.max(1, Math.min(50, form.nombre_ruches + delta)); };

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
            positionMessage.value = 'Position enregistrée.';
        },
        () => { positionMessage.value = 'Position non disponible. Vous pourrez l\'ajouter plus tard.'; },
        { timeout: 10000 },
    );
}
</script>

<template>
    <AppLayout title="Créer un rucher" sans-nav>
        <BackBar :href="route('ruchers.index')" titre="Où sont vos ruches ?" sous="Deux informations suffisent pour commencer." retour="Retour aux ruchers" />

        <form class="flex max-w-xl flex-col gap-5 px-4 pb-6 pt-3 lg:px-0" @submit.prevent="form.post(route('ruchers.store'))">
            <Field id="nom" v-model="form.nom" label="Nom du rucher" placeholder="Ex. : Le champ" :erreur="form.errors.nom" required />

            <div>
                <p id="nombre" class="bn-label">Nombre de ruches</p>
                <div role="group" aria-labelledby="nombre" class="flex items-center justify-between rounded-2xl border-[1.5px] border-line bg-white p-2">
                    <button type="button" aria-label="Une ruche de moins" class="h-16 w-16 rounded-xl bg-soft text-[28px] font-semibold text-forest" @click="changer(-1)">−</button>
                    <span class="bn-title text-4xl" aria-live="polite">{{ form.nombre_ruches }}</span>
                    <button type="button" aria-label="Une ruche de plus" class="h-16 w-16 rounded-xl bg-soft text-[28px] font-semibold text-forest" @click="changer(1)">+</button>
                </div>
                <div aria-hidden="true" class="mt-3 flex flex-wrap gap-0.5">
                    <Hex v-for="n in alveoles" :key="n" etat="todo" :mark="n" :size="52" />
                </div>
                <p v-if="form.errors.nombre_ruches" class="mt-1 text-sm font-semibold text-rust">{{ form.errors.nombre_ruches }}</p>
            </div>

            <div>
                <button type="button" class="inline-flex min-h-[56px] w-full items-center justify-center rounded-2xl bg-mint font-bold text-mint-ink" @click="position">Ajouter ma position, pour la météo</button>
                <p v-if="positionMessage" class="mt-1.5 text-muted" role="status">{{ positionMessage }}</p>
            </div>

            <button type="submit" class="bn-btn" :disabled="form.processing">Créer le rucher</button>
        </form>
    </AppLayout>
</template>
