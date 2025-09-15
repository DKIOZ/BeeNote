<template>
    <AppLayout :title="`Visite du ${formatDate(visite.date_visite)} - ${ruche.nom}`">
        <template #header>
            <div class="space-y-3">
                <!-- Breadcrumbs simplifiés -->
                <nav class="text-xs text-gray-500">
                    <div class="flex items-center gap-1">
                        <Link :href="route('ruchers.show', rucher.id)" class="hover:text-gray-700">{{ rucher.nom }}</Link>
                        <span>›</span>
                        <Link :href="route('ruchers.ruches.show', [rucher.id, ruche.id])" class="hover:text-gray-700">{{ ruche.nom }}</Link>
                        <span>›</span>
                        <span class="text-gray-900 font-medium">Visite du {{ formatDate(visite.date_visite) }}</span>
                    </div>
                </nav>

                <!-- Titre avec état général -->
                <div class="flex flex-col gap-4 sm:flex-row sm:justify-between sm:items-start">
                    <div class="flex items-start gap-4">
                        <!-- Couleur de la ruche -->
                        <div v-if="ruche.couleur" 
                             :style="{ backgroundColor: ruche.couleur }"
                             class="w-6 h-6 rounded-full border-2 border-white shadow-md flex-shrink-0 mt-1">
                        </div>

                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">
                                {{ ruche.nom }}
                            </h1>
                            <p class="text-lg text-gray-600">
                                {{ formatDateLong(visite.date_visite) }}
                            </p>
                            
                            <!-- État général visible immédiatement -->
                            <div class="mt-3">
                                <div v-if="getEtatGeneral()" class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-medium"
                                     :class="getEtatGeneralStyle()">
                                    <span class="text-xl">{{ getEtatGeneralEmoji() }}</span>
                                    <span>{{ getEtatGeneral() }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex flex-col gap-2 sm:flex-row">
                        <Link :href="route('ruchers.ruches.visites.edit', [rucher.id, ruche.id, visite.id])"
                              class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-2 px-4 rounded-lg inline-flex items-center justify-center gap-2 transition-colors">
                            <Edit class="w-4 h-4" />
                            Modifier
                        </Link>
                        
                        <Link :href="route('ruchers.ruches.visites.index', [rucher.id, ruche.id])"
                              class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium py-2 px-4 rounded-lg text-center transition-colors">
                            Retour
                        </Link>
                    </div>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-5xl mx-auto px-4 sm:px-6">

                <!-- Message de succès -->
                <div v-if="$page.props.flash?.message" class="mb-6">
                    <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg text-sm flex items-center gap-2">
                        <CheckCircle class="w-5 h-5" />
                        {{ $page.props.flash.message }}
                    </div>
                </div>

                <!-- Résumé visuel en haut -->
                <div class="mb-8 bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-200 rounded-xl p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                        <TrendingUp class="w-5 h-5" />
                        Résumé de la visite
                    </h2>
                    
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        <!-- Reine -->
                        <div class="text-center p-3 bg-white rounded-lg border border-blue-200">
                            <div class="text-2xl mb-1">👑</div>
                            <div class="text-xs text-gray-600 mb-1">Reine</div>
                            <div class="font-medium text-sm" :class="getStatusColor(visite.reine_vue)">
                                {{ getSimpleStatus(visite.reine_vue) }}
                            </div>
                        </div>

                        <!-- Ponte -->
                        <div class="text-center p-3 bg-white rounded-lg border border-blue-200">
                            <div class="text-2xl mb-1">🥚</div>
                            <div class="text-xs text-gray-600 mb-1">Ponte</div>
                            <div class="font-medium text-sm" :class="getStatusColor(visite.ponte_observee)">
                                {{ getSimpleStatus(visite.ponte_observee) }}
                            </div>
                        </div>

                        <!-- Population -->
                        <div class="text-center p-3 bg-white rounded-lg border border-blue-200">
                            <div class="text-2xl mb-1">🐝</div>
                            <div class="text-xs text-gray-600 mb-1">Population</div>
                            <div class="font-medium text-sm" :class="getPopulationColor(visite.estimation_population)">
                                {{ getPopulationLabel(visite.estimation_population) }}
                            </div>
                        </div>

                        <!-- Humeur -->
                        <div class="text-center p-3 bg-white rounded-lg border border-blue-200">
                            <div class="text-2xl mb-1">😊</div>
                            <div class="text-xs text-gray-600 mb-1">Humeur</div>
                            <div class="font-medium text-sm" :class="getHumeurColor(visite.humeur_colonie)">
                                {{ getHumeurLabel(visite.humeur_colonie) }}
                            </div>
                        </div>
                    </div>

                    <!-- Alerte problèmes -->
                    <div v-if="hasProblems()" class="mt-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                        <div class="flex items-center gap-2 text-red-800 text-sm font-medium mb-2">
                            <AlertTriangle class="w-4 h-4" />
                            Points d'attention détectés
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <span v-if="visite.varroas_observes === 'non_ok'" 
                                  class="px-2 py-1 bg-red-100 text-red-700 text-xs rounded-full">
                                🦟 Beaucoup de varroas
                            </span>
                            <span v-if="visite.mortalite === 'non_ok'" 
                                  class="px-2 py-1 bg-red-100 text-red-700 text-xs rounded-full">
                                💀 Mortalité anormale
                            </span>
                            <span v-if="visite.cellules_royales === 'ok'" 
                                  class="px-2 py-1 bg-orange-100 text-orange-700 text-xs rounded-full">
                                👸 Cellules royales présentes
                            </span>
                            <span v-if="visite.reserves_miel === 'insuffisantes'" 
                                  class="px-2 py-1 bg-yellow-100 text-yellow-700 text-xs rounded-full">
                                🍯 Réserves miel faibles
                            </span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                    <!-- Contenu principal -->
                    <div class="lg:col-span-2 space-y-6">

                        <!-- Observations principales -->
                        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-6 flex items-center gap-2">
                                <Eye class="w-5 h-5" />
                                Observations principales
                            </h3>

                            <div class="space-y-6">
                                <!-- Reine - Section détaillée -->
                                <div class="border-l-4 border-yellow-400 pl-4">
                                    <h4 class="font-medium text-gray-900 mb-3 flex items-center gap-2">
                                        <span class="text-lg">👑</span>
                                        La reine
                                    </h4>
                                    <div class="grid grid-cols-2 gap-4 text-sm">
                                        <SimpleObservation label="Vue" :value="visite.reine_vue" />
                                        <SimpleObservation label="Marquée" :value="visite.reine_marquee" />
                                        <SimpleObservation label="Ponte" :value="visite.ponte_observee" />
                                        <SimpleObservation label="Qualité ponte" :value="visite.qualite_ponte" />
                                    </div>
                                    
                                    <div v-if="visite.cellules_royales === 'ok'" class="mt-3 p-3 bg-orange-50 border border-orange-200 rounded-lg">
                                        <div class="flex items-center gap-2 text-orange-800 text-sm font-medium">
                                            <AlertTriangle class="w-4 h-4" />
                                            Cellules royales détectées
                                            <span v-if="visite.nombre_cellules_royales" class="ml-2">
                                                ({{ visite.nombre_cellules_royales }})
                                            </span>
                                        </div>
                                        <p class="text-orange-700 text-xs mt-1">
                                            La colonie pourrait préparer un essaimage ou remplacer la reine.
                                        </p>
                                    </div>
                                </div>

                                <!-- Couvain -->
                                <div class="border-l-4 border-green-400 pl-4">
                                    <h4 class="font-medium text-gray-900 mb-3 flex items-center gap-2">
                                        <span class="text-lg">🥚</span>
                                        Le couvain (bébés abeilles)
                                    </h4>
                                    <div class="grid grid-cols-2 gap-4 text-sm">
                                        <SimpleObservation label="Œufs" :value="visite.oeufs_vus" help="Petits grains blancs" />
                                        <SimpleObservation label="Larves" :value="visite.larves_vues" help="Petits vers blancs" />
                                        <SimpleObservation label="Operculé" :value="visite.couvain_opercule" help="Cellules fermées" />
                                        <SimpleObservation label="Surface" :value="visite.surface_couvain" />
                                    </div>
                                </div>

                                <!-- Comportement -->
                                <div class="border-l-4 border-blue-400 pl-4">
                                    <h4 class="font-medium text-gray-900 mb-3 flex items-center gap-2">
                                        <span class="text-lg">🐝</span>
                                        Comportement de la colonie
                                    </h4>
                                    <div class="grid grid-cols-2 gap-4 text-sm">
                                        <SimpleObservation label="Humeur" :value="visite.humeur_colonie" />
                                        <SimpleObservation label="Activité entrée" :value="visite.activite_entree" />
                                        <SimpleObservation label="Population" :value="visite.estimation_population" />
                                        <SimpleObservation label="Bruit" :value="visite.bruit_colonie" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Actions réalisées -->
                        <div v-if="hasActions" class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                                <Wrench class="w-5 h-5" />
                                Ce qui a été fait
                            </h3>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div v-if="visite.nourrissement_effectue"
                                     class="flex items-center gap-3 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                                    <span class="text-xl">🍯</span>
                                    <div>
                                        <div class="font-medium text-blue-900">Nourrissement</div>
                                        <div v-if="visite.type_nourrissement || visite.quantite_nourrissement" 
                                             class="text-sm text-blue-700">
                                            <span v-if="visite.type_nourrissement">{{ visite.type_nourrissement }}</span>
                                            <span v-if="visite.quantite_nourrissement"> - {{ visite.quantite_nourrissement }}kg</span>
                                        </div>
                                    </div>
                                </div>

                                <div v-if="visite.traitement_applique"
                                     class="flex items-center gap-3 p-3 bg-purple-50 border border-purple-200 rounded-lg">
                                    <span class="text-xl">💊</span>
                                    <div>
                                        <div class="font-medium text-purple-900">Traitement appliqué</div>
                                        <div class="text-sm text-purple-700">Contre parasites/maladies</div>
                                    </div>
                                </div>

                                <div v-if="visite.hausse_ajoutee"
                                     class="flex items-center gap-3 p-3 bg-green-50 border border-green-200 rounded-lg">
                                    <span class="text-xl">📦</span>
                                    <div>
                                        <div class="font-medium text-green-900">Hausse ajoutée</div>
                                        <div class="text-sm text-green-700">Plus d'espace pour le miel</div>
                                    </div>
                                </div>

                                <div v-if="visite.hausse_retiree"
                                     class="flex items-center gap-3 p-3 bg-orange-50 border border-orange-200 rounded-lg">
                                    <span class="text-xl">📦</span>
                                    <div>
                                        <div class="font-medium text-orange-900">Hausse retirée</div>
                                        <div class="text-sm text-orange-700">Récolte ou stockage</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div v-if="visite.notes_generales || visite.actions_prevues"
                             class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                                <FileText class="w-5 h-5" />
                                Notes
                            </h3>

                            <div class="space-y-4">
                                <div v-if="visite.notes_generales" class="p-4 bg-gray-50 rounded-lg">
                                    <h4 class="font-medium text-gray-900 mb-2">Observations</h4>
                                    <p class="text-gray-700 text-sm leading-relaxed whitespace-pre-line">
                                        {{ visite.notes_generales }}
                                    </p>
                                </div>

                                <div v-if="visite.actions_prevues" class="p-4 bg-blue-50 rounded-lg">
                                    <h4 class="font-medium text-blue-900 mb-2">À faire la prochaine fois</h4>
                                    <p class="text-blue-800 text-sm leading-relaxed whitespace-pre-line">
                                        {{ visite.actions_prevues }}
                                    </p>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">
                        
                        <!-- Météo -->
                        <div v-if="hasMeteoData" class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                            <h3 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                                <Sun class="w-5 h-5" />
                                Conditions météo
                            </h3>

                            <div class="space-y-3">
                                <div v-if="visite.meteo" class="flex items-center gap-3">
                                    <span class="text-2xl">{{ getMeteoEmoji(visite.meteo) }}</span>
                                    <span class="text-sm font-medium capitalize">{{ getMeteoLabel(visite.meteo) }}</span>
                                </div>
                                
                                <div class="grid grid-cols-2 gap-3 text-sm">
                                    <div v-if="visite.temperature" class="text-center p-2 bg-gray-50 rounded">
                                        <div class="font-medium">{{ visite.temperature }}°C</div>
                                        <div class="text-xs text-gray-600">Température</div>
                                    </div>
                                    <div v-if="visite.humidite" class="text-center p-2 bg-gray-50 rounded">
                                        <div class="font-medium">{{ visite.humidite }}%</div>
                                        <div class="text-xs text-gray-600">Humidité</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Réserves -->
                        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                            <h3 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                                <Archive class="w-5 h-5" />
                                Réserves de nourriture
                            </h3>

                            <div class="space-y-3">
                                <div class="flex items-center justify-between p-3 rounded-lg"
                                     :class="getReserveStyle(visite.reserves_miel)">
                                    <div class="flex items-center gap-2">
                                        <span class="text-lg">🍯</span>
                                        <span class="text-sm font-medium">Miel</span>
                                    </div>
                                    <span class="text-sm font-medium">{{ getReserveLabel(visite.reserves_miel) }}</span>
                                </div>

                                <div class="flex items-center justify-between p-3 rounded-lg"
                                     :class="getReserveStyle(visite.reserves_pollen)">
                                    <div class="flex items-center gap-2">
                                        <span class="text-lg">🌸</span>
                                        <span class="text-sm font-medium">Pollen</span>
                                    </div>
                                    <span class="text-sm font-medium">{{ getReserveLabel(visite.reserves_pollen) }}</span>
                                </div>

                                <div v-if="visite.cadres_miel_opercule" class="pt-3 border-t border-gray-200">
                                    <div class="text-center p-2 bg-yellow-50 rounded">
                                        <div class="font-medium text-yellow-800">{{ visite.cadres_miel_opercule }}</div>
                                        <div class="text-xs text-yellow-700">Cadres de miel prêts</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Santé -->
                        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                            <h3 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                                <Shield class="w-5 h-5" />
                                État de santé
                            </h3>

                            <div class="space-y-3">
                                <HealthIndicator 
                                    icon="🦟" 
                                    label="Varroas (parasites)" 
                                    :value="visite.varroas_observes" 
                                />
                                <HealthIndicator 
                                    icon="🐛" 
                                    label="Fausse teigne" 
                                    :value="visite.fausse_teigne" 
                                    :inverted="true"
                                />
                                <HealthIndicator 
                                    icon="💀" 
                                    label="Mortalité" 
                                    :value="visite.mortalite" 
                                />

                                <div v-if="visite.mortalite === 'non_ok' && visite.abeilles_mortes_quantite" 
                                     class="mt-3 p-3 bg-red-50 border border-red-200 rounded-lg">
                                    <div class="text-sm font-medium text-red-900">
                                        Quantité: {{ visite.abeilles_mortes_quantite.replace('_', ' ') }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Structure -->
                        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                            <h3 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                                <Package class="w-5 h-5" />
                                Structure de la ruche
                            </h3>

                            <div class="space-y-3">
                                <SimpleObservation label="État des cadres" :value="visite.cadres_etat" />
                                <SimpleObservation label="Construction cire" :value="visite.cire_construite" />

                                <div v-if="visite.nombre_cadres_total || visite.nombre_cadres_occupes" 
                                     class="grid grid-cols-2 gap-2 pt-3 border-t border-gray-200">
                                    <div v-if="visite.nombre_cadres_total" class="text-center p-2 bg-gray-50 rounded">
                                        <div class="font-medium">{{ visite.nombre_cadres_total }}</div>
                                        <div class="text-xs text-gray-600">Total cadres</div>
                                    </div>
                                    <div v-if="visite.nombre_cadres_occupes" class="text-center p-2 bg-blue-50 rounded">
                                        <div class="font-medium">{{ visite.nombre_cadres_occupes }}</div>
                                        <div class="text-xs text-gray-600">Occupés</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Prochaine visite -->
                        <div v-if="visite.prochaine_visite_prevue" 
                             class="bg-gradient-to-r from-green-50 to-blue-50 border border-green-200 rounded-xl p-5">
                            <h4 class="font-semibold text-green-900 mb-2 flex items-center gap-2">
                                <Calendar class="w-5 h-5" />
                                Prochaine visite prévue
                            </h4>
                            <p class="text-green-800 font-medium">{{ formatDateLong(visite.prochaine_visite_prevue) }}</p>
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
import { computed } from 'vue';
import {
    Calendar,
    Sun,
    Activity,
    Crown,
    Egg,
    Wrench,
    FileText,
    Archive,
    Package,
    Shield,
    Edit,
    Eye,
    CheckCircle,
    AlertTriangle,
    TrendingUp
} from 'lucide-vue-next';

// Composants utilitaires
const SimpleObservation = {
    props: ['label', 'value', 'help'],
    template: `
        <div>
            <div class="text-xs text-gray-600 mb-1">
                {{ label }}
                <span v-if="help" class="text-gray-400 ml-1">({{ help }})</span>
            </div>
            <div class="font-medium text-sm" :class="getStatusColor(value)">
                {{ getSimpleStatus(value) }}
            </div>
        </div>
    `
};

const HealthIndicator = {
    props: ['icon', 'label', 'value', 'inverted'],
    template: `
        <div class="flex items-center justify-between p-3 rounded-lg" :class="getHealthStyle(value, inverted)">
            <div class="flex items-center gap-2">
                <span class="text-lg">{{ icon }}</span>
                <span class="text-sm font-medium">{{ label }}</span>
            </div>
            <span class="text-sm font-medium">{{ getHealthLabel(value, inverted) }}</span>
        </div>
    `
};

// Props
const props = defineProps({
    rucher: Object,
    ruche: Object,
    visite: Object
});

// Computed
const hasMeteoData = computed(() => {
    const { meteo, temperature, humidite, force_vent } = props.visite;
    return meteo || temperature || humidite || force_vent;
});

const hasActions = computed(() => {
    const { nourrissement_effectue, traitement_applique, hausse_ajoutee, hausse_retiree } = props.visite;
    return nourrissement_effectue || traitement_applique || hausse_ajoutee || hausse_retiree;
});

// Helpers pour simplifier l'affichage
function getSimpleStatus(value) {
    if (!value || value === 'non_observe') return 'Non regardé';
    if (value === 'ok') return 'Bon';
    if (value === 'non_ok') return 'Problème';
    if (value === 'normale') return 'Normal';
    if (value === 'calme') return 'Calme';
    if (value === 'agitee') return 'Agitée';
    if (value === 'agressive') return 'Agressive';
    if (value === 'faible') return 'Faible';
    if (value === 'forte') return 'Forte';
    if (value === 'intense') return 'Intense';
    if (value === 'importante') return 'Important';
    return value;
}

function getStatusColor(value) {
    if (!value || value === 'non_observe') return 'text-gray-500';
    if (value === 'ok' || value === 'normale' || value === 'calme') return 'text-green-600';
    if (value === 'non_ok' || value === 'agressive') return 'text-red-600';
    if (value === 'agitee' || value === 'faible') return 'text-orange-600';
    if (value === 'forte' || value === 'intense' || value === 'importante') return 'text-blue-600';
    return 'text-gray-600';
}

function getPopulationLabel(value) {
    if (value === 'normale') return 'Normale';
    if (value === 'faible') return 'Faible';
    if (value === 'forte') return 'Forte';
    return 'Non observé';
}

function getPopulationColor(value) {
    if (value === 'normale') return 'text-green-600';
    if (value === 'faible') return 'text-orange-600';
    if (value === 'forte') return 'text-blue-600';
    return 'text-gray-500';
}

function getHumeurLabel(value) {
    if (value === 'calme') return 'Calme';
    if (value === 'agitee') return 'Agitée';
    if (value === 'agressive') return 'Agressive';
    return 'Non observé';
}

function getHumeurColor(value) {
    if (value === 'calme') return 'text-green-600';
    if (value === 'agitee') return 'text-orange-600';
    if (value === 'agressive') return 'text-red-600';
    return 'text-gray-500';
}

function getEtatGeneral() {
    // Logique pour déterminer l'état général basé sur les observations
    const problemes = hasProblems();
    const bonnesObservations = (props.visite.reine_vue === 'ok' && props.visite.ponte_observee === 'ok');
    
    if (problemes) return 'État préoccupant';
    if (bonnesObservations) return 'Bonne santé';
    if (props.visite.humeur_colonie === 'calme') return 'Colonie calme';
    return 'État à surveiller';
}

function getEtatGeneralEmoji() {
    const etat = getEtatGeneral();
    if (etat === 'Bonne santé') return '😊';
    if (etat === 'Colonie calme') return '😌';
    if (etat === 'État préoccupant') return '😟';
    return '😐';
}

function getEtatGeneralStyle() {
    const etat = getEtatGeneral();
    if (etat === 'Bonne santé') return 'bg-green-100 text-green-800 border border-green-200';
    if (etat === 'Colonie calme') return 'bg-blue-100 text-blue-800 border border-blue-200';
    if (etat === 'État préoccupant') return 'bg-red-100 text-red-800 border border-red-200';
    return 'bg-yellow-100 text-yellow-800 border border-yellow-200';
}

function hasProblems() {
    const { varroas_observes, mortalite, fausse_teigne, cellules_royales, reserves_miel } = props.visite;
    return varroas_observes === 'non_ok' || 
           mortalite === 'non_ok' || 
           fausse_teigne === 'non_ok' ||
           cellules_royales === 'ok' ||
           reserves_miel === 'insuffisantes';
}

function getMeteoEmoji(meteo) {
    const emojis = {
        'ensoleille': '☀️',
        'nuageux': '☁️', 
        'pluvieux': '🌧️',
        'orageux': '⛈️'
    };
    return emojis[meteo] || '🌤️';
}

function getMeteoLabel(meteo) {
    const labels = {
        'ensoleille': 'Ensoleillé',
        'nuageux': 'Nuageux',
        'pluvieux': 'Pluvieux', 
        'orageux': 'Orageux'
    };
    return labels[meteo] || meteo;
}

function getReserveLabel(value) {
    if (value === 'suffisantes') return 'Suffisantes';
    if (value === 'insuffisantes') return 'Faibles';
    return 'Non vérifié';
}

function getReserveStyle(value) {
    if (value === 'suffisantes') return 'bg-green-50 border border-green-200';
    if (value === 'insuffisantes') return 'bg-red-50 border border-red-200';
    return 'bg-gray-50 border border-gray-200';
}

function getHealthLabel(value, inverted = false) {
    if (!value || value === 'non_observe') return 'Non vérifié';
    
    if (inverted) {
        // Pour fausse teigne: ok = absente (bien), non_ok = présente (mal)
        if (value === 'ok') return 'Absente';
        if (value === 'non_ok') return 'Présente';
    } else {
        // Pour varroas/mortalité: ok = normal (bien), non_ok = problème (mal)
        if (value === 'ok') return 'Normal';
        if (value === 'non_ok') return 'Problème';
    }
    
    return value;
}

function getHealthStyle(value, inverted = false) {
    if (!value || value === 'non_observe') return 'bg-gray-50 border border-gray-200';
    
    const isGood = inverted ? (value === 'ok') : (value === 'ok');
    const isBad = inverted ? (value === 'non_ok') : (value === 'non_ok');
    
    if (isGood) return 'bg-green-50 border border-green-200';
    if (isBad) return 'bg-red-50 border border-red-200';
    
    return 'bg-gray-50 border border-gray-200';
}

// Helpers de formatage de dates
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('fr-FR', {
        day: 'numeric',
        month: 'short',
        year: 'numeric'
    });
}

function formatDateLong(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('fr-FR', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    });
}

// Confirmation de suppression
function confirmDelete() {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette visite ? Cette action est irréversible.')) {
        router.delete(route('ruchers.ruches.visites.destroy', [props.rucher.id, props.ruche.id, props.visite.id]));
    }
}
</script>