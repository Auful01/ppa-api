<script setup>
import { ref } from "vue";
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import Dropdown from "@/Components/Dropdown.vue";
import DropdownLink from "@/Components/DropdownLink.vue";
import NavLink from "@/Components/NavLink.vue";
import ResponsiveNavLink from "@/Components/ResponsiveNavLink.vue";
import { Link } from "@inertiajs/vue3";
import DashboardAside from "@/Components/inventory/DashboardAside.vue";
import DashboardNavbar from "@/Components/inventory/DashboardNavbar.vue";
import DashboardConfig from "@/Components/inventory/DashboardConfig.vue";

const showingNavigationDropdown = ref(false);
</script>

<template>
    <div
        class="absolute w-full dark:hidden min-h-75"
        style="
            background: linear-gradient(
                180deg,
                oklch(20.8% 0.042 265.755) 0%,
                oklch(35% 0.035 265.755) 100%
            );
        "
    ></div>
    <div class="hidden absolute w-full bg-slate-900 dark:block min-h-75"></div>
    
    <!-- <DashboardAside /> -->

    <!-- Page Content -->
    <main
        class="relative h-full max-h-screen transition-all duration-200 ease-in-out rounded-xl"
    >
        <nav
            class="relative flex flex-wrap items-center justify-between px-0 py-2 mx-6 transition-all ease-in shadow-none duration-250 rounded-2xl lg:flex-nowrap lg:justify-start"
            navbar-main
            navbar-scroll="false"
        >
            <div
                class="flex items-center justify-between w-full px-4 py-1 mx-auto flex-wrap-inherit"
            >
                <nav>
                    <!-- breadcrumb -->
                    <slot name="header" />
                    <!-- <h6 class="mb-0 font-bold text-white capitalize">Dashboard Group Leader</h6> -->
                </nav>

                <slot name="menu"></slot>

                <div
                    class="flex items-center mt-2 grow sm:mt-0 sm:mr-6 md:mr-0 lg:flex lg:basis-auto"
                >
                    <div class="flex items-center md:ml-auto md:pr-4">
                        <div
                            class="block text-sm font-semibold text-white transition-all ease-nav-brand"
                        >
                            <span>
                                Hello! {{ $page.props.auth.user.name }}
                            </span>
                        </div>
                    </div>
                    <ul
                        class="flex flex-row justify-end pl-0 mb-0 list-none md-max:w-full"
                    >
                        <li
                            class="flex items-center"
                        >
                            <ResponsiveNavLink
                                :href="route('guestAduan.page')"
                                @click="logout"
                                as="button"
                                class="block px-0 py-2 text-sm font-semibold text-white transition-all ease-nav-brand"
                            >
                                <i class="fas fa-home fa-beat mr-2" style="--fa-animation-duration: 2s;"></i>
                                <span class="sm:inline">Home</span>
                            </ResponsiveNavLink>
                        </li>
                        <li
                            class="flex items-center mobile-sm-logout-form:hidden"
                        >
                            <ResponsiveNavLink
                                :href="route('logout')"
                                @click="logout"
                                method="post"
                                as="button"
                                class="block px-0 py-2 text-sm font-semibold text-white transition-all ease-nav-brand"
                            >
                                <span class="sm:inline">LogOut</span>
                            <i class="fas fa-sign-out-alt ml-1"></i>
                            </ResponsiveNavLink>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <slot />
    </main>

    <DashboardConfig />
</template>
