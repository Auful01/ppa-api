<script setup>
import { ref, watchEffect, onMounted, computed } from "vue";
import { usePage } from "@inertiajs/vue3";
import { onClickOutside } from "@vueuse/core";
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import Dropdown from "@/Components/Dropdown.vue";
import DropdownLink from "@/Components/DropdownLink.vue";
import NavLink from "@/Components/NavLink.vue";
import ResponsiveNavLink from "@/Components/ResponsiveNavLink.vue";
import { Link } from "@inertiajs/vue3";
import DashboardAside from "@/Components/inventory/DashboardAside.vue";
import DashboardNavbar from "@/Components/inventory/DashboardNavbar.vue";
import DashboardFooter from "@/Components/inventory/DashboardFooter.vue";
import DashboardConfig from "@/Components/inventory/DashboardConfig.vue";
import Swal from "sweetalert2";

const showingNavigationDropdown = ref(false);

// 🔹 Mobile Sidebar (offcanvas)
const isActive = ref(false);
const handleMobileSidebar = () => {
    isActive.value = !isActive.value;
};

// 🔹 Desktop Sidebar (collapse/expand)
const isCollapseSidebar = ref(false);
const handleCollapseSidebar = () => {
    isCollapseSidebar.value = !isCollapseSidebar.value;

    // Optional: simpan ke localStorage biar keinget
    localStorage.setItem("sidebarCollapsed", isCollapseSidebar.value);
};

// 🔹 Saat pertama load, baca dari localStorage
onMounted(() => {});

//Argon Configurator
const configurator = ref(null);
const isConfiguratorActive = ref(false);
const handleConfigurator = () => {
    isConfiguratorActive.value = !isConfiguratorActive.value;
};

onClickOutside(configurator, (event) => (isConfiguratorActive.value = false));

const page = usePage();
const aduanBaru = computed(() => page.props.flash.aduan_baru);
const lastAduanMax_id = ref(0); // Simpan ID Aduan Terakhir
const userSite = computed(() => page.props.auth.user.site.toLowerCase());

let intervalId = null;
const notifAktif = ref(false); // State untuk mendeteksi notifikasi muncul
let audio = null;

// Simpan daftar notifikasi terbaru
const notifikasiList = ref([]);

const checkAduan = async () => {
    try {

        const user = page.props.auth?.user;

        if (!user || !user.site) {
            console.warn("User or site not available yet");
            return;
        }

        const userSiteUpper = computed(() => page.props.auth.user.site);
        const apiUrl = `/itportal/admin/check-aduan/${userSite.value}`; // Tambahkan site ke URL
        const response = await fetch(apiUrl);

        const data = await response.json();
        if (data.site === userSiteUpper.value) {
            if (data && data.id) {
                let lastAduanMax_id = localStorage.getItem("lastAduanMax_id");

                if (!lastAduanMax_id || data.id != lastAduanMax_id) {
                    localStorage.setItem("lastAduanMax_id", data.id);
                    console.log(data.id);

                    // 🔥 Tampilkan popup (browser menganggap ini sebagai interaksi)
                    Swal.fire({
                        title: "Aduan Baru!",
                        text: data.message,
                        icon: "info",
                        // timer: 5000,
                        // timerProgressBar: true,
                        position: "top-end",
                        showConfirmButton: false,
                        toast: true,
                        showCloseButton: true,
                        didOpen: () => {
                            // ✅ Mainkan audio di dalam event SweetAlert
                            const audio = document.getElementById("notifSound");
                            if (audio) {
                                audio.muted = false;
                                audio.currentTime = 0;
                                audio
                                    .play()
                                    .then(() => {
                                        console.log(
                                            "🔊 Audio berhasil diputar."
                                        );
                                    })
                                    .catch((error) => {
                                        console.warn(
                                            "⚠️ Gagal memutar audio:",
                                            error
                                        );
                                    });
                            }
                        },
                    });
                }
            }
        }
    } catch (error) {
        console.error("Error fetching aduan:", error);
    }
};

// onMounted(() => {
//     console.log("👀 Memeriksa localStorage di Admin...");

//     const lastAduan = localStorage.getItem("lastAduan");

//     if (lastAduan) {
//         console.log("✅ Data ditemukan:", JSON.parse(lastAduan));
//     } else {
//         console.log("❌ Tidak ada data aduan di localStorage");
//     }

//     // Tambahkan event listener untuk mendeteksi perubahan localStorage
//     window.addEventListener("storage", (event) => {
//         if (event.key === "lastAduan") {
//             console.log(
//                 "🔔 Data aduan baru masuk:",
//                 JSON.parse(event.newValue)
//             );
//         }
//     });

//     const audio = document.getElementById("notifSound");
//     if (audio) {
//         audio.muted = true; // Mulai dalam mode mute
//     }

//     // Jalankan polling untuk mengecek aduan baru
//     intervalId = setInterval(checkAduan, 2000);
// });

onMounted(() => {
    console.log("👀 Memeriksa localStorage di Admin...");

    // ✅ Cek aduan terakhir
    const lastAduan = localStorage.getItem("lastAduan");
    if (lastAduan) {
        console.log("✅ Data ditemukan:", JSON.parse(lastAduan));
    } else {
        console.log("❌ Tidak ada data aduan di localStorage");
    }

    // ✅ Event listener untuk notifikasi aduan
    window.addEventListener("storage", (event) => {
        if (event.key === "lastAduan") {
            console.log(
                "🔔 Data aduan baru masuk:",
                JSON.parse(event.newValue)
            );
        }
    });

    // ✅ Atur audio notif
    const audio = document.getElementById("notifSound");
    if (audio) {
        audio.muted = true; // mulai dalam mode mute
    }

    // ✅ Jalankan polling aduan
    intervalId = setInterval(checkAduan, 2000);

    // ✅ Baca state collapse sidebar dari localStorage (desktop)
    const savedSidebarState = localStorage.getItem("sidebarCollapsed");
    if (savedSidebarState !== null) {
        isCollapseSidebar.value = savedSidebarState === "true";
        console.log("📂 Sidebar collapse state:", isCollapseSidebar.value);
    }
});

const pages = defineModel("pages", {
    type: String,
});

const subMenu = defineModel("subMenu", {
    type: String,
});

const mainMenu = defineModel("mainMenu", {
    type: String,
});
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

    <DashboardAside
        v-model:isMobileSidebar="isActive"
        v-model:isCollapseSidebar="isCollapseSidebar"
        @toggleCollapse="handleCollapseSidebar"
    />

    <!-- Page Content -->
    <main
        :class="[
            'relative flex-1 min-h-0 overflow-auto transition-all duration-200 ease-in-out rounded-xl pt-1',
            isCollapseSidebar ? 'xl:ml-25' : 'xl:ml-68',
        ]"
        style="will-change: margin"
    >
        <audio
            id="notifSound"
            src="/sounds/aduan.mp3"
            muted
            autoplay
            preload="auto"
        ></audio>
        <DashboardNavbar
            @toggleMobileSidebar="handleMobileSidebar"
            @toggleConfig="handleConfigurator"
            v-model:isCollapseIconActive="isActive"
            v-model:pages="pages"
            v-model:subMenu="subMenu"
            v-model:mainMenu="mainMenu"
        />

        <div class="w-full px-6 py-6 mx-auto">
            <slot />
        </div>
        <DashboardFooter />
    </main>
    <DashboardConfig
        ref="configurator"
        v-model:isConfiguratorActive="isConfiguratorActive"
    />
</template>
