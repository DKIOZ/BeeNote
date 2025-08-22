<template>
    <AppLayout title="Mes Ruchers">
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Mes Ruchers
                </h2>
                
                <Link
                    :href="route('ruchers.create')"
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded inline-flex items-center"
                >
                    <Plus class="w-4 h-4 mr-2" />
                    Nouveau Rucher
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <!-- Message de succès -->
                <div v-if="$page.props.flash?.message" class="mb-6">
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                        {{ $page.props.flash.message }}
                    </div>
                </div>

                <!-- Contenu principal -->
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    
                    <!-- État vide -->
                    <div v-if="ruchers.length === 0" class="text-center py-12">
                        <Building2 class="mx-auto h-12 w-12 text-gray-400" />
                        <h3 class="mt-2 text-sm font-medium text-gray-900">Aucun rucher</h3>
                        <p class="mt-1 text-sm text-gray-500">Commencez par créer votre premier rucher.</p>
                        <div class="mt-6">
                            <Link
                                :href="route('ruchers.create')"
                                class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700"
                            >
                                <Plus class="-ml-1 mr-2 h-5 w-5" />
                                Créer un rucher
                            </Link>
                        </div>
                    </div>

                    <!-- Liste des ruchers -->
                    <div v-else>
                        <!-- En-tête du tableau -->
                        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-medium text-gray-900">
                                    {{ ruchers.length }} rucher{{ ruchers.length > 1 ? 's' : '' }}
                                </h3>
                            </div>
                        </div>

                        <!-- Grille des ruchers -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 p-6">
                            <div
                                v-for="rucher in ruchers"
                                :key="rucher.id"
                                class="bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200"
                            >
                                <!-- Contenu de la carte -->
                                <div class="p-6">
                                    <div class="flex items-center justify-between mb-4">
                                        <h3 class="text-lg font-semibold text-gray-900 truncate">
                                            {{ rucher.nom }}
                                        </h3>
                                        
                                        <!-- Menu actions -->
                                        <div class="flex space-x-2">
                                            <Link
                                                :href="route('ruchers.show', rucher.id)"
                                                class="text-blue-600 hover:text-blue-800"
                                                title="Voir"
                                            >
                                                <Eye class="w-5 h-5" />
                                            </Link>
                                            
                                            <Link
                                                :href="route('ruchers.edit', rucher.id)"
                                                class="text-green-600 hover:text-green-800"
                                                title="Modifier"
                                            >
                                                <Edit class="w-5 h-5" />
                                            </Link>
                                        </div>
                                    </div>

                                    <!-- Informations du rucher -->
                                    <div class="space-y-2 text-sm text-gray-600">
                                        <div v-if="rucher.localisation" class="flex items-center">
                                            <MapPin class="w-4 h-4 mr-2 text-gray-400" />
                                            {{ rucher.localisation }}
                                        </div>
                                        
                                        <div v-if="rucher.latitude && rucher.longitude" class="flex items-center">
                                            <Navigation class="w-4 h-4 mr-2 text-gray-400" />
                                            {{ rucher.latitude }}°, {{ rucher.longitude }}°
                                        </div>
                                        
                                        <div v-if="rucher.description" class="mt-3">
                                            <p class="text-gray-600 line-clamp-2">
                                                {{ rucher.description }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Footer de la carte -->
                                <div class="px-6 py-3 bg-gray-50 border-t border-gray-200 rounded-b-lg">
                                    <div class="flex items-center justify-between text-sm">
                                        <span class="text-gray-500">
                                            Créé {{ formatDate(rucher.created_at) }}
                                        </span>
                                        
                                        <Link
                                            :href="route('ruchers.show', rucher.id)"
                                            class="text-blue-600 hover:text-blue-800 font-medium inline-flex items-center"
                                        >
                                            Voir détails 
                                            <ArrowRight class="w-4 h-4 ml-1" />
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';
import { Plus, Building2, Eye, Edit, MapPin, Navigation, ArrowRight } from 'lucide-vue-next';

// Props du contrôleur
defineProps({
    ruchers: Array
});

// Helper pour formater les dates
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('fr-FR', {
        day: 'numeric',
        month: 'short',
        year: 'numeric'
    });
}
</script>