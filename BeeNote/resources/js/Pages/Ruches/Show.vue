<template>
    <AppLayout :title="`${ruche.nom} - ${rucher.nom}`">
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
                        <span class="text-gray-900 font-medium">{{ ruche.nom }}</span>
                    </div>
                </nav>

                <!-- Titre avec badge et actions -->
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-3">
                        <!-- Couleur de la ruche -->
                        <div v-if="ruche.couleur" :style="{ backgroundColor: ruche.couleur }"
                            class="w-4 h-4 rounded-full border border-gray-300 flex-shrink-0">
                        </div>

                        <div class="min-w-0 flex-1">
                            <h2 class="text-lg sm:text-xl font-medium text-gray-900 truncate">
                                {{ ruche.nom }}
                            </h2>
                            <span :class="{
                                'bg-gray-100 text-gray-800': ruche.statut === 'active',
                                'bg-gray-100 text-gray-600': ruche.statut === 'inactive',
                                'bg-gray-100 text-gray-900': ruche.statut === 'morte'
                            }" class="inline-flex items-center px-2 py-1 rounded-sm text-xs font-medium mt-1">
                                <Activity class="w-3 h-3 mr-1" />
                                {{ getStatutLabel(ruche.statut) }}
                            </span>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex flex-col gap-2 sm:flex-row sm:gap-2">
                        <Link :href="route('ruchers.ruches.edit', [rucher.id, ruche.id])"
                            class="bg-gray-900 hover:bg-gray-800 text-white text-sm font-medium py-2 px-3 rounded-sm text-center">
                        Modifier
                        </Link>

                        <div class="flex gap-2">
                            <button @click="confirmDelete"
                                class="flex-1 bg-white hover:bg-gray-50 text-red-600 border border-gray-300 text-sm font-medium py-2 px-3 rounded-sm sm:flex-none">
                                Supprimer
                            </button>

                            <Link :href="route('ruchers.ruches.index', rucher.id)"
                                class="flex-1 bg-white hover:bg-gray-50 text-gray-700 border border-gray-300 text-sm font-medium py-2 px-3 rounded-sm text-center sm:flex-none">
                            Retour
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <div class="py-6 sm:py-8 lg:py-12">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                    <!-- Informations principales -->
                    <div class="lg:col-span-2">
                        <div class="bg-white border border-gray-200 rounded-sm p-4 sm:p-6">
                            <h3 class="text-base font-medium text-gray-900 mb-6">Informations générales</h3>

                            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
                                <!-- Colonne 1 -->
                                <div class="space-y-4">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-700">Nom</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ ruche.nom }}</dd>
                                    </div>

                                    <div>
                                        <dt class="text-sm font-medium text-gray-700">Type</dt>
                                        <dd class="mt-1 text-sm text-gray-900 capitalize">{{ ruche.type || 'Non spécifié' }}
                                        </dd>
                                    </div>

                                    <div>
                                        <dt class="text-sm font-medium text-gray-700">Statut</dt>
                                        <dd class="mt-1">
                                            <span :class="{
                                                'text-gray-800': ruche.statut === 'active',
                                                'text-gray-600': ruche.statut === 'inactive',
                                                'text-gray-900': ruche.statut === 'morte'
                                            }" class="text-sm font-medium">
                                                {{ getStatutLabel(ruche.statut) }}
                                            </span>
                                        </dd>
                                    </div>

                                    <div v-if="ruche.couleur">
                                        <dt class="text-sm font-medium text-gray-700">Couleur</dt>
                                        <dd class="mt-1 flex items-center gap-2">
                                            <div :style="{ backgroundColor: ruche.couleur }"
                                                class="w-3 h-3 rounded border border-gray-300"></div>
                                            <span class="text-sm text-gray-900">{{ ruche.couleur }}</span>
                                        </dd>
                                    </div>
                                </div>

                                <!-- Colonne 2 -->
                                <div class="space-y-4">
                                    <div v-if="ruche.date_installation">
                                        <dt class="text-sm font-medium text-gray-700">Date d'installation</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ formatDate(ruche.date_installation) }}
                                        </dd>
                                    </div>

                                    <div>
                                        <dt class="text-sm font-medium text-gray-700">Rucher</dt>
                                        <dd class="mt-1">
                                            <Link :href="route('ruchers.show', rucher.id)"
                                                class="text-sm text-blue-700 hover:text-blue-500 font-medium">
                                            {{ rucher.nom }}
                                            </Link>
                                        </dd>
                                    </div>

                                    <div>
                                        <dt class="text-sm font-medium text-gray-700">Créée le</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ formatDate(ruche.created_at) }}</dd>
                                    </div>
                                </div>
                            </dl>

                            <!-- Notes -->
                            <div v-if="ruche.notes" class="mt-6 pt-6 border-t border-gray-200">
                                <dt class="text-sm font-medium text-gray-700 mb-2">Notes</dt>
                                <dd class="text-sm text-gray-900 leading-relaxed whitespace-pre-line">{{ ruche.notes }}
                                </dd>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">
                        <!-- Informations reine -->
                        <div class="bg-white border border-gray-200 rounded-sm p-4 sm:p-6">
                            <h3 class="text-base font-medium text-gray-900 mb-4 flex items-center">
                                <Crown class="w-4 h-4 mr-2" />
                                Informations reine
                            </h3>

                            <div class="space-y-4">
                                <div v-if="ruche.annee_reine">
                                    <dt class="text-sm font-medium text-gray-700">Année de naissance</dt>
                                    <dd class="mt-1">
                                        <span class="text-lg font-medium text-gray-900">{{ ruche.annee_reine }}</span>
                                        <span class="ml-2 text-xs text-gray-600">
                                            ({{ getAgeReine(ruche.annee_reine) }} an{{ getAgeReine(ruche.annee_reine) >
                                            1 ? 's'
                                            : '' }})
                                        </span>
                                    </dd>
                                </div>

                                <div v-if="ruche.couleur_marquage_reine">
                                    <dt class="text-sm font-medium text-gray-700">Marquage</dt>
                                    <dd class="mt-1 flex items-center gap-2">
                                        <div :style="{ backgroundColor: getReineColor(ruche.couleur_marquage_reine) }"
                                            class="w-4 h-4 rounded-full border border-gray-300"></div>
                                        <span class="text-sm text-gray-900 capitalize">{{ ruche.couleur_marquage_reine
                                            }}</span>
                                    </dd>
                                </div>

                                <div v-if="!ruche.annee_reine && !ruche.couleur_marquage_reine">
                                    <p class="text-sm text-gray-600 italic">Aucune information sur la reine</p>
                                </div>
                            </div>
                        </div>

                        <!-- Actions rapides -->
                        <div class="bg-white border border-gray-200 rounded-sm p-4 sm:p-6">
                            <h3 class="text-base font-medium text-gray-900 mb-4">Actions rapides</h3>

                            <div class="space-y-2">
                                <!-- Par ce Link fonctionnel : -->
                                <!-- Tous les boutons avec le même style -->
                                <Link :href="route('ruchers.ruches.visites.create', [rucher.id, ruche.id])"
                                    class="w-full bg-gray-900 hover:bg-gray-800 text-white text-sm font-medium py-3 px-4 rounded-sm flex items-center justify-center">
                                    <Plus class="w-4 h-4 mr-2" />
                                    Nouvelle visite
                                </Link>

                                <Link :href="route('ruchers.ruches.traitements.create', [rucher.id, ruche.id])"
                                    class="w-full bg-gray-900 hover:bg-gray-800 text-white text-sm font-medium py-3 px-4 rounded-sm flex items-center justify-center">
                                    <Beaker class="w-4 h-4 mr-2" />
                                    Nouveau traitement
                                </Link>

                                <Link :href="route('ruchers.ruches.recoltes.create', [rucher.id, ruche.id])"
                                    class="w-full bg-gray-900 hover:bg-gray-800 text-white text-sm font-medium py-3 px-4 rounded-sm flex items-center justify-center">
                                    <Plus class="w-4 h-4 mr-2" />
                                    Nouvelle récolte
                                </Link>

                                <button @click="showMoveModal = true"
                                    class="w-full bg-white hover:bg-gray-50 text-gray-900 border border-gray-300 text-sm font-medium py-3 px-4 rounded-sm flex items-center justify-center">
                                    <Truck class="w-4 h-4 mr-2" />
                                    Déplacer la ruche
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal de déplacement -->
                <div v-if="showMoveModal"
                    class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50"
                    @click="showMoveModal = false">
                    <div class="bg-white rounded-sm border border-gray-200 w-full max-w-md p-6" @click.stop>
                        <h3 class="text-base font-medium text-gray-900 mb-4">
                            Déplacer la ruche
                        </h3>

                        <form @submit.prevent="moveRuche">
                            <div class="mb-6">
                                <label for="rucher_destination" class="block text-sm font-medium text-gray-700 mb-2">
                                    Rucher de destination
                                </label>
                                <select id="rucher_destination" v-model="moveForm.rucher_id"
                                    class="w-full border border-gray-300 rounded-sm px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-gray-900 focus:border-gray-900"
                                    required>
                                    <option value="">Sélectionner un rucher</option>
                                    <option v-for="rucherOption in props.availableRuchers" :key="rucherOption.id"
                                        :value="rucherOption.id">
                                        {{ rucherOption.nom }}
                                    </option>
                                </select>
                                <div v-if="moveForm.errors.rucher_id" class="text-red-600 text-sm mt-1">
                                    {{ moveForm.errors.rucher_id }}
                                </div>
                            </div>

                            <div class="flex gap-3">
                                <button type="button" @click="showMoveModal = false"
                                    class="flex-1 bg-white hover:bg-gray-50 text-gray-700 border border-gray-300 text-sm font-medium py-2 px-4 rounded-sm">
                                    Annuler
                                </button>
                                <button type="submit" :disabled="moveForm.processing"
                                    class="flex-1 bg-gray-900 hover:bg-gray-800 text-white text-sm font-medium py-2 px-4 rounded-sm disabled:opacity-50">
                                    <span v-if="moveForm.processing">Déplacement...</span>
                                    <span v-else>Déplacer</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Historique -->
