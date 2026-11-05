<script setup>
import { Link } from '@inertiajs/vue3';
import Icon from '@/Components/Icon.vue';

defineProps({
    href: { type: String, required: true },
    titre: { type: String, required: true },
    sous: { type: String, default: null },
    retour: { type: String, default: 'Retour' },
});
</script>

<template>
    <header class="flex items-center gap-1 px-2 pb-2 pt-2 lg:px-0">
        <Link :href="href" :aria-label="retour" class="flex h-12 w-12 flex-none items-center justify-center text-forest">
            <Icon nom="retour" />
        </Link>
        <div class="min-w-0 flex-1">
            <h1 class="bn-title text-[28px] leading-tight lg:text-4xl">{{ titre }}</h1>
            <p v-if="sous" class="bn-sub">{{ sous }}</p>
        </div>
        <slot />
    </header>
</template>
