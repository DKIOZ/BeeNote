<script setup>
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Hex from '@/Components/Hex.vue';
import Icon from '@/Components/Icon.vue';
import { dateCourte } from '@/terrain/fields.js';

const props = defineProps({ ruche: Object, historique: Array, peutModifier: Boolean });

const POINTS = { visite: 'bg-[#2F6DB5]', traitement: 'bg-late', recolte: 'bg-[#E09A00]', deplacement: 'bg-line' };
const TITRES = { visite: 'Visite', traitement: 'Traitement', recolte: 'Récolte', deplacement: 'Déplacement' };
const lien = (ligne) => (ligne.type === 'visite' ? route('visites.show', ligne.id) : route('fiches.show', [ligne.type, ligne.id]));
const cocher = (tache) => router.patch(route('taches.toggle', tache.id), {}, { preserveScroll: true });
const reine = () => [props.ruche.type && props.ruche.type[0].toUpperCase() + props.ruche.type.slice(1), props.ruche.annee_reine && `Reine ${props.ruche.annee_reine}${props.ruche.couleur_marquage_reine ? ', ' + props.ruche.couleur_marquage_reine : ''}`].filter(Boolean).join(' · ');
</script>

<template>
    <AppLayout :title="ruche.nom">
        <div class="lg:grid lg:grid-cols-[minmax(0,1fr)_minmax(0,1.3fr)] lg:gap-6">
            <div>
                <section class="rounded-b-hero bg-forest px-5 pb-5 pt-2 text-white lg:rounded-hero lg:p-6">
                    <header class="-mx-3 flex items-center justify-between">
                        <Link :href="route('ruchers.show', ruche.rucher.id)" :aria-label="`Retour à ${ruche.rucher.nom}`" class="flex h-12 w-12 items-center justify-center"><Icon nom="retour" /></Link>
                        <Link v-if="peutModifier" :href="route('ruches.edit', ruche.id)" class="flex min-h-[48px] items-center px-3 font-semibold">Modifier</Link>
                    </header>
                    <div class="flex items-center gap-3">
                        <Hex :etat="ruche.etat" :mark="ruche.numero" :couleur="ruche.couleur" :size="62" sur-vert />
                        <div class="min-w-0">
                            <h1 class="bn-title text-4xl leading-tight">{{ ruche.nom }}</h1>
                            <p class="text-sage">{{ ruche.rucher.nom }}<template v-if="reine()"> · {{ reine() }}</template></p>
                        </div>
                    </div>
                    <Link :href="route('terrain', { ruche: ruche.id })" class="bn-btn-flat mt-3 lg:w-full">Commencer une visite</Link>
                </section>

                <div class="grid grid-cols-3 gap-2 px-4 pt-3 lg:px-0">
                    <Link :href="route('noter.create', { type: 'traitement', ruche: ruche.id })" class="bn-btn-2 !px-1 text-sm">Traitement</Link>
                    <Link :href="route('noter.create', { type: 'recolte', ruche: ruche.id })" class="bn-btn-2 !px-1 text-sm">Récolte</Link>
                    <Link :href="route('noter.create', { type: 'deplacement', ruche: ruche.id })" class="bn-btn-2 !px-1 text-sm">Déplacer</Link>
                </div>

                <div v-for="tache in ruche.taches" :key="tache.id" class="mx-4 mt-2.5 flex items-center gap-2 rounded-card py-1 pl-1 pr-3.5 lg:mx-0" :class="tache.retard ? 'bg-peach' : 'bg-white shadow-card'">
                    <button type="button" class="flex h-14 w-14 flex-none items-center justify-center" :aria-label="`Marquer comme fait : ${tache.titre}`" @click="cocher(tache)">
                        <span class="block h-[30px] w-[30px] rounded-[10px] border-2 border-line bg-white" />
                    </button>
                    <span class="min-w-0">
                        <span class="block font-semibold">{{ tache.titre }}</span>
                        <span class="block text-sm font-semibold" :class="tache.retard ? 'text-rust' : 'text-muted'">{{ tache.retard ? `En retard depuis le ${dateCourte(tache.date_prevue)}` : dateCourte(tache.date_prevue) }}</span>
                    </span>
                </div>
            </div>

            <section class="px-4 pt-3 lg:bn-card lg:p-5">
                <h2 class="bn-title mb-2 ml-1 text-[22px] lg:ml-0">Historique</h2>
                <p v-if="!historique.length" class="ml-1 text-muted">Rien pour l'instant. Votre première visite apparaîtra ici.</p>
                <ol class="flex flex-col gap-1.5">
                    <li v-for="ligne in historique" :key="`${ligne.type}-${ligne.id}`">
                        <Link :href="lien(ligne)" class="flex min-h-[56px] items-center gap-3 rounded-2xl bg-white px-3 py-1.5 lg:bg-ground">
                            <span aria-hidden="true" class="h-3 w-3 flex-none rounded-full" :class="POINTS[ligne.type]" />
                            <span class="min-w-0 flex-1">
                                <span class="block font-semibold">{{ TITRES[ligne.type] }}</span>
                                <span class="block truncate text-sm text-muted">{{ ligne.detail }}</span>
                            </span>
                            <span class="flex-none text-sm font-semibold text-muted">{{ dateCourte(ligne.date) }}</span>
                        </Link>
                    </li>
                </ol>
            </section>
        </div>
    </AppLayout>
</template>
