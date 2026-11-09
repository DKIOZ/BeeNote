<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import Hex from '@/Components/Hex.vue';

// Démonstration jouée en boucle : ce qu'on dit, puis ce que l'appli a noté.
const ETAPES = [
    { dit: 'reine vue', note: 'Reine vue' },
    { dit: 'pas de varroas', note: 'Pas de varroas' },
    { dit: 'hausse posée', note: 'Hausse posée' },
    { dit: 'ruche suivante', note: null },
];

const RUCHES = [1, 2, 3, 4, 5, 6];

const pas = ref(0);
const ruche = ref(4);
let minuteur = null;

const animer = !window.matchMedia?.('(prefers-reduced-motion: reduce)').matches;

const notes = computed(() => ETAPES.slice(0, pas.value).filter((e) => e.note));
const dit = computed(() => (pas.value ? `« ${ETAPES[pas.value - 1].dit} »` : '…'));

function etat(numero) {
    if (numero < ruche.value) return 'done';
    if (numero === ruche.value) return 'todo';
    return 'next';
}

function avancer() {
    if (pas.value < ETAPES.length) {
        pas.value += 1;
        return;
    }
    // « Ruche suivante » : la ruche est faite, on passe à la prochaine, puis la démo reprend.
    ruche.value = ruche.value >= 6 ? 4 : ruche.value + 1;
    pas.value = 0;
}

onMounted(() => {
    if (!animer) {
        pas.value = ETAPES.length;
        return;
    }
    minuteur = window.setInterval(avancer, 1700);
});

onBeforeUnmount(() => window.clearInterval(minuteur));
</script>

<template>
    <div class="relative mx-auto w-full max-w-[340px]" aria-hidden="true">
        <div class="absolute -inset-6 rounded-[48px] bg-honey/20 blur-2xl"></div>
        <div class="relative rounded-[40px] border-[10px] border-forest-deep bg-ground p-4 shadow-nav">
            <div class="mx-auto mb-3 h-1.5 w-16 rounded-full bg-forest-deep/20"></div>

            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-muted">Rucher des Tilleuls</p>
                    <p class="bn-title text-2xl text-forest">Ruche {{ ruche }}</p>
                </div>
                <span class="bn-badge bg-mint text-mint-ink">Hors ligne</span>
            </div>

            <div class="mt-3 flex gap-0.5">
                <Hex v-for="n in RUCHES" :key="n" :etat="etat(n)" :mark="String(n)" :size="38" />
            </div>

            <div class="mt-4 min-h-[148px] space-y-2">
                <div
                    v-for="item in notes"
                    :key="`${ruche}-${item.note}`"
                    class="flex animate-bn-pop items-center gap-2 rounded-2xl bg-white px-3 py-2.5 font-semibold text-forest shadow-card"
                >
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#174A32" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M5 13l4 4L19 7" /></svg>
                    {{ item.note }}
                </div>
            </div>

            <div class="mt-3 rounded-3xl bg-forest p-4 text-white">
                <p class="text-xs font-semibold uppercase tracking-wide text-sage">Vous dites</p>
                <p :key="`${ruche}-${pas}`" class="bn-title mt-1 min-h-[32px] animate-bn-pop text-[22px] text-honey">{{ dit }}</p>
                <div class="mt-3 flex items-center gap-3">
                    <span class="relative flex h-12 w-12 flex-none items-center justify-center rounded-full bg-honey text-forest">
                        <span class="absolute inset-0 animate-bn-ring rounded-full bg-honey"></span>
                        <svg class="relative" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><rect x="9" y="3" width="6" height="11" rx="3" fill="currentColor" /><path d="M5 11a7 7 0 0 0 14 0" /><path d="M12 18v3" /></svg>
                    </span>
                    <span class="flex h-8 items-center gap-1">
                        <span v-for="i in 7" :key="i" class="block h-full w-1.5 origin-center animate-bn-wave rounded-full bg-sage" :style="{ animationDelay: `${i * 0.11}s` }"></span>
                    </span>
                    <span class="text-sm text-sage-light">J'écoute…</span>
                </div>
            </div>
        </div>
    </div>
</template>
