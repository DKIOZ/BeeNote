<script setup>
// Le niveau détaillé d'une fiche : à lire au calme. Le texte vient du code, jamais du HTML.
defineProps({
    fiche: { type: Object, required: true },
    niveau: { type: String, default: 'h2' },
});
</script>

<template>
    <div class="flex flex-col gap-5">
        <section v-for="section in fiche.detail" :key="section.titre" class="flex flex-col gap-2">
            <component :is="niveau" class="bn-title m-0 text-[22px] text-forest lg:text-2xl">{{ section.titre }}</component>
            <p v-for="(paragraphe, index) in section.texte ?? []" :key="index" class="m-0 text-[17px] leading-relaxed lg:text-lg">{{ paragraphe }}</p>
            <ul v-if="section.liste" class="m-0 flex list-none flex-col gap-2 p-0">
                <li v-for="[nom, texte] in section.liste" :key="nom" class="bn-card px-4 py-3 text-base leading-relaxed">
                    <strong class="block text-[17px] text-ink">{{ nom }}</strong>
                    <span class="text-muted">{{ texte }}</span>
                </li>
            </ul>
        </section>

        <p class="m-0 border-t border-hairline pt-3 text-sm text-muted">
            <template v-if="fiche.relu">Relue par {{ fiche.relu }}.</template>
            <template v-else>Fiche en cours de relecture par un apiculteur expérimenté. En cas de doute, demandez conseil au GDSA de votre département.</template>
        </p>
    </div>
</template>
