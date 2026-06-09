<style src="vue-multiselect/dist/vue-multiselect.css"></style>
<script setup>
import AuthenticatedLayoutForm from "@/Layouts/AuthenticatedLayoutForm.vue";
import { Link } from "@inertiajs/vue3";
import { Head, useForm } from "@inertiajs/vue3";
import VueDatePicker from "@vuepic/vue-datepicker";
import "@vuepic/vue-datepicker/dist/main.css";
import VueMultiselect from "vue-multiselect";
import Swal from "sweetalert2";
import { Inertia } from "@inertiajs/inertia";
import { ref, computed, watch, onMounted } from "vue";

const props = defineProps({
    pengguna: {
        type: Array,
    },
    pengguna_selected: {
        type: Object,
    },
    dataInspeksi: {
        type: Array,
    },
    printer: {
        type: Array,
    },
});

const errors = ref({});

const form = useForm({
    id: props.dataInspeksi.id,
    tinta_cyan: props.dataInspeksi.tinta_cyan,
    tinta_magenta: props.dataInspeksi.tinta_magenta,
    tinta_yellow: props.dataInspeksi.tinta_yellow,
    tinta_black: props.dataInspeksi.tinta_black,
    body_condition: props.dataInspeksi.body_condition,
    usb_cable_condition: props.dataInspeksi.usb_cable_condition,
    power_cable_condition: props.dataInspeksi.power_cable_condition,
    performing_physical_power_cleaning:
        props.dataInspeksi.performing_physical_power_cleaning,
    performing_cleaning_on_the_printer_waste_box:
        props.dataInspeksi.performing_cleaning_on_the_printer_waste_box,
    performing_cleaning_head: props.dataInspeksi.performing_cleaning_head,
    performing_print_quality_test:
        props.dataInspeksi.performing_print_quality_test,
    do_replacing_cable: props.dataInspeksi.do_replacing_cable,

    condition: props.dataInspeksi.condition,
    status_inv: props.dataInspeksi.inventory_status,
    hardware_any_maintenance_explain:
        props.dataInspeksi.hardware_any_maintenance_explain,
    temuan: props.dataInspeksi.findings,
    tindakan: props.dataInspeksi.findings_action,
    due_date: props.dataInspeksi.due_date,
    findings_status: props.dataInspeksi.findings_status,
    remark: props.dataInspeksi.remarks,
    inspector: props.dataInspeksi.inspector,
    inspection_status: props.dataInspeksi.inspection_status,
    image_temuan: "",
    image_tindakan: "",
    image_inspeksi: "",
    image_nozle: "",
});

const temuanFile = ref(null);
const tindakanFile = ref(null);

const isDisabled = ref(true);
const file_temuan = ref(null);
const file_tindakan = ref(null);
const file_inspeksi = ref(null);
const file_nozle = ref(null);

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

const handleFileUploadTemuan = (event) => {
    file_temuan.value = event.target.files[0];
};

const handleFileUploadTindakan = (event) => {
    file_tindakan.value = event.target.files[0];
};

const handleFileUploadInspeksi = (event) => {
    file_inspeksi.value = event.target.files[0];
};

const handleFileUploadNozle = (event) => {
    file_nozle.value = event.target.files[0];
};

const formSubmittedPIC = ref(false);

const selectedDateDueDate = ref(props.dataInspeksi.due_date);

const options = props.pengguna;

const selectedValuesInspektor = ref(
    props.pengguna.filter((option) =>
        props.dataInspeksi.inspector.includes(option.name)
    )
);

const penggunaString = computed(() => {
    return selectedValuesInspektor.value.map((option) => option.name).join("");
});

