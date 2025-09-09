<template>
    <AppLayout :title="`Visites de ${ruche.nom}`">
        <template #header>
            <div class="space-y-3">
                <!-- Breadcrumbs -->
                <nav class="text-xs text-gray-600 overflow-x-auto">
                    <div class="flex items-center gap-1 whitespace-nowrap">
                        <Link :href="route('ruchers.index')" class="hover:text-gray-900">
                            Ruchers
                        </Link>
                        <span>›</span>
                        <Link :href="route('ruchers.show', rucher.id)" class="hover:text-gray-900">
                            {{ rucher.nom }}
                        </Link>
                        <span>›</span>
                        <Link :href="route('ruchers.ruches.index', rucher.id)" class="hover:text-gray-900">
                            Ruches
                        </Link>
                        <span>›</span>
                        <Link :href="route('ruchers.ruches.show', [rucher.id, ruche.id])" class="hover:text-gray-900">
                            {{ ruche.nom }}
                        </Link>
                        <span>›</span>
                        <span class="text-gray-900 font-medium">Visites</span>
                    </div>
                </nav>

                <!-- Titre avec actions -->
                <div class="flex flex-col gap-3 sm:flex-row sm:justify-between sm:items-center">
                    <div class="flex items-center gap-3">
                        <!-- Couleur de la ruche -->
                        <div v-if="ruche.couleur" 
                            :style="{ backgroundColor: ruche.couleur }"
                            class="w-4 h-4 rounded-full border border-gray-300 flex-shrink-0">
                        </div>

                        <h2 class="text-lg sm:text-xl font-medium text-gray-900">
                            Visites de {{ ruche.nom }}
                        </h2>
                    </div>

                    <div class="flex flex-col gap-2 sm:flex-row sm:gap-2">
                        <Link :href="route('ruchers.ruches.visites.create', [rucher.id, ruche.id])"
                            class="bg-gray-900 hover:bg-gray-800 text-white text-sm font-medium py-3 px-4 rounded-sm inline-flex items-center justify-center sm:py-2">
                            <Plus class="w-4 h-4 mr-2" />
                            Nouvelle Visite
                        </Link>

                        <Link :href="route('ruchers.ruches.show', [rucher.id, ruche.id])"
                            class="bg-white hover:bg-gray-50 text-gray-700 border border-gray-300 text-sm font-medium py-3 px-4 rounded-sm text-center sm:py-2">
                            Retour à la ruche
                        </Link>
                    </div>
                </div>
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
                    <div v-if="visites.length === 0" class="text-center py-16 px-4">
                        <ClipboardList class="mx-auto h-8 w-8 text-gray-400 mb-4" />
                        <h3 class="text-base font-medium text-gray-900 mb-2">Aucune visite</h3>
                        <p class="text-sm text-gray-600 mb-8 max-w-sm mx-auto">
                            Commencez par enregistrer votre première visite de cette ruche.
                        </p>
                        <Link :href="route('ruchers.ruches.visites.create', [rucher.id, ruche.id])"
                            class="inline-flex items-center bg-gray-900 hover:bg-gray-800 text-white text-sm font-medium py-3 px-4 rounded-sm">
                            <Plus class="mr-2 h-4 w-4" />
                            Première visite
                        </Link>
                    </div>

                    <!-- Liste des visites -->
                    <div v-else>
                        <!-- En-tête -->
                        <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
                            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                <h3 class="text-base font-medium text-gray-900">
                                    {{ visites.length }} visite{{ visites.length > 1 ? 's' : '' }}
                                </h3>
                                <span class="text-xs text-gray-600">
                                    Ruche : {{ ruche.nom }} - {{ rucher.nom }}
                                </span>
                            </div>
                        </div>

                        <!-- Liste des visites -->
                        <div class="divide-y divide-gray-200">
                            <Link v-for="visite in visites" :key="visite.id"
                                :href="route('ruchers.ruches.visites.show', [rucher.id, ruche.id, visite.id])"
                                class="block px-4 sm:px-6 py-4 hover:bg-gray-50 transition-colors cursor-pointer">

                                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                    <!-- Informations principales -->
                                    <div class="flex-1">
                                        <div class="flex items-center gap-3 mb-2">
                                            <!-- Icône météo -->
                                            <div class="flex-shrink-0">
                                                <Sun v-if="visite.meteo === 'ensoleille'" class="w-4 h-4 text-yellow-500" />
                                                <Cloud v-else-if="visite.meteo === 'nuageux'" class="w-4 h-4 text-gray-500" />
                                                <CloudRain v-else-if="visite.meteo === 'pluvieux'" class="w-4 h-4 text-blue-500" />
                                                <Zap v-else-if="visite.meteo === 'orageux'" class="w-4 h-4 text-purple-500" />
                                                <Calendar v-else class="w-4 h-4 text-gray-400" />
                                            </div>

                                            <!-- Date et heure -->
                                            <div>
                                                <h4 class="text-sm font-medium text-gray-900">
                                                    {{ formatDate(visite.date_visite) }}
                                                </h4>
                                                <p class="text-xs text-gray-600">
                                                    <span v-if="visite.heure_debut">
                                                        {{ visite.heure_debut }}
                                                        <span v-if="visite.heure_fin"> - {{ visite.heure_fin }}</span>
                                                        <span v-if="visite.duree_visite"> ({{ visite.duree_visite }}min)</span>
                                                    </span>
                                                    <span v-else>Heure non précisée</span>
                                                </p>
                                            </div>
                                        </div>

                                        <!-- Résumé de la visite -->
                                        <div class="flex flex-wrap items-center gap-4 text-xs text-gray-600">
                                            <!-- Température -->
                                            <div v-if="visite.temperature" class="flex items-center gap-1">
                                                <Thermometer class="w-3 h-3" />
                                                <span>{{ visite.temperature }}°C</span>
                                            </div>

                                            <!-- État colonie -->
                                            <div v-if="visite.humeur_colonie !== 'non_observe'" class="flex items-center gap-1">
                                                <Activity class="w-3 h-3" />
                                                <span>{{ getHumeurLabel(visite.humeur_colonie) }}</span>
                                            </div>

                                            <!-- Reine vue -->
                                            <div v-if="visite.reine_vue === 'ok'" class="flex items-center gap-1 text-green-600">
                                                <Crown class="w-3 h-3" />
                                                <span>Reine vue</span>
                                            </div>

                                            <!-- Actions effectuées -->
                                            <div class="flex items-center gap-2">
                                                <div v-if="visite.nourrissement_effectue" 
                                                    class="inline-flex items-center gap-1 bg-blue-100 text-blue-800 px-2 py-0.5 rounded-sm text-xs">
                                                    <Droplets class="w-3 h-3" />
                                                    <span>Nourri</span>
                                                </div>
                                                <div v-if="visite.traitement_applique" 
                                                    class="inline-flex items-center gap-1 bg-red-100 text-red-800 px-2 py-0.5 rounded-sm text-xs">
                                                    <Shield class="w-3 h-3" />
                                                    <span>Traité</span>
                                                </div>
                                                <div v-if="visite.hausse_ajoutee" 
                                                    class="inline-flex items-center gap-1 bg-green-100 text-green-800 px-2 py-0.5 rounded-sm text-xs">
                                                    <Plus class="w-3 h-3" />
                                                    <span>Hausse</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Actions -->
                                    <div class="flex items-center gap-2 flex-shrink-0" @click.prevent.stop>
                                        <Link :href="route('ruchers.ruches.visites.show', [rucher.id, ruche.id, visite.id])"
                                            class="text-gray-600 hover:text-gray-900" title="Voir">
                                            <Eye class="w-4 h-4" />
                                        </Link>

                                        <Link :href="route('ruchers.ruches.visites.edit', [rucher.id, ruche.id, visite.id])"
                                            class="text-gray-600 hover:text-gray-900" title="Modifier">
                                            <Edit class="w-4 h-4" />
                                        </Link>
                                    </div>
                                </div>

                                <!-- Notes (si présentes) -->
                                <div v-if="visite.notes_generales" class="mt-3 text-xs text-gray-600 bg-gray-50 p-2 rounded-sm">
                                    <span class="font-medium">Notes : </span>
                                    {{ truncateText(visite.notes_generales, 100) }}
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
import { 
    Plus, 
    Eye, 
    Edit, 
    ClipboardList,
    Calendar,
    Sun,
    Cloud,
    CloudRain,
    Zap,
    Thermometer,
    Activity,
    Crown,
    Droplets,
    Shield
} from 'lucide-vue-next';

// Props du contrôleur
defineProps({
    rucher: Object,
    ruche: Object,
    visites: Array
});

// Helper pour formater les dates
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('fr-FR', {
        weekday: 'short',
        day: 'numeric',
        month: 'short',
        year: 'numeric'
    });
}

// Helper pour les humeurs
function getHumeurLabel(humeur) {
    const labels = {
        'calme': 'Calme',
        'agitee': 'Agitée',
        'agressive': 'Agressive',
        'tres_agressive': 'Très agressive'
    };
    return labels[humeur] || humeur;
}

// Helper pour tronquer le texte
function truncateText(text, maxLength) {
    if (!text || text.length <= maxLength) return text;
    return text.substring(0, maxLength) + '...';
}
</script>