<script setup>
import NavLink from "@/Components/NavLink.vue";
import SideNavItems from "./SideNavItems.vue";
import { ref, onMounted, watch } from "vue";

// Toggle Site Ho
// State for controlling the visibility of submenus
const level1OpenHo = ref(false);
const level2OpenHo = ref(false);
const level2OpenAduanHo = ref(false);
const level2OpenSettingHo = ref(false);
const level3OpenHo = ref(false);
const level3KomputerOpenHo = ref(false);
const level3PrinterOpenHo = ref(false);
const level3CctvOpenHo = ref(false);

// Load initial state from localStorage
onMounted(() => {
    level1OpenHo.value = localStorage.getItem("level1OpenHo") === "true";
    level2OpenHo.value = localStorage.getItem("level2OpenHo") === "true";
    level2OpenAduanHo.value =
        localStorage.getItem("level2OpenAduanHo") === "true";
    level2OpenSettingHo.value =
        localStorage.getItem("level2OpenSettingHo") === "true";
    level3OpenHo.value = localStorage.getItem("level3OpenHo") === "true";
    level3PrinterOpenHo.value =
        localStorage.getItem("level3PrinterOpenHo") === "true";
    level3KomputerOpenHo.value =
        localStorage.getItem("level3KomputerOpenHo") === "true";
    level3CctvOpenHo.value =
        localStorage.getItem("level3CctvOpenHo") === "true";
});

// Watch changes and save to localStorage
watch(
    [
        level1OpenHo,
        level2OpenHo,
        level2OpenSettingHo,
        level2OpenAduanHo,
        level3PrinterOpenHo,
        level3KomputerOpenHo,
        level3CctvOpenHo,
    ],
    () => {
        localStorage.setItem("level1OpenHo", level1OpenHo.value);
        localStorage.setItem("level2OpenHo", level2OpenHo.value);
        localStorage.setItem("level2OpenAduanHo", level2OpenAduanHo.value);
        localStorage.setItem("level2OpenSettingHo", level2OpenSettingHo.value);
        localStorage.setItem("level3OpenHo", level3OpenHo.value);
        localStorage.setItem(
            "level3KomputerOpenHo",
            level3KomputerOpenHo.value
        );
        localStorage.setItem("level3PrinterOpenHo", level3PrinterOpenHo.value);
        localStorage.setItem("level3CctvOpenHo", level3CctvOpenHo.value);
    }
);

// Toggle functions for each level
const toggleLevel1Ho = () => {
    level1OpenHo.value = !level1OpenHo.value;

    // Jika level1 ditutup, tutup juga level2
    if (!level1OpenHo.value) {
        level2OpenHo.value = false;
        level3OpenHo.value = false;
        level3KomputerOpenHo.value = false;
        level3PrinterOpenHo.value = false;
        level3CctvOpenHo.value = false;
    }
};

const toggleLevel2Ho = () => {
    console.log(level1OpenHo.value);
    if (!level2OpenHo.value) {
        level1OpenHo.value = true; // pastikan level 1 terbuka jika level 2 dibuka
        level2OpenSettingHo.value = false;
        level3OpenHo.value = false;
        level3KomputerOpenHo.value = false;
        level3PrinterOpenHo.value = false;
        level3CctvOpenHo.value = false;
    }
    level2OpenHo.value = !level2OpenHo.value;
};

const toggleLevel2AduanHo = () => {
    console.log(level1OpenHo.value);
    if (!level2OpenAduanHo.value) {
        level2OpenHo.value = false; // pastikan level 1 terbuka jika level 2 dibuka
        level2OpenSettingHo.value = false; // pastikan level 1 terbuka jika level 2 dibuka
        level3OpenHo.value = false;
        level3KomputerOpenHo.value = false;
        level3PrinterOpenHo.value = false;
        level3CctvOpenHo.value = false;
    }
    level2OpenAduanHo.value = !level2OpenAduanHo.value;
};

const toggleLevel2SettingHo = () => {
    console.log(level1OpenHo.value);
    if (!level2OpenSettingHo.value) {
        level1OpenHo.value = true; // pastikan level 1 terbuka jika level 2 dibuka
        level2OpenHo.value = false; // pastikan level 1 terbuka jika level 2 dibuka
        level3OpenHo.value = false;
        level3KomputerOpenHo.value = false;
        level3PrinterOpenHo.value = false;
        level3CctvOpenHo.value = false;
    }
    level2OpenSettingHo.value = !level2OpenSettingHo.value;
};

