<template>
    <AppLayout :title="rucher.nom">
        <template #header>
            <div class="flex flex-col gap-4 sm:flex-row sm:justify-between sm:items-center">
                <h2 class="text-lg sm:text-xl font-medium text-gray-900">
                    {{ rucher.nom }}
                </h2>

                <!-- Actions mobile-first -->
                <div class="flex flex-col gap-2 sm:flex-row sm:gap-3">
                    <Link :href="route('ruchers.ruches.create', rucher.id)"
                        class="bg-gray-900 hover:bg-gray-800 text-white text-sm font-medium py-3 px-4 rounded-sm flex items-center justify-center sm:py-2">
                    <Plus class="w-4 h-4 mr-2" />
                    Ajouter une ruche
                    </Link>

                    <div class="flex gap-2">
                        <Link :href="route('ruchers.edit', rucher.id)"
                            class="flex-1 bg-white hover:bg-gray-50 text-gray-900 border border-gray-300 text-sm font-medium py-3 px-4 rounded-sm text-center sm:flex-none sm:py-2">
                        Modifier
                        </Link>

                        <button @click="confirmDelete"
                            class="flex-1 bg-white hover:bg-gray-50 text-red-600 border border-gray-300 text-sm font-medium py-3 px-4 rounded-sm sm:flex-none sm:py-2">
                            Supprimer
                        </button>

                        <Link :href="route('ruchers.index')"
                            class="flex-1 bg-white hover:bg-gray-50 text-gray-700 border border-gray-300 text-sm font-medium py-3 px-4 rounded-sm text-center sm:flex-none sm:py-2">
                        Retour
                        </Link>
                    </div>
                </div>
            </div>
        </template>

        <div class="py-6 sm:py-8 lg:py-12">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6 sm:space-y-8">

                <!-- Informations du rucher -->
                <div class="bg-white border border-gray-200 rounded-sm p-4 sm:p-6">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 lg:gap-8">

                        <!-- Informations de base -->
                        <div>
                            <h3 class="text-base font-medium text-gray-900 mb-4">Informations générales</h3>

                            <div class="space-y-4">
                                <div>
                                    <dt class="text-sm font-medium text-gray-700">Nom</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ rucher.nom }}</dd>
                                </div>

                                <div v-if="rucher.localisation">
                                    <dt class="text-sm font-medium text-gray-700">Localisation</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ rucher.localisation }}</dd>
                                </div>

                                <div v-if="rucher.description">
                                    <dt class="text-sm font-medium text-gray-700">Description</dt>
                                    <dd class="mt-1 text-sm text-gray-900 leading-relaxed">{{ rucher.description }}</dd>
                                </div>
                            </div>
                        </div>

                        <!-- Coordonnées GPS -->
                        <div v-if="rucher.latitude || rucher.longitude">
                            <h3 class="text-base font-medium text-gray-900 mb-4">Coordonnées GPS</h3>

                            <div class="space-y-4">
                                <div v-if="rucher.latitude">
                                    <dt class="text-sm font-medium text-gray-700">Latitude</dt>
                                    <dd class="mt-1 text-sm text-gray-900 font-mono">{{ rucher.latitude }}°</dd>
                                </div>

                                <div v-if="rucher.longitude">
                                    <dt class="text-sm font-medium text-gray-700">Longitude</dt>
                                    <dd class="mt-1 text-sm text-gray-900 font-mono">{{ rucher.longitude }}°</dd>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section Ruches -->
                <div class="bg-white border border-gray-200 rounded-sm">
                    <!-- En-tête avec actions -->
                    <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <h3 class="text-base font-medium text-gray-900">
                                Ruches ({{ ruches.length }})
                            </h3>

                            <div class="flex gap-2">
                                <Link :href="route('ruchers.ruches.create', rucher.id)"
                                    class="bg-gray-900 hover:bg-gray-800 text-white text-sm font-medium py-2 px-3 rounded-sm flex items-center">
                                <Plus class="w-3 h-3 mr-1" />
                                Ajouter
                                </Link>

                                <Link v-if="ruches.length > 0" :href="route('ruchers.ruches.index', rucher.id)"
                                    class="bg-white hover:bg-gray-50 text-gray-900 border border-gray-300 text-sm font-medium py-2 px-3 rounded-sm">
                                Voir toutes
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- État vide -->
                    <div v-if="ruches.length === 0" class="text-center py-12 px-4">
                        <Package class="mx-auto h-8 w-8 text-gray-400 mb-4" />
                        <h3 class="text-sm font-medium text-gray-900 mb-2">Aucune ruche</h3>
                        <p class="text-sm text-gray-600 mb-6 max-w-sm mx-auto">
                            Commencez par ajouter votre première ruche à ce rucher.
                        </p>
                        <Link :href="route('ruchers.ruches.create', rucher.id)"
                            class="inline-flex items-center bg-gray-900 hover:bg-gray-800 text-white text-sm font-medium py-3 px-4 rounded-sm">
                        <Plus class="w-4 h-4 mr-2" />
                        Ajouter une ruche
                        </Link>
                    </div>

                    <!-- Liste des ruches -->
                    <div v-else class="p-4 sm:p-6">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            <Link v-for="ruche in ruches" :key="ruche.id"
                                :href="route('ruchers.ruches.show', [rucher.id, ruche.id])"
                                class="block border border-gray-200 rounded-sm p-4 hover:border-gray-900 transition-colors">

                            <!-- En-tête ruche -->
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex items-center gap-2 min-w-0 flex-1">
                                    <div v-if="ruche.couleur" :style="{ backgroundColor: ruche.couleur }"
                                        class="w-3 h-3 rounded-full border border-gray-300 flex-shrink-0">
                                    </div>
                                    <h4 class="font-medium text-gray-900 text-sm truncate">{{ ruche.nom }}</h4>
                                </div>

                                <span :class="{
                                    'bg-gray-100 text-gray-800': ruche.statut === 'active',
                                    'bg-gray-100 text-gray-600': ruche.statut === 'inactive',
                                    'bg-gray-100 text-gray-900': ruche.statut === 'morte'
                                }" class="text-xs font-medium px-2 py-1 rounded-sm flex-shrink-0">
                                    {{ getStatutLabel(ruche.statut) }}
                                </span>
                            </div>

                            <!-- Informations ruche -->
                            <div class="space-y-2 text-xs text-gray-600">
                                <div v-if="ruche.type" class="flex items-center gap-1">
                                    <Package class="w-3 h-3 flex-shrink-0" />
                                    <span class="truncate">{{ ruche.type }}</span>
                                </div>

                                <div v-if="ruche.annee_reine" class="flex items-center gap-1">
                                    <Crown class="w-3 h-3 flex-shrink-0" />
                                    <span>Reine {{ ruche.annee_reine }}</span>
                                    <div v-if="ruche.couleur_marquage_reine"
                                        :style="{ backgroundColor: getReineColor(ruche.couleur_marquage_reine) }"
                                        class="w-2 h-2 rounded-full border border-gray-300 flex-shrink-0">
                                    </div>
                                </div>

                                <div v-if="ruche.date_installation" class="flex items-center gap-1">
                                    <Calendar class="w-3 h-3 flex-shrink-0" />
                                    <span class="truncate">{{ formatDate(ruche.date_installation) }}</span>
                                </div>
                            </div>

                            <!-- Footer -->
                            <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between">
                                <span class="text-xs text-gray-500">
                                    {{ formatDate(ruche.created_at) }}
                                </span>
                                <ArrowRight class="w-3 h-3 text-gray-400 flex-shrink-0" />
                            </div>
                            </Link>
                        </div>

                        <!-- Lien vers toutes les ruches si plus de 6 -->
                        <div v-if="ruches.length >= 6" class="mt-6 text-center">
                            <Link :href="route('ruchers.ruches.index', rucher.id)"
                                class="text-gray-900 hover:text-gray-700 text-sm font-medium">
                            Voir toutes les ruches ({{ ruches.length }}) →
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
import { Link, router } from '@inertiajs/vue3';
import { Plus, Package, Crown, Calendar, ArrowRight } from 'lucide-vue-next';

const props = defineProps({
    rucher: Object,
    ruches: Array
});

function confirmDelete() {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce rucher ? Cette action est irréversible.')) {
        router.delete(route('ruchers.destroy', props.rucher.id));
    }
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('fr-FR', {
        day: 'numeric',
        month: 'short'
    });
}

function getStatutLabel(statut) {
    const labels = {
        'active': 'Active',
        'inactive': 'Inactive',
        'morte': 'Morte'
    };
    return labels[statut] || statut;
}

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