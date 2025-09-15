<template>
    <AppLayout title="Tableau de Bord">
        <template #header>
            <h2 class="text-lg sm:text-xl font-medium text-gray-900">
                Tableau de Bord
            </h2>
        </template>

        <div class="py-6 sm:py-8 lg:py-12">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

                <!-- Alerte urgente si problèmes -->
                <div v-if="stats.ruches_mortes > 0" class="bg-red-50 border-l-4 border-red-400 p-4 rounded-r-lg">
                    <div class="flex items-center">
                        <AlertTriangle class="h-5 w-5 text-red-400 mr-3" />
                        <p class="text-sm text-red-700">
                            <span class="font-semibold">Attention !</span>
                            {{ stats.ruches_mortes }} ruche{{ stats.ruches_mortes > 1 ? 's' : '' }}
                            {{ stats.ruches_mortes > 1 ? 'sont mortes' : 'est morte' }}. Action requise.
                        </p>
                    </div>
                </div>

                <!-- Vue d'ensemble avec codes couleur -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Total ruchers -->
                    <div
                        class="bg-gradient-to-br from-blue-50 to-blue-100 border-l-4 border-blue-500 rounded-lg p-4 shadow-sm">
                        <div class="flex items-center gap-3 mb-2">
                            <Building2 class="h-5 w-5 text-blue-600 flex-shrink-0" />
                            <span class="text-sm font-semibold text-blue-800">Ruchers</span>
                        </div>
                        <div class="text-3xl font-bold text-blue-900 mb-3">
                            {{ stats.total_ruchers }}
                        </div>
                        <Link :href="route('ruchers.index')"
                            class="text-xs text-blue-700 hover:text-blue-900 font-medium inline-flex items-center">
                        Voir tous
                        <ArrowRight class="h-3 w-3 ml-1" />
                        </Link>
                    </div>

                    <!-- Total ruches -->
                    <div
                        class="bg-gradient-to-br from-amber-50 to-amber-100 border-l-4 border-amber-500 rounded-lg p-4 shadow-sm">
                        <div class="flex items-center gap-3 mb-2">
                            <Package class="h-5 w-5 text-amber-600 flex-shrink-0" />
                            <span class="text-sm font-semibold text-amber-800">Total Ruches</span>
                        </div>
                        <div class="text-3xl font-bold text-amber-900 mb-1">
                            {{ stats.total_ruches }}
                        </div>
                        <div class="relative">
                            <button @click="showRuchersDropdown = !showRuchersDropdown"
                                class="text-xs text-amber-700 hover:text-amber-900 font-medium inline-flex items-center">
                                Par rucher
                                <ChevronDown :class="{ 'rotate-180': showRuchersDropdown }"
                                    class="h-3 w-3 ml-1 transition-transform" />
                            </button>

                            <!-- Dropdown amélioré -->
                            <div v-if="showRuchersDropdown"
                                class="absolute top-full left-0 right-0 mt-2 bg-white border border-gray-200 rounded-lg shadow-xl z-20 py-2 max-h-48 overflow-y-auto min-w-48">
                                <Link v-for="rucher in availableRuchers" :key="rucher.id"
                                    :href="route('ruchers.ruches.index', rucher.id)"
                                    class="block px-4 py-3 text-sm text-gray-700 hover:bg-amber-50 flex items-center justify-between group"
                                    @click="showRuchersDropdown = false">
                                <span class="truncate font-medium">{{ rucher.nom }}</span>
                                <span class="bg-amber-100 text-amber-800 text-xs font-bold px-2 py-1 rounded-full ml-2">
                                    {{ rucher.ruches_count }}
                                </span>
                                </Link>

                                <div v-if="availableRuchers.length === 0"
                                    class="px-4 py-3 text-sm text-gray-500 italic text-center">
                                    Aucun rucher avec des ruches
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Ruches actives - VERT -->
                    <div
                        class="bg-gradient-to-br from-green-50 to-green-100 border-l-4 border-green-500 rounded-lg p-4 shadow-sm">
                        <div class="flex items-center gap-3 mb-2">
                            <CheckCircle class="h-5 w-5 text-green-600 flex-shrink-0" />
                            <span class="text-sm font-semibold text-green-800">✅ Actives</span>
                        </div>
                        <div class="flex items-baseline gap-2 mb-1">
                            <span class="text-3xl font-bold text-green-900">
                                {{ stats.ruches_actives }}
                            </span>
                            <span v-if="stats.total_ruches > 0" class="text-sm font-semibold text-green-700">
                                {{ Math.round((stats.ruches_actives / stats.total_ruches) * 100) }}%
                            </span>
                        </div>
                        <p class="text-xs text-green-700 font-medium">Tout va bien ! 🐝</p>
                    </div>

                    <!-- Ruches à surveiller - ROUGE/ORANGE -->
                    <div :class="{
                        'bg-gradient-to-br from-red-50 to-red-100 border-l-4 border-red-500': stats.ruches_mortes > 0,
                        'bg-gradient-to-br from-orange-50 to-orange-100 border-l-4 border-orange-500': stats.ruches_mortes === 0 && (stats.ruches_inactives > 0),
                        'bg-gradient-to-br from-gray-50 to-gray-100 border-l-4 border-gray-300': stats.ruches_inactives === 0 && stats.ruches_mortes === 0
                    }" class="rounded-lg p-4 shadow-sm">
                        <div class="flex items-center gap-3 mb-2">
                            <AlertCircle :class="{
                                'text-red-600': stats.ruches_mortes > 0,
                                'text-orange-600': stats.ruches_mortes === 0 && stats.ruches_inactives > 0,
                                'text-gray-400': stats.ruches_inactives === 0 && stats.ruches_mortes === 0
                            }" class="h-5 w-5 flex-shrink-0" />
                            <span :class="{
                                'text-red-800': stats.ruches_mortes > 0,
                                'text-orange-800': stats.ruches_mortes === 0 && stats.ruches_inactives > 0,
                                'text-gray-600': stats.ruches_inactives === 0 && stats.ruches_mortes === 0
                            }" class="text-sm font-semibold">
                                {{ stats.ruches_mortes > 0 ? '🚨 Urgent' : stats.ruches_inactives > 0 ? '⚠️ À surveiller' : '✅ Rien à signaler' }}
                            </span>
                        </div>
                        <div :class="{
                            'text-red-900': stats.ruches_mortes > 0,
                            'text-orange-900': stats.ruches_mortes === 0 && stats.ruches_inactives > 0,
                            'text-gray-600': stats.ruches_inactives === 0 && stats.ruches_mortes === 0
                        }" class="text-3xl font-bold mb-1">
                            {{ stats.ruches_inactives + stats.ruches_mortes || '0' }}
                        </div>
                        <div :class="{
                            'text-red-700': stats.ruches_mortes > 0,
                            'text-orange-700': stats.ruches_mortes === 0 && stats.ruches_inactives > 0,
                            'text-gray-500': stats.ruches_inactives === 0 && stats.ruches_mortes === 0
                        }" class="text-xs font-medium space-x-2">
                            <span v-if="stats.ruches_inactives > 0">{{ stats.ruches_inactives }} inactives</span>
                            <span v-if="stats.ruches_mortes > 0" class="text-red-600 font-bold">{{ stats.ruches_mortes
                                }}
                                mortes</span>
                            <span v-if="stats.ruches_inactives === 0 && stats.ruches_mortes === 0">Parfait !</span>
                        </div>
                    </div>
                </div>

                <!-- Actions rapides avec couleurs -->
                <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6 flex items-center">
                        <Zap class="h-5 w-5 text-yellow-500 mr-2" />
                        Actions rapides
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        <!-- Nouveau rucher -->
                        <Link :href="route('ruchers.create')"
                            class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold py-4 px-6 rounded-lg flex items-center justify-center shadow-md transition-all hover:shadow-lg transform hover:-translate-y-0.5">
                        <Plus class="mr-2 h-5 w-5" />
                        Nouveau rucher
                        </Link>

                        <!-- Visite rapide -->
                        <div v-if="stats.total_ruches > 0" class="relative">
                            <button @click="showVisiteDropdown = !showVisiteDropdown"
                                class="w-full bg-green-600 hover:bg-green-700 text-white text-sm font-semibold py-4 px-6 rounded-lg flex items-center justify-center shadow-md transition-all hover:shadow-lg transform hover:-translate-y-0.5">
                                <Calendar class="mr-2 h-5 w-5" />
                                Nouvelle visite
                                <ChevronDown class="ml-2 h-4 w-4" />
                            </button>

                            <!-- Dropdown sélection ruche -->
                            <div v-if="showVisiteDropdown"
                                class="absolute top-full left-0 right-0 mt-2 bg-white border border-gray-200 rounded-lg shadow-xl z-30 py-2 max-h-48 overflow-y-auto">
                                <div class="px-3 py-2 text-xs font-semibold text-gray-500 border-b border-gray-100">
                                    Choisir une ruche :
                                </div>
                                <template v-for="rucher in ruchersRecents" :key="rucher.id">
                                    <div v-if="rucher.ruches && rucher.ruches.length > 0">
                                        <div class="px-3 py-2 text-xs font-medium text-gray-700 bg-gray-50">
                                            📍 {{ rucher.nom }}
                                        </div>
                                        <Link v-for="ruche in rucher.ruches.slice(0, 3)" :key="ruche.id"
                                            :href="route('ruchers.ruches.visites.create', [rucher.id, ruche.id])"
                                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-green-50 flex items-center gap-2"
                                            @click="showVisiteDropdown = false">
                                        <div v-if="ruche.couleur" :style="{ backgroundColor: ruche.couleur }"
                                            class="w-3 h-3 rounded-full border border-gray-300 flex-shrink-0">
                                        </div>
                                        <span class="truncate">{{ ruche.nom }}</span>
                                        <span :class="{
                                            'text-green-600': ruche.statut === 'active',
                                            'text-orange-600': ruche.statut === 'inactive',
                                            'text-red-600': ruche.statut === 'morte'
                                        }" class="text-xs">
                                            {{ getStatutDisplay(ruche.statut).replace(/[🚨⚠️✅]/g, '') }}
                                        </span>
                                        </Link>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Traitement rapide -->
                        <div v-if="stats.total_ruches > 0" class="relative">
                            <button @click="showTraitementDropdown = !showTraitementDropdown"
                                class="w-full bg-purple-600 hover:bg-purple-700 text-white text-sm font-semibold py-4 px-6 rounded-lg flex items-center justify-center shadow-md transition-all hover:shadow-lg transform hover:-translate-y-0.5">
                                <Pill class="mr-2 h-5 w-5" />
                                Traitement
                                <ChevronDown class="ml-2 h-4 w-4" />
                            </button>

                            <!-- Dropdown sélection ruche -->
                            <div v-if="showTraitementDropdown"
                                class="absolute top-full left-0 right-0 mt-2 bg-white border border-gray-200 rounded-lg shadow-xl z-30 py-2 max-h-48 overflow-y-auto">
                                <div class="px-3 py-2 text-xs font-semibold text-gray-500 border-b border-gray-100">
                                    Choisir une ruche :
                                </div>
                                <template v-for="rucher in ruchersRecents" :key="rucher.id">
                                    <div v-if="rucher.ruches && rucher.ruches.length > 0">
                                        <div class="px-3 py-2 text-xs font-medium text-gray-700 bg-gray-50">
                                            📍 {{ rucher.nom }}
                                        </div>
                                        <Link v-for="ruche in rucher.ruches.slice(0, 3)" :key="ruche.id"
                                            :href="route('ruchers.ruches.traitements.create', [rucher.id, ruche.id])"
                                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-purple-50 flex items-center gap-2"
                                            @click="showTraitementDropdown = false">
                                        <div v-if="ruche.couleur" :style="{ backgroundColor: ruche.couleur }"
                                            class="w-3 h-3 rounded-full border border-gray-300 flex-shrink-0">
                                        </div>
                                        <span class="truncate">{{ ruche.nom }}</span>
                                        <span :class="{
                                            'text-green-600': ruche.statut === 'active',
                                            'text-orange-600': ruche.statut === 'inactive',
                                            'text-red-600': ruche.statut === 'morte'
                                        }" class="text-xs">
                                            {{ getStatutDisplay(ruche.statut).replace(/[🚨⚠️✅]/g, '') }}
                                        </span>
                                        </Link>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Récolte rapide -->
                        <div v-if="stats.total_ruches > 0" class="relative">
                            <button @click="showRecolteDropdown = !showRecolteDropdown"
                                class="w-full bg-amber-600 hover:bg-amber-700 text-white text-sm font-semibold py-4 px-6 rounded-lg flex items-center justify-center shadow-md transition-all hover:shadow-lg transform hover:-translate-y-0.5">
                                <Droplets class="mr-2 h-5 w-5" />
                                Récolte
                                <ChevronDown class="ml-2 h-4 w-4" />
                            </button>

                            <!-- Dropdown sélection ruche -->
                            <div v-if="showRecolteDropdown"
                                class="absolute top-full left-0 right-0 mt-2 bg-white border border-gray-200 rounded-lg shadow-xl z-30 py-2 max-h-48 overflow-y-auto">
                                <div class="px-3 py-2 text-xs font-semibold text-gray-500 border-b border-gray-100">
                                    Choisir une ruche :
                                </div>
                                <template v-for="rucher in ruchersRecents" :key="rucher.id">
                                    <div v-if="rucher.ruches && rucher.ruches.length > 0">
                                        <div class="px-3 py-2 text-xs font-medium text-gray-700 bg-gray-50">
                                            📍 {{ rucher.nom }}
                                        </div>
                                        <Link v-for="ruche in rucher.ruches.slice(0, 3)" :key="ruche.id"
                                            :href="route('ruchers.ruches.recoltes.create', [rucher.id, ruche.id])"
                                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-amber-50 flex items-center gap-2"
                                            @click="showRecolteDropdown = false">
                                        <div v-if="ruche.couleur" :style="{ backgroundColor: ruche.couleur }"
                                            class="w-3 h-3 rounded-full border border-gray-300 flex-shrink-0">
                                        </div>
                                        <span class="truncate">{{ ruche.nom }}</span>
                                        <span :class="{
                                            'text-green-600': ruche.statut === 'active',
                                            'text-orange-600': ruche.statut === 'inactive',
                                            'text-red-600': ruche.statut === 'morte'
                                        }" class="text-xs">
                                            {{ getStatutDisplay(ruche.statut).replace(/[🚨⚠️✅]/g, '') }}
                                        </span>
                                        </Link>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <p class="mt-4 text-sm text-gray-500 bg-blue-50 p-3 rounded-lg border-l-4 border-blue-400">
                        💡 <strong>Actions rapides :</strong> Cliquez sur les boutons colorés pour accéder directement
                        aux
                        formulaires de saisie !
                    </p>
                </div>

                <!-- État vide -->
                <div v-if="stats.total_ruchers === 0"
                    class="text-center py-20 bg-gradient-to-br from-blue-50 to-indigo-100 border-2 border-dashed border-blue-300 rounded-xl">
                    <Building2 class="mx-auto h-16 w-16 text-blue-400 mb-6" />
                    <h3 class="text-xl font-bold text-blue-900 mb-3">Bienvenue dans BeeNote ! 🐝</h3>
                    <p class="text-base text-blue-700 mb-8 max-w-md mx-auto">
                        Commencez votre aventure apicole en créant votre premier rucher. C'est parti !
                    </p>
                    <Link :href="route('ruchers.create')"
                        class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white text-base font-semibold py-4 px-8 rounded-lg shadow-lg transition-all hover:shadow-xl transform hover:-translate-y-1">
                    <Plus class="mr-3 h-5 w-5" />
                    Créer mon premier rucher
                    </Link>
                </div>

                <!-- Activité récente avec codes couleur -->
                <div v-else class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Ruchers récents -->
                    <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
                        <h3 class="text-lg font-semibold text-gray-900 mb-6 flex items-center">
                            <Building2 class="h-5 w-5 text-blue-600 mr-2" />
                            Ruchers récents
                        </h3>
                        <div class="space-y-4">
                            <Link v-for="rucher in ruchersRecents" :key="rucher.id"
                                :href="route('ruchers.show', rucher.id)"
                                class="block border border-gray-200 rounded-lg p-4 hover:border-blue-400 hover:shadow-md transition-all group">
                            <div class="flex items-center justify-between">
                                <div class="min-w-0 flex-1">
                                    <p class="text-base font-semibold text-gray-900 truncate group-hover:text-blue-600">
                                        {{ rucher.nom }}
                                    </p>
                                    <p class="text-sm text-gray-600 truncate mt-1">
                                        📍 {{ rucher.localisation || 'Aucune localisation' }}
                                    </p>
                                    <p class="text-xs text-blue-600 font-medium mt-2">
                                        🐝 {{ rucher.ruches?.length || 0 }} ruche{{ (rucher.ruches?.length || 0) > 1 ?
                                        's' : ''
                                        }}
                                    </p>
                                </div>
                                <ArrowRight
                                    class="h-4 w-4 text-gray-400 group-hover:text-blue-600 flex-shrink-0 ml-3 transition-colors" />
                            </div>
                            </Link>
                        </div>
                        <div class="mt-6 pt-4 border-t border-gray-200">
                            <Link :href="route('ruchers.index')"
                                class="text-sm font-semibold text-blue-600 hover:text-blue-700 inline-flex items-center">
                            Voir tous les ruchers
                            <ArrowRight class="h-4 w-4 ml-1" />
                            </Link>
                        </div>
                    </div>

                    <!-- Ruches récentes avec indicateurs visuels -->
                    <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
                        <h3 class="text-lg font-semibold text-gray-900 mb-6 flex items-center">
                            <Package class="h-5 w-5 text-amber-600 mr-2" />
                            Ruches récentes
                        </h3>
                        <div class="space-y-4">
                            <Link v-for="ruche in ruchesRecentes" :key="ruche.id"
                                :href="route('ruchers.ruches.show', [ruche.rucher_id, ruche.id])"
                                class="block border border-gray-200 rounded-lg p-4 hover:border-amber-400 hover:shadow-md transition-all group">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-4 min-w-0 flex-1">
                                    <!-- Indicateur couleur ruche -->
                                    <div v-if="ruche.couleur" :style="{ backgroundColor: ruche.couleur }"
                                        class="w-4 h-4 rounded-full border-2 border-gray-300 flex-shrink-0 shadow-sm">
                                    </div>
                                    <div v-else
                                        class="w-4 h-4 rounded-full bg-gray-200 border-2 border-gray-300 flex-shrink-0">
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <p
                                            class="text-base font-semibold text-gray-900 truncate group-hover:text-amber-600">
                                            {{ ruche.nom }}
                                        </p>
                                        <p class="text-sm text-gray-600 truncate mt-1">
                                            📍 {{ ruche.rucher_nom }}
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3 flex-shrink-0">
                                    <!-- Badge statut avec couleurs -->
                                    <span :class="{
                                        'bg-green-100 text-green-800 border-green-200': ruche.statut === 'active',
                                        'bg-orange-100 text-orange-800 border-orange-200': ruche.statut === 'inactive',
                                        'bg-red-100 text-red-800 border-red-200': ruche.statut === 'morte'
                                    }" class="text-xs font-bold px-3 py-1 rounded-full border">
                                        {{ getStatutDisplay(ruche.statut) }}
                                    </span>
                                    <ArrowRight
                                        class="h-4 w-4 text-gray-400 group-hover:text-amber-600 transition-colors" />
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
import {
    Building2, Package, CheckCircle, AlertCircle, AlertTriangle, Plus, ArrowRight,
    ChevronDown, Zap, Calendar, Pill, Droplets
} from 'lucide-vue-next';
import { ref, computed } from 'vue';

