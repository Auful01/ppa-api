<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import NavLinkCustom from "@/Components/NavLinkCustom.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import moment from "moment";
import Swal from "sweetalert2";
import { onMounted, ref } from "vue";

const pages = ref("Pages");
const subMenu = ref("Inspeksi PC Pages");
const mainMenu = ref("Form Detail Inspeksi PC");

const props = defineProps(["inspeksi"]);
const inspeksiId = props.inspeksi.id;

// Fungsi untuk format tanggal
function formattedDate(date) {
    return moment(date).format("MMMM Do, YYYY"); // Sesuaikan format sesuai kebutuhan
}

const mount = onMounted(() => {
    // Inisialisasi DataTable tanpa AJAX
    $("#tableData").DataTable();
    $("#tableData2").DataTable();
    $("#tableData3").DataTable();
});

const getEncryptedQuarter = () => {
    // Tampilkan loading popup
    Swal.fire({
        title: "Menyiapkan PDF...",
        text: "Harap tunggu sebentar.",
        allowOutsideClick: false,
        showConfirmButton: false,
        timer: 2000, // Timer 5 detik
        timerProgressBar: true, // Tampilkan garis waktu
        didOpen: () => {
            Swal.showLoading();
        },
        willClose: () => {
            console.log("Popup PDF selesai otomatis."); // Opsional
        },
    });

    // Kirim permintaan ke backend untuk enkripsi tahun
    router.post(
        route("computer.singleExport"),
        { inspeksiId: inspeksiId },
        {
            onSuccess: ({ props }) => {
                Swal.close(); // Tutup popup loading setelah selesai
                window.open(
                    route("computer.singleExportPdf", {
                        inspeksiId: inspeksiId,
                    }),
                    "_blank"
                );
            },
            onError: () => {
                Swal.close(); // Tutup popup loading jika ada error
                Swal.fire(
                    "Gagal!",
                    "Terjadi kesalahan dalam permintaan.",
                    "error"
                );
            },
        }
    );
};
</script>

