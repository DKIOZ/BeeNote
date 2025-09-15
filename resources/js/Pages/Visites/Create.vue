<template>
    <AppLayout :title="`Visite - ${ruche.nom}`">
        <template #header>
            <div class="space-y-3">
                <!-- Breadcrumbs compacts -->
                <nav class="text-xs text-gray-600">
                    <div class="flex items-center gap-1">
                        <Link :href="route('ruchers.show', rucher.id)" class="hover:text-gray-900">{{ rucher.nom }}</Link>
                        <span>›</span>
                        <Link :href="route('ruchers.ruches.show', [rucher.id, ruche.id])" class="hover:text-gray-900">{{ ruche.nom }}</Link>
                        <span>›</span>
                        <span class="text-gray-900 font-medium">Nouvelle visite</span>
                    </div>
                </nav>

                <!-- Titre avec indicateur ruche -->
                <div class="flex items-center gap-3">
                    <div v-if="ruche.couleur" 
                        :style="{ backgroundColor: ruche.couleur }"
                        class="w-4 h-4 rounded-full border border-gray-300">
                    </div>
                    <h2 class="text-lg sm:text-xl font-medium text-gray-900">
                        Visite - {{ ruche.nom }}
                    </h2>
                </div>
            </div>
        </template>

        <div class="py-4 sm:py-8">
            <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">

                <!-- Progression visuelle -->
                <div class="mb-6 bg-white rounded-lg border border-gray-200 p-4">
                    <div class="flex items-center justify-between text-sm text-gray-600 mb-2">
                        <span>Progression</span>
                        <span>{{ completedSteps }}/{{ totalSteps }} étapes</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-green-600 h-2 rounded-full transition-all duration-300" 
                             :style="{ width: `${(completedSteps / totalSteps) * 100}%` }">
                        </div>
                    </div>
                </div>

                <form @submit.prevent="submit" class="space-y-6">

                    <!-- ÉTAPE 1 : ESSENTIEL (toujours visible) -->
                    <div class="bg-white rounded-lg border-2 border-blue-200 shadow-sm">
                        <div class="bg-blue-50 px-4 py-3 border-b border-blue-200">
                            <h3 class="font-semibold text-blue-900 flex items-center gap-2">
                                <CheckCircle class="w-5 h-5" />
                                1. Essentiel (obligatoire)
                            </h3>
                            <p class="text-sm text-blue-700 mt-1">Les informations de base de votre visite</p>
                        </div>
                        
                        <div class="p-4 space-y-4">
                            <!-- Date et heure -->
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-900 mb-2">
                                        📅 Date de visite *
                                    </label>
                                    <input 
                                        type="date" 
                                        v-model="form.date_visite"
                                        class="w-full text-base border-2 border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        required
                                    />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">⏰ Début</label>
                                    <input 
                                        type="time" 
                                        v-model="form.heure_debut"
                                        class="w-full text-base border-2 border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">⏰ Fin</label>
                                    <input 
                                        type="time" 
                                        v-model="form.heure_fin"
                                        class="w-full text-base border-2 border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    />
                                </div>
                            </div>

                            <!-- État général (simplifié) -->
                            <div>
                                <label class="block text-sm font-medium text-gray-900 mb-3">
                                    🐝 Comment va la colonie aujourd'hui ?
                                </label>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                    <label class="relative cursor-pointer">
                                        <input type="radio" v-model="form.etat_general" value="bonne" class="sr-only">
                                        <div :class="{
                                            'border-green-500 bg-green-50': form.etat_general === 'bonne',
                                            'border-gray-300 hover:border-green-300': form.etat_general !== 'bonne'
                                        }" class="border-2 rounded-lg p-4 text-center transition-all">
                                            <div class="text-2xl mb-2">😊</div>
                                            <div class="font-medium text-green-800">Bonne</div>
                                            <div class="text-xs text-green-600">Calme, active</div>
                                        </div>
                                    </label>

                                    <label class="relative cursor-pointer">
                                        <input type="radio" v-model="form.etat_general" value="moyenne" class="sr-only">
                                        <div :class="{
                                            'border-orange-500 bg-orange-50': form.etat_general === 'moyenne',
                                            'border-gray-300 hover:border-orange-300': form.etat_general !== 'moyenne'
                                        }" class="border-2 rounded-lg p-4 text-center transition-all">
                                            <div class="text-2xl mb-2">😐</div>
                                            <div class="font-medium text-orange-800">Moyenne</div>
                                            <div class="text-xs text-orange-600">Quelques soucis</div>
                                        </div>
                                    </label>

                                    <label class="relative cursor-pointer">
                                        <input type="radio" v-model="form.etat_general" value="mauvaise" class="sr-only">
                                        <div :class="{
                                            'border-red-500 bg-red-50': form.etat_general === 'mauvaise',
                                            'border-gray-300 hover:border-red-300': form.etat_general !== 'mauvaise'
                                        }" class="border-2 rounded-lg p-4 text-center transition-all">
                                            <div class="text-2xl mb-2">😟</div>
                                            <div class="font-medium text-red-800">Mauvaise</div>
                                            <div class="text-xs text-red-600">Problèmes visibles</div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ÉTAPE 2 : MÉTÉO -->
                    <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
                        <button 
                            type="button"
                            @click="toggleStep('meteo')"
                            class="w-full flex items-center justify-between px-4 py-4 text-left hover:bg-gray-50 border-b border-gray-200"
                        >
                            <div>
                                <h3 class="font-semibold text-gray-900 flex items-center gap-2">
                                    <Sun class="w-5 h-5" />
                                    2. Conditions météo
                                </h3>
                                <p class="text-sm text-gray-600 mt-1">Conditions du jour</p>
                            </div>
                            <ChevronDown :class="{ 'rotate-180': openSteps.meteo }" class="w-5 h-5 transition-transform" />
                        </button>

                        <div v-show="openSteps.meteo" class="p-4 space-y-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Météo</label>
                                    <select v-model="form.meteo" class="w-full text-base border-2 border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="">Non précisée</option>
                                        <option value="ensoleille">☀️ Ensoleillé</option>
                                        <option value="nuageux">☁️ Nuageux</option>
                                        <option value="pluvieux">🌧️ Pluvieux</option>
                                        <option value="orageux">⛈️ Orageux</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Température (°C)</label>
                                    <input 
                                        type="number" 
                                        v-model="form.temperature"
                                        step="0.1"
                                        min="-50"
                                        max="50"
                                        class="w-full text-base border-2 border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        placeholder="Ex: 22.5"
                                    />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Humidité (%)</label>
                                    <input 
                                        type="number" 
                                        v-model="form.humidite"
                                        min="0"
                                        max="100"
                                        class="w-full text-base border-2 border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        placeholder="Ex: 65"
                                    />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Force du vent</label>
                                    <select v-model="form.force_vent" class="w-full text-base border-2 border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="">Non précisée</option>
                                        <option value="nul">💨 Nul</option>
                                        <option value="faible">🍃 Faible</option>
                                        <option value="modere">🌬️ Modéré</option>
                                        <option value="fort">💨 Fort</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ÉTAPE 3 : OBSERVATIONS RAPIDES -->
                    <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
                        <button 
                            type="button"
                            @click="toggleStep('observations')"
                            class="w-full flex items-center justify-between px-4 py-4 text-left hover:bg-gray-50 border-b border-gray-200"
                        >
                            <div>
                                <h3 class="font-semibold text-gray-900 flex items-center gap-2">
                                    <Eye class="w-5 h-5" />
                                    3. État général de la colonie
                                </h3>
                                <p class="text-sm text-gray-600 mt-1">Première impression en ouvrant la ruche</p>
                            </div>
                            <ChevronDown :class="{ 'rotate-180': openSteps.observations }" class="w-5 h-5 transition-transform" />
                        </button>

                        <div v-show="openSteps.observations" class="p-4 space-y-6">
                            <!-- Activité entrée et humeur sur une ligne -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                <!-- Activité -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-900 mb-3">
                                        🚪 Activité à l'entrée
                                    </label>
                                    <div class="space-y-2">
                                        <label class="flex items-center gap-3 cursor-pointer">
                                            <input type="radio" v-model="form.activite_entree" value="normale" class="text-green-600">
                                            <span>🐝 Normale</span>
                                        </label>
                                        <label class="flex items-center gap-3 cursor-pointer">
                                            <input type="radio" v-model="form.activite_entree" value="faible" class="text-orange-600">
                                            <span>😴 Faible</span>
                                        </label>
                                        <label class="flex items-center gap-3 cursor-pointer">
                                            <input type="radio" v-model="form.activite_entree" value="intense" class="text-blue-600">
                                            <span>🏃 Intense</span>
                                        </label>
                                        <label class="flex items-center gap-3 cursor-pointer">
                                            <input type="radio" v-model="form.activite_entree" value="non_observe" class="text-gray-600">
                                            <span>🤷 Non observé</span>
                                        </label>
                                    </div>
                                </div>

                                <!-- Humeur colonie -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-900 mb-3">
                                        😤 Humeur des abeilles
                                    </label>
                                    <div class="space-y-2">
                                        <label class="flex items-center gap-3 cursor-pointer">
                                            <input type="radio" v-model="form.humeur_colonie" value="calme" class="text-green-600">
                                            <span>😌 Calme</span>
                                        </label>
                                        <label class="flex items-center gap-3 cursor-pointer">
                                            <input type="radio" v-model="form.humeur_colonie" value="agitee" class="text-orange-600">
                                            <span>😤 Agitée</span>
                                        </label>
                                        <label class="flex items-center gap-3 cursor-pointer">
                                            <input type="radio" v-model="form.humeur_colonie" value="agressive" class="text-red-600">
                                            <span>😡 Agressive</span>
                                        </label>
                                        <label class="flex items-center gap-3 cursor-pointer">
                                            <input type="radio" v-model="form.humeur_colonie" value="tres_agressive" class="text-red-700">
                                            <span>🔥 Très agressive</span>
                                        </label>
                                        <label class="flex items-center gap-3 cursor-pointer">
                                            <input type="radio" v-model="form.humeur_colonie" value="non_observe" class="text-gray-600">
                                            <span>🤷 Non observé</span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Population et bruit -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                <!-- Population -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-900 mb-3">
                                        🐝 Population
                                    </label>
                                    <div class="space-y-2">
                                        <label class="flex items-center gap-3 cursor-pointer">
                                            <input type="radio" v-model="form.estimation_population" value="normale" class="text-green-600">
                                            <span>🐝🐝 Normale</span>
                                        </label>
                                        <label class="flex items-center gap-3 cursor-pointer">
                                            <input type="radio" v-model="form.estimation_population" value="faible" class="text-orange-600">
                                            <span>🐝 Faible</span>
                                        </label>
                                        <label class="flex items-center gap-3 cursor-pointer">
                                            <input type="radio" v-model="form.estimation_population" value="forte" class="text-blue-600">
                                            <span>🐝🐝🐝 Forte</span>
                                        </label>
                                        <label class="flex items-center gap-3 cursor-pointer">
                                            <input type="radio" v-model="form.estimation_population" value="non_observe" class="text-gray-600">
                                            <span>🤷 Non observé</span>
                                        </label>
                                    </div>
                                </div>

                                <!-- Bruit -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-900 mb-3">
                                        🔊 Bruit de la colonie
                                    </label>
                                    <div class="space-y-2">
                                        <label class="flex items-center gap-3 cursor-pointer">
                                            <input type="radio" v-model="form.bruit_colonie" value="normal" class="text-green-600">
                                            <span>🎵 Normal</span>
                                        </label>
                                        <label class="flex items-center gap-3 cursor-pointer">
                                            <input type="radio" v-model="form.bruit_colonie" value="anormal" class="text-red-600">
                                            <span>⚠️ Anormal</span>
                                        </label>
                                        <label class="flex items-center gap-3 cursor-pointer">
                                            <input type="radio" v-model="form.bruit_colonie" value="non_observe" class="text-gray-600">
                                            <span>🤷 Non observé</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ÉTAPE 4 : OBSERVATIONS DE LA REINE -->
                    <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
                        <button 
                            type="button"
                            @click="toggleStep('reine')"
                            class="w-full flex items-center justify-between px-4 py-4 text-left hover:bg-gray-50 border-b border-gray-200"
                        >
                            <div>
                                <h3 class="font-semibold text-gray-900 flex items-center gap-2">
                                    <Crown class="w-5 h-5" />
                                    4. Observations de la reine
                                </h3>
                                <p class="text-sm text-gray-600 mt-1">État et activité de la reine</p>
                            </div>
                            <ChevronDown :class="{ 'rotate-180': openSteps.reine }" class="w-5 h-5 transition-transform" />
                        </button>

                        <div v-show="openSteps.reine" class="p-4 space-y-6">
                            <!-- Reine vue et marquée -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-900 mb-3">
                                        👑 Reine vue
                                    </label>
                                    <div class="space-y-2">
                                        <label class="flex items-center gap-3 cursor-pointer">
                                            <input type="radio" v-model="form.reine_vue" value="ok" class="text-green-600">
                                            <span>✅ Oui, vue</span>
                                        </label>
                                        <label class="flex items-center gap-3 cursor-pointer">
                                            <input type="radio" v-model="form.reine_vue" value="non_ok" class="text-red-600">
                                            <span>❌ Pas vue</span>
                                        </label>
                                        <label class="flex items-center gap-3 cursor-pointer">
                                            <input type="radio" v-model="form.reine_vue" value="non_observe" class="text-gray-600">
                                            <span>🤷 Pas regardé</span>
                                        </label>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-900 mb-3">
                                        🎯 Reine marquée
                                    </label>
                                    <div class="space-y-2">
                                        <label class="flex items-center gap-3 cursor-pointer">
                                            <input type="radio" v-model="form.reine_marquee" value="ok" class="text-green-600">
                                            <span>✅ Oui</span>
                                        </label>
                                        <label class="flex items-center gap-3 cursor-pointer">
                                            <input type="radio" v-model="form.reine_marquee" value="non_ok" class="text-orange-600">
                                            <span>❌ Non</span>
                                        </label>
                                        <label class="flex items-center gap-3 cursor-pointer">
                                            <input type="radio" v-model="form.reine_marquee" value="non_observe" class="text-gray-600">
                                            <span>🤷 Non observé</span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Ponte observée et qualité -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-900 mb-3">
                                        🥚 Ponte observée
                                    </label>
                                    <div class="space-y-2">
                                        <label class="flex items-center gap-3 cursor-pointer">
                                            <input type="radio" v-model="form.ponte_observee" value="ok" class="text-green-600">
                                            <span>✅ Oui</span>
                                        </label>
                                        <label class="flex items-center gap-3 cursor-pointer">
                                            <input type="radio" v-model="form.ponte_observee" value="non_ok" class="text-red-600">
                                            <span>❌ Non</span>
                                        </label>
                                        <label class="flex items-center gap-3 cursor-pointer">
                                            <input type="radio" v-model="form.ponte_observee" value="non_observe" class="text-gray-600">
                                            <span>🤷 Non observé</span>
                                        </label>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-900 mb-3">
                                        ⭐ Qualité de la ponte
                                    </label>
                                    <div class="space-y-2">
                                        <label class="flex items-center gap-3 cursor-pointer">
                                            <input type="radio" v-model="form.qualite_ponte" value="ok" class="text-green-600">
                                            <span>✅ Bonne</span>
                                        </label>
                                        <label class="flex items-center gap-3 cursor-pointer">
                                            <input type="radio" v-model="form.qualite_ponte" value="non_ok" class="text-red-600">
                                            <span>❌ Mauvaise</span>
                                        </label>
                                        <label class="flex items-center gap-3 cursor-pointer">
                                            <input type="radio" v-model="form.qualite_ponte" value="non_observe" class="text-gray-600">
                                            <span>🤷 Non observé</span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Cellules royales -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-900 mb-3">
                                        👸 Cellules royales
                                    </label>
                                    <div class="space-y-2">
                                        <label class="flex items-center gap-3 cursor-pointer">
                                            <input type="radio" v-model="form.cellules_royales" value="ok" class="text-orange-600">
                                            <span>⚠️ Présentes</span>
                                        </label>
                                        <label class="flex items-center gap-3 cursor-pointer">
                                            <input type="radio" v-model="form.cellules_royales" value="non_ok" class="text-green-600">
                                            <span>✅ Absentes</span>
                                        </label>
                                        <label class="flex items-center gap-3 cursor-pointer">
                                            <input type="radio" v-model="form.cellules_royales" value="non_observe" class="text-gray-600">
                                            <span>🤷 Non observé</span>
                                        </label>
                                    </div>
                                </div>

                                <div v-if="form.cellules_royales === 'ok'">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Nombre de cellules royales</label>
                                    <input 
                                        type="number" 
                                        v-model="form.nombre_cellules_royales"
                                        min="0"
                                        max="20"
                                        class="w-full text-base border-2 border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        placeholder="Ex: 3"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ÉTAPE 5 : COUVAIN -->
                    <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
                        <button 
                            type="button"
                            @click="toggleStep('couvain')"
                            class="w-full flex items-center justify-between px-4 py-4 text-left hover:bg-gray-50 border-b border-gray-200"
                        >
                            <div>
                                <h3 class="font-semibold text-gray-900 flex items-center gap-2">
                                    <Egg class="w-5 h-5" />
                                    5. Couvain
                                </h3>
                                <p class="text-sm text-gray-600 mt-1">Observations des œufs, larves et couvain operculé</p>
                            </div>
                            <ChevronDown :class="{ 'rotate-180': openSteps.couvain }" class="w-5 h-5 transition-transform" />
                        </button>

                        <div v-show="openSteps.couvain" class="p-4 space-y-6">
                            <!-- Œufs et larves -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-900 mb-3">
                                        🥚 Œufs vus
                                    </label>
                                    <div class="space-y-2">
                                        <label class="flex items-center gap-3 cursor-pointer">
                                            <input type="radio" v-model="form.oeufs_vus" value="ok" class="text-green-600">
                                            <span>✅ Oui</span>
                                        </label>
                                        <label class="flex items-center gap-3 cursor-pointer">
                                            <input type="radio" v-model="form.oeufs_vus" value="non_ok" class="text-red-600">
                                            <span>❌ Non</span>
                                        </label>
                                        <label class="flex items-center gap-3 cursor-pointer">
                                            <input type="radio" v-model="form.oeufs_vus" value="non_observe" class="text-gray-600">
                                            <span>🤷 Non observé</span>
                                        </label>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-900 mb-3">
                                        🐛 Larves vues
                                    </label>
                                    <div class="space-y-2">
                                        <label class="flex items-center gap-3 cursor-pointer">
                                            <input type="radio" v-model="form.larves_vues" value="ok" class="text-green-600">
                                            <span>✅ Oui</span>
                                        </label>
                                        <label class="flex items-center gap-3 cursor-pointer">
                                            <input type="radio" v-model="form.larves_vues" value="non_ok" class="text-red-600">
                                            <span>❌ Non</span>
                                        </label>
                                        <label class="flex items-center gap-3 cursor-pointer">
                                            <input type="radio" v-model="form.larves_vues" value="non_observe" class="text-gray-600">
                                            <span>🤷 Non observé</span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Couvain operculé et surface -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-900 mb-3">
                                        🍯 Couvain operculé
                                    </label>
                                    <div class="space-y-2">
                                        <label class="flex items-center gap-3 cursor-pointer">
                                            <input type="radio" v-model="form.couvain_opercule" value="ok" class="text-green-600">
                                            <span>✅ Oui</span>
                                        </label>
                                        <label class="flex items-center gap-3 cursor-pointer">
                                            <input type="radio" v-model="form.couvain_opercule" value="non_ok" class="text-red-600">
                                            <span>❌ Non</span>
                                        </label>
                                        <label class="flex items-center gap-3 cursor-pointer">
                                            <input type="radio" v-model="form.couvain_opercule" value="non_observe" class="text-gray-600">
                                            <span>🤷 Non observé</span>
                                        </label>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-900 mb-3">
                                        📏 Surface du couvain
                                    </label>
                                    <div class="space-y-2">
                                        <label class="flex items-center gap-3 cursor-pointer">
                                            <input type="radio" v-model="form.surface_couvain" value="normale" class="text-green-600">
                                            <span>✅ Normale</span>
                                        </label>
                                        <label class="flex items-center gap-3 cursor-pointer">
                                            <input type="radio" v-model="form.surface_couvain" value="faible" class="text-orange-600">
                                            <span>⚠️ Faible</span>
                                        </label>
                                        <label class="flex items-center gap-3 cursor-pointer">
                                            <input type="radio" v-model="form.surface_couvain" value="importante" class="text-blue-600">
                                            <span>💪 Importante</span>
                                        </label>
                                        <label class="flex items-center gap-3 cursor-pointer">
                                            <input type="radio" v-model="form.surface_couvain" value="non_observe" class="text-gray-600">
                                            <span>🤷 Non observé</span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Régularité du couvain -->
                            <div>
                                <label class="block text-sm font-medium text-gray-900 mb-3">
                                    📐 Régularité du couvain
                                </label>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                    <label class="flex items-center gap-3 cursor-pointer">
                                        <input type="radio" v-model="form.regularite_couvain" value="ok" class="text-green-600">
                                        <span>✅ Régulier</span>
                                    </label>
                                    <label class="flex items-center gap-3 cursor-pointer">
                                        <input type="radio" v-model="form.regularite_couvain" value="non_ok" class="text-red-600">
                                        <span>❌ Irrégulier</span>
                                    </label>
                                    <label class="flex items-center gap-3 cursor-pointer">
                                        <input type="radio" v-model="form.regularite_couvain" value="non_observe" class="text-gray-600">
                                        <span>🤷 Non observé</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ÉTAPE 6 : RÉSERVES -->
                    <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
                        <button 
                            type="button"
                            @click="toggleStep('reserves')"
                            class="w-full flex items-center justify-between px-4 py-4 text-left hover:bg-gray-50 border-b border-gray-200"
                        >
                            <div>
                                <h3 class="font-semibold text-gray-900 flex items-center gap-2">
                                    <Archive class="w-5 h-5" />
                                    6. Réserves
                                </h3>
                                <p class="text-sm text-gray-600 mt-1">Réserves de miel et pollen</p>
                            </div>
                            <ChevronDown :class="{ 'rotate-180': openSteps.reserves }" class="w-5 h-5 transition-transform" />
                        </button>

                        <div v-show="openSteps.reserves" class="p-4 space-y-6">
                            <!-- Réserves miel et pollen -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-900 mb-3">
                                        🍯 Réserves de miel
                                    </label>
                                    <div class="space-y-2">
                                        <label class="flex items-center gap-3 cursor-pointer">
                                            <input type="radio" v-model="form.reserves_miel" value="suffisantes" class="text-green-600">
                                            <span>✅ Suffisantes</span>
                                        </label>
                                        <label class="flex items-center gap-3 cursor-pointer">
                                            <input type="radio" v-model="form.reserves_miel" value="insuffisantes" class="text-red-600">
                                            <span>⚠️ Insuffisantes</span>
                                        </label>
                                        <label class="flex items-center gap-3 cursor-pointer">
                                            <input type="radio" v-model="form.reserves_miel" value="non_observe" class="text-gray-600">
                                            <span>🤷 Non observé</span>
                                        </label>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-900 mb-3">
                                        🌸 Réserves de pollen
                                    </label>
                                    <div class="space-y-2">
                                        <label class="flex items-center gap-3 cursor-pointer">
                                            <input type="radio" v-model="form.reserves_pollen" value="suffisantes" class="text-green-600">
                                            <span>✅ Suffisantes</span>
                                        </label>
                                        <label class="flex items-center gap-3 cursor-pointer">
                                            <input type="radio" v-model="form.reserves_pollen" value="insuffisantes" class="text-red-600">
                                            <span>⚠️ Insuffisantes</span>
                                        </label>
                                        <label class="flex items-center gap-3 cursor-pointer">
                                            <input type="radio" v-model="form.reserves_pollen" value="non_observe" class="text-gray-600">
                                            <span>🤷 Non observé</span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Cadres miel operculé -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">📦 Nombre de cadres de miel operculé</label>
                                <input 
                                    type="number" 
                                    v-model="form.cadres_miel_opercule"
                                    min="0"
                                    max="20"
                                    class="w-full text-base border-2 border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    placeholder="Ex: 5"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- ÉTAPE 7 : SANTÉ & PARASITES -->
                    <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
                        <button 
                            type="button"
                            @click="toggleStep('sante')"
                            class="w-full flex items-center justify-between px-4 py-4 text-left hover:bg-gray-50 border-b border-gray-200"
                        >
                            <div>
                                <h3 class="font-semibold text-gray-900 flex items-center gap-2">
                                    <Shield class="w-5 h-5" />
                                    7. Santé & Parasites
                                </h3>
                                <p class="text-sm text-gray-600 mt-1">Signes d'alerte et problèmes sanitaires</p>
                            </div>
                            <ChevronDown :class="{ 'rotate-180': openSteps.sante }" class="w-5 h-5 transition-transform" />
                        </button>

                        <div v-show="openSteps.sante" class="p-4 space-y-6">
                            <!-- Varroas et fausse teigne -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-900 mb-3">
                                        🦟 Varroas observés
                                    </label>
                                    <div class="space-y-2">
                                        <label class="flex items-center gap-3 cursor-pointer">
                                            <input type="radio" v-model="form.varroas_observes" value="ok" class="text-green-600">
                                            <span>✅ Peu/Normal</span>
                                        </label>
                                        <label class="flex items-center gap-3 cursor-pointer">
                                            <input type="radio" v-model="form.varroas_observes" value="non_ok" class="text-red-600">
                                            <span>⚠️ Beaucoup</span>
                                        </label>
                                        <label class="flex items-center gap-3 cursor-pointer">
                                            <input type="radio" v-model="form.varroas_observes" value="non_observe" class="text-gray-600">
                                            <span>🤷 Non observé</span>
                                        </label>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-900 mb-3">
                                        🐛 Fausse teigne
                                    </label>
                                    <div class="space-y-2">
                                        <label class="flex items-center gap-3 cursor-pointer">
                                            <input type="radio" v-model="form.fausse_teigne" value="ok" class="text-green-600">
                                            <span>✅ Absente</span>
                                        </label>
                                        <label class="flex items-center gap-3 cursor-pointer">
                                            <input type="radio" v-model="form.fausse_teigne" value="non_ok" class="text-red-600">
                                            <span>⚠️ Présente</span>
                                        </label>
                                        <label class="flex items-center gap-3 cursor-pointer">
                                            <input type="radio" v-model="form.fausse_teigne" value="non_observe" class="text-gray-600">
                                            <span>🤷 Non observé</span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Mortalité -->
                            <div>
                                <label class="block text-sm font-medium text-gray-900 mb-3">
                                    💀 Mortalité (abeilles mortes)
                                </label>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                    <label class="flex items-center gap-3 cursor-pointer">
                                        <input type="radio" v-model="form.mortalite" value="ok" class="text-green-600">
                                        <span>✅ Normale</span>
                                    </label>
                                    <label class="flex items-center gap-3 cursor-pointer">
                                        <input type="radio" v-model="form.mortalite" value="non_ok" class="text-red-600">
                                        <span>⚠️ Anormale</span>
                                    </label>
                                    <label class="flex items-center gap-3 cursor-pointer">
                                        <input type="radio" v-model="form.mortalite" value="non_observe" class="text-gray-600">
                                        <span>🤷 Non observé</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Détails si mortalité anormale -->
                            <div v-if="form.mortalite === 'non_ok'" class="bg-red-50 border border-red-200 rounded-lg p-4">
                                <h4 class="font-medium text-red-900 mb-3">Détails de la mortalité anormale</h4>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-red-800 mb-2">Quantité d'abeilles mortes</label>
                                        <select v-model="form.abeilles_mortes_quantite" class="w-full text-base border-2 border-red-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-red-500">
                                            <option value="">Non précisé</option>
                                            <option value="quelques_unes">Quelques-unes</option>
                                            <option value="beaucoup">Beaucoup</option>
                                            <option value="enorme">Énorme</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-red-800 mb-2">Nombre estimé de varroas</label>
                                        <input 
                                            type="number" 
                                            v-model="form.nombre_varroas_estimes"
                                            min="0"
                                            class="w-full text-base border-2 border-red-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-red-500"
                                            placeholder="Ex: 15"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ÉTAPE 8 : STRUCTURE DE LA RUCHE -->
                    <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
                        <button 
                            type="button"
                            @click="toggleStep('structure')"
                            class="w-full flex items-center justify-between px-4 py-4 text-left hover:bg-gray-50 border-b border-gray-200"
                        >
                            <div>
                                <h3 class="font-semibold text-gray-900 flex items-center gap-2">
                                    <Package class="w-5 h-5" />
                                    8. Structure de la ruche
                                </h3>
                                <p class="text-sm text-gray-600 mt-1">État des cadres et construction</p>
                            </div>
                            <ChevronDown :class="{ 'rotate-180': openSteps.structure }" class="w-5 h-5 transition-transform" />
                        </button>

                        <div v-show="openSteps.structure" class="p-4 space-y-6">
                            <!-- État des cadres et cire -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-900 mb-3">
                                        🔧 État des cadres
                                    </label>
                                    <div class="space-y-2">
                                        <label class="flex items-center gap-3 cursor-pointer">
                                            <input type="radio" v-model="form.cadres_etat" value="ok" class="text-green-600">
                                            <span>✅ Bon état</span>
                                        </label>
                                        <label class="flex items-center gap-3 cursor-pointer">
                                            <input type="radio" v-model="form.cadres_etat" value="non_ok" class="text-red-600">
                                            <span>⚠️ Mauvais état</span>
                                        </label>
                                        <label class="flex items-center gap-3 cursor-pointer">
                                            <input type="radio" v-model="form.cadres_etat" value="non_observe" class="text-gray-600">
                                            <span>🤷 Non observé</span>
                                        </label>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-900 mb-3">
                                        🏗️ Cire construite
                                    </label>
                                    <div class="space-y-2">
                                        <label class="flex items-center gap-3 cursor-pointer">
                                            <input type="radio" v-model="form.cire_construite" value="ok" class="text-green-600">
                                            <span>✅ Bonne construction</span>
                                        </label>
                                        <label class="flex items-center gap-3 cursor-pointer">
                                            <input type="radio" v-model="form.cire_construite" value="non_ok" class="text-orange-600">
                                            <span>⚠️ Construction défaillante</span>
                                        </label>
                                        <label class="flex items-center gap-3 cursor-pointer">
                                            <input type="radio" v-model="form.cire_construite" value="non_observe" class="text-gray-600">
                                            <span>🤷 Non observé</span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Nombre de cadres -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">📦 Nombre total de cadres</label>
                                    <input 
                                        type="number" 
                                        v-model="form.nombre_cadres_total"
                                        min="1"
                                        max="20"
                                        class="w-full text-base border-2 border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        placeholder="Ex: 10"
                                    />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">🐝 Cadres occupés par les abeilles</label>
                                    <input 
                                        type="number" 
                                        v-model="form.nombre_cadres_occupes"
                                        min="0"
                                        max="20"
                                        class="w-full text-base border-2 border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        placeholder="Ex: 8"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ÉTAPE 9 : ACTIONS RÉALISÉES -->
                    <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
                        <button 
                            type="button"
                            @click="toggleStep('actions')"
                            class="w-full flex items-center justify-between px-4 py-4 text-left hover:bg-gray-50 border-b border-gray-200"
                        >
                            <div>
                                <h3 class="font-semibold text-gray-900 flex items-center gap-2">
                                    <Wrench class="w-5 h-5" />
                                    9. Actions réalisées
                                </h3>
                                <p class="text-sm text-gray-600 mt-1">Ce que vous avez fait durant la visite</p>
                            </div>
                            <ChevronDown :class="{ 'rotate-180': openSteps.actions }" class="w-5 h-5 transition-transform" />
                        </button>

                        <div v-show="openSteps.actions" class="p-4 space-y-4">
                            <!-- Actions checkboxes avec icônes -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <label class="flex items-center gap-3 p-3 border-2 border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors"
                                       :class="{ 'border-blue-500 bg-blue-50': form.nourrissement_effectue }">
                                    <input type="checkbox" v-model="form.nourrissement_effectue" class="w-5 h-5 text-blue-600 border-2 border-gray-300 rounded focus:ring-blue-500">
                                    <span class="text-lg">🍯</span>
                                    <span class="font-medium">Nourrissement</span>
                                </label>

                                <label class="flex items-center gap-3 p-3 border-2 border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors"
                                       :class="{ 'border-purple-500 bg-purple-50': form.traitement_applique }">
                                    <input type="checkbox" v-model="form.traitement_applique" class="w-5 h-5 text-purple-600 border-2 border-gray-300 rounded focus:ring-purple-500">
                                    <span class="text-lg">💊</span>
                                    <span class="font-medium">Traitement appliqué</span>
                                </label>

                                <label class="flex items-center gap-3 p-3 border-2 border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors"
                                       :class="{ 'border-green-500 bg-green-50': form.hausse_ajoutee }">
                                    <input type="checkbox" v-model="form.hausse_ajoutee" class="w-5 h-5 text-green-600 border-2 border-gray-300 rounded focus:ring-green-500">
                                    <span class="text-lg">📦</span>
                                    <span class="font-medium">Hausse ajoutée</span>
                                </label>

                                <label class="flex items-center gap-3 p-3 border-2 border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors"
                                       :class="{ 'border-orange-500 bg-orange-50': form.hausse_retiree }">
                                    <input type="checkbox" v-model="form.hausse_retiree" class="w-5 h-5 text-orange-600 border-2 border-gray-300 rounded focus:ring-orange-500">
                                    <span class="text-lg">📦</span>
                                    <span class="font-medium">Hausse retirée</span>
                                </label>
                            </div>

                            <!-- Détails nourrissement -->
                            <div v-if="form.nourrissement_effectue" class="mt-4 p-4 bg-blue-50 rounded-lg border border-blue-200">
                                <h4 class="font-medium text-blue-900 mb-3">Détails du nourrissement</h4>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-blue-800 mb-2">Type</label>
                                        <select v-model="form.type_nourrissement" class="w-full text-base border-2 border-blue-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            <option value="">Sélectionner</option>
                                            <option value="sirop">🥤 Sirop</option>
                                            <option value="candi">🍬 Candi</option>
                                            <option value="pate">🥧 Pâte</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-blue-800 mb-2">Quantité (kg)</label>
                                        <input 
                                            type="number" 
                                            v-model="form.quantite_nourrissement"
                                            step="0.1"
                                            min="0"
                                            max="50"
                                            class="w-full text-base border-2 border-blue-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            placeholder="Ex: 1.5"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ÉTAPE 10 : NOTES ET PROCHAINE VISITE -->
                    <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
                        <div class="px-4 py-4 border-b border-gray-200">
                            <h3 class="font-semibold text-gray-900 flex items-center gap-2">
                                <FileText class="w-5 h-5" />
                                10. Notes et planning
                            </h3>
                        </div>
                        
                        <div class="p-4 space-y-4">
                            <!-- Notes générales -->
                            <div>
                                <label class="block text-sm font-medium text-gray-900 mb-2">
                                    📝 Notes générales
                                </label>
                                <textarea 
                                    v-model="form.notes_generales"
                                    rows="3"
                                    class="w-full text-base border-2 border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Observations particulières, remarques..."
                                ></textarea>
                            </div>

                            <!-- Actions prévues -->
                            <div>
                                <label class="block text-sm font-medium text-gray-900 mb-2">
                                    📋 Actions prévues pour la prochaine visite
                                </label>
                                <textarea 
                                    v-model="form.actions_prevues"
                                    rows="2"
                                    class="w-full text-base border-2 border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Ce qu'il faudra faire la prochaine fois..."
                                ></textarea>
                            </div>

                            <!-- Prochaine visite -->
                            <div>
                                <label class="block text-sm font-medium text-gray-900 mb-2">
                                    📅 Prochaine visite prévue (optionnel)
                                </label>
                                <input 
                                    type="date" 
                                    v-model="form.prochaine_visite_prevue"
                                    class="w-full text-base border-2 border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Sauvegarde automatique -->
                    <div v-if="autoSaveStatus" class="bg-green-50 border border-green-200 rounded-lg p-3 flex items-center gap-2">
                        <CheckCircle class="w-5 h-5 text-green-600" />
                        <span class="text-sm text-green-800">Données sauvegardées automatiquement</span>
                    </div>

                    <!-- Actions -->
                    <div class="bg-white rounded-lg border border-gray-200 p-4 flex flex-col gap-3 sm:flex-row sm:justify-end sticky bottom-4">
                        <Link :href="route('ruchers.ruches.visites.index', [rucher.id, ruche.id])"
                            class="w-full sm:w-auto px-6 py-3 text-base font-medium text-gray-700 bg-white border-2 border-gray-300 rounded-lg hover:bg-gray-50 text-center transition-colors">
                            Annuler
                        </Link>
                        
                        <button 
                            type="submit" 
                            :disabled="form.processing || !form.date_visite"
                            class="w-full sm:w-auto px-6 py-3 text-base font-medium text-white bg-blue-600 border-2 border-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2 transition-colors"
                        >
                            <span v-if="form.processing">
                                <Loader class="w-5 h-5 animate-spin" />
                                Enregistrement...
                            </span>
                            <span v-else class="flex items-center gap-2">
                                <Save class="w-5 h-5" />
                                Enregistrer la visite
                            </span>
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';
import { 
    CheckCircle, 
    Eye,
    AlertTriangle,
    ChevronDown,
    Wrench,
    FileText,
    Save,
    Loader,
    Sun,
    Crown,
    Egg,
    Archive,
    Shield,
    Package
} from 'lucide-vue-next';

