<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import Icon from '@/Components/Icon.vue';

const props = defineProps({
    title: { type: String, default: null },
    sansNav: { type: Boolean, default: false },
});

const page = usePage();
const courant = computed(() => page.url.split(/[?#]/)[0]);
const actif = (...prefixes) => prefixes.some((prefixe) => courant.value === prefixe || courant.value.startsWith(`${prefixe}/`));

const items = [
    { nom: 'accueil', label: 'Accueil', href: () => route('dashboard'), prefixes: ['/dashboard', '/taches'] },
    { nom: 'ruchers', label: 'Ruchers', href: () => route('ruchers.index'), prefixes: ['/ruchers', '/ruches', '/visites', '/noter', '/fiches', '/terrain', '/scan'] },
    { nom: 'registre', label: 'Registre', href: () => route('registre.index'), prefixes: ['/registre'] },
    { nom: 'plus', label: 'Plus', labelPc: 'Réglages', href: () => route('plus'), prefixes: ['/plus', '/user', '/corbeille', '/mots', '/demarrage', '/etiquettes'] },
];

// Sur PC, les QR codes ont leur propre entrée : Réglages ne s'allume pas en même temps.
// Sur téléphone, le mode terrain a son bouton : le micro.
const auRucher = computed(() => actif('/terrain', '/scan'));
const actifMobile = (item) => actif(...item.prefixes.filter((prefixe) => !['/terrain', '/scan'].includes(prefixe)));
const itemsPc = computed(() => items.filter((item) => item.nom !== 'plus'));
const reglages = items.find((item) => item.nom === 'plus');
const actifPc = (item) => actif(...item.prefixes.filter((prefixe) => prefixe !== '/etiquettes'));

const enLigne = ref(navigator.onLine);
const majReseau = () => { enLigne.value = navigator.onLine; };

onMounted(() => {
    window.addEventListener('online', majReseau);
    window.addEventListener('offline', majReseau);
});

onBeforeUnmount(() => {
    window.removeEventListener('online', majReseau);
    window.removeEventListener('offline', majReseau);
});

const flash = computed(() => page.props.flash ?? {});

// Un message se lit puis s'en va : une confirmation au bout de 5 secondes, une erreur à la fermeture.
const bulle = ref(null);
let minuteur = null;
watch(flash, (valeur) => {
    clearTimeout(minuteur);
    bulle.value = valeur.erreur ? { texte: valeur.erreur, erreur: true } : valeur.message ? { texte: valeur.message, erreur: false } : null;
    if (bulle.value && !bulle.value.erreur) {
        minuteur = setTimeout(() => { bulle.value = null; }, 5000);
    }
}, { immediate: true });
</script>

<template>
    <div class="min-h-screen bg-ground lg:flex">
        <Head :title="title ?? undefined" />

        <aside class="hidden w-60 flex-none bg-forest px-4 py-6 text-white lg:block">
            <Link :href="route('dashboard')" class="flex items-center gap-2.5 px-3">
                <svg aria-hidden="true" width="26" height="28" viewBox="0 0 48 52"><path d="M24 4l19 11v22L24 48 5 37V15z" fill="#FFB81C" stroke="#FFB81C" stroke-width="6" stroke-linejoin="round" /></svg>
                <span class="bn-title text-[22px]">BeeNote</span>
            </Link>
            <nav aria-label="Navigation principale" class="mt-7 flex flex-col gap-1">
                <Link
                    v-for="item in itemsPc"
                    :key="item.nom"
                    :href="item.href()"
                    :aria-current="actifPc(item) ? 'page' : undefined"
                    class="flex min-h-[44px] items-center rounded-xl px-3.5 font-semibold hover:bg-white/10"
                    :class="actifPc(item) ? 'bg-white/15 text-white shadow-[inset_4px_0_0_#FFB81C]' : 'text-[#E4ECE8]'"
                >{{ item.label }}</Link>
                <Link :href="route('etiquettes.index')" :aria-current="actif('/etiquettes') ? 'page' : undefined" class="flex min-h-[44px] items-center rounded-xl px-3.5 font-semibold hover:bg-white/10" :class="actif('/etiquettes') ? 'bg-white/15 text-white shadow-[inset_4px_0_0_#FFB81C]' : 'text-[#E4ECE8]'">QR codes</Link>
                <Link :href="reglages.href()" :aria-current="actifPc(reglages) ? 'page' : undefined" class="flex min-h-[44px] items-center rounded-xl px-3.5 font-semibold hover:bg-white/10" :class="actifPc(reglages) ? 'bg-white/15 text-white shadow-[inset_4px_0_0_#FFB81C]' : 'text-[#E4ECE8]'">{{ reglages.labelPc }}</Link>
            </nav>
        </aside>

        <div class="min-w-0 flex-1">
            <div v-if="!enLigne" role="status" class="flex items-center justify-center gap-2 bg-forest px-4 py-1.5 text-sm font-semibold text-white">
                <span aria-hidden="true" class="h-2 w-2 rounded-full bg-honey" />
                Pas de réseau : tout est gardé sur le téléphone
            </div>

            <main class="mx-auto max-w-[1080px] lg:px-8 lg:py-8" :class="sansNav ? 'pb-6' : 'pb-28 lg:pb-8'">
                <slot name="header" />
                <!-- En bas, au-dessus de la barre : la bulle ne cache jamais le retour ni « Modifier ». -->
                <div v-if="bulle" class="pointer-events-none fixed bottom-28 left-4 right-4 z-50 flex justify-center lg:bottom-6 lg:left-64">
                    <p :role="bulle.erreur ? 'alert' : 'status'" class="pointer-events-auto flex max-w-md items-center gap-2 rounded-2xl border-2 py-2 pl-4 pr-1 font-semibold shadow-nav" :class="bulle.erreur ? 'border-rust bg-peach text-rust' : 'border-forest bg-white text-forest'">
                        <span class="min-w-0 flex-1">{{ bulle.texte }}</span>
                        <button type="button" aria-label="Fermer le message" class="flex h-11 w-11 flex-none items-center justify-center text-xl" @click="bulle = null">×</button>
                    </p>
                </div>
                <slot />
            </main>
        </div>

        <nav
            v-if="!sansNav"
            aria-label="Navigation principale"
            class="fixed bottom-3 left-3 right-3 z-40 flex h-[72px] items-stretch rounded-3xl bg-white shadow-nav lg:hidden"
        >
            <template v-for="(item, index) in items" :key="item.nom">
                <Link
                    v-if="index === 2"
                    :href="route('terrain')"
                    :aria-current="auRucher ? 'page' : undefined"
                    class="z-10 -mt-6 mx-1 flex w-[72px] flex-none flex-col items-center self-start text-sm"
                    :class="auRucher ? 'font-bold text-forest' : 'font-semibold text-muted'"
                >
                    <span class="flex h-[64px] w-[64px] items-center justify-center rounded-full border-4 bg-forest text-honey shadow-nav" :class="auRucher ? 'border-honey' : 'border-white'"><Icon nom="micro" :size="28" /></span>
                    Au rucher
                </Link>
                <Link
                    :href="item.href()"
                    :aria-current="actifMobile(item) ? 'page' : undefined"
                    class="flex flex-1 flex-col items-center justify-center gap-0.5 text-sm"
                    :class="actifMobile(item) ? 'font-bold text-forest' : 'font-semibold text-muted'"
                >
                    <span :class="actifMobile(item) && 'rounded-full bg-cream px-2'"><Icon :nom="item.nom" /></span>
                    {{ item.label }}
                </Link>
            </template>
        </nav>
    </div>
</template>
