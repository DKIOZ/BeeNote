<template>
    <AppLayout :title="`Ruches de ${rucher.nom}`">
        <template #header>
            <div class="flex flex-col space-y-2">
                <!-- Breadcrumbs -->
                <nav class="text-sm text-gray-500">
                    <Link :href="route('ruchers.index')" class="hover:text-gray-700">
                    Mes Ruchers
                    </Link>
                    <span class="mx-2">›</span>
                    <Link :href="route('ruchers.show', rucher.id)" class="hover:text-gray-700">
                    {{ rucher.nom }}
                    </Link>
                    <span class="mx-2">›</span>
                    <span class="text-gray-900">Ruches</span>
                </nav>

                <!-- Titre avec actions -->
                <div class="flex justify-between items-center">
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                        Ruches de {{ rucher.nom }}
                    </h2>

                    <div class="space-x-2">
                        <Link :href="route('ruchers.ruches.create', rucher.id)"
                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded inline-flex items-center">
                        <Plus class="w-4 h-4 mr-2" />
                        Nouvelle Ruche
                        </Link>

                        <Link :href="route('ruchers.show', rucher.id)"
                            class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        Retour au rucher
                        </Link>
                    </div>
                </div>
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
                    <div v-if="ruches.length === 0" class="text-center py-12">
                        <Package class="mx-auto h-12 w-12 text-gray-400" />
                        <h3 class="mt-2 text-sm font-medium text-gray-900">Aucune ruche</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Commencez par ajouter votre première ruche à ce rucher.
                        </p>
                        <div class="mt-6">
                            <Link :href="route('ruchers.ruches.create', rucher.id)"
                                class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                            <Plus class="-ml-1 mr-2 h-5 w-5" />
                            Ajouter une ruche
                            </Link>
                        </div>
                    </div>

                    <!-- Liste des ruches -->
                    <div v-else>
                        <!-- En-tête -->
                        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-medium text-gray-900">
                                    {{ ruches.length }} ruche{{ ruches.length > 1 ? 's' : '' }}
                                </h3>
                                <span class="text-sm text-gray-500">
                                    Rucher : {{ rucher.nom }}
                                </span>
                            </div>
                        </div>

                        <!-- Grille des ruches -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 p-6">
                            <div v-for="ruche in ruches" :key="ruche.id"
                                class="bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200">
                                <!-- Contenu de la carte -->
                                <div class="p-6">
                                    <div class="flex items-center justify-between mb-4">
                                        <div class="flex items-center space-x-3">
                                            <!-- Couleur de la ruche -->
                                            <div v-if="ruche.couleur" :style="{ backgroundColor: ruche.couleur }"
                                                class="w-4 h-4 rounded-full border border-gray-300"
                                                :title="`Couleur: ${ruche.couleur}`"></div>

                                            <h3 class="text-lg font-semibold text-gray-900">
                                                {{ ruche.nom }}
                                            </h3>
                                        </div>

                                        <!-- Menu actions -->
                                        <div class="flex space-x-2">
                                            <Link :href="route('ruchers.ruches.show', [rucher.id, ruche.id])"
                                                class="text-blue-600 hover:text-blue-800" title="Voir">
                                            <Eye class="w-5 h-5" />
                                            </Link>

                                            <Link :href="route('ruchers.ruches.edit', [rucher.id, ruche.id])"
                                                class="text-green-600 hover:text-green-800" title="Modifier">
                                            <Edit class="w-5 h-5" />
                                            </Link>
                                        </div>
                                    </div>

                                    <!-- Informations de la ruche -->
                                    <div class="space-y-2 text-sm text-gray-600">
                                        <div class="flex items-center">
                                            <Package class="w-4 h-4 mr-2 text-gray-400" />
                                            Type : {{ ruche.type || 'Non spécifié' }}
                                        </div>

                                        <div class="flex items-center">
                                            <Activity class="w-4 h-4 mr-2 text-gray-400" />
                                            Statut :
                                            <span :class="{
                                                'text-green-600': ruche.statut === 'active',
                                                'text-yellow-600': ruche.statut === 'inactive',
                                                'text-red-600': ruche.statut === 'morte'
                                            }" class="ml-1 font-medium">
                                                {{ getStatutLabel(ruche.statut) }}
                                            </span>
                                        </div>

                                        <div v-if="ruche.annee_reine" class="flex items-center">
                                            <Crown class="w-4 h-4 mr-2 text-gray-400" />
                                            Reine {{ ruche.annee_reine }}
                                            <span v-if="ruche.couleur_marquage_reine"
                                                :style="{ backgroundColor: getReineColor(ruche.couleur_marquage_reine) }"
                                                class="ml-2 w-3 h-3 rounded-full border border-gray-300"
                                                :title="`Marquage: ${ruche.couleur_marquage_reine}`"></span>
                                        </div>

                                        <div v-if="ruche.date_installation" class="flex items-center">
                                            <Calendar class="w-4 h-4 mr-2 text-gray-400" />
                                            Installée {{ formatDate(ruche.date_installation) }}
                                        </div>
                                    </div>
                                </div>

                                <!-- Footer de la carte -->
                                <div class="px-6 py-3 bg-gray-50 border-t border-gray-200 rounded-b-lg">
                                    <div class="flex items-center justify-between text-sm">
                                        <span class="text-gray-500">
                                            Ajoutée {{ formatDate(ruche.created_at) }}
                                        </span>

                                        <Link :href="route('ruchers.ruches.show', [rucher.id, ruche.id])"
                                            class="text-blue-600 hover:text-blue-800 font-medium inline-flex items-center">
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
import { Plus, Package, Eye, Edit, Activity, Crown, Calendar, ArrowRight } from 'lucide-vue-next';

// Props du contrôleur
defineProps({
    rucher: Object,
    ruches: Array
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

// Helper pour les statuts
function getStatutLabel(statut) {
    const labels = {
        'active': 'Active',
        'inactive': 'Inactive',
        'morte': 'Morte'
    };
    return labels[statut] || statut;
}

// Helper couleurs marquage reine
function getReineColor(couleur) {
    const colors = {
        'blanc': '#ffffff',
        'jaune': '#ffd700',
        'rouge': '#ff0000',
        'vert': '#008000',
        'bleu': '#0000ff'
    };
    return colors[couleur] || couleur;
}
</script>