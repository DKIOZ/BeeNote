<script setup>
import { Head } from '@inertiajs/vue3';

defineProps({ terms: String });

const retour = () => window.history.back();
</script>

<template>
    <Head title="Conditions" />
    <div class="min-h-screen bg-ground px-4 py-6">
        <div class="mx-auto max-w-2xl">
            <button type="button" class="mb-3 min-h-[44px] font-semibold text-forest" @click="retour">← Retour</button>
            <!-- Texte venant uniquement de resources/markdown, jamais d'un utilisateur. -->
            <article class="bn-card prose max-w-none p-5" v-html="terms" />
        </div>
    </div>
</template>
