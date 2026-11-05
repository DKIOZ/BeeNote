<script setup>
import { Head, Link } from '@inertiajs/vue3';

const AVANTAGES = [
    { fill: '#FFB81C', titre: 'Notez en parlant', texte: 'Chaque mot agit comme un bouton. L\'appli répète ce qu\'elle a compris.' },
    { fill: '#DDEFE3', titre: 'Sachez quoi faire', texte: 'Visites à prévoir, retards et ruches à surveiller, dès l\'ouverture.' },
    { fill: '#FFF0C7', titre: 'Registre tenu tout seul', texte: 'Visites, traitements et récoltes réunis, à télécharger quand il le faut.' },
];
</script>

<template>
    <Head title="Le carnet de vos ruches" />
    <div class="min-h-screen bg-ground">
        <section class="relative overflow-hidden rounded-b-hero bg-forest px-6 pb-14 pt-6 text-white lg:px-8">
            <svg aria-hidden="true" class="absolute right-[2%] top-16 hidden w-[40%] max-w-[520px] lg:block" viewBox="0 0 420 380" fill="none" stroke="#FFB81C" stroke-opacity="0.35" stroke-width="3" stroke-linejoin="round"><path d="M120 16l80 46v92l-80 46-80-46V62z" fill="#FFB81C" fill-opacity="0.95" /><path d="M296 16l80 46v92l-80 46-80-46V62z" /><path d="M208 168l80 46v92l-80 46-80-46v-92z" /></svg>
            <div class="relative mx-auto max-w-[1180px]">
                <header class="flex flex-wrap items-center justify-between gap-3">
                    <span class="bn-title text-[28px]">BeeNote</span>
                    <Link :href="route('login')" class="flex min-h-[46px] items-center rounded-2xl bg-white/15 px-4 font-semibold hover:bg-white/25">Se connecter</Link>
                </header>
                <h1 class="bn-title mt-14 max-w-[640px] text-[clamp(40px,6vw,68px)] leading-[1.02]">Le carnet de ruches qui vous écoute, même avec les gants</h1>
                <p class="mt-5 max-w-[560px] text-lg text-[#E4ECE8]">Dites « reine vue », « hausse posée » : c'est noté. Pas besoin de toucher l'écran, pas besoin de réseau, et le toucher reste là si besoin.</p>
                <div class="mt-7"><Link :href="route('register')" class="bn-btn-flat lg:w-auto lg:px-7">Créer mon compte</Link></div>
            </div>
        </section>

        <section class="mx-auto max-w-[1180px] px-6 py-10 lg:px-8">
            <div class="grid gap-4 lg:grid-cols-3">
                <article v-for="item in AVANTAGES" :key="item.titre" class="bn-card p-6">
                    <svg aria-hidden="true" width="46" height="50" viewBox="0 0 48 52"><path d="M24 4l19 11v22L24 48 5 37V15z" :fill="item.fill" :stroke="item.fill" stroke-width="6" stroke-linejoin="round" /></svg>
                    <h2 class="bn-title mb-1.5 mt-2.5 text-[22px]">{{ item.titre }}</h2>
                    <p class="text-lg text-muted">{{ item.texte }}</p>
                </article>
            </div>
            <p class="mt-7 text-sm text-muted">
                <Link :href="route('policy.show')" class="inline-flex min-h-[44px] items-center underline">Confidentialité</Link> ·
                <Link :href="route('terms.show')" class="inline-flex min-h-[44px] items-center underline">Conditions</Link>
            </p>
        </section>
    </div>
</template>
