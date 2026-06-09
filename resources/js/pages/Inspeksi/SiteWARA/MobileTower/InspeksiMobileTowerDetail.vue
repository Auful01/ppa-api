<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import NavLinkCustom from "@/Components/NavLinkCustom.vue";
import { Head, router } from "@inertiajs/vue3";
import Swal from "sweetalert2";
import { onMounted, ref, computed } from "vue";

const pages = ref("Pages");
const subMenu = ref("Inspeksi Mobile Tower Pages");
const mainMenu = ref("Form Detail Inspeksi Mobile Tower");

const props = defineProps(["inspeksi", "dataKategori", "subDataKategori"]);

const inspeksiId = props.inspeksi.id;

const findings = computed(() => JSON.parse(props.inspeksi.findings || "[]"));
const findingsImage = computed(() =>
    JSON.parse(props.inspeksi.findings_image || "[]")
);
const findingsStatus = computed(() =>
    JSON.parse(props.inspeksi.findings_status || "[]")
);
const findingsAction = computed(() =>
    JSON.parse(props.inspeksi.findings_action || "[]")
);
const actionImage = computed(() =>
    JSON.parse(props.inspeksi.action_image || "[]")
);

// Fungsi untuk format tanggal
const formattedDate = (date) => {
    if (!date) return "-";
    return new Date(date).toLocaleDateString("id-ID", {
        day: "2-digit",
        month: "long",
        year: "numeric",
    });
};

const mount = onMounted(() => {
    // Inisialisasi DataTable tanpa AJAX
    $("#tableData").DataTable();
    $("#tableData2").DataTable();
    $("#tableData3").DataTable();
});

