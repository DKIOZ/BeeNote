<template>
    <div>
        <dt class="text-sm font-medium text-gray-700">{{ label }}</dt>
        <dd class="mt-1">
            <span :class="getValueClass(value)" class="text-sm font-medium">
                {{ getValueLabel(value) }}
            </span>
        </dd>
    </div>
</template>

<script setup>
// Props
defineProps({
    label: String,
    value: String
});

// Fonction pour obtenir la classe CSS selon la valeur
function getValueClass(value) {
    if (value === 'non_observe') {
        return 'text-gray-400';
    }
    if (value === 'ok') {
        return 'text-green-600';
    }
    if (value === 'non_ok') {
        return 'text-red-600';
    }
    return 'text-gray-900';
}

// Fonction pour obtenir le label affiché
function getValueLabel(value) {
    const labels = {
        // États généraux
        'non_observe': 'Non observé',
        'ok': 'OK',
        'non_ok': 'Problème',
        
        // États spécifiques
        'calme': 'Calme',
        'agitee': 'Agitée',
        'agressive': 'Agressive',
        'tres_agressive': 'Très agressive',
        
        'normale': 'Normale',
        'faible': 'Faible',
        'intense': 'Intense',
        'forte': 'Forte',
        'importante': 'Importante',
        
        'normal': 'Normal',
        'anormal': 'Anormal',
        
        'suffisantes': 'Suffisantes',
        'insuffisantes': 'Insuffisantes',
        
        'regulier': 'Régulier',
        'irregulier': 'Irrégulier'
    };
    
    return labels[value] || value;
}
</script>