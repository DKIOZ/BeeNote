<script setup>
import { useForm } from '@inertiajs/vue3';
import AuthShell from '@/Components/AuthShell.vue';
import Field from '@/Components/Field.vue';

const props = defineProps({ email: String, token: String });

const form = useForm({ token: props.token, email: props.email, password: '', password_confirmation: '' });

const envoyer = () => form.post(route('password.update'), { onFinish: () => form.reset('password', 'password_confirmation') });
</script>

<template>
    <AuthShell titre="Nouveau mot de passe">
        <form class="flex flex-col gap-3.5" @submit.prevent="envoyer">
            <Field id="email" v-model="form.email" type="email" label="E-mail" autocomplete="username" :erreur="form.errors.email" required />
            <Field id="password" v-model="form.password" type="password" label="Nouveau mot de passe" autocomplete="new-password" :erreur="form.errors.password" required />
            <Field id="confirmation" v-model="form.password_confirmation" type="password" label="Répéter le mot de passe" autocomplete="new-password" :erreur="form.errors.password_confirmation" required />
            <button type="submit" class="bn-btn lg:w-full" :disabled="form.processing">Enregistrer</button>
        </form>
    </AuthShell>
</template>
