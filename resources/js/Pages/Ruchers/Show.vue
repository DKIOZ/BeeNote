<script setup>
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Hex from '@/Components/Hex.vue';
import Icon from '@/Components/Icon.vue';
import RuchesTable from '@/Components/RuchesTable.vue';
import { dateCourte } from '@/terrain/fields.js';

defineProps({ rucher: Object, peutSupprimer: Boolean });

function etatTexte(ruche) {
    if (ruche.taches[0]) {
        return ruche.taches[0].titre + (ruche.taches[0].retard ? ', en retard' : '');
    }
    if (ruche.etat === 'done') {
        return 'Visitée aujourd\'hui';
    }
    if (ruche.prochaine_visite) {
        return `Prochaine visite le ${dateCourte(ruche.prochaine_visite)}`;
    }

    return ruche.derniere_visite ? `Dernière visite le ${dateCourte(ruche.derniere_visite.date)}` : 'Pas encore de visite';
}
</script>

<template>
    <AppLayout :title="rucher.nom">
        <section class="rounded-b-hero bg-forest px-5 pb-5 pt-2 text-white lg:rounded-hero lg:p-7">
            <header class="-mx-3 flex items-center justify-between lg:mx-0">
                <Link :href="route('ruchers.index')" aria-label="Retour aux ruchers" class="flex h-12 w-12 items-center justify-center lg:-ml-3"><Icon nom="retour" /></Link>
                <Link :href="route('ruchers.edit', rucher.id)" class="flex min-h-[48px] items-center px-3 font-semibold">Modifier</Link>
            </header>
            <h1 class="bn-title text-4xl leading-tight">{{ rucher.nom }}</h1>
            <p class="mb-3.5 mt-1 text-sage">{{ rucher.ruches.length }} {{ rucher.ruches.length > 1 ? 'ruches' : 'ruche' }}<template v-if="!rucher.a_position"> · position à ajouter pour la météo</template></p>
            <Link v-if="rucher.ruches.length" :href="route('terrain', { rucher: rucher.id })" class="bn-btn-flat">Visiter ce rucher</Link>
        </section>

        <section class="px-4 pt-5 lg:px-0">
            <h2 class="bn-title mb-2 ml-1 text-[22px] lg:ml-0">Ruches</h2>
            <div v-if="!rucher.ruches.length" class="bn-card mb-3 p-4">
                <p class="font-semibold">Ajoutez votre première ruche</p>
                <p class="mt-1 text-muted">Il suffit d'un nom. Son numéro servira à la désigner à la voix.</p>
            </div>
            <div v-if="rucher.ruches.length" class="bn-card hidden overflow-hidden lg:block"><RuchesTable :ruches="rucher.ruches" /></div>
            <div class="flex flex-col gap-2.5 lg:hidden">
                <Link v-for="ruche in rucher.ruches" :key="ruche.id" :href="route('ruches.show', ruche.id)" class="bn-card flex min-h-[76px] items-center gap-3 py-2 pl-2.5 pr-3.5">
                    <Hex :etat="ruche.etat" :mark="ruche.numero" :couleur="ruche.couleur" :size="50" />
                    <span class="min-w-0 flex-1">
                        <span class="block font-semibold">{{ ruche.nom }}</span>
                        <span class="block text-sm" :class="ruche.etat === 'late' ? 'font-semibold text-rust' : 'text-muted'">{{ etatTexte(ruche) }}</span>
                    </span>
                </Link>
            </div>
            <Link :href="route('ruches.create', rucher.id)" class="bn-btn-2 mt-3">Ajouter une ruche</Link>
        </section>
    </AppLayout>
</template>