const toggleLevel3LaptopHo = () => {
    if (!level3OpenHo.value) {
        level2OpenHo.value = true; // pastikan level 1 terbuka jika level 3 dibuka
        level3KomputerOpenHo.value = false;
        level3PrinterOpenHo.value = false;
        level3CctvOpenHo.value = false;
    }
    level3OpenHo.value = !level3OpenHo.value;
};

const toggleLevel3KomputerHo = () => {
    if (!level3KomputerOpenHo.value) {
        level2OpenHo.value = true; // pastikan level 1 terbuka jika level 3 dibuka
        level3OpenHo.value = false;
        level3PrinterOpenHo.value = false;
        level3CctvOpenHo.value = false;
    }
    level3KomputerOpenHo.value = !level3KomputerOpenHo.value;
};

const toggleLevel3CctvHo = () => {
    if (!level3CctvOpenHo.value) {
        level2OpenHo.value = true; // pastikan level 1 terbuka jika level 2 dibuka
        level3OpenHo.value = false;
        level3KomputerOpenHo.value = false;
        level3PrinterOpenHo.value = false;
    }
    level3CctvOpenHo.value = !level3CctvOpenHo.value;
};

const toggleLevel3PrinterHo = () => {
    if (!level3PrinterOpenHo.value) {
        level2OpenHo.value = true; // pastikan level 1 terbuka jika level 2 dibuka
        level3OpenHo.value = false;
        level3KomputerOpenHo.value = false;
        level3CctvOpenHo.value = false;
    }
    level3PrinterOpenHo.value = !level3PrinterOpenHo.value;
};
</script>

<template>
    <!-- sidenav  -->
    <aside
        class="fixed inset-y-0 flex-wrap items-center justify-between block w-full p-0 my-8 overflow-y-auto antialiased transition-transform duration-200 -translate-x-full bg-white border-0 shadow-xl dark:shadow-none dark:bg-slate-850 max-w-64 ease-nav-brand z-990 xl:ml-6 rounded-2xl xl:left-0 xl:translate-x-0"
        aria-expanded="false"
    >
        <div class="h-19">
            <i
                class="absolute top-0 right-0 p-4 opacity-50 cursor-pointer fas fa-times dark:text-white text-slate-400 xl:hidden"
                sidenav-close
            ></i>
            <div
                class="block px-8 py-6 m-0 text-sm whitespace-nowrap dark:text-white text-slate-700"
            >
                <img
                    src="/assets/img/logoppa.png"
                    class="inline transition-all duration-200 dark:hidden ease-nav-brand max-h-8 mr-2"
                    alt="main_logo"
                />
                <span
                    class="ml-1 font-semibold transition-all duration-200 ease-nav-brand"
                    >ICT - PPA INVENTORY</span
                >
            </div>
        </div>

        <hr
            class="h-px mt-0 bg-transparent bg-gradient-to-r from-transparent via-black/40 to-transparent dark:bg-gradient-to-r dark:from-transparent dark:via-white dark:to-transparent"
        />

        <div class="items-center block w-auto max-h-screen grow basis-full">
            <ul class="flex flex-col pl-0 mb-0">
                <li>
                    <NavLink
                        @click="toggleLevel2AduanHo"
                        :href="route('guestAduan.page')"
                        :active="route().current('guestAduan.page')"
                    >
                        <div
                            class="mr-2 flex h-8 w-8 items-center justify-center rounded-lg bg-center stroke-0 text-center xl:p-2.5"
                        >
                            <i
                                class="relative top-0 text-sm leading-normal text-red-800 fas fa-comments"
                            ></i>
                        </div>
                        <span
                            class="ml-1 duration-300 opacity-100 pointer-events-none ease"
                            >Complaint</span
                        >
                    </NavLink>
                    <ul v-if="level1OpenHo">
                        <li></li>
                    </ul>
                </li>
            </ul>
        </div>
    </aside>

    <!-- end sidenav -->
</template>
