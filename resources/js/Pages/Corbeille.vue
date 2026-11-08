<script setup>
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import BackBar from '@/Components/BackBar.vue';
import { dateCourte } from '@/terrain/fields.js';

defineProps({ items: Array, jours: Number });

const remettre = (item) => router.post(route('corbeille.restore', [item.type, item.id]), {}, { preserveScroll: true });
</script>

<template>
    <AppLayout title="Corbeille" sans-nav>
        <BackBar :href="route('plus')" titre="Corbeille" />
        <p class="mx-5 mb-3 text-muted lg:mx-0">Vous pouvez remettre ces éléments pendant {{ jours }} jours. Ensuite, visites et récoltes sont supprimées pour de bon. Les traitements et déplacements d'une ruche à la corbeille restent toujours au registre d'élevage. Un traitement mis lui-même à la corbeille sort du registre, mais n'est jamais effacé : vous pouvez toujours le remettre.</p>

        <p v-if="!items.length" class="bn-card mx-4 p-5 text-muted lg:mx-0">La corbeille est vide.</p>

        <div class="flex max-w-xl flex-col gap-2.5 px-4 lg:px-0">
            <div v-for="item in items" :key="`${item.type}-${item.id}`" class="bn-card p-3.5">
                <p class="text-lg font-semibold">{{ item.titre }} · {{ item.detail }}</p>
                <p class="mb-2.5 text-sm text-muted">Supprimé le {{ dateCourte(item.supprime_le) }}</p>
                <button type="button" class="bn-btn-2" @click="remettre(item)">Remettre</button>
            </div>
        </div>
    </AppLayout>
</template>
