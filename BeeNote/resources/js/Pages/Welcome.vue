<template>
    <Head title="BeeNote - Gestion d'Apiculture" />
    
    <div class="bg-gray-50 min-h-screen">
        <!-- Header -->
        <header class="bg-white border-b border-gray-200">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <!-- Logo -->
                    <div class="flex items-center">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 bg-gray-900 rounded-sm flex items-center justify-center">
                                <Package class="h-4 w-4 text-white" />
                            </div>
                            <h1 class="text-xl font-bold text-gray-900">BeeNote</h1>
                        </div>
                    </div>

                    <!-- Navigation -->
                    <nav v-if="canLogin" class="flex items-center space-x-4">
                        <Link v-if="$page.props.auth.user" :href="route('dashboard')"
                            class="bg-gray-900 hover:bg-gray-800 text-white text-sm font-medium py-2 px-4 rounded-sm transition-colors">
                            Tableau de bord
                        </Link>
                        <template v-else>
                            <Link :href="route('login')"
                                class="text-gray-700 hover:text-gray-900 text-sm font-medium py-2 px-4 rounded-sm transition-colors">
                                Connexion
                            </Link>
                            <Link v-if="canRegister" :href="route('register')"
                                class="bg-gray-900 hover:bg-gray-800 text-white text-sm font-medium py-2 px-4 rounded-sm transition-colors">
                                Créer un compte
                            </Link>
                        </template>
                    </nav>
                </div>
            </div>
        </header>

        <!-- Hero Section -->
        <section class="py-16 sm:py-24">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <div class="max-w-3xl mx-auto">
                    <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-gray-900 mb-6">
                        Gérez vos ruchers avec simplicité
                    </h2>
                    <p class="text-lg sm:text-xl text-gray-600 mb-8 leading-relaxed">
                        BeeNote vous aide à suivre et organiser vos ruchers, ruches et colonies d'abeilles 
                        avec un système simple et efficace conçu par des apiculteurs.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <Link v-if="canRegister" :href="route('register')"
                            class="bg-gray-900 hover:bg-gray-800 text-white text-lg font-medium py-3 px-8 rounded-sm transition-colors">
                            Commencer gratuitement
                        </Link>
                        <button class="bg-white hover:bg-gray-50 text-gray-900 border border-gray-300 text-lg font-medium py-3 px-8 rounded-sm transition-colors">
                            Voir la démo
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features -->
        <section class="py-16 bg-white">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <h3 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-4">
                        Tout ce dont vous avez besoin
                    </h3>
                    <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                        Une solution complète pour gérer votre activité apicole, 
                        du rucher individuel à l'exploitation professionnelle.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <!-- Gestion des ruchers -->
                    <div class="bg-gray-50 border border-gray-200 rounded-sm p-6">
                        <div class="flex items-center gap-3 mb-4">
                            <Building2 class="h-6 w-6 text-gray-700" />
                            <h4 class="text-lg font-semibold text-gray-900">Gestion des ruchers</h4>
                        </div>
                        <p class="text-gray-600 mb-4">
                            Organisez vos ruchers par localisation avec géolocalisation, 
                            notes et informations détaillées.
                        </p>
                        <ul class="text-sm text-gray-700 space-y-2">
                            <li class="flex items-center gap-2">
                                <CheckCircle class="h-4 w-4 text-gray-500" />
                                Localisation GPS
                            </li>
                            <li class="flex items-center gap-2">
                                <CheckCircle class="h-4 w-4 text-gray-500" />
                                Notes et descriptions
                            </li>
                            <li class="flex items-center gap-2">
                                <CheckCircle class="h-4 w-4 text-gray-500" />
                                Vue d'ensemble
                            </li>
                        </ul>
                    </div>

                    <!-- Suivi des ruches -->
                    <div class="bg-gray-50 border border-gray-200 rounded-sm p-6">
                        <div class="flex items-center gap-3 mb-4">
                            <Package class="h-6 w-6 text-gray-700" />
                            <h4 class="text-lg font-semibold text-gray-900">Suivi des ruches</h4>
                        </div>
                        <p class="text-gray-600 mb-4">
                            Suivez chaque ruche individuellement avec son statut, 
                            sa reine et ses caractéristiques.
                        </p>
                        <ul class="text-sm text-gray-700 space-y-2">
                            <li class="flex items-center gap-2">
                                <CheckCircle class="h-4 w-4 text-gray-500" />
                                Identification couleur
                            </li>
                            <li class="flex items-center gap-2">
                                <CheckCircle class="h-4 w-4 text-gray-500" />
                                Suivi des reines
                            </li>
                            <li class="flex items-center gap-2">
                                <CheckCircle class="h-4 w-4 text-gray-500" />
                                Statut de santé
                            </li>
                        </ul>
                    </div>

                    <!-- Tableaux de bord -->
                    <div class="bg-gray-50 border border-gray-200 rounded-sm p-6">
                        <div class="flex items-center gap-3 mb-4">
                            <BarChart3 class="h-6 w-6 text-gray-700" />
                            <h4 class="text-lg font-semibold text-gray-900">Tableaux de bord</h4>
                        </div>
                        <p class="text-gray-600 mb-4">
                            Visualisez vos données avec des statistiques claires 
                            et des indicateurs de performance.
                        </p>
                        <ul class="text-sm text-gray-700 space-y-2">
                            <li class="flex items-center gap-2">
                                <CheckCircle class="h-4 w-4 text-gray-500" />
                                Statistiques globales
                            </li>
                            <li class="flex items-center gap-2">
                                <CheckCircle class="h-4 w-4 text-gray-500" />
                                Ruches à surveiller
                            </li>
                            <li class="flex items-center gap-2">
                                <CheckCircle class="h-4 w-4 text-gray-500" />
                                Actions rapides
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <!-- Stats Section -->
        <section class="py-16 bg-gray-900 text-white">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <h3 class="text-2xl sm:text-3xl font-bold mb-4">
                        Simple, efficace, professionnel
                    </h3>
                    <p class="text-lg text-gray-300 max-w-2xl mx-auto">
                        Conçu pour les apiculteurs qui veulent se concentrer sur leurs abeilles, 
                        pas sur la paperasse.
                    </p>
                </div>

                <div class="grid grid-cols-2 lg:grid-cols-4 gap-8 text-center">
                    <div>
                        <div class="text-3xl sm:text-4xl font-bold mb-2">100%</div>
                        <div class="text-gray-300">Gratuit</div>
                    </div>
                    <div>
                        <div class="text-3xl sm:text-4xl font-bold mb-2">0</div>
                        <div class="text-gray-300">Installation</div>
                    </div>
                    <div>
                        <div class="text-3xl sm:text-4xl font-bold mb-2">24/7</div>
                        <div class="text-gray-300">Disponible</div>
                    </div>
                    <div>
                        <div class="text-3xl sm:text-4xl font-bold mb-2">∞</div>
                        <div class="text-gray-300">Ruches</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="py-16 bg-white">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h3 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-6">
                    Prêt à organiser vos ruchers ?
                </h3>
                <p class="text-lg text-gray-600 mb-8 max-w-2xl mx-auto">
                    Rejoignez les apiculteurs qui utilisent déjà BeeNote pour gérer 
                    leurs ruchers plus efficacement.
                </p>
                <Link v-if="canRegister" :href="route('register')"
                    class="inline-flex items-center bg-gray-900 hover:bg-gray-800 text-white text-lg font-medium py-3 px-8 rounded-sm transition-colors">
                    <Plus class="mr-2 h-5 w-5" />
                    Créer mon compte gratuitement
                </Link>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-gray-50 border-t border-gray-200 py-8">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col sm:flex-row justify-between items-center">
                    <div class="flex items-center gap-2 mb-4 sm:mb-0">
                        <div class="w-6 h-6 bg-gray-900 rounded-sm flex items-center justify-center">
                            <Package class="h-3 w-3 text-white" />
                        </div>
                        <span class="font-semibold text-gray-900">BeeNote</span>
                    </div>
                    <div class="text-sm text-gray-600">
                        Laravel v{{ laravelVersion }} • PHP v{{ phpVersion }}
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { Building2, Package, CheckCircle, BarChart3, Plus } from 'lucide-vue-next';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    laravelVersion: {
        type: String,
        required: true,
    },
    phpVersion: {
        type: String,
        required: true,
    },
});
</script>