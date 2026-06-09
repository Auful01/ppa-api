<style src="vue-multiselect/dist/vue-multiselect.css"></style>
<script setup>
import AuthenticatedLayoutForm from "@/Layouts/AuthenticatedLayoutForm.vue";
import { Link } from "@inertiajs/vue3";
import { Head, useForm, router } from "@inertiajs/vue3";
import VueMultiselect from "vue-multiselect";
import VueDatePicker from "@vuepic/vue-datepicker";
import "@vuepic/vue-datepicker/dist/main.css";
import Swal from "sweetalert2";
import { ref, computed, watch } from "vue";

const props = defineProps({
    pengguna: {
        type: Array,
    },
    dataInspeksi: {
        type: Array,
    },
    mobileTower: {
        type: Array,
    },
    dataKategori: {
        type: Array,
    },
    subDataKategori: {
        type: Array,
    },
    totalItems: { type: Number, required: true },
});

const optionsPic = props.pengguna;

const activeTab = ref("deskripsi");
const selectedPicValues = ref(null);
const selectedCrewValues = ref([]);
const selectedDateDueDate = ref(null);

const temuanList = ref([
    {
        temuan: "",
        temuan_image: null,
        tindak_lanjut: "",
        tindak_image: null,
        status: "",
    },
]);

function onFileChange(e, idx, field) {
    const file = e.target.files[0];
    if (file && file.type.startsWith("image/")) {
        // Revoke previous preview if exists
        if (temuanList.value[idx][field]?.preview) {
            URL.revokeObjectURL(temuanList.value[idx][field].preview);
        }

        // Store file + preview
        temuanList.value[idx][field] = {
            file,
            preview: URL.createObjectURL(file),
        };
    }
}

function removeImage(idx, field) {
    if (temuanList.value[idx][field]) {
        URL.revokeObjectURL(temuanList.value[idx][field].preview);
        temuanList.value[idx][field] = null;
    }
}

// Handle upload Lampiran inspeksi

const lampiranFotoInspeksi = ref(null);

// Handle upload (single file only)
function onLampiranChange(e) {
    const file = e.target.files[0];
    if (file && file.type.startsWith("image/")) {
        // cleanup old preview if exists
        if (lampiranFotoInspeksi.value?.preview) {
            URL.revokeObjectURL(lampiranFotoInspeksi.value.preview);
        }

        lampiranFotoInspeksi.value = {
            file,
            preview: URL.createObjectURL(file),
        };
    }
    e.target.value = "";
}

// Remove image
function removeLampiran() {
    if (lampiranFotoInspeksi.value?.preview) {
        URL.revokeObjectURL(lampiranFotoInspeksi.value.preview);
    }
    lampiranFotoInspeksi.value = null;
}

// add temuan with validation
function addTemuan() {
    const lastItem = temuanList.value[temuanList.value.length - 1];

    // Validate temuan
    if (!lastItem.temuan) {
        alert("Field 'Temuan' harus diisi");
        return;
    }
    if (!lastItem.temuan_image) {
        alert("Gambar Temuan harus diupload");
        return;
    }
    if (!lastItem.status) {
        alert("Status harus dipilih");
        return;
    }

    // Validate tindak lanjut
    if (lastItem.tindak_lanjut && !lastItem.tindak_image) {
        alert("Gambar Tidak Lanjut harus diupload");
        return;
    }

    // Add new item if under 5
    if (temuanList.value.length < 5) {
        temuanList.value.push({
            temuan: "",
            temuan_image: null,
            tindak_lanjut: "",
            tindak_image: null,
            status: "",
        });
    }
}

const form = useForm({
    id: props.dataInspeksi.id,
    checklist_results_list: {},
    list_results_remark: {},
    inspection_status: "",
    condition: "",
    kelayakan: "",
    inventory_status: "",
    remark: "",
    pic: "",
    lokasi: "",
    detail_lokasi: "",
    crew: [],
    due_date: "",
    temuan: [],
    lampiran: null,
});

const inventoryOptions = [
    { value: "READY_USED", label: "Ready Used" },
    { value: "READY_STANDBY", label: "Ready Standby" },
    { value: "SCRAP", label: "Scrap" },
    { value: "BREAKDOWN", label: "Breakdown" },
    { value: "MUTASI", label: "Mutasi" },
];

const conditionInventoryMap = {
    BAGUS: ["READY_USED", "READY_STANDBY"],
    RUSAK: ["SCRAP", "BREAKDOWN"],
    SCRAP: ["SCRAP"],
    MUTASI: ["MUTASI"],
};

const filteredInventoryOptions = computed(() => {
    return inventoryOptions.map((opt) => ({
        ...opt,
        disabled: !conditionInventoryMap[form.condition]?.includes(opt.value),
    }));
});

const customFormat = (date) => {
    const d = new Date(date);
    const year = d.getFullYear();
    const month = String(d.getMonth() + 1).padStart(2, "0");
    const day = String(d.getDate()).padStart(2, "0");
    const hours = String(d.getHours()).padStart(2, "0");
    const minutes = String(d.getMinutes()).padStart(2, "0");
    const seconds = String(d.getSeconds()).padStart(2, "0");

    return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
};

const selectedList = ref([]);
const remarksList = ref([]);

