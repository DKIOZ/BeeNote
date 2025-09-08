<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import ActionSection from '@/Components/ActionSection.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import DangerButton from '@/Components/DangerButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    team: Object,
});

const confirmingTeamDeletion = ref(false);
const form = useForm({});

const confirmTeamDeletion = () => {
    confirmingTeamDeletion.value = true;
};

const deleteTeam = () => {
    form.delete(route('teams.destroy', props.team), {
        errorBag: 'deleteTeam',
    });
};
</script>

<template>
    <ActionSection>
        <template #title>
            Supprimer l'équipe
        </template>

        <template #description>
            Supprimez définitivement cette équipe.
        </template>

        <template #content>
            <div class="max-w-xl text-sm text-gray-600">
                Une fois une équipe supprimée, toutes ses ressources et données seront définitivement supprimées. Avant de supprimer cette équipe, veuillez télécharger toutes les données ou informations concernant cette équipe que vous souhaitez conserver.
            </div>

            <div class="mt-5">
                <DangerButton @click="confirmTeamDeletion">
                    Supprimer l'équipe
                </DangerButton>
            </div>

            <!-- Modal de confirmation de suppression d'équipe -->
            <ConfirmationModal :show="confirmingTeamDeletion" @close="confirmingTeamDeletion = false">
                <template #title>
                    Supprimer l'équipe
                </template>

                <template #content>
                    Êtes-vous sûr de vouloir supprimer cette équipe ? Une fois une équipe supprimée, toutes ses ressources et données seront définitivement supprimées.
                </template>

                <template #footer>
                    <SecondaryButton @click="confirmingTeamDeletion = false">
                        Annuler
                    </SecondaryButton>

                    <DangerButton
                        class="ms-3"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                        @click="deleteTeam"
                    >
                        Supprimer l'équipe
                    </DangerButton>
                </template>
            </ConfirmationModal>
        </template>
    </ActionSection>
</template>