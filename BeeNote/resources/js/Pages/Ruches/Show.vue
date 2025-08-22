<template>
    <AppLayout :title="`${ruche.nom} - ${rucher.nom}`">
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
                    <Link :href="route('ruchers.ruches.index', rucher.id)" class="hover:text-gray-700">
                    Ruches
                    </Link>
                    <span class="mx-2">›</span>
                    <span class="text-gray-900">{{ ruche.nom }}</span>
                </nav>

                <!-- Titre avec actions -->
                <div class="flex justify-between items-center">
                    <div class="flex items-center space-x-3">
                        <!-- Couleur de la ruche -->
                        <div v-if="ruche.couleur" :style="{ backgroundColor: ruche.couleur }"
                            class="w-6 h-6 rounded-full border-2 border-gray-300" :title="`Couleur: ${ruche.couleur}`">
                        </div>

                        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                            {{ ruche.nom }}
                        </h2>

                        <!-- Badge statut -->
                        <span :class="{
                            'bg-green-100 text-green-800': ruche.statut === 'active',
                            'bg-yellow-100 text-yellow-800': ruche.statut === 'inactive',
                            'bg-red-100 text-red-800': ruche.statut === 'morte'
                        }" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                            <Activity class="w-3 h-3 mr-1" />
                            {{ getStatutLabel(ruche.statut) }}
                        </span>
                    </div>

                    <div class="space-x-2">
                        <Link :href="route('ruchers.ruches.edit', [rucher.id, ruche.id])"
                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Modifier
                        </Link>

                        <button @click="confirmDelete"
                            class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                            Supprimer
                        </button>

                        <Link :href="route('ruchers.ruches.index', rucher.id)"
                            class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        Retour aux ruches
                        </Link>
                    </div>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                    <!-- Informations principales -->
                    <div class="lg:col-span-2">
                        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                            <h3 class="text-lg font-semibold mb-6">Informations générales</h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Colonne 1 -->
                                <div class="space-y-4">
                                    <div>
                                        <span class="font-medium text-gray-700">Nom :</span>
                                        <span class="ml-2">{{ ruche.nom }}</span>
                                    </div>

                                    <div>
                                        <span class="font-medium text-gray-700">Type :</span>
                                        <span class="ml-2 capitalize">{{ ruche.type || 'Non spécifié' }}</span>
                                    </div>

                                    <div>
                                        <span class="font-medium text-gray-700">Statut :</span>
                                        <span :class="{
                                            'text-green-600': ruche.statut === 'active',
                                            'text-yellow-600': ruche.statut === 'inactive',
                                            'text-red-600': ruche.statut === 'morte'
                                        }" class="ml-2 font-medium">
                                            {{ getStatutLabel(ruche.statut) }}
                                        </span>
                                    </div>

                                    <div v-if="ruche.couleur">
                                        <span class="font-medium text-gray-700">Couleur :</span>
                                        <div class="ml-2 inline-flex items-center">
                                            <div :style="{ backgroundColor: ruche.couleur }"
                                                class="w-4 h-4 rounded border border-gray-300 mr-2"></div>
                                            {{ ruche.couleur }}
                                        </div>
                                    </div>
                                </div>

                                <!-- Colonne 2 -->
                                <div class="space-y-4">
                                    <div v-if="ruche.date_installation">
                                        <span class="font-medium text-gray-700">Date d'installation :</span>
                                        <span class="ml-2">{{ formatDate(ruche.date_installation) }}</span>
                                    </div>

                                    <div>
                                        <span class="font-medium text-gray-700">Rucher :</span>
                                        <Link :href="route('ruchers.show', rucher.id)"
                                            class="ml-2 text-blue-600 hover:text-blue-800">
                                        {{ rucher.nom }}
                                        </Link>
                                    </div>

                                    <div>
                                        <span class="font-medium text-gray-700">Créée le :</span>
                                        <span class="ml-2">{{ formatDate(ruche.created_at) }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Notes -->
                            <div v-if="ruche.notes" class="mt-6 pt-6 border-t">
                                <span class="font-medium text-gray-700">Notes :</span>
                                <p class="mt-2 text-gray-600 whitespace-pre-line">{{ ruche.notes }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Informations reine -->
                    <div>
                        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                            <h3 class="text-lg font-semibold mb-6 flex items-center">
                                <Crown class="w-5 h-5 mr-2 text-yellow-600" />
                                Informations reine
                            </h3>

                            <div class="space-y-4">
                                <div v-if="ruche.annee_reine">
                                    <span class="font-medium text-gray-700">Année de naissance :</span>
                                    <div class="mt-1">
                                        <span class="text-lg font-bold">{{ ruche.annee_reine }}</span>
                                        <span class="ml-2 text-sm text-gray-500">
                                            ({{ getAgeReine(ruche.annee_reine) }} an{{ getAgeReine(ruche.annee_reine) >
                                                1 ? 's'
                                                : '' }})
                                        </span>
                                    </div>
                                </div>

                                <div v-if="ruche.couleur_marquage_reine">
                                    <span class="font-medium text-gray-700">Marquage :</span>
                                    <div class="mt-1 flex items-center">
                                        <div :style="{ backgroundColor: getReineColor(ruche.couleur_marquage_reine) }"
                                            class="w-6 h-6 rounded-full border-2 border-gray-300 mr-2"></div>
                                        <span class="capitalize">{{ ruche.couleur_marquage_reine }}</span>
                                    </div>
                                </div>

                                <div v-if="!ruche.annee_reine && !ruche.couleur_marquage_reine">
                                    <p class="text-gray-500 italic">Aucune information sur la reine</p>
                                </div>
                            </div>
                        </div>

                        <!-- Actions rapides -->
                        <div class="mt-6 bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                            <h3 class="text-lg font-semibold mb-4">Actions rapides</h3>

                            <div class="space-y-3">
                                <button
                                    class="w-full bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded flex items-center justify-center">
                                    <Plus class="w-4 h-4 mr-2" />
                                    Nouvelle visite
                                </button>

                                <button
                                    class="w-full bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded flex items-center justify-center">
                                    <Plus class="w-4 h-4 mr-2" />
                                    Nouveau traitement
                                </button>

                                <button
                                    class="w-full bg-orange-500 hover:bg-orange-700 text-white font-bold py-2 px-4 rounded flex items-center justify-center">
                                    <Plus class="w-4 h-4 mr-2" />
                                    Nouvelle récolte
                                </button>

                                <button @click="showMoveModal = true"
                                    class="w-full bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded flex items-center justify-center">
                                    <Truck class="w-4 h-4 mr-2" />
                                    Déplacer la ruche
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal de déplacement -->
                <div v-if="showMoveModal"
                    class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
                    @click="showMoveModal = false">
                    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white" @click.stop>
                        <div class="mt-3">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">
                                Déplacer la ruche
                            </h3>

                            <form @submit.prevent="moveRuche">
                                <div class="mb-4">
                                    <label for="rucher_destination" class="block text-sm font-medium text-gray-700">
                                        Rucher de destination
                                    </label>
                                    <select id="rucher_destination" v-model="moveForm.rucher_id"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
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

                                <div class="flex items-center justify-end space-x-4">
                                    <button type="button" @click="showMoveModal = false"
                                        class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                        Annuler
                                    </button>
                                    <button type="submit" :disabled="moveForm.processing"
                                        class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded disabled:opacity-50">
                                        <span v-if="moveForm.processing">Déplacement...</span>
                                        <span v-else>Déplacer</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Placeholder pour futures fonctionnalités -->
                <div class="mt-8">
                    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                        <h3 class="text-lg font-semibold mb-4">Historique de la ruche</h3>
                        <div class="text-center py-8">
                            <Package class="mx-auto h-12 w-12 text-gray-400" />
                            <p class="mt-2 text-gray-500">
                                Visites, traitements et récoltes apparaîtront ici une fois ces fonctionnalités
                                implémentées.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router, useForm } from '@inertiajs/vue3';  // ✅ Ajouter useForm
import { Activity, Crown, Plus, Package, Truck } from 'lucide-vue-next';  // ✅ Ajouter Truck
import { ref, onMounted } from 'vue';

// État du modal
const showMoveModal = ref(false);
const availableRuchers = ref([]);

// Formulaire de déplacement
const moveForm = useForm({
    rucher_id: ''
});



// Déplacer la ruche
function moveRuche() {
    moveForm.patch(route('ruchers.ruches.move', [props.rucher.id, props.ruche.id]), {
        onSuccess: () => {
            showMoveModal.value = false;
        }
    });
}

// Props du contrôleur
const props = defineProps({
    rucher: Object,
    ruche: Object,
    availableRuchers: Array
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
</script>