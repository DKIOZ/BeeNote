<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import AuthShell from '@/Components/AuthShell.vue';
import Field from '@/Components/Field.vue';

defineProps({ canResetPassword: Boolean, status: String });

const form = useForm({ email: '', password: '', remember: true });

const envoyer = () => form.post(route('login'), { onFinish: () => form.reset('password') });
</script>

<template>
    <AuthShell titre="Connexion">
        <p v-if="status" role="status" class="mb-3 rounded-2xl bg-mint p-3 font-semibold text-mint-ink">{{ status }}</p>

        <form class="flex flex-col gap-3.5" @submit.prevent="envoyer">
            <Field id="email" v-model="form.email" type="email" label="E-mail" autocomplete="username" :erreur="form.errors.email" required />
            <Field id="password" v-model="form.password" type="password" label="Mot de passe" autocomplete="current-password" :erreur="form.errors.password" required />
            <button type="submit" class="bn-btn mt-1 lg:w-full" :disabled="form.processing">Se connecter</button>
            <Link :href="route('register')" class="flex min-h-[48px] items-center justify-center font-bold text-forest">Créer un compte</Link>
            <Link v-if="canResetPassword" :href="route('password.request')" class="flex min-h-[44px] items-center justify-center text-muted">Mot de passe oublié</Link>
        </form>
    </AuthShell>
</template>
