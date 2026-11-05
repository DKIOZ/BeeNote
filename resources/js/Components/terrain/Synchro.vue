<script setup>
import { computed } from 'vue';
import Hex from '@/Components/Hex.vue';
import Icon from '@/Components/Icon.vue';
import { dateCourte, jourLocal } from '@/terrain/fields.js';
import { useTerrain } from '@/terrain/useTerrain.js';

const { state, enAttente, refusees, aller, envoyer, supprimerRefusee, reessayerRefusee } = useTerrain();

const entete = computed(() => {
    if (state.envoi === 'encours') return { titre: 'Envoi en cours', texte: 'Vous pouvez fermer l\'appli, rien ne sera perdu.' };
    if (state.envoi === 'session') return { titre: 'Reconnectez-vous', texte: 'Votre session a expiré. Vos visites restent sur le téléphone.' };
    if (!state.enligne) return { titre: 'Tout est gardé sur votre téléphone', texte: 'Pas de réseau pour l\'instant. L\'envoi partira tout seul à son retour.' };
    if (state.envoi === 'echec') return { titre: 'L\'envoi a échoué', texte: 'Vos visites restent sur le téléphone. Nouvel essai automatique dans une minute.' };
    if (!enAttente.value.length) return { titre: 'Tout est envoyé', texte: 'Vos visites sont dans votre registre.' };

    return { titre: 'Visites à envoyer', texte: 'Elles partiront dès que possible.' };
});

const dernier = computed(() => {
    if (!state.dernierEnvoi) {
        return null;
    }

    const date = new Date(state.dernierEnvoi);
    const heure = date.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' }).replace(':', ' h ');
    const jour = date.toDateString() === new Date().toDateString() ? 'aujourd\'hui' : `le ${dateCourte(jourLocal(date))}`;

    return `${jour} à ${heure}`;
});
</script>

<template>
    <div class="pb-6">
        <section class="rounded-b-hero bg-forest px-5 pb-6 pt-2 text-white lg:rounded-hero">
            <button type="button" aria-label="Retour à l'accueil" class="-ml-3 flex h-12 w-12 items-center justify-center" @click="aller('accueil')"><Icon nom="retour" /></button>
            <h1 class="bn-title text-[28px] leading-tight" aria-live="polite">{{ entete.titre }}</h1>
            <p class="mt-2 text-lg text-sage">{{ entete.texte }}</p>
        </section>

        <section class="px-4 pt-4 lg:px-0">
            <h2 v-if="enAttente.length" class="bn-title mb-2 ml-1 text-[22px]">À envoyer</h2>
            <div class="flex flex-col gap-2">
                <div v-for="item in enAttente" :key="item.client_uuid" class="bn-card flex min-h-[64px] items-center gap-3 px-4 py-2">
                    <Hex :etat="item.recue ? 'done' : 'todo'" :size="36" />
                    <span class="min-w-0 flex-1">
                        <span class="block font-semibold">Visite de {{ item.nom }}</span>
                        <span class="block text-sm text-muted">{{ dateCourte(item.date_visite) }}<template v-if="item.note"> · note vocale</template></span>
                    </span>
                    <span class="flex-none text-sm font-semibold text-muted">{{ item.recue ? 'Note à envoyer' : (state.envoi === 'encours' ? 'Envoi…' : 'En attente') }}</span>
                </div>
            </div>

            <template v-if="refusees.length">
                <h2 class="bn-title mb-2 ml-1 mt-5 text-[22px] text-rust">Refusées</h2>
                <div v-for="item in refusees" :key="item.client_uuid" class="mb-2 rounded-card bg-peach p-3.5">
                    <p class="font-semibold">{{ item.noteRefusee ? 'Note vocale' : 'Visite' }} de {{ item.nom }}, {{ dateCourte(item.date_visite) }}</p>
                    <p class="mb-2 text-sm">{{ item.noteRefusee ? 'La visite est bien reçue, mais pas sa note vocale.' : 'Cette ruche est à la corbeille ou n\'existe plus, ou la date n\'est pas valable.' }}</p>
                    <div class="grid grid-cols-2 gap-2">
                        <button type="button" class="bn-btn-2 lg:w-full" :disabled="!state.enligne" @click="reessayerRefusee(item.client_uuid)">Réessayer</button>
                        <button type="button" class="bn-btn-2 lg:w-full" @click="supprimerRefusee(item.client_uuid)">{{ item.noteRefusee ? 'Supprimer la note' : 'Supprimer' }}</button>
                    </div>
                </div>
            </template>

            <p v-if="dernier" class="ml-1 mt-2.5 text-sm text-muted">Dernier envoi réussi : {{ dernier }}</p>
            <a v-if="state.envoi === 'session'" href="/login" class="bn-btn mt-3">Me reconnecter</a>
            <button v-else-if="enAttente.length && state.enligne" type="button" class="bn-btn-2 mt-3" :disabled="state.envoi === 'encours'" @click="envoyer">Réessayer maintenant</button>
            <button type="button" class="bn-btn mt-3 lg:w-full" @click="aller('accueil')">Retour à l'accueil</button>
        </section>

        <section class="bn-card mx-4 mt-4 p-4 lg:mx-0">
            <h2 class="font-bold">Possible sans réseau</h2>
            <p class="mt-1 text-muted">Noter une visite à la voix ou au toucher, retrouver vos ruches, scanner un QR code.</p>
            <h2 class="mt-3.5 font-bold">Au retour du réseau</h2>
            <p class="mt-1 text-muted">Envoi de vos visites, météo et téléchargement du registre.</p>
        </section>
    </div>
</template>
