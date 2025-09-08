<template>

    <Head title="Inscription - BeeNote" />

    <div class="min-h-screen bg-gray-50 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="w-full max-w-md">
            <!-- Logo et titre -->
            <div class="text-center mb-8">
                <div class="flex items-center justify-center gap-3 mb-6">
                    <div class="w-8 h-8 bg-gray-900 flex items-center justify-center rounded-sm">
                        <Package class="h-4 w-4 text-white" />
                    </div>
                    <h1 class="text-xl font-medium text-gray-900">BeeNote</h1>
                </div>
                <h2 class="text-lg font-medium text-gray-900">Inscription</h2>
            </div>

            <!-- Formulaire -->
            <div class="bg-white border border-gray-200 rounded-sm p-6">
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Nom -->
                    <div>
                        <InputLabel for="name" value="Nom complet" class="text-sm font-medium text-gray-900 mb-2" />
                        <TextInput id="name" v-model="form.name" type="text"
                            class="w-full border border-gray-300 rounded-sm px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-gray-900 focus:border-gray-900"
                            placeholder="Votre nom et prénom" required autofocus autocomplete="name" />
                        <InputError class="mt-2" :message="form.errors.name" />
                    </div>

                    <!-- Email -->
                    <div>
                        <InputLabel for="email" value="Adresse email" class="text-sm font-medium text-gray-900 mb-2" />
                        <TextInput id="email" v-model="form.email" type="email"
                            class="w-full border border-gray-300 rounded-sm px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-gray-900 focus:border-gray-900"
                            placeholder="votre@email.com" required autocomplete="username" />
                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>

                    <!-- Mot de passe -->
                    <div>
                        <InputLabel for="password" value="Mot de passe"
                            class="text-sm font-medium text-gray-900 mb-2" />
                        <TextInput id="password" v-model="form.password" type="password"
                            class="w-full border border-gray-300 rounded-sm px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-gray-900 focus:border-gray-900"
                            placeholder="Votre mot de passe" required autocomplete="new-password" />
                        <InputError class="mt-2" :message="form.errors.password" />
                    </div>

                    <!-- Confirmation mot de passe -->
                    <div>
                        <InputLabel for="password_confirmation" value="Confirmer le mot de passe"
                            class="text-sm font-medium text-gray-900 mb-2" />
                        <TextInput id="password_confirmation" v-model="form.password_confirmation" type="password"
                            class="w-full border border-gray-300 rounded-sm px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-gray-900 focus:border-gray-900"
                            placeholder="Confirmez votre mot de passe" required autocomplete="new-password" />
                        <InputError class="mt-2" :message="form.errors.password_confirmation" />
                    </div>

                    <!-- Conditions d'utilisation -->
                    <div v-if="$page.props.jetstream.hasTermsAndPrivacyPolicyFeature" class="flex items-start">
                        <Checkbox v-model:checked="form.terms" name="terms"
                            class="h-4 w-4 text-gray-900 border-gray-300 focus:ring-gray-900 mt-0.5" required />
                        <div class="ml-3 text-sm text-gray-700">
                            J'accepte les
                            <Link target="_blank" :href="route('terms.show')"
                                class="text-gray-900 hover:text-gray-700 font-medium transition-colors">
                            conditions d'utilisation
                            </Link>
                            et la
                            <Link target="_blank" :href="route('policy.show')"
                                class="text-gray-900 hover:text-gray-700 font-medium transition-colors">
                            politique de confidentialité
                            </Link>
                        </div>
                        <InputError class="mt-2" :message="form.errors.terms" />
                    </div>

                    <!-- Bouton d'inscription -->
                    <div>
                        <PrimaryButton type="submit"
                            class="w-full bg-gray-900 hover:bg-gray-800 text-white text-sm font-medium py-3 px-4 rounded-sm transition-colors focus:outline-none focus:ring-2 focus:ring-gray-900 focus:ring-offset-2"
                            :class="{ 'opacity-50 cursor-not-allowed': form.processing }" :disabled="form.processing">
                            <span v-if="form.processing" class="flex items-center justify-center">
                                <div
                                    class="w-4 h-4 border border-white border-t-transparent rounded-full animate-spin mr-2">
                                </div>
                                Inscription...
                            </span>
                            <span v-else>S'inscrire</span>
                        </PrimaryButton>
                    </div>
                </form>

                <!-- Actions secondaires -->
                <div class="mt-6 pt-6 border-t border-gray-200 text-center">
                    <span class="text-sm text-gray-600">Déjà un compte ?</span>
                    <Link :href="route('login')"
                        class="text-sm text-gray-900 hover:text-gray-700 ml-1 font-medium transition-colors">
                    Se connecter
                    </Link>
                </div>
            </div>

            <!-- Retour à l'accueil -->
            <div class="mt-6 text-center">
                <Link href="/"
                    class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900 transition-colors">
                <ArrowLeft class="h-4 w-4 mr-2" />
                Retour à l'accueil
                </Link>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Package, ArrowLeft } from 'lucide-vue-next';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    terms: false,
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>