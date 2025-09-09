<template>
    <AppLayout :title="`Nouvelle visite - ${ruche.nom}`">
        <template #header>
            <div class="space-y-3">
                <!-- Breadcrumbs -->
                <nav class="text-xs text-gray-600 overflow-x-auto">
                    <div class="flex items-center gap-1 whitespace-nowrap">
                        <Link :href="route('ruchers.index')" class="hover:text-gray-900">Ruchers</Link>
                        <span>›</span>
                        <Link :href="route('ruchers.show', rucher.id)" class="hover:text-gray-900">{{ rucher.nom }}</Link>
                        <span>›</span>
                        <Link :href="route('ruchers.ruches.index', rucher.id)" class="hover:text-gray-900">Ruches</Link>
                        <span>›</span>
                        <Link :href="route('ruchers.ruches.show', [rucher.id, ruche.id])" class="hover:text-gray-900">{{ ruche.nom }}</Link>
                        <span>›</span>
                        <Link :href="route('ruchers.ruches.visites.index', [rucher.id, ruche.id])" class="hover:text-gray-900">Visites</Link>
                        <span>›</span>
                        <span class="text-gray-900 font-medium">Nouvelle visite</span>
                    </div>
                </nav>

                <!-- Titre -->
                <h2 class="text-lg sm:text-xl font-medium text-gray-900">
                    Nouvelle visite - {{ ruche.nom }}
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
                                <div v-if="ruche.couleur" 
                                    :style="{ backgroundColor: ruche.couleur }"
                                    class="w-4 h-4 rounded-full border border-gray-300">
                                </div>
                                <h3 class="text-base font-medium text-gray-900">Visite du {{ form.date_visite }}</h3>
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
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Date de visite *</label>
                                        <input 
                                            type="date" 
                                            v-model="form.date_visite"
                                            class="w-full text-sm border border-gray-300 rounded-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-gray-900 focus:border-gray-900"
                                            required
                                        />
                                    </div>

                                    <!-- Heure début -->
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Heure début</label>
                                        <input 
                                            type="time" 
                                            v-model="form.heure_debut"
                                            class="w-full text-sm border border-gray-300 rounded-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-gray-900 focus:border-gray-900"
                                        />
                                    </div>

                                    <!-- Heure fin -->
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Heure fin</label>
                                        <input 
                                            type="time" 
                                            v-model="form.heure_fin"
                                            class="w-full text-sm border border-gray-300 rounded-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-gray-900 focus:border-gray-900"
                                        />
                                    </div>
                                </div>
                            </div>

                            <!-- SECTION : Météo - Accordéon -->
                            <div class="border border-gray-200 rounded-sm">
                                <button 
                                    type="button"
                                    @click="toggleSection('meteo')"
                                    class="w-full flex items-center justify-between px-4 py-3 text-left hover:bg-gray-50"
                                >
                                    <span class="text-sm font-medium text-gray-900 flex items-center gap-2">
                                        <Sun class="w-4 h-4" />
                                        Météo & Conditions
                                    </span>
                                    <ChevronDown :class="{ 'rotate-180': sections.meteo }" class="w-4 h-4 transition-transform" />
                                </button>

                                <div v-show="sections.meteo" class="px-4 pb-4 border-t border-gray-200">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mt-4">
                                        <!-- Météo -->
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Météo</label>
                                            <select v-model="form.meteo" class="w-full text-sm border border-gray-300 rounded-sm px-3 py-2">
                                                <option value="">Non précisée</option>
                                                <option value="ensoleille">Ensoleillé</option>
                                                <option value="nuageux">Nuageux</option>
                                                <option value="pluvieux">Pluvieux</option>
                                                <option value="orageux">Orageux</option>
                                            </select>
                                        </div>

                                        <!-- Température -->
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Température (°C)</label>
                                            <input 
                                                type="number" 
                                                v-model="form.temperature"
                                                step="0.1"
                                                min="-50"
                                                max="50"
                                                class="w-full text-sm border border-gray-300 rounded-sm px-3 py-2"
                                                placeholder="Ex: 22.5"
                                            />
                                        </div>

                                        <!-- Humidité -->
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Humidité (%)</label>
                                            <input 
                                                type="number" 
                                                v-model="form.humidite"
                                                min="0"
                                                max="100"
                                                class="w-full text-sm border border-gray-300 rounded-sm px-3 py-2"
                                                placeholder="Ex: 65"
                                            />
                                        </div>

                                        <!-- Force du vent -->
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Force du vent</label>
                                            <select v-model="form.force_vent" class="w-full text-sm border border-gray-300 rounded-sm px-3 py-2">
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

                            <!-- SECTION : État de la colonie - Accordéon -->
                            <div class="border border-gray-200 rounded-sm">
                                <button 
                                    type="button"
                                    @click="toggleSection('colonie')"
                                    class="w-full flex items-center justify-between px-4 py-3 text-left hover:bg-gray-50"
                                >
                                    <span class="text-sm font-medium text-gray-900 flex items-center gap-2">
                                        <Activity class="w-4 h-4" />
                                        État de la colonie
                                    </span>
                                    <ChevronDown :class="{ 'rotate-180': sections.colonie }" class="w-4 h-4 transition-transform" />
                                </button>

                                <div v-show="sections.colonie" class="px-4 pb-4 border-t border-gray-200">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mt-4">
                                        <!-- Humeur -->
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Humeur</label>
                                            <select v-model="form.humeur_colonie" class="w-full text-sm border border-gray-300 rounded-sm px-3 py-2">
                                                <option value="non_observe">Non observé</option>
                                                <option value="calme">Calme</option>
                                                <option value="agitee">Agitée</option>
                                                <option value="agressive">Agressive</option>
                                                <option value="tres_agressive">Très agressive</option>
                                            </select>
                                        </div>

                                        <!-- Activité entrée -->
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Activité entrée</label>
                                            <select v-model="form.activite_entree" class="w-full text-sm border border-gray-300 rounded-sm px-3 py-2">
                                                <option value="non_observe">Non observé</option>
                                                <option value="normale">Normale</option>
                                                <option value="faible">Faible</option>
                                                <option value="intense">Intense</option>
                                            </select>
                                        </div>

                                        <!-- Population -->
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Population</label>
                                            <select v-model="form.estimation_population" class="w-full text-sm border border-gray-300 rounded-sm px-3 py-2">
                                                <option value="non_observe">Non observé</option>
                                                <option value="normale">Normale</option>
                                                <option value="faible">Faible</option>
                                                <option value="forte">Forte</option>
                                            </select>
                                        </div>

                                        <!-- Bruit -->
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Bruit</label>
                                            <select v-model="form.bruit_colonie" class="w-full text-sm border border-gray-300 rounded-sm px-3 py-2">
                                                <option value="non_observe">Non observé</option>
                                                <option value="normal">Normal</option>
                                                <option value="anormal">Anormal</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- SECTION : Observations de la reine - Accordéon -->
                            <div class="border border-gray-200 rounded-sm">
                                <button 
                                    type="button"
                                    @click="toggleSection('reine')"
                                    class="w-full flex items-center justify-between px-4 py-3 text-left hover:bg-gray-50"
                                >
                                    <span class="text-sm font-medium text-gray-900 flex items-center gap-2">
                                        <Crown class="w-4 h-4" />
                                        Observations de la reine
                                    </span>
                                    <ChevronDown :class="{ 'rotate-180': sections.reine }" class="w-4 h-4 transition-transform" />
                                </button>

                                <div v-show="sections.reine" class="px-4 pb-4 border-t border-gray-200">
                                    <div class="space-y-4 mt-4">
                                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                                            <!-- Reine vue -->
                                            <div>
                                                <label class="block text-xs font-medium text-gray-700 mb-1">Reine vue</label>
                                                <select v-model="form.reine_vue" class="w-full text-sm border border-gray-300 rounded-sm px-3 py-2">
                                                    <option value="non_observe">Non observé</option>
                                                    <option value="ok">Oui</option>
                                                    <option value="non_ok">Non</option>
                                                </select>
                                            </div>

                                            <!-- Reine marquée -->
                                            <div>
                                                <label class="block text-xs font-medium text-gray-700 mb-1">Reine marquée</label>
                                                <select v-model="form.reine_marquee" class="w-full text-sm border border-gray-300 rounded-sm px-3 py-2">
                                                    <option value="non_observe">Non observé</option>
                                                    <option value="ok">Oui</option>
                                                    <option value="non_ok">Non</option>
                                                </select>
                                            </div>

                                            <!-- Ponte observée -->
                                            <div>
                                                <label class="block text-xs font-medium text-gray-700 mb-1">Ponte observée</label>
                                                <select v-model="form.ponte_observee" class="w-full text-sm border border-gray-300 rounded-sm px-3 py-2">
                                                    <option value="non_observe">Non observé</option>
                                                    <option value="ok">Oui</option>
                                                    <option value="non_ok">Non</option>
                                                </select>
                                            </div>

                                            <!-- Qualité ponte -->
                                            <div>
                                                <label class="block text-xs font-medium text-gray-700 mb-1">Qualité ponte</label>
                                                <select v-model="form.qualite_ponte" class="w-full text-sm border border-gray-300 rounded-sm px-3 py-2">
                                                    <option value="non_observe">Non observé</option>
                                                    <option value="ok">Bonne</option>
                                                    <option value="non_ok">Mauvaise</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                            <!-- Cellules royales -->
                                            <div>
                                                <label class="block text-xs font-medium text-gray-700 mb-1">Cellules royales</label>
                                                <select v-model="form.cellules_royales" class="w-full text-sm border border-gray-300 rounded-sm px-3 py-2">
                                                    <option value="non_observe">Non observé</option>
                                                    <option value="ok">Présentes</option>
                                                    <option value="non_ok">Absentes</option>
                                                </select>
                                            </div>

                                            <!-- Nombre cellules royales -->
                                            <div v-if="form.cellules_royales === 'ok'">
                                                <label class="block text-xs font-medium text-gray-700 mb-1">Nombre de cellules royales</label>
                                                <input 
                                                    type="number" 
                                                    v-model="form.nombre_cellules_royales"
                                                    min="0"
                                                    max="20"
                                                    class="w-full text-sm border border-gray-300 rounded-sm px-3 py-2"
                                                    placeholder="Ex: 3"
                                                />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- SECTION : Couvain - Accordéon -->
                            <div class="border border-gray-200 rounded-sm">
                                <button 
                                    type="button"
                                    @click="toggleSection('couvain')"
                                    class="w-full flex items-center justify-between px-4 py-3 text-left hover:bg-gray-50"
                                >
                                    <span class="text-sm font-medium text-gray-900 flex items-center gap-2">
                                        <Egg class="w-4 h-4" />
                                        Couvain
                                    </span>
                                    <ChevronDown :class="{ 'rotate-180': sections.couvain }" class="w-4 h-4 transition-transform" />
                                </button>

                                <div v-show="sections.couvain" class="px-4 pb-4 border-t border-gray-200">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mt-4">
                                        <!-- Œufs vus -->
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Œufs vus</label>
                                            <select v-model="form.oeufs_vus" class="w-full text-sm border border-gray-300 rounded-sm px-3 py-2">
                                                <option value="non_observe">Non observé</option>
                                                <option value="ok">Oui</option>
                                                <option value="non_ok">Non</option>
                                            </select>
                                        </div>

                                        <!-- Larves vues -->
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Larves vues</label>
                                            <select v-model="form.larves_vues" class="w-full text-sm border border-gray-300 rounded-sm px-3 py-2">
                                                <option value="non_observe">Non observé</option>
                                                <option value="ok">Oui</option>
                                                <option value="non_ok">Non</option>
                                            </select>
                                        </div>

                                        <!-- Couvain operculé -->
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Couvain operculé</label>
                                            <select v-model="form.couvain_opercule" class="w-full text-sm border border-gray-300 rounded-sm px-3 py-2">
                                                <option value="non_observe">Non observé</option>
                                                <option value="ok">Oui</option>
                                                <option value="non_ok">Non</option>
                                            </select>
                                        </div>

                                        <!-- Surface couvain -->
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Surface couvain</label>
                                            <select v-model="form.surface_couvain" class="w-full text-sm border border-gray-300 rounded-sm px-3 py-2">
                                                <option value="non_observe">Non observé</option>
                                                <option value="normale">Normale</option>
                                                <option value="faible">Faible</option>
                                                <option value="importante">Importante</option>
                                            </select>
                                        </div>

                                        <!-- Régularité couvain -->
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Régularité</label>
                                            <select v-model="form.regularite_couvain" class="w-full text-sm border border-gray-300 rounded-sm px-3 py-2">
                                                <option value="non_observe">Non observé</option>
                                                <option value="ok">Régulier</option>
                                                <option value="non_ok">Irrégulier</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- SECTION : Réserves - Accordéon -->
                            <div class="border border-gray-200 rounded-sm">
                                <button 
                                    type="button"
                                    @click="toggleSection('reserves')"
                                    class="w-full flex items-center justify-between px-4 py-3 text-left hover:bg-gray-50"
                                >
                                    <span class="text-sm font-medium text-gray-900 flex items-center gap-2">
                                        <Archive class="w-4 h-4" />
                                        Réserves
                                    </span>
                                    <ChevronDown :class="{ 'rotate-180': sections.reserves }" class="w-4 h-4 transition-transform" />
                                </button>

                                <div v-show="sections.reserves" class="px-4 pb-4 border-t border-gray-200">
                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-4">
                                        <!-- Réserves miel -->
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Réserves miel</label>
                                            <select v-model="form.reserves_miel" class="w-full text-sm border border-gray-300 rounded-sm px-3 py-2">
                                                <option value="non_observe">Non observé</option>
                                                <option value="suffisantes">Suffisantes</option>
                                                <option value="insuffisantes">Insuffisantes</option>
                                            </select>
                                        </div>

                                        <!-- Réserves pollen -->
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Réserves pollen</label>
                                            <select v-model="form.reserves_pollen" class="w-full text-sm border border-gray-300 rounded-sm px-3 py-2">
                                                <option value="non_observe">Non observé</option>
                                                <option value="suffisantes">Suffisantes</option>
                                                <option value="insuffisantes">Insuffisantes</option>
                                            </select>
                                        </div>

                                        <!-- Cadres miel operculé -->
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Cadres miel operculé</label>
                                            <input 
                                                type="number" 
                                                v-model="form.cadres_miel_opercule"
                                                min="0"
                                                max="20"
                                                class="w-full text-sm border border-gray-300 rounded-sm px-3 py-2"
                                                placeholder="Ex: 5"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- SECTION : Santé & Parasites - Accordéon -->
                            <div class="border border-gray-200 rounded-sm">
                                <button 
                                    type="button"
                                    @click="toggleSection('sante')"
                                    class="w-full flex items-center justify-between px-4 py-3 text-left hover:bg-gray-50"
                                >
                                    <span class="text-sm font-medium text-gray-900 flex items-center gap-2">
                                        <Shield class="w-4 h-4" />
                                        Santé & Parasites
                                    </span>
                                    <ChevronDown :class="{ 'rotate-180': sections.sante }" class="w-4 h-4 transition-transform" />
                                </button>

                                <div v-show="sections.sante" class="px-4 pb-4 border-t border-gray-200">
                                    <div class="space-y-4 mt-4">
                                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                            <!-- Varroas -->
                                            <div>
                                                <label class="block text-xs font-medium text-gray-700 mb-1">Varroas observés</label>
                                                <select v-model="form.varroas_observes" class="w-full text-sm border border-gray-300 rounded-sm px-3 py-2">
                                                    <option value="non_observe">Non observé</option>
                                                    <option value="ok">Peu/Normal</option>
                                                    <option value="non_ok">Beaucoup</option>
                                                </select>
                                            </div>

                                            <!-- Fausse teigne -->
                                            <div>
                                                <label class="block text-xs font-medium text-gray-700 mb-1">Fausse teigne</label>
                                                <select v-model="form.fausse_teigne" class="w-full text-sm border border-gray-300 rounded-sm px-3 py-2">
                                                    <option value="non_observe">Non observé</option>
                                                    <option value="ok">Absente</option>
                                                    <option value="non_ok">Présente</option>
                                                </select>
                                            </div>

                                            <!-- Mortalité -->
                                            <div>
                                                <label class="block text-xs font-medium text-gray-700 mb-1">Mortalité</label>
                                                <select v-model="form.mortalite" class="w-full text-sm border border-gray-300 rounded-sm px-3 py-2">
                                                    <option value="non_observe">Non observé</option>
                                                    <option value="ok">Normale</option>
                                                    <option value="non_ok">Anormale</option>
                                                </select>
                                            </div>
                                        </div>

                                        <!-- Quantité abeilles mortes (si mortalité anormale) -->
                                        <div v-if="form.mortalite === 'non_ok'" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-xs font-medium text-gray-700 mb-1">Quantité d'abeilles mortes</label>
                                                <select v-model="form.abeilles_mortes_quantite" class="w-full text-sm border border-gray-300 rounded-sm px-3 py-2">
                                                    <option value="">Non précisé</option>
                                                    <option value="quelques_unes">Quelques-unes</option>
                                                    <option value="beaucoup">Beaucoup</option>
                                                    <option value="enorme">Énorme</option>
                                                </select>
                                            </div>

                                            <div>
                                                <label class="block text-xs font-medium text-gray-700 mb-1">Nombre estimé varroas</label>
                                                <input 
                                                    type="number" 
                                                    v-model="form.nombre_varroas_estimes"
                                                    min="0"
                                                    class="w-full text-sm border border-gray-300 rounded-sm px-3 py-2"
                                                    placeholder="Ex: 15"
                                                />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- SECTION : Structure de la ruche - Accordéon -->
                            <div class="border border-gray-200 rounded-sm">
                                <button 
                                    type="button"
                                    @click="toggleSection('structure')"
                                    class="w-full flex items-center justify-between px-4 py-3 text-left hover:bg-gray-50"
                                >
                                    <span class="text-sm font-medium text-gray-900 flex items-center gap-2">
                                        <Package class="w-4 h-4" />
                                        Structure de la ruche
                                    </span>
                                    <ChevronDown :class="{ 'rotate-180': sections.structure }" class="w-4 h-4 transition-transform" />
                                </button>

                                <div v-show="sections.structure" class="px-4 pb-4 border-t border-gray-200">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mt-4">
                                        <!-- État cadres -->
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">État des cadres</label>
                                            <select v-model="form.cadres_etat" class="w-full text-sm border border-gray-300 rounded-sm px-3 py-2">
                                                <option value="non_observe">Non observé</option>
                                                <option value="ok">Bon état</option>
                                                <option value="non_ok">Mauvais état</option>
                                            </select>
                                        </div>

                                        <!-- Nombre cadres total -->
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Cadres total</label>
                                            <input 
                                                type="number" 
                                                v-model="form.nombre_cadres_total"
                                                min="1"
                                                max="20"
                                                class="w-full text-sm border border-gray-300 rounded-sm px-3 py-2"
                                                placeholder="Ex: 10"
                                            />
                                        </div>

                                        <!-- Nombre cadres occupés -->
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Cadres occupés</label>
                                            <input 
                                                type="number" 
                                                v-model="form.nombre_cadres_occupes"
                                                min="0"
                                                max="20"
                                                class="w-full text-sm border border-gray-300 rounded-sm px-3 py-2"
                                                placeholder="Ex: 8"
                                            />
                                        </div>

                                        <!-- Cire construite -->
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Cire construite</label>
                                            <select v-model="form.cire_construite" class="w-full text-sm border border-gray-300 rounded-sm px-3 py-2">
                                                <option value="non_observe">Non observé</option>
                                                <option value="ok">Bonne construction</option>
                                                <option value="non_ok">Construction défaillante</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- SECTION : Actions réalisées - Accordéon -->
                            <div class="border border-gray-200 rounded-sm">
                                <button 
                                    type="button"
                                    @click="toggleSection('actions')"
                                    class="w-full flex items-center justify-between px-4 py-3 text-left hover:bg-gray-50"
                                >
                                    <span class="text-sm font-medium text-gray-900 flex items-center gap-2">
                                        <Wrench class="w-4 h-4" />
                                        Actions réalisées
                                    </span>
                                    <ChevronDown :class="{ 'rotate-180': sections.actions }" class="w-4 h-4 transition-transform" />
                                </button>

                                <div v-show="sections.actions" class="px-4 pb-4 border-t border-gray-200">
                                    <div class="space-y-4 mt-4">
                                        <!-- Checkboxes actions -->
                                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                                            <label class="flex items-center gap-2 cursor-pointer">
                                                <input type="checkbox" v-model="form.nourrissement_effectue" class="rounded border-gray-300">
                                                <span class="text-sm text-gray-900">Nourrissement</span>
                                            </label>

                                            <label class="flex items-center gap-2 cursor-pointer">
                                                <input type="checkbox" v-model="form.traitement_applique" class="rounded border-gray-300">
                                                <span class="text-sm text-gray-900">Traitement appliqué</span>
                                            </label>

                                            <label class="flex items-center gap-2 cursor-pointer">
                                                <input type="checkbox" v-model="form.hausse_ajoutee" class="rounded border-gray-300">
                                                <span class="text-sm text-gray-900">Hausse ajoutée</span>
                                            </label>

                                            <label class="flex items-center gap-2 cursor-pointer">
                                                <input type="checkbox" v-model="form.hausse_retiree" class="rounded border-gray-300">
                                                <span class="text-sm text-gray-900">Hausse retirée</span>
                                            </label>
                                        </div>

                                        <!-- Détails nourrissement -->
                                        <div v-if="form.nourrissement_effectue" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-xs font-medium text-gray-700 mb-1">Type de nourrissement</label>
                                                <select v-model="form.type_nourrissement" class="w-full text-sm border border-gray-300 rounded-sm px-3 py-2">
                                                    <option value="">Sélectionner</option>
                                                    <option value="sirop">Sirop</option>
                                                    <option value="candi">Candi</option>
                                                    <option value="pate">Pâte</option>
                                                </select>
                                            </div>

                                            <div>
                                                <label class="block text-xs font-medium text-gray-700 mb-1">Quantité (kg)</label>
                                                <input 
                                                    type="number" 
                                                    v-model="form.quantite_nourrissement"
                                                    step="0.1"
                                                    min="0"
                                                    max="50"
                                                    class="w-full text-sm border border-gray-300 rounded-sm px-3 py-2"
                                                    placeholder="Ex: 1.5"
                                                />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Notes générales -->
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-2">Notes générales</label>
                                <textarea 
                                    v-model="form.notes_generales"
                                    rows="3"
                                    class="w-full text-sm border border-gray-300 rounded-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-gray-900 focus:border-gray-900"
                                    placeholder="Observations, remarques particulières..."
                                ></textarea>
                            </div>

                            <!-- Actions prévues et prochaine visite -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-2">Actions prévues</label>
                                    <textarea 
                                        v-model="form.actions_prevues"
                                        rows="2"
                                        class="w-full text-sm border border-gray-300 rounded-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-gray-900 focus:border-gray-900"
                                        placeholder="Prochaines actions à prévoir..."
                                    ></textarea>
                                </div>

                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-2">Prochaine visite prévue</label>
                                    <input 
                                        type="date" 
                                        v-model="form.prochaine_visite_prevue"
                                        class="w-full text-sm border border-gray-300 rounded-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-gray-900 focus:border-gray-900"
                                    />
                                </div>
                            </div>

                            <!-- Notes générales -->
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-2">Notes générales</label>
                                <textarea 
                                    v-model="form.notes_generales"
                                    rows="3"
                                    class="w-full text-sm border border-gray-300 rounded-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-gray-900 focus:border-gray-900"
                                    placeholder="Observations, remarques particulières..."
                                ></textarea>
                            </div>

                        </div>

                        <!-- Actions -->
                        <div class="px-4 sm:px-6 py-4 border-t border-gray-200 flex flex-col gap-2 sm:flex-row sm:justify-end">
                            <Link :href="route('ruchers.ruches.visites.index', [rucher.id, ruche.id])"
                                class="w-full sm:w-auto px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-sm hover:bg-gray-50 text-center">
                                Annuler
                            </Link>
                            
                            <button 
                                type="submit" 
                                :disabled="form.processing"
                                class="w-full sm:w-auto px-4 py-2 text-sm font-medium text-white bg-gray-900 border border-transparent rounded-sm hover:bg-gray-800 disabled:opacity-50"
                            >
                                <span v-if="form.processing">Enregistrement...</span>
                                <span v-else>Enregistrer la visite</span>
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
    Egg,
    Archive,
    Shield,
    Package,
    Wrench
} from 'lucide-vue-next';

// Props
const props = defineProps({
    rucher: Object,
    ruche: Object,
    formData: Object
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

// Formulaire Inertia
const form = useForm({
    ...props.formData
});

// Toggle accordéon
function toggleSection(section) {
    sections.value[section] = !sections.value[section];
}

// Soumission
function submit() {
    form.post(route('ruchers.ruches.visites.store', [props.rucher.id, props.ruche.id]));
}
</script>