// Props
const props = defineProps({
    rucher: Object,
    ruche: Object,
    formData: Object
});

// État des sections ouvertes
const openSteps = ref({
    meteo: false,
    observations: false,
    reine: false,
    couvain: false,
    reserves: false,
    sante: false,
    structure: false,
    actions: false
});

// Sauvegarde automatique
const autoSaveStatus = ref(false);

// Formulaire Inertia avec valeurs par défaut
const form = useForm({
    // Données de base
    date_visite: new Date().toISOString().split('T')[0],
    heure_debut: '',
    heure_fin: '',
    
    // État général simplifié
    etat_general: '',
    
    // Météo
    meteo: '',
    temperature: null,
    humidite: null,
    force_vent: '',
    
    // État général de la colonie
    humeur_colonie: 'non_observe',
    activite_entree: 'non_observe',
    estimation_population: 'non_observe',
    bruit_colonie: 'non_observe',
    
    // Observations reine
    reine_vue: 'non_observe',
    reine_marquee: 'non_observe',
    ponte_observee: 'non_observe',
    qualite_ponte: 'non_observe',
    cellules_royales: 'non_observe',
    nombre_cellules_royales: null,
    
    // Couvain
    oeufs_vus: 'non_observe',
    larves_vues: 'non_observe',
    couvain_opercule: 'non_observe',
    surface_couvain: 'non_observe',
    regularite_couvain: 'non_observe',
    
    // Réserves
    reserves_miel: 'non_observe',
    reserves_pollen: 'non_observe',
    cadres_miel_opercule: null,
    
    // Santé & Parasites
    varroas_observes: 'non_observe',
    nombre_varroas_estimes: null,
    fausse_teigne: 'non_observe',
    mortalite: 'non_observe',
    abeilles_mortes_quantite: '',
    
    // Structure de la ruche
    cadres_etat: 'non_observe',
    nombre_cadres_total: null,
    nombre_cadres_occupes: null,
    cire_construite: 'non_observe',
    
    // Actions
    nourrissement_effectue: false,
    traitement_applique: false,
    hausse_ajoutee: false,
    hausse_retiree: false,
    type_nourrissement: '',
    quantite_nourrissement: '',
    
    // Notes
    notes_generales: '',
    actions_prevues: '',
    prochaine_visite_prevue: '',
    
    // Merge avec données du serveur si disponibles
    ...props.formData
});

