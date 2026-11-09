<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue';
import Icon from '@/Components/Icon.vue';
import { useTerrain } from '@/terrain/useTerrain.js';

const { aller, ouvrirParJeton } = useTerrain();

const video = ref(null);
const message = ref('Placez le QR code de la ruche dans le cadre.');
const erreur = ref(false);

let stream = null;
let timer = null;
let lire = null;

function jetonDepuis(texte) {
    try {
        // Seul un QR de cette appli est accepté : on ne suit jamais une autre adresse.
        const url = new URL(texte);
        const trouve = url.origin === window.location.origin && url.pathname.match(/^\/scan\/([A-Za-z0-9]{32})$/);

        return trouve ? trouve[1] : null;
    } catch {
        return null;
    }
}

async function preparerLecteur() {
    if ('BarcodeDetector' in window) {
        const detecteur = new window.BarcodeDetector({ formats: ['qr_code'] });

        return async (source) => (await detecteur.detect(source))[0]?.rawValue ?? null;
    }

    const { default: jsQR } = await import('jsqr');
    const toile = document.createElement('canvas');

    return async (source) => {
        if (!source.videoWidth) {
            return null;
        }

        toile.width = source.videoWidth;
        toile.height = source.videoHeight;
        const contexte = toile.getContext('2d', { willReadFrequently: true });
        contexte.drawImage(source, 0, 0);
        const image = contexte.getImageData(0, 0, toile.width, toile.height);

        return jsQR(image.data, image.width, image.height)?.data ?? null;
    };
}

function balayer() {
    timer = setTimeout(async () => {
        const texte = video.value ? await lire(video.value).catch(() => null) : null;

        if (texte) {
            const jeton = jetonDepuis(texte);
            if (!jeton) {
                message.value = 'Ce QR code n\'est pas une ruche BeeNote.';
                erreur.value = true;
            } else if (ouvrirParJeton(jeton)) {
                navigator.vibrate?.(120);
                return;
            } else {
                message.value = 'Cette ruche n\'est pas sur ce téléphone.';
                erreur.value = true;
            }
        }

        balayer();
    }, 300);
}

onMounted(async () => {
    try {
        stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' }, audio: false });
        video.value.srcObject = stream;
        await video.value.play();
        lire = await preparerLecteur();
        balayer();
    } catch {
        message.value = 'La caméra est bloquée ou indisponible. Choisissez la ruche dans la liste.';
        erreur.value = true;
    }
});

onBeforeUnmount(() => {
    clearTimeout(timer);
    stream?.getTracks().forEach((track) => track.stop());
});
</script>

<template>
    <div class="fixed inset-0 z-40 overflow-y-auto bg-forest-deep text-white">
        <div class="mx-auto flex min-h-full max-w-xl flex-col px-2 pb-4 pt-2">
            <header class="flex items-center gap-1">
                <button type="button" aria-label="Fermer le scanner" class="flex h-12 w-12 flex-none items-center justify-center" @click="aller('accueil')"><Icon nom="fermer" /></button>
                <h1 class="bn-title text-[28px]">Scanner une ruche</h1>
            </header>

            <div class="relative mx-auto mt-6 aspect-square w-[280px] overflow-hidden rounded-3xl bg-black">
                <video ref="video" class="h-full w-full object-cover" muted playsinline />
                <span aria-hidden="true" class="absolute inset-3 rounded-2xl border-4 border-honey" />
            </div>

            <p class="mx-6 mt-6 text-center text-lg" :class="erreur && 'font-semibold text-peach-light'" role="status">{{ message }}</p>
            <p class="mx-6 mt-2 text-center text-sage">Fonctionne sans réseau.</p>

            <div class="mt-auto px-2 pt-8">
                <button type="button" class="min-h-[56px] w-full font-bold" @click="aller('accueil')">Choisir la ruche dans la liste</button>
            </div>
        </div>
    </div>
</template>
