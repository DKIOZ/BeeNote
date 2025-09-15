<template>
    <AppLayout title="Mes Ruchers">
        <template #header>
            <div class="flex flex-col gap-4 sm:flex-row sm:justify-between sm:items-start">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">
                        Mes Ruchers
                    </h1>
                    <p class="text-gray-600 mt-1">
                        Gérez et surveillez tous vos emplacements d'abeilles
                    </p>
                </div>
                
                <Link :href="route('ruchers.create')"
                    class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-3 px-6 rounded-lg inline-flex items-center justify-center gap-2 transition-colors shadow-sm">
                    <Plus class="w-5 h-5" />
                    Nouveau Rucher
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6">
                
                <!-- Message de succès -->
                <div v-if="$page.props.flash?.message" class="mb-6">
                    <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg text-sm flex items-center gap-2">
                        <CheckCircle class="w-5 h-5" />
                        {{ $page.props.flash.message }}
                    </div>
                </div>

                <!-- Statistiques rapides (si ruchers existent) -->
                <div v-if="ruchers.length > 0" class="mb-8">
                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-6 border border-blue-200">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                            <BarChart3 class="w-5 h-5" />
                            Vue d'ensemble
                        </h2>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                            <div class="text-center p-4 bg-white rounded-lg border border-blue-200">
                                <div class="text-2xl font-bold text-blue-600">{{ ruchers.length }}</div>
                                <div class="text-sm text-gray-600">Rucher{{ ruchers.length > 1 ? 's' : '' }}</div>
                            </div>
                            <div class="text-center p-4 bg-white rounded-lg border border-blue-200">
                                <div class="text-2xl font-bold text-green-600">{{ totalRuches }}</div>
                                <div class="text-sm text-gray-600">Ruche{{ totalRuches > 1 ? 's' : '' }} total</div>
                            </div>
                            <div class="text-center p-4 bg-white rounded-lg border border-blue-200">
                                <div class="text-2xl font-bold text-yellow-600">{{ ruchesActives }}</div>
                                <div class="text-sm text-gray-600">Active{{ ruchesActives > 1 ? 's' : '' }}</div>
                            </div>
                            <div class="text-center p-4 bg-white rounded-lg border border-blue-200">
                                <div class="text-2xl font-bold text-purple-600">{{ totalVisites }}</div>
                                <div class="text-sm text-gray-600">Visite{{ totalVisites > 1 ? 's' : '' }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- État vide avec onboarding -->
                <div v-if="ruchers.length === 0" class="bg-white rounded-xl border border-gray-200 shadow-sm">
                    <div class="text-center py-16 px-8">
                        <div class="mb-6">
                            <div class="mx-auto w-20 h-20 bg-blue-100 rounded-full flex items-center justify-center mb-4">
                                <Building2 class="w-10 h-10 text-blue-600" />
                            </div>
                            <h3 class="text-xl font-semibold text-gray-900 mb-2">
                                Bienvenue dans votre espace apicole !
                            </h3>
                            <p class="text-gray-600 max-w-md mx-auto leading-relaxed">
                                Pour commencer, créez votre premier rucher. C'est l'endroit où vous installez vos ruches.
                            </p>
                        </div>

                        <!-- Guide rapide -->
                        <div class="mb-8 max-w-2xl mx-auto">
                            <h4 class="font-medium text-gray-900 mb-4">Votre parcours apicole en 3 étapes :</h4>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div class="p-4 bg-blue-50 rounded-lg border border-blue-200">
                                    <div class="text-3xl mb-2">🏠</div>
                                    <div class="font-medium text-blue-900 mb-1">1. Créer un rucher</div>
                                    <div class="text-sm text-blue-700">Définissez l'emplacement de vos ruches</div>
                                </div>
                                <div class="p-4 bg-green-50 rounded-lg border border-green-200">
                                    <div class="text-3xl mb-2">🏺</div>
                                    <div class="font-medium text-green-900 mb-1">2. Ajouter des ruches</div>
                                    <div class="text-sm text-green-700">Enregistrez chaque ruche avec ses caractéristiques</div>
                                </div>
                                <div class="p-4 bg-purple-50 rounded-lg border border-purple-200">
                                    <div class="text-3xl mb-2">📝</div>
                                    <div class="font-medium text-purple-900 mb-1">3. Faire des visites</div>
                                    <div class="text-sm text-purple-700">Suivez l'état de vos colonies</div>
                                </div>
                            </div>
                        </div>
                        
                        <Link :href="route('ruchers.create')"
                            class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white text-base font-medium py-3 px-8 rounded-lg gap-2 transition-colors shadow-sm">
                            <Plus class="w-5 h-5" />
                            Créer mon premier rucher
                        </Link>
                    </div>
                </div>

                <!-- Liste des ruchers avec cartes améliorées -->
                <div v-else class="space-y-6">
                    
                    <!-- Filtres/Actions rapides -->
                    <div class="flex flex-col sm:flex-row gap-4 justify-between items-start sm:items-center">
                        <div class="flex items-center gap-4">
                            <h2 class="text-lg font-semibold text-gray-900">
                                Vos ruchers ({{ ruchers.length }})
                            </h2>
                        </div>
                        
                        <!-- Actions rapides -->
                        <div class="flex gap-2">
                            <button class="px-4 py-2 text-sm text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                <MapPin class="w-4 h-4 inline mr-2" />
                                Vue carte
                            </button>
                            <button class="px-4 py-2 text-sm text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                <Filter class="w-4 h-4 inline mr-2" />
                                Filtrer
                            </button>
                        </div>
                    </div>

                    <!-- Grille des ruchers -->
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                        <Link v-for="rucher in ruchers" :key="rucher.id"
                            :href="route('ruchers.show', rucher.id)"
                            class="group block bg-white rounded-xl border border-gray-200 hover:border-blue-300 hover:shadow-lg transition-all duration-200 overflow-hidden">
                            
                            <!-- Header de la carte -->
                            <div class="p-6 pb-4">
                                <div class="flex items-start justify-between mb-4">
                                    <div class="flex-1 min-w-0">
                                        <h3 class="text-lg font-semibold text-gray-900 truncate group-hover:text-blue-600 transition-colors">
                                            {{ rucher.nom }}
                                        </h3>
                                        <div v-if="rucher.localisation" class="flex items-center gap-2 mt-1">
                                            <MapPin class="w-4 h-4 text-gray-400 flex-shrink-0" />
                                            <span class="text-sm text-gray-600 truncate">{{ rucher.localisation }}</span>
                                        </div>
                                    </div>
                                    
                                    <!-- Menu actions -->
                                    <div class="flex gap-1 ml-4" @click.prevent.stop>
                                        <Link :href="route('ruchers.edit', rucher.id)"
                                            class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors"
                                            title="Modifier">
                                            <Edit class="w-4 h-4" />
                                        </Link>
                                    </div>
                                </div>

                                <!-- Description -->
                                <div v-if="rucher.description" class="mb-4">
                                    <p class="text-sm text-gray-600 line-clamp-2 leading-relaxed">
                                        {{ rucher.description }}
                                    </p>
                                </div>

                                <!-- Statistiques du rucher -->
                                <div class="grid grid-cols-3 gap-4 mb-4">
                                    <div class="text-center p-3 bg-blue-50 rounded-lg">
                                        <div class="text-lg font-bold text-blue-600">{{ getRucherStats(rucher).ruches }}</div>
                                        <div class="text-xs text-blue-700">Ruche{{ getRucherStats(rucher).ruches > 1 ? 's' : '' }}</div>
                                    </div>
                                    <div class="text-center p-3 bg-green-50 rounded-lg">
                                        <div class="text-lg font-bold text-green-600">{{ getRucherStats(rucher).actives }}</div>
                                        <div class="text-xs text-green-700">Active{{ getRucherStats(rucher).actives > 1 ? 's' : '' }}</div>
                                    </div>
                                    <div class="text-center p-3 bg-purple-50 rounded-lg">
                                        <div class="text-lg font-bold text-purple-600">{{ getRucherStats(rucher).visites }}</div>
                                        <div class="text-xs text-purple-700">Visite{{ getRucherStats(rucher).visites > 1 ? 's' : '' }}</div>
                                    </div>
                                </div>

                                <!-- Dernière activité -->
                                <div v-if="getRucherStats(rucher).derniere_visite" class="mb-4 p-3 bg-gray-50 rounded-lg">
                                    <div class="flex items-center gap-2 text-sm">
                                        <Clock class="w-4 h-4 text-gray-400" />
                                        <span class="text-gray-600">Dernière visite :</span>
                                        <span class="font-medium text-gray-900">{{ formatDateRelative(getRucherStats(rucher).derniere_visite) }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Footer de la carte -->
                            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 group-hover:bg-blue-50 transition-colors">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2 text-sm text-gray-600">
                                        <Calendar class="w-4 h-4" />
                                        <span>Créé le {{ formatDate(rucher.created_at) }}</span>
                                    </div>
                                    
                                    <div class="flex items-center gap-1 text-sm text-blue-600 group-hover:text-blue-700 font-medium">
                                        <span>Gérer</span>
                                        <ArrowRight class="w-4 h-4 group-hover:translate-x-1 transition-transform" />
                                    </div>
                                </div>
                            </div>
                        </Link>
                    </div>

                    <!-- Actions en bas de page -->
                    <div class="text-center pt-8">
                        <Link :href="route('ruchers.create')"
                            class="inline-flex items-center bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium py-3 px-6 rounded-lg gap-2 transition-colors">
                            <Plus class="w-4 h-4" />
                            Ajouter un autre rucher
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { 
    Plus, 
    Building2, 
    Edit, 
    MapPin, 
    ArrowRight, 
    CheckCircle,
    BarChart3,
    Calendar,
    Clock,
    Filter
} from 'lucide-vue-next';

// Props du contrôleur
const props = defineProps({
    ruchers: Array
});

// Computed properties pour les statistiques
const totalRuches = computed(() => {
    return props.ruchers.reduce((total, rucher) => total + (rucher.ruches_count || 0), 0);
});

const ruchesActives = computed(() => {
    return props.ruchers.reduce((total, rucher) => total + (rucher.ruches_actives_count || 0), 0);
});

const totalVisites = computed(() => {
    return props.ruchers.reduce((total, rucher) => total + (rucher.visites_count || 0), 0);
});

// Fonctions utilitaires
function getRucherStats(rucher) {
    return {
        ruches: rucher.ruches_count || 0,
        actives: rucher.ruches_actives_count || 0,
        visites: rucher.visites_count || 0,
        derniere_visite: rucher.derniere_visite || null
    };
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('fr-FR', {
        day: 'numeric',
        month: 'short',
        year: 'numeric'
    });
}

function formatDateRelative(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diffInDays = Math.floor((now - date) / (1000 * 60 * 60 * 24));
    
    if (diffInDays === 0) return "Aujourd'hui";
    if (diffInDays === 1) return "Hier";
    if (diffInDays < 7) return `Il y a ${diffInDays} jours`;
    if (diffInDays < 30) return `Il y a ${Math.floor(diffInDays / 7)} semaine${Math.floor(diffInDays / 7) > 1 ? 's' : ''}`;
    if (diffInDays < 365) return `Il y a ${Math.floor(diffInDays / 30)} mois`;
    return `Il y a ${Math.floor(diffInDays / 365)} an${Math.floor(diffInDays / 365) > 1 ? 's' : ''}`;
}
</script>