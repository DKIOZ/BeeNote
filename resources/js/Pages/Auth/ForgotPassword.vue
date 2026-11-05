<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import AuthShell from '@/Components/AuthShell.vue';
import Field from '@/Components/Field.vue';

defineProps({ status: String });

const form = useForm({ email: '' });
</script>

<template>
    <AuthShell titre="Mot de passe oublié">
        <div v-if="status" role="status" class="rounded-card bg-mint p-4">
            <p class="text-lg font-bold text-mint-ink">C'est envoyé</p>
            <p class="mb-3 mt-1">{{ status }} Regardez aussi vos indésirables.</p>
            <Link :href="route('login')" class="bn-btn-2">Retour à la connexion</Link>
        </div>

        <form v-else class="flex flex-col gap-3.5" @submit.prevent="form.post(route('password.email'))">
            <p class="text-lg text-muted">Indiquez votre e-mail : nous vous enverrons un lien pour en choisir un nouveau.</p>
            <Field id="email" v-model="form.email" type="email" label="E-mail" autocomplete="username" :erreur="form.errors.email" required />
            <button type="submit" class="bn-btn lg:w-full" :disabled="form.processing">Recevoir le lien</button>
            <Link :href="route('login')" class="flex min-h-[48px] items-center justify-center font-semibold text-forest">Retour à la connexion</Link>
        </form>
    </AuthShell>
</template>
