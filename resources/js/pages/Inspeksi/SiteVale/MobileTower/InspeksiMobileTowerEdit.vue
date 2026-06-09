<style src="vue-multiselect/dist/vue-multiselect.css"></style>
<script setup>
import AuthenticatedLayoutForm from "@/Layouts/AuthenticatedLayoutForm.vue";
import { Link } from "@inertiajs/vue3";
import { Head, useForm } from "@inertiajs/vue3";
import VueMultiselect from "vue-multiselect";
import VueDatePicker from "@vuepic/vue-datepicker";
import "@vuepic/vue-datepicker/dist/main.css";
import Swal from "sweetalert2";
import { ref, onMounted, watch } from "vue";

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
// const selectedPicValues = ref();
const selectedPicValues = ref(
    props.pengguna.filter((option) =>
        props.dataInspeksi.pic.includes(option.name)
    )
);
// const selectedCrewValues = ref([]);
const selectedCrewValues = ref(
    props.pengguna.filter((option) =>
        props.dataInspeksi.crew.includes(option.name)
    )
);

const temuanList = ref([]);

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

    inspection_status: props.dataInspeksi.inspection_status || "",
    condition: props.dataInspeksi.condition || "",
    kelayakan: props.dataInspeksi.worthiness || "",
    inventory_status: props.mobileTower.status || "",
    remark: props.dataInspeksi.remarks || "",
    pic: props.dataInspeksi.pic || "",
    lokasi: props.mobileTower.location || "",
    detail_lokasi: props.mobileTower.detail_location || "",
    crew: props.dataInspeksi.crew || [],
    due_date: props.dataInspeksi.due_date || "",
    temuan: props.dataInspeksi.temuan || [],
    lampiran: props.dataInspeksi.inspection_image || null,
});

function onLampiranChange(e) {
    const file = e.target.files[0];
    if (file && file.type.startsWith("image/")) {
        // cleanup old preview if needed
        if (
            form.lampiran &&
            typeof form.lampiran === "object" &&
            form.lampiran.preview
        ) {
            URL.revokeObjectURL(form.lampiran.preview);
        }

        form.lampiran = {
            file,
            preview: URL.createObjectURL(file),
        };
    }

    // reset file input (so user can reselect the same file)
    e.target.value = "";
}

// Remove image
function removeLampiran() {
    if (
        form.lampiran &&
        typeof form.lampiran === "object" &&
        form.lampiran.preview
    ) {
        URL.revokeObjectURL(form.lampiran.preview);
    }
    form.lampiran = null;
}

onMounted(() => {
    Object.assign(form.checklist_results_list, selectedList.value);
    Object.assign(form.list_results_remark, remarksList.value);

    const parseIfJson = (val) => {
        if (!val) return [];
        if (Array.isArray(val)) return val;
        try {
            return JSON.parse(val);
        } catch {
            return [];
        }
    };

    const findings = parseIfJson(props.dataInspeksi.findings);
    const findings_image = parseIfJson(props.dataInspeksi.findings_image);
    const findings_status = parseIfJson(props.dataInspeksi.findings_status);
    const findings_action = parseIfJson(props.dataInspeksi.findings_action);
    const action_image = parseIfJson(props.dataInspeksi.action_image);

    const parsedTemuan =
        findings.length > 0
            ? findings.map((f, idx) => ({
                  temuan: f || "",
                  temuan_image: findings_image[idx]
                      ? { preview: findings_image[idx], file: null }
                      : null,
                  tindak_lanjut: findings_action[idx] || "",
                  tindak_image: action_image[idx]
                      ? { preview: action_image[idx], file: null }
                      : null,
                  status: findings_status[idx] || "",
              }))
            : [
                  {
                      temuan: "",
                      temuan_image: null,
                      tindak_lanjut: "",
                      tindak_image: null,
                      status: "",
                  },
              ];

    // ✅ Assign to both local variable and form
    temuanList.value = parsedTemuan;
    form.temuan = parsedTemuan;
});

