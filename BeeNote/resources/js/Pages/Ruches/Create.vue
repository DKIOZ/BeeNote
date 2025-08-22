<template>
    <AppLayout :title="`Nouvelle ruche - ${rucher.nom}`">
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
                    <span class="text-gray-900">Nouvelle ruche</span>
                </nav>

                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Ajouter une ruche à {{ rucher.nom }}
                </h2>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">

                    <form @submit.prevent="submit">

                        <!-- Nom de la ruche -->
                        <div class="mb-4">
                            <label for="nom" class="block text-sm font-medium text-gray-700">
                                Nom de la ruche *
                            </label>
                            <input id="nom" v-model="form.nom" type="text" placeholder="ex: R1, Ruche du Tilleul..."
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required />
                            <div v-if="form.errors.nom" class="text-red-600 text-sm mt-1">
                                {{ form.errors.nom }}
                            </div>
                        </div>

                        <!-- Couleur de la ruche -->
                        <div class="mb-4">
                            <label for="couleur" class="block text-sm font-medium text-gray-700">
                                Couleur de la ruche
                            </label>
                            <div class="mt-1 flex items-center space-x-4">
                                <input id="couleur" v-model="form.couleur" type="color"
                                    class="h-10 w-20 rounded border border-gray-300" />
                                <input v-model="form.couleur" type="text" placeholder="#ffffff ou nom de couleur"
                                    class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                            </div>
                            <div v-if="form.errors.couleur" class="text-red-600 text-sm mt-1">
                                {{ form.errors.couleur }}
                            </div>
                        </div>

                        <!-- Type de ruche -->
                        <div class="mb-4">
                            <label for="type" class="block text-sm font-medium text-gray-700">
                                Type de ruche
                            </label>
                            <select id="type" v-model="form.type"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Sélectionner un type</option>
                                <option value="dadant">Dadant</option>
                                <option value="warre">Warré</option>
                                <option value="langstroth">Langstroth</option>
                                <option value="ktbh">KTBH (Kényane)</option>
                                <option value="autre">Autre</option>
                            </select>
                            <div v-if="form.errors.type" class="text-red-600 text-sm mt-1">
                                {{ form.errors.type }}
                            </div>
                        </div>

                        <!-- Statut -->
                        <div class="mb-4">
                            <label for="statut" class="block text-sm font-medium text-gray-700">
                                Statut de la ruche
                            </label>
                            <select id="statut" v-model="form.statut"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="morte">Morte</option>
                            </select>
                            <div v-if="form.errors.statut" class="text-red-600 text-sm mt-1">
                                {{ form.errors.statut }}
                            </div>
                        </div>

                        <!-- Informations reine -->
                        <div class="mb-6 p-4 bg-yellow-50 rounded-lg">
                            <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                                <Crown class="w-5 h-5 mr-2 text-yellow-600" />
                                Informations sur la reine
                            </h3>

                            <div class="grid grid-cols-2 gap-4">
                                <!-- Année de la reine -->
                                <div>
                                    <label for="annee_reine" class="block text-sm font-medium text-gray-700">
                                        Année de la reine
                                    </label>
                                    <input id="annee_reine" v-model="form.annee_reine" type="number"
                                        :min="new Date().getFullYear() - 5" :max="new Date().getFullYear()"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                                    <div v-if="form.errors.annee_reine" class="text-red-600 text-sm mt-1">
                                        {{ form.errors.annee_reine }}
                                    </div>
                                </div>

                                <!-- Couleur marquage reine -->
                                <div>
                                    <label for="couleur_marquage_reine" class="block text-sm font-medium text-gray-700">
                                        Couleur de marquage
                                    </label>
                                    <select id="couleur_marquage_reine" v-model="form.couleur_marquage_reine"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="">Pas de marquage</option>
                                        <option value="blanc">Blanc</option>
                                        <option value="jaune">Jaune</option>
                                        <option value="rouge">Rouge</option>
                                        <option value="vert">Vert</option>
                                        <option value="bleu">Bleu</option>
                                    </select>
                                    <div v-if="form.errors.couleur_marquage_reine" class="text-red-600 text-sm mt-1">
                                        {{ form.errors.couleur_marquage_reine }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Date d'installation -->
                        <div class="mb-4">
                            <label for="date_installation" class="block text-sm font-medium text-gray-700">
                                Date d'installation
                            </label>
                            <input id="date_installation" v-model="form.date_installation" type="date"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                            <div v-if="form.errors.date_installation" class="text-red-600 text-sm mt-1">
                                {{ form.errors.date_installation }}
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="mb-6">
                            <label for="notes" class="block text-sm font-medium text-gray-700">
                                Notes
                            </label>
                            <textarea id="notes" v-model="form.notes" rows="3"
                                placeholder="Observations particulières, origine de l'essaim..."
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                            <div v-if="form.errors.notes" class="text-red-600 text-sm mt-1">
                                {{ form.errors.notes }}
                            </div>
                        </div>

                        <!-- Boutons -->
                        <div class="flex items-center justify-end space-x-4">
                            <Link :href="route('ruchers.ruches.index', rucher.id)"
                                class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                            Annuler
                            </Link>
                            <button type="submit" :disabled="form.processing"
                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded disabled:opacity-50">
                                <span v-if="form.processing">Création...</span>
                                <span v-else>Créer la ruche</span>
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, useForm } from '@inertiajs/vue3';
import { Crown } from 'lucide-vue-next';

// Formulaire réactif
const form = useForm({
    nom: '',
    couleur: '#ffffff',
    type: 'dadant',
    statut: 'active',
    annee_reine: new Date().getFullYear(),
    couleur_marquage_reine: '',
    date_installation: '',
    notes: '',
});

// Soumission du formulaire
function submit() {
    form.post(route('ruchers.ruches.store', props.rucher.id));
}

// Récupérer props pour l'utiliser dans submit
const props = defineProps({
    rucher: Object
});
</script>