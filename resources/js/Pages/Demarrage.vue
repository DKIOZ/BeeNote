<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import BackBar from '@/Components/BackBar.vue';
import Hex from '@/Components/Hex.vue';
import { createVoskEngine, micSupported } from '@/terrain/voice/engine.js';

const page = usePage();

const micro = ref('todo');
const voix = ref('todo');
const icone = ref('todo');
const invite = ref(null);

const lire = (cle) => {
    try {
        return localStorage.getItem(cle) === '1';
    } catch {
        return false;
    }
};
const ecrire = (cle) => {
    try {
        localStorage.setItem(cle, '1');
    } catch {
        // L'état sera simplement redemandé.
    }
};

const estIphone = /iPhone|iPad/.test(navigator.userAgent);
const installee = window.matchMedia?.('(display-mode: standalone)').matches || window.navigator.standalone;

const surInvite = (event) => {
    event.preventDefault();
    invite.value = event;
};

onMounted(() => {
    if (lire('bn-micro-ok')) micro.value = 'ok';
    if (lire('bn-voix-prete')) voix.value = 'ok';
    if (installee) icone.value = 'ok';
    window.addEventListener('beforeinstallprompt', surInvite);
});

onBeforeUnmount(() => window.removeEventListener('beforeinstallprompt', surInvite));

async function autoriserMicro() {
    micro.value = 'encours';
    if (!micSupported()) {
        micro.value = 'erreur';
        return;
    }

    try {
        const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
        stream.getTracks().forEach((track) => track.stop());
        ecrire('bn-micro-ok');
        micro.value = 'ok';
    } catch {
        micro.value = 'erreur';
    }
}

async function telecharger() {
    voix.value = 'encours';
    try {
        await createVoskEngine(page.props.voiceModelUrl).load();
        ecrire('bn-voix-prete');
        voix.value = 'ok';
    } catch {
        voix.value = 'erreur';
    }
}

async function ajouterIcone() {
    if (invite.value) {
        invite.value.prompt();
        const { outcome } = await invite.value.userChoice;
        if (outcome === 'accepted') {
            icone.value = 'ok';
        }
        invite.value = null;
    } else {
        icone.value = 'aide';
    }
}

const etapes = computed(() => [
    {
        titre: 'Autoriser le micro',
        texte: { erreur: 'Le micro est bloqué : autorisez-le dans les réglages du navigateur. Le toucher reste disponible.' }[micro.value] ?? 'Pour que BeeNote entende vos mots.',
        action: 'Autoriser le micro',
        etat: micro.value,
        lancer: autoriserMicro,
    },
    {
        titre: 'Télécharger la voix',
        texte: {
            encours: 'Téléchargement de 46 Mo : restez en Wi-Fi si possible.',
            erreur: 'Le téléchargement a échoué. Le toucher reste disponible au rucher.',
        }[voix.value] ?? 'Pour vous comprendre sans réseau. Environ 46 Mo.',
        action: voix.value === 'erreur' ? 'Réessayer' : 'Télécharger',
        etat: voix.value,
        lancer: telecharger,
    },
    {
        titre: 'Ajouter l\'icône',
        texte: icone.value === 'aide'
            ? (estIphone ? 'Touchez Partager, puis « Sur l\'écran d\'accueil ».' : 'Ouvrez le menu du navigateur, puis « Installer l\'application ».')
            : 'Pour ouvrir BeeNote comme une appli.',
        action: 'Ajouter à l\'écran d\'accueil',
        etat: icone.value,
        lancer: ajouterIcone,
    },
]);
</script>

<template>
    <AppLayout title="Préparer le téléphone" sans-nav>
        <BackBar :href="route('dashboard')" retour="Retour à l'accueil" titre="Préparons votre téléphone" />
        <header class="px-5 pb-2 lg:px-0">
            <p class="text-lg text-muted">Au toucher, rien à faire : vos ruches sont déjà sur ce téléphone, même sans réseau. Pour noter à la voix, faites ces étapes chez vous, avec du réseau.</p>
        </header>

        <ol class="mx-4 mt-3 flex max-w-xl flex-col gap-3 lg:mx-0">
            <li v-for="(etape, index) in etapes" :key="etape.titre" class="bn-card p-4">
                <div class="flex items-center gap-3">
                    <Hex :etat="etape.etat === 'ok' ? 'done' : 'todo'" :mark="index + 1" :size="42" />
                    <span class="min-w-0">
                        <span class="block text-lg font-bold">{{ etape.titre }}</span>
                        <span class="block" :class="etape.etat === 'erreur' ? 'font-semibold text-rust' : 'text-muted'">{{ etape.texte }}</span>
                    </span>
                </div>
                <button v-if="etape.etat !== 'ok'" type="button" class="bn-btn-2 mt-3 lg:w-full" :disabled="etape.etat === 'encours'" @click="etape.lancer">{{ etape.etat === 'encours' ? 'En cours…' : etape.action }}</button>
            </li>
        </ol>

        <div class="mx-4 mt-4 max-w-xl lg:mx-0">
            <Link :href="route('mots')" class="bn-btn lg:w-full">Essayer ma voix</Link>
            <Link :href="route('dashboard')" class="mt-1 flex min-h-[48px] items-center justify-center font-semibold text-forest">Passer pour l'instant</Link>
        </div>
    </AppLayout>
</template>
