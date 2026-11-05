<script setup>
import { onMounted, ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ConfirmSheet from '@/Components/ConfirmSheet.vue';
import Icon from '@/Components/Icon.vue';
import { aPerdre, clearAll, pendingCount } from '@/terrain/store.js';

const lire = (cle, defaut) => {
    try {
        return (localStorage.getItem(cle) ?? defaut) !== '0';
    } catch {
        return defaut !== '0';
    }
};
const ecrire = (cle, valeur) => {
    try {
        localStorage.setItem(cle, valeur ? '1' : '0');
    } catch {
        // Le réglage vaut au moins pour cette session.
    }
};

const reglages = ref([
    { cle: 'bn-voix', label: 'Utiliser la voix', on: lire('bn-voix', '1') },
    { cle: 'bn-echo', label: 'Répéter à voix haute', on: lire('bn-echo', '1') },
    { cle: 'bn-vibre', label: 'Vibrer quand c\'est noté', on: lire('bn-vibre', '1'), telephone: true },
    { cle: 'bn-ecran', label: 'Garder l\'écran allumé', on: lire('bn-ecran', '1'), telephone: true },
]);

const basculer = (reglage) => {
    reglage.on = !reglage.on;
    ecrire(reglage.cle, reglage.on);
};

const attente = ref(0);
const risque = ref(0);
const confirmation = ref(false);

onMounted(async () => { attente.value = await pendingCount().catch(() => 0); });

// Rien n'est effacé tant que la déconnexion n'a pas réussi. L'historique est rendu illisible :
// le bouton retour ne doit pas réafficher les pages du compte.
function deconnecter() {
    router.post(route('logout'), {}, {
        onSuccess: () => {
            router.clearHistory();
            clearAll().catch(() => {});
        },
    });
}

const demanderDeconnexion = async () => {
    risque.value = await aPerdre().catch(() => 0);

    if (risque.value > 0) {
        confirmation.value = true;
    } else {
        deconnecter();
    }
};
</script>

<template>
    <AppLayout title="Plus">
        <header class="px-5 pb-3.5 pt-5 lg:px-0 lg:pt-0">
            <h1 class="bn-title text-4xl"><span class="lg:hidden">Plus</span><span class="hidden lg:inline">Réglages et compte</span></h1>
        </header>

        <div class="flex flex-col gap-3.5 px-4 lg:grid lg:grid-cols-2 lg:gap-6 lg:px-0">
            <section class="bn-card px-4 py-1">
                <h2 class="mb-0.5 mt-3 text-sm font-bold text-muted">Au rucher</h2>
                <button v-for="reglage in reglages" :key="reglage.cle" type="button" role="switch" :aria-checked="reglage.on" class="flex min-h-[56px] w-full items-center justify-between gap-3 border-b border-hairline text-left font-semibold" :class="reglage.telephone && 'lg:hidden'" @click="basculer(reglage)">
                    {{ reglage.label }}
                    <span aria-hidden="true" class="flex h-[34px] w-[60px] flex-none rounded-full p-[3px] transition-colors" :class="reglage.on ? 'bg-forest' : 'bg-line'">
                        <span class="h-7 w-7 rounded-full bg-white transition-transform" :class="reglage.on && 'translate-x-[26px]'" />
                    </span>
                </button>
                <Link :href="route('mots')" class="flex min-h-[56px] items-center justify-between border-b border-hairline font-semibold">Mots à dire <Icon nom="suite" :size="20" /></Link>
                <Link :href="route('demarrage')" class="flex min-h-[56px] items-center justify-between font-semibold">Préparer le téléphone <Icon nom="suite" :size="20" /></Link>
            </section>

            <section class="bn-card px-4 py-1">
                <h2 class="mb-0.5 mt-3 text-sm font-bold text-muted">Mon compte</h2>
                <Link :href="route('terrain') + '#synchro'" class="flex min-h-[52px] items-center justify-between border-b border-hairline font-semibold">
                    Visites à envoyer
                    <span v-if="attente" class="bn-badge bg-cream text-cream-ink">{{ attente }}</span>
                </Link>
                <Link :href="route('profile.show')" class="flex min-h-[52px] items-center border-b border-hairline font-semibold">E-mail et mot de passe</Link>
                <Link :href="route('corbeille.index')" class="flex min-h-[52px] items-center border-b border-hairline font-semibold">Corbeille</Link>
                <Link :href="route('etiquettes.index')" class="flex min-h-[52px] items-center border-b border-hairline font-semibold lg:hidden">QR codes à imprimer</Link>
                <Link :href="route('policy.show')" class="flex min-h-[52px] items-center border-b border-hairline font-semibold">Confidentialité et conditions</Link>
                <button type="button" class="flex min-h-[52px] w-full items-center font-semibold text-rust" @click="demanderDeconnexion">Se déconnecter</button>
            </section>
        </div>

        <ConfirmSheet
            v-if="confirmation"
            :titre="`${risque} ${risque > 1 ? 'visites ne sont' : 'visite n\'est'} pas encore ${risque > 1 ? 'envoyées' : 'envoyée'}`"
            :texte="`Si vous vous déconnectez maintenant, ${risque > 1 ? 'elles seront perdues' : 'elle sera perdue'}.`"
            garder="Rester connecté"
            confirmer="Me déconnecter quand même"
            @garder="confirmation = false"
            @confirmer="deconnecter"
        />
    </AppLayout>
</template>
