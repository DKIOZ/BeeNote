<script setup>
import { useForm } from '@inertiajs/vue3';
import AuthShell from '@/Components/AuthShell.vue';
import Field from '@/Components/Field.vue';

const form = useForm({ password: '' });

const envoyer = () => form.post(route('password.confirm'), { onFinish: () => form.reset() });
</script>

<template>
    <AuthShell titre="Confirmez votre mot de passe">
        <form class="flex flex-col gap-3.5" @submit.prevent="envoyer">
            <Field id="password" v-model="form.password" type="password" label="Mot de passe" autocomplete="current-password" :erreur="form.errors.password" required />
            <button type="submit" class="bn-btn lg:w-full" :disabled="form.processing">Confirmer</button>
        </form>
    </AuthShell>
</template>
