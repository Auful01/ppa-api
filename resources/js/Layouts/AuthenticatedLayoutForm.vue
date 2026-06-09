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
                        <!-- online builder btn  -->
                        <!-- <li class="flex items-center">
                <a class="inline-block px-8 py-2 mb-0 mr-4 text-xs font-bold text-center text-blue-500 uppercase align-middle transition-all ease-in bg-transparent border border-blue-500 border-solid rounded-lg shadow-none cursor-pointer leading-pro hover:-translate-y-px active:shadow-xs hover:border-blue-500 active:bg-blue-500 active:hover:text-blue-500 hover:text-blue-500 tracking-tight-rem hover:bg-transparent hover:opacity-75 hover:shadow-none active:text-white active:hover:bg-transparent" target="_blank" href="https://www.creative-tim.com/builder/soft-ui?ref=navbar-dashboard&amp;_ga=2.76518741.1192788655.1647724933-1242940210.1644448053">Online Builder</a>
              </li> -->
                        <li class="flex items-center">
                            <!-- <a :href="route('logout')" method="post" as="button" class="block px-0 py-2 text-sm font-semibold text-white transition-all ease-nav-brand">
                            <i class="fa fa-user sm:mr-1"></i>
                            <span class="hidden sm:inline">LogOut</span>
                        </a> -->

                            <ResponsiveNavLink
                                :href="route('logout')"
                                method="post"
                                as="button"
                                class="block px-0 py-2 text-sm font-semibold text-white transition-all ease-nav-brand"
                            >
                                <i class="fa fa-user sm:mr-1"></i>
                                <span class="hidden sm:inline">LogOut</span>
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
