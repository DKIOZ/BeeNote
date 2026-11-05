<script setup>
import { clearAll } from '@/terrain/store.js';
import { computed } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import AuthShell from '@/Components/AuthShell.vue';

const props = defineProps({ status: String });

const form = useForm({});
const envoye = computed(() => props.status === 'verification-link-sent');

function deconnecter() {
    router.post(route('logout'), {}, {
        onSuccess: () => {
            router.clearHistory();
            clearAll().catch(() => {});
        },
    });
}
</script>

<template>
    <AuthShell titre="Vérifiez votre e-mail">
        <p class="text-lg text-muted">Touchez le lien que nous venons de vous envoyer. Regardez aussi vos indésirables.</p>
        <p v-if="envoye" role="status" class="mt-3 rounded-2xl bg-mint p-3 font-semibold text-mint-ink">Un nouveau lien vient de partir.</p>
        <form class="mt-4" @submit.prevent="form.post(route('verification.send'))">
            <button type="submit" class="bn-btn lg:w-full" :disabled="form.processing">Renvoyer le lien</button>
        </form>
        <button type="button" class="mt-2 flex min-h-[48px] w-full items-center justify-center font-semibold text-forest" @click="deconnecter">Se déconnecter</button>
    </AuthShell>
</template>
