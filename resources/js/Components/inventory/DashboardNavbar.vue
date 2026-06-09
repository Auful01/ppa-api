<script setup>
import ResponsiveNavLink from "@/Components/ResponsiveNavLink.vue";
import { computed, ref, onMounted } from "vue";
import { usePage } from "@inertiajs/vue3";
import DashboardBreadcrumb from "@/Components/inventory/DashboardBreadcrumb.vue";

const pages = defineModel('pages', {
    type: String,
});

const subMenu = defineModel('subMenu', {
    type: String,
});

const mainMenu = defineModel('mainMenu', {
    type: String,
});

// Notification Menu
const isNotificationActive = ref(false);

const notificationMenuClasses = computed(() =>
    isNotificationActive.value
        ? "before:-top-5 dark:before:-top-5 dark:before:text-slate-850 transform-dropdown-show"
        : "before:top-0 pointer-events-none opacity-0 transform-dropdown"
);
const handleNotificationMenu = () =>
    (isNotificationActive.value = !isNotificationActive.value);

// Mobile Menu
const emit = defineEmits(["toggleMobileSidebar", "toggleConfig"]);

const isCollapseIconActive = defineModel("isCollapseIconActive", {
    type: Boolean,
    default: false,
});

const collapseIconClasses = computed(() =>
    isCollapseIconActive.value ? "translate-x-[5px]" : ""
);

// Current User
const page = usePage();
const user = computed(() => page.props.auth.user);
</script>

<template>
    <!-- Navbar -->
    <nav
        class="flex flex-wrap items-center justify-between px-0 py-2 mx-6 mt-2 transition-all ease-in shadow-none duration-250 rounded-2xl lg:flex-nowrap lg:justify-start top-0 z-50"
        style="background-color: #ffff;"
        navbar-main
        navbar-scroll="false"
    >
        <div
            class="flex items-center justify-between w-full px-4 py-1 mx-auto flex-wrap-inherit"
        >
            <DashboardBreadcrumb
            v-model:pages="pages"
            v-model:subMenu="subMenu"
            v-model:mainMenu="mainMenu" 
            />

            <slot name="menu"></slot>

            <div
                class="flex items-center mt-2 grow sm:mt-0 sm:mr-6 md:mr-0 lg:flex lg:basis-auto"
            >
                <div class="flex items-center md:ml-auto md:pr-4">
                    <div
                        class="block text-sm font-semibold text-dark transition-all ease-nav-brand"
                    >
                        <span> Hello! {{ $page.props.auth.user.name }} </span>
                    </div>
                </div>
                <ul
                    class="flex flex-row justify-end pl-0 mb-0 list-none md-max:w-full"
                >
                    <!-- online builder btn  -->
                    <!-- <li class="flex items-center">
                <a class="inline-block px-8 py-2 mb-0 mr-4 text-xs font-bold text-center text-blue-500 uppercase align-middle transition-all ease-in bg-transparent border border-blue-500 border-solid rounded-lg shadow-none cursor-pointer leading-pro hover:-translate-y-px active:shadow-xs hover:border-blue-500 active:bg-blue-500 active:hover:text-blue-500 hover:text-blue-500 tracking-tight-rem hover:bg-transparent hover:opacity-75 hover:shadow-none active:text-dark active:hover:bg-transparent" target="_blank" href="https://www.creative-tim.com/builder/soft-ui?ref=navbar-dashboard&amp;_ga=2.76518741.1192788655.1647724933-1242940210.1644448053">Online Builder</a>
              </li> -->
                    <li class="flex items-center pl-4 xl:hidden">
                        <a
                            class="block p-0 text-sm text-dark transition-all ease-nav-brand"
                            @click="emit('toggleMobileSidebar')"
                        >
                            <div class="w-4.5 overflow-hidden">
                                <i
                                    class="ease mb-0.75 relative block h-0.5 rounded-sm bg-slate-900 transition-all"
                                    :class="collapseIconClasses"
                                ></i>
                                <i
                                    class="ease mb-0.75 relative block h-0.5 rounded-sm bg-slate-900 transition-all"
                                ></i>
                                <i
                                    class="ease relative block h-0.5 rounded-sm bg-slate-900 transition-all"
                                    :class="collapseIconClasses"
                                ></i>
                            </div>
                        </a>
                    </li>
                    <li class="flex items-center">
                        <!-- <a :href="route('logout')" method="post" as="button" class="block px-0 py-2 text-sm font-semibold text-dark transition-all ease-nav-brand">
                            <i class="fa fa-user sm:mr-1"></i>
                            <span class="hidden sm:inline">LogOut</span>
                        </a> -->

                        <ResponsiveNavLink
                        v-if="$page.props.auth.user.site != 'BGE'"
                            :href="route('logout')"
                            method="post"
                            as="button"
                            class="block px-0 py-2 text-sm font-semibold text-dark transition-all ease-nav-brand"
                        >
                            <i class="fa fa-user sm:mr-1 max-mobile-sm-logout-form:hidden"></i>
                            <span class="sm:inline">Log Out</span>
                        </ResponsiveNavLink>

                        <ResponsiveNavLink
                        v-if="$page.props.auth.user.site == 'BGE'"
                            :href="route('logout')"
                            method="post"
                            as="button"
                            class="block px-0 py-2 text-sm font-semibold text-dark transition-all ease-nav-brand"
                        >
                            <i class="fa fa-user sm:mr-1 max-mobile-sm-logout-form:hidden"></i>
                            <span class="sm:inline">Log Out</span>
                        </ResponsiveNavLink>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    

    <!-- end Navbar -->
</template>