const save = () => {
    if (!lampiranFotoInspeksi.value || !lampiranFotoInspeksi.value.file) {
        alert("Lampiran Foto Inspeksi harus diupload.");
        return;
    }

    const selectPic = selectedPicValues.value?.name || "";
    const selectCrew = selectedCrewValues.value.map((c) => c.name);
    const dueDate = selectedDateDueDate.value;

    form.id = props.dataInspeksi.id;
    form.checklist_results_list = { ...selectedList.value };
    form.list_results_remark = { ...remarksList.value };
    form.inspection_status = "Y";
    form.pic = selectPic;
    form.crew = selectCrew;
    form.due_date = dueDate;

    // Temuan with images
    form.temuan = temuanList.value.map((t) => ({
        temuan: t.temuan,
        tindak_lanjut: t.tindak_lanjut,
        status: t.status,
        temuan_image: t.temuan_image?.file || null,
        tindak_image: t.tindak_image?.file || null,
    }));

    // Lampiran Foto Inspeksi
    form.lampiran = lampiranFotoInspeksi.value?.file || null;

    form.post(route("inspeksiMobileTowerIpt.store"), {
        forceFormData: true, // <-- important for file uploads
        onSuccess: () => {
            Swal.fire({
                title: "Success!",
                text: "Data has been successfully created!",
                icon: "success",
                confirmButtonText: "OK",
                confirmButtonColor: "#3085d6",
            });
        },
        onError: () => {
            Swal.fire({
                title: "Error!",
                text: "Data not created!",
                icon: "error",
                confirmButtonText: "OK",
                confirmButtonColor: "#3085d6",
            });
        },
    });
};

function goBack() {
    Swal.fire({
        title: "Yakin ingin kembali?",
        text: "Perubahan yang belum disimpan mungkin akan hilang.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#16a34a",
        cancelButtonColor: "#d33",
        confirmButtonText: "Ya, kembali!",
        cancelButtonText: "Batal",
    }).then((result) => {
        if (result.isConfirmed) {
            router.visit(route("inspeksiMobileTowerIpt.page"));
        }
    });
}

const getSubKategori = (idKategori) => {
    return props.subDataKategori.filter((sub) => sub.parent === idKategori);
};

const tabOrder = ["deskripsi", "temuan", "foto"];

function validateDeskripsi(totalItems) {
    // return { ok: true };

    const keys = Object.keys(selectedList.value);

    // Check radios/text count matches total items
    if (keys.length < totalItems) {
        return {
            ok: false,
            message: "Semua item harus dipilih Aman atau Tidak Aman.",
        };
    }

    // Check each selected item
    for (const key of keys) {
        const val = selectedList.value[key];
        if (!val) {
            return {
                ok: false,
                message: "Semua item harus dipilih Aman atau Tidak Aman.",
            };
        }
        if (
            val === "N" &&
            (!remarksList.value[key] || !remarksList.value[key].trim())
        ) {
            const friendlyKey = key.replace("_", ".");
            return {
                ok: false,
                message: `Keterangan wajib diisi untuk item ${friendlyKey}.`,
            };
        }
    }

    // ✅ Validate Condition
    if (!form.condition || form.condition === "") {
        return {
            ok: false,
            message: "Condition tidak boleh kosong.",
        };
    }

    // ✅ Validate Inventory Status
    if (!form.inventory_status || form.inventory_status === "") {
        return {
            ok: false,
            message: "Inventory Status tidak boleh kosong.",
        };
    }

    if (!form.kelayakan || form.kelayakan === "") {
        return {
            ok: false,
            message: "Kelayakan tidak boleh kosong.",
        };
    }

    return { ok: true };
}

const excludedJuduls = ["1_5"];

const hasTidakAman = computed(() => {
    return Object.entries(selectedList.value).some(([key, value]) => {
        if (excludedJuduls.includes(key)) return false;

        if (value === "N") return true;

        if (key === "2_1") {
            const num = Number(value);
            if (!isNaN(num)) {
                return num < 12; // true = Tidak Aman
            }
        }

        if (key === "3_1") {
            const num = Number(value);
            if (!isNaN(num)) {
                return num < 24; // true = Tidak Aman
            }
        }

        return false; // default Aman
    });
});

function checkTemuan() {
    const lastItem = temuanList.value[temuanList.value.length - 1];
    if (hasTidakAman.value) {
        console.log(hasTidakAman.value);
        if (!lastItem.temuan || !lastItem.temuan.trim()) {
            return {
                ok: false,
                message:
                    "Field Temuan wajib diisi jika ada kondisi Tidak Aman.",
            };
        }
    }

    // Validate other required fields
    if (!form.remark || form.remark.trim() === "") {
        return { ok: false, message: "Field 'Remark' harus diisi." };
    }

    // PIC
    if (
        !selectedPicValues.value ||
        Object.keys(selectedPicValues.value).length === 0
    ) {
        return { ok: false, message: "PIC harus dipilih." };
    }

    // Crew
    if (!selectedCrewValues.value || selectedCrewValues.value.length === 0) {
        return { ok: false, message: "Minimal satu Crew harus dipilih." };
    }

    if (!form.lokasi || form.lokasi.trim() === "") {
        return { ok: false, message: "Lokasi harus diisi." };
    }

    if (!form.detail_lokasi || form.detail_lokasi.trim() === "") {
        return { ok: false, message: "Detail Lokasi harus diisi." };
    }

    const allEmpty =
        !lastItem.temuan &&
        !lastItem.temuan_image &&
        !lastItem.tindak_lanjut &&
        !lastItem.tindak_image &&
        !lastItem.status;

    if (allEmpty) {
        return { ok: true }; // no validation needed
    }

    // Validate temuan
    if (!lastItem.temuan) {
        return {
            ok: false,
            message: "Field 'Temuan' harus diisi.",
        };
    }
    if (!lastItem.temuan_image) {
        return {
            ok: false,
            message: "Gambar Temuan harus diupload.",
        };
    }
    if (!lastItem.status) {
        return {
            ok: false,
            message: "Status harus dipilih.",
        };
    }

    // Validate tindak lanjut
    if (lastItem.tindak_lanjut && !lastItem.tindak_image) {
        return {
            ok: false,
            message: "Gambar Tidak Lanjut harus diupload.",
        };
    }

    // Due Date
    if (!selectedDateDueDate.value) {
        return { ok: false, message: "Due Date harus diisi." };
    }

    return { ok: true };
}

