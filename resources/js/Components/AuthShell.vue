<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    titre: { type: String, required: true },
    tete: { type: String, default: null },
});
</script>

<template>
    <Head :title="titre" />
    <div class="min-h-screen bg-ground lg:flex">
        <section class="relative overflow-hidden rounded-b-hero bg-forest px-6 pb-8 pt-10 text-white lg:flex-1 lg:rounded-none lg:p-12">
            <svg aria-hidden="true" class="absolute -bottom-10 -right-10 h-auto w-[130px] lg:bottom-10 lg:left-[10%] lg:right-auto lg:w-[70%]" width="240" height="220" viewBox="0 0 420 380" fill="none" stroke="#FFB81C" stroke-opacity="0.35" stroke-width="3" stroke-linejoin="round"><path d="M120 16l80 46v92l-80 46-80-46V62z" fill="#FFB81C" /><path d="M296 16l80 46v92l-80 46-80-46V62z" /><path d="M208 168l80 46v92l-80 46-80-46v-92z" /></svg>
            <Link :href="route('home')" class="bn-title relative text-4xl">BeeNote</Link>
            <p class="relative mt-2 max-w-[220px] text-lg text-[#E4ECE8] lg:max-w-[380px] lg:text-[22px]">Le carnet de vos ruches, qui vous écoute même avec les gants.</p>
            <p class="relative mt-6 hidden max-w-[380px] text-sage lg:block">Retrouvez vos ruches sur grand écran : tournées, registre et QR codes à imprimer.</p>
        </section>

        <main class="flex items-start justify-center px-4 py-6 lg:flex-1 lg:items-center">
            <div class="w-full max-w-[420px]">
                <h1 class="bn-title mb-4 text-[28px] leading-tight">{{ tete ?? titre }}</h1>
                <slot />
            </div>
        </main>
    </div>
</template>
