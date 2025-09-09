<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ApplicationMark from '@/Components/ApplicationMark.vue';
import Banner from '@/Components/Banner.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';

defineProps({
    title: String,
});

const showingNavigationDropdown = ref(false);

const switchToTeam = (team) => {
    router.put(route('current-team.update'), {
        team_id: team.id,
    }, {
        preserveState: false,
    });
};

const logout = () => {
    router.post(route('logout'));
};
</script>

<template>
    <div>
        <Head :title="title" />
        <Banner />

        <div class="min-h-screen bg-gray-50">
            <!-- Navigation -->
            <nav class="bg-white border-b border-gray-200">
                <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-14">
                        <div class="flex">
                            <!-- Logo -->
                            <div class="flex items-center">
                                <Link :href="route('dashboard')" class="flex items-center">
                                    <ApplicationMark class="block h-6 w-auto" />
                                </Link>
                            </div>

                            <!-- Navigation Links -->
                            <div class="hidden sm:flex sm:items-center sm:ml-8 sm:space-x-6">
                                <NavLink :href="route('dashboard')" :active="route().current('dashboard')"
                                    class="text-sm font-medium text-gray-700 hover:text-gray-900 px-3 py-2 rounded-sm transition-colors">
                                    Accueil
                                </NavLink>
                                <NavLink :href="route('ruchers.index')" :active="route().current('ruchers.*')"
                                    class="text-sm font-medium text-gray-700 hover:text-gray-900 px-3 py-2 rounded-sm transition-colors">
                                    Ruchers
                                </NavLink>
                            </div>
                        </div>

                        <div class="hidden sm:flex sm:items-center sm:space-x-4">
                            <!-- Teams Dropdown -->
                            <Dropdown v-if="$page.props.jetstream.hasTeamFeatures" align="right" width="60">
                                <template #trigger>
                                    <button type="button"
                                        class="text-sm font-medium text-gray-700 hover:text-gray-900 px-3 py-2 rounded-sm transition-colors">
                                        {{ $page.props.auth.user.current_team.name }}
                                        <svg class="ml-1 -mr-0.5 h-4 w-4 inline" xmlns="http://www.w3.org/2000/svg"
                                            fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                        </svg>
                                    </button>
                                </template>

                                <template #content>
                                    <div class="w-60">
                                        <!-- Team Management -->
                                        <div class="px-4 py-2 text-xs font-medium text-gray-600 uppercase tracking-wide">
                                            Gestion d'équipe
                                        </div>

                                        <DropdownLink :href="route('teams.show', $page.props.auth.user.current_team)">
                                            Paramètres de l'équipe
                                        </DropdownLink>

                                        <DropdownLink v-if="$page.props.jetstream.canCreateTeams" :href="route('teams.create')">
                                            Créer une équipe
                                        </DropdownLink>

                                        <!-- Team Switcher -->
                                        <template v-if="$page.props.auth.user.all_teams.length > 1">
                                            <div class="border-t border-gray-200 my-1" />

                                            <div class="px-4 py-2 text-xs font-medium text-gray-600 uppercase tracking-wide">
                                                Changement d'équipe
                                            </div>

                                            <template v-for="team in $page.props.auth.user.all_teams" :key="team.id">
                                                <form @submit.prevent="switchToTeam(team)">
                                                    <DropdownLink as="button">
                                                        <div class="flex items-center">
                                                            <div v-if="team.id == $page.props.auth.user.current_team_id"
                                                                class="w-2 h-2 bg-gray-900 rounded-full mr-2"></div>
                                                            <div v-else class="w-2 h-2 mr-2"></div>
                                                            <div>{{ team.name }}</div>
                                                        </div>
                                                    </DropdownLink>
                                                </form>
                                            </template>
                                        </template>
                                    </div>
                                </template>
                            </Dropdown>

                            <!-- Settings Dropdown -->
                            <Dropdown align="right" width="48">
                                <template #trigger>
                                    <button v-if="$page.props.jetstream.managesProfilePhotos"
                                        class="flex items-center border border-gray-300 rounded-sm p-1 hover:border-gray-900 transition-colors">
                                        <img class="h-6 w-6 rounded-sm object-cover"
                                            :src="$page.props.auth.user.profile_photo_url"
                                            :alt="$page.props.auth.user.name">
                                    </button>

                                    <button v-else type="button"
                                        class="text-sm font-medium text-gray-700 hover:text-gray-900 px-3 py-2 rounded-sm transition-colors">
                                        {{ $page.props.auth.user.name }}
                                        <svg class="ml-1 -mr-0.5 h-4 w-4 inline" xmlns="http://www.w3.org/2000/svg"
                                            fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                        </svg>
                                    </button>
                                </template>

                                <template #content>
                                    <!-- Account Management -->
                                    <div class="px-4 py-2 text-xs font-medium text-gray-600 uppercase tracking-wide">
                                        Gestion du compte
                                    </div>

                                    <DropdownLink :href="route('profile.show')">
                                        Profile
                                    </DropdownLink>

                                    <DropdownLink v-if="$page.props.jetstream.hasApiFeatures" :href="route('api-tokens.index')">
                                        API Tokens
                                    </DropdownLink>

                                    <div class="border-t border-gray-200 my-1" />

                                    <!-- Authentication -->
                                    <form @submit.prevent="logout">
                                        <DropdownLink as="button">
                                            Déconnexion
                                        </DropdownLink>
                                    </form>
                                </template>
                            </Dropdown>
                        </div>

                        <!-- Mobile menu button -->
                        <div class="flex items-center sm:hidden">
                            <button
                                class="p-2 rounded-sm text-gray-700 hover:text-gray-900 hover:bg-gray-100 transition-colors"
                                @click="showingNavigationDropdown = !showingNavigationDropdown">
                                <svg class="h-5 w-5" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path :class="{ 'hidden': showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown }"
                                        stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                    <path :class="{ 'hidden': !showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }"
                                        stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Mobile Navigation Menu -->
                <div :class="{ 'block': showingNavigationDropdown, 'hidden': !showingNavigationDropdown }" class="sm:hidden">
                    <div class="px-4 pt-2 pb-3 space-y-1 border-t border-gray-200">
                        <ResponsiveNavLink :href="route('dashboard')" :active="route().current('dashboard')"
                            class="block text-sm font-medium text-gray-700 hover:text-gray-900 px-3 py-2 rounded-sm">
                            Accueil
                        </ResponsiveNavLink>

                        <ResponsiveNavLink :href="route('ruchers.index')" :active="route().current('ruchers.*')"
                            class="block text-sm font-medium text-gray-700 hover:text-gray-900 px-3 py-2 rounded-sm">
                            Ruchers
                        </ResponsiveNavLink>
                    </div>

                    <!-- Mobile Settings -->
                    <div class="pt-4 pb-1 border-t border-gray-200">
                        <div class="flex items-center px-4 py-2">
                            <div v-if="$page.props.jetstream.managesProfilePhotos" class="mr-3">
                                <img class="h-8 w-8 rounded-sm object-cover" :src="$page.props.auth.user.profile_photo_url"
                                    :alt="$page.props.auth.user.name">
                            </div>

                            <div>
                                <div class="text-sm font-medium text-gray-900">
                                    {{ $page.props.auth.user.name }}
                                </div>
                                <div class="text-xs text-gray-600">
                                    {{ $page.props.auth.user.email }}
                                </div>
                            </div>
                        </div>

                        <div class="mt-3 space-y-1 px-4">
                            <ResponsiveNavLink :href="route('profile.show')" :active="route().current('profile.show')"
                                class="block text-sm font-medium text-gray-700 hover:text-gray-900 px-3 py-2 rounded-sm">
                                Profile
                            </ResponsiveNavLink>

                            <ResponsiveNavLink v-if="$page.props.jetstream.hasApiFeatures" :href="route('api-tokens.index')"
                                :active="route().current('api-tokens.index')"
                                class="block text-sm font-medium text-gray-700 hover:text-gray-900 px-3 py-2 rounded-sm">
                                API Tokens
                            </ResponsiveNavLink>

                            <!-- Authentication -->
                            <form @submit.prevent="logout">
                                <ResponsiveNavLink as="button"
                                    class="w-full text-left text-sm font-medium text-gray-700 hover:text-gray-900 px-3 py-2 rounded-sm">
                                    Déconnexion
                                </ResponsiveNavLink>
                            </form>

                            <!-- Team Management -->
                            <template v-if="$page.props.jetstream.hasTeamFeatures">
                                <div class="border-t border-gray-200 my-2" />

                                <div class="px-3 py-2 text-xs font-medium text-gray-600 uppercase tracking-wide">
                                    Gestion de l'équipe
                                </div>

                                <ResponsiveNavLink :href="route('teams.show', $page.props.auth.user.current_team)"
                                    :active="route().current('teams.show')"
                                    class="block text-sm font-medium text-gray-700 hover:text-gray-900 px-3 py-2 rounded-sm">
                                    Paramètres de l'équipe
                                </ResponsiveNavLink>

                                <ResponsiveNavLink v-if="$page.props.jetstream.canCreateTeams" :href="route('teams.create')"
                                    :active="route().current('teams.create')"
                                    class="block text-sm font-medium text-gray-700 hover:text-gray-900 px-3 py-2 rounded-sm">
                                    Créer une équipe
                                </ResponsiveNavLink>

                                <!-- Team Switcher -->
                                <template v-if="$page.props.auth.user.all_teams.length > 1">
                                    <div class="border-t border-gray-200 my-2" />

                                    <div class="px-3 py-2 text-xs font-medium text-gray-600 uppercase tracking-wide">
                                        Changement d'équipe
                                    </div>

                                    <template v-for="team in $page.props.auth.user.all_teams" :key="team.id">
                                        <form @submit.prevent="switchToTeam(team)">
                                            <ResponsiveNavLink as="button"
                                                class="w-full text-left text-sm font-medium text-gray-700 hover:text-gray-900 px-3 py-2 rounded-sm">
                                                <div class="flex items-center">
                                                    <div v-if="team.id == $page.props.auth.user.current_team_id"
                                                        class="w-2 h-2 bg-gray-900 rounded-full mr-2"></div>
                                                    <div v-else class="w-2 h-2 mr-2"></div>
                                                    <div>{{ team.name }}</div>
                                                </div>
                                            </ResponsiveNavLink>
                                        </form>
                                    </template>
                                </template>
                            </template>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Heading -->
            <header v-if="$slots.header" class="bg-white border-b border-gray-200">
                <div class="max-w-6xl mx-auto py-4 sm:py-6 px-4 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main>
                <slot />
            </main>
        </div>
    </div>
</template>