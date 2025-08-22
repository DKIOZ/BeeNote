<template>
    <AppLayout title="Modifier le Rucher">
        <template #header>
            <h2 class="text-lg sm:text-xl font-medium text-gray-900">
                Modifier le rucher : {{ rucher.nom }}
            </h2>
        </template>

        <div class="py-6 sm:py-8 lg:py-12">
            <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white border border-gray-200 rounded-sm p-4 sm:p-6">

                    <form @submit.prevent="submit" class="space-y-6">

                        <!-- Nom du rucher -->
                        <div>
                            <label for="nom" class="block text-sm font-medium text-gray-900 mb-2">
                                Nom du rucher *
                            </label>
                            <input id="nom" v-model="form.nom" type="text"
                                class="w-full border border-gray-300 rounded-sm px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-gray-900 focus:border-gray-900"
                                required />
                            <div v-if="form.errors.nom" class="text-red-600 text-sm mt-1">
                                {{ form.errors.nom }}
                            </div>
                        </div>

                        <!-- Localisation avec autocomplete -->
                        <div>
                            <label for="localisation" class="block text-sm font-medium text-gray-900 mb-2">
                                Localisation
                            </label>
                            <div class="relative">
                                <input id="localisation" v-model="form.localisation" @input="searchPlaces"
                                    @focus="showSuggestions = true" @blur="hideSuggestions" type="text"
                                    placeholder="Tapez votre ville, département..."
                                    class="w-full border border-gray-300 rounded-sm px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-gray-900 focus:border-gray-900"
                                    autocomplete="off" />

                                <!-- Loading indicator -->
                                <div v-if="searchLoading" class="absolute right-3 top-2.5">
                                    <svg class="animate-spin h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                            stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                        </path>
                                    </svg>
                                </div>

                                <!-- Suggestions dropdown -->
                                <div v-if="showSuggestions && suggestions.length > 0"
                                    class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-sm shadow-lg max-h-60 overflow-y-auto">
                                    <ul class="py-1">
                                        <li v-for="place in suggestions" :key="place.properties.id"
                                            @click="selectPlace(place)"
                                            class="px-3 py-2 hover:bg-gray-50 cursor-pointer">
                                            <div class="text-sm font-medium text-gray-900">{{ place.properties.name }}
                                            </div>
                                            <div class="text-xs text-gray-600">
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
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="latitude" class="block text-sm font-medium text-gray-900 mb-2">
                                    Latitude
                                </label>
                                <input id="latitude" v-model="form.latitude" type="number" step="any"
                                    class="w-full border border-gray-300 rounded-sm px-3 py-2 text-sm font-mono focus:outline-none focus:ring-1 focus:ring-gray-900 focus:border-gray-900" />
                                <div v-if="form.errors.latitude" class="text-red-600 text-sm mt-1">
                                    {{ form.errors.latitude }}
                                </div>
                            </div>
                            <div>
                                <label for="longitude" class="block text-sm font-medium text-gray-900 mb-2">
                                    Longitude
                                </label>
                                <input id="longitude" v-model="form.longitude" type="number" step="any"
                                    class="w-full border border-gray-300 rounded-sm px-3 py-2 text-sm font-mono focus:outline-none focus:ring-1 focus:ring-gray-900 focus:border-gray-900" />
                                <div v-if="form.errors.longitude" class="text-red-600 text-sm mt-1">
                                    {{ form.errors.longitude }}
                                </div>
                            </div>
                        </div>

                        <!-- Description -->
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-900 mb-2">
                                Description
                            </label>
                            <textarea id="description" v-model="form.description" rows="4"
                                class="w-full border border-gray-300 rounded-sm px-3 py-2 text-sm leading-relaxed resize-none focus:outline-none focus:ring-1 focus:ring-gray-900 focus:border-gray-900"
                                placeholder="Décrivez votre rucher..."></textarea>
                            <div v-if="form.errors.description" class="text-red-600 text-sm mt-1">
                                {{ form.errors.description }}
                            </div>
                        </div>

                        <!-- Boutons -->
                        <div class="flex flex-col gap-3 pt-4 sm:flex-row sm:justify-end">
                            <Link :href="route('ruchers.show', rucher.id)"
                                class="w-full bg-white hover:bg-gray-50 text-gray-700 border border-gray-300 text-sm font-medium py-3 px-4 rounded-sm text-center sm:w-auto">
                            Annuler
                            </Link>
                            <button type="submit" :disabled="form.processing"
                                class="w-full bg-gray-900 hover:bg-gray-800 text-white text-sm font-medium py-3 px-4 rounded-sm disabled:opacity-50 sm:w-auto">
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