<div class="mt-8">
    <div class="bg-white border border-gray-200 rounded-sm p-4 sm:p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-base font-medium text-gray-900">Historique de la ruche</h3>
            <div class="text-xs text-gray-600">
                {{ getTotalHistorique() }} entrée{{ getTotalHistorique() > 1 ? 's' : '' }}
            </div>
        </div>

        <!-- État vide -->
        <div v-if="getTotalHistorique() === 0" class="text-center py-12">
            <Package class="mx-auto h-8 w-8 text-gray-400 mb-4" />
            <p class="text-sm text-gray-600 max-w-sm mx-auto">
                Aucune activité enregistrée pour cette ruche.
            </p>
        </div>

        <!-- Timeline unifiée -->
<div v-else class="space-y-3">
    <Link v-for="item in getHistoriqueUnifie()" :key="`${item.type}-${item.id}`" 
        :href="getItemDetailUrl(item)"
        class="flex items-start gap-3 p-3 border border-gray-200 rounded-sm hover:bg-gray-50 cursor-pointer">
        
        <!-- Icône selon le type -->
        <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center"
            :class="getTypeClass(item.type)">
            <Beaker v-if="item.type === 'traitement'" class="w-4 h-4" />
            <Eye v-else-if="item.type === 'visite'" class="w-4 h-4" />
            <Package v-else-if="item.type === 'recolte'" class="w-4 h-4" />
        </div>

        <!-- Contenu -->
        <div class="flex-1 min-w-0">
            <div class="flex items-center justify-between">
                <h4 class="text-sm font-medium text-gray-900">
                    {{ getItemTitle(item) }}
                </h4>
                <time class="text-xs text-gray-600">
                    {{ formatDate(getItemDate(item)) }}
                </time>
            </div>
            
            <p class="text-sm text-gray-600 mt-1">
                {{ getItemDescription(item) }}
            </p>
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
import { Link, router, useForm } from '@inertiajs/vue3';
import { Activity, Crown, Plus, Package, Truck, Beaker, Eye } from 'lucide-vue-next';
import { ref } from 'vue';

