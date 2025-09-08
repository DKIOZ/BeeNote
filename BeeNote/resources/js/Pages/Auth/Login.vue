<template>
    <Head title="Connexion - BeeNote" />

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
                <h2 class="text-lg font-medium text-gray-900">Connexion</h2>
            </div>

            <!-- Formulaire -->
            <div class="bg-white border border-gray-200 rounded-sm p-6">
                <!-- Message de statut -->
                <div v-if="status" class="mb-6 p-4 bg-gray-50 border border-gray-200 rounded-sm">
                    <p class="text-sm text-gray-700">{{ status }}</p>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Email -->
                    <div>
                        <InputLabel 
                            for="email" 
                            value="Adresse email" 
                            class="text-sm font-medium text-gray-900 mb-2" 
                        />
                        <TextInput
                            id="email"
                            v-model="form.email"
                            type="email"
                            class="w-full border border-gray-300 rounded-sm px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-gray-900 focus:border-gray-900"
                            placeholder="votre@email.com"
                            required
                            autofocus
                            autocomplete="username"
                        />
                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>

                    <!-- Mot de passe -->
                    <div>
                        <InputLabel 
                            for="password" 
                            value="Mot de passe" 
                            class="text-sm font-medium text-gray-900 mb-2" 
                        />
                        <TextInput
                            id="password"
                            v-model="form.password"
                            type="password"
                            class="w-full border border-gray-300 rounded-sm px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-gray-900 focus:border-gray-900"
                            placeholder="Votre mot de passe"
                            required
                            autocomplete="current-password"
                        />
                        <InputError class="mt-2" :message="form.errors.password" />
                    </div>

                    <!-- Se souvenir de moi -->
                    <div class="flex items-center">
                        <Checkbox 
                            v-model:checked="form.remember" 
                            name="remember" 
                            class="h-4 w-4 text-gray-900 border-gray-300 focus:ring-gray-900"
                        />
                        <span class="ml-3 text-sm text-gray-700">Se souvenir de moi</span>
                    </div>

                    <!-- Bouton de connexion -->
                    <div>
                        <PrimaryButton 
                            type="submit"
                            class="w-full bg-gray-900 hover:bg-gray-800 text-white text-sm font-medium py-3 px-4 rounded-sm transition-colors focus:outline-none focus:ring-2 focus:ring-gray-900 focus:ring-offset-2"
                            :class="{ 'opacity-50 cursor-not-allowed': form.processing }" 
                            :disabled="form.processing"
                        >
                            <span v-if="form.processing" class="flex items-center justify-center">
                                <div class="w-4 h-4 border border-white border-t-transparent rounded-full animate-spin mr-2"></div>
                                Connexion...
                            </span>
                            <span v-else>Se connecter</span>
                        </PrimaryButton>
                    </div>
                </form>

                <!-- Actions secondaires -->
                <div class="mt-6 pt-6 border-t border-gray-200 space-y-3 text-center">
                    <div v-if="canResetPassword">
                        <Link 
                            :href="route('password.request')" 
                            class="text-sm text-gray-600 hover:text-gray-900 transition-colors"
                        >
                            Mot de passe oublié ?
                        </Link>
                    </div>
                    
                    <div>
                        <span class="text-sm text-gray-600">Pas encore de compte ?</span>
                        <Link 
                            :href="route('register')" 
                            class="text-sm text-gray-900 hover:text-gray-700 ml-1 font-medium transition-colors"
                        >
                            Créer un compte
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Retour à l'accueil -->
            <div class="mt-6 text-center">
                <Link 
                    href="/" 
                    class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900 transition-colors"
                >
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

defineProps({
    canResetPassword: Boolean,
    status: String,
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.transform(data => ({
        ...data,
        remember: form.remember ? 'on' : '',
    })).post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>