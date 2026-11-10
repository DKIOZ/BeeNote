<script setup>
// Le niveau terrain d'une fiche : ce qui se lit en 30 secondes, ruche ouverte.
defineProps({
    fiche: { type: Object, required: true },
});
</script>

<template>
    <div class="flex flex-col gap-3">
        <div v-if="fiche.urgent" class="flex items-start gap-2.5 rounded-2xl bg-peach px-3.5 py-3 text-rust">
            <svg aria-hidden="true" class="mt-0.5 flex-none" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3l10 18H2z" /><path d="M12 10v5M12 18h.01" /></svg>
            <p class="m-0 text-base leading-snug"><strong>Urgent</strong> {{ fiche.urgent }}</p>
        </div>

        <section class="bn-card px-4 pb-1.5 pt-3.5">
            <h2 class="bn-title mb-1.5 text-xl text-forest">Les gestes</h2>
            <ol class="m-0 flex list-none flex-col p-0">
                <li v-for="(geste, index) in fiche.gestes" :key="index" class="flex gap-3 border-b border-hairline py-2.5 last:border-0">
                    <span aria-hidden="true" class="flex h-[30px] w-[30px] flex-none items-center justify-center rounded-full bg-forest font-bold text-honey">{{ index + 1 }}</span>
                    <span class="text-[17px] leading-snug"><span class="sr-only">Étape {{ index + 1 }} : </span>{{ geste }}</span>
                </li>
            </ol>
        </section>

        <p v-if="fiche.eviter" class="m-0 mx-1 text-base leading-snug text-muted"><strong class="text-rust">À éviter :</strong> {{ fiche.eviter }}</p>
    </div>
</template>
