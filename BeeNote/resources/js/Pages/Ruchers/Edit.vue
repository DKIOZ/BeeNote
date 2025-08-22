<template>
    <AppLayout title="Modifier le Rucher">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Modifier le rucher : {{ rucher.nom }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">

                    <form @submit.prevent="submit">
                        <!-- Nom du rucher -->
                        <div class="mb-4">
                            <label for="nom" class="block text-sm font-medium text-gray-700">
                                Nom du rucher *
                            </label>
                            <input id="nom" v-model="form.nom" type="text"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required />
                            <div v-if="form.errors.nom" class="text-red-600 text-sm mt-1">
                                {{ form.errors.nom }}
                            </div>
                        </div>

                        <!-- Localisation avec autocomplete -->
                        <div class="mb-4">
                            <label for="localisation" class="block text-sm font-medium text-gray-700">
                                Localisation
                            </label>
                            <div class="relative">
                                <input id="localisation" v-model="form.localisation" @input="searchPlaces"
                                    @focus="showSuggestions = true" type="text"
                                    placeholder="Tapez votre ville, département..."
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    autocomplete="off" />

                                <!-- Loading indicator -->
                                <div v-if="searchLoading" class="absolute right-3 top-3">
                                    <svg class="animate-spin h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                            stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                        </path>
                                    </svg>
                                </div>

                                <!-- Suggestions dropdown -->
                                <div v-if="showSuggestions && suggestions.length > 0"
                                    class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-y-auto">
                                    <ul class="py-1">
                                        <li v-for="place in suggestions" :key="place.properties.id"
                                            @click="selectPlace(place)"
                                            class="px-4 py-2 hover:bg-gray-100 cursor-pointer text-sm">
                                            <div class="font-medium">{{ place.properties.name }}</div>
                                            <div class="text-gray-500 text-xs">
                                                {{ place.properties.context }}
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <div v-if="form.errors.localisation" class="text-red-600 text-sm mt-1">
                                {{ form.errors.localisation }}
                            </div>
                        </div>

                        <!-- Coordonnées GPS -->
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label for="latitude" class="block text-sm font-medium text-gray-700">
                                    Latitude
                                </label>
                                <input id="latitude" v-model="form.latitude" type="number" step="any"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                                <div v-if="form.errors.latitude" class="text-red-600 text-sm mt-1">
                                    {{ form.errors.latitude }}
                                </div>
                            </div>
                            <div>
                                <label for="longitude" class="block text-sm font-medium text-gray-700">
                                    Longitude
                                </label>
                                <input id="longitude" v-model="form.longitude" type="number" step="any"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                                <div v-if="form.errors.longitude" class="text-red-600 text-sm mt-1">
                                    {{ form.errors.longitude }}
                                </div>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="mb-6">
                            <label for="description" class="block text-sm font-medium text-gray-700">
                                Description
                            </label>
                            <textarea id="description" v-model="form.description" rows="3"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                            <div v-if="form.errors.description" class="text-red-600 text-sm mt-1">
                                {{ form.errors.description }}
                            </div>
                        </div>

                        <!-- Boutons -->
                        <div class="flex items-center justify-end space-x-4">
                            <Link :href="route('ruchers.show', rucher.id)"
                                class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                            Annuler
                            </Link>
                            <button type="submit" :disabled="form.processing"
                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded disabled:opacity-50">
                                <span v-if="form.processing">Modification...</span>
                                <span v-else>Modifier le rucher</span>
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

// Props du contrôleur
const props = defineProps({
    rucher: Object
});

// Variables pour l'autocomplete
const suggestions = ref([]);
const showSuggestions = ref(false);
const searchLoading = ref(false);
let searchTimeout = null;

// Formulaire pré-rempli avec les données existantes
const form = useForm({
    nom: props.rucher.nom,
    localisation: props.rucher.localisation,
    latitude: props.rucher.latitude,
    longitude: props.rucher.longitude,
    description: props.rucher.description,
});

// Recherche de lieux
async function searchPlaces() {
    const query = form.localisation.trim();

    if (query.length < 3) {
        suggestions.value = [];
        return;
    }

    // Debounce pour éviter trop de requêtes
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(async () => {
        searchLoading.value = true;

        try {
            const response = await fetch(
                `https://api-adresse.data.gouv.fr/search/?q=${encodeURIComponent(query)}&type=municipality&limit=5`
            );
            const data = await response.json();
            suggestions.value = data.features || [];
        } catch (error) {
            console.error('Erreur recherche localisation:', error);
            suggestions.value = [];
        } finally {
            searchLoading.value = false;
        }
    }, 300);
}

// Sélectionner un lieu
function selectPlace(place) {
    form.localisation = place.properties.label;
    form.latitude = place.geometry.coordinates[1]; // Latitude
    form.longitude = place.geometry.coordinates[0]; // Longitude

    suggestions.value = [];
    showSuggestions.value = false;
}

// Cacher suggestions quand on clique ailleurs
function hideSuggestions() {
    setTimeout(() => {
        showSuggestions.value = false;
    }, 150);
}


// Soumission avec PUT
function submit() {
    form.put(route('ruchers.update', props.rucher.id));
}
</script>