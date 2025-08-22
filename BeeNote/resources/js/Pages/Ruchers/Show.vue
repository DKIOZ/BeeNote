<template>
    <AppLayout :title="rucher.nom">
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ rucher.nom }}
                </h2>

                <div class="space-x-2">
                    <!-- ✅ Nouveau bouton supprimer -->
                    <button @click="confirmDelete"
                        class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                        Supprimer
                    </button>
                    <Link :href="route('ruchers.edit', rucher.id)"
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Modifier
                    </Link>
                    <Link :href="route('ruchers.index')"
                        class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Retour
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">

                    <!-- Informations du rucher -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        <!-- Informations de base -->
                        <div>
                            <h3 class="text-lg font-semibold mb-4">Informations générales</h3>

                            <div class="space-y-3">
                                <div>
                                    <span class="font-medium text-gray-700">Nom :</span>
                                    <span class="ml-2">{{ rucher.nom }}</span>
                                </div>

                                <div v-if="rucher.localisation">
                                    <span class="font-medium text-gray-700">Localisation :</span>
                                    <span class="ml-2">{{ rucher.localisation }}</span>
                                </div>

                                <div v-if="rucher.description">
                                    <span class="font-medium text-gray-700">Description :</span>
                                    <p class="ml-2 mt-1 text-gray-600">{{ rucher.description }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Coordonnées GPS -->
                        <div v-if="rucher.latitude || rucher.longitude">
                            <h3 class="text-lg font-semibold mb-4">Coordonnées GPS</h3>

                            <div class="space-y-3">
                                <div v-if="rucher.latitude">
                                    <span class="font-medium text-gray-700">Latitude :</span>
                                    <span class="ml-2">{{ rucher.latitude }}°</span>
                                </div>

                                <div v-if="rucher.longitude">
                                    <span class="font-medium text-gray-700">Longitude :</span>
                                    <span class="ml-2">{{ rucher.longitude }}°</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Placeholder pour futures fonctionnalités -->
                    <div class="mt-8 border-t pt-6">
                        <h3 class="text-lg font-semibold mb-4">Ruches de ce rucher</h3>
                        <p class="text-gray-500 italic">
                            Fonctionnalité à venir : liste des ruches de ce rucher
                        </p>
                    </div>

                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';

// ✅ Une seule déclaration de props
const props = defineProps({
    rucher: Object
});

// Confirmation de suppression
function confirmDelete() {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce rucher ? Cette action est irréversible.')) {
        router.delete(route('ruchers.destroy', props.rucher.id));
    }
}
</script>