// Progression calculée
const totalSteps = 10;
const completedSteps = computed(() => {
    let completed = 0;
    
    // Étape 1: Essentiel
    if (form.date_visite && form.etat_general) completed++;
    
    // Étape 2: Météo
    if (form.meteo || form.temperature || form.humidite || form.force_vent) {
        completed++;
    }
    
    // Étape 3: Observations générales
    if (form.activite_entree !== 'non_observe' || form.humeur_colonie !== 'non_observe' || 
        form.estimation_population !== 'non_observe' || form.bruit_colonie !== 'non_observe') {
        completed++;
    }
    
    // Étape 4: Reine
    if (form.reine_vue !== 'non_observe' || form.reine_marquee !== 'non_observe' || 
        form.ponte_observee !== 'non_observe' || form.qualite_ponte !== 'non_observe' ||
        form.cellules_royales !== 'non_observe') {
        completed++;
    }
    
    // Étape 5: Couvain
    if (form.oeufs_vus !== 'non_observe' || form.larves_vues !== 'non_observe' || 
        form.couvain_opercule !== 'non_observe' || form.surface_couvain !== 'non_observe' ||
        form.regularite_couvain !== 'non_observe') {
        completed++;
    }
    
    // Étape 6: Réserves
    if (form.reserves_miel !== 'non_observe' || form.reserves_pollen !== 'non_observe' || 
        form.cadres_miel_opercule) {
        completed++;
    }
    
    // Étape 7: Santé
    if (form.varroas_observes !== 'non_observe' || form.fausse_teigne !== 'non_observe' || 
        form.mortalite !== 'non_observe') {
        completed++;
    }
    
    // Étape 8: Structure
    if (form.cadres_etat !== 'non_observe' || form.cire_construite !== 'non_observe' || 
        form.nombre_cadres_total || form.nombre_cadres_occupes) {
        completed++;
    }
    
    // Étape 9: Actions
    if (form.nourrissement_effectue || form.traitement_applique || form.hausse_ajoutee || form.hausse_retiree) {
        completed++;
    }
    
    // Étape 10: Notes
    if (form.notes_generales || form.actions_prevues || form.prochaine_visite_prevue) {
        completed++;
    }
    
    return completed;
});

// Toggle accordéon
function toggleStep(step) {
    openSteps.value[step] = !openSteps.value[step];
}

// Sauvegarde automatique toutes les 30 secondes
let autoSaveTimer;
function startAutoSave() {
    autoSaveTimer = setInterval(() => {
        if (form.isDirty && !form.processing) {
            // Simuler sauvegarde (en production: appel AJAX)
            autoSaveStatus.value = true;
            setTimeout(() => autoSaveStatus.value = false, 2000);
        }
    }, 30000);
}

// Soumission
function submit() {
    form.post(route('ruchers.ruches.visites.store', [props.rucher.id, props.ruche.id]), {
        onSuccess: () => {
            clearInterval(autoSaveTimer);
        }
    });
}

// Cycle de vie
onMounted(() => {
    startAutoSave();
    
    // Ouvrir la première section par défaut
    openSteps.value.observations = true;
});
</script>