const update = () => {
    const temuanAktif = form.temuan || props.dataInspeksi.temuan;

    errors.value = {}; // reset

    if (temuanAktif) {
        // === VALIDASI TEMUAN WAJIB ===
        if (!form.temuan || form.temuan.trim() === "") {
            errors.value.temuan = "Deskripsi temuan wajib diisi.";
        }

        // === FOTO TEMUAN WAJIB HANYA JIKA BELUM ADA & USER TIDAK UPLOAD BARU ===
        if (
            !props.dataInspeksi.findings_image &&
            !temuanFile.value?.files?.length
        ) {
            errors.value.findings_image = "Lampiran foto temuan wajib.";
        }

        // === VALIDASI TINDAKAN: jika ada teks → foto tindakan wajib ===
        if (form.tindakan && form.tindakan.trim() !== "") {
            if (
                !props.dataInspeksi.action_image &&
                !tindakanFile.value?.files?.length
            ) {
                errors.value.action_image =
                    "Lampiran foto tindakan wajib jika ada tindak lanjut.";
            }
        }

        // === VALIDASI DUE DATE ===
        if (!selectedDateDueDate.value) {
            errors.value.due_date = "Due date wajib diisi.";
        } else {
            form.due_date = customFormat(selectedDateDueDate.value);
        }

        // === STATUS TEMUAN ===
        if (!form.findings_status) {
            errors.value.findings_status = "Status temuan wajib dipilih.";
        }
    }

    // STOP JIKA ERROR
    if (Object.keys(errors.value).length > 0) {
        return;
    }

    if (selectedValuesInspektor.value == "") {
        formSubmittedPIC.value = true;
        // console.log('ga oke')
        return; // Stop execution if validation fails
    }

    form.id = props.dataInspeksi.id;

    form.image_temuan = file_temuan.value;
    form.image_tindakan = file_tindakan.value;
    form.inspector = penggunaString.value;
    form.image_inspeksi = file_inspeksi.value;
    form.image_nozle = file_nozle.value;
    form.inspection_status = "Y";

    Swal.fire({
        title: "Are you sure?",
        text: "You want edit this data?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes!",
    }).then((result) => {
        if (result.isConfirmed) {
            form.post(route("inspeksiPrinterBa.update"), {
                onSuccess: () => {
                    // Show SweetAlert2 success notification
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
                        title: "error!",
                        text: "Data not created!",
                        icon: "waring",
                        confirmButtonText: "OK",
                        confirmButtonColor: "#3085d6",
                    });
                },
            });
        }
    });
};
</script>