function goNextTab() {
    if (activeTab.value === "deskripsi") {
        const result = validateDeskripsi(props.totalItems);
        if (!result.ok) {
            alert(result.message);
            return;
        }
    }

    if (activeTab.value === "temuan") {
        const valid = checkTemuan();
        if (!valid.ok) {
            alert(valid.message);
            return;
        }
    }

    const currentIndex = tabOrder.indexOf(activeTab.value);
    if (currentIndex < tabOrder.length - 1) {
        activeTab.value = tabOrder[currentIndex + 1];
    }
}

function goPrevTab() {
    const currentIndex = tabOrder.indexOf(activeTab.value);
    if (currentIndex > 0) {
        activeTab.value = tabOrder[currentIndex - 1];
    }
}

watch(selectedPicValues, (newPic, oldPic) => {
    if (oldPic) {
        selectedCrewValues.value = selectedCrewValues.value.filter(
            (crew) => crew.id !== oldPic.id
        );
    }

    if (newPic) {
        const alreadyExists = selectedCrewValues.value.some(
            (crew) => crew.id === newPic.id
        );
        if (!alreadyExists) selectedCrewValues.value.push(newPic);
    }
});
</script>

<template>
    <Head title="Inspeksi Mobile Tower" />

    <AuthenticatedLayoutForm>
        <template #header>
            <nav>
                <!-- breadcrumb -->
                <ol
                    class="flex flex-wrap pt-1 mr-12 bg-transparent rounded-lg sm:mr-16"
                >
                    <li class="text-sm leading-normal">
                        <a class="text-white opacity-50">Pages</a>
                    </li>
                    <Link
                        :href="route('inspeksiMobileTowerIpt.page')"
                        class="text-sm pl-2 capitalize leading-normal text-white before:float-left before:pr-2 before:text-white before:content-['/']"
                        aria-current="page"
                    >
                        Inspeksi MobileTower
                    </Link>
                </ol>
                <h6 class="mb-0 font-bold text-white capitalize">
                    Inspeksi Page
                </h6>
            </nav>
        </template>

        <div class="w-full p-6 mx-auto">
            <div class="flex flex-wrap -mx-3 justify-center">
                <div
                    class="w-full max-w-full px-3 shrink-0 md:w-8/12 md:flex-0"
                >
                    <div
                        class="mb-4 mx-auto w-[375px] relative flex flex-col min-w-0 break-words bg-white border-0 shadow-xl dark:bg-slate-850 dark:shadow-dark-xl rounded-2xl bg-clip-border"
                    >
                        <div
                            class="border-black/12.5 rounded-t-2xl border-b-0 border-solid p-6 pb-0"
                        >
                            <div class="flex items-center justify-between">
                                <p class="mb-0 font-black dark:text-white/80">
                                    ASSET DETAIL
                                </p>

                                <!-- Back/Close button -->
                                <button
                                    @click="goBack"
                                    class="flex items-center justify-center w-8 h-8 rounded-full bg-red-600 border border-red-700 transition hover:bg-red-700"
                                >
                                    <!-- Heroicons X Mark -->
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke-width="3"
                                        stroke="white"
                                        class="w-4 h-4 font-bold"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M6 18L18 6M6 6l12 12"
                                        />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div class="flex-auto p-6">
                            <hr
                                class="h-px mx-0 my-4 bg-transparent border-0 opacity-25 bg-gradient-to-r from-transparent via-black/40 to-transparent dark:bg-gradient-to-r dark:from-transparent dark:via-white dark:to-transparent"
                            />

                            <div class="flex flex-wrap -mx-3">
                                <div class="w-full max-w-full px-3">
                                    <!-- Always 1 column -->
                                    <div class="px-4 pb-0 grid grid-cols-1">
                                        <div class="grid grid-cols-2">
                                            <div>
                                                <p class="text-base">
                                                    Inventory Number
                                                </p>
                                            </div>
                                            <div>
                                                <p>
                                                    :
                                                    {{
                                                        mobileTower.inventory_number
                                                    }}
                                                </p>
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-2">
                                            <div>
                                                <p class="text-base">MT CODE</p>
                                            </div>
                                            <div>
                                                <p>
                                                    : {{ mobileTower.mt_code }}
                                                </p>
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-2">
                                            <div>
                                                <p class="text-base">Tipe MT</p>
                                            </div>
                                            <div>
                                                <p>
                                                    : {{ mobileTower.type_mt }}
                                                </p>
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-2">
                                            <div>
                                                <p class="text-base">
                                                    Location
                                                </p>
                                            </div>
                                            <div>
                                                <p>
                                                    : {{ mobileTower.location }}
                                                </p>
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-2">
                                            <div>
                                                <p class="text-base">
                                                    Detail Location
                                                </p>
                                            </div>
                                            <div>
                                                <p>
                                                    :
                                                    {{
                                                        mobileTower.detail_location
                                                    }}
                                                </p>
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-2">
                                            <div>
                                                <p class="text-base">Status</p>
                                            </div>
                                            <div>
                                                <p>
                                                    : {{ mobileTower.status }}
                                                </p>
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-2">
                                            <div>
                                                <p class="text-base">Note</p>
                                            </div>
                                            <div>
                                                <p>: {{ mobileTower.note }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        class="mx-auto w-[375px] bg-white shadow-xl dark:bg-slate-850 dark:shadow-dark-xl rounded-2xl"
                    >
                        <div
                            class="border-black/12.5 rounded-t-2xl border-b-0 border-solid p-6 pb-0"
                        >
                            <div class="flex items-center">
                                <p class="mb-0 font-black dark:text-white/80">
                                    Form Inspeksi Mobile Tower
                                </p>
                            </div>
                            <label
                                class="inline-block text-xs text-slate-700 dark:text-white/80"
                            >
                                Mohon mengisi sesuai dengan kondisi di lapangan
                            </label>
                        </div>

                        <div class="flex-auto p-6">
                            <!-- Tabs -->
                            <!-- Tabs Container -->
                            <div
                                class="flex space-x-2 bg-slate-100 p-2 rounded-lg"
                            >
                                <!-- Deskripsi Pemeriksaan -->
                                <button
                                    type="button"
                                    @click="goNextTab"
                                    :class="
                                        activeTab === 'deskripsi'
                                            ? 'bg-white px-3 py-2 rounded-lg font-semibold text-black shadow text-xs text-center whitespace-normal'
                                            : 'px-3 py-2 rounded-lg text-slate-500 hover:text-black font-semibold text-xs text-center whitespace-normal'
                                    "
                                >
                                    Deskripsi Pemeriksaan
                                </button>

                                <!-- Hasil Temuan -->
                                <button
                                    type="button"
                                    @click="goNextTab"
                                    :class="
                                        activeTab === 'temuan'
                                            ? 'bg-white px-3 py-2 rounded-lg font-semibold text-black shadow text-xs text-center whitespace-normal'
                                            : 'px-3 py-2 rounded-lg text-slate-500 hover:text-black font-semibold text-xs text-center whitespace-normal'
                                    "
                                >
                                    Hasil Temuan
                                </button>

                                <!-- Lampiran Foto -->
                                <button
                                    type="button"
                                    @click="goNextTab"
                                    :class="
                                        activeTab === 'foto'
                                            ? 'bg-white px-3 py-2 rounded-lg font-semibold text-black shadow text-xs text-center whitespace-normal'
                                            : 'px-3 py-2 rounded-lg text-slate-500 hover:text-black font-semibold text-xs text-center whitespace-normal'
                                    "
                                >
                                    Lampiran Foto
                                </button>
                            </div>

                            <!-- Tabs Content -->
                            <div class="mt-4">
                                <form @submit.prevent="save">
                                    <div v-if="activeTab === 'deskripsi'">
                                        <hr
                                            class="h-px mx-0 my-4 bg-transparent border-0 opacity-25 bg-gradient-to-r from-transparent via-black/40 to-transparent dark:bg-gradient-to-r dark:from-transparent dark:via-white dark:to-transparent"
                                        />

                                        <div
                                            class="flex flex-wrap -mx-3 mb-8"
                                            v-for="(
                                                kategori, index
                                            ) in props.dataKategori"
                                            :key="index"
                                        >
                                            <div class="w-full px-3">
                                                <div class="text-center mb-8">
                                                    <p
                                                        class="mb-0 dark:text-white/80 font-semibold"
                                                    >
                                                        {{
                                                            kategori.nama_judul
                                                        }}
                                                    </p>
                                                </div>
                                            </div>
                                            <div
                                                class="w-full px-3"
                                                v-for="(
                                                    sub, i
                                                ) in getSubKategori(
                                                    kategori.id_kat_inspeksi
                                                )"
                                                :key="i"
                                            >
                                                <div class="mb-4">
                                                    <label
                                                        class="inline-block ml-1 text-sm text-slate-700 dark:text-white/80"
                                                    >
                                                        {{ index + 1 }}.{{
                                                            sub.urutan
                                                        }}
                                                        {{ sub.nama_judul }}
                                                    </label>
                                                    <br />
                                                    <!-- CASE 1: Voltase text input -->
                                                    <div
                                                        v-if="
                                                            sub.nama_judul ===
                                                                'Voltase yang masuk ke MPPT sesuai dengan spek solpan' ||
                                                            sub.nama_judul ===
                                                                'Voltase Batrai >12V'
                                                        "
                                                    >
                                                        <input
                                                            required
                                                            type="number"
                                                            step="any"
                                                            min="0"
                                                            :name="`inspeksiMT_${
                                                                index + 1
                                                            }_${sub.urutan}`"
                                                            v-model.number="
                                                                selectedList[
                                                                    index +
                                                                        1 +
                                                                        '_' +
                                                                        sub.urutan
                                                                ]
                                                            "
                                                            class="focus:shadow-primary-outline dark:bg-slate-850 dark:text-white text-sm leading-5.6 ease block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-blue-500 focus:outline-none"
                                                            placeholder="masukkan aktual voltase"
                                                        />
                                                    </div>

                                                    <!-- CASE 2: Terdapat tali untuk moving MT → Ada / Tidak Ada -->
                                                    <div
                                                        v-else-if="
                                                            sub.nama_judul ===
                                                            'Terdapat tali untuk moving MT'
                                                        "
                                                        class="inline-flex items-center space-x-4 mb-2 pl-8"
                                                    >
                                                        <label
                                                            class="inline-flex items-center space-x-2"
                                                        >
                                                            <input
                                                                required
                                                                type="radio"
                                                                :name="`inspeksiMT_${
                                                                    index + 1
                                                                }_${
                                                                    sub.urutan
                                                                }`"
                                                                v-model="
                                                                    selectedList[
                                                                        index +
                                                                            1 +
                                                                            '_' +
                                                                            sub.urutan
                                                                    ]
                                                                "
                                                                value="Y"
                                                                class="form-radio text-blue-600 border-gray-300 focus:ring-blue-500"
                                                            />
                                                            <span
                                                                class="text-gray-700"
                                                                >Ada</span
                                                            >
                                                        </label>

                                                        <label
                                                            class="inline-flex items-center space-x-2"
                                                        >
                                                            <input
                                                                required
                                                                type="radio"
                                                                :name="`inspeksiMT_${
                                                                    index + 1
                                                                }_${
                                                                    sub.urutan
                                                                }`"
                                                                v-model="
                                                                    selectedList[
                                                                        index +
                                                                            1 +
                                                                            '_' +
                                                                            sub.urutan
                                                                    ]
                                                                "
                                                                value="N"
                                                                class="form-radio text-blue-600 border-gray-300 focus:ring-blue-500"
                                                            />
                                                            <span
                                                                class="text-gray-700"
                                                                >Tidak Ada</span
                                                            >
                                                        </label>
                                                    </div>
                                                    <!-- CASE AMAN TIDAK AMAN -->
                                                    <div
                                                        class="inline-flex items-center space-x-4 mb-2 pl-8"
                                                        v-else
                                                    >
                                                        <label
                                                            class="inline-flex items-center space-x-2"
                                                        >
                                                            <input
                                                                required
                                                                type="radio"
                                                                :name="`inspeksiMT_${
                                                                    index + 1
                                                                }_${
                                                                    sub.urutan
                                                                }`"
                                                                v-model="
                                                                    selectedList[
                                                                        index +
                                                                            1 +
                                                                            '_' +
                                                                            sub.urutan
                                                                    ]
                                                                "
                                                                value="Y"
                                                                class="form-radio text-blue-600 border-gray-300 focus:ring-blue-500"
                                                            />
                                                            <span
                                                                class="text-gray-700"
                                                                >Aman</span
                                                            >
                                                        </label>

                                                        <label
                                                            class="inline-flex items-center space-x-2"
                                                        >
                                                            <input
                                                                required
                                                                type="radio"
                                                                :name="`inspeksiMT_${
                                                                    index + 1
                                                                }_${
                                                                    sub.urutan
                                                                }`"
                                                                v-model="
                                                                    selectedList[
                                                                        index +
                                                                            1 +
                                                                            '_' +
                                                                            sub.urutan
                                                                    ]
                                                                "
                                                                value="N"
                                                                class="form-radio text-blue-600 border-gray-300 focus:ring-blue-500"
                                                            />
                                                            <span
                                                                class="text-gray-700"
                                                                >Tidak
                                                                aman</span
                                                            >
                                                        </label>
                                                    </div>
                                                    <div
                                                        class="mt-2"
                                                        v-if="
                                                            selectedList[
                                                                index +
                                                                    1 +
                                                                    '_' +
                                                                    sub.urutan
                                                            ] === 'N'
                                                        "
                                                    >
                                                        <input
                                                            type="text"
                                                            placeholder="Tambahkan keterangan..."
                                                            v-model="
                                                                remarksList[
                                                                    index +
                                                                        1 +
                                                                        '_' +
                                                                        sub.urutan
                                                                ]
                                                            "
                                                            :required="
                                                                selectedList[
                                                                    index +
                                                                        1 +
                                                                        '_' +
                                                                        sub.urutan
                                                                ] === 'N'
                                                            "
                                                            class="form-input w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
                                                        />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Condition Dropdown -->
                                        <div class="mb-8">
                                            <label
                                                for="condition"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                            >
                                                Kondisi MT
                                            </label>
                                            <select
                                                id="condition"
                                                v-model="form.condition"
                                                required
                                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                            >
                                                <option value="">
                                                    -- Pilih Kondisi --
                                                </option>
                                                <option value="BAGUS">
                                                    Bagus
                                                </option>
                                                <option value="RUSAK">
                                                    Rusak
                                                </option>
                                                <option value="SCRAP">
                                                    Scrap
                                                </option>
                                                <option value="MUTASI">
                                                    Mutasi
                                                </option>
                                            </select>
                                        </div>

                                        <!-- Inventory Status Dropdown -->
                                        <div class="mb-8">
                                            <label
                                                for="inventory_status"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                            >
                                                Status Inventory
                                            </label>
                                            <select
                                                id="inventory_status"
                                                v-model="form.inventory_status"
                                                required
                                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                            >
                                                <option value="">
                                                    -- Pilih Status --
                                                </option>
                                                <option
                                                    v-for="opt in filteredInventoryOptions"
                                                    :key="opt.value"
                                                    :value="opt.value"
                                                    :disabled="opt.disabled"
                                                    :class="
                                                        opt.disabled
                                                            ? 'text-gray-400 bg-gray-100 cursor-not-allowed'
                                                            : 'text-gray-900 bg-white cursor-pointer'
                                                    "
                                                >
                                                    {{ opt.label }}
                                                </option>
                                            </select>
                                        </div>

                                        <!-- Kelayakan Dropdown -->
                                        <div class="mb-8">
                                            <label
                                                for="kelayakan"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                            >
                                                Kelayakan MT
                                            </label>
                                            <select
                                                id="kelayakan"
                                                v-model="form.kelayakan"
                                                required
                                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                            >
                                                <option value="">
                                                    -- Pilih Kelayakan --
                                                </option>
                                                <option value="LAYAK">
                                                    Layak
                                                </option>
                                                <option value="TIDAK_LAYAK">
                                                    Tidak Layak
                                                </option>
                                            </select>
                                        </div>
                                        <hr
                                            class="h-px mx-0 my-4 bg-transparent border-0 opacity-25 bg-gradient-to-r from-transparent via-black/40 to-transparent dark:bg-gradient-to-r dark:from-transparent dark:via-white dark:to-transparent"
                                        />
                                        <div class="flex justify-between mt-4">
                                            <button
                                                v-if="activeTab !== 'deskripsi'"
                                                @click="goPrevTab"
                                                class="px-3 py-2 rounded-lg bg-slate-200 hover:bg-slate-300 text-sm font-semibold"
                                            >
                                                ← Sebelumnya
                                            </button>

                                            <button
                                                type="button"
                                                v-if="activeTab !== 'foto'"
                                                @click="goNextTab"
                                                class="ml-auto px-3 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold"
                                            >
                                                Selanjutnya →
                                            </button>
                                        </div>
                                    </div>

                                    <div v-if="activeTab === 'temuan'">
                                        <p
                                            class="mb-4 font-black dark:text-white/80 text-center"
                                        >
                                            Temuan Inspeksi
                                        </p>

                                        <!-- Dynamic Temuan Section -->
                                        <fieldset
                                            :disabled="!hasTidakAman"
                                            class="transition-all duration-200"
                                        >
                                            <div
                                                v-for="(
                                                    item, idx
                                                ) in temuanList"
                                                :key="idx"
                                                class="mb-6 border rounded-lg p-4 bg-gray-50"
                                            >
                                                <h3
                                                    class="text-sm font-semibold text-slate-700 mb-3"
                                                >
                                                    Temuan {{ idx + 1 }}
                                                </h3>

                                                <!-- Input Temuan -->
                                                <div class="mb-0">
                                                    <label
                                                        class="block mb-1 text-sm text-slate-700"
                                                    >
                                                        Temuan
                                                        <span
                                                            v-if="hasTidakAman"
                                                            class="text-blue-500"
                                                            >*</span
                                                        >
                                                    </label>

                                                    <textarea
                                                        v-model="item.temuan"
                                                        rows="3"
                                                        :required="hasTidakAman"
                                                        :class="[
                                                            'w-full px-3 py-2 rounded-lg text-sm text-gray-700 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200',
                                                            hasTidakAman
                                                                ? 'border-blue-500 focus:ring-blue-500'
                                                                : 'border-gray-300 focus:border-blue-500',
                                                        ]"
                                                        placeholder="Masukkan deskripsi temuan"
                                                    ></textarea>
                                                </div>

                                                <label
                                                    class="mb-4 inline-block text-xs text-slate-700 dark:text-white/80"
                                                >
                                                    *Temuan inspeksi tidak wajib
                                                    diisi jika tidak ada kondisi
                                                    tidak aman
                                                </label>

                                                <!-- Upload Image Temuan -->
                                                <div class="mb-4">
                                                    <label
                                                        class="block mb-1 text-sm text-slate-700"
                                                    >
                                                        Upload Gambar Temuan
                                                        <span
                                                            v-if="hasTidakAman"
                                                            class="text-red-500"
                                                            >*</span
                                                        >
                                                    </label>

                                                    <div
                                                        :class="[
                                                            'relative border-2 border-dashed rounded-lg p-4 text-center cursor-pointer transition-all duration-200',
                                                            hasTidakAman
                                                                ? 'border-blue-500 hover:border-blue-600'
                                                                : 'border-gray-300 ',
                                                        ]"
                                                    >
                                                        <input
                                                            type="file"
                                                            accept="image/*"
                                                            :required="
                                                                hasTidakAman
                                                            "
                                                            class="absolute inset-0 opacity-0 cursor-pointer"
                                                            @change="
                                                                onFileChange(
                                                                    $event,
                                                                    idx,
                                                                    'temuan_image'
                                                                )
                                                            "
                                                        />

                                                        <div
                                                            v-if="
                                                                temuanList[idx]
                                                                    ?.temuan_image
                                                                    ?.preview
                                                            "
                                                            class="relative"
                                                        >
                                                            <img
                                                                :src="
                                                                    temuanList[
                                                                        idx
                                                                    ]
                                                                        .temuan_image
                                                                        .preview
                                                                "
                                                                class="w-32 h-32 object-cover rounded-lg mx-auto shadow"
                                                            />
                                                            <button
                                                                type="button"
                                                                @click="
                                                                    removeImage(
                                                                        idx,
                                                                        'temuan_image'
                                                                    )
                                                                "
                                                                class="absolute top-1 right-1 bg-red-500 text-white rounded-full p-1 shadow hover:bg-red-600"
                                                            >
                                                                ✕
                                                            </button>
                                                        </div>

                                                        <div
                                                            v-else
                                                            class="text-gray-400"
                                                        >
                                                            <i
                                                                class="fas fa-upload text-gray-400 text-4xl mb-2"
                                                            ></i>
                                                            <p class="text-sm">
                                                                Klik untuk
                                                                upload
                                                            </p>
                                                            <p class="text-xs">
                                                                Hanya gambar
                                                                (PNG, JPG, JPEG)
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Input Tindak Lanjut -->
                                                <div class="mb-4">
                                                    <label
                                                        class="block mb-1 text-sm text-slate-700"
                                                        >Tindak Lanjut</label
                                                    >
                                                    <textarea
                                                        v-model="
                                                            item.tindak_lanjut
                                                        "
                                                        rows="3"
                                                        class="w-full px-3 py-2 border rounded-lg text-sm text-gray-700 focus:ring-blue-500 focus:border-blue-500"
                                                        placeholder="Masukkan tindak lanjut"
                                                    ></textarea>
                                                    <label
                                                        class="mb-4 inline-block text-xs text-slate-700 dark:text-white/80"
                                                    >
                                                        *Tindak lanjut tidak
                                                        wajib diisi jika belum
                                                        ada progres tindakan
                                                    </label>
                                                </div>

                                                <!-- Upload Image Tindak Lanjut -->
                                                <div class="mb-4">
                                                    <label
                                                        class="block mb-1 text-sm text-slate-700"
                                                    >
                                                        Upload Gambar Tindak
                                                        Lanjut
                                                    </label>
                                                    <div
                                                        :class="[
                                                            'relative border-2 border-dashed rounded-lg p-4 text-center cursor-pointer transition-all duration-200',
                                                            hasTidakAman
                                                                ? 'border-gray-300 hover:border-blue-600'
                                                                : 'border-gray-300 ',
                                                        ]"
                                                    >
                                                        <input
                                                            type="file"
                                                            accept="image/*"
                                                            class="absolute inset-0 opacity-0 cursor-pointer"
                                                            @change="
                                                                onFileChange(
                                                                    $event,
                                                                    idx,
                                                                    'tindak_image'
                                                                )
                                                            "
                                                        />

                                                        <!-- Preview -->
                                                        <div
                                                            v-if="
                                                                temuanList[idx]
                                                                    ?.tindak_image
                                                                    ?.preview
                                                            "
                                                            class="relative"
                                                        >
                                                            <img
                                                                :src="
                                                                    temuanList[
                                                                        idx
                                                                    ]
                                                                        .tindak_image
                                                                        .preview
                                                                "
                                                                class="w-32 h-32 object-cover rounded-lg mx-auto shadow"
                                                                alt="Preview Tindak Lanjut"
                                                            />
                                                            <button
                                                                type="button"
                                                                @click="
                                                                    removeImage(
                                                                        idx,
                                                                        'tindak_image'
                                                                    )
                                                                "
                                                                class="absolute top-1 right-1 bg-red-500 text-white rounded-full p-1 shadow hover:bg-red-600"
                                                            >
                                                                ✕
                                                            </button>
                                                        </div>

                                                        <!-- Placeholder -->
                                                        <div
                                                            v-else
                                                            class="text-gray-400"
                                                        >
                                                            <i
                                                                class="fas fa-upload text-gray-400 text-4xl mb-2"
                                                            ></i>

                                                            <p class="text-sm">
                                                                Klik untuk
                                                                upload
                                                            </p>
                                                            <p class="text-xs">
                                                                Hanya gambar
                                                                (PNG, JPG, JPEG)
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Status -->
                                                <div class="mb-4">
                                                    <label
                                                        class="block mb-1 text-sm text-slate-700"
                                                        >Status Temuan</label
                                                    >
                                                    <select
                                                        v-model="item.status"
                                                        :class="[
                                                            'w-full px-3 py-2 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500 transition-all duration-200',
                                                            hasTidakAman
                                                                ? 'border-blue-500 bg-white text-gray-700'
                                                                : 'border-gray-300 bg-gray-100 text-gray-400 cursor-not-allowed',
                                                        ]"
                                                    >
                                                        <option value="">
                                                            -- Pilih Status --
                                                        </option>
                                                        <option value="OPEN">
                                                            Open
                                                        </option>
                                                        <option
                                                            value="PROGRESS"
                                                        >
                                                            Progress
                                                        </option>
                                                        <option value="CLOSED">
                                                            Closed
                                                        </option>
                                                    </select>
                                                </div>
                                            </div>
                                        </fieldset>

                                        <!-- Button Add More -->
                                        <div class="mb-6">
                                            <button
                                                type="button"
                                                @click="addTemuan"
                                                :disabled="
                                                    !hasTidakAman ||
                                                    temuanList.length >= 5
                                                "
                                                class="px-3 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 disabled:bg-gray-400 text-white text-sm font-semibold"
                                            >
                                                + Add More
                                            </button>
                                        </div>

                                        <!-- Due Date -->
                                        <div
                                            class="mb-4 transition-all duration-200"
                                        >
                                            <label
                                                for="date-of-inventory"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                                :class="
                                                    hasTidakAman
                                                        ? 'text-slate-700'
                                                        : 'text-gray-400'
                                                "
                                            >
                                                Due Date
                                                <span
                                                    v-if="hasTidakAman"
                                                    class="text-blue-500"
                                                    >*</span
                                                >
                                            </label>

                                            <div
                                                :class="[
                                                    'rounded-lg border transition-all duration-200',
                                                    hasTidakAman
                                                        ? 'border-blue-500 bg-white'
                                                        : 'border-gray-300 bg-gray-100 opacity-60 cursor-not-allowed',
                                                ]"
                                            >
                                                <VueDatePicker
                                                    v-model="
                                                        selectedDateDueDate
                                                    "
                                                    :format="customFormat"
                                                    name="date_of_inventory"
                                                    placeholder="Select a date and time"
                                                    :disabled="!hasTidakAman"
                                                    :required="hasTidakAman"
                                                    class="w-full"
                                                />
                                            </div>

                                            <p
                                                v-if="!hasTidakAman"
                                                class="text-xs text-gray-500 mt-1"
                                            >
                                                *Due date hanya perlu diisi jika
                                                terdapat Temuan.
                                            </p>
                                        </div>

                                        <!-- Other Fields -->
                                        <div class="mb-4">
                                            <label
                                                class="block mb-1 text-sm text-slate-700"
                                                >Remark</label
                                            >
                                            <textarea
                                                v-model="form.remark"
                                                rows="3"
                                                class="w-full px-3 py-2 border rounded-lg text-sm text-gray-700 focus:ring-blue-500 focus:border-blue-500"
                                                placeholder="Masukkan remark"
                                            ></textarea>
                                        </div>

                                        <div class="mb-4">
                                            <label
                                                class="block mb-1 text-sm text-slate-700"
                                                >Inspektor</label
                                            >
                                            <VueMultiselect
                                                ref="picSelect"
                                                v-model="selectedPicValues"
                                                :options="optionsPic"
                                                :multiple="false"
                                                :close-on-select="true"
                                                placeholder="Pilih Inspektor"
                                                track-by="name"
                                                label="name"
                                                :class="{
                                                    'shake-border':
                                                        showValidation,
                                                }"
                                            />
                                        </div>

                                        <div class="mb-4">
                                            <label
                                                class="block mb-1 text-sm text-slate-700"
                                                >Crew</label
                                            >
                                            <VueMultiselect
                                                ref="crewSelect"
                                                v-model="selectedCrewValues"
                                                :options="optionsPic"
                                                :multiple="true"
                                                :close-on-select="false"
                                                placeholder="Pilih Crew"
                                                track-by="name"
                                                label="name"
                                                :class="{
                                                    'shake-border':
                                                        showValidation,
                                                }"
                                            />
                                        </div>

                                        <div class="mb-4">
                                            <label
                                                class="block mb-1 text-sm text-slate-700"
                                                >Lokasi</label
                                            >
                                            <!-- subtitle -->
                                            <p
                                                class="mb-2 text-xs text-slate-500"
                                            >
                                                Lokasi terakhir:
                                                {{ mobileTower.location }}
                                            </p>
                                            <input
                                                type="text"
                                                v-model="form.lokasi"
                                                class="w-full px-3 py-2 border rounded-lg text-sm text-gray-700 focus:ring-blue-500 focus:border-blue-500"
                                                placeholder="Masukkan lokasi"
                                            />
                                        </div>

                                        <div class="mb-8">
                                            <label
                                                class="block mb-1 text-sm text-slate-700"
                                                >Detail Lokasi</label
                                            >
                                            <!-- subtitle -->
                                            <p
                                                class="mb-2 text-xs text-slate-500"
                                            >
                                                Detail lokasi terakhir:
                                                {{
                                                    mobileTower.detail_location
                                                }}
                                            </p>
                                            <input
                                                type="text"
                                                v-model="form.detail_lokasi"
                                                class="w-full px-3 py-2 border rounded-lg text-sm text-gray-700 focus:ring-blue-500 focus:border-blue-500"
                                                placeholder="Masukkan detail lokasi"
                                            />
                                        </div>

                                        <!-- Nav Buttons -->
                                        <hr
                                            class="h-px mx-0 my-4 bg-transparent border-0 opacity-25 bg-gradient-to-r from-transparent via-black/40 to-transparent dark:bg-gradient-to-r dark:from-transparent dark:via-white dark:to-transparent"
                                        />
                                        <div class="flex justify-between mt-4">
                                            <button
                                                v-if="activeTab !== 'deskripsi'"
                                                @click="goPrevTab"
                                                class="px-3 py-2 rounded-lg bg-green-600 hover:bg-green-700 text-white text-sm font-semibold"
                                            >
                                                ← Sebelumnya
                                            </button>

                                            <button
                                                type="button"
                                                v-if="activeTab !== 'foto'"
                                                @click="goNextTab"
                                                class="ml-auto px-3 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold"
                                            >
                                                Selanjutnya →
                                            </button>
                                        </div>
                                    </div>

                                    <div v-if="activeTab === 'foto'">
                                        <!-- Lampiran Foto Inspeksi -->
                                        <div class="mb-6">
                                            <label
                                                class="block mb-2 text-sm font-medium text-slate-700"
                                            >
                                                Lampiran Foto KIP Inspeksi
                                            </label>

                                            <div
                                                class="relative border-2 border-dashed rounded-lg p-6 text-center cursor-pointer hover:border-blue-400 transition"
                                            >
                                                <input
                                                    type="file"
                                                    accept="image/*"
                                                    class="absolute inset-0 opacity-0 cursor-pointer"
                                                    @change="onLampiranChange"
                                                />

                                                <!-- Placeholder -->
                                                <div
                                                    v-if="!lampiranFotoInspeksi"
                                                    class="text-gray-400"
                                                >
                                                    <i
                                                        class="fas fa-upload text-4xl mb-2"
                                                    ></i>
                                                    <p class="text-sm">
                                                        Klik untuk upload foto
                                                    </p>
                                                    <p class="text-xs">
                                                        Hanya gambar (PNG, JPG,
                                                        JPEG)
                                                    </p>
                                                </div>

                                                <!-- Preview -->
                                                <div
                                                    v-else
                                                    class="relative inline-block"
                                                >
                                                    <img
                                                        :src="
                                                            lampiranFotoInspeksi.preview
                                                        "
                                                        alt="Lampiran Foto"
                                                        class="w-40 h-40 object-cover rounded-lg shadow"
                                                    />
                                                    <button
                                                        type="button"
                                                        @click="removeLampiran"
                                                        class="absolute top-1 right-1 bg-red-500 text-white rounded-full p-1 shadow hover:bg-red-600"
                                                    >
                                                        <i
                                                            class="fas fa-times"
                                                        ></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="flex justify-between mt-4">
                                            <button
                                                v-if="activeTab !== 'deskripsi'"
                                                @click="goPrevTab"
                                                class="px-3 py-2 rounded-lg bg-green-600 hover:bg-green-700 text-white text-sm font-semibold"
                                            >
                                                ← Sebelumnya
                                            </button>

                                            <button
                                                type="button"
                                                v-if="activeTab !== 'foto'"
                                                @click="goNextTab"
                                                class="ml-auto px-3 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold"
                                            >
                                                Selanjutnya →
                                            </button>
                                        </div>
                                        <hr
                                            class="h-px mx-0 my-4 bg-transparent border-0 opacity-25 bg-gradient-to-r from-transparent via-black/40 to-transparent dark:bg-gradient-to-r dark:from-transparent dark:via-white dark:to-transparent"
                                        />
                                        <div
                                            class="flex flex-nowrap mt-6 justify-between"
                                        >
                                            <Link
                                                :href="
                                                    route(
                                                        'inspeksiMobileTowerIpt.page'
                                                    )
                                                "
                                                class="relative inline-flex items-center justify-center p-0.5 mb-2 me-2 overflow-hidden text-sm font-medium text-gray-900 rounded-lg group bg-gradient-to-br from-red-200 via-red-300 to-yellow-200 group-hover:from-red-200 group-hover:via-red-300 group-hover:to-yellow-200 dark:text-white dark:hover:text-gray-900 focus:ring-4 focus:outline-none focus:ring-red-100 dark:focus:ring-red-400"
                                            >
                                                <span
                                                    class="relative px-5 py-2.5 transition-all ease-in duration-75 bg-white dark:bg-gray-900 rounded-md group-hover:bg-opacity-0"
                                                >
                                                    Cancel
                                                </span>
                                            </Link>

                                            <button
                                                type="submit"
                                                class="relative inline-flex items-center justify-center p-0.5 mb-2 me-2 overflow-hidden text-sm font-medium text-gray-900 rounded-lg group bg-gradient-to-br from-purple-600 to-blue-500 group-hover:from-purple-600 group-hover:to-blue-500 hover:text-white dark:text-white focus:ring-4 focus:outline-none focus:ring-blue-300 dark:focus:ring-blue-800"
                                            >
                                                <span
                                                    class="relative px-5 py-2.5 transition-all ease-in duration-75 bg-white dark:bg-gray-900 rounded-md group-hover:bg-opacity-0"
                                                >
                                                    Save
                                                </span>
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayoutForm>
</template>
