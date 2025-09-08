<template>
    <AppLayout :title="`Visite du ${formatDate(visite.date_visite)} - ${ruche.nom}`">
        <template #header>
            <div class="space-y-3">
                <!-- Breadcrumbs -->
                <nav class="text-xs text-gray-600 overflow-x-auto">
                    <div class="flex items-center gap-1 whitespace-nowrap">
                        <Link :href="route('ruchers.index')" class="hover:text-gray-900">Ruchers</Link>
                        <span>›</span>
                        <Link :href="route('ruchers.show', rucher.id)" class="hover:text-gray-900">{{ rucher.nom }}
                        </Link>
                        <span>›</span>
                        <Link :href="route('ruchers.ruches.index', rucher.id)" class="hover:text-gray-900">Ruches</Link>
                        <span>›</span>
                        <Link :href="route('ruchers.ruches.show', [rucher.id, ruche.id])" class="hover:text-gray-900">{{
                        ruche.nom }}</Link>
                        <span>›</span>
                        <Link :href="route('ruchers.ruches.visites.index', [rucher.id, ruche.id])"
                            class="hover:text-gray-900">Visites</Link>
                        <span>›</span>
                        <span class="text-gray-900 font-medium">{{ formatDate(visite.date_visite) }}</span>
                    </div>
                </nav>

                <!-- Titre avec actions -->
                <div class="flex flex-col gap-3 sm:flex-row sm:justify-between sm:items-center">
                    <div class="flex items-center gap-3">
                        <!-- Couleur de la ruche -->
                        <div v-if="ruche.couleur" :style="{ backgroundColor: ruche.couleur }"
                            class="w-4 h-4 rounded-full border border-gray-300 flex-shrink-0">
                        </div>

                        <div>
                            <h2 class="text-lg sm:text-xl font-medium text-gray-900">
                                Visite du {{ formatDateLong(visite.date_visite) }}
                            </h2>
                            <p class="text-sm text-gray-600">
                                {{ ruche.nom }} - {{ rucher.nom }}
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-col gap-2 sm:flex-row sm:gap-2">
                        <button @click="confirmDelete"
                            class="flex-1 bg-white hover:bg-gray-50 text-red-600 border border-gray-300 text-sm font-medium py-2 px-3 rounded-sm sm:flex-none">
                            Supprimer
                        </button>
                        <Link :href="route('ruchers.ruches.visites.edit', [rucher.id, ruche.id, visite.id])"
                            class="bg-gray-900 hover:bg-gray-800 text-white text-sm font-medium py-3 px-4 rounded-sm inline-flex items-center justify-center sm:py-2">
                        <Edit class="w-4 h-4 mr-2" />
                        Modifier
                        </Link>

                        <Link :href="route('ruchers.ruches.visites.index', [rucher.id, ruche.id])"
                            class="bg-white hover:bg-gray-50 text-gray-700 border border-gray-300 text-sm font-medium py-3 px-4 rounded-sm text-center sm:py-2">
                        Retour aux visites
                        </Link>
                    </div>
                </div>
            </div>
        </template>

        <div class="py-6 sm:py-8 lg:py-12">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

                <!-- Message de succès -->
                <div v-if="$page.props.flash?.message" class="mb-6">
                    <div class="bg-gray-100 border border-gray-300 text-gray-900 px-4 py-3 rounded-sm text-sm">
                        {{ $page.props.flash.message }}
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                    <!-- Contenu principal -->
                    <div class="lg:col-span-2 space-y-6">

                        <!-- Informations générales -->
                        <div class="bg-white border border-gray-200 rounded-sm p-4 sm:p-6">
                            <h3 class="text-base font-medium text-gray-900 mb-4 flex items-center">
                                <Calendar class="w-4 h-4 mr-2" />
                                Informations générales
                            </h3>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <dt class="text-sm font-medium text-gray-700">Date</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ formatDateLong(visite.date_visite) }}</dd>
                                </div>

                                <div v-if="visite.heure_debut">
                                    <dt class="text-sm font-medium text-gray-700">Horaires</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        {{ visite.heure_debut }}
                                        <span v-if="visite.heure_fin"> - {{ visite.heure_fin }}</span>
                                        <span v-if="visite.duree_visite" class="text-gray-600 ml-2">({{
                                            visite.duree_visite
                                            }}min)</span>
                                    </dd>
                                </div>
                            </div>
                        </div>

                        <!-- Météo -->
                        <div v-if="hasMeteoData" class="bg-white border border-gray-200 rounded-sm p-4 sm:p-6">
                            <h3 class="text-base font-medium text-gray-900 mb-4 flex items-center">
                                <Sun class="w-4 h-4 mr-2" />
                                Conditions météo
                            </h3>

                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                                <div v-if="visite.meteo">
                                    <dt class="text-sm font-medium text-gray-700">Météo</dt>
                                    <dd class="mt-1 flex items-center gap-2">
                                        <Sun v-if="visite.meteo === 'ensoleille'" class="w-4 h-4 text-yellow-500" />
                                        <Cloud v-else-if="visite.meteo === 'nuageux'" class="w-4 h-4 text-gray-500" />
                                        <CloudRain v-else-if="visite.meteo === 'pluvieux'"
                                            class="w-4 h-4 text-blue-500" />
                                        <Zap v-else-if="visite.meteo === 'orageux'" class="w-4 h-4 text-purple-500" />
                                        <span class="text-sm text-gray-900 capitalize">{{ getMeteoLabel(visite.meteo)
                                            }}</span>
                                    </dd>
                                </div>

                                <div v-if="visite.temperature">
                                    <dt class="text-sm font-medium text-gray-700">Température</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ visite.temperature }}°C</dd>
                                </div>

                                <div v-if="visite.humidite">
                                    <dt class="text-sm font-medium text-gray-700">Humidité</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ visite.humidite }}%</dd>
                                </div>

                                <div v-if="visite.force_vent">
                                    <dt class="text-sm font-medium text-gray-700">Vent</dt>
                                    <dd class="mt-1 text-sm text-gray-900 capitalize">{{ visite.force_vent }}</dd>
                                </div>
                            </div>
                        </div>

                        <!-- État de la colonie -->
                        <div class="bg-white border border-gray-200 rounded-sm p-4 sm:p-6">
                            <h3 class="text-base font-medium text-gray-900 mb-4 flex items-center">
                                <Activity class="w-4 h-4 mr-2" />
                                État de la colonie
                            </h3>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <ObservationItem label="Humeur" :value="visite.humeur_colonie" />
                                <ObservationItem label="Activité entrée" :value="visite.activite_entree" />
                                <ObservationItem label="Population" :value="visite.estimation_population" />
                                <ObservationItem label="Bruit" :value="visite.bruit_colonie" />
                            </div>
                        </div>

                        <!-- Observations reine -->
                        <div class="bg-white border border-gray-200 rounded-sm p-4 sm:p-6">
                            <h3 class="text-base font-medium text-gray-900 mb-4 flex items-center">
                                <Crown class="w-4 h-4 mr-2" />
                                Observations reine
                            </h3>

                            <div class="space-y-4">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <ObservationItem label="Reine vue" :value="visite.reine_vue" />
                                    <ObservationItem label="Reine marquée" :value="visite.reine_marquee" />
                                    <ObservationItem label="Ponte observée" :value="visite.ponte_observee" />
                                    <ObservationItem label="Qualité ponte" :value="visite.qualite_ponte" />
                                </div>

                                <div v-if="visite.cellules_royales !== 'non_observe'"
                                    class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-4 border-t border-gray-200">
                                    <ObservationItem label="Cellules royales" :value="visite.cellules_royales" />
                                    <div v-if="visite.nombre_cellules_royales">
                                        <dt class="text-sm font-medium text-gray-700">Nombre</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ visite.nombre_cellules_royales }}</dd>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Couvain -->
                        <div class="bg-white border border-gray-200 rounded-sm p-4 sm:p-6">
                            <h3 class="text-base font-medium text-gray-900 mb-4 flex items-center">
                                <Egg class="w-4 h-4 mr-2" />
                                Couvain
                            </h3>

                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                <ObservationItem label="Œufs" :value="visite.oeufs_vus" />
                                <ObservationItem label="Larves" :value="visite.larves_vues" />
                                <ObservationItem label="Operculé" :value="visite.couvain_opercule" />
                                <ObservationItem label="Surface" :value="visite.surface_couvain" />
                                <ObservationItem label="Régularité" :value="visite.regularite_couvain" />
                            </div>
                        </div>

                        <!-- Actions si présentes -->
                        <div v-if="hasActions" class="bg-white border border-gray-200 rounded-sm p-4 sm:p-6">
                            <h3 class="text-base font-medium text-gray-900 mb-4 flex items-center">
                                <Wrench class="w-4 h-4 mr-2" />
                                Actions réalisées
                            </h3>

                            <div class="space-y-4">
                                <div class="flex flex-wrap gap-2">
                                    <div v-if="visite.nourrissement_effectue"
                                        class="inline-flex items-center gap-2 bg-blue-100 text-blue-800 px-3 py-1 rounded-sm text-sm">
                                        <Droplets class="w-4 h-4" />
                                        <span>Nourrissement</span>
                                        <span v-if="visite.type_nourrissement" class="font-medium">({{
                                            visite.type_nourrissement
                                            }})</span>
                                        <span v-if="visite.quantite_nourrissement" class="font-medium">{{
                                            visite.quantite_nourrissement }}kg</span>
                                    </div>

                                    <div v-if="visite.traitement_applique"
                                        class="inline-flex items-center gap-2 bg-red-100 text-red-800 px-3 py-1 rounded-sm text-sm">
                                        <Shield class="w-4 h-4" />
                                        <span>Traitement appliqué</span>
                                    </div>

                                    <div v-if="visite.hausse_ajoutee"
                                        class="inline-flex items-center gap-2 bg-green-100 text-green-800 px-3 py-1 rounded-sm text-sm">
                                        <Plus class="w-4 h-4" />
                                        <span>Hausse ajoutée</span>
                                    </div>

                                    <div v-if="visite.hausse_retiree"
                                        class="inline-flex items-center gap-2 bg-orange-100 text-orange-800 px-3 py-1 rounded-sm text-sm">
                                        <Minus class="w-4 h-4" />
                                        <span>Hausse retirée</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div v-if="visite.notes_generales || visite.actions_prevues"
                            class="bg-white border border-gray-200 rounded-sm p-4 sm:p-6">
                            <h3 class="text-base font-medium text-gray-900 mb-4 flex items-center">
                                <FileText class="w-4 h-4 mr-2" />
                                Notes
                            </h3>

                            <div class="space-y-4">
                                <div v-if="visite.notes_generales">
                                    <dt class="text-sm font-medium text-gray-700 mb-2">Observations</dt>
                                    <dd class="text-sm text-gray-900 leading-relaxed whitespace-pre-line">{{
                                        visite.notes_generales }}</dd>
                                </div>

                                <div v-if="visite.actions_prevues">
                                    <dt class="text-sm font-medium text-gray-700 mb-2">Actions prévues</dt>
                                    <dd class="text-sm text-gray-900 leading-relaxed whitespace-pre-line">{{
                                        visite.actions_prevues }}</dd>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">
                        <!-- Réserves -->
                        <div class="bg-white border border-gray-200 rounded-sm p-4 sm:p-6">
                            <h3 class="text-base font-medium text-gray-900 mb-4 flex items-center">
                                <Archive class="w-4 h-4 mr-2" />
                                Réserves
                            </h3>

                            <div class="space-y-4">
                                <ObservationItem label="Miel" :value="visite.reserves_miel" />
                                <ObservationItem label="Pollen" :value="visite.reserves_pollen" />

                                <div v-if="visite.cadres_miel_opercule">
                                    <dt class="text-sm font-medium text-gray-700">Cadres operculés</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ visite.cadres_miel_opercule }}</dd>
                                </div>
                            </div>
                        </div>

                        <!-- Structure -->
                        <div class="bg-white border border-gray-200 rounded-sm p-4 sm:p-6">
                            <h3 class="text-base font-medium text-gray-900 mb-4 flex items-center">
                                <Package class="w-4 h-4 mr-2" />
                                Structure
                            </h3>

                            <div class="space-y-4">
                                <ObservationItem label="État cadres" :value="visite.cadres_etat" />
                                <ObservationItem label="Cire" :value="visite.cire_construite" />

                                <div v-if="visite.nombre_cadres_total"
                                    class="grid grid-cols-2 gap-4 pt-4 border-t border-gray-200">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-700">Total</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ visite.nombre_cadres_total }}</dd>
                                    </div>
                                    <div v-if="visite.nombre_cadres_occupes">
                                        <dt class="text-sm font-medium text-gray-700">Occupés</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ visite.nombre_cadres_occupes }}</dd>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Santé -->
                        <div class="bg-white border border-gray-200 rounded-sm p-4 sm:p-6">
                            <h3 class="text-base font-medium text-gray-900 mb-4 flex items-center">
                                <Shield class="w-4 h-4 mr-2" />
                                Santé
                            </h3>

                            <div class="space-y-4">
                                <ObservationItem label="Varroas" :value="visite.varroas_observes" />
                                <ObservationItem label="Fausse teigne" :value="visite.fausse_teigne" />
                                <ObservationItem label="Mortalité" :value="visite.mortalite" />

                                <div v-if="visite.abeilles_mortes_quantite || visite.nombre_varroas_estimes"
                                    class="pt-4 border-t border-gray-200 space-y-2">
                                    <div v-if="visite.abeilles_mortes_quantite">
                                        <dt class="text-sm font-medium text-gray-700">Quantité</dt>
                                        <dd class="mt-1 text-sm text-gray-900 capitalize">{{
                                            visite.abeilles_mortes_quantite.replace('_', ' ') }}</dd>
                                    </div>
                                    <div v-if="visite.nombre_varroas_estimes">
                                        <dt class="text-sm font-medium text-gray-700">Varroas estimés</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ visite.nombre_varroas_estimes }}</dd>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Prochaine visite -->
                        <div v-if="visite.prochaine_visite_prevue"
                            class="bg-blue-50 border border-blue-200 rounded-sm p-4">
                            <h4 class="text-sm font-medium text-blue-900 mb-2 flex items-center">
                                <Calendar class="w-4 h-4 mr-2" />
                                Prochaine visite prévue
                            </h4>
                            <p class="text-sm text-blue-800">{{ formatDateLong(visite.prochaine_visite_prevue) }}</p>
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
    Cloud,
    CloudRain,
    Zap,
    Droplets,
    Plus,
    Minus
} from 'lucide-vue-next';

// Composant pour les observations
import ObservationItem from '@/Components/ObservationItem.vue';

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

// Helpers
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

function getMeteoLabel(meteo) {
    const labels = {
        'ensoleille': 'Ensoleillé',
        'nuageux': 'Nuageux',
        'pluvieux': 'Pluvieux',
        'orageux': 'Orageux'
    };
    return labels[meteo] || meteo;
}

// Confirmation de suppression
function confirmDelete() {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette visite ? Cette action est irréversible.')) {
        router.delete(route('ruchers.ruches.visites.destroy', [props.rucher.id, props.ruche.id, props.visite.id]));
    }
}
</script>