<template>
    <Head title="Detail Inspeksi" />

    <AuthenticatedLayout
        v-model:pages="pages"
        v-model:subMenu="subMenu"
        v-model:mainMenu="mainMenu"
    >
        <div class="py-12">
            <div class="min-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="flex flex-wrap md:flex-nowrap gap-4">
                    <button
                        @click="getEncryptedQuarter"
                        class="flex items-center text-sm justify-center gap-2 w-60 h-10 bg-gray-800 text-white font-semibold rounded-lg shadow-md transition-all duration-300 ease-in-out transform hover:bg-slate-850 hover:scale-105"
                    >
                        <i class="fas fa-download"></i>
                        Download Hasil Inspeksi
                    </button>
                </div>
                <div class="flex flex-wrap -mx-3">
                    <div
                        class="w-full max-w-full px-3 mt-6 md:w-7/12 md:flex-none"
                    >
                        <div
                            class="relative flex flex-col min-w-0 break-words bg-white border-0 shadow-xl dark:bg-slate-850 dark:shadow-dark-xl rounded-2xl bg-clip-border"
                        >
                            <div
                                class="flex flex-row p-6 px-4 pb-0 mb-0 border-b-0 rounded-t-2xl"
                            >
                                <h4 class="mb-0 mr-3 dark:text-white">
                                    Form Detail Inspeksi PC
                                </h4>
                                <NavLinkCustom
                                    class="text-red-700"
                                    :href="route('inspeksiKomputerSbs.page')"
                                >
                                    Move to home page
                                </NavLinkCustom>
                            </div>
                            <div class="flex flex-wrap -mx-3 p-12">
                                <div
                                    class="border-black/12.5 rounded-t-2xl border-b-0 border-solid p-6 pb-0 w-full max-w-full px-3 shrink-0 md:w-12/12 md:flex-0"
                                >
                                    <div class="text-center mb-4">
                                        <p
                                            class="mb-0 dark:text-white/80 font-semibold"
                                        >
                                            INSPEKSI DAN PERAWATAN HARDWARE
                                        </p>
                                        <hr
                                            class="h-px mx-0 my-4 bg-transparent border-0 opacity-25 bg-gradient-to-r from-transparent via-black/40 to-transparent dark:bg-gradient-to-r dark:from-transparent dark:via-white dark:to-transparent"
                                        />
                                    </div>
                                </div>

                                <div
                                    class="w-full max-w-full px-3 shrink-0 md:w-3/12 md:flex-0"
                                >
                                    <div class="mb-4">
                                        <label
                                            for="number-asset-ho"
                                            class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                            >Check dan Pembersihan Bagian Luar
                                            CPU</label
                                        >
                                    </div>
                                </div>

                                <div
                                    class="w-full max-w-full px-3 shrink-0 md:w-3/12 md:flex-0"
                                >
                                    <div class="mb-4">
                                        <label
                                            for="device-name"
                                            class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80 font-bold"
                                        >
                                            :
                                            {{
                                                inspeksi.physique_condition_cpu ==
                                                "N"
                                                    ? "Tidak"
                                                    : "Ya"
                                            }}</label
                                        >
                                    </div>
                                </div>

                                <div
                                    class="w-full max-w-full px-3 shrink-0 md:w-3/12 md:flex-0"
                                >
                                    <div class="mb-4">
                                        <label
                                            for="number-asset-ho"
                                            class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                            >Pembersihan Bagian Internal
                                            CPU</label
                                        >
                                    </div>
                                </div>

                                <div
                                    class="w-full max-w-full px-3 shrink-0 md:w-3/12 md:flex-0"
                                >
                                    <div class="mb-4">
                                        <label
                                            for="device-name"
                                            class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80 font-bold"
                                        >
                                            :
                                            {{
                                                inspeksi.physique_condition_internal_cpu ==
                                                "N"
                                                    ? "Tidak"
                                                    : "Ya"
                                            }}</label
                                        >
                                    </div>
                                </div>

                                <div
                                    class="w-full max-w-full px-3 shrink-0 md:w-3/12 md:flex-0"
                                >
                                    <div class="mb-4">
                                        <label
                                            for="assets-category"
                                            class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                            >MONITOR</label
                                        >
                                    </div>
                                </div>
                                <div
                                    class="w-full max-w-full px-3 shrink-0 md:w-3/12 md:flex-0"
                                >
                                    <div class="mb-4">
                                        <label
                                            for="device-name"
                                            class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80 font-bold"
                                        >
                                            :
                                            {{
                                                inspeksi.physique_condition_monitor ==
                                                "N"
                                                    ? "Tidak"
                                                    : "Ya"
                                            }}</label
                                        >
                                    </div>
                                </div>

                                <div
                                    class="border-black/12.5 rounded-t-2xl border-b-0 border-solid p-6 pb-0 w-full max-w-full px-3 shrink-0 md:w-12/12 md:flex-0"
                                >
                                    <div class="text-center mb-4">
                                        <p
                                            class="mb-0 dark:text-white/80 font-semibold"
                                        >
                                            PEMERIKSAAN SOFTWARE
                                        </p>
                                        <hr
                                            class="h-px mx-0 my-4 bg-gradient-to-r from-transparent via-black/40 to-transparent border-0 opacity-100"
                                        />
                                    </div>
                                </div>

                                <div
                                    class="w-full max-w-full px-3 shrink-0 md:w-3/12 md:flex-0"
                                >
                                    <div class="mb-4">
                                        <label
                                            for="aplikasi"
                                            class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                            >Standarisasi Software</label
                                        >
                                    </div>
                                </div>
                                <div
                                    class="w-full max-w-full px-3 shrink-0 md:w-3/12 md:flex-0"
                                >
                                    <div class="mb-4">
                                        <label
                                            for="device-name"
                                            class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80 font-bold"
                                        >
                                            :
                                            {{
                                                inspeksi.software_license == "N"
                                                    ? "Tidak"
                                                    : "Ya"
                                            }}</label
                                        >
                                    </div>
                                </div>
                                <div
                                    class="w-full max-w-full px-3 shrink-0 md:w-3/12 md:flex-0"
                                >
                                    <div class="mb-4">
                                        <label
                                            for="license"
                                            class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                            >Standarisasi Device Name</label
                                        >
                                    </div>
                                </div>
                                <div
                                    class="w-full max-w-full px-3 shrink-0 md:w-3/12 md:flex-0"
                                >
                                    <div class="mb-4">
                                        <label
                                            for="device-name"
                                            class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80 font-bold"
                                        >
                                            :
                                            {{
                                                inspeksi.software_device_name_standaritation ==
                                                "N"
                                                    ? "Tidak"
                                                    : "Ya"
                                            }}</label
                                        >
                                    </div>
                                </div>

                                <div
                                    class="w-full max-w-full px-3 shrink-0 md:w-3/12 md:flex-0"
                                >
                                    <div class="mb-4">
                                        <label
                                            for="ip-address"
                                            class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                            >License Software</label
                                        >
                                    </div>
                                </div>
                                <div
                                    class="w-full max-w-full px-3 shrink-0 md:w-3/12 md:flex-0"
                                >
                                    <div class="mb-4">
                                        <label
                                            for="device-name"
                                            class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80 font-bold"
                                        >
                                            :
                                            {{
                                                inspeksi.software_license == "N"
                                                    ? "Tidak"
                                                    : "Ya"
                                            }}</label
                                        >
                                    </div>
                                </div>

                                <div
                                    class="w-full max-w-full px-3 shrink-0 md:w-3/12 md:flex-0"
                                >
                                    <div class="mb-4">
                                        <label
                                            for="ip-address"
                                            class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                            >Clear Cache</label
                                        >
                                    </div>
                                </div>
                                <div
                                    class="w-full max-w-full px-3 shrink-0 md:w-3/12 md:flex-0"
                                >
                                    <div class="mb-4">
                                        <label
                                            for="device-name"
                                            class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80 font-bold"
                                        >
                                            :
                                            {{
                                                inspeksi.software_clear_cache ==
                                                "N"
                                                    ? "Tidak"
                                                    : "Ya"
                                            }}</label
                                        >
                                    </div>
                                </div>

                                <div
                                    class="w-full max-w-full px-3 shrink-0 md:w-3/12 md:flex-0"
                                >
                                    <div class="mb-4">
                                        <label
                                            for="ssd_persen"
                                            class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                            >System Restore</label
                                        >
                                    </div>
                                </div>
                                <div
                                    class="w-full max-w-full px-3 shrink-0 md:w-3/12 md:flex-0"
                                >
                                    <div class="mb-4">
                                        <label
                                            for="device-name"
                                            class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80 font-bold"
                                        >
                                            :
                                            {{
                                                inspeksi.software_system_restore ==
                                                "N"
                                                    ? "Tidak"
                                                    : "Ya"
                                            }}</label
                                        >
                                    </div>
                                </div>

                                <div
                                    class="w-full max-w-full px-3 shrink-0 md:w-3/12 md:flex-0"
                                >
                                    <div class="mb-4">
                                        <label
                                            for="ssd_persen"
                                            class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                            >Check Windows Update</label
                                        >
                                    </div>
                                </div>
                                <div
                                    class="w-full max-w-full px-3 shrink-0 md:w-3/12 md:flex-0"
                                >
                                    <div class="mb-4">
                                        <label
                                            for="device-name"
                                            class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80 font-bold"
                                        >
                                            :
                                            {{
                                                inspeksi.software_windows_update
                                            }}</label
                                        >
                                    </div>
                                </div>

                                <div
                                    class="w-full max-w-full px-3 shrink-0 md:w-3/12 md:flex-0"
                                >
                                    <div class="mb-4">
                                        <label
                                            for="device-name"
                                            class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                            >Defrag</label
                                        >
                                    </div>
                                </div>
                                <div
                                    class="w-full max-w-full px-3 shrink-0 md:w-3/12 md:flex-0"
                                >
                                    <div class="mb-4">
                                        <label
                                            for="device-name"
                                            class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80 font-bold"
                                        >
                                            :
                                            {{
                                                inspeksi.defrag == "N"
                                                    ? "Tidak"
                                                    : "Ya"
                                            }}</label
                                        >
                                    </div>
                                </div>

                                <div
                                    class="w-full max-w-full px-3 shrink-0 md:w-3/12 md:flex-0"
                                >
                                    <div class="mb-4">
                                        <label
                                            for="device-name"
                                            class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                            >SSD/HDD Health</label
                                        >
                                    </div>
                                </div>
                                <div
                                    class="w-full max-w-full px-3 shrink-0 md:w-3/12 md:flex-0"
                                >
                                    <div class="mb-4">
                                        <label
                                            for="device-name"
                                            class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80 font-bold"
                                        >
                                            :
                                            {{
                                                inspeksi.software_storage_health
                                            }}</label
                                        >
                                    </div>
                                </div>

                                <div
                                    class="border-black/12.5 rounded-t-2xl border-b-0 border-solid p-6 pb-0 w-full max-w-full px-3 shrink-0 md:w-12/12 md:flex-0"
                                >
                                    <div class="text-center mb-4">
                                        <p
                                            class="mb-0 dark:text-white/80 font-semibold"
                                        >
                                            PEMERIKSAAN SECURITY
                                        </p>
                                        <hr
                                            class="h-px mx-0 my-4 bg-transparent border-0 opacity-25 bg-gradient-to-r from-transparent via-black/40 to-transparent dark:bg-gradient-to-r dark:from-transparent dark:via-white dark:to-transparent"
                                        />
                                    </div>
                                </div>

                                <div
                                    class="w-full max-w-full px-3 shrink-0 md:w-3/12 md:flex-0"
                                >
                                    <div class="mb-4">
                                        <label
                                            for="number-asset-ho"
                                            class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                            >Penggantian Username</label
                                        >
                                    </div>
                                </div>

                                <div
                                    class="w-full max-w-full px-3 shrink-0 md:w-3/12 md:flex-0"
                                >
                                    <div class="mb-4">
                                        <label
                                            for="device-name"
                                            class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80 font-bold"
                                        >
                                            :
                                            {{
                                                inspeksi.security_change_password ==
                                                "N"
                                                    ? "Tidak"
                                                    : "Ya"
                                            }}</label
                                        >
                                    </div>
                                </div>

                                <div
                                    class="w-full max-w-full px-3 shrink-0 md:w-3/12 md:flex-0"
                                >
                                    <div class="mb-4">
                                        <label
                                            for="number-asset-ho"
                                            class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                            >Setting Auto Lock Screen</label
                                        >
                                    </div>
                                </div>

                                <div
                                    class="w-full max-w-full px-3 shrink-0 md:w-3/12 md:flex-0"
                                >
                                    <div class="mb-4">
                                        <label
                                            for="device-name"
                                            class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80 font-bold"
                                        >
                                            :
                                            {{
                                                inspeksi.security_auto_lock ==
                                                "N"
                                                    ? "Tidak"
                                                    : "Ya"
                                            }}</label
                                        >
                                    </div>
                                </div>

                                <div
                                    class="w-full max-w-full px-3 shrink-0 md:w-3/12 md:flex-0"
                                >
                                    <div class="mb-4">
                                        <label
                                            for="assets-category"
                                            class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                            >Setting Input Password After Lock
                                            Screen</label
                                        >
                                    </div>
                                </div>
                                <div
                                    class="w-full max-w-full px-3 shrink-0 md:w-3/12 md:flex-0"
                                >
                                    <div class="mb-4">
                                        <label
                                            for="device-name"
                                            class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80 font-bold"
                                        >
                                            :
                                            {{
                                                inspeksi.security_input_password ==
                                                "N"
                                                    ? "Tidak"
                                                    : "Ya"
                                            }}</label
                                        >
                                    </div>
                                </div>

                                <div
                                    class="w-full max-w-full px-3 shrink-0 md:w-3/12 md:flex-0"
                                >
                                    <div class="mb-4">
                                        <label
                                            for="assets-category"
                                            class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                        ></label>
                                    </div>
                                </div>
                                <div
                                    class="w-full max-w-full px-3 shrink-0 md:w-3/12 md:flex-0"
                                >
                                    <div class="mb-4">
                                        <label
                                            for="device-name"
                                            class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80 font-bold"
                                        >
                                        </label>
                                    </div>
                                </div>

                                <div
                                    class="w-full max-w-full px-3 shrink-0 md:w-3/12 md:flex-0"
                                >
                                    <div class="mb-4">
                                        <label
                                            for="condition"
                                            class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                            >Condition</label
                                        >
                                    </div>
                                </div>
                                <div
                                    class="w-full max-w-full px-3 shrink-0 md:w-3/12 md:flex-0"
                                >
                                    <div class="mb-4">
                                        <label
                                            for="device-name"
                                            class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80 font-bold"
                                        >
                                            :
                                            {{
                                                inspeksi.conditions == ""
                                                    ? "-"
                                                    : inspeksi.conditions
                                            }}</label
                                        >
                                    </div>
                                </div>

                                <div
                                    class="w-full max-w-full px-3 shrink-0 md:w-3/12 md:flex-0"
                                >
                                    <div class="mb-4">
                                        <label
                                            for="status"
                                            class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                        >
                                            Status Inventory</label
                                        >
                                    </div>
                                </div>
                                <div
                                    class="w-full max-w-full px-3 shrink-0 md:w-3/12 md:flex-0"
                                >
                                    <div class="mb-4">
                                        <label
                                            for="device-name"
                                            class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80 font-bold"
                                        >
                                            :
                                            {{
                                                inspeksi.inventory_status == "-"
                                                    ? "-"
                                                    : inspeksi.inventory_status
                                            }}</label
                                        >
                                    </div>
                                </div>

                                <div
                                    class="border-black/12.5 rounded-t-2xl border-b-0 border-solid p-6 pb-0 w-full max-w-full px-3 shrink-0 md:w-12/12 md:flex-0"
                                >
                                    <div class="text-center mb-4">
                                        <p
                                            class="mb-0 dark:text-white/80 font-semibold"
                                        >
                                            Temuan
                                        </p>

                                        <hr
                                            class="h-px mx-0 my-4 bg-transparent border-0 opacity-25 bg-gradient-to-r from-transparent via-black/40 to-transparent dark:bg-gradient-to-r dark:from-transparent dark:via-white dark:to-transparent"
                                        />
                                    </div>
                                </div>

                                <div
                                    class="w-full max-w-full px-3 shrink-0 md:w-4/12 md:flex-0"
                                >
                                    <div class="mb-4">
                                        <label
                                            for="temuan"
                                            class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                            >Deskripsi Temuan</label
                                        >
                                    </div>
                                </div>
                                <div
                                    class="w-full max-w-full px-3 shrink-0 md:w-6/12 md:flex-0"
                                >
                                    <div class="mb-4">
                                        <label
                                            for="device-name"
                                            class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80 font-bold"
                                        >
                                            :
                                            {{
                                                inspeksi.findings == null
                                                    ? "-"
                                                    : inspeksi.findings
                                            }}</label
                                        >
                                    </div>
                                </div>

                                <div
                                    class="w-full max-w-full px-3 shrink-0 md:w-4/12 md:flex-0"
                                >
                                    <div class="mb-4">
                                        <label
                                            for="findings_image"
                                            class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                            >Lampiran Foto Temuan</label
                                        >
                                    </div>
                                </div>
                                <div
                                    class="w-full max-w-full px-3 shrink-0 md:w-6/12 md:flex-0"
                                >
                                    <div
                                        v-if="inspeksi.findings_image != null"
                                        class="mb-4"
                                    >
                                        <img
                                            :src="inspeksi.findings_image"
                                            alt="Foto Temuan"
                                            class="w-50 h-30 shadow-2xl rounded-xl"
                                        />
                                    </div>
                                </div>

                                <div
                                    class="w-full max-w-full px-3 shrink-0 md:w-4/12 md:flex-0"
                                >
                                    <div class="mb-4">
                                        <label
                                            for="tindakan"
                                            class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                            >Tindak Lanjut</label
                                        >
                                    </div>
                                </div>
                                <div
                                    class="w-full max-w-full px-3 shrink-0 md:w-6/12 md:flex-0"
                                >
                                    <div class="mb-4">
                                        <label
                                            for="device-name"
                                            class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80 font-bold"
                                        >
                                            :
                                            {{
                                                inspeksi.findings_action == null
                                                    ? "-"
                                                    : inspeksi.findings_action
                                            }}</label
                                        >
                                    </div>
                                </div>

                                <div
                                    class="w-full max-w-full px-3 shrink-0 md:w-4/12 md:flex-0"
                                >
                                    <div class="mb-4">
                                        <label
                                            for="action_image"
                                            class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                            >Lampiran Foto Tindakan</label
                                        >
                                    </div>
                                </div>
                                <div
                                    class="w-full max-w-full px-3 shrink-0 md:w-6/12 md:flex-0"
                                >
                                    <div
                                        v-if="inspeksi.action_image != null"
                                        class="mb-4"
                                    >
                                        <img
                                            :src="inspeksi.action_image"
                                            alt="Foto Tindakan"
                                            class="w-50 h-30 shadow-2xl rounded-xl"
                                        />
                                    </div>
                                </div>

                                <div
                                    class="w-full max-w-full px-3 shrink-0 md:w-4/12 md:flex-0"
                                >
                                    <div class="mb-4">
                                        <label
                                            for="date-of-inventory"
                                            class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                            >Due Date</label
                                        >
                                    </div>
                                </div>
                                <div
                                    class="w-full max-w-full px-3 shrink-0 md:w-6/12 md:flex-0"
                                >
                                    <div class="mb-4">
                                        <label
                                            for="device-name"
                                            class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80 font-bold"
                                        >
                                            :
                                            {{
                                                inspeksi.due_date == null
                                                    ? "-"
                                                    : formattedDate(
                                                          inspeksi.due_date
                                                      )
                                            }}</label
                                        >
                                    </div>
                                </div>

                                <div
                                    class="w-full max-w-full px-3 shrink-0 md:w-4/12 md:flex-0"
                                >
                                    <div class="mb-4">
                                        <label
                                            for="findings_status"
                                            class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                        >
                                            Status Temuan</label
                                        >
                                    </div>
                                </div>
                                <div
                                    class="w-full max-w-full px-3 shrink-0 md:w-6/12 md:flex-0"
                                >
                                    <div class="mb-4">
                                        <label
                                            for="device-name"
                                            class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80 font-bold"
                                        >
                                            :
                                            {{
                                                inspeksi.findings_status == null
                                                    ? "-"
                                                    : inspeksi.findings_status
                                            }}</label
                                        >
                                    </div>
                                </div>

                                <div
                                    class="w-full max-w-full px-3 shrink-0 md:w-4/12 md:flex-0"
                                >
                                    <div class="mb-4">
                                        <label
                                            for="device-location"
                                            class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                            >Remark</label
                                        >
                                    </div>
                                </div>
                                <div
                                    class="w-full max-w-full px-3 shrink-0 md:w-6/12 md:flex-0"
                                >
                                    <div class="mb-4">
                                        <label
                                            for="device-name"
                                            class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80 font-bold"
                                        >
                                            :
                                            {{
                                                inspeksi.remarks == null
                                                    ? "-"
                                                    : inspeksi.remarks
                                            }}</label
                                        >
                                    </div>
                                </div>

                                <div
                                    class="w-full max-w-full px-3 shrink-0 md:w-4/12 md:flex-0"
                                >
                                    <div class="mb-4">
                                        <label
                                            for="select_pic"
                                            class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                        >
                                            PIC</label
                                        >
                                    </div>
                                </div>
                                <div
                                    class="w-full max-w-full px-3 shrink-0 md:w-6/12 md:flex-0"
                                >
                                    <div class="mb-4">
                                        <label
                                            for="device-name"
                                            class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80 font-bold"
                                        >
                                            :
                                            {{
                                                inspeksi.inspector == ""
                                                    ? "-"
                                                    : inspeksi.inspector
                                            }}</label
                                        >
                                    </div>
                                </div>

                                <div
                                    class="w-full max-w-full px-3 shrink-0 md:w-4/12 md:flex-0"
                                >
                                    <div class="mb-4">
                                        <label
                                            for="inspection_image"
                                            class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                            >Lampiran Foto Inspeksi</label
                                        >
                                    </div>
                                </div>
                                <div
                                    class="w-full max-w-full px-3 shrink-0 md:w-6/12 md:flex-0"
                                >
                                    <div
                                        v-if="inspeksi.inspection_image != null"
                                        class="mb-4"
                                    >
                                        <img
                                            :src="inspeksi.inspection_image"
                                            alt="Foto Inspeksi"
                                            class="w-50 h-30 shadow-2xl rounded-xl"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        class="w-full max-w-full px-3 mt-6 md:w-5/12 md:flex-none"
                    >
                        <div
                            class="relative flex flex-col h-full min-w-0 mb-6 break-words bg-white border-0 shadow-xl dark:bg-slate-850 dark:shadow-dark-xl rounded-2xl bg-clip-border"
                        >
                            <div
                                class="p-6 px-4 pb-0 mb-0 border-b-0 rounded-t-2xl"
                            >
                                <div class="flex flex-wrap -mx-3 mb-6">
                                    <div
                                        class="max-w-full px-3 md:w-1/2 md:flex-none"
                                    >
                                        <h4 class="mb-0 dark:text-white">
                                            Data Aset PC
                                        </h4>
                                    </div>
                                    <div
                                        class="flex items-center justify-end max-w-full px-3 dark:text-white/80 md:w-1/2 md:flex-none"
                                    >
                                        <small>last maintenance: </small>
                                        <small class="ml-2">{{
                                            formattedDate(
                                                inspeksi.inspection_at
                                            )
                                        }}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="flex-auto p-4 pt-6">
                                <div class="grid grid-cols-2">
                                    <div>
                                        <p class="text-base">Iventory Number</p>
                                    </div>
                                    <div>
                                        <p>
                                            :
                                            {{
                                                inspeksi.computer.computer_code
                                            }}
                                        </p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2">
                                    <div>
                                        <p class="text-base">Asset Ho Number</p>
                                    </div>
                                    <div>
                                        <p>
                                            :
                                            {{
                                                inspeksi.computer
                                                    .number_asset_ho
                                            }}
                                        </p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2">
                                    <div>
                                        <p class="text-base">Pengguna</p>
                                    </div>
                                    <div>
                                        <p>
                                            :
                                            {{
                                                inspeksi.computer.pengguna
                                                    .username
                                            }}
                                        </p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2">
                                    <div>
                                        <p class="text-base">Brand</p>
                                    </div>
                                    <div>
                                        <p>
                                            :
                                            {{
                                                inspeksi.computer.computer_name
                                            }}
                                        </p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2">
                                    <div>
                                        <p class="text-base">Category Asset</p>
                                    </div>
                                    <div>
                                        <p>
                                            :
                                            {{
                                                inspeksi.computer
                                                    .assets_category
                                            }}
                                        </p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2">
                                    <div>
                                        <p class="text-base">Spesifikasi</p>
                                    </div>
                                    <div>
                                        <p>
                                            :
                                            {{ inspeksi.computer.spesifikasi }}
                                        </p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2">
                                    <div>
                                        <p class="text-base">Serial Number</p>
                                    </div>
                                    <div>
                                        <p>
                                            :
                                            {{
                                                inspeksi.computer.serial_number
                                            }}
                                        </p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2">
                                    <div>
                                        <p class="text-base">Aplikasi</p>
                                    </div>
                                    <div>
                                        <p>
                                            : {{ inspeksi.computer.aplikasi }}
                                        </p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2">
                                    <div>
                                        <p class="text-base">License</p>
                                    </div>
                                    <div>
                                        <p>: {{ inspeksi.computer.license }}</p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2">
                                    <div>
                                        <p class="text-base">Ip Address</p>
                                    </div>
                                    <div>
                                        <p>
                                            : {{ inspeksi.computer.ip_address }}
                                        </p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2">
                                    <div>
                                        <p class="text-base">
                                            Date Of Inventory
                                        </p>
                                    </div>
                                    <div>
                                        <p
                                            v-if="
                                                inspeksi.computer
                                                    .date_of_inventory
                                            "
                                        >
                                            :
                                            {{
                                                formattedDate(
                                                    inspeksi.computer
                                                        .date_of_inventory
                                                )
                                            }}
                                        </p>
                                        <p v-else>
                                            : Edit untuk setting tanggal !
                                        </p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2">
                                    <div>
                                        <p class="text-base">Date Of Deploy</p>
                                    </div>
                                    <div>
                                        <p
                                            v-if="
                                                inspeksi.computer.date_of_deploy
                                            "
                                        >
                                            :
                                            {{
                                                formattedDate(
                                                    inspeksi.computer
                                                        .date_of_deploy
                                                )
                                            }}
                                        </p>
                                        <p v-else>
                                            : Edit untuk setting tanggal !
                                        </p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2">
                                    <div>
                                        <p class="text-base">Location</p>
                                    </div>
                                    <div>
                                        <p>
                                            : {{ inspeksi.computer.location }}
                                        </p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2">
                                    <div>
                                        <p class="text-base">Status</p>
                                    </div>
                                    <div>
                                        <p>: {{ inspeksi.computer.status }}</p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2">
                                    <div>
                                        <p class="text-base">Condition</p>
                                    </div>
                                    <div>
                                        <p>
                                            : {{ inspeksi.computer.condition }}
                                        </p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2">
                                    <div>
                                        <p class="text-base">Note</p>
                                    </div>
                                    <div>
                                        <p>: {{ inspeksi.computer.note }}</p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2">
                                    <div>
                                        <p class="text-base">
                                            Documentation Asset
                                        </p>
                                    </div>
                                    <div>
                                        <p
                                            v-if="
                                                inspeksi.computer
                                                    .link_documentation_asset_image !=
                                                null
                                            "
                                        >
                                            <img
                                                :src="
                                                    inspeksi.computer
                                                        .link_documentation_asset_image
                                                "
                                                alt="documentation image"
                                                class="w-50 h-30 shadow-2xl rounded-xl"
                                            />
                                        </p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2">
                                    <div>
                                        <p class="text-base">Last Edit At</p>
                                    </div>
                                    <div>
                                        <p>
                                            :
                                            {{
                                                formattedDate(
                                                    inspeksi.computer.updated_at
                                                )
                                            }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
