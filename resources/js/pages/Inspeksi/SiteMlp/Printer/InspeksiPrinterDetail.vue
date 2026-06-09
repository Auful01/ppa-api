<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import NavLinkCustom from "@/Components/NavLinkCustom.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import moment from "moment";
import Swal from "sweetalert2";
import { onMounted, ref } from "vue";

const pages = ref("Pages");
const subMenu = ref("Inspeksi Printer Pages");
const mainMenu = ref("Form Detail Inspeksi Printer");

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

const getEncryptedYear = () => {
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
    console.log(inspeksiId);
    router.post(
        route("printer.singleExport"),
        { inspeksiId: inspeksiId },
        {
            onSuccess: ({ props }) => {
                Swal.close();
                window.open(
                    route("export.inspectionPrinterSingle", {
                        inspeksiId: inspeksiId,
                    }),
                    "_blank"
                );
            },
            onError: () => {
                Swal.close();
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
                        @click="getEncryptedYear"
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
                                    Form Detail Inspeksi Printer
                                </h4>
                                <NavLinkCustom
                                    class="text-red-700"
                                    :href="route('inspeksiPrinterMlp.page')"
                                >
                                    Move to home page
                                </NavLinkCustom>
                            </div>
                            <div class="flex flex-wrap -mx-3 p-12">
                                <!-- ========================= -->
                                <!-- HARDWARE DETAIL & CONDITION -->
                                <!-- ========================= -->
                                <div
                                    class="border-black/12.5 rounded-t-2xl border-b-0 border-solid p-6 pb-0 w-full max-w-full px-3 shrink-0 md:w-12/12 md:flex-0"
                                >
                                    <div class="text-center mb-4">
                                        <p
                                            class="mb-0 dark:text-white/80 font-semibold"
                                        >
                                            HARDWARE DETAIL & CONDITION
                                        </p>
                                        <hr
                                            class="h-px mx-0 my-4 bg-transparent border-0 opacity-25 bg-gradient-to-r from-transparent via-black/40 to-transparent dark:bg-gradient-to-r dark:from-transparent dark:via-white dark:to-transparent"
                                        />
                                    </div>
                                </div>

                                <!-- Tinta Cyan -->
                                <div
                                    class="w-full max-w-full px-3 shrink-0 md:w-3/12 md:flex-0"
                                >
                                    <div class="mb-4">
                                        <label
                                            class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                            >Tinta Cyan</label
                                        >
                                    </div>
                                </div>
                                <div
                                    class="w-full max-w-full px-3 shrink-0 md:w-3/12 md:flex-0"
                                >
                                    <div class="mb-4">
                                        <label
                                            class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80 font-bold"
                                        >
                                            :
                                            {{
                                                inspeksi.tinta_cyan == "N"
                                                    ? "Tidak Aman"
                                                    : "Aman"
                                            }}
                                        </label>
                                    </div>
                                </div>

                                <!-- Tinta Magenta -->
                                <div
                                    class="w-full max-w-full px-3 shrink-0 md:w-3/12 md:flex-0"
                                >
                                    <div class="mb-4">
                                        <label
                                            class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                            >Tinta Magenta</label
                                        >
                                    </div>
                                </div>
                                <div
                                    class="w-full max-w-full px-3 shrink-0 md:w-3/12 md:flex-0"
                                >
                                    <div class="mb-4">
                                        <label
                                            class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80 font-bold"
                                        >
                                            :
                                            {{
                                                inspeksi.tinta_magenta == "N"
                                                    ? "Tidak Aman"
                                                    : "Aman"
                                            }}
                                        </label>
                                    </div>
                                </div>

                                <!-- Tinta Yellow -->
                                <div
                                    class="w-full max-w-full px-3 shrink-0 md:w-3/12 md:flex-0"
                                >
                                    <div class="mb-4">
                                        <label
                                            class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                            >Tinta Yellow</label
                                        >
                                    </div>
                                </div>
                                <div
                                    class="w-full max-w-full px-3 shrink-0 md:w-3/12 md:flex-0"
                                >
                                    <div class="mb-4">
                                        <label
                                            class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80 font-bold"
                                        >
                                            :
                                            {{
                                                inspeksi.tinta_yellow == "N"
                                                    ? "Tidak Aman"
                                                    : "Aman"
                                            }}
                                        </label>
                                    </div>
                                </div>

                                <!-- Tinta Black -->
                                <div
                                    class="w-full max-w-full px-3 shrink-0 md:w-3/12 md:flex-0"
                                >
                                    <div class="mb-4">
                                        <label
                                            class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                            >Tinta Black</label
                                        >
                                    </div>
                                </div>
                                <div
                                    class="w-full max-w-full px-3 shrink-0 md:w-3/12 md:flex-0"
                                >
                                    <div class="mb-4">
                                        <label
                                            class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80 font-bold"
                                        >
                                            :
                                            {{
                                                inspeksi.tinta_black == "N"
                                                    ? "Tidak Aman"
                                                    : "Aman"
                                            }}
                                        </label>
                                    </div>
                                </div>

                                <!-- Kondisi Body / Cover -->
                                <div
                                    class="w-full max-w-full px-3 shrink-0 md:w-3/12 md:flex-0"
                                >
                                    <div class="mb-4">
                                        <label
                                            class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                            >Kondisi Body / Cover</label
                                        >
                                    </div>
                                </div>
                                <div
                                    class="w-full max-w-full px-3 shrink-0 md:w-3/12 md:flex-0"
                                >
                                    <div class="mb-4">
                                        <label
                                            class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80 font-bold"
                                        >
                                            :
                                            {{
                                                inspeksi.body_condition == "N"
                                                    ? "Tidak Aman"
                                                    : "Aman"
                                            }}
                                        </label>
                                    </div>
                                </div>

                                <!-- Kondisi Kabel USB -->
                                <div
                                    class="w-full max-w-full px-3 shrink-0 md:w-3/12 md:flex-0"
                                >
                                    <div class="mb-4">
                                        <label
                                            class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                            >Kondisi Kabel USB</label
                                        >
                                    </div>
                                </div>
                                <div
                                    class="w-full max-w-full px-3 shrink-0 md:w-3/12 md:flex-0"
                                >
                                    <div class="mb-4">
                                        <label
                                            class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80 font-bold"
                                        >
                                            :
                                            {{
                                                inspeksi.usb_cable_condition ==
                                                "N"
                                                    ? "Tidak Aman"
                                                    : "Aman"
                                            }}
                                        </label>
                                    </div>
                                </div>

                                <!-- Kondisi Kabel Power -->
                                <div
                                    class="w-full max-w-full px-3 shrink-0 md:w-3/12 md:flex-0"
                                >
                                    <div class="mb-4">
                                        <label
                                            class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                            >Kondisi Kabel Power</label
                                        >
                                    </div>
                                </div>
                                <div
                                    class="w-full max-w-full px-3 shrink-0 md:w-3/12 md:flex-0"
                                >
                                    <div class="mb-4">
                                        <label
                                            class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80 font-bold"
                                        >
                                            :
                                            {{
                                                inspeksi.power_cable_condition ==
                                                "N"
                                                    ? "Tidak Aman"
                                                    : "Aman"
                                            }}
                                        </label>
                                    </div>
                                </div>

                                <!-- ========================= -->
                                <!-- MAINTENANCE ACTIVITY -->
                                <!-- ========================= -->
                                <div
                                    class="border-black/12.5 rounded-t-2xl border-b-0 border-solid p-6 pb-0 w-full max-w-full px-3 shrink-0 md:w-12/12 md:flex-0"
                                >
                                    <div class="text-center mb-4">
                                        <p
                                            class="mb-0 dark:text-white/80 font-semibold"
                                        >
                                            MAINTENANCE ACTIVITY
                                        </p>
                                        <hr
                                            class="h-px mx-0 my-4 bg-transparent border-0 opacity-25 bg-gradient-to-r from-transparent via-black/40 to-transparent dark:bg-gradient-to-r dark:from-transparent dark:via-white dark:to-transparent"
                                        />
                                    </div>
                                </div>

                                <!-- Melakukan Pembersihan Fisik Power -->
                                <div
                                    class="w-full max-w-full px-3 shrink-0 md:w-3/12 md:flex-0"
                                >
                                    <div class="mb-4">
                                        <label
                                            class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                            >Melakukan Pembersihan Fisik
                                            Power</label
                                        >
                                    </div>
                                </div>
                                <div
                                    class="w-full max-w-full px-3 shrink-0 md:w-3/12 md:flex-0"
                                >
                                    <div class="mb-4">
                                        <label
                                            class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80 font-bold"
                                        >
                                            :
                                            {{
                                                inspeksi.performing_physical_power_cleaning ==
                                                "N"
                                                    ? "Tidak"
                                                    : "Ya"
                                            }}
                                        </label>
                                    </div>
                                </div>

                                <!-- Melakukan Pembersihan pada box pembuangan printer -->
                                <div
                                    class="w-full max-w-full px-3 shrink-0 md:w-3/12 md:flex-0"
                                >
                                    <div class="mb-4">
                                        <label
                                            class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                            >Melakukan Pembersihan pada box
                                            pembuangan printer</label
                                        >
                                    </div>
                                </div>
                                <div
                                    class="w-full max-w-full px-3 shrink-0 md:w-3/12 md:flex-0"
                                >
                                    <div class="mb-4">
                                        <label
                                            class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80 font-bold"
                                        >
                                            :
                                            {{
                                                inspeksi.performing_cleaning_on_the_printer_waste_box ==
                                                "N"
                                                    ? "Tidak"
                                                    : "Ya"
                                            }}
                                        </label>
                                    </div>
                                </div>

                                <!-- Melakukan Cleaning Head / Deep Cleaning -->
                                <div
                                    class="w-full max-w-full px-3 shrink-0 md:w-3/12 md:flex-0"
                                >
                                    <div class="mb-4">
                                        <label
                                            class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                            >Melakukan Cleaning Head / Deep
                                            Cleaning</label
                                        >
                                    </div>
                                </div>
                                <div
                                    class="w-full max-w-full px-3 shrink-0 md:w-3/12 md:flex-0"
                                >
                                    <div class="mb-4">
                                        <label
                                            class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80 font-bold"
                                        >
                                            :
                                            {{
                                                inspeksi.performing_cleaning_head ==
                                                "N"
                                                    ? "Tidak"
                                                    : "Ya"
                                            }}
                                        </label>
                                    </div>
                                </div>

                                <!-- Melakukan uji kualitas hasil print dengan print Test Page -->
                                <div
                                    class="w-full max-w-full px-3 shrink-0 md:w-3/12 md:flex-0"
                                >
                                    <div class="mb-4">
                                        <label
                                            class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                            >Melakukan uji kualitas hasil print
                                            dengan print Test Page</label
                                        >
                                    </div>
                                </div>
                                <div
                                    class="w-full max-w-full px-3 shrink-0 md:w-3/12 md:flex-0"
                                >
                                    <div class="mb-4">
                                        <label
                                            class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80 font-bold"
                                        >
                                            :
                                            {{
                                                inspeksi.performing_print_quality_test ==
                                                "N"
                                                    ? "Tidak"
                                                    : "Ya"
                                            }}
                                        </label>
                                    </div>
                                </div>

                                <!-- Memberikan / mengganti Label -->
                                <div
                                    class="w-full max-w-full px-3 shrink-0 md:w-3/12 md:flex-0"
                                >
                                    <div class="mb-4">
                                        <label
                                            class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                            >Memberikan / mengganti Label</label
                                        >
                                    </div>
                                </div>
                                <div
                                    class="w-full max-w-full px-3 shrink-0 md:w-3/12 md:flex-0"
                                >
                                    <div class="mb-4">
                                        <label
                                            class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80 font-bold"
                                        >
                                            :
                                            {{
                                                inspeksi.do_replacing_cable ==
                                                "N"
                                                    ? "Tidak"
                                                    : "Ya"
                                            }}
                                        </label>
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
                                                inspeksi.condition == ""
                                                    ? "-"
                                                    : inspeksi.condition
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
                                            for="nozle_image"
                                            class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                            >Lampiran Nozle Check</label
                                        >
                                    </div>
                                </div>
                                <div
                                    class="w-full max-w-full px-3 shrink-0 md:w-6/12 md:flex-0"
                                >
                                    <div
                                        v-if="inspeksi.nozle_image != null"
                                        class="mb-4"
                                    >
                                        <img
                                            :src="inspeksi.nozle_image"
                                            alt="Foto Nozle Check"
                                            class="w-50 h-30 shadow-2xl rounded-xl"
                                        />
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
                                            Data Aset Printer
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
                                <!-- ========================= -->
                                <!-- PRINTER INVENTORY DETAILS -->
                                <!-- ========================= -->
                                <div class="grid grid-cols-2">
                                    <div>
                                        <p class="text-base">Printer Code</p>
                                    </div>
                                    <div>
                                        <p>
                                            :
                                            {{ inspeksi.printer.printer_code }}
                                        </p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2">
                                    <div>
                                        <p class="text-base">Asset HO Number</p>
                                    </div>
                                    <div>
                                        <p>
                                            :
                                            {{
                                                inspeksi.printer.asset_ho_number
                                            }}
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
                                            {{ inspeksi.printer.serial_number }}
                                        </p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2">
                                    <div>
                                        <p class="text-base">IP Address</p>
                                    </div>
                                    <div>
                                        <p>
                                            :
                                            {{ inspeksi.printer.ip_address }}
                                        </p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2">
                                    <div>
                                        <p class="text-base">MAC Address</p>
                                    </div>
                                    <div>
                                        <p>
                                            :
                                            {{ inspeksi.printer.mac_address }}
                                        </p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2">
                                    <div>
                                        <p class="text-base">Printer Brand</p>
                                    </div>
                                    <div>
                                        <p>
                                            :
                                            {{ inspeksi.printer.printer_brand }}
                                        </p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2">
                                    <div>
                                        <p class="text-base">Printer Type</p>
                                    </div>
                                    <div>
                                        <p>
                                            :
                                            {{ inspeksi.printer.printer_type }}
                                        </p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2">
                                    <div>
                                        <p class="text-base">Division</p>
                                    </div>
                                    <div>
                                        <p>: {{ inspeksi.printer.division }}</p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2">
                                    <div>
                                        <p class="text-base">Department</p>
                                    </div>
                                    <div>
                                        <p>
                                            :
                                            {{ inspeksi.printer.department }}
                                        </p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2">
                                    <div>
                                        <p class="text-base">Location</p>
                                    </div>
                                    <div>
                                        <p>: {{ inspeksi.printer.location }}</p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2">
                                    <div>
                                        <p class="text-base">
                                            Date of Inventory
                                        </p>
                                    </div>
                                    <div>
                                        <p
                                            v-if="
                                                inspeksi.printer
                                                    .date_of_inventory
                                            "
                                        >
                                            :
                                            {{
                                                formattedDate(
                                                    inspeksi.printer
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
                                        <p class="text-base">Status</p>
                                    </div>
                                    <div>
                                        <p>: {{ inspeksi.printer.status }}</p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2">
                                    <div>
                                        <p class="text-base">Condition</p>
                                    </div>
                                    <div>
                                        <p>: {{ inspeksi.condition }}</p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2">
                                    <div>
                                        <p class="text-base">Note</p>
                                    </div>
                                    <div>
                                        <p>: {{ inspeksi.printer.note }}</p>
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
                                                    inspeksi.printer.updated_at
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
