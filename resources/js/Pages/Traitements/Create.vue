<template>
    <AppLayout :title="`Nouveau traitement - ${ruche.nom} - ${rucher.nom}`">
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
                        <span class="text-gray-900 font-medium">Nouveau</span>
                    </div>
                </nav>

                <!-- Titre avec info ruche -->
                <div class="flex items-center gap-3">
                    <!-- Couleur de la ruche -->
                    <div v-if="ruche.couleur" :style="{ backgroundColor: ruche.couleur }"
                        class="w-4 h-4 rounded-full border border-gray-300 flex-shrink-0">
                    </div>

                    <div class="min-w-0 flex-1">
                        <h2 class="text-lg sm:text-xl font-medium text-gray-900">
                            Nouveau traitement - {{ ruche.nom }}
                        </h2>
                        <p class="text-sm text-gray-600">
                            Enregistrer un nouveau traitement appliqué à cette ruche
                        </p>
                    </div>
                </div>
            </div>
        </template>

        <div class="py-6 sm:py-8 lg:py-12">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white border border-gray-200 rounded-sm p-6 sm:p-8">

                    <form @submit.prevent="submit" class="space-y-6">

                        <!-- Date et type -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Date du traitement -->
                            <div>
                                <label for="date_traitement" class="block text-sm font-medium text-gray-900 mb-2">
                                    Date du traitement <span class="text-red-500">*</span>
                                </label>
                                <input id="date_traitement" v-model="form.date_traitement" type="date"
                                    class="w-full border border-gray-300 rounded-sm px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-gray-900 focus:border-gray-900"
                                    required />
                                <div v-if="form.errors.date_traitement" class="text-red-600 text-sm mt-1">
                                    {{ form.errors.date_traitement }}
                                </div>
                            </div>

                            <!-- Type de traitement -->
                            <div>
                                <label for="type_traitement" class="block text-sm font-medium text-gray-900 mb-2">
                                    Type de traitement <span class="text-red-500">*</span>
                                </label>
                                <select id="type_traitement" v-model="form.type_traitement">
                                    <option value="">Sélectionner un type</option>
                                    <option value="varroa">Varroa</option>
                                    <option value="nosema">Nosémose</option>
                                    <option value="loque">Loque</option>
                                    <option value="preventif">Préventif</option>
                                    <option value="frelon_asiatique">Frelon asiatique</option>
                                    <option value="autre">Autre</option>
                                </select>
                                <div v-if="form.errors.type_traitement" class="text-red-600 text-sm mt-1">
                                    {{ form.errors.type_traitement }}
                                </div>
                            </div>
                        </div>

                        <!-- Produit et dosage -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Produit utilisé -->
                            <div>
                                <label for="produit_utilise" class="block text-sm font-medium text-gray-900 mb-2">
                                    Produit utilisé <span class="text-red-500">*</span>
                                </label>
                                <input id="produit_utilise" v-model="form.produit_utilise" type="text"
                                    class="w-full border border-gray-300 rounded-sm px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-gray-900 focus:border-gray-900"
                                    placeholder="Ex: Apiguard, Apivar, sirop de nourrissement..." required />
                                <div v-if="form.errors.produit_utilise" class="text-red-600 text-sm mt-1">
                                    {{ form.errors.produit_utilise }}
                                </div>
                            </div>

                            <!-- Dosage -->
                            <div>
                                <label for="dosage" class="block text-sm font-medium text-gray-900 mb-2">
                                    Dosage <span class="text-red-500">*</span>
                                </label>
                                <input id="dosage" v-model="form.dosage" type="text"
                                    class="w-full border border-gray-300 rounded-sm px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-gray-900 focus:border-gray-900"
                                    placeholder="Ex: 1 lanière par 5 cadres, 2L de sirop..." required />
                                <div v-if="form.errors.dosage" class="text-red-600 text-sm mt-1">
                                    {{ form.errors.dosage }}
                                </div>
                            </div>
                        </div>

                        <!-- Méthode et température -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Méthode d'application -->
                            <div>
                                <label for="methode_application" class="block text-sm font-medium text-gray-900 mb-2">
                                    Méthode d'application <span class="text-red-500">*</span>
                                </label>
                                <select id="methode_application" v-model="form.methode_application"
                                    class="w-full border border-gray-300 rounded-sm px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-gray-900 focus:border-gray-900"
                                    required>
                                    <option value="">Sélectionner une méthode</option>
                                    <option value="vaporisation">Vaporisation</option>
                                    <option value="goutte_a_goutte">Goutte à goutte</option>
                                    <option value="bandes">Bandes</option>
                                    <option value="poudrage">Poudrage</option>
                                    <option value="autre">Autre</option>
                                </select>
                                <div v-if="form.errors.methode_application" class="text-red-600 text-sm mt-1">
                                    {{ form.errors.methode_application }}
                                </div>
                            </div>

                            <!-- Température d'application -->
                            <div>
                                <label for="temperature_application"
                                    class="block text-sm font-medium text-gray-900 mb-2">
                                    Température d'application (°C)
                                </label>
                                <input id="temperature_application" v-model="form.temperature_application" type="number"
                                    step="0.1" min="-20" max="50"
                                    class="w-full border border-gray-300 rounded-sm px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-gray-900 focus:border-gray-900"
                                    placeholder="Ex: 18.5" />
                                <div v-if="form.errors.temperature_application" class="text-red-600 text-sm mt-1">
                                    {{ form.errors.temperature_application }}
                                </div>
                            </div>
                        </div>

                        <!-- Efficacité -->
                        <div>
                            <label for="efficacite" class="block text-sm font-medium text-gray-900 mb-2">
                                Efficacité (évaluation à posteriori)
                            </label>
                            <select id="efficacite" v-model="form.efficacite"
                                class="w-full sm:w-1/2 border border-gray-300 rounded-sm px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-gray-900 focus:border-gray-900">
                                <option value="non_evaluee">Non évaluée</option>
                                <option value="excellente">Excellente</option>
                                <option value="bonne">Bonne</option>
                                <option value="moyenne">Moyenne</option>
                                <option value="faible">Faible</option>
                            </select>
                            <div v-if="form.errors.efficacite" class="text-red-600 text-sm mt-1">
                                {{ form.errors.efficacite }}
                            </div>
                        </div>

                        <!-- Effets secondaires -->
                        <div>
                            <label for="effets_secondaires" class="block text-sm font-medium text-gray-900 mb-2">
                                Effets secondaires observés
                            </label>
                            <textarea id="effets_secondaires" v-model="form.effets_secondaires" rows="3"
                                class="w-full border border-gray-300 rounded-sm px-3 py-2 text-sm leading-relaxed resize-none focus:outline-none focus:ring-1 focus:ring-gray-900 focus:border-gray-900"
                                placeholder="Décrivez tout effet secondaire observé sur la colonie..."></textarea>
                            <div v-if="form.errors.effets_secondaires" class="text-red-600 text-sm mt-1">
                                {{ form.errors.effets_secondaires }}
                            </div>
                        </div>

                        <!-- Notes -->
                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-900 mb-2">
                                Notes complémentaires
                            </label>
                            <textarea id="notes" v-model="form.notes" rows="4"
                                class="w-full border border-gray-300 rounded-sm px-3 py-2 text-sm leading-relaxed resize-none focus:outline-none focus:ring-1 focus:ring-gray-900 focus:border-gray-900"
                                placeholder="Contexte, observations particulières, rappels pour le prochain traitement..."></textarea>
                            <div v-if="form.errors.notes" class="text-red-600 text-sm mt-1">
                                {{ form.errors.notes }}
                            </div>
                        </div>

                        <!-- Boutons -->
                        <div class="flex flex-col gap-3 pt-4 sm:flex-row sm:justify-end">
                            <Link :href="route('ruchers.ruches.traitements.index', [rucher.id, ruche.id])"
                                class="w-full bg-white hover:bg-gray-50 text-gray-700 border border-gray-300 text-sm font-medium py-3 px-4 rounded-sm text-center sm:w-auto">
                            Annuler
                            </Link>
                            <button type="submit" :disabled="form.processing"
                                class="w-full bg-gray-900 hover:bg-gray-800 text-white text-sm font-medium py-3 px-4 rounded-sm disabled:opacity-50 sm:w-auto">
                                <span v-if="form.processing">Enregistrement...</span>
                                <span v-else>Enregistrer le traitement</span>
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

// Props du contrôleur
const props = defineProps({
    rucher: Object,
    ruche: Object,
    formData: Object // Données pré-remplies du service
});

// Formulaire avec pré-remplissage intelligent
const form = useForm({
    date_traitement: props.formData?.date_traitement || new Date().toISOString().split('T')[0],
    type_traitement: props.formData?.type_traitement || '',
    produit_utilise: props.formData?.produit_utilise || '',
    dosage: props.formData?.dosage || '',
    methode_application: props.formData?.methode_application || '',
    temperature_application: props.formData?.temperature_application || '',
    efficacite: props.formData?.efficacite || 'non_evaluee',
    effets_secondaires: props.formData?.effets_secondaires || '',
    notes: props.formData?.notes || '',
});

// Soumission du formulaire
function submit() {
    form.post(route('ruchers.ruches.traitements.store', [props.rucher.id, props.ruche.id]));
}
</script>