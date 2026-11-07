<script setup>
import { computed, ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
import ConfirmSheet from '@/Components/ConfirmSheet.vue';
import Icon from '@/Components/Icon.vue';
import VoixProbleme from '@/Components/terrain/VoixProbleme.vue';
import { ACTIONS, FAMILLES, PROCHAINES, dateFr } from '@/terrain/fields.js';
import { useTerrain } from '@/terrain/useTerrain.js';

const page = usePage();
const { state, ruche, rucher, ruchesDuRucher, aller, noter, effacerGroupe, ecouter, arreterEcoute, quitterVisite, abandonnerVisite } = useTerrain();

const quitter = ref(false);
const abandon = ref(false);
const valeurs = computed(() => state.visite?.valeurs ?? {});
const voix = computed(() => state.voix);
const voixActive = computed(() => {
    try {
        return localStorage.getItem('bn-voix') !== '0';
    } catch {
        return true;
    }
});

const TOTAL = 9;
const nbNotes = computed(() => {
    const champs = FAMILLES.flatMap((f) => f.groupes.map((g) => g.id));
    let total = champs.filter((c) => valeurs.value[c]).length;
    if (ACTIONS.some((a) => valeurs.value[a.id])) total += 1;
    if (valeurs.value.prochaine_visite_prevue) total += 1;

    return total;
});

const position = computed(() => ruchesDuRucher.value.findIndex((r) => r.id === state.visite?.ruche_id) + 1);
const rappel = computed(() => ruche.value?.taches?.[0]?.titre ?? null);

const statut = computed(() => {
    const v = voix.value;
    if (v.chargement) return { titre: 'Préparation…', texte: 'La voix se charge.', alerte: false };
    if (v.pause) return { titre: 'En pause', texte: 'Dites « reprends » ou touchez le micro.', alerte: false };
    if (v.ecoute && v.rate) return { titre: 'Pas compris', texte: `« ${v.entendu} ». Redites le mot.`, alerte: true };
    if (v.echo) return { titre: v.ecoute ? 'J\'écoute' : 'Noté', texte: `« ${v.echo} »`, alerte: false };
    if (v.ecoute) return { titre: 'J\'écoute', texte: 'Dites ce qui est écrit sur les boutons.', alerte: false };

    return { titre: 'Écoute coupée', texte: 'Activez le micro, ou choisissez sur les boutons.', alerte: false };
});

const estNote = (groupe, option) => valeurs.value[groupe.id] === option.value;
const estDernier = (cle) => voix.value.dernier === cle;
const prochaineChoisie = (p) => valeurs.value._semaines === p.semaines;

const choisir = (groupe, option) => noter({ type: 'choix', champ: groupe.id, valeur: option.value, texte: option.text });
const faire = (action) => noter({ type: 'action', champ: action.id, texte: action.text });
const planifier = (p) => noter({ type: 'prochaine', semaines: p.semaines, texte: p.text });

const basculer = () => {
    if (voix.value.ecoute) {
        arreterEcoute();
    } else {
        ecouter(page.props.voiceModelUrl);
    }
};

const actionsNotees = computed(() => ACTIONS.some((a) => valeurs.value[a.id]));
</script>

<template>
    <div v-if="state.visite" class="pb-28">
        <section class="rounded-b-hero bg-forest px-4 pb-4 pt-2 text-white lg:rounded-hero">
            <header class="-ml-2 flex items-center gap-2">
                <button type="button" aria-label="Quitter la visite" class="flex h-12 w-12 flex-none items-center justify-center" @click="quitter = true"><Icon nom="retour" /></button>
                <div class="min-w-0 flex-1">
                    <h1 class="bn-title text-[28px] leading-tight">{{ ruche?.nom ?? 'Ruche' }}</h1>
                    <p class="text-sage">
                        <template v-if="state.visite.apres_coup">{{ dateFr(state.visite.date_visite) }}</template>
                        <template v-else-if="position">{{ position }} sur {{ ruchesDuRucher.length }}</template>
                        <template v-if="rappel"> · À faire : {{ rappel }}</template>
                    </p>
                </div>
            </header>

            <div v-if="voixActive" class="mt-3 flex items-center gap-4">
                <div class="relative flex h-[84px] w-[84px] flex-none items-center justify-center">
                    <span v-if="voix.ecoute && !voix.pause" aria-hidden="true" class="absolute h-[76px] w-[76px] animate-bn-ring rounded-full bg-honey" />
                    <button type="button" :aria-pressed="voix.ecoute" :aria-label="voix.ecoute ? 'Arrêter l\'écoute' : 'Commencer à écouter'" :disabled="voix.chargement" class="relative flex h-20 w-20 items-center justify-center rounded-full bg-honey text-forest" @click="basculer">
                        <Icon nom="micro" :size="36" />
                    </button>
                </div>
                <div class="min-w-0 flex-1" aria-live="polite">
                    <p class="bn-title text-[22px] leading-tight">{{ statut.titre }}</p>
                    <p class="mt-1 break-words" :class="statut.alerte ? 'text-[#FFD9BF]' : 'text-sage'">{{ statut.texte }}</p>
                </div>
            </div>

            <div role="img" :aria-label="`${nbNotes} sur ${TOTAL} notés`" class="mt-3 flex gap-0.5">
                <svg v-for="n in TOTAL" :key="n" aria-hidden="true" width="32" height="35" viewBox="0 0 48 52"><path d="M24 4l19 11v22L24 48 5 37V15z" class="transition-colors duration-300" :fill="n <= nbNotes ? '#FFB81C' : 'transparent'" :stroke="n <= nbNotes ? '#FFB81C' : '#C9D8D0'" stroke-width="4" stroke-linejoin="round" /></svg>
            </div>
        </section>

        <section v-if="voix.echecs >= 2 && voix.ecoute" class="mx-4 mt-3 rounded-card bg-peach p-3.5 lg:mx-0">
            <p class="mb-2.5 font-bold text-rust">La voix passe mal ici</p>
            <div class="grid grid-cols-2 gap-2">
                <button type="button" class="bn-btn-2" @click="arreterEcoute">Continuer au toucher</button>
                <button type="button" class="bn-btn-2" @click="arreterEcoute(); quitterVisite()">Noter au retour</button>
            </div>
        </section>

        <VoixProbleme class="mx-4 mt-3 lg:mx-0" />

        <div class="flex flex-col gap-7 px-4 pt-4 lg:px-0">
            <section v-for="famille in FAMILLES" :key="famille.titre">
                <h2 class="bn-title mb-2.5 text-[22px]">{{ famille.titre }}</h2>
                <div class="flex flex-col gap-3.5">
                    <fieldset v-for="groupe in famille.groupes" :key="groupe.id" :aria-labelledby="`g-${groupe.id}`" class="m-0 border-0 p-0">
                        <div class="mb-1 flex min-h-[28px] items-center justify-between">
                            <span :id="`g-${groupe.id}`" class="text-sm font-semibold text-muted">{{ groupe.label }}</span>
                            <button v-if="valeurs[groupe.id]" type="button" class="-my-2 min-h-[44px] min-w-[76px] text-sm font-semibold text-muted underline" @click="effacerGroupe([groupe.id])">Effacer</button>
                            <span v-else class="text-sm text-muted">Pas noté</span>
                        </div>
                        <div class="grid gap-2" :class="groupe.options.length === 3 ? 'grid-cols-3' : 'grid-cols-2'">
                            <button
                                v-for="option in groupe.options"
                                :key="option.value"
                                type="button"
                                :aria-pressed="estNote(groupe, option)"
                                class="bn-choix"
                                :class="{ 'bn-choix-on': estNote(groupe, option), 'bn-choix-frais': estDernier(`${groupe.id}:${option.value}`) }"
                                @click="choisir(groupe, option)"
                            >{{ option.text }}</button>
                        </div>
                    </fieldset>
                </div>
            </section>

            <section>
                <h2 class="bn-title mb-2.5 text-[22px]">Actions</h2>
                <fieldset aria-labelledby="g-actions" class="m-0 border-0 p-0">
                    <div class="mb-1 flex min-h-[28px] items-center justify-between">
                        <span id="g-actions" class="text-sm font-semibold text-muted">J'ai fait</span>
                        <button v-if="actionsNotees" type="button" class="-my-2 min-h-[44px] min-w-[76px] text-sm font-semibold text-muted underline" @click="effacerGroupe(ACTIONS.map((a) => a.id))">Effacer</button>
                        <span v-else class="text-sm text-muted">Pas noté</span>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <button v-for="action in ACTIONS" :key="action.id" type="button" :aria-pressed="!!valeurs[action.id]" class="bn-choix" :class="{ 'bn-choix-on': valeurs[action.id], 'bn-choix-frais': estDernier(`${action.id}:true`) }" @click="faire(action)">{{ action.text }}</button>
                    </div>
                </fieldset>

                <fieldset aria-labelledby="g-prochaine" class="m-0 mt-3.5 border-0 p-0">
                    <div class="mb-1 flex min-h-[28px] items-center justify-between">
                        <span id="g-prochaine" class="text-sm font-semibold text-muted">Prochaine visite</span>
                        <button v-if="valeurs.prochaine_visite_prevue" type="button" class="-my-2 min-h-[44px] min-w-[76px] text-sm font-semibold text-muted underline" @click="effacerGroupe(['prochaine_visite_prevue', '_semaines'])">Effacer</button>
                        <span v-else class="text-sm text-muted">Pas noté</span>
                    </div>
                    <div class="grid grid-cols-3 gap-2">
                        <button v-for="p in PROCHAINES" :key="p.semaines" type="button" :aria-pressed="prochaineChoisie(p)" class="bn-choix" :class="{ 'bn-choix-on': prochaineChoisie(p), 'bn-choix-frais': estDernier(`prochaine:${p.semaines}`) }" @click="planifier(p)">{{ p.text }}</button>
                    </div>
                </fieldset>
            </section>

            <div class="flex flex-col gap-2">
                <button type="button" class="inline-flex min-h-[56px] w-full items-center justify-center rounded-2xl bg-cream font-bold text-cream-ink" @click="aller('note')">
                    {{ state.visite.note ? 'Note vocale enregistrée, refaire' : 'Note : dites « note », puis parlez' }}
                </button>
                <div class="grid grid-cols-2 gap-2">
                    <button type="button" class="bn-btn-2" @click="aller('details')">Plus de détails</button>
                    <button type="button" class="bn-btn-2" @click="aller('aide')">Aide</button>
                </div>
            </div>
        </div>

        <div class="fixed bottom-0 left-0 right-0 z-30 bg-ground/95 px-4 pb-4 pt-3 lg:left-60">
            <div class="mx-auto max-w-xl">
                <!-- Une fois la page défilée, l'état de la voix reste sous les yeux. L'annonce est déjà faite en haut. -->
                <p v-if="voixActive && (voix.ecoute || voix.echo)" aria-hidden="true" class="mb-2 flex items-center gap-2 text-sm font-semibold" :class="statut.alerte ? 'text-rust' : 'text-forest'">
                    <Icon v-if="voix.ecoute" nom="micro" :size="16" />
                    <span class="min-w-0 flex-1 truncate">{{ statut.titre }} · {{ statut.texte }}</span>
                    <span class="flex-none text-muted">{{ nbNotes }} sur {{ TOTAL }}</span>
                </p>
                <button type="button" class="bn-btn w-full lg:w-full" @click="aller('recap')">Ruche terminée</button>
            </div>
        </div>

        <ConfirmSheet
            v-if="quitter"
            titre="Quitter cette visite ?"
            texte="Elle reste en cours. Vous pourrez la reprendre depuis le micro."
            garder="Continuer la visite"
            confirmer="Quitter"
            :danger="false"
            @garder="quitter = false"
            @confirmer="quitter = false; quitterVisite()"
        >
            <!-- Loin des deux autres boutons : avec des gants, un appui raté ne doit rien effacer. -->
            <div class="mt-6 border-t border-hairline pt-4">
                <button type="button" class="inline-flex min-h-[48px] w-full items-center justify-center font-semibold text-rust underline" @click="quitter = false; abandon = true">Abandonner cette visite…</button>
            </div>
        </ConfirmSheet>

        <ConfirmSheet
            v-if="abandon"
            :titre="`Effacer la visite de ${ruche?.nom ?? 'cette ruche'} ?`"
            texte="Ce qui est noté et la note vocale seront effacés. Cela ne se rattrape pas."
            garder="Garder la visite"
            confirmer="Effacer la visite"
            @garder="abandon = false"
            @confirmer="abandon = false; abandonnerVisite()"
        />
    </div>
</template>

<style scoped>
.bn-choix {
    min-height: 60px;
    padding: 2px 4px;
    border: 1.5px solid #6e7a73;
    border-radius: 16px;
    background-color: #fff;
    background-image: linear-gradient(#17382c, #17382c);
    background-repeat: no-repeat;
    background-position: bottom;
    background-size: 100% 0;
    color: #17382c;
    font-weight: 600;
    line-height: 1.15;
    transition: background-size 0.3s ease, color 0.2s ease, transform 0.09s ease;
}

.bn-choix-on {
    border-color: #17382c;
    background-size: 100% 100%;
    color: #fff;
}

.bn-choix-frais {
    background-image: linear-gradient(#ffb81c, #ffb81c);
    color: #17382c;
}
</style>
