<script setup>
import { computed, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Icon from '@/Components/Icon.vue';
import { FICHES, MOIS, THEMES, chercher, fichesDuMois, fichesDuTheme } from '@/academie/fiches.js';

const recherche = ref('');
const resultats = computed(() => chercher(recherche.value));
const enRecherche = computed(() => recherche.value.trim() !== '');

const mois = new Date().getMonth() + 1;
const deSaison = fichesDuMois(mois);
const signes = FICHES.filter((fiche) => fiche.voir);
const themes = THEMES.map((theme) => ({ ...theme, fiches: fichesDuTheme(theme.id) }));
// Un point rouille pour ce qui demande d'agir vite, miel pour le reste.
const pressant = (fiche) => Boolean(fiche.urgent);
</script>

<template>
    <AppLayout title="Académie">
        <header class="flex flex-col gap-3 px-5 pb-2 pt-5 lg:flex-row lg:items-end lg:justify-between lg:px-0 lg:pt-0">
            <div>
                <h1 class="bn-title text-4xl text-forest lg:text-[40px]">Académie</h1>
                <p class="mt-1 text-[17px] text-muted">Une réponse courte au rucher, le détail à la maison.</p>
            </div>
            <label class="flex min-h-[56px] items-center gap-2.5 rounded-2xl border-[1.5px] border-line bg-white px-4 focus-within:border-forest lg:min-h-[48px] lg:w-[380px] lg:rounded-xl">
                <svg aria-hidden="true" class="flex-none text-muted" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><circle cx="11" cy="11" r="7" /><path d="M20 20l-3.5-3.5" /></svg>
                <span class="sr-only">Chercher une fiche</span>
                <input v-model="recherche" type="search" placeholder="Varroa, hausse, essaim…" class="min-w-0 flex-1 border-0 bg-transparent p-0 text-lg text-ink placeholder:text-muted focus:ring-0 lg:text-base">
            </label>
        </header>

        <section v-if="enRecherche" class="px-4 pt-3 lg:px-0" aria-live="polite">
            <h2 class="bn-title mb-2 ml-1 text-[22px] text-forest">{{ resultats.length ? `${resultats.length} ${resultats.length > 1 ? 'fiches' : 'fiche'}` : 'Aucune fiche' }}</h2>
            <p v-if="!resultats.length" class="mx-1 text-muted">Essayez un autre mot, ou parcourez les thèmes.</p>
            <div class="flex flex-col gap-2 lg:grid lg:grid-cols-2 lg:gap-3">
                <Link v-for="fiche in resultats" :key="fiche.id" :href="route('academie.show', fiche.id)" class="bn-card flex min-h-[64px] items-center gap-3 px-4 py-3 lg:hover:shadow-nav">
                    <span class="min-w-0 flex-1">
                        <span class="block font-semibold">{{ fiche.titre }}</span>
                        <span class="block text-sm text-muted">{{ fiche.enBref }}</span>
                    </span>
                    <Icon nom="suite" :size="20" class="text-forest" />
                </Link>
            </div>
        </section>

        <template v-else>
            <section v-if="deSaison.length" class="pt-3">
                <h2 class="bn-title mx-5 mb-2 text-[22px] text-forest lg:mx-0 lg:text-2xl">En {{ MOIS[mois - 1] }}</h2>
                <div class="flex gap-3 overflow-x-auto px-4 pb-2 lg:grid lg:grid-cols-2 lg:overflow-visible lg:px-0 xl:grid-cols-3">
                    <Link
                        v-for="(fiche, index) in deSaison"
                        :key="fiche.id"
                        :href="route('academie.show', fiche.id)"
                        class="relative flex min-h-[156px] w-[240px] flex-none flex-col justify-between gap-2 overflow-hidden rounded-card p-4 lg:w-auto lg:p-6 lg:transition-transform lg:hover:-translate-y-0.5"
                        :class="index === 0 ? 'bg-forest text-white' : 'bg-cream text-ink'"
                    >
                        <svg v-if="index === 0" aria-hidden="true" class="absolute -right-5 -top-4 opacity-[0.16]" width="120" height="130" viewBox="0 0 48 52"><path d="M24 4l19 11v22L24 48 5 37V15z" fill="#FFB81C" /></svg>
                        <span class="text-[13px] font-bold" :class="index === 0 ? 'text-honey' : 'text-cream-ink'">FICHE TERRAIN · 30 S</span>
                        <span class="bn-title text-[22px] leading-tight" :class="index !== 0 && 'text-forest'">{{ fiche.titre }}</span>
                        <span class="text-[15px]" :class="index === 0 ? 'text-sage' : 'text-muted'">Le détail en {{ fiche.lecture }} min</span>
                    </Link>
                </div>
            </section>

            <section class="px-4 pt-4 lg:px-0 lg:pt-7">
                <h2 class="bn-title mx-1 text-[22px] text-forest lg:mx-0 lg:text-2xl">Je vois…</h2>
                <p class="mx-1 mb-2.5 text-[15px] text-muted lg:mx-0">Les mêmes mots que pendant la visite.</p>
                <div class="grid grid-cols-2 gap-2.5 lg:grid-cols-3 lg:gap-3 xl:grid-cols-4">
                    <Link v-for="fiche in signes" :key="fiche.id" :href="route('academie.show', fiche.id)" class="bn-card flex min-h-[64px] items-center gap-2.5 px-3 py-2.5 font-semibold leading-tight lg:px-4 lg:hover:shadow-nav">
                        <span aria-hidden="true" class="h-3 w-3 flex-none rounded-full" :class="pressant(fiche) ? 'bg-late' : 'bg-honey-deep'" />
                        {{ fiche.voir }}
                    </Link>
                </div>
            </section>

            <section class="px-4 pt-5 lg:px-0 lg:pt-7">
                <h2 class="bn-title mx-1 mb-2 text-[22px] text-forest lg:mx-0 lg:text-2xl">Par thème</h2>
                <div class="flex flex-col gap-3 lg:grid lg:grid-cols-2 lg:gap-4">
                    <section v-for="theme in themes" :key="theme.id" class="bn-card px-4 py-1">
                        <h3 class="mt-3 font-bold text-forest">{{ theme.titre }}</h3>
                        <p class="text-sm text-muted">{{ theme.texte }}</p>
                        <Link v-for="fiche in theme.fiches" :key="fiche.id" :href="route('academie.show', fiche.id)" class="flex min-h-[56px] items-center justify-between gap-3 border-b border-hairline font-semibold last:border-0 lg:hover:text-forest-deep">
                            <span class="min-w-0">{{ fiche.titreDetail }}<span class="block text-sm font-normal text-muted">{{ fiche.lecture }} min</span></span>
                            <Icon nom="suite" :size="20" />
                        </Link>
                    </section>
                </div>
            </section>

            <p class="mx-5 mt-5 text-sm text-muted lg:mx-0">Pendant une visite, BeeNote propose la fiche terrain qui correspond à ce que vous avez noté, même sans réseau.</p>
        </template>
    </AppLayout>
</template>
