<template>
    <AppLayout title="Mes Ruchers">
        <template #header>
            <div class="flex flex-col gap-3 sm:flex-row sm:justify-between sm:items-center">
                <h2 class="text-lg sm:text-xl font-medium text-gray-900">
                    Mes Ruchers
                </h2>
                
                <Link :href="route('ruchers.create')"
                    class="bg-gray-900 hover:bg-gray-800 text-white text-sm font-medium py-3 px-4 rounded-sm inline-flex items-center justify-center sm:py-2">
                    <Plus class="w-4 h-4 mr-2" />
                    Nouveau Rucher
                </Link>
            </div>
        </template>

        <div class="py-6 sm:py-8 lg:py-12">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <!-- Message de succès -->
                <div v-if="$page.props.flash?.message" class="mb-6">
                    <div class="bg-gray-100 border border-gray-300 text-gray-900 px-4 py-3 rounded-sm text-sm">
                        {{ $page.props.flash.message }}
                    </div>
                </div>

                <!-- Contenu principal -->
                <div class="bg-white border border-gray-200 rounded-sm">
                    
                    <!-- État vide -->
                    <div v-if="ruchers.length === 0" class="text-center py-16 px-4">
                        <Building2 class="mx-auto h-8 w-8 text-gray-400 mb-4" />
                        <h3 class="text-base font-medium text-gray-900 mb-2">Aucun rucher</h3>
                        <p class="text-sm text-gray-600 mb-8 max-w-sm mx-auto">
                            Commencez par créer votre premier rucher.
                        </p>
                        <Link :href="route('ruchers.create')"
                            class="inline-flex items-center bg-gray-900 hover:bg-gray-800 text-white text-sm font-medium py-3 px-4 rounded-sm">
                            <Plus class="mr-2 h-4 w-4" />
                            Créer un rucher
                        </Link>
                    </div>

                    <!-- Liste des ruchers -->
                    <div v-else>
                        <!-- En-tête -->
                        <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
                            <h3 class="text-base font-medium text-gray-900">
                                {{ ruchers.length }} rucher{{ ruchers.length > 1 ? 's' : '' }}
                            </h3>
                        </div>

                        <!-- Grille des ruchers -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6 p-4 sm:p-6">
                            <Link v-for="rucher in ruchers" :key="rucher.id"
                                :href="route('ruchers.show', rucher.id)"
                                class="block border border-gray-200 rounded-sm hover:border-gray-900 transition-colors cursor-pointer">
                                
                                <!-- Contenu de la carte -->
                                <div class="p-4">
                                    <div class="flex items-start justify-between mb-4">
                                        <h3 class="text-sm font-medium text-gray-900 truncate flex-1 mr-3">
                                            {{ rucher.nom }}
                                        </h3>
                                        
                                        <!-- Menu actions -->
                                        <div class="flex gap-2 flex-shrink-0" @click.prevent.stop>
                                            <Link :href="route('ruchers.show', rucher.id)"
                                                class="text-gray-600 hover:text-gray-900"
                                                title="Voir">
                                                <Eye class="w-4 h-4" />
                                            </Link>
                                            
                                            <Link :href="route('ruchers.edit', rucher.id)"
                                                class="text-gray-600 hover:text-gray-900"
                                                title="Modifier">
                                                <Edit class="w-4 h-4" />
                                            </Link>
                                        </div>
                                    </div>

                                    <!-- Informations du rucher -->
                                    <div class="space-y-2 text-xs text-gray-600">
                                        <div v-if="rucher.localisation" class="flex items-center gap-2">
                                            <MapPin class="w-3 h-3 flex-shrink-0" />
                                            <span class="truncate">{{ rucher.localisation }}</span>
                                        </div>
                                        
                                        <div v-if="rucher.latitude && rucher.longitude" class="flex items-center gap-2">
                                            <Navigation class="w-3 h-3 flex-shrink-0" />
                                            <span class="font-mono">{{ rucher.latitude }}°, {{ rucher.longitude }}°</span>
                                        </div>
                                        
                                        <div v-if="rucher.description" class="mt-3">
                                            <p class="text-gray-600 line-clamp-2 leading-relaxed">
                                                {{ rucher.description }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Footer de la carte -->
                                <div class="px-4 py-3 border-t border-gray-200">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs text-gray-600">
                                            {{ formatDate(rucher.created_at) }}
                                        </span>
                                        
                                        <span class="text-xs text-gray-900 font-medium inline-flex items-center gap-1">
                                            Voir détails 
                                            <ArrowRight class="w-3 h-3" />
                                        </span>
                                    </div>
                                </div>
                            </Link>
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