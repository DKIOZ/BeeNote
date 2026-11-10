<script setup>
import { ref } from 'vue';
import { router, useForm, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import BackBar from '@/Components/BackBar.vue';
import ConfirmSheet from '@/Components/ConfirmSheet.vue';
import Field from '@/Components/Field.vue';
import { clearAll } from '@/terrain/store.js';

const user = usePage().props.auth.user;

const profil = useForm({
    _method: 'PUT',
    name: user.name,
    email: user.email,
    current_password: '',
    adresse: user.adresse ?? '',
    telephone: user.telephone ?? '',
    napi: user.napi ?? '',
    siret: user.siret ?? '',
});
const motDePasse = useForm({ current_password: '', password: '', password_confirmation: '' });
const suppression = useForm({ password: '' });
const confirmation = ref(false);

const enregistrerProfil = () => profil.post(route('user-profile-information.update'), {
    errorBag: 'updateProfileInformation',
    preserveScroll: true,
    onFinish: () => profil.reset('current_password'),
});
const enregistrerMotDePasse = () => motDePasse.put(route('user-password.update'), {
    errorBag: 'updatePassword',
    preserveScroll: true,
    onSuccess: () => motDePasse.reset(),
});

function supprimer() {
    suppression.delete(route('current-user.destroy'), {
        errorBag: 'deleteUser',
        preserveScroll: true,
        onSuccess: () => {
            router.clearHistory();
            clearAll().catch(() => {});
        },
        onError: () => { confirmation.value = false; },
    });
}
</script>

<template>
    <AppLayout title="Mon compte" sans-nav>
        <BackBar :href="route('plus')" titre="Mon compte" />

        <div class="flex max-w-xl flex-col gap-5 px-4 pb-6 pt-2 lg:px-0">
            <form class="flex flex-col gap-3.5" @submit.prevent="enregistrerProfil">
                <Field id="name" v-model="profil.name" label="Nom" autocomplete="name" :erreur="profil.errors.name" required />
                <Field id="email" v-model="profil.email" type="email" label="E-mail" autocomplete="email" :erreur="profil.errors.email" required />
                <Field v-if="profil.email.trim().toLowerCase() !== user.email.toLowerCase()" id="mdp-actuel" v-model="profil.current_password" type="password" label="Mot de passe actuel, pour changer d'adresse" autocomplete="current-password" :erreur="profil.errors.current_password" required />

                <fieldset class="m-0 mt-2 flex flex-col gap-3.5 rounded-2xl border-0 bg-cream p-4">
                    <legend class="float-left mb-1 p-0 font-bold">Pour le registre d'élevage</legend>
                    <p class="clear-left -mt-2 text-sm text-cream-ink">Facultatif. Repris en tête du registre PDF, pour ne pas les recopier à la main.</p>
                    <Field id="napi" v-model="profil.napi" label="N° d'apiculteur (NAPI)" placeholder="A1234567" autocomplete="off" :erreur="profil.errors.napi" />
                    <Field id="siret" v-model="profil.siret" label="SIRET ou NUMAGRIT" inputmode="numeric" autocomplete="off" :erreur="profil.errors.siret" />
                    <Field id="adresse" v-model="profil.adresse" label="Adresse" autocomplete="street-address" :erreur="profil.errors.adresse" />
                    <Field id="telephone" v-model="profil.telephone" type="tel" label="Téléphone" autocomplete="tel" :erreur="profil.errors.telephone" />
                </fieldset>

                <p v-if="profil.recentlySuccessful" role="status" class="font-semibold text-mint-ink">Enregistré.</p>
                <button type="submit" class="bn-btn-2" :disabled="profil.processing">Enregistrer</button>
            </form>

            <form class="flex flex-col gap-3.5" @submit.prevent="enregistrerMotDePasse">
                <Field id="current_password" v-model="motDePasse.current_password" type="password" label="Mot de passe actuel" autocomplete="current-password" :erreur="motDePasse.errors.current_password" />
                <Field id="password" v-model="motDePasse.password" type="password" label="Nouveau mot de passe" autocomplete="new-password" :erreur="motDePasse.errors.password" />
                <Field id="password_confirmation" v-model="motDePasse.password_confirmation" type="password" label="Répéter le nouveau mot de passe" autocomplete="new-password" :erreur="motDePasse.errors.password_confirmation" />
                <p v-if="motDePasse.recentlySuccessful" role="status" class="font-semibold text-mint-ink">Mot de passe changé.</p>
                <button type="submit" class="bn-btn-2" :disabled="motDePasse.processing">Changer le mot de passe</button>
            </form>

            <section class="rounded-2xl bg-peach p-4">
                <h2 class="font-bold text-rust">Supprimer mon compte</h2>
                <p class="mb-2.5 mt-1">Vos ruchers, visites et registre seront effacés. Pensez à télécharger votre registre avant.</p>
                <Field id="delete_password" v-model="suppression.password" type="password" label="Votre mot de passe" autocomplete="current-password" :erreur="suppression.errors.password" />
                <button type="button" class="bn-btn-2 mt-2.5 !text-rust" :disabled="!suppression.password" @click="confirmation = true">Supprimer définitivement</button>
            </section>
        </div>

        <ConfirmSheet
            v-if="confirmation"
            titre="Supprimer votre compte ?"
            texte="Cette action est définitive : vos données ne pourront pas être récupérées."
            garder="Garder mon compte"
            confirmer="Supprimer définitivement"
            @garder="confirmation = false"
            @confirmer="supprimer"
        />
    </AppLayout>
</template>
