<template>
    <AppLayout :title="`Modifier la visite - ${ruche.nom}`">
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
                        <Link :href="route('ruchers.ruches.visites.show', [rucher.id, ruche.id, visite.id])"
                            class="hover:text-gray-900">{{ formatDate(visite.date_visite) }}</Link>
                        <span>›</span>
                        <span class="text-gray-900 font-medium">Modifier</span>
                    </div>
                </nav>

                <!-- Titre -->
                <h2 class="text-lg sm:text-xl font-medium text-gray-900">
                    Modifier la visite du {{ formatDate(visite.date_visite) }} - {{ ruche.nom }}
                </h2>
            </div>
        </template>

        <div class="py-6 sm:py-8 lg:py-12">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

                <form @submit.prevent="submit">
                    <div class="bg-white border border-gray-200 rounded-sm">

                        <!-- En-tête du formulaire -->
                        <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
                            <div class="flex items-center gap-3">
                                <div v-if="ruche.couleur" :style="{ backgroundColor: ruche.couleur }"
                                    class="w-4 h-4 rounded-full border border-gray-300">
                                </div>
                                <h3 class="text-base font-medium text-gray-900">Modification de la visite</h3>
                            </div>
                        </div>

                        <!-- Formulaire par sections -->
                        <div class="p-4 sm:p-6 space-y-6">

                            <!-- SECTION : Informations générales -->
                            <div class="space-y-4">
                                <h4 class="text-sm font-medium text-gray-900 flex items-center gap-2">
                                    <Calendar class="w-4 h-4" />
                                    Informations générales
                                </h4>

                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                    <!-- Date -->
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Date de visite
                                            *</label>
                                        <input type="date" v-model="form.date_visite"
                                            class="w-full text-sm border border-gray-300 rounded-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-gray-900 focus:border-gray-900"
                                            required />
                                        <div v-if="form.errors.date_visite" class="text-red-600 text-xs mt-1">
                                            {{ form.errors.date_visite }}
                                        </div>
                                    </div>

                                    <!-- Heure début -->
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Heure début</label>
                                        <input type="time" v-model="form.heure_debut"
                                            class="w-full text-sm border border-gray-300 rounded-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-gray-900 focus:border-gray-900" />
                                    </div>

                                    <!-- Heure fin -->
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Heure fin</label>
                                        <input type="time" v-model="form.heure_fin"
                                            class="w-full text-sm border border-gray-300 rounded-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-gray-900 focus:border-gray-900" />
                                    </div>
                                </div>
                            </div>

                            <!-- SECTION : Météo -->
                            <div class="border border-gray-200 rounded-sm">
                                <button type="button" @click="toggleSection('meteo')"
                                    class="w-full flex items-center justify-between px-4 py-3 text-left hover:bg-gray-50">
                                    <span class="text-sm font-medium text-gray-900 flex items-center gap-2">
                                        <Sun class="w-4 h-4" />
                                        Météo & Conditions
                                    </span>
                                    <ChevronDown :class="{ 'rotate-180': sections.meteo }"
                                        class="w-4 h-4 transition-transform" />
                                </button>

                                <div v-show="sections.meteo" class="px-4 pb-4 border-t border-gray-200">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mt-4">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Météo</label>
                                            <select v-model="form.meteo"
                                                class="w-full text-sm border border-gray-300 rounded-sm px-3 py-2">
                                                <option value="">Non précisée</option>
                                                <option value="ensoleille">Ensoleillé</option>
                                                <option value="nuageux">Nuageux</option>
                                                <option value="pluvieux">Pluvieux</option>
                                                <option value="orageux">Orageux</option>
                                            </select>
                                        </div>

                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Température
                                                (°C)</label>
                                            <input type="number" v-model="form.temperature" step="0.1" min="-50"
                                                max="50"
                                                class="w-full text-sm border border-gray-300 rounded-sm px-3 py-2"
                                                placeholder="Ex: 22.5" />
                                        </div>

                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Humidité
                                                (%)</label>
                                            <input type="number" v-model="form.humidite" min="0" max="100"
                                                class="w-full text-sm border border-gray-300 rounded-sm px-3 py-2"
                                                placeholder="Ex: 65" />
                                        </div>

                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Force du
                                                vent</label>
                                            <select v-model="form.force_vent"
                                                class="w-full text-sm border border-gray-300 rounded-sm px-3 py-2">
                                                <option value="">Non précisée</option>
                                                <option value="nul">Nul</option>
                                                <option value="faible">Faible</option>
                                                <option value="modere">Modéré</option>
                                                <option value="fort">Fort</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- SECTION : État de la colonie -->
                            <div class="border border-gray-200 rounded-sm">
                                <button type="button" @click="toggleSection('colonie')"
                                    class="w-full flex items-center justify-between px-4 py-3 text-left hover:bg-gray-50">
                                    <span class="text-sm font-medium text-gray-900 flex items-center gap-2">
                                        <Activity class="w-4 h-4" />
                                        État de la colonie
                                    </span>
                                    <ChevronDown :class="{ 'rotate-180': sections.colonie }"
                                        class="w-4 h-4 transition-transform" />
                                </button>

                                <div v-show="sections.colonie" class="px-4 pb-4 border-t border-gray-200">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mt-4">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Humeur *</label>
                                            <select v-model="form.humeur_colonie"
                                                class="w-full text-sm border border-gray-300 rounded-sm px-3 py-2">
                                                <option value="non_observe">Non observé</option>
                                                <option value="calme">Calme</option>
                                                <option value="agitee">Agitée</option>
                                                <option value="agressive">Agressive</option>
                                                <option value="tres_agressive">Très agressive</option>
                                            </select>
                                        </div>

                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Activité entrée
                                                *</label>
                                            <select v-model="form.activite_entree"
                                                class="w-full text-sm border border-gray-300 rounded-sm px-3 py-2">
                                                <option value="non_observe">Non observé</option>
                                                <option value="normale">Normale</option>
                                                <option value="faible">Faible</option>
                                                <option value="intense">Intense</option>
                                            </select>
                                        </div>

                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Population
                                                *</label>
                                            <select v-model="form.estimation_population"
                                                class="w-full text-sm border border-gray-300 rounded-sm px-3 py-2">
                                                <option value="non_observe">Non observé</option>
                                                <option value="normale">Normale</option>
                                                <option value="faible">Faible</option>
                                                <option value="forte">Forte</option>
                                            </select>
                                        </div>

                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Bruit *</label>
                                            <select v-model="form.bruit_colonie"
                                                class="w-full text-sm border border-gray-300 rounded-sm px-3 py-2">
                                                <option value="non_observe">Non observé</option>
                                                <option value="normal">Normal</option>
                                                <option value="anormal">Anormal</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- SECTION : Observations de la reine -->
                            <div class="border border-gray-200 rounded-sm">
                                <button type="button" @click="toggleSection('reine')"
                                    class="w-full flex items-center justify-between px-4 py-3 text-left hover:bg-gray-50">
                                    <span class="text-sm font-medium text-gray-900 flex items-center gap-2">
                                        <Crown class="w-4 h-4" />
                                        Observations de la reine
                                    </span>
                                    <ChevronDown :class="{ 'rotate-180': sections.reine }"
                                        class="w-4 h-4 transition-transform" />
                                </button>

                                <div v-show="sections.reine" class="px-4 pb-4 border-t border-gray-200">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mt-4">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Reine vue
                                                *</label>
                                            <select v-model="form.reine_vue"
                                                class="w-full text-sm border border-gray-300 rounded-sm px-3 py-2">
                                                <option value="non_observe">Non observé</option>
                                                <option value="ok">Oui</option>
                                                <option value="non_ok">Non</option>
                                            </select>
                                        </div>

                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Reine marquée
                                                *</label>
                                            <select v-model="form.reine_marquee"
                                                class="w-full text-sm border border-gray-300 rounded-sm px-3 py-2">
                                                <option value="non_observe">Non observé</option>
                                                <option value="ok">Oui</option>
                                                <option value="non_ok">Non</option>
                                            </select>
                                        </div>

                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Ponte observée
                                                *</label>
                                            <select v-model="form.ponte_observee"
                                                class="w-full text-sm border border-gray-300 rounded-sm px-3 py-2">
                                                <option value="non_observe">Non observé</option>
                                                <option value="ok">Oui</option>
                                                <option value="non_ok">Non</option>
                                            </select>
                                        </div>

                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Qualité ponte
                                                *</label>
                                            <select v-model="form.qualite_ponte"
                                                class="w-full text-sm border border-gray-300 rounded-sm px-3 py-2">
                                                <option value="non_observe">Non observé</option>
                                                <option value="ok">Bonne</option>
                                                <option value="non_ok">Mauvaise</option>
                                            </select>
                                        </div>

                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Cellules royales
                                                *</label>
                                            <select v-model="form.cellules_royales"
                                                class="w-full text-sm border border-gray-300 rounded-sm px-3 py-2">
                                                <option value="non_observe">Non observé</option>
                                                <option value="ok">Présentes</option>
                                                <option value="non_ok">Absentes</option>
                                            </select>
                                        </div>

                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Nombre cellules
                                                royales</label>
                                            <input type="number" v-model="form.nombre_cellules_royales" min="0" max="20"
                                                class="w-full text-sm border border-gray-300 rounded-sm px-3 py-2" />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- SECTION : Couvain -->
                            <div class="border border-gray-200 rounded-sm">
                                <button type="button" @click="toggleSection('couvain')"
                                    class="w-full flex items-center justify-between px-4 py-3 text-left hover:bg-gray-50">
                                    <span class="text-sm font-medium text-gray-900 flex items-center gap-2">
                                        <Baby class="w-4 h-4" />
                                        Couvain
                                    </span>
                                    <ChevronDown :class="{ 'rotate-180': sections.couvain }"
                                        class="w-4 h-4 transition-transform" />
                                </button>

                                <div v-show="sections.couvain" class="px-4 pb-4 border-t border-gray-200">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mt-4">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Œufs vus
                                                *</label>
                                            <select v-model="form.oeufs_vus"
                                                class="w-full text-sm border border-gray-300 rounded-sm px-3 py-2">
                                                <option value="non_observe">Non observé</option>
                                                <option value="ok">Oui</option>
                                                <option value="non_ok">Non</option>
                                            </select>
                                        </div>

                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Larves vues
                                                *</label>
                                            <select v-model="form.larves_vues"
                                                class="w-full text-sm border border-gray-300 rounded-sm px-3 py-2">
                                                <option value="non_observe">Non observé</option>
                                                <option value="ok">Oui</option>
                                                <option value="non_ok">Non</option>
                                            </select>
                                        </div>

                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Couvain operculé
                                                *</label>
                                            <select v-model="form.couvain_opercule"
                                                class="w-full text-sm border border-gray-300 rounded-sm px-3 py-2">
                                                <option value="non_observe">Non observé</option>
                                                <option value="ok">Oui</option>
                                                <option value="non_ok">Non</option>
                                            </select>
                                        </div>

                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Surface couvain
                                                *</label>
                                            <select v-model="form.surface_couvain"
                                                class="w-full text-sm border border-gray-300 rounded-sm px-3 py-2">
                                                <option value="non_observe">Non observé</option>
                                                <option value="normale">Normale</option>
                                                <option value="faible">Faible</option>
                                                <option value="importante">Importante</option>
                                            </select>
                                        </div>

                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Régularité
                                                couvain
                                                *</label>
                                            <select v-model="form.regularite_couvain"
                                                class="w-full text-sm border border-gray-300 rounded-sm px-3 py-2">
                                                <option value="non_observe">Non observé</option>
                                                <option value="ok">Régulier</option>
                                                <option value="non_ok">Irrégulier</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- SECTION : Réserves -->
                            <div class="border border-gray-200 rounded-sm">
                                <button type="button" @click="toggleSection('reserves')"
                                    class="w-full flex items-center justify-between px-4 py-3 text-left hover:bg-gray-50">
                                    <span class="text-sm font-medium text-gray-900 flex items-center gap-2">
                                        <Container class="w-4 h-4" />
                                        Réserves
                                    </span>
                                    <ChevronDown :class="{ 'rotate-180': sections.reserves }"
                                        class="w-4 h-4 transition-transform" />
                                </button>

                                <div v-show="sections.reserves" class="px-4 pb-4 border-t border-gray-200">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mt-4">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Réserves miel
                                                *</label>
                                            <select v-model="form.reserves_miel"
                                                class="w-full text-sm border border-gray-300 rounded-sm px-3 py-2">
                                                <option value="non_observe">Non observé</option>
                                                <option value="suffisantes">Suffisantes</option>
                                                <option value="insuffisantes">Insuffisantes</option>
                                            </select>
                                        </div>

                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Réserves pollen
                                                *</label>
                                            <select v-model="form.reserves_pollen"
                                                class="w-full text-sm border border-gray-300 rounded-sm px-3 py-2">
                                                <option value="non_observe">Non observé</option>
                                                <option value="suffisantes">Suffisantes</option>
                                                <option value="insuffisantes">Insuffisantes</option>
                                            </select>
                                        </div>

                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Cadres miel
                                                operculé</label>
                                            <input type="number" v-model="form.cadres_miel_opercule" min="0" max="20"
                                                class="w-full text-sm border border-gray-300 rounded-sm px-3 py-2" />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- SECTION : Santé & parasites -->
                            <div class="border border-gray-200 rounded-sm">
                                <button type="button" @click="toggleSection('sante')"
                                    class="w-full flex items-center justify-between px-4 py-3 text-left hover:bg-gray-50">
                                    <span class="text-sm font-medium text-gray-900 flex items-center gap-2">
                                        <Shield class="w-4 h-4" />
                                        Santé & Parasites
                                    </span>
                                    <ChevronDown :class="{ 'rotate-180': sections.sante }"
                                        class="w-4 h-4 transition-transform" />
                                </button>

                                <div v-show="sections.sante" class="px-4 pb-4 border-t border-gray-200">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mt-4">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Varroas observés
                                                *</label>
                                            <select v-model="form.varroas_observes"
                                                class="w-full text-sm border border-gray-300 rounded-sm px-3 py-2">
                                                <option value="non_observe">Non observé</option>
                                                <option value="ok">Peu/Pas de varroas</option>
                                                <option value="non_ok">Nombreux varroas</option>
                                            </select>
                                        </div>

                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Nombre varroas
                                                estimé</label>
                                            <input type="number" v-model="form.nombre_varroas_estimes" min="0"
                                                class="w-full text-sm border border-gray-300 rounded-sm px-3 py-2" />
                                        </div>

                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Fausse teigne
                                                *</label>
                                            <select v-model="form.fausse_teigne"
                                                class="w-full text-sm border border-gray-300 rounded-sm px-3 py-2">
                                                <option value="non_observe">Non observé</option>
                                                <option value="ok">Pas de fausse teigne</option>
                                                <option value="non_ok">Fausse teigne présente</option>
                                            </select>
                                        </div>

                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Mortalité
                                                *</label>
                                            <select v-model="form.mortalite"
                                                class="w-full text-sm border border-gray-300 rounded-sm px-3 py-2">
                                                <option value="non_observe">Non observé</option>
                                                <option value="ok">Mortalité normale</option>
                                                <option value="non_ok">Mortalité importante</option>
                                            </select>
                                        </div>

                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Abeilles
                                                mortes</label>
                                            <select v-model="form.abeilles_mortes_quantite"
                                                class="w-full text-sm border border-gray-300 rounded-sm px-3 py-2">
                                                <option value="">Non précisé</option>
                                                <option value="quelques_unes">Quelques-unes</option>
                                                <option value="beaucoup">Beaucoup</option>
                                                <option value="enorme">Énorme quantité</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- SECTION : Structure de la ruche -->
                            <div class="border border-gray-200 rounded-sm">
                                <button type="button" @click="toggleSection('structure')"
                                    class="w-full flex items-center justify-between px-4 py-3 text-left hover:bg-gray-50">
                                    <span class="text-sm font-medium text-gray-900 flex items-center gap-2">
                                        <Home class="w-4 h-4" />
                                        Structure de la ruche
                                    </span>
                                    <ChevronDown :class="{ 'rotate-180': sections.structure }"
                                        class="w-4 h-4 transition-transform" />
                                </button>

                                <div v-show="sections.structure" class="px-4 pb-4 border-t border-gray-200">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mt-4">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">État des cadres
                                                *</label>
                                            <select v-model="form.cadres_etat"
                                                class="w-full text-sm border border-gray-300 rounded-sm px-3 py-2">
                                                <option value="non_observe">Non observé</option>
                                                <option value="ok">Bon état</option>
                                                <option value="non_ok">Mauvais état</option>
                                            </select>
                                        </div>

                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Nombre cadres
                                                total</label>
                                            <input type="number" v-model="form.nombre_cadres_total" min="1" max="20"
                                                class="w-full text-sm border border-gray-300 rounded-sm px-3 py-2" />
                                        </div>

                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Cadres
                                                occupés</label>
                                            <input type="number" v-model="form.nombre_cadres_occupes" min="0" max="20"
                                                class="w-full text-sm border border-gray-300 rounded-sm px-3 py-2" />
                                        </div>

                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Cire construite
                                                *</label>
                                            <select v-model="form.cire_construite"
                                                class="w-full text-sm border border-gray-300 rounded-sm px-3 py-2">
                                                <option value="non_observe">Non observé</option>
                                                <option value="ok">Bien construite</option>
                                                <option value="non_ok">Mal construite</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- SECTION : Actions réalisées -->
                            <div class="border border-gray-200 rounded-sm">
                                <button type="button" @click="toggleSection('actions')"
                                    class="w-full flex items-center justify-between px-4 py-3 text-left hover:bg-gray-50">
                                    <span class="text-sm font-medium text-gray-900 flex items-center gap-2">
                                        <Wrench class="w-4 h-4" />
                                        Actions réalisées
                                    </span>
                                    <ChevronDown :class="{ 'rotate-180': sections.actions }"
                                        class="w-4 h-4 transition-transform" />
                                </button>

                                <div v-show="sections.actions" class="px-4 pb-4 border-t border-gray-200">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mt-4">
                                        <!-- Nourrissement -->
                                        <div
                                            class="col-span-1 sm:col-span-2 lg:col-span-4 border border-gray-200 rounded-sm p-4">
                                            <div class="flex items-center gap-2 mb-3">
                                                <input type="checkbox" v-model="form.nourrissement_effectue"
                                                    id="nourrissement"
                                                    class="rounded border-gray-300 text-gray-900 focus:ring-gray-900" />
                                                <label for="nourrissement" class="text-sm font-medium text-gray-700">
                                                    Nourrissement effectué
                                                </label>
                                            </div>

                                            <div v-if="form.nourrissement_effectue"
                                                class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-700 mb-1">Type de
                                                        nourrissement</label>
                                                    <select v-model="form.type_nourrissement"
                                                        class="w-full text-sm border border-gray-300 rounded-sm px-3 py-2">
                                                        <option value="">Sélectionner</option>
                                                        <option value="sirop">Sirop</option>
                                                        <option value="candi">Candi</option>
                                                        <option value="pate">Pâte</option>
                                                    </select>
                                                </div>

                                                <div>
                                                    <label class="block text-xs font-medium text-gray-700 mb-1">Quantité
                                                        (kg)</label>
                                                    <input type="number" v-model="form.quantite_nourrissement"
                                                        step="0.1" min="0" max="50"
                                                        class="w-full text-sm border border-gray-300 rounded-sm px-3 py-2" />
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Autres actions -->
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <input type="checkbox" v-model="form.traitement_applique"
                                                    id="traitement"
                                                    class="rounded border-gray-300 text-gray-900 focus:ring-gray-900" />
                                                <label for="traitement" class="text-sm font-medium text-gray-700">
                                                    Traitement appliqué
                                                </label>
                                            </div>
                                        </div>

                                        <div>
                                            <div class="flex items-center gap-2">
                                                <input type="checkbox" v-model="form.hausse_ajoutee" id="hausse_add"
                                                    class="rounded border-gray-300 text-gray-900 focus:ring-gray-900" />
                                                <label for="hausse_add" class="text-sm font-medium text-gray-700">
                                                    Hausse ajoutée
                                                </label>
                                            </div>
                                        </div>

                                        <div>
                                            <div class="flex items-center gap-2">
                                                <input type="checkbox" v-model="form.hausse_retiree" id="hausse_rem"
                                                    class="rounded border-gray-300 text-gray-900 focus:ring-gray-900" />
                                                <label for="hausse_rem" class="text-sm font-medium text-gray-700">
                                                    Hausse retirée
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Notes générales -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Notes générales</label>
                                <textarea v-model="form.notes_generales" rows="3"
                                    class="w-full text-sm border border-gray-300 rounded-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-gray-900 focus:border-gray-900"
                                    placeholder="Observations, remarques particulières..."></textarea>
                                <div v-if="form.errors.notes_generales" class="text-red-600 text-xs mt-1">
                                    {{ form.errors.notes_generales }}
                                </div>
                            </div>

                            <!-- Actions prévues et prochaine visite -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Actions prévues</label>
                                    <textarea v-model="form.actions_prevues" rows="2"
                                        class="w-full text-sm border border-gray-300 rounded-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-gray-900 focus:border-gray-900"
                                        placeholder="Prochaines actions à prévoir..."></textarea>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Prochaine visite
                                        prévue</label>
                                    <input type="date" v-model="form.prochaine_visite_prevue"
                                        class="w-full text-sm border border-gray-300 rounded-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-gray-900 focus:border-gray-900" />
                                </div>
                            </div>

                        </div>

                        <!-- Actions -->
                        <div
                            class="px-4 sm:px-6 py-4 border-t border-gray-200 flex flex-col gap-2 sm:flex-row sm:justify-end">
                            <Link :href="route('ruchers.ruches.visites.show', [rucher.id, ruche.id, visite.id])"
                                class="w-full sm:w-auto px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-sm hover:bg-gray-50 text-center">
                            Annuler
                            </Link>

                            <button type="submit" :disabled="form.processing"
                                class="w-full sm:w-auto px-4 py-2 text-sm font-medium text-white bg-gray-900 border border-transparent rounded-sm hover:bg-gray-800 disabled:opacity-50">
                                <span v-if="form.processing">Enregistrement...</span>
                                <span v-else>Mettre à jour</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';
import {
    Calendar,
    Sun,
    Activity,
    ChevronDown,
    Crown,
    Baby,
    Container,
    Shield,
    Home,
    Wrench
} from 'lucide-vue-next';

// Props
const props = defineProps({
    rucher: Object,
    ruche: Object,
    visite: Object,
    formData: Object  // ← Ajouter ceci
});

// État des sections accordéon
const sections = ref({
    meteo: false,
    colonie: true, // Ouvert par défaut
    reine: false,
    couvain: false,
    reserves: false,
    sante: false,
    structure: false,
    actions: false,
});

// Formulaire Inertia pré-rempli avec TOUS les champs requis

// Formulaire simplifié comme Create
const form = useForm({
    ...props.formData  // ← Utiliser les données préparées
});

// Toggle accordéon
function toggleSection(section) {
    sections.value[section] = !sections.value[section];
}

// Helper pour formater les dates
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('fr-FR', {
        day: 'numeric',
        month: 'short',
        year: 'numeric'
    });
}

// Soumission
function submit() {
    form.put(route('ruchers.ruches.visites.update', [props.rucher.id, props.ruche.id, props.visite.id]));
}
</script>