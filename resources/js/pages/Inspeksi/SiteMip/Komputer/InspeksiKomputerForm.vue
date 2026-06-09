<style src="vue-multiselect/dist/vue-multiselect.css"></style>
<script setup>
import AuthenticatedLayoutForm from "@/Layouts/AuthenticatedLayoutForm.vue";
import { Link } from "@inertiajs/vue3";
import { Head, useForm } from "@inertiajs/vue3";
import VueMultiselect from "vue-multiselect";
import VueDatePicker from "@vuepic/vue-datepicker";
import "@vuepic/vue-datepicker/dist/main.css";
import Swal from "sweetalert2";
import { ref, computed } from "vue";

const props = defineProps(["inspeksi", "crew"]);

const form = useForm({
    id: props.inspeksi.id,
    cpu: "",
    internalCpu: "",
    monitor: "",
    license: "",
    softwareStandaritation: "",
    deviceNameStandaritation: "",
    cache: "",
    restore: "",
    winUpdate: "",
    storageHealth: "",
    defrag: "",
    change_user_pass: "",
    autolock: "",
    enter_password: "",
    findings: "",
    findings_image: "",
    action: "",
    action_image: "",
    inspection_image: "",
    remark: "",
    due_date: "",
    kondisi: "",
    inventory_status: "",
    status: "",
    ip_address: "",
    location: "",
    crew: "",
    ip_address: props.inspeksi.computer.ip_address,
    location: props.inspeksi.computer.location,
});

const isDisabled = ref(true);
const selectedValues = ref(null); // Awalnya array kosong
const selectedOptionCondition = ref("");
const selectedOptionInventoryStatus = ref(props.inspeksi.computer.status);
const selectedOptionStatus = ref("");
const fileFindings = ref(null);
const fileAction = ref(null);
const fileInspection = ref(null);
const selectedDate = ref(null);

const findings = ref("");
const findingImageIsRequired = ref(false);
const findingStatusIsRequired = ref(false);
const findingDuedateIsRequired = ref(false);
const action = ref("");
const actionImageIsRequired = ref(false);

const checkRequiredImageFinding = () => {
    findingImageIsRequired.value = findings.value.trim() !== "";
    findingStatusIsRequired.value = findings.value.trim() !== "";
    findingDuedateIsRequired.value = findings.value.trim() !== "";
};

const checkRequiredImageAction = () => {
    actionImageIsRequired.value = action.value.trim() !== "";
};

const handleFileUploadFindings = (event) => {
    fileFindings.value = event.target.files[0];
};

const handleFileUploadAction = (event) => {
    fileAction.value = event.target.files[0];
};

const handleFileUploadInspection = (event) => {
    fileInspection.value = event.target.files[0];
};

const crewString = computed(() => {
    return selectedValues.value.map((option) => option.name).join(", ");
});

const options = props.crew;

const customFormat = (date) => {
    const d = new Date(date);
    const year = d.getFullYear();
    const month = String(d.getMonth() + 1).padStart(2, "0");
    const day = String(d.getDate()).padStart(2, "0");
    const hours = String(d.getHours()).padStart(2, "0");
    const minutes = String(d.getMinutes()).padStart(2, "0");
    const seconds = String(d.getSeconds()).padStart(2, "0");

    return `${year}-${month}-${day}`;
};

const formSubmittedCrew = ref(false);