<template>
    <Head title="Edit data Inspeksi" />

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
                        :href="route('inspeksiPrinterBa.page')"
                        class="text-sm pl-2 capitalize leading-normal text-white before:float-left before:pr-2 before:text-white before:content-['/']"
                        aria-current="page"
                    >
                        Inspeksi Printer
                    </Link>
                </ol>
                <h6 class="mb-0 font-bold text-white capitalize">Edit Pages</h6>
            </nav>
        </template>

        <div class="w-full p-6 mx-auto">
            <div class="flex flex-wrap -mx-3 justify-center">
                <div
                    class="w-full max-w-full px-3 shrink-0 md:w-8/12 md:flex-0"
                >
                    <div
                        class="mb-4 relative flex flex-col min-w-0 break-words justify-self: center bg-white border-0 shadow-xl dark:bg-slate-850 dark:shadow-dark-xl rounded-2xl bg-clip-border"
                    >
                        <div
                            class="border-black/12.5 rounded-t-2xl border-b-0 border-solid p-6 pb-0"
                        >
                            <div class="flex items-center">
                                <p class="mb-0 font-black dark:text-white/80">
                                    USER & ASSET DETAIL
                                </p>
                            </div>
                        </div>
                        <div class="flex-auto p-12">
                            <hr
                                class="h-px mx-0 my-4 bg-transparent border-0 opacity-25 bg-gradient-to-r from-transparent via-black/40 to-transparent dark:bg-gradient-to-r dark:from-transparent dark:via-white dark:to-transparent"
                            />
                            <div class="flex flex-wrap -mx-3">
                                <div
                                    class="border-black/12.5 rounded-t-2xl border-b-0 border-solid p-6 pb-0 w-full max-w-full px-3 shrink-0 md:w-12/12 md:flex-0"
                                >
                                    <div class="text-center">
                                        <p
                                            class="mb-0 dark:text-white/80 font-semibold"
                                        ></p>
                                    </div>
                                </div>
                                <div
                                    class="w-full max-w-full px-3 mt-6 md:w-12/12 md:flex-none"
                                >
                                    <div
                                        class="px-4 pb-0 grid grid-cols-1 gap-12 md:grid-cols-2"
                                    >
                                        <div class="grid grid-cols-2">
                                            <div>
                                                <p class="text-base">
                                                    Iventory Number
                                                </p>
                                            </div>
                                            <div>
                                                <p>
                                                    : {{ printer.printer_code }}
                                                </p>
                                            </div>
                                        </div>
                                        <div class="grid grid-cols-2">
                                            <div>
                                                <p class="text-base">
                                                    Asset Ho Number
                                                </p>
                                            </div>
                                            <div>
                                                <p>
                                                    :
                                                    {{
                                                        printer.asset_ho_number
                                                    }}
                                                </p>
                                            </div>
                                        </div>
                                        <div class="grid grid-cols-2">
                                            <div>
                                                <p class="text-base">Divisi</p>
                                            </div>
                                            <div>
                                                <p>
                                                    :
                                                    {{ printer.division }}
                                                </p>
                                            </div>
                                        </div>
                                        <div class="grid grid-cols-2">
                                            <div>
                                                <p class="text-base">Dept</p>
                                            </div>
                                            <div>
                                                <p>
                                                    :
                                                    {{ printer.department }}
                                                </p>
                                            </div>
                                        </div>
                                        <div class="grid grid-cols-2">
                                            <div>
                                                <p class="text-base">
                                                    Printer Type
                                                </p>
                                            </div>
                                            <div>
                                                <p>
                                                    :
                                                    {{ printer.printer_brand }}
                                                    {{ printer.printer_type }}
                                                </p>
                                            </div>
                                        </div>
                                        <div class="grid grid-cols-2">
                                            <div>
                                                <p class="text-base">
                                                    Serial Number
                                                </p>
                                            </div>
                                            <div>
                                                <p>
                                                    :
                                                    {{ printer.serial_number }}
                                                </p>
                                            </div>
                                        </div>
                                        <div class="grid grid-cols-2">
                                            <div>
                                                <p class="text-base">
                                                    Ip Address
                                                </p>
                                            </div>
                                            <div>
                                                <p>
                                                    : {{ printer.ip_address }}
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
                                                <p>: {{ printer.location }}</p>
                                            </div>
                                        </div>
                                        <div class="grid grid-cols-2">
                                            <div>
                                                <p class="text-base">Status</p>
                                            </div>
                                            <div>
                                                <p>: {{ printer.status }}</p>
                                            </div>
                                        </div>
                                        <div class="grid grid-cols-2">
                                            <div>
                                                <p class="text-base">Note</p>
                                            </div>
                                            <div>
                                                <p>: {{ printer.note }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        class="relative flex flex-col min-w-0 break-words justify-self: center bg-white border-0 shadow-xl dark:bg-slate-850 dark:shadow-dark-xl rounded-2xl bg-clip-border"
                    >
                        <div
                            class="border-black/12.5 rounded-t-2xl border-b-0 border-solid p-6 pb-0"
                        >
                            <div class="flex items-center">
                                <p class="mb-0 font-bold dark:text-white/80">
                                    Form Edit Inspeksi Printer
                                </p>
                            </div>
                        </div>
                        <div class="flex-auto p-6">
                            <form @submit.prevent="update">
                                <hr
                                    class="h-px mx-0 my-4 bg-transparent border-0 opacity-25 bg-gradient-to-r from-transparent via-black/40 to-transparent dark:bg-gradient-to-r dark:from-transparent dark:via-white dark:to-transparent"
                                />
                                <div class="flex flex-wrap -mx-3">
                                    <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-1/12 md:flex-0"
                                    ></div>

                                    <!-- HARDWARE DETAIL & CONDITION -->
                                    <div
                                        class="border-black/12.5 rounded-t-2xl border-b-0 border-solid p-6 pb-0 w-full max-w-full px-3 shrink-0 md:w-12/12 md:flex-0"
                                    >
                                        <div class="text-center mb-4">
                                            <p
                                                class="mb-0 dark:text-white/80 font-semibold"
                                            >
                                                HARDWARE DETAIL & CONDITION
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Tinta Cyan -->
                                    <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-4/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="tinta_cyan"
                                                class="inline-block ml-1 text-sm text-slate-700 dark:text-white/80"
                                            >
                                                Tinta Cyan
                                            </label>
                                            <br />
                                            <div
                                                class="inline-flex items-center space-x-4 mb-2"
                                            >
                                                <label
                                                    class="inline-flex items-center space-x-2"
                                                >
                                                    <input
                                                        required
                                                        type="radio"
                                                        name="tinta_cyan"
                                                        v-model="
                                                            form.tinta_cyan
                                                        "
                                                        value="Y"
                                                        class="form-radio text-blue-600 border-gray-300 focus:ring-blue-500"
                                                    />
                                                    <span class="text-gray-700"
                                                        >Aman</span
                                                    >
                                                </label>
                                                <label
                                                    class="inline-flex items-center space-x-2"
                                                >
                                                    <input
                                                        required
                                                        type="radio"
                                                        name="tinta_cyan"
                                                        v-model="
                                                            form.tinta_cyan
                                                        "
                                                        value="N"
                                                        class="form-radio text-blue-600 border-gray-300 focus:ring-blue-500"
                                                    />
                                                    <span class="text-gray-700"
                                                        >Tidak Aman</span
                                                    >
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Repeat similar structure for each condition field -->

                                    <!-- Tinta Magenta -->
                                    <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-4/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="tinta_magenta"
                                                class="inline-block ml-1 text-sm text-slate-700 dark:text-white/80"
                                            >
                                                Tinta Magenta
                                            </label>
                                            <br />
                                            <div
                                                class="inline-flex items-center space-x-4 mb-2"
                                            >
                                                <label
                                                    class="inline-flex items-center space-x-2"
                                                >
                                                    <input
                                                        required
                                                        type="radio"
                                                        name="tinta_magenta"
                                                        v-model="
                                                            form.tinta_magenta
                                                        "
                                                        value="Y"
                                                        class="form-radio text-blue-600 border-gray-300 focus:ring-blue-500"
                                                    />
                                                    <span class="text-gray-700"
                                                        >Aman</span
                                                    >
                                                </label>
                                                <label
                                                    class="inline-flex items-center space-x-2"
                                                >
                                                    <input
                                                        required
                                                        type="radio"
                                                        name="tinta_magenta"
                                                        v-model="
                                                            form.tinta_magenta
                                                        "
                                                        value="N"
                                                        class="form-radio text-blue-600 border-gray-300 focus:ring-blue-500"
                                                    />
                                                    <span class="text-gray-700"
                                                        >Tidak Aman</span
                                                    >
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Tinta Yellow -->
                                    <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-4/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="tinta_yellow"
                                                class="inline-block ml-1 text-sm text-slate-700 dark:text-white/80"
                                            >
                                                Tinta Yellow
                                            </label>
                                            <br />
                                            <div
                                                class="inline-flex items-center space-x-4 mb-2"
                                            >
                                                <label
                                                    class="inline-flex items-center space-x-2"
                                                >
                                                    <input
                                                        required
                                                        type="radio"
                                                        name="tinta_yellow"
                                                        v-model="
                                                            form.tinta_yellow
                                                        "
                                                        value="Y"
                                                        class="form-radio text-blue-600 border-gray-300 focus:ring-blue-500"
                                                    />
                                                    <span class="text-gray-700"
                                                        >Aman</span
                                                    >
                                                </label>
                                                <label
                                                    class="inline-flex items-center space-x-2"
                                                >
                                                    <input
                                                        required
                                                        type="radio"
                                                        name="tinta_yellow"
                                                        v-model="
                                                            form.tinta_yellow
                                                        "
                                                        value="N"
                                                        class="form-radio text-blue-600 border-gray-300 focus:ring-blue-500"
                                                    />
                                                    <span class="text-gray-700"
                                                        >Tidak Aman</span
                                                    >
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Tinta Black -->
                                    <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-4/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="tinta_black"
                                                class="inline-block ml-1 text-sm text-slate-700 dark:text-white/80"
                                            >
                                                Tinta Black
                                            </label>
                                            <br />
                                            <div
                                                class="inline-flex items-center space-x-4 mb-2"
                                            >
                                                <label
                                                    class="inline-flex items-center space-x-2"
                                                >
                                                    <input
                                                        required
                                                        type="radio"
                                                        name="tinta_black"
                                                        v-model="
                                                            form.tinta_black
                                                        "
                                                        value="Y"
                                                        class="form-radio text-blue-600 border-gray-300 focus:ring-blue-500"
                                                    />
                                                    <span class="text-gray-700"
                                                        >Aman</span
                                                    >
                                                </label>
                                                <label
                                                    class="inline-flex items-center space-x-2"
                                                >
                                                    <input
                                                        required
                                                        type="radio"
                                                        name="tinta_black"
                                                        v-model="
                                                            form.tinta_black
                                                        "
                                                        value="N"
                                                        class="form-radio text-blue-600 border-gray-300 focus:ring-blue-500"
                                                    />
                                                    <span class="text-gray-700"
                                                        >Tidak Aman</span
                                                    >
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Body / Cover Condition -->
                                    <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-4/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="body_condition"
                                                class="inline-block ml-1 text-sm text-slate-700 dark:text-white/80"
                                            >
                                                Kondisi Body / Cover
                                            </label>
                                            <br />
                                            <div
                                                class="inline-flex items-center space-x-4 mb-2"
                                            >
                                                <label
                                                    class="inline-flex items-center space-x-2"
                                                >
                                                    <input
                                                        required
                                                        type="radio"
                                                        name="body_condition"
                                                        v-model="
                                                            form.body_condition
                                                        "
                                                        value="Y"
                                                        class="form-radio text-blue-600 border-gray-300 focus:ring-blue-500"
                                                    />
                                                    <span class="text-gray-700"
                                                        >Aman</span
                                                    >
                                                </label>
                                                <label
                                                    class="inline-flex items-center space-x-2"
                                                >
                                                    <input
                                                        required
                                                        type="radio"
                                                        name="body_condition"
                                                        v-model="
                                                            form.body_condition
                                                        "
                                                        value="N"
                                                        class="form-radio text-blue-600 border-gray-300 focus:ring-blue-500"
                                                    />
                                                    <span class="text-gray-700"
                                                        >Tidak Aman</span
                                                    >
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Kondisi Kabel USB -->
                                    <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-4/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="usb_cable_condition"
                                                class="inline-block ml-1 text-sm text-slate-700 dark:text-white/80"
                                            >
                                                Kondisi Kabel USB
                                            </label>
                                            <br />
                                            <div
                                                class="inline-flex items-center space-x-4 mb-2"
                                            >
                                                <label
                                                    class="inline-flex items-center space-x-2"
                                                >
                                                    <input
                                                        required
                                                        type="radio"
                                                        name="usb_cable_condition"
                                                        v-model="
                                                            form.usb_cable_condition
                                                        "
                                                        value="Y"
                                                        class="form-radio text-blue-600 border-gray-300 focus:ring-blue-500"
                                                    />
                                                    <span class="text-gray-700"
                                                        >Aman</span
                                                    >
                                                </label>
                                                <label
                                                    class="inline-flex items-center space-x-2"
                                                >
                                                    <input
                                                        required
                                                        type="radio"
                                                        name="usb_cable_condition"
                                                        v-model="
                                                            form.usb_cable_condition
                                                        "
                                                        value="N"
                                                        class="form-radio text-blue-600 border-gray-300 focus:ring-blue-500"
                                                    />
                                                    <span class="text-gray-700"
                                                        >Tidak Aman</span
                                                    >
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Kondisi Kabel Power -->
                                    <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-4/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="power_cable_condition"
                                                class="inline-block ml-1 text-sm text-slate-700 dark:text-white/80"
                                            >
                                                Kondisi Kabel Power
                                            </label>
                                            <br />
                                            <div
                                                class="inline-flex items-center space-x-4 mb-2"
                                            >
                                                <label
                                                    class="inline-flex items-center space-x-2"
                                                >
                                                    <input
                                                        required
                                                        type="radio"
                                                        name="power_cable_condition"
                                                        v-model="
                                                            form.power_cable_condition
                                                        "
                                                        value="Y"
                                                        class="form-radio text-blue-600 border-gray-300 focus:ring-blue-500"
                                                    />
                                                    <span class="text-gray-700"
                                                        >Aman</span
                                                    >
                                                </label>
                                                <label
                                                    class="inline-flex items-center space-x-2"
                                                >
                                                    <input
                                                        required
                                                        type="radio"
                                                        name="power_cable_condition"
                                                        v-model="
                                                            form.power_cable_condition
                                                        "
                                                        value="N"
                                                        class="form-radio text-blue-600 border-gray-300 focus:ring-blue-500"
                                                    />
                                                    <span class="text-gray-700"
                                                        >Tidak Aman</span
                                                    >
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- MAINTENANCE ACTIVITY -->
                                    <div
                                        class="border-black/12.5 rounded-t-2xl border-b-0 border-solid p-6 pb-0 w-full max-w-full px-3 shrink-0 md:w-12/12 md:flex-0"
                                    >
                                        <div class="text-center mb-4">
                                            <p
                                                class="mb-0 dark:text-white/80 font-semibold"
                                            >
                                                MAINTENANCE ACTIVITY
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Melakukan Pembersihan Fisik Power -->
                                    <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-4/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="performing_physical_power_cleaning"
                                                class="inline-block ml-1 text-sm text-slate-700 dark:text-white/80"
                                            >
                                                Melakukan Pembersihan Fisik
                                                Power
                                            </label>
                                            <br />
                                            <div
                                                class="inline-flex items-center space-x-4 mb-2"
                                            >
                                                <label
                                                    class="inline-flex items-center space-x-2"
                                                >
                                                    <input
                                                        required
                                                        type="radio"
                                                        name="performing_physical_power_cleaning"
                                                        v-model="
                                                            form.performing_physical_power_cleaning
                                                        "
                                                        value="Y"
                                                        class="form-radio text-blue-600 border-gray-300 focus:ring-blue-500"
                                                    />
                                                    <span class="text-gray-700"
                                                        >Ya</span
                                                    >
                                                </label>
                                                <label
                                                    class="inline-flex items-center space-x-2"
                                                >
                                                    <input
                                                        required
                                                        type="radio"
                                                        name="performing_physical_power_cleaning"
                                                        v-model="
                                                            form.performing_physical_power_cleaning
                                                        "
                                                        value="N"
                                                        class="form-radio text-blue-600 border-gray-300 focus:ring-blue-500"
                                                    />
                                                    <span class="text-gray-700"
                                                        >Tidak</span
                                                    >
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Melakukan Pembersihan pada box pembuangan printer -->
                                    <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-4/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="performing_cleaning_on_the_printer_waste_box"
                                                class="inline-block ml-1 text-sm text-slate-700 dark:text-white/80"
                                            >
                                                Melakukan Pembersihan pada box
                                                pembuangan printer
                                            </label>
                                            <br />
                                            <div
                                                class="inline-flex items-center space-x-4 mb-2"
                                            >
                                                <label
                                                    class="inline-flex items-center space-x-2"
                                                >
                                                    <input
                                                        required
                                                        type="radio"
                                                        name="performing_cleaning_on_the_printer_waste_box"
                                                        v-model="
                                                            form.performing_cleaning_on_the_printer_waste_box
                                                        "
                                                        value="Y"
                                                        class="form-radio text-blue-600 border-gray-300 focus:ring-blue-500"
                                                    />
                                                    <span class="text-gray-700"
                                                        >Ya</span
                                                    >
                                                </label>
                                                <label
                                                    class="inline-flex items-center space-x-2"
                                                >
                                                    <input
                                                        required
                                                        type="radio"
                                                        name="performing_cleaning_on_the_printer_waste_box"
                                                        v-model="
                                                            form.performing_cleaning_on_the_printer_waste_box
                                                        "
                                                        value="N"
                                                        class="form-radio text-blue-600 border-gray-300 focus:ring-blue-500"
                                                    />
                                                    <span class="text-gray-700"
                                                        >Tidak</span
                                                    >
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Melakukan Cleaning Head / Deep Cleaning -->
                                    <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-4/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="performing_cleaning_head"
                                                class="inline-block ml-1 text-sm text-slate-700 dark:text-white/80"
                                            >
                                                Melakukan Cleaning Head / Deep
                                                Cleaning
                                            </label>
                                            <br />
                                            <div
                                                class="inline-flex items-center space-x-4 mb-2"
                                            >
                                                <label
                                                    class="inline-flex items-center space-x-2"
                                                >
                                                    <input
                                                        required
                                                        type="radio"
                                                        name="performing_cleaning_head"
                                                        v-model="
                                                            form.performing_cleaning_head
                                                        "
                                                        value="Y"
                                                        class="form-radio text-blue-600 border-gray-300 focus:ring-blue-500"
                                                    />
                                                    <span class="text-gray-700"
                                                        >Ya</span
                                                    >
                                                </label>
                                                <label
                                                    class="inline-flex items-center space-x-2"
                                                >
                                                    <input
                                                        required
                                                        type="radio"
                                                        name="performing_cleaning_head"
                                                        v-model="
                                                            form.performing_cleaning_head
                                                        "
                                                        value="N"
                                                        class="form-radio text-blue-600 border-gray-300 focus:ring-blue-500"
                                                    />
                                                    <span class="text-gray-700"
                                                        >Tidak</span
                                                    >
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Melakukan uji kualitas hasil print dengan print Test Page -->
                                    <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-4/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="performing_print_quality_test"
                                                class="inline-block ml-1 text-sm text-slate-700 dark:text-white/80"
                                            >
                                                Melakukan uji kualitas hasil
                                                print dengan print Test Page
                                            </label>
                                            <br />
                                            <div
                                                class="inline-flex items-center space-x-4 mb-2"
                                            >
                                                <label
                                                    class="inline-flex items-center space-x-2"
                                                >
                                                    <input
                                                        required
                                                        type="radio"
                                                        name="performing_print_quality_test"
                                                        v-model="
                                                            form.performing_print_quality_test
                                                        "
                                                        value="Y"
                                                        class="form-radio text-blue-600 border-gray-300 focus:ring-blue-500"
                                                    />
                                                    <span class="text-gray-700"
                                                        >Ya</span
                                                    >
                                                </label>
                                                <label
                                                    class="inline-flex items-center space-x-2"
                                                >
                                                    <input
                                                        required
                                                        type="radio"
                                                        name="performing_print_quality_test"
                                                        v-model="
                                                            form.performing_print_quality_test
                                                        "
                                                        value="N"
                                                        class="form-radio text-blue-600 border-gray-300 focus:ring-blue-500"
                                                    />
                                                    <span class="text-gray-700"
                                                        >Tidak</span
                                                    >
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Memberikan / mengganti Label -->
                                    <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-4/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="do_replacing_cable"
                                                class="inline-block ml-1 text-sm text-slate-700 dark:text-white/80"
                                            >
                                                Memberikan / mengganti Label
                                            </label>
                                            <br />
                                            <div
                                                class="inline-flex items-center space-x-4 mb-2"
                                            >
                                                <label
                                                    class="inline-flex items-center space-x-2"
                                                >
                                                    <input
                                                        required
                                                        type="radio"
                                                        name="do_replacing_cable"
                                                        v-model="
                                                            form.do_replacing_cable
                                                        "
                                                        value="Y"
                                                        class="form-radio text-blue-600 border-gray-300 focus:ring-blue-500"
                                                    />
                                                    <span class="text-gray-700"
                                                        >Ya</span
                                                    >
                                                </label>
                                                <label
                                                    class="inline-flex items-center space-x-2"
                                                >
                                                    <input
                                                        required
                                                        type="radio"
                                                        name="do_replacing_cable"
                                                        v-model="
                                                            form.do_replacing_cable
                                                        "
                                                        value="N"
                                                        class="form-radio text-blue-600 border-gray-300 focus:ring-blue-500"
                                                    />
                                                    <span class="text-gray-700"
                                                        >Tidak</span
                                                    >
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <div
                                        class="border-black/12.5 rounded-t-2xl border-b-0 border-solid p-6 pb-0 w-full max-w-full px-3 shrink-0 md:w-12/12 md:flex-0"
                                    >
                                        <div class="text-center mb-4">
                                            <p
                                                class="mb-0 dark:text-white/80 font-semibold"
                                            ></p>
                                        </div>
                                    </div>

                                    <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-6/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="inspection_image"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                                >Lampiran Hasil Nozzle
                                                Check</label
                                            >
                                            <img
                                                :src="dataInspeksi.nozle_image"
                                                alt="Gambar tidak tersedia"
                                                class="w-100 h-50 shadow-2xl rounded-xl"
                                            />
                                        </div>
                                    </div>

                                    <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-6/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="findings_image"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                                >Update Hasil Nozzle
                                                Check</label
                                            >
                                            <input
                                                type="file"
                                                ref="fileInput"
                                                class="focus:shadow-primary-outline dark:bg-slate-850 dark:text-white text-sm leading-5.6 ease block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-blue-500 focus:outline-none"
                                                @change="handleFileUploadNozle"
                                            />
                                        </div>
                                    </div>

                                    <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-6/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="condition"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                                >Condition</label
                                            >
                                            <select
                                                required
                                                id="condition"
                                                v-model="form.condition"
                                                name="condition"
                                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                            >
                                                <option value="BAGUS">
                                                    Bagus
                                                </option>
                                                <option value="RUSAK">
                                                    Rusak
                                                </option>
                                            </select>
                                        </div>
                                    </div>

                                    <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-6/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="status"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                            >
                                                Status Inventory</label
                                            >
                                            <select
                                                required
                                                id="status"
                                                v-model="form.status_inv"
                                                name="status"
                                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                            >
                                                <option
                                                    selected
                                                    value="READY_USED"
                                                >
                                                    Ready Used
                                                </option>
                                                <option value="READY_STANDBY">
                                                    Ready Standby
                                                </option>
                                                <option value="SCRAP">
                                                    Scrap
                                                </option>
                                                <option value="BREAKDOWN">
                                                    Breakdown
                                                </option>
                                            </select>
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
                                        </div>
                                    </div>

                                    <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-5/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="temuan"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                                >Deskripsi Temuan</label
                                            >
                                            <input
                                                type="text"
                                                v-model="form.temuan"
                                                name="temuan"
                                                class="focus:shadow-primary-outline dark:bg-slate-850 dark:text-white text-sm leading-5.6 ease block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-blue-500 focus:outline-none"
                                                placeholder=""
                                            />
                                            <span
                                                v-if="errors.temuan"
                                                class="text-red-500 text-sm"
                                                >{{ errors.temuan }}</span
                                            >
                                        </div>
                                    </div>

                                    <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-3/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="inspection_image"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                                >Lampiran Foto Temuan</label
                                            >
                                            <img
                                                :src="
                                                    dataInspeksi.findings_image
                                                "
                                                alt="Gambar tidak tersedia"
                                                class="w-50 h-30 shadow-2xl rounded-xl"
                                            />
                                        </div>
                                    </div>

                                    <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-4/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="findings_image"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                                >Update Lampiran Foto
                                                Temuan</label
                                            >
                                            <input
                                                type="file"
                                                ref="fileInput"
                                                class="focus:shadow-primary-outline dark:bg-slate-850 dark:text-white text-sm leading-5.6 ease block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-blue-500 focus:outline-none"
                                                @change="handleFileUploadTemuan"
                                            />
                                            <span
                                                v-if="errors.findings_image"
                                                class="text-red-500 text-sm"
                                                >{{
                                                    errors.findings_image
                                                }}</span
                                            >
                                        </div>
                                    </div>

                                    <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-5/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="tindakan"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                                >Tindak Lanjut</label
                                            >
                                            <input
                                                type="text"
                                                v-model="form.tindakan"
                                                name="tindakan"
                                                class="focus:shadow-primary-outline dark:bg-slate-850 dark:text-white text-sm leading-5.6 ease block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-blue-500 focus:outline-none"
                                                placeholder=""
                                            />
                                            <span
                                                v-if="errors.tindakan"
                                                class="text-red-500 text-sm"
                                                >{{ errors.tindakan }}</span
                                            >
                                        </div>
                                    </div>

                                    <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-3/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="inspection_image"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                                >Lampiran Foto Tindakan</label
                                            >
                                            <img
                                                :src="dataInspeksi.action_image"
                                                alt="Gambar tidak tersedia"
                                                class="w-50 h-30 shadow-2xl rounded-xl"
                                            />
                                        </div>
                                    </div>

                                    <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-4/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="action_image"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                                >Update Lampiran Foto
                                                Tindakan</label
                                            >
                                            <input
                                                type="file"
                                                ref="fileInput"
                                                class="focus:shadow-primary-outline dark:bg-slate-850 dark:text-white text-sm leading-5.6 ease block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-blue-500 focus:outline-none"
                                                @change="
                                                    handleFileUploadTindakan
                                                "
                                            />
                                            <span
                                                v-if="errors.action_image"
                                                class="text-red-500 text-sm"
                                                >{{ errors.action_image }}</span
                                            >
                                        </div>
                                    </div>

                                    <!-- Due Date -->
                                    <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-6/12 md:flex-0"
                                    >
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
                                            <span
                                                v-if="errors.due_date"
                                                class="text-red-500 text-sm"
                                                >{{ errors.due_date }}</span
                                            >
                                        </div>
                                    </div>

                                    <!-- Status Temuan -->
                                    <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-6/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="findings_status"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                            >
                                                Status Temuan
                                            </label>
                                            <select
                                                id="findings_status"
                                                v-model="form.findings_status"
                                                name="findings_status"
                                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 disabled:bg-gray-100 dark:disabled:bg-slate-800 dark:disabled:text-gray-400"
                                            >
                                                <option value="OPEN">
                                                    OPEN
                                                </option>
                                                <option value="CLOSE">
                                                    CLOSE
                                                </option>
                                            </select>
                                            <span
                                                v-if="errors.findings_status"
                                                class="text-red-500 text-sm"
                                                >{{
                                                    errors.findings_status
                                                }}</span
                                            >
                                        </div>
                                    </div>

                                    <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-12/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="device-location"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                                >Remark</label
                                            >
                                            <textarea
                                                id="message"
                                                name="remark"
                                                v-model="form.remark"
                                                rows="4"
                                                class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                                placeholder="Leave a note..."
                                            ></textarea>
                                        </div>
                                    </div>

                                    <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-5/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="select_pic"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                            >
                                                Select Inspektor</label
                                            >
                                            <VueMultiselect
                                                required
                                                v-model="
                                                    selectedValuesInspektor
                                                "
                                                :options="options"
                                                :multiple="true"
                                                :max="1"
                                                :close-on-select="true"
                                                placeholder="Pilih Inspektor"
                                                track-by="name"
                                                label="name"
                                            />
                                        </div>
                                        <span
                                            v-if="
                                                !selectedOption &&
                                                formSubmittedPIC
                                            "
                                            class="text-red-500"
                                            >PIC Tidak boleh kosong!</span
                                        >
                                    </div>

                                    <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-3/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="inspection_image"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                                >Lampiran Foto Inspeksi</label
                                            >
                                            <img
                                                :src="
                                                    dataInspeksi.inspection_image
                                                "
                                                alt="Gambar tidak tersedia"
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
                                                >Update Foto Inspeksi</label
                                            >
                                            <input
                                                type="file"
                                                ref="fileInput"
                                                class="focus:shadow-primary-outline dark:bg-slate-850 dark:text-white text-sm leading-5.6 ease block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-blue-500 focus:outline-none"
                                                @change="
                                                    handleFileUploadInspeksi
                                                "
                                            />
                                        </div>
                                    </div>
                                </div>

                                <hr
                                    class="h-px mx-0 my-4 bg-transparent border-0 opacity-25 bg-gradient-to-r from-transparent via-black/40 to-transparent dark:bg-gradient-to-r dark:from-transparent dark:via-white dark:to-transparent"
                                />
                                <div
                                    class="flex flex-nowrap mt-6 justify-between"
                                >
                                    <Link
                                        :href="route('inspeksiPrinterBa.page')"
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
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayoutForm>
</template>
