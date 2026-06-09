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
    laptop: {
        type: Array,
    },
});

const form = useForm({
    id: props.dataInspeksi.id,
    software_standaritation_software:
        props.dataInspeksi.software_standaritation_software,
    software_defrag: props.dataInspeksi.software_defrag,
    software_check_system_restore:
        props.dataInspeksi.software_check_system_restore,
    software_clean_cache_data: props.dataInspeksi.software_clean_cache_data,
    software_windows_license: props.dataInspeksi.software_windows_license,
    software_office_license: props.dataInspeksi.software_office_license,
    software_turn_off_windows_update:
        props.dataInspeksi.software_turn_off_windows_update,
    software_cheking_ssd_health: props.dataInspeksi.software_cheking_ssd_health,
    software_standaritation_device_name:
        props.dataInspeksi.software_standaritation_device_name,
    hardware_fan_cleaning: props.dataInspeksi.hardware_fan_cleaning,
    hardware_change_pasta: props.dataInspeksi.hardware_change_pasta,
    hardware_any_maintenance: props.dataInspeksi.hardware_any_maintenance,
    security_change_password: props.dataInspeksi.security_change_password,
    security_auto_lock: props.dataInspeksi.security_auto_lock,
    security_input_password: props.dataInspeksi.security_input_password,
    ssd_persen: props.dataInspeksi.software_percentage_ssd_health,
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
});

const isDisabled = ref(true);
const file_temuan = ref(null);
const file_tindakan = ref(null);
const file_inspeksi = ref(null);

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

onMounted(() => {
    if (form.software_defrag === 'Y' || form.software_defrag === 'N') {
        storageTipe.value = 'HDD';
    } else {
        storageTipe.value = 'SSD';
    }
});

const storageTipe = ref("");
watch(() => form.storageTipe, (newVal) => {
    if (newVal === 'SSD') {
        form.software_defrag = '';
    }
});

watch(() => form.software_defrag, (newVal) => {
    if (newVal === 'Y' || newVal === 'N') {
        form.storageTipe = 'HDD';
    }
});

const formSubmittedPIC = ref(false);

const selectedDateDueDate = ref(props.dataInspeksi.due_date);

const options = props.pengguna;

const selectedValuesPIC = ref(
    props.pengguna.filter((option) =>
        props.pengguna_selected.includes(option.name)
    )
);

const penggunaString = computed(() => {
    return selectedValuesPIC.value.map((option) => option.name).join("");
});

