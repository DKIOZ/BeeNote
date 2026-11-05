<script setup>
const LISTE = [
    ['Ruches', 'ruche deux · ruche suivante · ruche terminée · fin du rucher'],
    ['Humeur', 'calme · agitée · agressive'],
    ['Force', 'colonie faible · colonie normale · colonie forte'],
    ['Reine et ponte', 'reine vue · reine pas vue · ponte vue · pas de ponte'],
    ['Cellules royales', 'cellules royales · pas de cellules'],
    ['Réserves', 'réserves bonnes · réserves faibles'],
    ['Varroas', 'varroas présents · pas de varroas'],
    ["J'ai fait", 'nourri · traité · hausse posée · hausse enlevée'],
    ['Prochaine visite', 'dans une, deux ou trois semaines'],
    ['Commandes', "pause · reprends · annule · corrige · efface · note · fin de note · aide · compris · c'est bon"],
];
</script>

<template>
    <dl class="bn-card m-0 px-3.5 py-0.5">
        <div v-for="[champ, mots] in LISTE" :key="champ" class="border-b border-hairline py-1.5 last:border-0">
            <dt class="text-sm font-semibold text-muted">{{ champ }}</dt>
            <dd class="m-0 font-semibold leading-tight">{{ mots }}</dd>
        </div>
    </dl>
</template>
