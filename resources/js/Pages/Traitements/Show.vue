<template>
    <AppLayout :title="`Traitement du ${formatDate(traitement.date_traitement)} - ${ruche.nom} - ${rucher.nom}`">
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
                        <Link :href="route('ruchers.ruches.show', [rucher.id, ruche.id])" class="hover:text-gray-900">
                        {{ ruche.nom }}
                        </Link>
                        <span>›</span>
                        <Link :href="route('ruchers.ruches.traitements.index', [rucher.id, ruche.id])"
                            class="hover:text-gray-900">
                        Traitements
                        </Link>
                        <span>›</span>
                        <span class="text-gray-900 font-medium">{{ formatDate(traitement.date_traitement) }}</span>
                    </div>
                </nav>

                <!-- Titre avec actions -->
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-3">
                        <!-- Couleur de la ruche -->
                        <div v-if="ruche.couleur" :style="{ backgroundColor: ruche.couleur }"
                            class="w-4 h-4 rounded-full border border-gray-300 flex-shrink-0">
                        </div>

                        <div class="min-w-0 flex-1">
                            <h2 class="text-lg sm:text-xl font-medium text-gray-900">
                                Traitement - {{ ruche.nom }}
                            </h2>
                            <div class="flex items-center gap-2 text-sm text-gray-600">
                                <time>{{ formatDate(traitement.date_traitement) }}</time>
                                <span :class="getTypeTraitementClass(traitement.type_traitement)"
                                    class="inline-flex items-center px-2 py-1 rounded text-xs font-medium">
                                    {{ getTypeTraitementLabel(traitement.type_traitement) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <Link :href="route('ruchers.ruches.traitements.index', [rucher.id, ruche.id])"
                            class="inline-flex items-center bg-white hover:bg-gray-50 text-gray-700 border border-gray-300 text-sm font-medium py-2 px-4 rounded-sm">
                        <ArrowLeft class="w-4 h-4 mr-2" />
                        Retour
                        </Link>

                        <Link :href="route('ruchers.ruches.traitements.edit', [rucher.id, ruche.id, traitement.id])"
                            class="inline-flex items-center bg-gray-900 hover:bg-gray-800 text-white text-sm font-medium py-2 px-4 rounded-sm">
                        <Edit class="w-4 h-4 mr-2" />
                        Modifier
                        </Link>

                        <button @click="confirmDelete"
                            class="inline-flex items-center bg-red-600 hover:bg-red-700 text-white text-sm font-medium py-2 px-4 rounded-sm">
                            <Trash2 class="w-4 h-4 mr-2" />
                            Supprimer
                        </button>
                    </div>
                </div>
            </div>
        </template>

        <div class="py-6 sm:py-8 lg:py-12">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

                <!-- Informations principales -->
                <div class="bg-white border border-gray-200 rounded-sm p-6">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                        <!-- Informations de base -->
                        <div>
                            <h3 class="text-base font-medium text-gray-900 mb-4">Informations de base</h3>

                            <div class="space-y-4">
                                <div>
                                    <dt class="text-sm font-medium text-gray-700">Date du traitement</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ formatDate(traitement.date_traitement) }}
                                    </dd>
                                </div>

                                <div>
                                    <dt class="text-sm font-medium text-gray-700">Type de traitement</dt>
                                    <dd class="mt-1">
                                        <span :class="getTypeTraitementClass(traitement.type_traitement)"
                                            class="inline-flex items-center px-2 py-1 rounded text-xs font-medium">
                                            {{ getTypeTraitementLabel(traitement.type_traitement) }}
                                        </span>
                                    </dd>
                                </div>

                                <div>
                                    <dt class="text-sm font-medium text-gray-700">Produit utilisé</dt>
                                    <dd class="mt-1 text-sm text-gray-900 font-medium">{{ traitement.produit_utilise }}
                                    </dd>
                                </div>

                                <div>
                                    <dt class="text-sm font-medium text-gray-700">Dosage</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ traitement.dosage }}</dd>
                                </div>
                            </div>
                        </div>

                        <!-- Détails application -->
                        <div>
                            <h3 class="text-base font-medium text-gray-900 mb-4">Détails d'application</h3>

                            <div class="space-y-4">
                                <div>
                                    <dt class="text-sm font-medium text-gray-700">Méthode d'application</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{
                                        getMethodeApplicationLabel(traitement.methode_application) }}</dd>
                                </div>

                                <div v-if="traitement.temperature_application">
                                    <dt class="text-sm font-medium text-gray-700">Température d'application</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ traitement.temperature_application }}°C
                                    </dd>
                                </div>

                                <div>
                                    <dt class="text-sm font-medium text-gray-700">Efficacité</dt>
                                    <dd class="mt-1">
                                        <span :class="getEfficaciteClass(traitement.efficacite)"
                                            class="text-sm font-medium">
                                            {{ getEfficaciteLabel(traitement.efficacite) }}
                                        </span>
                                    </dd>
                                </div>

                                <div>
                                    <dt class="text-sm font-medium text-gray-700">Ruche concernée</dt>
                                    <dd class="mt-1">
                                        <Link :href="route('ruchers.ruches.show', [rucher.id, ruche.id])"
                                            class="text-sm text-blue-700 hover:text-blue-500 font-medium">
                                        {{ ruche.nom }}
                                        </Link>
                                    </dd>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Effets secondaires -->
                <div v-if="traitement.effets_secondaires" class="bg-white border border-gray-200 rounded-sm p-6">
                    <h3 class="text-base font-medium text-gray-900 mb-4">Effets secondaires observés</h3>
                    <div class="bg-orange-50 border border-orange-200 rounded-sm p-4">
                        <div class="flex">
                            <AlertTriangle class="w-5 h-5 text-orange-400 flex-shrink-0 mt-0.5" />
                            <div class="ml-3">
                                <p class="text-sm text-orange-800 whitespace-pre-line">{{ traitement.effets_secondaires
                                    }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div v-if="traitement.notes" class="bg-white border border-gray-200 rounded-sm p-6">
                    <h3 class="text-base font-medium text-gray-900 mb-4">Notes complémentaires</h3>
                    <div class="bg-gray-50 border border-gray-200 rounded-sm p-4">
                        <p class="text-sm text-gray-700 whitespace-pre-line">{{ traitement.notes }}</p>
                    </div>
                </div>

                <!-- Métadonnées -->
                <div class="bg-white border border-gray-200 rounded-sm p-6">
                    <h3 class="text-base font-medium text-gray-900 mb-4">Informations d'enregistrement</h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                        <div>
                            <dt class="font-medium text-gray-700">Enregistré le</dt>
                            <dd class="mt-1 text-gray-900">{{ formatDateTime(traitement.created_at) }}</dd>
                        </div>

                        <div v-if="traitement.updated_at !== traitement.created_at">
                            <dt class="font-medium text-gray-700">Dernière modification</dt>
                            <dd class="mt-1 text-gray-900">{{ formatDateTime(traitement.updated_at) }}</dd>
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
import { ArrowLeft, Edit, Trash2, Beaker, AlertTriangle } from 'lucide-vue-next';

// Props du contrôleur
const props = defineProps({
    rucher: Object,
    ruche: Object,
    traitement: Object
});

// Helper pour formater les dates
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('fr-FR', {
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    });
}

