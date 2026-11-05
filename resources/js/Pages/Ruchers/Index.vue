<script setup>
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Comb from '@/Components/Comb.vue';
import RuchesTable from '@/Components/RuchesTable.vue';

defineProps({ ruchers: Array });
</script>

<template>
    <AppLayout title="Mes ruchers">
        <header class="flex flex-wrap items-center justify-between gap-3 px-5 pb-3.5 pt-5 lg:px-0 lg:pt-0">
            <h1 class="bn-title text-4xl">Mes ruchers</h1>
            <Link v-if="ruchers.length" :href="route('ruchers.create')" class="bn-btn hidden lg:inline-flex">Créer un rucher</Link>
        </header>

        <div class="flex flex-col gap-3 px-4 lg:hidden">
            <Link v-for="rucher in ruchers" :key="rucher.id" :href="route('ruchers.show', rucher.id)" class="bn-card block p-4">
                <span class="bn-title block text-[22px]">{{ rucher.nom }}</span>
                <span class="mt-0.5 block text-muted">
                    {{ rucher.ruches.length }} {{ rucher.ruches.length > 1 ? 'ruches' : 'ruche' }}
                    <template v-if="rucher.a_visiter"> · {{ rucher.a_visiter }} à visiter</template>
                </span>
                <Comb class="mt-2.5" :ruches="rucher.ruches" :size="48" :par-ligne="5" />
            </Link>

            <div v-if="!ruchers.length" class="bn-card p-5 text-center">
                <p class="bn-title text-[22px]">Pas encore de rucher</p>
                <p class="mt-1 text-muted">Créez le premier pour commencer.</p>
                <Link :href="route('ruchers.create')" class="bn-btn mt-3">Créer mon rucher</Link>
            </div>

            <div v-else class="grid grid-cols-2 gap-2">
                <Link :href="route('terrain') + '#scanner'" class="bn-btn-2">Scanner un QR</Link>
                <Link :href="route('ruchers.create')" class="bn-btn-2">Créer un rucher</Link>
            </div>
        </div>

        <div class="hidden flex-col gap-6 lg:flex">
            <div v-if="!ruchers.length" class="bn-card p-5 text-center">
                <p class="bn-title text-[22px]">Pas encore de rucher</p>
                <p class="mt-1 text-muted">Créez le premier pour commencer.</p>
                <Link :href="route('ruchers.create')" class="bn-btn mt-3">Créer mon rucher</Link>
            </div>

            <section v-for="rucher in ruchers" :key="rucher.id" class="bn-card overflow-hidden">
                <div class="flex flex-wrap items-center justify-between gap-3 p-5">
                    <div>
                        <h2 class="bn-title text-[22px]"><Link :href="route('ruchers.show', rucher.id)" class="hover:underline">{{ rucher.nom }}</Link></h2>
                        <p class="text-muted">{{ rucher.ruches.length }} {{ rucher.ruches.length > 1 ? 'ruches' : 'ruche' }}<template v-if="rucher.a_visiter"> · {{ rucher.a_visiter }} à visiter</template></p>
                    </div>
                    <div class="flex gap-2">
                        <Link :href="route('ruches.create', rucher.id)" class="bn-btn-2">Ajouter une ruche</Link>
                        <Link :href="route('etiquettes.index')" class="bn-btn-2">Imprimer les QR codes</Link>
                    </div>
                </div>
                <RuchesTable v-if="rucher.ruches.length" :ruches="rucher.ruches" />
            </section>
        </div>
    </AppLayout>
</template>
