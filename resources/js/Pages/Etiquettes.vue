<script setup>
import { computed, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import BackBar from '@/Components/BackBar.vue';

const props = defineProps({ ruchers: Array });

const choisi = ref(props.ruchers[0]?.id ?? null);
const rucher = computed(() => props.ruchers.find((r) => r.id === choisi.value));
const imprimer = () => window.print();
</script>

<template>
    <AppLayout title="QR codes à imprimer" sans-nav>
        <div class="print:hidden">
            <BackBar :href="route('plus')" titre="QR codes à imprimer" sous="À coller sur vos ruches, à l'abri de la pluie." />
            <div v-if="ruchers.length" class="flex flex-wrap items-center justify-between gap-2 px-4 lg:px-0">
                <div role="group" aria-label="Choix du rucher" class="flex flex-wrap gap-1.5">
                    <button v-for="item in ruchers" :key="item.id" type="button" :aria-pressed="choisi === item.id" class="min-h-[46px] rounded-full px-3.5 font-semibold" :class="choisi === item.id ? 'bg-forest text-white' : 'bg-white'" @click="choisi = item.id">{{ item.nom }}</button>
                </div>
                <button type="button" class="bn-btn lg:w-auto" :disabled="!rucher" @click="imprimer">Imprimer la page</button>
            </div>
        </div>

        <div v-if="!ruchers.length" class="bn-card mx-4 mt-4 p-5 lg:mx-0">
            <p class="font-semibold">Pas encore de ruche à étiqueter.</p>
            <p class="mt-1 text-muted">Créez un rucher pour imprimer ses QR codes.</p>
            <Link :href="route('ruchers.create')" class="bn-btn mt-3">Créer mon rucher</Link>
        </div>

        <section v-if="rucher" aria-label="Aperçu de la page" class="bn-card mx-4 mb-6 mt-4 p-5 print:m-0 print:shadow-none lg:mx-0">
            <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                <div v-for="ruche in rucher.ruches" :key="ruche.id" class="flex break-inside-avoid flex-col items-center gap-1.5 rounded-2xl border-2 border-dashed border-[#CBCEC2] p-3">
                    <img :src="ruche.qr" :alt="`QR code de ${ruche.nom}`" class="aspect-square w-full max-w-[150px]">
                    <span class="bn-title text-[22px]">{{ ruche.nom }}</span>
                    <span class="text-sm text-muted">{{ rucher.nom }}</span>
                </div>
            </div>
        </section>
    </AppLayout>
</template>