const update = () => {
    if (selectedValuesPIC.value == "") {
        formSubmittedPIC.value = true;
        // console.log('ga oke')
        return; // Stop execution if validation fails
    }

    if (selectedDateDueDate.value != null) {
        form.due_date = customFormat(selectedDateDueDate.value);
    }

    form.id = props.dataInspeksi.id;

    form.image_temuan = file_temuan.value;
    form.image_tindakan = file_tindakan.value;
    form.inspector = penggunaString.value;
    form.image_inspeksi = file_inspeksi.value;
    form.inspection_status = "Y";

    form.post(route("inspeksiLaptopPik.update"), {
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
                        :href="route('inspeksiLaptopPik.page')"
                        class="text-sm pl-2 capitalize leading-normal text-white before:float-left before:pr-2 before:text-white before:content-['/']"
                        aria-current="page"
                    >
                        Inspeksi Laptop
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
                                                    : {{ laptop.laptop_code }}
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
                                                    {{ laptop.number_asset_ho }}
                                                </p>
                                            </div>
                                        </div>
                                        <div class="grid grid-cols-2">
                                            <div>
                                                <p class="text-base">
                                                    Pengguna
                                                </p>
                                            </div>
                                            <div>
                                                <p>
                                                    :
                                                    {{
                                                        laptop.pengguna.username
                                                    }}
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
                                                    {{
                                                        laptop.pengguna
                                                            .department
                                                    }}
                                                </p>
                                            </div>
                                        </div>
                                        <div class="grid grid-cols-2">
                                            <div>
                                                <p class="text-base">
                                                    Laptop Name
                                                </p>
                                            </div>
                                            <div>
                                                <p>
                                                    : {{ laptop.laptop_name }}
                                                </p>
                                            </div>
                                        </div>
                                        <div class="grid grid-cols-2">
                                            <div>
                                                <p class="text-base">
                                                    Spesifikasi
                                                </p>
                                            </div>
                                            <div>
                                                <p>
                                                    : {{ laptop.spesifikasi }}
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
                                                    : {{ laptop.serial_number }}
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
                                                <p>: {{ laptop.ip_address }}</p>
                                            </div>
                                        </div>
                                        <div class="grid grid-cols-2">
                                            <div>
                                                <p class="text-base">
                                                    Location
                                                </p>
                                            </div>
                                            <div>
                                                <p>: {{ laptop.location }}</p>
                                            </div>
                                        </div>
                                        <div class="grid grid-cols-2">
                                            <div>
                                                <p class="text-base">Status</p>
                                            </div>
                                            <div>
                                                <p>: {{ laptop.status }}</p>
                                            </div>
                                        </div>
                                        <div class="grid grid-cols-2">
                                            <div>
                                                <p class="text-base">
                                                    Condition
                                                </p>
                                            </div>
                                            <div>
                                                <p>: {{ laptop.condition }}</p>
                                            </div>
                                        </div>
                                        <div class="grid grid-cols-2">
                                            <div>
                                                <p class="text-base">Note</p>
                                            </div>
                                            <div>
                                                <p>: {{ laptop.note }}</p>
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
                                    Form Edit Inspeksi Laptop
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

                                    <div
                                        class="border-black/12.5 rounded-t-2xl border-b-0 border-solid p-6 pb-0 w-full max-w-full px-3 shrink-0 md:w-12/12 md:flex-0"
                                    >
                                        <div class="text-center mb-4">
                                            <p
                                                class="mb-0 dark:text-white/80 font-semibold"
                                            >
                                                HARDWARE
                                            </p>
                                        </div>
                                    </div>

                                    <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-4/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="aplikasi"
                                                class="inline-block ml-1 text-sm text-slate-700 dark:text-white/80"
                                                >Melakukan Fan Cleaning</label
                                            >

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
                                                        name="fan"
                                                        v-model="
                                                            form.hardware_fan_cleaning
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
                                                        name="fan"
                                                        v-model="
                                                            form.hardware_fan_cleaning
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
                                        class="w-full max-w-full px-3 shrink-0 md:w-4/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="license"
                                                class="inline-block ml-1 text-sm text-slate-700 dark:text-white/80"
                                                >Melakukan Pergantian
                                                Pasta</label
                                            >

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
                                                        name="pasta"
                                                        v-model="
                                                            form.hardware_change_pasta
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
                                                        name="pasta"
                                                        v-model="
                                                            form.hardware_change_pasta
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
                                        class="w-full max-w-full px-3 shrink-0 md:w-4/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="ip-address"
                                                class="inline-block ml-1 text-sm text-slate-700 dark:text-white/80"
                                                >Melakukan Perbaikan
                                                Lainnya</label
                                            >

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
                                                        name="lainnya"
                                                        v-model="
                                                            form.hardware_any_maintenance
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
                                                        name="lainnya"
                                                        v-model="
                                                            form.hardware_any_maintenance
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
                                            >
                                                SOFTWARE
                                            </p>
                                        </div>
                                    </div>

                                    <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-6/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="ram"
                                                class="inline-block ml-1 text-sm text-slate-700 dark:text-white/80"
                                                >Melakukan Cek Standarisasi
                                                Software</label
                                            >

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
                                                        name="standarisasi"
                                                        v-model="
                                                            form.software_standaritation_software
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
                                                        name="standarisasi"
                                                        v-model="
                                                            form.software_standaritation_software
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
                                        class="w-full max-w-full px-3 shrink-0 md:w-6/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="serial-number"
                                                class="inline-block ml-1 text-sm text-slate-700 dark:text-white/80"
                                                >Melakukan Standarisasi Nama
                                                Device</label
                                            >

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
                                                        name="nama"
                                                        v-model="
                                                            form.software_standaritation_device_name
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
                                                        name="nama"
                                                        v-model="
                                                            form.software_standaritation_device_name
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
                                        class="w-full max-w-full px-3 shrink-0 md:w-6/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="ssd"
                                                class="inline-block ml-1 text-sm text-slate-700 dark:text-white/80"
                                                >Melakukan Cek License
                                                Software</label
                                            >

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
                                                        name="office"
                                                        v-model="
                                                            form.software_office_license
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
                                                        name="office"
                                                        v-model="
                                                            form.software_office_license
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
                                        class="w-full max-w-full px-3 shrink-0 md:w-6/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="assets-category"
                                                class="inline-block ml-1 text-sm text-slate-700 dark:text-white/80"
                                                >Melakukan Clean Temporary &
                                                Cache Data</label
                                            >

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
                                                        name="cache"
                                                        v-model="
                                                            form.software_clean_cache_data
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
                                                        name="cache"
                                                        v-model="
                                                            form.software_clean_cache_data
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
                                        class="w-full max-w-full px-3 shrink-0 md:w-6/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="number-asset-ho"
                                                class="inline-block ml-1 text-sm text-slate-700 dark:text-white/80"
                                                >Melakukan Cek System
                                                Restore</label
                                            >

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
                                                        name="restore"
                                                        v-model="
                                                            form.software_check_system_restore
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
                                                        name="restore"
                                                        v-model="
                                                            form.software_check_system_restore
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
                                        class="w-full max-w-full px-3 shrink-0 md:w-6/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="warna_laptop"
                                                class="inline-block ml-1 text-sm text-slate-700 dark:text-white/80"
                                                >Melakukan Cek Windows
                                                Update</label
                                            >

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
                                                        name="off"
                                                        v-model="
                                                            form.software_turn_off_windows_update
                                                        "
                                                        value="ON"
                                                        class="form-radio text-blue-600 border-gray-300 focus:ring-blue-500"
                                                    />
                                                    <span class="text-gray-700"
                                                        >On</span
                                                    >
                                                </label>

                                                <label
                                                    class="inline-flex items-center space-x-2"
                                                >
                                                    <input
                                                        required
                                                        type="radio"
                                                        name="off"
                                                        v-model="
                                                            form.software_turn_off_windows_update
                                                        "
                                                        value="OFF"
                                                        class="form-radio text-blue-600 border-gray-300 focus:ring-blue-500"
                                                    />
                                                    <span class="text-gray-700"
                                                        >Off</span
                                                    >
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-4/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="device-name"
                                                class="inline-block ml-1 text-sm text-slate-700 dark:text-white/80"
                                                >Storage Tipe</label
                                            >

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
                                                        name="storage"
                                                        v-model="
                                                            storageTipe
                                                        "
                                                        value="SSD"
                                                        class="form-radio text-blue-600 border-gray-300 focus:ring-blue-500"
                                                    />
                                                    <span class="text-gray-700"
                                                        >SSD</span
                                                    >
                                                </label>

                                                <label
                                                    class="inline-flex items-center space-x-2"
                                                >
                                                    <input
                                                        required
                                                        type="radio"
                                                        name="storage"
                                                        v-model="
                                                            storageTipe
                                                        "
                                                        value="HDD"
                                                        class="form-radio text-blue-600 border-gray-300 focus:ring-blue-500"
                                                    />
                                                    <span class="text-gray-700"
                                                        >HDD</span
                                                    >
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <div
                                        v-if="storageTipe != 'HDD'"
                                        class="w-full max-w-full px-3 shrink-0 md:w-2/12 md:flex-0"
                                    >
                                        <div class="mb-4"></div>
                                    </div>

                                    <div
                                        v-if="storageTipe === 'HDD'"
                                        class="w-full max-w-full px-3 shrink-0 md:w-2/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="device-name"
                                                class="inline-block ml-1 text-sm text-slate-700 dark:text-white/80"
                                                >Defrag</label
                                            >

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
                                                        name="defrag"
                                                        v-model="
                                                            form.software_defrag
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
                                                        name="defrag"
                                                        v-model="
                                                            form.software_defrag
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
                                        class="w-full max-w-full px-3 shrink-0 md:w-3/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="ssd_persen"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                                >Percentage SSD/HDD
                                                Health</label
                                            >
                                            <input
                                                type="text"
                                                id="ssd_persen"
                                                v-model="form.ssd_persen"
                                                name="ssd_persen"
                                                class="focus:shadow-primary-outline dark:bg-slate-850 dark:text-white text-sm leading-5.6 ease block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-blue-500 focus:outline-none"
                                                placeholder="80%"
                                            />
                                        </div>
                                    </div>
                                    <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-3/12 md:flex-0"
                                    >
                                        <div class="mb-4"></div>
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
                                        </div>
                                    </div>

                                    <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-4/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="processor"
                                                class="inline-block ml-1 text-sm text-slate-700 dark:text-white/80"
                                                >Melakukan Change
                                                Password</label
                                            >

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
                                                        name="password"
                                                        v-model="
                                                            form.security_change_password
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
                                                        name="password"
                                                        v-model="
                                                            form.security_change_password
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
                                        class="w-full max-w-full px-3 shrink-0 md:w-4/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="hdd"
                                                class="inline-block ml-1 text-sm text-slate-700 dark:text-white/80"
                                                >Setting Auto Lock Screen</label
                                            >

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
                                                        name="autolock"
                                                        v-model="
                                                            form.security_auto_lock
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
                                                        name="autolock"
                                                        v-model="
                                                            form.security_auto_lock
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
                                        class="w-full max-w-full px-3 shrink-0 md:w-4/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="os_laptop"
                                                class="inline-block ml-1 text-sm text-slate-700 dark:text-white/80"
                                                >Setting Input Password After
                                                Lock Screen</label
                                            >

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
                                                        name="inputPassword"
                                                        v-model="
                                                            form.security_input_password
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
                                                        name="inputPassword"
                                                        v-model="
                                                            form.security_input_password
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
                                        class="w-full max-w-full px-3 shrink-0 md:w-4/12 md:flex-0"
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
                                        class="w-full max-w-full px-3 shrink-0 md:w-4/12 md:flex-0"
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
                                        class="w-full max-w-full px-3 shrink-0 md:w-4/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="hardware_any_maintenance_explain"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                                >Isian Tindakan Lainnya</label
                                            >
                                            <input
                                                type="text"
                                                v-model="
                                                    form.hardware_any_maintenance_explain
                                                "
                                                name="hardware_any_maintenance_explain"
                                                class="focus:shadow-primary-outline dark:bg-slate-850 dark:text-white text-sm leading-5.6 ease block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-blue-500 focus:outline-none"
                                                placeholder="Instal Ulang Office"
                                            />
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
                                        class="border-black/12.5 rounded-t-2xl border-b-0 border-solid p-6 pb-0 w-full max-w-full px-3 shrink-0 md:w-12/12 md:flex-0">
                                        <div class="text-center mb-4">
                                            <p class="mb-0 dark:text-white/80 font-semibold">
                                                Temuan
                                            </p>
                                        </div>
                                    </div>

                                    <div class="w-full max-w-full px-3 shrink-0 md:w-5/12 md:flex-0">
                                        <div class="mb-4">
                                            <label for="temuan"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80">Deskripsi
                                                Temuan</label>
                                            <input type="text" v-model="form.temuan" name="temuan"
                                                class="focus:shadow-primary-outline dark:bg-slate-850 dark:text-white text-sm leading-5.6 ease block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-blue-500 focus:outline-none"
                                                placeholder="" />
                                        </div>
                                    </div>

                                    <div class="w-full max-w-full px-3 shrink-0 md:w-3/12 md:flex-0">
                                        <div class="mb-4">
                                            <label for="inspection_image"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80">Lampiran
                                                Foto Temuan</label>
                                            <img :src="dataInspeksi.findings_image
                                                " alt="Lampiran
                                                Foto Temuan" class="w-50 h-30 shadow-2xl rounded-xl" />
                                        </div>
                                    </div>

                                    <div class="w-full max-w-full px-3 shrink-0 md:w-4/12 md:flex-0">
                                        <div class="mb-4">
                                            <label for="findings_image"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80">Update Lampiran
                                                Foto Temuan</label>
                                            <input type="file" ref="fileInput"
                                                class="focus:shadow-primary-outline dark:bg-slate-850 dark:text-white text-sm leading-5.6 ease block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-blue-500 focus:outline-none"
                                                @change="handleFileUploadTemuan" />
                                        </div>
                                    </div>

                                    <div class="w-full max-w-full px-3 shrink-0 md:w-5/12 md:flex-0">
                                        <div class="mb-4">
                                            <label for="tindakan"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80">Tindak
                                                Lanjut</label>
                                            <input type="text" v-model="form.tindakan" name="tindakan"
                                                class="focus:shadow-primary-outline dark:bg-slate-850 dark:text-white text-sm leading-5.6 ease block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-blue-500 focus:outline-none"
                                                placeholder="" />
                                        </div>
                                    </div>

                                    <div class="w-full max-w-full px-3 shrink-0 md:w-3/12 md:flex-0">
                                        <div class="mb-4">
                                            <label for="inspection_image"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80">Lampiran
                                                Foto Tindakan</label>
                                            <img :src="dataInspeksi.action_image
                                                " alt="Lampiran
                                                Foto Tindakan" class="w-50 h-30 shadow-2xl rounded-xl" />
                                        </div>
                                    </div>

                                    <div class="w-full max-w-full px-3 shrink-0 md:w-4/12 md:flex-0">
                                        <div class="mb-4">
                                            <label for="action_image"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80">Update Lampiran
                                                Foto Tindakan</label>
                                            <input type="file" ref="fileInput"
                                                class="focus:shadow-primary-outline dark:bg-slate-850 dark:text-white text-sm leading-5.6 ease block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-blue-500 focus:outline-none"
                                                @change="handleFileUploadTindakan" />
                                        </div>
                                    </div>

                                    <div class="w-full max-w-full px-3 shrink-0 md:w-6/12 md:flex-0">
                                        <div class="mb-4">
                                            <label for="date-of-inventory"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80">Due
                                                Date</label>
                                            <VueDatePicker v-model="selectedDateDueDate" :format="customFormat"
                                                name="date_of_inventory" placeholder="Select a date and time" />
                                        </div>
                                    </div>


                                    <div class="w-full max-w-full px-3 shrink-0 md:w-6/12 md:flex-0">
                                        <div class="mb-4">
                                            <label for="findings_status"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80">
                                                Status Temuan</label>
                                            <select id="findings_status" v-model="form.findings_status"
                                                name="findings_status"
                                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                                                <option value="OPEN">
                                                    OPEN
                                                </option>
                                                <option value="CLOSE">
                                                    CLOSE
                                                </option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="w-full max-w-full px-3 shrink-0 md:w-12/12 md:flex-0">
                                        <div class="mb-4">
                                            <label for="device-location"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80">Remark</label>
                                            <textarea id="message" name="remark" v-model="form.remark" rows="4"
                                                class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                                placeholder="Leave a note..."></textarea>
                                        </div>
                                    </div>

                                    <div class="w-full max-w-full px-3 shrink-0 md:w-5/12 md:flex-0">
                                        <div class="mb-4">
                                            <label for="select_pic"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80">
                                                Select PIC</label>
                                            <VueMultiselect v-model="selectedValuesPIC" :options="options"
                                                :multiple="true" :max="1" :close-on-select="true"
                                                placeholder="Pilih PIC" track-by="name" label="name" />
                                        </div>
                                        <span v-if="
                                            !selectedOption && formSubmittedPIC
                                        " class="text-red-500">PIC Tidak boleh kosong!</span>
                                    </div>

                                    <div class="w-full max-w-full px-3 shrink-0 md:w-3/12 md:flex-0">
                                        <div class="mb-4">
                                            <label for="inspection_image"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80">Lampiran
                                                Foto Inspeksi</label>
                                            <img :src="dataInspeksi.inspection_image
                                                " alt="Lampiran
                                                Foto Inspeksi" class="w-50 h-30 shadow-2xl rounded-xl" />
                                        </div>
                                    </div>

                                    <div class="w-full max-w-full px-3 shrink-0 md:w-4/12 md:flex-0">
                                        <div class="mb-4">
                                            <label for="inspection_image"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80">Update Lampiran
                                                Foto Inspeksi</label>
                                            <input type="file" ref="fileInput"
                                                class="focus:shadow-primary-outline dark:bg-slate-850 dark:text-white text-sm leading-5.6 ease block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-blue-500 focus:outline-none"
                                                @change="handleFileUploadInspeksi" />
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
                                        :href="route('inspeksiLaptopPik.page')"
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
