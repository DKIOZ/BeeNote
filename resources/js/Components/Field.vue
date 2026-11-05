<script setup>
const model = defineModel();

defineProps({
    label: { type: String, required: true },
    id: { type: String, required: true },
    erreur: { type: String, default: null },
    type: { type: String, default: 'text' },
    placeholder: { type: String, default: null },
    autocomplete: { type: String, default: null },
    inputmode: { type: String, default: null },
    min: { type: [String, Number], default: null },
    max: { type: [String, Number], default: null },
    step: { type: [String, Number], default: null },
    required: { type: Boolean, default: false },
    options: { type: Array, default: null }, // [{ value, text }]
});
</script>

<template>
    <div>
        <label :for="id" class="bn-label">{{ label }}</label>
        <select v-if="options" :id="id" v-model="model" class="bn-input" :aria-invalid="!!erreur" :aria-describedby="erreur ? `${id}-erreur` : null">
            <option v-for="option in options" :key="option.value" :value="option.value">{{ option.text }}</option>
        </select>
        <textarea v-else-if="type === 'textarea'" :id="id" v-model="model" rows="3" class="bn-input py-3" :placeholder="placeholder" :aria-invalid="!!erreur" :aria-describedby="erreur ? `${id}-erreur` : null" />
        <input
            v-else
            :id="id"
            v-model="model"
            :type="type"
            class="bn-input"
            :placeholder="placeholder"
            :autocomplete="autocomplete"
            :inputmode="inputmode"
            :min="min"
            :max="max"
            :step="step"
            :required="required"
            :aria-invalid="!!erreur"
            :aria-describedby="erreur ? `${id}-erreur` : null"
        >
        <p v-if="erreur" :id="`${id}-erreur`" role="alert" class="mt-1 text-sm font-semibold text-rust">{{ erreur }}</p>
    </div>
</template>
