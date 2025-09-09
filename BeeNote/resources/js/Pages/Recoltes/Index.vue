<template>
    <AppLayout :title="`Récoltes de ${ruche.nom}`">
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
                        <span class="text-gray-900 font-medium">Récoltes</span>
                    </div>
                </nav>

                <!-- Titre avec actions -->
                <div class="flex flex-col gap-3 sm:flex-row sm:justify-between sm:items-center">
                    <h2 class="text-lg sm:text-xl font-medium text-gray-900">
                        Récoltes de {{ ruche.nom }}
                    </h2>

                    <div class="flex flex-col gap-2 sm:flex-row sm:gap-2">
                        <Link :href="route('ruchers.ruches.recoltes.create', [rucher.id, ruche.id])"
                            class="bg-gray-900 hover:bg-gray-800 text-white text-sm font-medium py-3 px-4 rounded-sm inline-flex items-center justify-center sm:py-2">
                            <Plus class="w-4 h-4 mr-2" />
                            Nouvelle Récolte
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
                    <div v-if="recoltes.length === 0" class="text-center py-16 px-4">
                        <Droplets class="mx-auto h-8 w-8 text-gray-400 mb-4" />
                        <h3 class="text-base font-medium text-gray-900 mb-2">Aucune récolte</h3>
                        <p class="text-sm text-gray-600 mb-8 max-w-sm mx-auto">
                            Commencez par enregistrer votre première récolte pour cette ruche.
                        </p>
                        <Link :href="route('ruchers.ruches.recoltes.create', [rucher.id, ruche.id])"
                            class="inline-flex items-center bg-gray-900 hover:bg-gray-800 text-white text-sm font-medium py-3 px-4 rounded-sm">
                            <Plus class="mr-2 h-4 w-4" />
                            Enregistrer une récolte
                        </Link>
                    </div>

                    <!-- Liste des récoltes -->
                    <div v-else>
                        <!-- En-tête -->
                        <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
                            <h3 class="text-base font-medium text-gray-900">
                                {{ recoltes.length }} récolte{{ recoltes.length > 1 ? 's' : '' }}
                            </h3>
                        </div>

                        <!-- Liste -->
                        <div class="divide-y divide-gray-200">
                            <Link v-for="recolte in recoltes" :key="recolte.id"
                                :href="route('ruchers.ruches.recoltes.show', [rucher.id, ruche.id, recolte.id])"
                                class="block px-4 sm:px-6 py-4 hover:bg-gray-50 transition-colors">
                                
                                <div class="flex items-center justify-between gap-4">
                                    <!-- Informations principales -->
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-3 mb-2">
                                            <Droplets class="h-4 w-4 text-gray-400 flex-shrink-0" />
                                            <span class="text-sm font-medium text-gray-900">
                                                {{ formatDate(recolte.date_recolte) }}
                                            </span>
                                            <span :class="{
                                                'bg-amber-100 text-amber-800': recolte.type_miel === 'acacia',
                                                'bg-green-100 text-green-800': recolte.type_miel === 'tilleul',
                                                'bg-orange-100 text-orange-800': recolte.type_miel === 'chataignier',
                                                'bg-purple-100 text-purple-800': recolte.type_miel === 'lavande',
                                                'bg-blue-100 text-blue-800': recolte.type_miel === 'toutes_fleurs',
                                                'bg-gray-100 text-gray-800': recolte.type_miel === 'autre'
                                            }" class="text-xs font-medium px-2 py-1 rounded-sm">
                                                {{ getTypeMielLabel(recolte.type_miel) }}
                                            </span>
                                        </div>
                                        
                                        <div class="flex items-center gap-4 text-sm text-gray-600">
                                            <span class="flex items-center gap-1">
                                                <Weight class="h-3 w-3" />
                                                {{ recolte.quantite_kg }} kg
                                            </span>
                                            <span class="flex items-center gap-1">
                                                <Grid class="h-3 w-3" />
                                                {{ recolte.nombre_cadres }} cadres
                                            </span>
                                            <span :class="{
                                                'text-green-600': recolte.qualite === 'excellente',
                                                'text-blue-600': recolte.qualite === 'bonne',
                                                'text-yellow-600': recolte.qualite === 'moyenne',
                                                'text-red-600': recolte.qualite === 'mediocre'
                                            }">
                                                {{ getQualiteLabel(recolte.qualite) }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Flèche -->
                                    <ChevronRight class="h-4 w-4 text-gray-400 flex-shrink-0" />
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
import { Plus, Droplets, Weight, Grid, ChevronRight } from 'lucide-vue-next';

// Props du contrôleur
defineProps({
    rucher: Object,
    ruche: Object,
    recoltes: Array
});

// Helpers pour l'affichage
function formatDate(dateString) {
    return new Date(dateString).toLocaleDateString('fr-FR', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

function getTypeMielLabel(type) {
    const labels = {
        'acacia': 'Acacia',
        'tilleul': 'Tilleul',
        'chataignier': 'Châtaignier',
        'toutes_fleurs': 'Toutes fleurs',
        'lavande': 'Lavande',
        'autre': 'Autre'
    };
    return labels[type] || type;
}

function getQualiteLabel(qualite) {
    const labels = {
        'excellente': 'Excellente',
        'bonne': 'Bonne',
        'moyenne': 'Moyenne',
        'mediocre': 'Médiocre'
    };
    return labels[qualite] || qualite;
}
</script>