<template>
    <AppLayout :title="`Ruches de ${rucher.nom}`">
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
                        <span class="text-gray-900 font-medium">Ruches</span>
                    </div>
                </nav>

                <!-- Titre avec actions -->
                <div class="flex flex-col gap-3 sm:flex-row sm:justify-between sm:items-center">
                    <h2 class="text-lg sm:text-xl font-medium text-gray-900">
                        Ruches de {{ rucher.nom }}
                    </h2>

                    <div class="flex flex-col gap-2 sm:flex-row sm:gap-2">
                        <Link :href="route('ruchers.ruches.create', rucher.id)"
                            class="bg-gray-900 hover:bg-gray-800 text-white text-sm font-medium py-3 px-4 rounded-sm inline-flex items-center justify-center sm:py-2">
                        <Plus class="w-4 h-4 mr-2" />
                        Nouvelle Ruche
                        </Link>

                        <Link :href="route('ruchers.show', rucher.id)"
                            class="bg-white hover:bg-gray-50 text-gray-700 border border-gray-300 text-sm font-medium py-3 px-4 rounded-sm text-center sm:py-2">
                        Retour au rucher
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
                    <div v-if="ruches.length === 0" class="text-center py-16 px-4">
                        <Package class="mx-auto h-8 w-8 text-gray-400 mb-4" />
                        <h3 class="text-base font-medium text-gray-900 mb-2">Aucune ruche</h3>
                        <p class="text-sm text-gray-600 mb-8 max-w-sm mx-auto">
                            Commencez par ajouter votre première ruche à ce rucher.
                        </p>
                        <Link :href="route('ruchers.ruches.create', rucher.id)"
                            class="inline-flex items-center bg-gray-900 hover:bg-gray-800 text-white text-sm font-medium py-3 px-4 rounded-sm">
                        <Plus class="mr-2 h-4 w-4" />
                        Ajouter une ruche
                        </Link>
                    </div>

                    <!-- Liste des ruches -->
                    <div v-else>
                        <!-- En-tête -->
                        <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
                            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                <h3 class="text-base font-medium text-gray-900">
                                    {{ ruches.length }} ruche{{ ruches.length > 1 ? 's' : '' }}
                                </h3>
                                <span class="text-xs text-gray-600">
                                    Rucher : {{ rucher.nom }}
                                </span>
                            </div>
                        </div>

                        <!-- Grille des ruches -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6 p-4 sm:p-6">
                            <Link v-for="ruche in ruches" :key="ruche.id"
                                :href="route('ruchers.ruches.show', [rucher.id, ruche.id])"
                                class="block border border-gray-200 rounded-sm hover:border-gray-900 transition-colors cursor-pointer">

                            <!-- Contenu de la carte -->
                            <div class="p-4">
                                <div class="flex items-start justify-between mb-4">
                                    <div class="flex items-center gap-3 min-w-0 flex-1">
                                        <!-- Couleur de la ruche -->
                                        <div v-if="ruche.couleur" :style="{ backgroundColor: ruche.couleur }"
                                            class="w-3 h-3 rounded-full border border-gray-300 flex-shrink-0">
                                        </div>

                                        <h3 class="text-sm font-medium text-gray-900 truncate">
                                            {{ ruche.nom }}
                                        </h3>
                                    </div>

                                    <!-- Menu actions -->
                                    <div class="flex gap-2 flex-shrink-0" @click.prevent.stop>
                                        <Link :href="route('ruchers.ruches.show', [rucher.id, ruche.id])"
                                            class="text-gray-600 hover:text-gray-900" title="Voir">
                                        <Eye class="w-4 h-4" />
                                        </Link>

                                        <Link :href="route('ruchers.ruches.edit', [rucher.id, ruche.id])"
                                            class="text-gray-600 hover:text-gray-900" title="Modifier">
                                        <Edit class="w-4 h-4" />
                                        </Link>
                                    </div>
                                </div>

                                <!-- Informations de la ruche -->
                                <div class="space-y-2 text-xs text-gray-600">
                                    <div class="flex items-center gap-2">
                                        <Package class="w-3 h-3 flex-shrink-0" />
                                        <span>{{ ruche.type || 'Non spécifié' }}</span>
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <Activity class="w-3 h-3 flex-shrink-0" />
                                        <span :class="{
                                            'text-gray-800': ruche.statut === 'active',
                                            'text-gray-600': ruche.statut === 'inactive',
                                            'text-gray-900': ruche.statut === 'morte'
                                        }" class="font-medium">
                                            {{ getStatutLabel(ruche.statut) }}
                                        </span>
                                    </div>

                                    <div v-if="ruche.annee_reine" class="flex items-center gap-2">
                                        <Crown class="w-3 h-3 flex-shrink-0" />
                                        <span>Reine {{ ruche.annee_reine }}</span>
                                        <div v-if="ruche.couleur_marquage_reine"
                                            :style="{ backgroundColor: getReineColor(ruche.couleur_marquage_reine) }"
                                            class="w-2 h-2 rounded-full border border-gray-300 flex-shrink-0">
                                        </div>
                                    </div>

                                    <div v-if="ruche.date_installation" class="flex items-center gap-2">
                                        <Calendar class="w-3 h-3 flex-shrink-0" />
                                        <span class="truncate">{{ formatDate(ruche.date_installation) }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Footer de la carte -->
                            <div class="px-4 py-3 border-t border-gray-200">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-gray-600">
                                        {{ formatDate(ruche.created_at) }}
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