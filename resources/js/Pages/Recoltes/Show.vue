<template>
    <AppLayout :title="`Récolte du ${formatDate(recolte.date_recolte)}`">
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
                        <Link :href="route('ruchers.ruches.recoltes.index', [rucher.id, ruche.id])" class="hover:text-gray-900">
                            Récoltes
                        </Link>
                        <span>›</span>
                        <span class="text-gray-900 font-medium">{{ formatDate(recolte.date_recolte) }}</span>
                    </div>
                </nav>

                <!-- Titre avec actions -->
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-lg sm:text-xl font-medium text-gray-900">
                            Récolte du {{ formatDate(recolte.date_recolte) }}
                        </h2>
                        <p class="text-sm text-gray-600 mt-1">
                            {{ ruche.nom }} - {{ rucher.nom }}
                        </p>
                    </div>

                    <div class="flex flex-col gap-2 sm:flex-row sm:gap-2">
                        <Link :href="route('ruchers.ruches.recoltes.edit', [rucher.id, ruche.id, recolte.id])"
                            class="bg-gray-900 hover:bg-gray-800 text-white text-sm font-medium py-2 px-4 rounded-sm inline-flex items-center justify-center">
                            <Edit class="w-4 h-4 mr-2" />
                            Modifier
                        </Link>

                        <button @click="showDeleteModal = true"
                            class="bg-red-600 hover:bg-red-700 text-white text-sm font-medium py-2 px-4 rounded-sm inline-flex items-center justify-center">
                            <Trash2 class="w-4 h-4 mr-2" />
                            Supprimer
                        </button>
                    </div>
                </div>
            </div>
        </template>

        <div class="py-6 sm:py-8 lg:py-12">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

                <!-- Message de succès -->
                <div v-if="$page.props.flash?.message" class="mb-6">
                    <div class="bg-gray-100 border border-gray-300 text-gray-900 px-4 py-3 rounded-sm text-sm">
                        {{ $page.props.flash.message }}
                    </div>
                </div>

                <!-- Informations principales -->
                <div class="bg-white border border-gray-200 rounded-sm p-6">
                    <h3 class="text-base font-medium text-gray-900 mb-4">Informations de la récolte</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <!-- Date -->
                        <div class="flex items-center gap-3">
                            <Calendar class="h-5 w-5 text-gray-400" />
                            <div>
                                <div class="text-sm font-medium text-gray-900">Date de récolte</div>
                                <div class="text-sm text-gray-600">{{ formatDate(recolte.date_recolte) }}</div>
                            </div>
                        </div>

                        <!-- Type de miel -->
                        <div class="flex items-center gap-3">
                            <Droplets class="h-5 w-5 text-gray-400" />
                            <div>
                                <div class="text-sm font-medium text-gray-900">Type de miel</div>
                                <div class="flex items-center gap-2 mt-1">
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
                            </div>
                        </div>

                        <!-- Quantité -->
                        <div class="flex items-center gap-3">
                            <Weight class="h-5 w-5 text-gray-400" />
                            <div>
                                <div class="text-sm font-medium text-gray-900">Quantité récoltée</div>
                                <div class="text-lg font-medium text-gray-900">{{ recolte.quantite_kg }} kg</div>
                            </div>
                        </div>

                        <!-- Nombre de cadres -->
                        <div class="flex items-center gap-3">
                            <Grid class="h-5 w-5 text-gray-400" />
                            <div>
                                <div class="text-sm font-medium text-gray-900">Cadres récoltés</div>
                                <div class="text-sm text-gray-600">{{ recolte.nombre_cadres }} cadres</div>
                            </div>
                        </div>

                        <!-- Qualité -->
                        <div class="flex items-center gap-3">
                            <Star class="h-5 w-5 text-gray-400" />
                            <div>
                                <div class="text-sm font-medium text-gray-900">Qualité</div>
                                <div class="mt-1">
                                    <span :class="{
                                        'bg-green-100 text-green-800': recolte.qualite === 'excellente',
                                        'bg-blue-100 text-blue-800': recolte.qualite === 'bonne',
                                        'bg-yellow-100 text-yellow-800': recolte.qualite === 'moyenne',
                                        'bg-red-100 text-red-800': recolte.qualite === 'mediocre'
                                    }" class="text-xs font-medium px-2 py-1 rounded-sm">
                                        {{ getQualiteLabel(recolte.qualite) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Humidité -->
                        <div v-if="recolte.humidite_miel" class="flex items-center gap-3">
                            <Thermometer class="h-5 w-5 text-gray-400" />
                            <div>
                                <div class="text-sm font-medium text-gray-900">Humidité</div>
                                <div class="text-sm text-gray-600">{{ recolte.humidite_miel }}%</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div v-if="recolte.notes_qualite || recolte.notes_degustation" class="bg-white border border-gray-200 rounded-sm p-6">
                    <h3 class="text-base font-medium text-gray-900 mb-4">Notes et observations</h3>
                    
                    <div class="space-y-4">
                        <!-- Notes qualité -->
                        <div v-if="recolte.notes_qualite">
                            <h4 class="text-sm font-medium text-gray-900 mb-2">Notes sur la qualité</h4>
                            <p class="text-sm text-gray-600 leading-relaxed">{{ recolte.notes_qualite }}</p>
                        </div>

                        <!-- Notes dégustation -->
                        <div v-if="recolte.notes_degustation">
                            <h4 class="text-sm font-medium text-gray-900 mb-2">Notes de dégustation</h4>
                            <p class="text-sm text-gray-600 leading-relaxed">{{ recolte.notes_degustation }}</p>
                        </div>
                    </div>
                </div>

                <!-- Actions rapides -->
                <div class="bg-white border border-gray-200 rounded-sm p-6">
                    <h3 class="text-base font-medium text-gray-900 mb-4">Actions</h3>
                    
                    <div class="flex flex-col gap-3 sm:flex-row sm:gap-4">
                        <Link :href="route('ruchers.ruches.recoltes.index', [rucher.id, ruche.id])"
                            class="bg-white hover:bg-gray-50 text-gray-700 border border-gray-300 text-sm font-medium py-2 px-4 rounded-sm text-center">
                            ← Retour aux récoltes
                        </Link>
                        
                        <Link :href="route('ruchers.ruches.recoltes.create', [rucher.id, ruche.id])"
                            class="bg-gray-900 hover:bg-gray-800 text-white text-sm font-medium py-2 px-4 rounded-sm text-center">
                            + Nouvelle récolte
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de confirmation de suppression -->
        <div v-if="showDeleteModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="showDeleteModal = false"></div>

                <div class="inline-block align-bottom bg-white rounded-sm text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                <AlertTriangle class="h-6 w-6 text-red-600" />
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                    Supprimer cette récolte
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">
                                        Êtes-vous sûr de vouloir supprimer cette récolte du {{ formatDate(recolte.date_recolte) }} ? 
                                        Cette action est irréversible.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <form @submit.prevent="deleteRecolte" class="w-full sm:w-auto">
                            <button type="submit" :disabled="deleteForm.processing"
                                class="w-full inline-flex justify-center rounded-sm border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50">
                                <span v-if="deleteForm.processing">Suppression...</span>
                                <span v-else>Supprimer</span>
                            </button>
                        </form>
                        <button type="button" @click="showDeleteModal = false"
                            class="mt-3 w-full inline-flex justify-center rounded-sm border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Annuler
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { 
    Edit, Trash2, Calendar, Droplets, Weight, Grid, Star, 
    Thermometer, AlertTriangle 
} from 'lucide-vue-next';

// Props du contrôleur
const props = defineProps({
    rucher: Object,
    ruche: Object,
    recolte: Object
});

// État pour le modal
const showDeleteModal = ref(false);

// Formulaire pour la suppression
const deleteForm = useForm({});

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

// Supprimer la récolte
function deleteRecolte() {
    deleteForm.delete(route('ruchers.ruches.recoltes.destroy', [props.rucher.id, props.ruche.id, props.recolte.id]));
}
</script>