// Helper pour formater les dates avec heure
function formatDateTime(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('fr-FR', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// Helpers pour les types de traitement
function getTypeTraitementLabel(type) {
    const labels = {
        'varroa': 'Varroa',
        'nosema': 'Nosémose',
        'loque': 'Loque',
        'preventif': 'Préventif',
        'frelon_asiatique': 'Frelon asiatique',
        'autre': 'Autre'
    };
    return labels[type] || type;
}

function getTypeTraitementClass(type) {
    const classes = {
        'varroa': 'bg-red-100 text-red-800',
        'nosema': 'bg-orange-100 text-orange-800',
        'loque': 'bg-purple-100 text-purple-800',
        'preventif': 'bg-blue-100 text-blue-800',
        'frelon_asiatique': 'bg-yellow-100 text-yellow-800',
        'autre': 'bg-gray-100 text-gray-800'
    };
    return classes[type] || 'bg-gray-100 text-gray-800';
}

// Helpers pour les méthodes d'application
function getMethodeApplicationLabel(methode) {
    const labels = {
        'vaporisation': 'Vaporisation',
        'goutte_a_goutte': 'Goutte à goutte',
        'bandes': 'Bandes',
        'poudrage': 'Poudrage',
        'autre': 'Autre'
    };
    return labels[methode] || methode;
}

// Helpers pour l'efficacité
function getEfficaciteLabel(efficacite) {
    const labels = {
        'non_evaluee': 'Non évaluée',
        'excellente': 'Excellente',
        'bonne': 'Bonne',
        'moyenne': 'Moyenne',
        'faible': 'Faible'
    };
    return labels[efficacite] || efficacite;
}

function getEfficaciteClass(efficacite) {
    const classes = {
        'non_evaluee': 'text-gray-600',
        'excellente': 'text-green-600',
        'bonne': 'text-green-500',
        'moyenne': 'text-yellow-600',
        'faible': 'text-orange-600'
    };
    return classes[efficacite] || 'text-gray-600';
}

// Confirmation de suppression
function confirmDelete() {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce traitement ? Cette action est irréversible.')) {
        router.delete(route('ruchers.ruches.traitements.destroy', [props.rucher.id, props.ruche.id, props.traitement.id]));
    }
}
</script>