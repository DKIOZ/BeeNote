<template>
    <AppLayout title="Nouvelle Récolte">
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
                        <span class="text-gray-900 font-medium">Nouvelle</span>
                    </div>
                </nav>

                <h2 class="text-lg sm:text-xl font-medium text-gray-900">
                    Nouvelle récolte - {{ ruche.nom }}
                </h2>
            </div>
        </template>

        <div class="py-6 sm:py-8 lg:py-12">
            <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white border border-gray-200 rounded-sm p-4 sm:p-6">

                    <form @submit.prevent="submit" class="space-y-6">

                        <!-- Date de récolte -->
                        <div>
                            <label for="date_recolte" class="block text-sm font-medium text-gray-900 mb-2">
                                Date de récolte *
                            </label>
                            <input id="date_recolte" v-model="form.date_recolte" type="date"
                                class="w-full border border-gray-300 rounded-sm px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-gray-900 focus:border-gray-900"
                                required />
                            <div v-if="form.errors.date_recolte" class="text-red-600 text-sm mt-1">
                                {{ form.errors.date_recolte }}
                            </div>
                        </div>

                        <!-- Type de miel -->
                        <div>
                            <label for="type_miel" class="block text-sm font-medium text-gray-900 mb-2">
                                Type de miel *
                            </label>
                            <select id="type_miel" v-model="form.type_miel" required
                                class="w-full border border-gray-300 rounded-sm px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-gray-900 focus:border-gray-900">
                                <option value="">-- Sélectionnez --</option>
                                <option value="acacia">Acacia</option>
                                <option value="tilleul">Tilleul</option>
                                <option value="chataignier">Châtaignier</option>
                                <option value="toutes_fleurs">Toutes fleurs</option>
                                <option value="lavande">Lavande</option>
                                <option value="autre">Autre</option>
                            </select>
                            <div v-if="form.errors.type_miel" class="text-red-600 text-sm mt-1">
                                {{ form.errors.type_miel }}
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <!-- Quantité récoltée -->
                            <div>
                                <label for="quantite_kg" class="block text-sm font-medium text-gray-900 mb-2">
                                    Quantité récoltée (kg) *
                                </label>
                                <input id="quantite_kg" v-model="form.quantite_kg" type="number" 
                                    step="0.01" min="0" max="9999.99" placeholder="0.00"
                                    class="w-full border border-gray-300 rounded-sm px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-gray-900 focus:border-gray-900"
                                    required />
                                <div v-if="form.errors.quantite_kg" class="text-red-600 text-sm mt-1">
                                    {{ form.errors.quantite_kg }}
                                </div>
                            </div>

                            <!-- Nombre de cadres -->
                            <div>
                                <label for="nombre_cadres" class="block text-sm font-medium text-gray-900 mb-2">
                                    Nombre de cadres *
                                </label>
                                <input id="nombre_cadres" v-model="form.nombre_cadres" type="number" 
                                    min="1" max="50" placeholder="0"
                                    class="w-full border border-gray-300 rounded-sm px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-gray-900 focus:border-gray-900"
                                    required />
                                <div v-if="form.errors.nombre_cadres" class="text-red-600 text-sm mt-1">
                                    {{ form.errors.nombre_cadres }}
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <!-- Qualité -->
                            <div>
                                <label for="qualite" class="block text-sm font-medium text-gray-900 mb-2">
                                    Qualité du miel *
                                </label>
                                <select id="qualite" v-model="form.qualite" required
                                    class="w-full border border-gray-300 rounded-sm px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-gray-900 focus:border-gray-900">
                                    <option value="">-- Sélectionnez --</option>
                                    <option value="excellente">Excellente</option>
                                    <option value="bonne">Bonne</option>
                                    <option value="moyenne">Moyenne</option>
                                    <option value="mediocre">Médiocre</option>
                                </select>
                                <div v-if="form.errors.qualite" class="text-red-600 text-sm mt-1">
                                    {{ form.errors.qualite }}
                                </div>
                            </div>

                            <!-- Humidité du miel -->
                            <div>
                                <label for="humidite_miel" class="block text-sm font-medium text-gray-900 mb-2">
                                    Humidité (%)
                                </label>
                                <input id="humidite_miel" v-model="form.humidite_miel" type="number" 
                                    step="0.1" min="0" max="999.9" placeholder="18.5"
                                    class="w-full border border-gray-300 rounded-sm px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-gray-900 focus:border-gray-900" />
                                <div v-if="form.errors.humidite_miel" class="text-red-600 text-sm mt-1">
                                    {{ form.errors.humidite_miel }}
                                </div>
                            </div>
                        </div>

                        <!-- Notes qualité -->
                        <div>
                            <label for="notes_qualite" class="block text-sm font-medium text-gray-900 mb-2">
                                Notes sur la qualité
                            </label>
                            <textarea id="notes_qualite" v-model="form.notes_qualite" rows="3"
                                placeholder="Observations sur la couleur, texture, cristallisation..."
                                class="w-full border border-gray-300 rounded-sm px-3 py-2 text-sm leading-relaxed resize-none focus:outline-none focus:ring-1 focus:ring-gray-900 focus:border-gray-900"></textarea>
                            <div v-if="form.errors.notes_qualite" class="text-red-600 text-sm mt-1">
                                {{ form.errors.notes_qualite }}
                            </div>
                        </div>

                        <!-- Notes dégustation -->
                        <div>
                            <label for="notes_degustation" class="block text-sm font-medium text-gray-900 mb-2">
                                Notes de dégustation
                            </label>
                            <textarea id="notes_degustation" v-model="form.notes_degustation" rows="3"
                                placeholder="Goût, arôme, particularités gustatives..."
                                class="w-full border border-gray-300 rounded-sm px-3 py-2 text-sm leading-relaxed resize-none focus:outline-none focus:ring-1 focus:ring-gray-900 focus:border-gray-900"></textarea>
                            <div v-if="form.errors.notes_degustation" class="text-red-600 text-sm mt-1">
                                {{ form.errors.notes_degustation }}
                            </div>
                        </div>

                        <!-- Boutons -->
                        <div class="flex flex-col gap-3 pt-4 sm:flex-row sm:justify-end">
                            <Link :href="route('ruchers.ruches.recoltes.index', [rucher.id, ruche.id])"
                                class="w-full bg-white hover:bg-gray-50 text-gray-700 border border-gray-300 text-sm font-medium py-3 px-4 rounded-sm text-center sm:w-auto">
                                Annuler
                            </Link>
                            <button type="submit" :disabled="form.processing"
                                class="w-full bg-gray-900 hover:bg-gray-800 text-white text-sm font-medium py-3 px-4 rounded-sm disabled:opacity-50 sm:w-auto">
                                <span v-if="form.processing">Enregistrement...</span>
                                <span v-else>Enregistrer la récolte</span>
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
    formData: Object
});

// Formulaire pré-rempli avec formData du service
const form = useForm({
    date_recolte: props.formData.date_recolte,
    type_miel: props.formData.type_miel,
    quantite_kg: props.formData.quantite_kg,
    nombre_cadres: props.formData.nombre_cadres,
    qualite: props.formData.qualite,
    humidite_miel: props.formData.humidite_miel,
    notes_qualite: props.formData.notes_qualite,
    notes_degustation: props.formData.notes_degustation,
});

// Soumission du formulaire
function submit() {
    form.post(route('ruchers.ruches.recoltes.store', [props.rucher.id, props.ruche.id]));
}
</script>