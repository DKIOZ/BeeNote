<script setup>
import { Link } from '@inertiajs/vue3';
import Hex from '@/Components/Hex.vue';
import { dateCourte } from '@/terrain/fields.js';

defineProps({ ruches: { type: Array, required: true } });

const ETATS = { done: 'Visitée aujourd\'hui', todo: 'À visiter', late: 'En retard', next: 'À venir' };

function aPrevoir(ruche) {
    if (ruche.taches[0]) {
        return ruche.taches[0].titre + (ruche.taches[0].retard ? ', en retard' : '');
    }

    return ruche.prochaine_visite ? `Visite le ${dateCourte(ruche.prochaine_visite)}` : '—';
}
</script>

<template>
    <table class="w-full border-collapse">
        <thead>
            <tr class="text-left text-sm text-muted">
                <th scope="col" class="px-5 py-2 font-semibold">Ruche</th>
                <th scope="col" class="px-3 py-2 font-semibold">Dernière visite</th>
                <th scope="col" class="px-3 py-2 font-semibold">État</th>
                <th scope="col" class="px-5 py-2 font-semibold">À prévoir</th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="ruche in ruches" :key="ruche.id" class="border-t border-hairline hover:bg-ground">
                <td class="px-5 py-2">
                    <Link :href="route('ruches.show', ruche.id)" class="flex min-h-[44px] items-center gap-2.5 font-semibold">
                        <Hex :etat="ruche.etat" :mark="ruche.numero" :couleur="ruche.couleur" :size="36" />
                        {{ ruche.nom }}
                    </Link>
                </td>
                <td class="px-3 py-2 text-muted">{{ ruche.derniere_visite ? dateCourte(ruche.derniere_visite.date) : 'Jamais' }}</td>
                <td class="px-3 py-2" :class="ruche.etat === 'late' && 'font-semibold text-rust'">{{ ETATS[ruche.etat] }}</td>
                <td class="px-5 py-2" :class="ruche.taches[0]?.retard ? 'font-semibold text-rust' : 'text-muted'">{{ aPrevoir(ruche) }}</td>
            </tr>
        </tbody>
    </table>
</template>