// Props du contrôleur
const props = defineProps({
    stats: Object,
    ruchersRecents: Array,
    ruchesRecentes: Array
});

// État des dropdowns
const showRuchersDropdown = ref(false);
const showVisiteDropdown = ref(false);
const showTraitementDropdown = ref(false);
const showRecolteDropdown = ref(false);

// Ruchers avec comptage de ruches
const availableRuchers = computed(() => {
    return props.ruchersRecents.map(rucher => ({
        ...rucher,
        ruches_count: rucher.ruches?.length || 0
    })).filter(rucher => rucher.ruches_count > 0);
});

// Fermer dropdowns quand on clique ailleurs
function closeDropdown(event) {
    if (!event.target.closest('.relative')) {
        showRuchersDropdown.value = false;
        showVisiteDropdown.value = false;
        showTraitementDropdown.value = false;
        showRecolteDropdown.value = false;
    }
}

// Écouter les clics globaux
if (typeof window !== 'undefined') {
    document.addEventListener('click', closeDropdown);
}

// Helper pour les statuts avec emojis
function getStatutDisplay(statut) {
    const displays = {
        'active': '✅ Active',
        'inactive': '⚠️ Inactive',
        'morte': '🚨 Morte'
    };
    return displays[statut] || statut;
}
</script>