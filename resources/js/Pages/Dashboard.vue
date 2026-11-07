<script setup>
import { computed, onMounted, ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Comb from '@/Components/Comb.vue';
import Hex from '@/Components/Hex.vue';
import Icon from '@/Components/Icon.vue';
import { dateCourte, dateFr } from '@/terrain/fields.js';
import { compterNotes } from '@/terrain/fields.js';
import { getItem, pendingCount } from '@/terrain/store.js';

const props = defineProps({
    ruchers: Array,
    taches: Array,
    aCompleter: Array,
    saison: [Object, Array],
    aujourdhui: String,
});

const MOIS = ['Janv.', 'Févr.', 'Mars', 'Avril', 'Mai', 'Juin', 'Juil.', 'Août', 'Sept.', 'Oct.', 'Nov.', 'Déc.'];

const vide = computed(() => props.ruchers.length === 0);
const priorite = computed(() => [...props.ruchers].sort((a, b) => b.a_visiter - a.a_visiter)[0] ?? null);
// Les autres ruchers qui attendent une visite : on ne les cache pas derrière le premier.
const autres = computed(() => props.ruchers.filter((r) => r.id !== priorite.value?.id && r.a_visiter > 0));
const moisCourant = computed(() => Number(props.aujourdhui.slice(5, 7)));
const titre = computed(() => {
    const rucher = priorite.value;
    if (!rucher) {
        return '';
    }
    if (rucher.a_visiter === 0) {
        return `Tout est à jour : ${rucher.nom}`;
    }

    return `${rucher.a_visiter} ${rucher.a_visiter > 1 ? 'ruches' : 'ruche'} à visiter : ${rucher.nom}`;
});
// À jour ne veut pas dire en bonne santé : ce qu'on a vu la dernière fois reste affiché.
const aSurveiller = computed(() => props.ruchers.flatMap((r) => r.ruches.filter((h) => h.alertes?.length).map((h) => ({ ...h, rucher: r.nom }))));
const enCours = ref(null);

const attente = ref(0);
const voixPrete = ref(false);
const faite = ref(null);
let effacer = null;

onMounted(async () => {
    attente.value = await pendingCount().catch(() => 0);
    // Une visite laissée à moitié se reprend dès l'accueil.
    const brouillon = await getItem('brouillon').catch(() => null);
    if (brouillon && compterNotes(brouillon.valeurs)) {
        enCours.value = brouillon;
    }
    try {
        voixPrete.value = localStorage.getItem('bn-voix-prete') === '1';
    } catch {
        voixPrete.value = false;
    }

    // « Prêt » seulement si le micro est vraiment autorisé.
    try {
        const micro = await navigator.permissions?.query({ name: 'microphone' });
        if (micro && micro.state !== 'granted') {
            voixPrete.value = false;
        }
    } catch {
        // Navigateur sans cette question : on garde l'indication du dernier essai.
    }
});

// Une coche ratée avec des gants se rattrape : « Annuler » reste quelques secondes.
const cocher = (tache) => router.patch(route('taches.toggle', tache.id), {}, {
    preserveScroll: true,
    onSuccess: () => {
        faite.value = tache;
        clearTimeout(effacer);
        effacer = setTimeout(() => { faite.value = null; }, 8000);
    },
});
const annuler = () => {
    const tache = faite.value;
    faite.value = null;
    router.patch(route('taches.toggle', tache.id), {}, { preserveScroll: true });
};
</script>

<template>
    <AppLayout title="Accueil">
        <template v-if="vide">
            <section class="rounded-b-hero bg-forest px-6 pb-7 pt-10 text-white lg:rounded-hero">
                <svg aria-hidden="true" class="mb-4" width="180" height="120" viewBox="0 0 180 120" fill="none" stroke="#FFB81C" stroke-width="3" stroke-linejoin="round" stroke-dasharray="6 6"><path d="M50 8l36 21v42L50 92 14 71V29z" /><path d="M130 8l36 21v42l-36 21-36-21V29z" /></svg>
                <h1 class="bn-title text-4xl leading-tight">Vos ruches vous attendent ici</h1>
                <p class="mt-2 text-lg text-sage">Créez votre rucher en moins d'une minute.</p>
                <Link :href="route('ruchers.create')" class="bn-btn-flat mt-5">Créer mon rucher</Link>
            </section>
            <ol class="mx-4 mt-5 flex flex-col gap-2.5 lg:mx-0">
                <li v-for="(etape, index) in ['Créez votre rucher et ses ruches', 'Préparez le téléphone pour le rucher', 'Notez votre première visite']" :key="etape" class="bn-card flex min-h-[64px] items-center gap-3 px-3.5 py-2 font-semibold">
                    <Hex etat="todo" :mark="index + 1" :size="38" />
                    {{ etape }}
                </li>
            </ol>
        </template>

        <div v-else class="lg:flex lg:flex-col lg:gap-6">
            <header class="hidden items-end justify-between gap-6 lg:flex">
                <div>
                    <p class="text-muted">{{ dateFr(aujourdhui) }}</p>
                    <h1 class="bn-title mt-1 text-4xl leading-tight">{{ titre }}</h1>
                    <p class="mt-1 text-muted">Les visites se notent sur le téléphone, au rucher. Ici, vous tenez le registre et préparez la saison.</p>
                </div>
                <div class="flex flex-none items-center gap-3">
                    <Link v-if="attente" :href="route('terrain') + '#synchro'" class="bn-btn-2">{{ attente }} à envoyer</Link>
                    <Link :href="route('visites.create')" class="bn-btn">Noter une visite</Link>
                </div>
            </header>

            <section class="rounded-b-hero bg-forest px-5 pb-6 pt-3 text-white lg:hidden">
                <header class="flex items-center justify-between lg:hidden">
                    <span class="bn-title text-[22px]">BeeNote</span>
                    <Link v-if="attente" :href="route('terrain') + '#synchro'" class="flex min-h-[44px] items-center gap-2 rounded-full bg-white/15 px-3.5 text-sm font-semibold">
                        <span aria-hidden="true" class="h-2 w-2 rounded-full bg-honey" />
                        {{ attente }} à envoyer
                    </Link>
                </header>
                <div>
                    <div class="min-w-0">
                        <p class="mt-4 text-sage">{{ dateFr(aujourdhui) }}</p>
                        <h1 class="bn-title mt-1 text-4xl leading-[1.05]">{{ titre }}</h1>
                        <Comb class="mt-3.5" :ruches="priorite.ruches" :max="8" :size="58" :par-ligne="4" sur-vert />
                    </div>
                    <div class="mt-4">
                        <Link :href="route('terrain', { rucher: priorite.id })" class="bn-btn-flat">Visiter le rucher</Link>
                    </div>
                </div>
            </section>

            <Link v-if="enCours" :href="route('terrain')" class="mx-4 mt-3 flex min-h-[60px] items-center justify-between gap-3 rounded-card bg-cream px-4 py-2 text-cream-ink lg:mx-0">
                <span>
                    <span class="block font-semibold">Visite en cours : {{ enCours.nom ?? 'une ruche' }}</span>
                    <span class="block text-sm">{{ compterNotes(enCours.valeurs) }} points notés, rien n'est perdu</span>
                </span>
                <span class="font-bold">Reprendre</span>
            </Link>

            <section v-if="aSurveiller.length" class="mx-4 mt-3 rounded-card bg-peach p-4 text-rust lg:mx-0">
                <h2 class="font-bold">À surveiller</h2>
                <Link v-for="ruche in aSurveiller" :key="ruche.id" :href="route('ruches.show', ruche.id)" class="mt-1 flex min-h-[44px] items-center justify-between gap-2">
                    <span><strong>{{ ruche.nom }}</strong> · {{ ruche.alertes.join(', ') }}</span>
                    <Icon nom="suite" :size="18" />
                </Link>
            </section>

            <section v-if="autres.length" class="mx-4 mt-3 flex flex-col gap-2 lg:hidden">
                <Link v-for="rucher in autres" :key="rucher.id" :href="route('terrain', { rucher: rucher.id })" class="bn-card flex min-h-[60px] items-center justify-between gap-3 px-4 py-2">
                    <span>
                        <span class="block font-semibold">{{ rucher.nom }}</span>
                        <span class="block text-sm text-muted">{{ rucher.a_visiter }} {{ rucher.a_visiter > 1 ? 'ruches à visiter' : 'ruche à visiter' }}</span>
                    </span>
                    <Icon nom="suite" :size="20" />
                </Link>
            </section>

            <p v-if="faite" role="status" class="fixed bottom-24 left-4 right-4 z-50 flex items-center justify-between gap-3 rounded-2xl bg-forest px-4 py-2 text-white shadow-nav lg:bottom-6 lg:left-auto lg:w-96">
                <span class="min-w-0 truncate">« {{ faite.titre }} » est faite</span>
                <button type="button" class="min-h-[48px] flex-none px-2 font-bold text-honey underline" @click="annuler">Annuler</button>
            </p>

            <Link v-if="!voixPrete" :href="route('demarrage')" class="mx-5 mt-3 flex min-h-[44px] items-center gap-2 font-semibold text-forest underline lg:hidden">
                <Icon nom="suite" :size="20" />
                Préparer le téléphone pour le rucher
            </Link>
            <Link v-else :href="route('demarrage')" class="mx-5 mt-3 flex min-h-[44px] items-center gap-2 font-semibold text-mint-ink lg:hidden">
                <Icon nom="coche" :size="20" />
                Prêt pour le rucher : voix disponible
            </Link>

            <section aria-label="Frise de la saison" class="bn-card hidden p-5 lg:block">
                <ol class="grid grid-cols-12 gap-1.5">
                    <li v-for="(mois, index) in MOIS" :key="mois" class="min-h-[112px] rounded-xl p-2 text-sm" :class="index + 1 === moisCourant ? 'bg-forest text-white' : 'bg-ground'">
                        <span class="block font-semibold">{{ mois }}</span>
                        <span v-if="saison?.[index + 1]" class="mt-1 block">{{ saison[index + 1] }} {{ saison[index + 1] > 1 ? 'visites' : 'visite' }}</span>
                    </li>
                </ol>
            </section>

            <div class="lg:grid lg:grid-cols-2 lg:gap-6">
                <section class="px-4 pt-2 lg:bn-card lg:p-5">
                    <div class="mb-2 ml-1 flex items-center justify-between lg:ml-0">
                        <h2 class="bn-title text-[22px]">À faire</h2>
                        <Link :href="route('taches.create')" class="flex min-h-[44px] items-center px-1 font-semibold text-forest">Ajouter</Link>
                    </div>

                    <div class="flex flex-col gap-2">
                        <Link v-for="item in aCompleter" :key="item.visite_id" :href="route('noter.create', { type: 'traitement', ruche: item.ruche_id, visite: item.visite_id })" class="flex min-h-[64px] items-center justify-between gap-2 rounded-card bg-cream px-4 py-2">
                            <span>
                                <span class="block font-semibold">Compléter le traitement</span>
                                <span class="block text-sm text-cream-ink">{{ item.ruche }} · {{ dateCourte(item.date) }}</span>
                            </span>
                            <Icon nom="suite" :size="20" />
                        </Link>

                        <div v-for="tache in taches" :key="tache.id" class="bn-card flex min-h-[64px] items-center gap-2 py-1 pl-1 pr-3.5 lg:bg-ground lg:shadow-none">
                            <button type="button" class="flex h-14 w-14 flex-none items-center justify-center" :aria-label="`Marquer comme fait : ${tache.titre}`" @click="cocher(tache)">
                                <span class="block h-[30px] w-[30px] rounded-[10px] border-2 border-line bg-white" />
                            </button>
                            <component :is="tache.ruche_id ? Link : 'span'" :href="tache.ruche_id ? route('ruches.show', tache.ruche_id) : undefined" class="min-w-0 flex-1">
                                <span class="block font-semibold">{{ tache.titre }}</span>
                                <span class="block text-sm text-muted">{{ tache.ou }}</span>
                            </component>
                            <span class="bn-badge" :class="tache.retard ? 'bg-peach text-rust' : 'bg-soft text-muted'">{{ tache.retard ? 'En retard' : dateCourte(tache.date_prevue) }}</span>
                        </div>

                        <p v-if="!taches.length && !aCompleter.length" class="px-1 text-muted">Rien à faire pour l'instant.</p>
                    </div>
                </section>

                <section class="hidden lg:block lg:bn-card lg:p-5">
                    <h2 class="bn-title mb-3 text-[22px]">Vos ruchers</h2>
                    <div class="flex flex-col gap-4">
                        <Link v-for="rucher in ruchers" :key="rucher.id" :href="route('ruchers.show', rucher.id)" class="block rounded-2xl bg-ground p-3.5 hover:brightness-[.98]">
                            <span class="flex justify-between gap-2 font-semibold">
                                {{ rucher.nom }}
                                <span class="text-sm font-normal text-muted">{{ rucher.ruches.length }} ruches</span>
                            </span>
                            <Comb class="mt-2.5" :ruches="rucher.ruches" :size="44" :par-ligne="8" />
                        </Link>
                    </div>
                </section>
            </div>
        </div>
    </AppLayout>
</template>
