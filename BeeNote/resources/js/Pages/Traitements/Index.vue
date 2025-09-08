<template>
    <AppLayout :title="`Traitements - ${ruche.nom} - ${rucher.nom}`">
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
                        <span class="text-gray-900 font-medium">Traitements</span>
                    </div>
                </nav>

                <!-- Titre avec actions -->
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-3">
                        <!-- Couleur de la ruche -->
                        <div v-if="ruche.couleur" 
                            :style="{ backgroundColor: ruche.couleur }"
                            class="w-4 h-4 rounded-full border border-gray-300 flex-shrink-0">
                        </div>

                        <div class="min-w-0 flex-1">
                            <h2 class="text-lg sm:text-xl font-medium text-gray-900">
                                Traitements - {{ ruche.nom }}
                            </h2>
                            <p class="text-sm text-gray-600">
                                Historique des traitements appliqués
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <Link :href="route('ruchers.ruches.show', [rucher.id, ruche.id])"
                            class="inline-flex items-center bg-white hover:bg-gray-50 text-gray-700 border border-gray-300 text-sm font-medium py-2 px-4 rounded-sm">
                            <ArrowLeft class="w-4 h-4 mr-2" />
                            Retour à la ruche
                        </Link>
                        
                        <Link :href="route('ruchers.ruches.traitements.create', [rucher.id, ruche.id])"
                            class="inline-flex items-center bg-gray-900 hover:bg-gray-800 text-white text-sm font-medium py-2 px-4 rounded-sm">
                            <Plus class="w-4 h-4 mr-2" />
                            Nouveau traitement
                        </Link>
                    </div>
                </div>
            </div>
        </template>

        <div class="py-6 sm:py-8 lg:py-12">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <!-- État vide -->
                <div v-if="traitements.length === 0" class="bg-white border border-gray-200 rounded-sm p-8 sm:p-12 text-center">
                    <div class="flex flex-col items-center">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                            <Beaker class="w-8 h-8 text-gray-400" />
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">
                            Aucun traitement enregistré
                        </h3>
                        <p class="text-gray-600 mb-6 max-w-sm">
                            Commencez par enregistrer le premier traitement pour cette ruche.
                        </p>
                        <Link :href="route('ruchers.ruches.traitements.create', [rucher.id, ruche.id])"
                            class="inline-flex items-center bg-gray-900 hover:bg-gray-800 text-white text-sm font-medium py-3 px-4 rounded-sm">
                            <Plus class="w-4 h-4 mr-2" />
                            Ajouter un traitement
                        </Link>
                    </div>
                </div>

                <!-- Liste des traitements -->
                <div v-else class="bg-white border border-gray-200 rounded-sm overflow-hidden">
                    <!-- En-tête -->
                    <div class="px-4 sm:px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                            <h3 class="text-base font-medium text-gray-900">
                                {{ traitements.length }} traitement{{ traitements.length > 1 ? 's' : '' }}
                            </h3>
                            <span class="text-xs text-gray-600">
                                Triés par date (plus récent en premier)
                            </span>
                        </div>
                    </div>

                    <!-- Liste -->
                    <div class="divide-y divide-gray-200">
                        <Link v-for="traitement in traitements" :key="traitement.id"
                            :href="route('ruchers.ruches.traitements.show', [rucher.id, ruche.id, traitement.id])"
                            class="block p-4 sm:p-6 hover:bg-gray-50 transition-colors">
                            
                            <div class="flex items-start justify-between">
                                <div class="flex-1 min-w-0">
                                    <!-- Date et type -->
                                    <div class="flex items-center gap-3 mb-2">
                                        <time class="text-sm font-medium text-gray-900">
                                            {{ formatDate(traitement.date_traitement) }}
                                        </time>
                                        <span :class="getTypeTraitementClass(traitement.type_traitement)"
                                            class="inline-flex items-center px-2 py-1 rounded text-xs font-medium">
                                            {{ getTypeTraitementLabel(traitement.type_traitement) }}
                                        </span>
                                    </div>

                                    <!-- Produit utilisé -->
                                    <div class="flex items-center gap-2 mb-2">
                                        <Beaker class="w-4 h-4 text-gray-400 flex-shrink-0" />
                                        <span class="text-sm text-gray-900 font-medium">
                                            {{ traitement.produit_utilise }}
                                        </span>
                                        <span v-if="traitement.dosage" class="text-sm text-gray-600">
                                            ({{ traitement.dosage }})
                                        </span>
                                    </div>

                                    <!-- Méthode et efficacité -->
                                    <div class="flex items-center gap-4 text-xs text-gray-600">
                                        <span v-if="traitement.methode_application">
                                            {{ getMethodeApplicationLabel(traitement.methode_application) }}
                                        </span>
                                        <span v-if="traitement.efficacite && traitement.efficacite !== 'non_evaluee'" 
                                            :class="getEfficaciteClass(traitement.efficacite)">
                                            Efficacité: {{ getEfficaciteLabel(traitement.efficacite) }}
                                        </span>
                                        <span v-if="traitement.temperature_application">
                                            {{ traitement.temperature_application }}°C
                                        </span>
                                    </div>

                                    <!-- Notes (aperçu) -->
                                    <p v-if="traitement.notes" class="text-sm text-gray-600 mt-2 line-clamp-2">
                                        {{ traitement.notes }}
                                    </p>
                                </div>

                                <!-- Flèche -->
                                <div class="ml-4 flex-shrink-0">
                                    <ArrowRight class="w-4 h-4 text-gray-400" />
                                </div>
                            </div>
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
import { Plus, ArrowLeft, ArrowRight, Beaker } from 'lucide-vue-next';

// Props du contrôleur
const props = defineProps({
    rucher: Object,
    ruche: Object,
    traitements: Array
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

// Helpers pour les types de traitement
function getTypeTraitementLabel(type) {
    const labels = {
        'preventif': 'Préventif',
        'curatif': 'Curatif', 
        'nourrissement': 'Nourrissement',
        'autre': 'Autre'
    };
    return labels[type] || type;
}

function getTypeTraitementClass(type) {
    const classes = {
        'preventif': 'bg-blue-100 text-blue-800',
        'curatif': 'bg-red-100 text-red-800',
        'nourrissement': 'bg-green-100 text-green-800',
        'autre': 'bg-gray-100 text-gray-800'
    };
    return classes[type] || 'bg-gray-100 text-gray-800';
}

// Helpers pour les méthodes d'application
function getMethodeApplicationLabel(methode) {
    const labels = {
        'pulverisation': 'Pulvérisation',
        'fumigation': 'Fumigation',
        'nourrisseur': 'Nourrisseur',
        'contact': 'Contact',
        'autre': 'Autre'
    };
    return labels[methode] || methode;
}

// Helpers pour l'efficacité
function getEfficaciteLabel(efficacite) {
    const labels = {
        'excellente': 'Excellente',
        'bonne': 'Bonne',
        'moyenne': 'Moyenne',
        'faible': 'Faible',
        'aucune': 'Aucune'
    };
    return labels[efficacite] || efficacite;
}

function getEfficaciteClass(efficacite) {
    const classes = {
        'excellente': 'text-green-600 font-medium',
        'bonne': 'text-green-500',
        'moyenne': 'text-yellow-600',
        'faible': 'text-orange-600',
        'aucune': 'text-red-600'
    };
    return classes[efficacite] || 'text-gray-600';
}
</script>