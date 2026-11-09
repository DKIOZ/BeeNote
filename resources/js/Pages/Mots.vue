<script setup>
import { computed, onBeforeUnmount, ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Icon from '@/Components/Icon.vue';
import MotsListe from '@/Components/MotsListe.vue';
import { createVoskEngine, micSupported } from '@/terrain/voice/engine.js';
import { matchPhrase, normalize } from '@/terrain/voice/phrases.js';

const page = usePage();
const MOTS = ['Reine vue', 'Pas de ponte', 'Hausse posée', 'Ruche terminée'];

const index = ref(0);
const etat = ref('repos'); // repos, chargement, ecoute, juste, faux, erreur
const entendu = ref('');
let engine = null;

const mot = computed(() => MOTS[index.value]);

const LIBELLES = { repos: 'Écouter', chargement: 'Préparation de la voix…', ecoute: 'J\'écoute, parlez…', juste: 'Compris ! Mot suivant', faux: 'Réessayer' };

function surTexte(texte) {
    entendu.value = texte;
    arreter();
    const compris = matchPhrase(texte);
    const attendu = matchPhrase(mot.value);
    etat.value = compris && normalize(texte) && compris === attendu ? 'juste' : 'faux';
    navigator.vibrate?.(etat.value === 'juste' ? 80 : [80, 80, 80]);
}

function arreter() {
    engine?.stop();
}

async function agir() {
    if (etat.value === 'juste') {
        index.value = (index.value + 1) % MOTS.length;
        etat.value = 'repos';
        entendu.value = '';
        return;
    }

    if (!micSupported()) {
        etat.value = 'erreur';
        return;
    }

    etat.value = 'chargement';
    try {
        engine = engine ?? createVoskEngine(page.props.voiceModelUrl);
        await engine.start(surTexte);
        etat.value = 'ecoute';
    } catch {
        etat.value = 'erreur';
    }
}

onBeforeUnmount(arreter);

const imprimer = () => window.print();
</script>

<template>
    <AppLayout title="Mots à dire" sans-nav>
        <section class="rounded-b-hero bg-forest px-5 pb-5 pt-2 text-white print:hidden lg:rounded-hero">
            <Link :href="route('plus')" aria-label="Retour" class="-ml-3 flex h-12 w-12 items-center justify-center"><Icon nom="retour" /></Link>
            <p class="text-sage">Essai · mot {{ index + 1 }} sur {{ MOTS.length }}</p>
            <p class="mt-1.5 text-sage-light">Touchez le micro et dites :</p>
            <h1 class="bn-title mb-3.5 mt-0.5 text-4xl leading-tight text-honey">« {{ mot }} »</h1>
            <button type="button" class="bn-btn-flat w-full gap-2.5 lg:w-full" :disabled="etat === 'chargement' || etat === 'ecoute'" @click="agir">
                <Icon nom="micro" />
                {{ LIBELLES[etat] ?? 'Réessayer' }}
            </button>
            <p v-if="etat === 'faux'" class="mt-2.5 font-semibold text-peach-light" role="alert">Entendu : « {{ entendu }} ». Redites la phrase, seule.</p>
            <p v-if="etat === 'erreur'" class="mt-2.5 font-semibold text-peach-light" role="alert">La voix n'est pas disponible ici. Vérifiez le micro, ou préparez le téléphone.</p>
        </section>

        <section class="px-4 pt-4 lg:px-0">
            <h2 class="bn-title mb-2 ml-1 text-[22px]">Les mots à dire</h2>
            <MotsListe />
            <p class="mx-1 mt-2.5 text-muted print:hidden">Perdu au rucher ? Dites « aide ».</p>
            <div class="mt-3 grid grid-cols-2 gap-2 print:hidden lg:flex">
                <button type="button" class="bn-btn-2" @click="imprimer">Imprimer la carte</button>
                <Link :href="route('dashboard')" class="bn-btn-2">C'est bon</Link>
            </div>
        </section>
    </AppLayout>
</template>
