<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import AuthShell from '@/Components/AuthShell.vue';
import Field from '@/Components/Field.vue';

const form = useForm({ name: '', email: '', password: '', password_confirmation: '', terms: false });

const envoyer = () => {
    form.password_confirmation = form.password;
    form.post(route('register'), { onFinish: () => form.reset('password', 'password_confirmation') });
};
</script>

<template>
    <AuthShell titre="Créer un compte" tete="Créer mon compte">
        <form class="flex flex-col gap-3.5" novalidate @submit.prevent="envoyer">
            <Field id="name" v-model="form.name" label="Nom" autocomplete="name" :erreur="form.errors.name" required />
            <Field id="email" v-model="form.email" type="email" label="E-mail" autocomplete="username" :erreur="form.errors.email" required />
            <Field id="password" v-model="form.password" type="password" label="Mot de passe" autocomplete="new-password" :erreur="form.errors.password" required />

            <label class="flex min-h-[44px] cursor-pointer items-start gap-3 py-2">
                <input v-model="form.terms" type="checkbox" class="mt-0.5 h-7 w-7 flex-none rounded-lg border-line text-forest focus:ring-honey" required>
                <span>J'accepte les <Link :href="route('terms.show')" class="inline-block py-2 font-bold underline">conditions</Link> et la <Link :href="route('policy.show')" class="inline-block py-2 font-bold underline">politique de confidentialité</Link>.</span>
            </label>
            <p v-if="form.errors.terms" class="text-sm font-semibold text-rust">{{ form.errors.terms }}</p>

            <button type="submit" class="bn-btn mt-1 lg:w-full" :disabled="form.processing">Créer mon compte</button>
            <Link :href="route('login')" class="flex min-h-[48px] items-center justify-center font-semibold text-forest">J'ai déjà un compte</Link>
        </form>
    </AuthShell>
</template>
