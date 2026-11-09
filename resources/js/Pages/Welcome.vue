<script setup>
import { Head, Link } from '@inertiajs/vue3';
import Comb from '@/Components/Comb.vue';
import DemoVoix from '@/Components/Welcome/DemoVoix.vue';

const PROBLEMES = [
    { titre: 'Le carnet reste dans la voiture', texte: 'Et le soir, impossible de se rappeler si la reine était là dans la ruche 7.' },
    { titre: 'Les gants pleins de propolis', texte: 'Taper sur un écran ? Il faudrait tout poser, enfumoir compris.' },
    { titre: 'Le registre à recopier', texte: 'Visites, traitements, récoltes : des soirées entières avant un contrôle.' },
];

const ETAPES = [
    { titre: 'Touchez le micro', texte: 'Un seul geste en arrivant devant la ruche. Ensuite, plus besoin de l\'écran.' },
    { titre: 'Parlez comme d\'habitude', texte: '« Reine vue », « hausse posée », « ruche suivante ». Chaque mot-clé vaut un bouton.' },
    { titre: 'Vérifiez, c\'est noté', texte: 'BeeNote répète ce qu\'il a compris. Une erreur ? Dites « annule ».' },
];

const RUCHER = [
    { id: 1, numero: '1', etat: 'done', nom: 'Ruche 1' },
    { id: 2, numero: '2', etat: 'done', nom: 'Ruche 2' },
    { id: 3, numero: '3', etat: 'late', nom: 'Ruche 3' },
    { id: 4, numero: '4', etat: 'todo', nom: 'Ruche 4' },
    { id: 5, numero: '5', etat: 'todo', nom: 'Ruche 5' },
    { id: 6, numero: '6', etat: 'done', nom: 'Ruche 6' },
    { id: 7, numero: '7', etat: 'next', nom: 'Ruche 7' },
    { id: 8, numero: '8', etat: 'next', nom: 'Ruche 8' },
];

const ATOUTS = [
    { icone: 'todo', titre: 'Sachez quoi faire en arrivant', texte: 'Visites à prévoir, ruches en retard, tâches à cocher : tout est là dès l\'ouverture.' },
    { icone: 'registre', titre: 'Le registre se tient tout seul', texte: 'Visites, traitements, nourrissements et récoltes classés par rucher, en PDF ou CSV.' },
    { icone: 'reseau', titre: 'Pas de réseau ? Aucun souci', texte: 'Vos visites restent sur le téléphone et partent dès que vous retrouvez du réseau.' },
    { icone: 'qr', titre: 'Chaque ruche a sa fiche', texte: 'Un QR code collé sur la ruche : un scan, et ses visites, emplacements et transhumances s\'affichent.' },
];

const QUESTIONS = [
    { q: 'Et si la voix ne me comprend pas ?', r: 'Tout se fait aussi au toucher, avec de grands boutons pensés pour les gants. La voix est un raccourci, jamais une obligation.' },
    { q: 'Faut-il parler en phrases ?', r: 'Non. BeeNote reconnaît des mots-clés courts, comme « reine vue » ou « pas de varroas ». La liste complète est consultable dans l\'appli.' },
    { q: 'Ça marche au fond d\'un vallon sans réseau ?', r: 'Oui pour la tournée : la visite est gardée sur le téléphone, puis envoyée au retour.' },
    { q: 'Sur quel appareil ?', r: 'Sur votre téléphone au rucher, et sur ordinateur pour préparer la saison ou imprimer le registre. Rien à installer depuis un store.' },
];
</script>

