<script setup>
import { computed, onBeforeUnmount, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Icon from '@/Components/Icon.vue';
import FicheDetail from '@/Components/academie/FicheDetail.vue';
import FicheTerrain from '@/Components/academie/FicheTerrain.vue';
import { THEMES, ficheParId, texteALire } from '@/academie/fiches.js';
import { arreterLecture, lectureDisponible, lire } from '@/academie/lire.js';

const props = defineProps({
    id: { type: String, required: true },
});

const fiche = computed(() => ficheParId(props.id));
const theme = computed(() => THEMES.find((t) => t.id === fiche.value?.theme));
const liees = computed(() => (fiche.value?.liees ?? []).map(ficheParId).filter(Boolean));

const peutLire = lectureDisponible();
const enLecture = ref(false);
const basculerLecture = () => {
    if (enLecture.value) {
        arreterLecture();
        enLecture.value = false;
        return;
    }
    enLecture.value = lire(texteALire(fiche.value), () => { enLecture.value = false; });
};
onBeforeUnmount(arreterLecture);

const imprimer = () => window.print();
</script>

<template>
    <AppLayout :title="fiche?.titreDetail ?? 'Fiche introuvable'">
        <div v-if="!fiche" class="px-5 pt-5 lg:px-0">
            <h1 class="bn-title text-3xl text-forest">Fiche introuvable</h1>
            <p class="mt-2 text-muted">Cette fiche n'existe pas ou a changé de nom.</p>
            <Link :href="route('academie.index')" class="bn-btn mt-4">Voir toutes les fiches</Link>
        </div>

        <template v-else>
            <nav aria-label="Fil d'Ariane" class="flex items-center gap-1 px-2 pt-2 lg:px-0 lg:pt-0">
                <Link :href="route('academie.index')" class="flex min-h-[48px] items-center gap-1 pr-2 font-semibold text-forest">
                    <Icon nom="retour" />
                    Académie
                </Link>
                <span class="text-muted print:hidden" aria-hidden="true">·</span>
                <span class="ml-1 text-muted">{{ theme?.titre }}</span>
            </nav>

            <div class="lg:mt-4 lg:grid lg:grid-cols-[minmax(0,1fr)_340px] lg:items-start lg:gap-8">
                <!-- Sur téléphone, la fiche terrain d'abord ; sur PC, elle passe à droite. -->
                <aside class="lg:sticky lg:top-8 lg:order-2">
                    <section class="rounded-b-hero bg-forest px-5 pb-5 pt-4 text-white lg:rounded-hero">
                        <span class="inline-block rounded-xl bg-honey px-2.5 py-1 text-[13px] font-bold text-forest">Au rucher · 30 s</span>
                        <h1 class="bn-title mt-2.5 text-[28px] leading-tight lg:text-[22px]">{{ fiche.titre }}</h1>
                        <p class="mt-2 text-[17px] leading-snug text-sage-light lg:text-base">{{ fiche.enBref }}</p>
                    </section>
                    <div class="px-4 pt-3 lg:px-0">
                        <FicheTerrain :fiche="fiche" />
                        <div class="mt-3 flex gap-2 print:hidden">
                            <button v-if="peutLire" type="button" class="bn-btn-2 flex-1 gap-2 lg:w-full" :aria-pressed="enLecture" @click="basculerLecture">
                                <Icon :nom="enLecture ? 'fermer' : 'lecture'" :size="20" />
                                {{ enLecture ? 'Arrêter la lecture' : 'Lire à voix haute' }}
                            </button>
                            <button type="button" class="bn-btn-2 hidden flex-1 lg:inline-flex lg:w-full" @click="imprimer">Imprimer</button>
                        </div>
                    </div>
                </aside>

                <article class="px-4 pt-7 lg:order-1 lg:px-0 lg:pt-0">
                    <p class="mx-1 text-sm font-bold uppercase tracking-wide text-late lg:mx-0">{{ theme?.titre }} · lecture {{ fiche.lecture }} min</p>
                    <h2 class="bn-title mx-1 mt-1 text-[32px] leading-tight text-forest lg:mx-0 lg:text-[44px]">{{ fiche.titreDetail }}</h2>
                    <div class="mx-1 mt-4 max-w-[680px] lg:mx-0">
                        <FicheDetail :fiche="fiche" niveau="h3" />
                    </div>

                    <section v-if="liees.length" class="mx-1 mt-6 print:hidden lg:mx-0">
                        <h3 class="mb-2 font-bold text-forest">À lire ensuite</h3>
                        <div class="flex flex-wrap gap-2">
                            <Link v-for="autre in liees" :key="autre.id" :href="route('academie.show', autre.id)" class="inline-flex min-h-[48px] items-center rounded-2xl border-[1.5px] border-line bg-white px-4 font-semibold text-forest lg:hover:bg-ground">{{ autre.titreDetail }}</Link>
                        </div>
                    </section>
                </article>
            </div>
        </template>
    </AppLayout>
</template>
