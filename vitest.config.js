import { defineConfig } from 'vitest/config';

// Configuration séparée : les tests n'ont pas besoin du plugin Laravel.
export default defineConfig({
    test: {
        include: ['resources/js/terrain/*.test.js'],
    },
});