<template>
    <Head title="Le carnet de rucher qui prend les notes à votre place" />
    <div class="min-h-screen overflow-x-hidden bg-ground">
        <!-- Accroche -->
        <section class="bn-safe-pt relative overflow-hidden rounded-b-hero bg-forest text-white">
            <svg aria-hidden="true" class="pointer-events-none absolute -left-24 -top-10 w-[420px] opacity-[0.07]" viewBox="0 0 200 200" fill="none" stroke="#FFB81C" stroke-width="3"><path d="M50 10l35 20v40L50 90 15 70V30z" /><path d="M120 10l35 20v40l-35 20-35-20V30z" /><path d="M85 70l35 20v40l-35 20-35-20V90z" /><path d="M155 70l35 20v40l-35 20-35-20V90z" /></svg>
            <div class="relative mx-auto max-w-[1180px] px-5 pb-16 pt-5 lg:px-8 lg:pb-24">
                <header class="flex items-center justify-between gap-3">
                    <span class="bn-title flex items-center gap-2 text-[26px]">
                        <svg aria-hidden="true" width="26" height="28" viewBox="0 0 48 52"><path d="M24 4l19 11v22L24 48 5 37V15z" fill="#FFB81C" stroke="#FFB81C" stroke-width="6" stroke-linejoin="round" /></svg>
                        BeeNote
                    </span>
                    <nav class="flex items-center gap-2">
                        <Link :href="route('login')" class="flex min-h-[46px] items-center rounded-2xl bg-white/15 px-4 font-semibold hover:bg-white/25">Se connecter</Link>
                        <Link :href="route('register')" class="hidden min-h-[46px] items-center rounded-2xl bg-honey px-4 font-bold text-forest hover:brightness-95 sm:flex">Créer un compte</Link>
                    </nav>
                </header>

                <div class="mt-12 grid items-center gap-14 lg:mt-16 lg:grid-cols-[1.1fr_0.9fr]">
                    <div>
                        <p class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3.5 py-1.5 text-sm font-semibold text-sage-light">
                            <span class="h-2 w-2 rounded-full bg-honey"></span>
                            Le carnet de rucher à la voix
                        </p>
                        <h1 class="bn-title mt-5 text-[clamp(40px,6.4vw,72px)] leading-[1.02]">
                            Visitez vos ruches.<br />
                            <span class="text-honey">BeeNote prend les notes.</span>
                        </h1>
                        <p class="mt-6 max-w-[540px] text-lg leading-relaxed text-sage-light lg:text-xl">
                            Les gants aux mains, l'enfumoir dans l'autre : dites « reine vue », « hausse posée », c'est noté. En fin de tournée, vous savez quoi faire ensuite et votre registre est déjà à jour.
                        </p>
                        <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                            <Link :href="route('register')" class="bn-btn sm:w-auto sm:px-8">Essayer BeeNote</Link>
                            <a href="#comment" class="inline-flex min-h-[60px] items-center justify-center rounded-[18px] border-[1.5px] border-white/30 px-6 font-semibold hover:bg-white/10 lg:min-h-[48px] lg:rounded-[14px]">Voir comment ça marche</a>
                        </div>
                        <ul class="mt-8 flex flex-wrap gap-x-5 gap-y-2 text-sm text-sage">
                            <li class="flex items-center gap-1.5"><span class="text-honey">✓</span> Téléphone et ordinateur</li>
                            <li class="flex items-center gap-1.5"><span class="text-honey">✓</span> Tournée sans réseau</li>
                            <li class="flex items-center gap-1.5"><span class="text-honey">✓</span> Le toucher toujours possible</li>
                        </ul>
                    </div>
                    <DemoVoix />
                </div>
            </div>
        </section>

        <!-- Le problème -->
        <section class="mx-auto max-w-[1180px] px-5 py-16 lg:px-8 lg:py-24">
            <p class="text-sm font-bold uppercase tracking-wider text-late">Vous connaissez ça ?</p>
            <h2 class="bn-title mt-2 max-w-[720px] text-[clamp(30px,4vw,44px)] leading-tight text-forest">Au rucher, les mains sont prises. Les notes, elles, passent à la trappe.</h2>
            <div class="mt-10 grid gap-4 md:grid-cols-3">
                <article v-for="(item, i) in PROBLEMES" :key="item.titre" class="rounded-card border-[1.5px] border-hairline bg-white/60 p-6">
                    <span class="bn-title text-4xl text-honey-deep">0{{ i + 1 }}</span>
                    <h3 class="bn-title mt-3 text-xl text-forest">{{ item.titre }}</h3>
                    <p class="mt-1.5 text-muted">{{ item.texte }}</p>
                </article>
            </div>
        </section>

        <!-- Comment ça marche -->
        <section id="comment" class="scroll-mt-6 bg-cream/60 py-16 lg:py-24">
            <div class="mx-auto max-w-[1180px] px-5 lg:px-8">
                <p class="text-sm font-bold uppercase tracking-wider text-cream-ink">Comment ça marche</p>
                <h2 class="bn-title mt-2 max-w-[720px] text-[clamp(30px,4vw,44px)] leading-tight text-forest">Trois gestes, et vos mains restent pour les abeilles.</h2>
                <ol class="mt-10 grid gap-4 md:grid-cols-3">
                    <li v-for="(etape, i) in ETAPES" :key="etape.titre" class="bn-card relative p-6 pt-8">
                        <span class="bn-title absolute -top-5 left-6 flex h-11 w-11 items-center justify-center rounded-full bg-forest text-xl text-honey">{{ i + 1 }}</span>
                        <h3 class="bn-title text-xl text-forest">{{ etape.titre }}</h3>
                        <p class="mt-1.5 text-muted">{{ etape.texte }}</p>
                    </li>
                </ol>
            </div>
        </section>

        <!-- Les atouts -->
        <section class="mx-auto max-w-[1180px] px-5 py-16 lg:px-8 lg:py-24">
            <p class="text-sm font-bold uppercase tracking-wider text-mint-ink">Tout le rucher dans la poche</p>
            <h2 class="bn-title mt-2 max-w-[720px] text-[clamp(30px,4vw,44px)] leading-tight text-forest">Moins de paperasse, plus de temps au rucher.</h2>

            <div class="mt-10 grid gap-4 lg:grid-cols-3">
                <article class="relative overflow-hidden rounded-card bg-forest p-7 text-white lg:row-span-2">
                    <h3 class="bn-title text-2xl">Votre rucher d'un coup d'œil</h3>
                    <p class="mt-2 text-sage-light">Chaque alvéole est une ruche. Vous voyez tout de suite celles qui sont faites, à faire ou en retard.</p>
                    <div class="mt-8 flex justify-center"><Comb :ruches="RUCHER" :size="62" :par-ligne="4" sur-vert /></div>
                    <ul class="mt-8 flex flex-wrap gap-x-4 gap-y-2 text-sm text-sage-light">
                        <li class="flex items-center gap-1.5"><span class="h-3 w-3 rounded-full bg-mint"></span> Faite</li>
                        <li class="flex items-center gap-1.5"><span class="h-3 w-3 rounded-full bg-honey"></span> À faire</li>
                        <li class="flex items-center gap-1.5"><span class="h-3 w-3 rounded-full border-2 border-white bg-late"></span> En retard</li>
                    </ul>
                </article>
                <article v-for="atout in ATOUTS" :key="atout.titre" class="bn-card p-6">
                    <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-cream text-forest">
                        <svg aria-hidden="true" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <template v-if="atout.icone === 'todo'"><path d="M9 6h11" /><path d="M9 12h11" /><path d="M9 18h11" /><path d="M4 6l1 1 2-2" /><path d="M4 12l1 1 2-2" /><circle cx="5" cy="18" r="1" /></template>
                            <template v-else-if="atout.icone === 'registre'"><path d="M6 3h9l4 4v14H6z" /><path d="M9 13h7" /><path d="M9 17h7" /></template>
                            <template v-else-if="atout.icone === 'reseau'"><path d="M2 8.5a15 15 0 0 1 20 0" /><path d="M5.5 12a10 10 0 0 1 13 0" /><path d="M9 15.5a5 5 0 0 1 6 0" /><circle cx="12" cy="19" r="1" fill="currentColor" /></template>
                            <template v-else><rect x="4" y="4" width="6" height="6" rx="1" /><rect x="14" y="4" width="6" height="6" rx="1" /><rect x="4" y="14" width="6" height="6" rx="1" /><path d="M14 14h2v2h-2zM18 18h2v2h-2zM14 18h2M18 14h2" /></template>
                        </svg>
                    </span>
                    <h3 class="bn-title mt-4 text-xl text-forest">{{ atout.titre }}</h3>
                    <p class="mt-1.5 text-muted">{{ atout.texte }}</p>
                </article>
            </div>
        </section>

        <!-- Questions -->
        <section class="mx-auto max-w-[860px] px-5 pb-16 lg:px-8 lg:pb-24">
            <h2 class="bn-title text-center text-[clamp(28px,3.6vw,40px)] text-forest">Vos questions</h2>
            <div class="mt-8 space-y-3">
                <details v-for="item in QUESTIONS" :key="item.q" class="group bn-card px-6 py-1">
                    <summary class="flex min-h-[60px] cursor-pointer list-none items-center justify-between gap-4 text-lg font-semibold text-forest">
                        {{ item.q }}
                        <span aria-hidden="true" class="flex h-8 w-8 flex-none items-center justify-center rounded-full bg-ground text-xl transition-transform group-open:rotate-45">+</span>
                    </summary>
                    <p class="pb-5 text-muted">{{ item.r }}</p>
                </details>
            </div>
        </section>

        <!-- Appel final -->
        <section class="px-5 pb-10 lg:px-8">
            <div class="relative mx-auto max-w-[1180px] overflow-hidden rounded-hero bg-honey px-6 py-14 text-center text-forest lg:py-20">
                <svg aria-hidden="true" class="pointer-events-none absolute -right-10 -top-10 w-[260px] opacity-20" viewBox="0 0 48 52"><path d="M24 4l19 11v22L24 48 5 37V15z" fill="none" stroke="#17382C" stroke-width="2" /></svg>
                <h2 class="bn-title relative mx-auto max-w-[640px] text-[clamp(30px,4.4vw,50px)] leading-tight">Votre prochaine visite, les mains libres.</h2>
                <p class="relative mx-auto mt-4 max-w-[520px] text-lg text-forest/80">Créez votre compte, ajoutez votre premier rucher et lancez la tournée en quelques minutes.</p>
                <Link :href="route('register')" class="relative mt-8 inline-flex min-h-[60px] items-center justify-center rounded-[18px] bg-forest px-8 text-lg font-bold text-white hover:bg-forest-deep lg:min-h-[52px]">Créer mon compte</Link>
            </div>
        </section>

        <footer class="mx-auto flex max-w-[1180px] flex-wrap items-center justify-between gap-3 px-5 pb-10 text-sm text-muted lg:px-8">
            <span class="bn-title text-lg text-forest">BeeNote</span>
            <span>
                <Link :href="route('policy.show')" class="inline-flex min-h-[44px] items-center underline">Confidentialité</Link> ·
                <Link :href="route('terms.show')" class="inline-flex min-h-[44px] items-center underline">Conditions</Link>
            </span>
        </footer>
    </div>
</template>