// État du modal
const showMoveModal = ref(false);

// Formulaire de déplacement
const moveForm = useForm({
    rucher_id: ''
});

// Props du contrôleur
const props = defineProps({
    rucher: Object,
    ruche: Object,
    availableRuchers: Array
});

// Déplacer la ruche
function moveRuche() {
    moveForm.patch(route('ruchers.ruches.move', [props.rucher.id, props.ruche.id]), {
        onSuccess: () => {
            showMoveModal.value = false;
        }
    });
}

// Helper pour formater les dates
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('fr-FR', {
        day: 'numeric',
        month: 'long',
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

// Helper âge reine
function getAgeReine(anneeReine) {
    return new Date().getFullYear() - anneeReine;
}

// Confirmation de suppression
function confirmDelete() {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette ruche ? Cette action est irréversible.')) {
        router.delete(route('ruchers.ruches.destroy', [props.rucher.id, props.ruche.id]));
    }
}

// Helper pour trier les traitements par date
function getSortedTraitements() {
    if (!props.ruche.traitements) return [];
    
    return [...props.ruche.traitements].sort((a, b) => {
        return new Date(b.date_traitement) - new Date(a.date_traitement);
    });
}

// Helper pour les types de traitement (réutilise celui existant)
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

// Calculer le total d'entrées
function getTotalHistorique() {
    return (props.ruche.traitements?.length || 0) +
           (props.ruche.visites?.length || 0) +
           (props.ruche.recoltes?.length || 0);
}

// Créer un historique unifié et trié
function getHistoriqueUnifie() {
    const items = [];
    
    // Ajouter les traitements
    if (props.ruche.traitements) {
        props.ruche.traitements.forEach(traitement => {
            items.push({ ...traitement, type: 'traitement' });
        });
    }
    
    // Ajouter les visites
    if (props.ruche.visites) {
        props.ruche.visites.forEach(visite => {
            items.push({ ...visite, type: 'visite' });
        });
    }
    
    // Ajouter les récoltes
    if (props.ruche.recoltes) {
        props.ruche.recoltes.forEach(recolte => {
            items.push({ ...recolte, type: 'recolte' });
        });
    }
    
    // Trier par date décroissante
    return items.sort((a, b) => {
        const dateA = new Date(getItemDate(a));
        const dateB = new Date(getItemDate(b));
        return dateB - dateA;
    });
}

// Obtenir la date selon le type
function getItemDate(item) {
    switch(item.type) {
        case 'traitement': return item.date_traitement;
        case 'visite': return item.date_visite;
        case 'recolte': return item.date_recolte;
        default: return item.created_at;
    }
}

// Titre selon le type
function getItemTitle(item) {
    switch(item.type) {
        case 'traitement': 
            return `Traitement ${getTypeTraitementLabel(item.type_traitement)}`;
        case 'visite': 
            return 'Visite de contrôle';
        case 'recolte': 
            return `Récolte ${item.type_miel || ''}`;
        default: 
            return 'Activité';
    }
}

// Description selon le type
function getItemDescription(item) {
    switch(item.type) {
        case 'traitement': 
            return `${item.produit_utilise} - ${item.dosage}`;
        case 'visite': 
            return item.notes_generales || 'Visite d\'inspection';
        case 'recolte': 
            return `${item.quantite_kg}kg - ${item.nombre_cadres} cadres`;
        default: 
            return '';
    }
}

// Classes CSS selon le type
function getTypeClass(type) {
    const classes = {
        'traitement': 'bg-blue-100 text-blue-600',
        'visite': 'bg-green-100 text-green-600',
        'recolte': 'bg-yellow-100 text-yellow-600'
    };
    return classes[type] || 'bg-gray-100 text-gray-600';
}

// URL de détail selon le type
function getItemDetailUrl(item) {
    switch(item.type) {
        case 'traitement': 
            return route('ruchers.ruches.traitements.show', [props.rucher.id, props.ruche.id, item.id]);
        case 'visite': 
            return route('ruchers.ruches.visites.show', [props.rucher.id, props.ruche.id, item.id]);
        case 'recolte': 
            return route('ruchers.ruches.recoltes.show', [props.rucher.id, props.ruche.id, item.id]);
        default: 
            return '#';
    }
}
</script>