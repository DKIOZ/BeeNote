<template>
    <AppLayout :title="`Nouvelle ruche - ${rucher.nom}`">
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
                        <span class="text-gray-900 font-medium">Nouvelle ruche</span>
                    </div>
                </nav>

                <h2 class="text-lg sm:text-xl font-medium text-gray-900">
                    Ajouter une ruche à {{ rucher.nom }}
                </h2>
            </div>
        </template>

        <div class="py-6 sm:py-8 lg:py-12">
            <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white border border-gray-200 rounded-sm p-4 sm:p-6">

                    <form @submit.prevent="submit" class="space-y-6">

                        <!-- Nom de la ruche -->
                        <div>
                            <label for="nom" class="block text-sm font-medium text-gray-900 mb-2">
                                Nom de la ruche *
                            </label>
                            <input id="nom" v-model="form.nom" type="text" placeholder="Ex: R1, Ruche du Tilleul..."
                                class="w-full border border-gray-300 rounded-sm px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-gray-900 focus:border-gray-900"
                                required />
                            <div v-if="form.errors.nom" class="text-red-600 text-sm mt-1">
                                {{ form.errors.nom }}
                            </div>
                        </div>

                        <!-- Couleur de la ruche -->
                        <div>
                            <label for="couleur" class="block text-sm font-medium text-gray-900 mb-2">
                                Couleur de la ruche
                            </label>
                            <div class="flex gap-3">
                                <input id="couleur" v-model="form.couleur" type="color"
                                    class="w-12 h-10 border border-gray-300 rounded-sm cursor-pointer" />
                                <input v-model="form.couleur" type="text" placeholder="#ffffff ou nom de couleur"
                                    class="flex-1 border border-gray-300 rounded-sm px-3 py-2 text-sm font-mono focus:outline-none focus:ring-1 focus:ring-gray-900 focus:border-gray-900" />
                            </div>
                            <div v-if="form.errors.couleur" class="text-red-600 text-sm mt-1">
                                {{ form.errors.couleur }}
                            </div>
                        </div>

                        <!-- Type et Statut -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Type de ruche -->
                            <div>
                                <label for="type" class="block text-sm font-medium text-gray-900 mb-2">
                                    Type de ruche
                                </label>
                                <select id="type" v-model="form.type"
                                    class="w-full border border-gray-300 rounded-sm px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-gray-900 focus:border-gray-900">
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
                            <div>
                                <label for="statut" class="block text-sm font-medium text-gray-900 mb-2">
                                    Statut de la ruche
                                </label>
                                <select id="statut" v-model="form.statut"
                                    class="w-full border border-gray-300 rounded-sm px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-gray-900 focus:border-gray-900">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="morte">Morte</option>
                                </select>
                                <div v-if="form.errors.statut" class="text-red-600 text-sm mt-1">
                                    {{ form.errors.statut }}
                                </div>
                            </div>
                        </div>

                        <!-- Informations reine -->
                        <div class="border border-gray-200 rounded-sm p-4">
                            <h3 class="text-sm font-medium text-gray-900 mb-4 flex items-center">
                                <Crown class="w-4 h-4 mr-2" />
                                Informations sur la reine
                            </h3>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <!-- Année de la reine -->
                                <div>
                                    <label for="annee_reine" class="block text-sm font-medium text-gray-900 mb-2">
                                        Année de la reine
                                    </label>
                                    <input id="annee_reine" v-model="form.annee_reine" type="number"
                                        :min="new Date().getFullYear() - 5" :max="new Date().getFullYear()"
                                        class="w-full border border-gray-300 rounded-sm px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-gray-900 focus:border-gray-900" />
                                    <div v-if="form.errors.annee_reine" class="text-red-600 text-sm mt-1">
                                        {{ form.errors.annee_reine }}
                                    </div>
                                </div>

                                <!-- Couleur marquage reine -->
                                <div>
                                    <label for="couleur_marquage_reine"
                                        class="block text-sm font-medium text-gray-900 mb-2">
                                        Couleur de marquage
                                    </label>
                                    <select id="couleur_marquage_reine" v-model="form.couleur_marquage_reine"
                                        class="w-full border border-gray-300 rounded-sm px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-gray-900 focus:border-gray-900">
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
                        <div>
                            <label for="date_installation" class="block text-sm font-medium text-gray-900 mb-2">
                                Date d'installation
                            </label>
                            <input id="date_installation" v-model="form.date_installation" type="date"
                                class="w-full border border-gray-300 rounded-sm px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-gray-900 focus:border-gray-900" />
                            <div v-if="form.errors.date_installation" class="text-red-600 text-sm mt-1">
                                {{ form.errors.date_installation }}
                            </div>
                        </div>

                        <!-- Notes -->
                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-900 mb-2">
                                Notes
                            </label>
                            <textarea id="notes" v-model="form.notes" rows="4"
                                placeholder="Observations particulières, origine de l'essaim..."
                                class="w-full border border-gray-300 rounded-sm px-3 py-2 text-sm leading-relaxed resize-none focus:outline-none focus:ring-1 focus:ring-gray-900 focus:border-gray-900"></textarea>
                            <div v-if="form.errors.notes" class="text-red-600 text-sm mt-1">
                                {{ form.errors.notes }}
                            </div>
                        </div>

                        <!-- Boutons -->
                        <div class="flex flex-col gap-3 pt-4 sm:flex-row sm:justify-end">
                            <Link :href="route('ruchers.ruches.index', rucher.id)"
                                class="w-full bg-white hover:bg-gray-50 text-gray-700 border border-gray-300 text-sm font-medium py-3 px-4 rounded-sm text-center sm:w-auto">
                            Annuler
                            </Link>
                            <button type="submit" :disabled="form.processing"
                                class="w-full bg-gray-900 hover:bg-gray-800 text-white text-sm font-medium py-3 px-4 rounded-sm disabled:opacity-50 sm:w-auto">
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

// Props du contrôleur
const props = defineProps({
    rucher: Object
});

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
</script>