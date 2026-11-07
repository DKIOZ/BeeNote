<script setup>
import { computed, ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { dateCourte } from '@/terrain/fields.js';

const props = defineProps({ lignes: Array, aUnRucher: Boolean, limite: { type: Number, default: null } });

const FILTRES = [['tout', 'Tout'], ['traitement', 'Traitements'], ['visite', 'Visites'], ['recolte', 'Récoltes'], ['deplacement', 'Déplacements']];
const TITRES = { visite: 'Visite', traitement: 'Traitement', recolte: 'Récolte', deplacement: 'Déplacement' };
const POINTS = { visite: 'bg-[#2F6DB5]', traitement: 'bg-late', recolte: 'bg-[#E09A00]', deplacement: 'bg-line' };
const BADGES = { visite: 'bg-[#DCEAF8] text-[#1C5A99]', traitement: 'bg-peach text-rust', recolte: 'bg-cream text-cream-ink', deplacement: 'bg-soft text-muted' };

const filtre = ref('tout');
const lignes = computed(() => (filtre.value === 'tout' ? props.lignes : props.lignes.filter((l) => l.type === filtre.value)));
const lien = (ligne) => (ligne.type === 'visite' ? route('visites.show', ligne.id) : route('fiches.show', [ligne.type, ligne.id]));
</script>

<template>
    <AppLayout title="Registre">
        <header class="flex flex-wrap items-end justify-between gap-3 px-5 pb-3.5 pt-5 lg:px-0 lg:pt-0">
            <div>
                <h1 class="bn-title text-4xl">Registre</h1>
                <p class="mt-1 text-muted">Le registre d'élevage, à présenter en cas de contrôle sanitaire. 12 derniers mois, tous les ruchers.</p>
            </div>
            <Link v-if="props.lignes.length" :href="route('registre.export-form')" class="bn-btn lg:w-auto">Télécharger mon registre</Link>
        </header>

        <div v-if="props.lignes.length" role="group" aria-label="Filtrer" class="flex flex-wrap gap-1.5 px-4 pb-1 lg:px-0">
            <button
                v-for="[valeur, texte] in FILTRES"
                :key="valeur"
                type="button"
                :aria-pressed="filtre === valeur"
                class="min-h-[46px] flex-none rounded-full px-3.5 font-semibold"
                :class="filtre === valeur ? 'bg-forest text-white' : 'bg-white text-ink'"
                @click="filtre = valeur"
            >{{ texte }}</button>
        </div>

        <p v-if="limite" class="mx-5 mt-2 text-sm text-muted lg:mx-0">Seules les {{ limite }} dernières lignes s'affichent. Le fichier téléchargé contient tout.</p>

        <p v-if="props.lignes.length && !lignes.length" class="bn-card mx-4 mt-3 p-5 text-muted lg:mx-0">Rien dans ce filtre.</p>
        <div v-if="!props.lignes.length" class="bn-card mx-4 mt-3 p-5 lg:mx-0">
            <p class="font-semibold">Rien pour l'instant.</p>
            <p class="mt-1 text-muted">Votre première visite apparaîtra ici.</p>
            <Link v-if="aUnRucher" :href="route('terrain')" class="bn-btn mt-3">Noter une visite</Link>
            <Link v-else :href="route('ruchers.create')" class="bn-btn mt-3">Créer mon rucher</Link>
        </div>

        <ol class="mx-4 mt-3 flex flex-col gap-2 lg:hidden">
            <li v-for="ligne in lignes" :key="`${ligne.type}-${ligne.id}`">
                <Link :href="lien(ligne)" class="bn-card flex min-h-[60px] items-center gap-3 px-3.5 py-2.5">
                    <span aria-hidden="true" class="h-3 w-3 flex-none rounded-full" :class="POINTS[ligne.type]" />
                    <span class="min-w-0 flex-1">
                        <span class="block font-semibold">{{ TITRES[ligne.type] }} · {{ ligne.ruche }}</span>
                        <span class="line-clamp-2 block text-sm text-muted">{{ ligne.rucher }} · {{ ligne.detail }}</span>
                    </span>
                    <span class="flex-none text-sm font-semibold text-muted">{{ dateCourte(ligne.date) }}</span>
                </Link>
            </li>
        </ol>

        <div v-if="lignes.length" class="bn-card mt-4 hidden overflow-x-auto lg:block">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="text-left text-sm text-muted">
                        <th scope="col" class="px-5 py-4 font-semibold">Date</th>
                        <th scope="col" class="px-3 py-4 font-semibold">Type</th>
                        <th scope="col" class="px-3 py-4 font-semibold">Ruche</th>
                        <th scope="col" class="px-3 py-4 font-semibold">Rucher</th>
                        <th scope="col" class="px-5 py-4 font-semibold">Détail</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="ligne in lignes" :key="`${ligne.type}-${ligne.id}`" class="cursor-pointer border-t border-hairline hover:bg-ground" @click="router.visit(lien(ligne))">
                        <td class="whitespace-nowrap px-5 py-3 font-semibold"><Link :href="lien(ligne)" class="underline-offset-2 hover:underline">{{ dateCourte(ligne.date) }}</Link></td>
                        <td class="px-3 py-3"><span class="bn-badge" :class="BADGES[ligne.type]">{{ TITRES[ligne.type] }}</span></td>
                        <td class="whitespace-nowrap px-3 py-3">{{ ligne.ruche }}</td>
                        <td class="whitespace-nowrap px-3 py-3">{{ ligne.rucher }}</td>
                        <td class="px-5 py-3 text-muted">{{ ligne.detail }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AppLayout>
</template>