const save = () => {
    if (selectedValues.value == null) {
        formSubmittedCrew.value = true;
        // console.log('ga oke')
        return; // Stop execution if validation fails
    }

    if (selectedDate.value != null) {
        form.due_date = customFormat(selectedDate.value);
    }

    if (fileAction) {
        form.action_image = fileAction.value;
    }
    if (fileFindings) {
        form.findings_image = fileFindings.value;
    }
    form.crew = crewString.value;
    form.kondisi = selectedOptionCondition.value;
    form.inventory_status = selectedOptionInventoryStatus.value;
    form.status = selectedOptionStatus.value;
    form.findings = findings.value;
    form.action = action.value;
    form.inspection_image = fileInspection.value;
    form.post(route("inspeksiKomputerMip.store"), {
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
};
</script>

<template>
    <Head title="Form Inspeksi dan Perawatan PC" />

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
                    <li
                        class="text-sm pl-2 capitalize leading-normal text-white before:float-left before:pr-2 before:text-white before:content-['/']"
                        aria-current="page"
                    >
                        PC Inspection Pages
                    </li>
                </ol>
                <h6 class="mb-0 font-bold text-white capitalize">
                    PC Inspection
                </h6>
            </nav>
        </template>

        <div class="w-full p-6 mx-auto">
            <div class="flex flex-wrap -mx-3 justify-center">
                <div class="w-full max-w-full px-3 md:w-8/12 md:flex-none mb-5">
                    <div
                        class="mb-5 relative flex flex-col min-w-0 break-words bg-white border-0 shadow-xl dark:bg-slate-850 dark:shadow-dark-xl rounded-2xl bg-clip-border"
                    >
                        <div
                            class="flex flex-row p-6 px-4 pb-0 mb-0 border-b-0 rounded-t-2xl"
                        >
                            <h6 class="mb-0 mr-3 dark:text-white">
                                Data PC
                            </h6>
                        </div>
                        <div class="flex-auto p-4 pt-6">
                            <div class="grid grid-cols-2">
                                <div>
                                    <p class="text-base">Iventory Number</p>
                                </div>
                                <div>
                                    <p>
                                        :
                                        {{ inspeksi.computer.computer_code }}
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
                                        {{ inspeksi.computer.number_asset_ho }}
                                    </p>
                                </div>
                            </div>
                            <div class="grid grid-cols-2">
                                <div>
                                    <p class="text-base">PC Name</p>
                                </div>
                                <div>
                                    <p>
                                        :
                                        {{ inspeksi.computer.computer_name }}
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
                                        {{ inspeksi.computer.assets_category }}
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
                                        {{ inspeksi.computer.serial_number }}
                                    </p>
                                </div>
                            </div>
                            <div class="grid grid-cols-2">
                                <div>
                                    <p class="text-base">Aplikasi</p>
                                </div>
                                <div>
                                    <p>
                                        :
                                        {{ inspeksi.computer.aplikasi }}
                                    </p>
                                </div>
                            </div>
                            <div class="grid grid-cols-2">
                                <div>
                                    <p class="text-base">License</p>
                                </div>
                                <div>
                                    <p>
                                        :
                                        {{ inspeksi.computer.license }}
                                    </p>
                                </div>
                            </div>
                            <div class="grid grid-cols-2">
                                <div>
                                    <p class="text-base">Ip Address</p>
                                </div>
                                <div>
                                    <p>
                                        :
                                        {{ inspeksi.computer.ip_address }}
                                    </p>
                                </div>
                            </div>
                            <div class="grid grid-cols-2">
                                <div>
                                    <p class="text-base">Location</p>
                                </div>
                                <div>
                                    <p>
                                        :
                                        {{ inspeksi.computer.location }}
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
                                        :
                                        {{ inspeksi.computer.condition }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    class="w-full max-w-full px-3 shrink-0 md:w-8/12 md:flex-0"
                >
                    <div
                        class="relative flex flex-col min-w-0 break-words justify-self: center bg-white border-0 shadow-xl dark:bg-slate-850 dark:shadow-dark-xl rounded-2xl bg-clip-border"
                    >
                        <div
                            class="border-black/12.5 rounded-t-2xl border-b-0 border-solid p-6 pb-0"
                        >
                            <div class="flex items-center">
                                <h4 class="mb-0 font-bold dark:text-white/80">
                                    Form Inspeksi dan Perawatan PC
                                </h4>
                            </div>
                        </div>

                        <div class="flex-auto p-6">
                            <form @submit.prevent="save">
                                <hr
                                    class="h-px mx-0 my-4 bg-transparent border-0 opacity-25 bg-gradient-to-r from-transparent via-black/40 to-transparent dark:bg-gradient-to-r dark:from-transparent dark:via-white dark:to-transparent"
                                />
                                <div class="max-w-8xl mx-auto p-1 mt-10">
                                    <h2
                                        class="text-lg text-left font-bold mb-4"
                                    >
                                        INSPEKSI DAN PERWATAN HARDWARE
                                    </h2>
                                </div>
                                <div class="flex flex-wrap p-4 -mx-3">
                                    <div
                                        class="w-full max-w-full shrink-0 md:w-4/12 md:flex-0"
                                    >
                                        <h3 class="text-sm font-semibold">
                                            Check dan Perbersihan Bagian Luar
                                            CPU
                                        </h3>
                                        <div
                                            class="mb-4 flex items-center space-x-4"
                                        >
                                            <label
                                                class="flex items-center space-x-2"
                                            >
                                                <input
                                                    required
                                                    v-model="form.cpu"
                                                    type="radio"
                                                    name="cpu"
                                                    value="Y"
                                                    class="text-blue-600 focus:ring-blue-500"
                                                />
                                                <span class="text-sm"
                                                    >Ya</span
                                                >
                                            </label>
                                            <label
                                                class="flex items-center space-x-2"
                                            >
                                                <input
                                                    required
                                                    v-model="form.cpu"
                                                    type="radio"
                                                    name="cpu"
                                                    value="N"
                                                    class="text-blue-600 focus:ring-blue-500"
                                                />
                                                <span class="text-sm"
                                                    >Tidak</span
                                                >
                                            </label>
                                        </div>
                                    </div>

                                    <div
                                        class="w-full max-w-full shrink-0 md:w-4/12 md:flex-0"
                                    >
                                        <h3 class="text-sm font-semibold">
                                            Perbersihan Komponen Internal CPU
                                        </h3>
                                        <div
                                            class="mb-4 flex items-center space-x-4"
                                        >
                                            <label
                                                class="flex items-center space-x-2"
                                            >
                                                <input
                                                    required
                                                    v-model="form.internalCpu"
                                                    type="radio"
                                                    name="internalCpu"
                                                    value="Y"
                                                    class="text-blue-600 focus:ring-blue-500"
                                                />
                                                <span class="text-sm"
                                                    >Ya</span
                                                >
                                            </label>
                                            <label
                                                class="flex items-center space-x-2"
                                            >
                                                <input
                                                    required
                                                    v-model="form.internalCpu"
                                                    type="radio"
                                                    name="internalCpu"
                                                    value="N"
                                                    class="text-blue-600 focus:ring-blue-500"
                                                />
                                                <span class="text-sm"
                                                    >Tidak</span
                                                >
                                            </label>
                                        </div>
                                    </div>

                                    <div
                                        class="w-full max-w-full shrink-0 md:w-4/12 md:flex-0"
                                    >
                                        <h3 class="text-sm font-semibold">
                                            Monitor
                                        </h3>
                                        <div
                                            class="mb-4 flex items-center space-x-4"
                                        >
                                            <label
                                                class="flex items-center space-x-2"
                                            >
                                                <input
                                                    required
                                                    v-model="form.monitor"
                                                    type="radio"
                                                    name="monitor"
                                                    value="Y"
                                                    class="text-blue-600 focus:ring-blue-500"
                                                />
                                                <span class="text-sm"
                                                    >Ya</span
                                                >
                                            </label>
                                            <label
                                                class="flex items-center space-x-2"
                                            >
                                                <input
                                                    required
                                                    v-model="form.monitor"
                                                    type="radio"
                                                    name="monitor"
                                                    value="N"
                                                    class="text-blue-600 focus:ring-blue-500"
                                                />
                                                <span class="text-sm"
                                                    >Tidak</span
                                                >
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="max-w-8xl mx-auto p-1 mt-3">
                                    <h2
                                        class="text-lg text-left font-bold mb-4"
                                    >
                                        PEMERIKSAAN SOFTWARE
                                    </h2>
                                </div>
                                <div class="flex flex-wrap p-4 -mx-3">
                                    <div
                                        class="w-full max-w-full shrink-0 md:w-3/12 md:flex-0"
                                    >
                                        <h3 class="text-sm font-semibold">
                                            Standarisasi Software
                                        </h3>
                                        <div
                                            class="mb-4 flex items-center space-x-4"
                                        >
                                            <label
                                                class="flex items-center space-x-2"
                                            >
                                                <input
                                                    required
                                                    v-model="form.softwareStandaritation"
                                                    type="radio"
                                                    name="softwareStandaritation"
                                                    value="Y"
                                                    class="text-blue-600 focus:ring-blue-500"
                                                />
                                                <span class="text-sm"
                                                    >Ya</span
                                                >
                                            </label>
                                            <label
                                                class="flex items-center space-x-2"
                                            >
                                                <input
                                                    required
                                                    v-model="form.softwareStandaritation"
                                                    type="radio"
                                                    name="softwareStandaritation"
                                                    value="N"
                                                    class="text-blue-600 focus:ring-blue-500"
                                                />
                                                <span class="text-sm"
                                                    >Tidak</span
                                                >
                                            </label>
                                        </div>
                                    </div>
                                    <div
                                        class="w-full max-w-full shrink-0 md:w-3/12 md:flex-0"
                                    >
                                        <h3 class="text-sm font-semibold">
                                            Standarisasi Device Name
                                        </h3>
                                        <div
                                            class="mb-4 flex items-center space-x-4"
                                        >
                                            <label
                                                class="flex items-center space-x-2"
                                            >
                                                <input
                                                    required
                                                    v-model="form.deviceNameStandaritation"
                                                    type="radio"
                                                    name="deviceNameStandaritation"
                                                    value="Y"
                                                    class="text-blue-600 focus:ring-blue-500"
                                                />
                                                <span class="text-sm"
                                                    >Ya</span
                                                >
                                            </label>
                                            <label
                                                class="flex items-center space-x-2"
                                            >
                                                <input
                                                    required
                                                    v-model="form.deviceNameStandaritation"
                                                    type="radio"
                                                    name="deviceNameStandaritation"
                                                    value="N"
                                                    class="text-blue-600 focus:ring-blue-500"
                                                />
                                                <span class="text-sm"
                                                    >Tidak</span
                                                >
                                            </label>
                                        </div>
                                    </div>
                                    <div
                                        class="w-full max-w-full shrink-0 md:w-3/12 md:flex-0"
                                    >
                                        <h3 class="text-sm font-semibold">
                                            License Software
                                        </h3>
                                        <div
                                            class="mb-4 flex items-center space-x-4"
                                        >
                                            <label
                                                class="flex items-center space-x-2"
                                            >
                                                <input
                                                    required
                                                    v-model="form.license"
                                                    type="radio"
                                                    name="license"
                                                    value="Y"
                                                    class="text-blue-600 focus:ring-blue-500"
                                                />
                                                <span class="text-sm"
                                                    >Ya</span
                                                >
                                            </label>
                                            <label
                                                class="flex items-center space-x-2"
                                            >
                                                <input
                                                    required
                                                    v-model="form.license"
                                                    type="radio"
                                                    name="license"
                                                    value="N"
                                                    class="text-blue-600 focus:ring-blue-500"
                                                />
                                                <span class="text-sm"
                                                    >Tidak</span
                                                >
                                            </label>
                                        </div>
                                    </div>

                                    <div
                                        class="w-full max-w-full shrink-0 md:w-3/12 md:flex-0"
                                    >
                                        <h3 class="text-sm font-semibold">
                                            Pembersihan Cache
                                        </h3>
                                        <div
                                            class="mb-4 flex items-center space-x-4"
                                        >
                                            <label
                                                class="flex items-center space-x-2"
                                            >
                                                <input
                                                    required
                                                    v-model="form.cache"
                                                    type="radio"
                                                    name="cache"
                                                    value="Y"
                                                    class="text-blue-600 focus:ring-blue-500"
                                                />
                                                <span class="text-sm"
                                                    >Ya</span
                                                >
                                            </label>
                                            <label
                                                class="flex items-center space-x-2"
                                            >
                                                <input
                                                    required
                                                    v-model="form.cache"
                                                    type="radio"
                                                    name="cache"
                                                    value="N"
                                                    class="text-blue-600 focus:ring-blue-500"
                                                />
                                                <span class="text-sm"
                                                    >Tidak</span
                                                >
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex flex-wrap p-4 -mx-3">
                                    <div
                                        class="w-full max-w-full shrink-0 md:w-3/12 md:flex-0"
                                    >
                                        <h3 class="text-sm font-semibold">
                                            System Restore
                                        </h3>
                                        <div
                                            class="mb-4 flex items-center space-x-4"
                                        >
                                            <label
                                                class="flex items-center space-x-2"
                                            >
                                                <input
                                                    required
                                                    v-model="form.restore"
                                                    type="radio"
                                                    name="restore"
                                                    value="Y"
                                                    class="text-blue-600 focus:ring-blue-500"
                                                />
                                                <span class="text-sm"
                                                    >Ya</span
                                                >
                                            </label>
                                            <label
                                                class="flex items-center space-x-2"
                                            >
                                                <input
                                                    required
                                                    v-model="form.restore"
                                                    type="radio"
                                                    name="restore"
                                                    value="N"
                                                    class="text-blue-600 focus:ring-blue-500"
                                                />
                                                <span class="text-sm"
                                                    >Tidak</span
                                                >
                                            </label>
                                        </div>
                                    </div>

                                    <div
                                        class="w-full max-w-full shrink-0 md:w-3/12 md:flex-0"
                                    >
                                        <h3 class="text-sm font-semibold">
                                            Check Windows Update
                                        </h3>
                                        <div
                                            class="mb-4 flex items-center space-x-4"
                                        >
                                            <label
                                                class="flex items-center space-x-2"
                                            >
                                                <input
                                                    required
                                                    v-model="
                                                        form.winUpdate
                                                    "
                                                    type="radio"
                                                    name="winUpdate"
                                                    value="ON"
                                                    class="text-blue-600 focus:ring-blue-500"
                                                />
                                                <span class="text-sm"
                                                    >On</span
                                                >
                                            </label>
                                            <label
                                                class="flex items-center space-x-2"
                                            >
                                                <input
                                                    required
                                                    v-model="
                                                        form.winUpdate
                                                    "
                                                    type="radio"
                                                    name="winUpdate"
                                                    value="OFF"
                                                    class="text-blue-600 focus:ring-blue-500"
                                                />
                                                <span class="text-sm"
                                                    >Off</span
                                                >
                                            </label>
                                        </div>
                                    </div>

                                    <div
                                        class="w-full max-w-full shrink-0 md:w-3/12 md:flex-0"
                                    >
                                        <h3 class="text-sm font-semibold">
                                            Defrag
                                        </h3>
                                        <div
                                            class="mb-4 flex items-center space-x-4"
                                        >
                                            <label
                                                class="flex items-center space-x-2"
                                            >
                                                <input
                                                    required
                                                    v-model="form.defrag"
                                                    type="radio"
                                                    name="defrag"
                                                    value="Y"
                                                    class="text-blue-600 focus:ring-blue-500"
                                                />
                                                <span class="text-sm"
                                                    >Ya</span
                                                >
                                            </label>
                                            <label
                                                class="flex items-center space-x-2"
                                            >
                                                <input
                                                    required
                                                    v-model="form.defrag"
                                                    type="radio"
                                                    name="defrag"
                                                    value="N"
                                                    class="text-blue-600 focus:ring-blue-500"
                                                />
                                                <span class="text-sm"
                                                    >Tidak</span
                                                >
                                            </label>
                                        </div>
                                    </div>

                                     <div
                                        class="w-full max-w-full shrink-0 md:w-2/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="serial-number"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                                >SSD/HDD Health</label
                                            >
                                            <input
                                                type="text"
                                                name="serial_number"
                                                v-model="form.storageHealth"
                                                class="focus:shadow-primary-outline dark:bg-slate-850 dark:text-white text-sm leading-5.6 ease block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-blue-500 focus:outline-none"
                                                placeholder="90%"
                                            />
                                        </div>
                                    </div>
                                      <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-1/12 md:flex-0"
                                    >
                                        
                                    </div>
                                </div>

                                <div class="max-w-8xl mx-auto p-1">
                                    <h2
                                        class="text-lg text-left font-bold mb-4"
                                    >
                                        PEMERIKSAAN SECURITY
                                    </h2>
                                </div>
                                <div class="flex flex-wrap p-4 -mx-3">
                                    <div
                                        class="w-full max-w-full shrink-0 md:w-4/12 md:flex-0"
                                    >
                                        <h3 class="text-sm font-semibold">
                                            Penggantian Username
                                        </h3>
                                        <div
                                            class="mb-4 flex items-center space-x-4"
                                        >
                                            <label
                                                class="flex items-center space-x-2"
                                            >
                                                <input
                                                    required
                                                    v-model="
                                                        form.change_user_pass
                                                    "
                                                    type="radio"
                                                    name="change_user_pass"
                                                    value="Y"
                                                    class="text-blue-600 focus:ring-blue-500"
                                                />
                                                <span class="text-sm"
                                                    >Ya</span
                                                >
                                            </label>
                                            <label
                                                class="flex items-center space-x-2"
                                            >
                                                <input
                                                    required
                                                    v-model="
                                                        form.change_user_pass
                                                    "
                                                    type="radio"
                                                    name="change_user_pass"
                                                    value="N"
                                                    class="text-blue-600 focus:ring-blue-500"
                                                />
                                                <span class="text-sm"
                                                    >Tidak</span
                                                >
                                            </label>
                                        </div>
                                    </div>

                                    <div
                                        class="w-full max-w-full shrink-0 md:w-4/12 md:flex-0"
                                    >
                                        <h3 class="text-sm font-semibold">
                                            Setting Auto Lock Screen
                                        </h3>
                                        <div
                                            class="mb-4 flex items-center space-x-4"
                                        >
                                            <label
                                                class="flex items-center space-x-2"
                                            >
                                                <input
                                                    required
                                                    v-model="form.autolock"
                                                    type="radio"
                                                    name="autolock"
                                                    value="Y"
                                                    class="text-blue-600 focus:ring-blue-500"
                                                />
                                                <span class="text-sm"
                                                    >Ya</span
                                                >
                                            </label>
                                            <label
                                                class="flex items-center space-x-2"
                                            >
                                                <input
                                                    required
                                                    v-model="form.autolock"
                                                    type="radio"
                                                    name="autolock"
                                                    value="N"
                                                    class="text-blue-600 focus:ring-blue-500"
                                                />
                                                <span class="text-sm"
                                                    >Tidak</span
                                                >
                                            </label>
                                        </div>
                                    </div>

                                    <div
                                        class="w-full max-w-full shrink-0 md:w-4/12 md:flex-0"
                                    >
                                        <h3 class="text-sm font-semibold">
                                            Setting Input Password After Lock Screen
                                        </h3>
                                        <div
                                            class="mb-4 flex items-center space-x-4"
                                        >
                                            <label
                                                class="flex items-center space-x-2"
                                            >
                                                <input
                                                    required
                                                    v-model="
                                                        form.enter_password
                                                    "
                                                    type="radio"
                                                    name="enter_password"
                                                    value="Y"
                                                    class="text-blue-600 focus:ring-blue-500"
                                                />
                                                <span class="text-sm"
                                                    >Ya</span
                                                >
                                            </label>
                                            <label
                                                class="flex items-center space-x-2"
                                            >
                                                <input
                                                    required
                                                    v-model="
                                                        form.enter_password
                                                    "
                                                    type="radio"
                                                    name="enter_password"
                                                    value="N"
                                                    class="text-blue-600 focus:ring-blue-500"
                                                />
                                                <span class="text-sm"
                                                    >Tidak</span
                                                >
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <hr
                                    class="mb-10 h-px mx-0 my-4 bg-transparent border-0 opacity-25 bg-gradient-to-r from-transparent via-black/40 to-transparent dark:bg-gradient-to-r dark:from-transparent dark:via-white dark:to-transparent"
                                />

                                <div class="flex flex-wrap -mx-3">
                                    <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-3/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="serial-number"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                                >Findings</label
                                            >
                                            <input
                                                type="text"
                                                name="serial_number"
                                                v-model="findings"
                                                class="focus:shadow-primary-outline dark:bg-slate-850 dark:text-white text-sm leading-5.6 ease block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-blue-500 focus:outline-none"
                                                placeholder="Temuan Inspeksi"
                                                @input="
                                                    checkRequiredImageFinding
                                                "
                                            />
                                        </div>
                                    </div>
                                    <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-3/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="link_documentation_asset_image"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                                >Findings Image</label
                                            >
                                            <input
                                                :required="
                                                    findingImageIsRequired
                                                "
                                                type="file"
                                                ref="fileInput"
                                                class="focus:shadow-primary-outline dark:bg-slate-850 dark:text-white text-sm leading-5.6 ease block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-blue-500 focus:outline-none"
                                                placeholder="2.4 / 5.8 Ghz"
                                                @change="
                                                    handleFileUploadFindings
                                                "
                                            />
                                        </div>
                                    </div>
                                    <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-3/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="serial-number"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                                >Action Findings</label
                                            >
                                            <input
                                                type="text"
                                                v-model="action"
                                                name="serial_number"
                                                class="focus:shadow-primary-outline dark:bg-slate-850 dark:text-white text-sm leading-5.6 ease block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-blue-500 focus:outline-none"
                                                placeholder="Action Temuan Inspeksi"
                                                @input="
                                                    checkRequiredImageAction
                                                "
                                            />
                                        </div>
                                    </div>
                                    <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-3/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="link_documentation_asset_image"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                                >Action Image</label
                                            >
                                            <input
                                                :required="
                                                    actionImageIsRequired
                                                "
                                                type="file"
                                                ref="fileInput"
                                                class="focus:shadow-primary-outline dark:bg-slate-850 dark:text-white text-sm leading-5.6 ease block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-blue-500 focus:outline-none"
                                                placeholder="2.4 / 5.8 Ghz"
                                                @change="handleFileUploadAction"
                                            />
                                        </div>
                                    </div>
                                    <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-4/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="status"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                            >
                                                Finding Status</label
                                            >
                                            <select
                                                :required="
                                                    findingStatusIsRequired
                                                "
                                                v-model="selectedOptionStatus"
                                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                            >
                                                <option value="" disabled>
                                                    Select Condition
                                                </option>
                                                <option value="OPEN">
                                                    OPEN
                                                </option>
                                                <option value="CLOSED">
                                                    CLOSED
                                                </option>
                                                <option value="SCRAP">
                                                    SCRAP
                                                </option>
                                                <option value="MUTASI">
                                                    MUTASI
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                    <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-4/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="link_documentation_asset_image"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                                >Inspection Image</label
                                            >
                                            <input
                                                required
                                                type="file"
                                                ref="fileInput"
                                                class="focus:shadow-primary-outline dark:bg-slate-850 dark:text-white text-sm leading-5.6 ease block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-blue-500 focus:outline-none"
                                                placeholder="2.4 / 5.8 Ghz"
                                                @change="
                                                    handleFileUploadInspection
                                                "
                                            />
                                        </div>
                                    </div>
                                    <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-4/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="dept"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                                >Crew</label
                                            >
                                            <VueMultiselect
                                                v-model="selectedValues"
                                                :options="options"
                                                :multiple="true"
                                                :close-on-select="true"
                                                placeholder="Select Crew"
                                                track-by="name"
                                                label="name"
                                            />
                                        </div>
                                        <span
                                            v-if="
                                                !selectedOption &&
                                                formSubmittedCrew
                                            "
                                            class="text-red-500"
                                            >Crew Tidak boleh kosong!</span
                                        >
                                    </div>
                                    <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-4/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="remark"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                                >Remark Inspection</label
                                            >
                                            <input
                                                type="text"
                                                v-model="form.remark"
                                                name="remark"
                                                class="focus:shadow-primary-outline dark:bg-slate-850 dark:text-white text-sm leading-5.6 ease block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-blue-500 focus:outline-none"
                                                placeholder="Remark Inspection"
                                            />
                                        </div>
                                    </div>
                                    <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-4/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="date-of-complaint"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                                >Due Date</label
                                            >
                                            <VueDatePicker
                                                :required="
                                                    findingDuedateIsRequired
                                                "
                                                v-model="selectedDate"
                                                :format="customFormat"
                                                placeholder="Select a date and time"
                                            />
                                        </div>
                                    </div>
                                    <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-4/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="kondisi"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                            >
                                                Condition</label
                                            >
                                            <select
                                                required
                                                v-model="
                                                    selectedOptionCondition
                                                "
                                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                            >
                                                <option value="" disabled>
                                                    Select Condition
                                                </option>
                                                <option value="BAGUS">
                                                    BAGUS
                                                </option>
                                                <option value="RUSAK">
                                                    RUSAK
                                                </option>
                                                <option value="SCRAP">
                                                    SCRAP
                                                </option>
                                                <option value="MUTASI">
                                                    MUTASI
                                                </option>
                                            </select>
                                        </div>
                                    </div>

                                    <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-4/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="inventory-status"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                            >
                                                Inventory Status</label
                                            >
                                            <select
                                                required
                                                v-model="
                                                    selectedOptionInventoryStatus
                                                "
                                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                            >
                                                <option value="" disabled>
                                                    Select Condition
                                                </option>
                                                <option value="READY_USED">
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
                                        class="w-full max-w-full px-3 shrink-0 md:w-4/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="ip-address"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                                >Ip Address</label
                                            >
                                            <input
                                                required
                                                type="text"
                                                v-model="form.ip_address"
                                                name="ip_address"
                                                class="focus:shadow-primary-outline dark:bg-slate-850 dark:text-white text-sm leading-5.6 ease block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-blue-500 focus:outline-none"
                                                placeholder="10.1.xx.xxx"
                                            />
                                        </div>
                                    </div>
                                    <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-4/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="location"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                                >Location</label
                                            >
                                            <input
                                                required
                                                type="text"
                                                v-model="form.location"
                                                name="location"
                                                class="focus:shadow-primary-outline dark:bg-slate-850 dark:text-white text-sm leading-5.6 ease block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-blue-500 focus:outline-none"
                                                placeholder="Main Office"
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
                                        :href="route('inspeksiKomputerMip.page')"
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
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayoutForm>
</template>
