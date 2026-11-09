<script setup>
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Hex from '@/Components/Hex.vue';
import Icon from '@/Components/Icon.vue';
import RuchesTable from '@/Components/RuchesTable.vue';
import { dateCourte, jourLocal } from '@/terrain/fields.js';

defineProps({ rucher: Object, peutSupprimer: Boolean });

function etatTexte(ruche) {
    if (ruche.etat === 'hors') {
        return ruche.statut === 'morte' ? 'Morte, hors tournée' : 'Inactive, hors tournée';
    }

    const morceaux = [];
    if (ruche.etat === 'done') {
        morceaux.push(ruche.prochaine_visite ? `Visitée aujourd'hui · prochaine le ${dateCourte(ruche.prochaine_visite)}` : 'Visitée aujourd\'hui');
    } else if (ruche.etat === 'late') {
        morceaux.push(ruche.prochaine_visite && ruche.prochaine_visite < aujourdhui ? `En retard depuis le ${dateCourte(ruche.prochaine_visite)}` : 'En retard');
    } else if (ruche.etat === 'todo') {
        morceaux.push(ruche.derniere_visite ? 'À visiter aujourd\'hui' : 'Pas encore de visite');
    } else if (ruche.prochaine_visite) {
        morceaux.push(`Prochaine visite le ${dateCourte(ruche.prochaine_visite)}`);
    } else if (ruche.rappel) {
        morceaux.push(`À revoir vers le ${dateCourte(ruche.rappel)}`);
    }

    if (ruche.taches[0]) {
        // La ruche est déjà dite « en retard » : ne pas le répéter pour la tâche.
        morceaux.push(ruche.taches[0].titre + (ruche.taches[0].retard && ruche.etat !== 'late' ? ', en retard' : ''));
    }
    return morceaux.join(' · ');
}

const aujourdhui = jourLocal();
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
            <Link v-if="rucher.ruches.length" :href="route('terrain', { rucher: rucher.id })" class="bn-btn-flat">Visiter le rucher</Link>
        </section>

        <section v-if="rucher.taches?.length" class="bn-card mx-4 mt-4 p-4 lg:mx-0">
            <h2 class="font-bold">Pour tout le rucher</h2>
            <p v-for="tache in rucher.taches" :key="tache.id" class="mt-1" :class="tache.retard ? 'font-semibold text-rust' : 'text-muted'">{{ tache.titre }} · {{ tache.retard ? 'en retard' : dateCourte(tache.date_prevue) }}</p>
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
                        <span v-if="ruche.alertes?.length" class="block text-sm font-semibold text-rust">À surveiller : {{ ruche.alertes.join(', ') }}</span>
                    </span>
                </Link>
            </div>
            <div class="mt-3 flex flex-wrap gap-2">
                <Link :href="route('ruches.create', rucher.id)" class="bn-btn-2">Ajouter une ruche</Link>
                <Link v-if="rucher.ruches.length" :href="route('noter.create', { rucher: rucher.id })" class="bn-btn-2">Traiter ou déplacer plusieurs ruches</Link>
            </div>
        </section>
    </AppLayout>
</template>