const inventoryOptions = [
    { value: "READY_USED", label: "Ready Used" },
    { value: "READY_STANDBY", label: "Ready Standby" },
    { value: "SCRAP", label: "Scrap" },
    { value: "BREAKDOWN", label: "Breakdown" },
    { value: "MUTASI", label: "Mutasi" },
];

const selectedDateDueDate = ref(props.dataInspeksi.due_date);

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

const selectedList = ref(
    props.dataInspeksi.checklist_results_list
        ? JSON.parse(props.dataInspeksi.checklist_results_list)
        : {}
);

const remarksList = ref(
    props.dataInspeksi.list_results_remark
        ? JSON.parse(props.dataInspeksi.list_results_remark)
        : {}
);

const update = () => {
    const existingLampiran = props.dataInspeksi?.lampiran;

    if (!form.lampiran && !existingLampiran) {
        alert("Lampiran Foto Inspeksi harus diupload.");
        return;
    }

    const selectPic =
        Array.isArray(selectedPicValues.value) &&
        selectedPicValues.value.length > 0
            ? selectedPicValues.value[0].name
            : "";
    const selectCrew = selectedCrewValues.value.map((c) => c.name);
    const dueDate = selectedDateDueDate.value;

    form.id = props.dataInspeksi.id;
    // form.checklist_results_list = { ...selectedList.value };
    // form.list_results_remark = { ...remarksList.value };
    form.pic = selectPic;
    form.crew = selectCrew;
    form.due_date = dueDate;

    // Temuan with images
    form.temuan = temuanList.value.map((t) => ({
        temuan: t.temuan,
        tindak_lanjut: t.tindak_lanjut,
        status: t.status,
        temuan_image: t.temuan_image?.file || t.temuan_image,
        tindak_image: t.tindak_image?.file || t.tindak_image,
    }));

    if (form.lampiran && form.lampiran.file) {
        // new file uploaded
        form.lampiran = form.lampiran.file;
    } else {
        // no new upload — keep existing URL or null
        form.lampiran = existingLampiran || null;
    }

    form.post(route("inspeksiMobileTowerVale.update"), {
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

    if (!form.kelayakan || form.kelayakan === "") {
        return {
            ok: false,
            message: "Kelayakan tidak boleh kosong.",
        };
    }

    // ✅ Validate Inventory Status
    if (!form.inventory_status || form.inventory_status === "") {
        return {
            ok: false,
            message: "Inventory Status tidak boleh kosong.",
        };
    }

    return { ok: true };
}

function checkTemuan() {
    const lastItem = temuanList.value[temuanList.value.length - 1];

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

watch(selectedPicValues, (newPic) => {
    if (newPic) {
        // Check if already in crew
        const alreadyExists = selectedCrewValues.value.some(
            (crew) => crew.id === newPic.id
        );

        // Add to crew if not already there
        if (!alreadyExists) {
            selectedCrewValues.value.push(newPic);
        }
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
                        :href="route('inspeksiMobileTowerVale.page')"
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
                                <form @submit.prevent="update">
                                    <div v-if="activeTab === 'deskripsi'">
                                        <hr
                                            class="h-px mx-0 my-4 bg-transparent border-0 opacity-25 bg-gradient-to-r from-transparent via-black/40 to-transparent dark:bg-gradient-to-r dark:from-transparent dark:via-white dark:to-transparent"
                                        />

                                        <!-- ===== Loop Kategori dan Subkategori ===== -->
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

                                                    <!-- CASE 1: Voltase Input -->
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
                                                                form
                                                                    .checklist_results_list[
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

                                                    <!-- CASE 2: Radio Aman/Tidak Aman -->
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
                                                                    form
                                                                        .checklist_results_list[
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
                                                                    form
                                                                        .checklist_results_list[
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

                                                    <!-- Tambah Remark jika Tidak Aman -->
                                                    <div
                                                        class="mt-2"
                                                        v-if="
                                                            form
                                                                .checklist_results_list[
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
                                                                form
                                                                    .list_results_remark[
                                                                    index +
                                                                        1 +
                                                                        '_' +
                                                                        sub.urutan
                                                                ]
                                                            "
                                                            required
                                                            class="form-input w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
                                                        />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Condition -->
                                        <div class="mb-8">
                                            <label
                                                for="condition"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                            >
                                                Condition
                                            </label>
                                            <select
                                                id="condition"
                                                v-model="form.condition"
                                                required
                                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                            >
                                                <option value="">
                                                    -- Select Condition --
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

                                        <!-- Inventory Status -->
                                        <div class="mb-8">
                                            <label
                                                for="inventory_status"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                            >
                                                Inventory Status
                                            </label>
                                            <select
                                                id="inventory_status"
                                                v-model="form.inventory_status"
                                                required
                                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                            >
                                                <option value="">
                                                    -- Select Inventory Status
                                                    --
                                                </option>
                                                <option value="READY_USED">
                                                    READY USED
                                                </option>
                                                <option value="READY_STANDBY">
                                                    READY STANDBY
                                                </option>
                                                <option value="BREAKDOWN">
                                                    BREAKDOWN
                                                </option>
                                                <option value="SCRAP">
                                                    SCRAP
                                                </option>
                                                <option value="MUTASI">
                                                    MUTASI
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
                                                type="button"
                                                class="px-3 py-2 rounded-lg bg-slate-200 hover:bg-slate-300 text-sm font-semibold"
                                            >
                                                ← Sebelumnya
                                            </button>

                                            <button
                                                v-if="activeTab !== 'foto'"
                                                @click="goNextTab"
                                                type="button"
                                                class="ml-auto px-3 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold"
                                            >
                                                Selanjutnya →
                                            </button>
                                        </div>
                                    </div>

                                    <!-- ================= TEMUAN TAB ================= -->
                                    <div v-if="activeTab === 'temuan'">
                                        <!-- Dynamic Temuan Section -->
                                        <div
                                            v-for="(item, idx) in form.temuan"
                                            :key="idx"
                                            class="mb-6 border rounded-lg p-4 bg-gray-50"
                                        >
                                            <h3
                                                class="text-sm font-semibold text-slate-700 mb-3"
                                            >
                                                Temuan {{ idx + 1 }}
                                            </h3>

                                            <!-- Input Temuan -->
                                            <div class="mb-4">
                                                <label
                                                    class="block mb-1 text-sm text-slate-700"
                                                    >Temuan</label
                                                >
                                                <textarea
                                                    v-model="item.temuan"
                                                    rows="3"
                                                    class="w-full px-3 py-2 border rounded-lg text-sm text-gray-700 focus:ring-blue-500 focus:border-blue-500"
                                                    placeholder="Masukkan deskripsi temuan"
                                                ></textarea>
                                            </div>

                                            <!-- Upload Gambar Temuan -->
                                            <div class="mb-4">
                                                <label
                                                    class="block mb-1 text-sm text-slate-700"
                                                >
                                                    Upload Gambar Temuan
                                                </label>
                                                <div
                                                    class="relative border-2 border-dashed rounded-lg p-4 text-center cursor-pointer hover:border-blue-400 transition"
                                                >
                                                    <input
                                                        type="file"
                                                        accept="image/*"
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
                                                            item.temuan_image
                                                                ?.preview
                                                        "
                                                        class="relative"
                                                    >
                                                        <img
                                                            :src="
                                                                item
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
                                                            Klik untuk upload
                                                        </p>
                                                        <p class="text-xs">
                                                            Hanya gambar (PNG,
                                                            JPG, JPEG)
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
                                                    v-model="item.tindak_lanjut"
                                                    rows="3"
                                                    class="w-full px-3 py-2 border rounded-lg text-sm text-gray-700 focus:ring-blue-500 focus:border-blue-500"
                                                    placeholder="Masukkan tindak lanjut"
                                                ></textarea>
                                            </div>

                                            <!-- Upload Image Tindak Lanjut -->
                                            <div class="mb-4">
                                                <label
                                                    class="block mb-1 text-sm text-slate-700"
                                                >
                                                    Upload Gambar Tindak Lanjut
                                                </label>
                                                <div
                                                    class="relative border-2 border-dashed rounded-lg p-4 text-center cursor-pointer hover:border-blue-400 transition"
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

                                                    <div
                                                        v-if="
                                                            item.tindak_image
                                                                ?.preview
                                                        "
                                                        class="relative"
                                                    >
                                                        <img
                                                            :src="
                                                                item
                                                                    .tindak_image
                                                                    .preview
                                                            "
                                                            class="w-32 h-32 object-cover rounded-lg mx-auto shadow"
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

                                                    <div
                                                        v-else
                                                        class="text-gray-400"
                                                    >
                                                        <i
                                                            class="fas fa-upload text-gray-400 text-4xl mb-2"
                                                        ></i>
                                                        <p class="text-sm">
                                                            Klik untuk upload
                                                        </p>
                                                        <p class="text-xs">
                                                            Hanya gambar (PNG,
                                                            JPG, JPEG)
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
                                                    class="w-full px-3 py-2 border rounded-lg text-sm text-gray-700 focus:ring-blue-500 focus:border-blue-500"
                                                >
                                                    <option value="">
                                                        -- Pilih Status --
                                                    </option>
                                                    <option value="OPEN">
                                                        Open
                                                    </option>
                                                    <option value="PROGRESS">
                                                        Progress
                                                    </option>
                                                    <option value="CLOSED">
                                                        Closed
                                                    </option>
                                                </select>
                                            </div>
                                        </div>

                                        <!-- Add More Button -->
                                        <div class="mb-6">
                                            <button
                                                type="button"
                                                @click="addTemuan"
                                                :disabled="
                                                    form.temuan.length >= 5
                                                "
                                                class="px-3 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 disabled:bg-gray-400 text-white text-sm font-semibold"
                                            >
                                                + Add More
                                            </button>
                                        </div>

                                        <!-- ================= EXTRA FIELDS ================= -->
                                        <div class="mb-4">
                                            <label
                                                for="date-of-inventory"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                            >
                                                Due Date
                                            </label>
                                            <VueDatePicker
                                                v-model="selectedDateDueDate"
                                                :format="customFormat"
                                                name="date_of_inventory"
                                                placeholder="Select a date and time"
                                            />
                                        </div>

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

                                        <!-- Navigation Buttons -->
                                        <hr
                                            class="h-px mx-0 my-4 bg-transparent border-0 opacity-25 bg-gradient-to-r from-transparent via-black/40 to-transparent dark:bg-gradient-to-r dark:from-transparent dark:via-white dark:to-transparent"
                                        />

                                        <div class="flex justify-between mt-4">
                                            <button
                                                @click="goPrevTab"
                                                type="button"
                                                class="px-3 py-2 rounded-lg bg-slate-200 hover:bg-slate-300 text-sm font-semibold"
                                            >
                                                ← Sebelumnya
                                            </button>

                                            <button
                                                @click="goNextTab"
                                                type="button"
                                                class="ml-auto px-3 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold"
                                            >
                                                Selanjutnya →
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Lampiran Foto Inspeksi -->
                                    <div v-if="activeTab === 'foto'">
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
                                                    v-if="!form.lampiran"
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

                                                <!-- Image Preview (string or object) -->
                                                <div
                                                    v-else
                                                    class="relative inline-block"
                                                >
                                                    <img
                                                        :src="
                                                            typeof form.lampiran ===
                                                            'string'
                                                                ? form.lampiran
                                                                : form.lampiran
                                                                      .preview
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
                                                        'inspeksiMobileTowerVale.page'
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
                                                    Update
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
