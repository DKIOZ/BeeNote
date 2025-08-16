<template>
    <AppLayout title="Mes Ruchers">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Mes Ruchers
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">

                    <!-- Debug : Affichage des données -->
                    <div class="mb-4">
                        <p>Nombre de ruchers : {{ ruchers.length }}</p>
                        <pre>{{ ruchers }}</pre>
                    </div>

                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';

// Récupérer les props envoyées par le contrôleur
defineProps({
    ruchers: Array
});
</script>