<template>
    <AppLayout title="Nouveau Rucher">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Créer un nouveau rucher
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">

                    <form @submit.prevent="submit">
                        <!-- Nom du rucher -->
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">
                                Nom du rucher
                            </label>
                            <input v-model="form.nom" type="text"
                                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500"
                                placeholder="Ex: Prairie du Sud" required>
                        </div>

                        <!-- Localisation -->
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">
                                Localisation
                            </label>
                            <input v-model="form.localisation" type="text"
                                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500"
                                placeholder="Ex: Maine-et-Loire, France">
                        </div>

                        <!-- Description -->
                        <div class="mb-6">
                            <label class="block text-gray-700 text-sm font-bold mb-2">
                                Description
                            </label>
                            <textarea v-model="form.description"
                                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500"
                                rows="3" placeholder="Notes sur le rucher..."></textarea>
                        </div>

                        <!-- Boutons -->
                        <div class="flex items-center justify-between">
                            <button type="submit"
                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none">
                                Créer le rucher
                            </button>

                            <a :href="route('ruchers.index')" class="text-gray-600 hover:text-gray-800">
                                Annuler
                            </a>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { reactive } from 'vue';
import { router } from '@inertiajs/vue3';

// Formulaire réactif
const form = reactive({
    nom: '',
    localisation: '',
    description: ''
});

// Soumission du formulaire
const submit = () => {
    router.post(route('ruchers.store'), form);
};
</script>