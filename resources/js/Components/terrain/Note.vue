<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { useTerrain } from '@/terrain/useTerrain.js';

const { ruche, ajouterNote, aller } = useTerrain();

const MAX_SECONDES = 120;
const secondes = ref(0);
const erreur = ref(null);
const BARRES = [16, 30, 44, 24, 58, 34, 20, 48, 28, 58, 22, 40, 54, 26, 18, 44, 32, 58, 24, 16];

let recorder = null;
let stream = null;
let timer = null;
let morceaux = [];
let annule = false;
let demonte = false;

const horloge = computed(() => `${Math.floor(secondes.value / 60)}:${String(secondes.value % 60).padStart(2, '0')}`);

function format() {
    return ['audio/webm;codecs=opus', 'audio/webm', 'audio/mp4', 'audio/ogg'].find((type) => window.MediaRecorder?.isTypeSupported?.(type)) ?? '';
}

async function demarrer() {
    if (!window.MediaRecorder || !navigator.mediaDevices?.getUserMedia) {
        erreur.value = 'Votre téléphone ne permet pas d\'enregistrer ici.';
        return;
    }

    try {
        stream = await navigator.mediaDevices.getUserMedia({ audio: true });

        // L'écran a pu être quitté pendant la demande du micro : rien ne doit démarrer.
        if (demonte) {
            stream.getTracks().forEach((track) => track.stop());
            return;
        }

        const type = format();
        recorder = new MediaRecorder(stream, type ? { mimeType: type } : undefined);
        recorder.ondataavailable = (event) => morceaux.push(event.data);
        recorder.onstop = terminer;
        recorder.start();
        timer = setInterval(() => {
            secondes.value += 1;
            if (secondes.value >= MAX_SECONDES) {
                fin();
            }
        }, 1000);
    } catch {
        erreur.value = 'Le micro est bloqué. Autorisez-le dans les réglages du navigateur.';
    }
}

async function terminer() {
    stream?.getTracks().forEach((track) => track.stop());
    clearInterval(timer);

    if (!annule && morceaux.length) {
        await ajouterNote(new Blob(morceaux, { type: recorder.mimeType }), secondes.value);
    }

    if (!demonte) {
        // La note ne reste pas dans l'historique : le retour ne doit pas relancer le micro.
        aller('visite', true);
    }
}

function fin() {
    if (recorder?.state === 'recording') {
        recorder.stop();
    } else if (!demonte) {
        aller('visite', true);
    }
}

function annuler() {
    annule = true;
    fin();
}

onMounted(() => {
    demarrer();
    window.addEventListener('bn-fin-note', fin);
});

onBeforeUnmount(() => {
    demonte = true;
    window.removeEventListener('bn-fin-note', fin);
    clearInterval(timer);
    if (recorder?.state === 'recording') {
        annule = true;
        recorder.stop();
    }
});
</script>

<template>
    <div class="fixed inset-0 z-40 overflow-y-auto bg-forest-deep text-white lg:left-60">
        <div class="mx-auto flex min-h-full max-w-xl flex-col px-5 pb-4 pt-6">
            <p class="text-sage">{{ ruche?.nom }}</p>
            <h1 class="bn-title mt-1 text-4xl leading-tight">Parlez librement</h1>
            <p class="mt-2 text-lg text-[#E4ECE8]">Votre voix est gardée telle quelle pour la réécouter. Rien n'est analysé.</p>

            <div class="mt-16 text-center">
                <p v-if="erreur" role="alert" class="rounded-2xl bg-peach p-3 font-semibold text-rust">{{ erreur }}</p>
                <template v-else>
                    <p class="bn-title text-6xl" aria-live="off">{{ horloge }}</p>
                    <div aria-hidden="true" class="mt-3 flex h-[60px] items-center justify-center gap-[5px]">
                        <span v-for="(hauteur, index) in BARRES" :key="index" class="w-1.5 animate-bn-wave rounded-sm bg-honey" :style="{ height: `${hauteur}px`, animationDelay: `${index * 0.07}s` }" />
                    </div>
                    <p class="mt-6 text-[#E4ECE8]">Dites « fin de note » pour revenir à la visite.</p>
                </template>
            </div>

            <div class="mt-auto flex flex-col gap-1 pt-8">
                <button type="button" class="bn-btn-flat w-full lg:w-full" @click="fin">Fin de note</button>
                <button type="button" class="min-h-[56px] font-bold" @click="annuler">Annuler la note</button>
            </div>
        </div>
    </div>
</template>
