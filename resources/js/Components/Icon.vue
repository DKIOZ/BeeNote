<script setup>
defineProps({
    nom: { type: String, required: true },
    size: { type: Number, default: 24 },
});
</script>

<template>
    <svg aria-hidden="true" class="flex-none" :width="size" :height="size" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
        <template v-if="nom === 'accueil'"><path d="M12 3l8 4.6v8.8L12 21l-8-4.6V7.6z" /></template>
        <template v-else-if="nom === 'ruchers'"><path d="M12 21s7-6 7-11a7 7 0 0 0-14 0c0 5 7 11 7 11z" /><circle cx="12" cy="10" r="2.5" /></template>
        <template v-else-if="nom === 'micro'"><rect x="9" y="3" width="6" height="11" rx="3" fill="currentColor" /><path d="M5 11a7 7 0 0 0 14 0" /><path d="M12 18v3" /></template>
        <template v-else-if="nom === 'registre'"><path d="M6 3h9l4 4v14H6z" /><path d="M9 13h7" /><path d="M9 17h7" /></template>
        <template v-else-if="nom === 'plus'"><circle cx="5" cy="12" r="1.6" fill="currentColor" /><circle cx="12" cy="12" r="1.6" fill="currentColor" /><circle cx="19" cy="12" r="1.6" fill="currentColor" /></template>
        <template v-else-if="nom === 'qr'"><path d="M4 8V5a1 1 0 0 1 1-1h3" /><path d="M16 4h3a1 1 0 0 1 1 1v3" /><path d="M20 16v3a1 1 0 0 1-1 1h-3" /><path d="M8 20H5a1 1 0 0 1-1-1v-3" /><path d="M4 12h16" /></template>
        <template v-else-if="nom === 'retour'"><path d="M15 5l-7 7 7 7" /></template>
        <template v-else-if="nom === 'fermer'"><path d="M6 6l12 12" /><path d="M18 6L6 18" /></template>
        <template v-else-if="nom === 'suite'"><path d="M9 6l6 6-6 6" /></template>
        <template v-else-if="nom === 'lecture'"><path d="M8 5l11 7-11 7z" fill="currentColor" /></template>
        <template v-else-if="nom === 'coche'"><path d="M5 13l4 4L19 7" /></template>
    </svg>
</template>