const exportHasilInspeksi = () => {
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
        route("mt.singleExport"),
        { inspeksiId: inspeksiId },
        {
            onSuccess: ({ props }) => {
                Swal.close();
                window.open(
                    route("export.inspectionMTSingle", {
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
                        @click="exportHasilInspeksi"
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
                                    Form Detail Inspeksi Mobile Tower
                                </h4>
                                <NavLinkCustom
                                    class="text-red-700"
                                    :href="route('inspeksiMobileTowerAdw.page')"
                                >
                                    Move to home page
                                </NavLinkCustom>
                            </div>
                            <div class="flex flex-wrap -mx-3 p-12">
                                <div class="p-6">
                                    <!-- Loop kategori -->
                                    <div
                                        v-for="(
                                            kategori, index
                                        ) in props.dataKategori"
                                        :key="index"
                                        class="mb-10 border-b border-gray-300 pb-4"
                                    >
                                        <div class="text-center mb-4">
                                            <p class="font-semibold text-lg">
                                                {{ kategori.nama_judul }}
                                            </p>
                                            <hr
                                                class="h-px mx-auto my-4 opacity-25 bg-gradient-to-r from-transparent via-black/40 to-transparent"
                                            />
                                        </div>

                                        <!-- Loop subkategori -->
                                        <div class="flex flex-wrap -mx-3">
                                            <template
                                                v-for="sub in subDataKategori.filter(
                                                    (s) =>
                                                        s.parent ===
                                                        kategori.id_kat_inspeksi
                                                )"
                                            >
                                                <div
                                                    class="w-1/2 md:w-3/12 px-3 mb-4"
                                                >
                                                    <label
                                                        class="text-sm text-slate-700 dark:text-white/80"
                                                    >
                                                        {{ sub.nama_judul }}
                                                    </label>
                                                </div>

                                                <div
                                                    class="w-1/2 md:w-3/12 px-3 mb-4 font-bold text-slate-800 dark:text-white"
                                                >
                                                    :
                                                    {{
                                                        JSON.parse(
                                                            props.inspeksi
                                                                .checklist_results_list
                                                        )[
                                                            `${index + 1}_${
                                                                sub.urutan
                                                            }`
                                                        ] == "Y"
                                                            ? "Aman"
                                                            : JSON.parse(
                                                                  props.inspeksi
                                                                      .checklist_results_list
                                                              )[
                                                                  `${
                                                                      index + 1
                                                                  }_${
                                                                      sub.urutan
                                                                  }`
                                                              ] == "N"
                                                            ? "Tidak aman"
                                                            : JSON.parse(
                                                                  props.inspeksi
                                                                      .checklist_results_list
                                                              )[
                                                                  `${
                                                                      index + 1
                                                                  }_${
                                                                      sub.urutan
                                                                  }`
                                                              ]
                                                    }}
                                                </div>
                                            </template>
                                        </div>
                                    </div>

                                    <!-- ========================= -->
                                    <!-- TEMUAN SECTION (looping) -->
                                    <!-- ========================= -->
                                    <div
                                        class="border-t border-gray-400 mt-6 pt-6"
                                    >
                                        <div class="text-center mb-4">
                                            <p class="font-semibold text-lg">
                                                TEMUAN
                                            </p>
                                            <hr
                                                class="h-px mx-auto my-4 opacity-25 bg-gradient-to-r from-transparent via-black/40 to-transparent"
                                            />
                                        </div>

                                        <div
                                            v-for="(item, index) in findings"
                                            :key="index"
                                            class="flex flex-wrap -mx-3 mb-8 pb-4 border-b border-gray-300"
                                        >
                                            <!-- Deskripsi Temuan -->
                                            <div
                                                class="w-full md:w-4/12 px-3 mb-2 text-sm text-slate-700 dark:text-white/80"
                                            >
                                                Deskripsi Temuan {{ index + 1 }}
                                            </div>
                                            <div
                                                class="w-full md:w-6/12 px-3 mb-2 font-bold"
                                            >
                                                : {{ item }}
                                            </div>

                                            <!-- Foto Temuan -->
                                            <div
                                                class="w-full md:w-4/12 px-3 mb-2 text-sm text-slate-700 dark:text-white/80"
                                            >
                                                Lampiran Foto Temuan
                                                {{ index + 1 }}
                                            </div>
                                            <div
                                                class="w-full md:w-6/12 px-3 mb-2"
                                            >
                                                <img
                                                    v-if="findingsImage[index]"
                                                    :src="findingsImage[index]"
                                                    alt="Foto Temuan"
                                                    class="w-40 h-28 object-cover rounded-lg shadow"
                                                />
                                            </div>

                                            <!-- Status Temuan -->
                                            <div
                                                class="w-full md:w-4/12 px-3 mb-2 text-sm text-slate-700 dark:text-white/80"
                                            >
                                                Status Temuan {{ index + 1 }}
                                            </div>
                                            <div
                                                class="w-full md:w-6/12 px-3 mb-2 font-bold"
                                            >
                                                :
                                                {{
                                                    findingsStatus[index] ?? "-"
                                                }}
                                            </div>

                                            <!-- Tindak Lanjut -->
                                            <div
                                                class="w-full md:w-4/12 px-3 mb-2 text-sm text-slate-700 dark:text-white/80"
                                            >
                                                Tindak Lanjut {{ index + 1 }}
                                            </div>
                                            <div
                                                class="w-full md:w-6/12 px-3 mb-2 font-bold"
                                            >
                                                :
                                                {{
                                                    findingsAction[index] ?? "-"
                                                }}
                                            </div>

                                            <!-- Foto Tindakan -->
                                            <div
                                                class="w-full md:w-4/12 px-3 mb-2 text-sm text-slate-700 dark:text-white/80"
                                            >
                                                Lampiran Foto Tindakan
                                                {{ index + 1 }}
                                            </div>
                                            <div
                                                class="w-full md:w-6/12 px-3 mb-2"
                                            >
                                                <img
                                                    v-if="actionImage[index]"
                                                    :src="actionImage[index]"
                                                    alt="Foto Tindakan"
                                                    class="w-40 h-28 object-cover rounded-lg shadow"
                                                />
                                            </div>

                                            <!-- Separator line -->
                                            <div class="w-full mt-4">
                                                <hr
                                                    class="border-gray-300 opacity-50"
                                                />
                                            </div>
                                        </div>
                                    </div>

                                    <hr
                                        class="h-px mx-auto my-4 opacity-25 bg-gradient-to-r from-transparent via-black/40 to-transparent"
                                    />

                                    <!-- Other info -->
                                    <div class="mt-8">
                                        <div class="flex flex-wrap -mx-3">
                                            <div
                                                class="w-full md:w-4/12 px-3 mb-2 text-sm"
                                            >
                                                Due Date
                                            </div>
                                            <div
                                                class="w-full md:w-6/12 px-3 mb-2 font-bold"
                                            >
                                                : {{ props.inspeksi.due_date }}
                                            </div>

                                            <div
                                                class="w-full md:w-4/12 px-3 mb-2 text-sm"
                                            >
                                                Remark
                                            </div>
                                            <div
                                                class="w-full md:w-6/12 px-3 mb-2 font-bold"
                                            >
                                                :
                                                {{
                                                    props.inspeksi.remarks ||
                                                    "-"
                                                }}
                                            </div>

                                            <div
                                                class="w-full md:w-4/12 px-3 mb-2 text-sm"
                                            >
                                                PIC
                                            </div>
                                            <div
                                                class="w-full md:w-6/12 px-3 mb-2 font-bold"
                                            >
                                                :
                                                {{
                                                    props.inspeksi.inspector ||
                                                    "-"
                                                }}
                                            </div>

                                            <div
                                                class="w-full md:w-4/12 px-3 mb-2 text-sm"
                                            >
                                                Foto Inspeksi
                                            </div>
                                            <div
                                                class="w-full md:w-6/12 px-3 mb-2"
                                            >
                                                <img
                                                    v-if="
                                                        props.inspeksi
                                                            .inspection_image
                                                    "
                                                    :src="
                                                        props.inspeksi
                                                            .inspection_image
                                                    "
                                                    alt="Foto Inspeksi"
                                                    class="w-50 h-30 shadow-2xl rounded-xl"
                                                />
                                            </div>
                                        </div>
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
                                            Data Aset Mobile Tower
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
                            <div class="flex-auto p-4 pt-6 space-y-2">
                                <div class="grid grid-cols-2">
                                    <p class="text-base">Site</p>
                                    <p>: {{ inspeksi.mt.site }}</p>
                                </div>

                                <div class="grid grid-cols-2">
                                    <p class="text-base">Inventory Number</p>
                                    <p>
                                        :
                                        {{ inspeksi.mt.inventory_number }}
                                    </p>
                                </div>

                                <div class="grid grid-cols-2">
                                    <p class="text-base">MT Code</p>
                                    <p>: {{ inspeksi.mt.mt_code }}</p>
                                </div>

                                <div class="grid grid-cols-2">
                                    <p class="text-base">Type MT</p>
                                    <p>: {{ inspeksi.mt.type_mt }}</p>
                                </div>

                                <div class="grid grid-cols-2">
                                    <p class="text-base">Location</p>
                                    <p>: {{ inspeksi.mt.location }}</p>
                                </div>

                                <div class="grid grid-cols-2">
                                    <p class="text-base">Detail Location</p>
                                    <p>
                                        :
                                        {{ inspeksi.mt.detail_location }}
                                    </p>
                                </div>

                                <div class="grid grid-cols-2">
                                    <p class="text-base">GPS</p>
                                    <p>: {{ inspeksi.mt.gps }}</p>
                                </div>

                                <div class="grid grid-cols-2">
                                    <p class="text-base">LED Lamp</p>
                                    <p>: {{ inspeksi.mt.led_lamp }}</p>
                                </div>

                                <div class="grid grid-cols-2">
                                    <p class="text-base">Padlock Code</p>
                                    <p>: {{ inspeksi.mt.padlock_code }}</p>
                                </div>

                                <div class="grid grid-cols-2">
                                    <p class="text-base">Status</p>
                                    <p>: {{ inspeksi.mt.status }}</p>
                                </div>

                                <div class="grid grid-cols-2">
                                    <p class="text-base">Condition</p>
                                    <p>: {{ inspeksi.mt.condition }}</p>
                                </div>

                                <div class="grid grid-cols-2">
                                    <p class="text-base">Note</p>
                                    <p>: {{ inspeksi.mt.note }}</p>
                                </div>

                                <div class="grid grid-cols-2">
                                    <p class="text-base">Inspection Remark</p>
                                    <p>
                                        :
                                        {{ inspeksi.mt.inspection_remark }}
                                    </p>
                                </div>

                                <div class="grid grid-cols-2">
                                    <p class="text-base">Last Edit At</p>
                                    <p>
                                        :
                                        {{
                                            formattedDate(
                                                inspeksi.mt.updated_at
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
    </AuthenticatedLayout>
</template>
