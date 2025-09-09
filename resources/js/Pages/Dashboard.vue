<template>
    <AppLayout title="Tableau de Bord">
        <template #header>
            <h2 class="text-lg sm:text-xl font-medium text-gray-900">
                Tableau de Bord
            </h2>
        </template>

        <div class="py-6 sm:py-8 lg:py-12">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

                <!-- Vue d'ensemble -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Total ruchers -->
                    <div class="bg-white border border-gray-200 rounded-sm p-4">
                        <div class="flex items-center gap-3 mb-2">
                            <Building2 class="h-4 w-4 text-gray-700 flex-shrink-0" />
                            <span class="text-sm font-medium text-gray-700">Ruchers</span>
                        </div>
                        <div class="text-2xl font-medium text-gray-900 mb-3">
                            {{ stats.total_ruchers }}
                        </div>
                        <Link :href="route('ruchers.index')"
                            class="text-xs text-gray-600 hover:text-gray-900 font-medium">
                        Voir tous →
                        </Link>
                    </div>

                    <!-- Total ruches -->
                    <div class="bg-white border border-gray-200 rounded-sm p-4">
                        <div class="flex items-center gap-3 mb-2">
                            <Package class="h-4 w-4 text-gray-700 flex-shrink-0" />
                            <span class="text-sm font-medium text-gray-700">Ruches</span>
                        </div>
                        <div class="text-2xl font-medium text-gray-900 mb-3">
                            {{ stats.total_ruches }}
                        </div>
                        <div class="relative">
                            <button @click="showRuchersDropdown = !showRuchersDropdown"
                                class="text-xs text-gray-600 hover:text-gray-900 font-medium flex items-center gap-1">
                                Par rucher
                                <ChevronDown :class="{ 'rotate-180': showRuchersDropdown }"
                                    class="h-3 w-3 transition-transform" />
                            </button>

                            <!-- Dropdown -->
                            <div v-if="showRuchersDropdown"
                                class="absolute top-full left-0 right-0 mt-1 bg-white border border-gray-200 rounded-sm shadow-lg z-10 py-1">
                                <Link v-for="rucher in availableRuchers" :key="rucher.id"
                                    :href="route('ruchers.ruches.index', rucher.id)"
                                    class="block px-3 py-2 text-xs text-gray-700 hover:bg-gray-50 flex items-center justify-between"
                                    @click="showRuchersDropdown = false">
                                <span class="truncate">{{ rucher.nom }}</span>
                                <span class="text-gray-500 ml-2 flex-shrink-0">
                                    {{ rucher.ruches_count }}
                                </span>
                                </Link>

                                <div v-if="availableRuchers.length === 0"
                                    class="px-3 py-2 text-xs text-gray-500 italic">
                                    Aucun rucher
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Ruches actives -->
                    <div class="bg-white border border-gray-200 rounded-sm p-4">
                        <div class="flex items-center gap-3 mb-2">
                            <CheckCircle class="h-4 w-4 text-gray-700 flex-shrink-0" />
                            <span class="text-sm font-medium text-gray-700">Actives</span>
                        </div>
                        <div class="flex items-baseline gap-2 mb-3">
                            <span class="text-2xl font-medium text-gray-900">
                                {{ stats.ruches_actives }}
                            </span>
                            <span v-if="stats.total_ruches > 0" class="text-xs text-gray-600">
                                {{ Math.round((stats.ruches_actives / stats.total_ruches) * 100) }}%
                            </span>
                        </div>
                    </div>

                    <!-- Ruches à surveiller -->
                    <div class="bg-white border border-gray-200 rounded-sm p-4">
                        <div class="flex items-center gap-3 mb-2">
                            <AlertCircle class="h-4 w-4 text-gray-700 flex-shrink-0" />
                            <span class="text-sm font-medium text-gray-700">À surveiller</span>
                        </div>
                        <div class="text-2xl font-medium text-gray-900 mb-1">
                            {{ stats.ruches_inactives + stats.ruches_mortes }}
                        </div>
                        <div class="text-xs text-gray-600 space-x-2">
                            <span>{{ stats.ruches_inactives }} inactives</span>
                            <span>{{ stats.ruches_mortes }} mortes</span>
                        </div>
                    </div>
                </div>

                <!-- Actions rapides -->
                <div class="bg-white border border-gray-200 rounded-sm p-4 sm:p-6">
                    <h3 class="text-base font-medium text-gray-900 mb-4">
                        Actions rapides
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                        <Link :href="route('ruchers.create')"
                            class="bg-gray-900 hover:bg-gray-800 text-white text-sm font-medium py-3 px-4 rounded-sm flex items-center justify-center">
                        <Plus class="mr-2 h-4 w-4" />
                        Nouveau rucher
                        </Link>

                        <button v-if="stats.total_ruchers > 0"
                            class="bg-white hover:bg-gray-50 text-gray-500 border border-gray-300 text-sm font-medium py-3 px-4 rounded-sm flex items-center justify-center cursor-not-allowed"
                            disabled>
                            <Plus class="mr-2 h-4 w-4" />
                            Nouvelle visite
                        </button>

                        <button v-if="stats.total_ruches > 0"
                            class="bg-white hover:bg-gray-50 text-gray-500 border border-gray-300 text-sm font-medium py-3 px-4 rounded-sm flex items-center justify-center cursor-not-allowed"
                            disabled>
                            <Plus class="mr-2 h-4 w-4" />
                            Nouveau traitement
                        </button>

                        <button v-if="stats.total_ruches > 0"
                            class="bg-white hover:bg-gray-50 text-gray-500 border border-gray-300 text-sm font-medium py-3 px-4 rounded-sm flex items-center justify-center cursor-not-allowed"
                            disabled>
                            <Plus class="mr-2 h-4 w-4" />
                            Nouvelle récolte
                        </button>
                    </div>
                    <p class="mt-4 text-xs text-gray-600">
                        Les boutons visites, traitements et récoltes seront disponibles prochainement.
                    </p>
                </div>

                <!-- État vide -->
                <div v-if="stats.total_ruchers === 0"
                    class="text-center py-16 bg-white border border-gray-200 rounded-sm">
                    <Building2 class="mx-auto h-8 w-8 text-gray-400 mb-4" />
                    <h3 class="text-base font-medium text-gray-900 mb-2">Aucun rucher</h3>
                    <p class="text-sm text-gray-600 mb-8 max-w-sm mx-auto">
                        Commencez votre aventure apicole en créant votre premier rucher.
                    </p>
                    <Link :href="route('ruchers.create')"
                        class="inline-flex items-center bg-gray-900 hover:bg-gray-800 text-white text-sm font-medium py-3 px-4 rounded-sm">
                    <Plus class="mr-2 h-4 w-4" />
                    Créer mon premier rucher
                    </Link>
                </div>

                <!-- Activité récente -->
                <div v-else class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Ruchers récents -->
                    <div class="bg-white border border-gray-200 rounded-sm p-4 sm:p-6">
                        <h3 class="text-base font-medium text-gray-900 mb-4">
                            Ruchers récents
                        </h3>
                        <div class="space-y-3">
                            <Link v-for="rucher in ruchersRecents" :key="rucher.id"
                                :href="route('ruchers.show', rucher.id)"
                                class="block border border-gray-200 rounded-sm p-3 hover:border-gray-900 transition-colors">
                            <div class="flex items-center justify-between">
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ rucher.nom }}</p>
                                    <p class="text-xs text-gray-600 truncate">
                                        {{ rucher.localisation || 'Aucune localisation' }}
                                    </p>
                                </div>
                                <ArrowRight class="h-3 w-3 text-gray-400 flex-shrink-0 ml-3" />
                            </div>
                            </Link>
                        </div>
                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <Link :href="route('ruchers.index')"
                                class="text-sm font-medium text-gray-900 hover:text-gray-700">
                            Voir tous les ruchers →
                            </Link>
                        </div>
                    </div>

                    <!-- Ruches récentes -->
                    <div class="bg-white border border-gray-200 rounded-sm p-4 sm:p-6">
                        <h3 class="text-base font-medium text-gray-900 mb-4">
                            Ruches récentes
                        </h3>
                        <div class="space-y-3">
                            <Link v-for="ruche in ruchesRecentes" :key="ruche.id"
                                :href="route('ruchers.ruches.show', [ruche.rucher_id, ruche.id])"
                                class="block border border-gray-200 rounded-sm p-3 hover:border-gray-900 transition-colors">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3 min-w-0 flex-1">
                                    <div v-if="ruche.couleur" :style="{ backgroundColor: ruche.couleur }"
                                        class="w-3 h-3 rounded-full border border-gray-300 flex-shrink-0">
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-medium text-gray-900 truncate">{{ ruche.nom }}</p>
                                        <p class="text-xs text-gray-600 truncate">{{ ruche.rucher_nom }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 flex-shrink-0">
                                    <span :class="{
                                        'bg-gray-100 text-gray-800': ruche.statut === 'active',
                                        'bg-gray-100 text-gray-600': ruche.statut === 'inactive',
                                        'bg-gray-100 text-gray-900': ruche.statut === 'morte'
                                    }" class="text-xs font-medium px-2 py-1 rounded-sm">
                                        {{ getStatutLabel(ruche.statut) }}
                                    </span>
                                    <ArrowRight class="h-3 w-3 text-gray-400" />
                                </div>
                            </div>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';
import { Building2, Package, CheckCircle, AlertCircle, Plus, ArrowRight, ChevronDown } from 'lucide-vue-next';
import { ref, computed } from 'vue';

// Props du contrôleur
const props = defineProps({
    stats: Object,
    ruchersRecents: Array,
    ruchesRecentes: Array
});

// État du dropdown
const showRuchersDropdown = ref(false);

// Ruchers avec comptage de ruches (calculé côté client)
const availableRuchers = computed(() => {
    return props.ruchersRecents.map(rucher => ({
        ...rucher,
        ruches_count: rucher.ruches?.length || 0
    })).filter(rucher => rucher.ruches_count > 0);
});

// Fermer dropdown quand on clique ailleurs
function closeDropdown(event) {
    if (!event.target.closest('.relative')) {
        showRuchersDropdown.value = false;
    }
}

// Écouter les clics globaux
if (typeof window !== 'undefined') {
    document.addEventListener('click', closeDropdown);
}

// Helper pour les statuts
function getStatutLabel(statut) {
    const labels = {
        'active': 'Active',
        'inactive': 'Inactive',
        'morte': 'Morte'
    };
    return labels[statut] || statut;
}
</script>