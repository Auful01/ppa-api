<script setup>
import AuthenticatedLayoutForm from "@/Layouts/AuthenticatedLayoutForm.vue";
import { Link, router } from "@inertiajs/vue3";
import { Head, useForm, usePage } from "@inertiajs/vue3";
import VueMultiselect from "vue-multiselect";
import VueDatePicker from "@vuepic/vue-datepicker";
import "@vuepic/vue-datepicker/dist/main.css";
import Swal from "sweetalert2";
import { Inertia } from "@inertiajs/inertia";
import { ref, computed, nextTick, watch } from "vue";

const props = defineProps({
    pengguna: {
        type: Array,
    },
    dept: {
        type: Object,
    },
    department: {
        type: Array,
    },
});

const form = useForm({
    laptop_name: "",
    laptop_code: props.inventoryNumber,
    number_asset_ho: "",
    assets_category: "",
    model: "",
    processor: "",
    hdd: "",
    ssd: "",
    ram: "",
    vga: "",
    warna_laptop: "",
    os_laptop: "",
    serial_number: "",
    aplikasi: "",
    license: "",
    ip_address: "",
    date_of_inventory: "",
    date_of_deploy: "",
    location: "",
    status: "",
    condition: "",
    note: "",
    link_documentation_asset_image: "",
    user_alls_id: "",
    dept: "",
});

const page = usePage();
const optionsDept = props.department;
const selectedOptionDept = ref(null);

watch(selectedOptionDept, async (newVal) => {
    if (!newVal) return;

    await nextTick(); // Menunggu Vue memperbarui DOM

    router.post(
        route("laptopMlp.generate"),
        { department: newVal },
        {
            preserveState: true,
            replace: true,
            onSuccess: (page) => {
                form.laptop_code = page.props.laptop_code;
                form.dept = page.props.dept;
            },
        }
    );
});

const isDisabled = ref(true);
const isDisabled_asetnoho = ref(false);
const file = ref(null);

const handleFileUpload = (event) => {
    file.value = event.target.files[0];
};

const formSubmitted = ref(false);

const selectedDateInv = ref(null);
const selectedDateDeploy = ref(null);

const customFormat = (date) => {
    if (!date) {
        // Jika date null atau kosong, kembalikan null
        return "";
    }

    const d = new Date(date);
    const year = d.getFullYear();
    const month = String(d.getMonth() + 1).padStart(2, "0");
    const day = String(d.getDate()).padStart(2, "0");
    const hours = String(d.getHours()).padStart(2, "0");
    const minutes = String(d.getMinutes()).padStart(2, "0");
    const seconds = String(d.getSeconds()).padStart(2, "0");

    return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
};

const selectedValues = ref(null);
const selected = "";

const save = () => {
    if (selectedValues.value == null) {
        formSubmitted.value = true;
        return; // Stop execution if validation fails
    }

    // console.log(selectedValues.value.name);
    // return

    const formData = new FormData();

    const fixTanggalInv = customFormat(selectedDateInv.value);
    const fixTanggalDeploy = customFormat(selectedDateDeploy.value);

    formData.append("image", file.value);
    formData.append("laptop_name", form.laptop_name);
    formData.append("laptop_code", form.laptop_code);
    formData.append("number_asset_ho", form.number_asset_ho);
    formData.append("assets_category", form.assets_category);
    formData.append("model", form.model);
    formData.append("processor", form.processor);
    formData.append("hdd", form.hdd);
    formData.append("ssd", form.ssd);
    formData.append("ram", form.ram);
    formData.append("vga", form.vga);
    formData.append("warna_laptop", form.warna_laptop);
    formData.append("os_laptop", form.os_laptop);
    formData.append("serial_number", form.serial_number);
    formData.append("aplikasi", form.aplikasi);
    formData.append("license", form.license);
    formData.append("ip_address", form.ip_address);
    formData.append("date_of_inventory", fixTanggalInv);
    formData.append("date_of_deploy", fixTanggalDeploy);
    formData.append("location", form.location);
    formData.append("status", form.status);
    formData.append("condition", form.condition);
    formData.append("note", form.note);
    formData.append("dept", form.dept);
    formData.append(
        "link_documentation_asset_image",
        form.link_documentation_asset_image
    );
    formData.append("user_alls_id", selectedValues.value.name);
    Inertia.post(route("laptopMlp.store"), formData, {
        forceFormData: true,
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

function handleCategoryChange(event) {
    form.category_name = event.target.value;
}

const options = props.pengguna;

const onInput = (data, some) => {
    form.assets_category = data;

    if (data == "NON_STANDART") {
        isDisabled_asetnoho.value = true;
        form.number_asset_ho = "unidentified";
    } else {
        isDisabled_asetnoho.value = false;
        form.number_asset_ho = null;
    }

    console.log(data);
};
</script>

<template>
    <Head title="Tambah data Laptop" />

    <AuthenticatedLayoutForm>
        <template #header>
            <nav>
                <!-- breadcrumb -->
                <ol
                    class="flex flex-wrap pt-1 mr-12 bg-transparent rounded-lg sm:mr-16"
                >
                    <li class="text-sm leading-normal">
                        <a class="text-white opacity-50">Pages {{ form.assets_category }} </a>
                    </li>
                    <Link
                        :href="route('laptopMlp.page')"
                        class="text-sm pl-2 capitalize leading-normal text-white before:float-left before:pr-2 before:text-white before:content-['/']"
                        aria-current="page"
                    >
                        Laptop
                    </Link>
                </ol>
                <h6 class="mb-0 font-bold text-white capitalize">
                    Laptop Create Pages
                </h6>
            </nav>
        </template>

        <div class="w-full p-6 mx-auto">
            <div class="flex flex-wrap -mx-3 justify-center">
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
                                   Form Input Data Laptop
                                </h4>
                            </div>
                        </div>
                        <div class="flex-auto p-6">
                            <form @submit.prevent="save">
                                <hr
                                    class="h-px mx-0 my-4 bg-transparent border-0 opacity-25 bg-gradient-to-r from-transparent via-black/40 to-transparent dark:bg-gradient-to-r dark:from-transparent dark:via-white dark:to-transparent"
                                />
                                <div class="flex flex-wrap -mx-3">
                                    <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-6/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="assets-category"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                                >Department</label
                                            >
                                            <VueMultiselect
                                                v-model="selectedOptionDept"
                                                :options="optionsDept"
                                                :multiple="false"
                                                :close-on-select="true"
                                                placeholder="Select Department"
                                                track-by="name"
                                                label="name"
                                            />
                                        </div>
                                    </div>
                                    <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-6/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="laptop-code"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                                >Laptop Code</label
                                            >
                                            <input
                                                type="hidden"
                                                name="roterx"
                                                value="create"
                                            />
                                            <input
                                                type="hidden"
                                                name="roterx"
                                                v-model="form.dept"
                                            />
                                            <input
                                                :disabled="isDisabled"
                                                required
                                                type="text"
                                                name="laptop_code"
                                                v-model="form.laptop_code"
                                                class="mb-5 bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 cursor-not-allowed dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-gray-400 dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                                placeholder="Auto Generate Laptop Code"
                                            />
                                        </div>
                                    </div>

                                    <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-6/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="device-name"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                                >Brand Laptop</label
                                            >
                                            <input
                                                required
                                                type="text"
                                                name="laptop_name"
                                                v-model="form.laptop_name"
                                                class="focus:shadow-primary-outline dark:bg-slate-850 dark:text-white text-sm leading-5.6 ease block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-blue-500 focus:outline-none"
                                                placeholder="Lenovo"
                                            />
                                        </div>
                                    </div>

                                    <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-6/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="assets-category"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                                >Kategori Aset</label
                                            >
                                            <select
                                                required
                                                id="assets_category"
                                                v-model="form.assets_category"
                                                name="assets_category"
                                                @update:model-value="onInput"
                                                class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                            >
                                                <option
                                                    selected
                                                    value="STANDART"
                                                >
                                                    STANDART
                                                </option>
                                                <option value="NON_STANDART">
                                                    NON_STANDART
                                                </option>
                                            </select>
                                        </div>
                                    </div>

                                    <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-6/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="model"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                                >Model</label
                                            >
                                            <input
                                                required
                                                type="text"
                                                v-model="form.model"
                                                name="model"
                                                class="focus:shadow-primary-outline dark:bg-slate-850 dark:text-white text-sm leading-5.6 ease block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-blue-500 focus:outline-none"
                                                placeholder="Legixx 12"
                                            />
                                        </div>
                                    </div>

                                    <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-6/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="number-asset-ho"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                                >Asset Ho Number</label
                                            >
                                            <input
                                                required
                                                type="text"
                                                v-model="form.number_asset_ho"
                                                name="number_asset_ho"
                                                v-bind:class="{
                                                    'mb-5 bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 cursor-not-allowed dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-gray-400 dark:focus:ring-blue-500 dark:focus:border-blue-500':
                                                        form.assets_category ==
                                                        'NON_STANDART',
                                                    'focus:shadow-primary-outline dark:bg-slate-850 dark:text-white text-sm leading-5.6 ease block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-blue-500 focus:outline-none':
                                                        form.assets_category !=
                                                        'NON_STANDART',
                                                }"
                                                :disabled="isDisabled_asetnoho"
                                                placeholder="10700xxx"
                                            />
                                        </div>
                                    </div>

                                    <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-6/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="processor"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                                >Processor</label
                                            >
                                            <input
                                                required
                                                type="text"
                                                v-model="form.processor"
                                                name="processor"
                                                class="focus:shadow-primary-outline dark:bg-slate-850 dark:text-white text-sm leading-5.6 ease block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-blue-500 focus:outline-none"
                                                placeholder="Intel Core I7-1xxx0HX"
                                            />
                                        </div>
                                    </div>
                                    <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-6/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="hdd"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                                >Hdd</label
                                            >
                                            <input
                                                type="text"
                                                v-model="form.hdd"
                                                name="hdd"
                                                class="focus:shadow-primary-outline dark:bg-slate-850 dark:text-white text-sm leading-5.6 ease block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-blue-500 focus:outline-none"
                                                placeholder="1 xx"
                                            />
                                        </div>
                                    </div>
                                    <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-6/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="ssd"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                                >Ssd</label
                                            >
                                            <input
                                                type="text"
                                                v-model="form.ssd"
                                                name="ssd"
                                                class="focus:shadow-primary-outline dark:bg-slate-850 dark:text-white text-sm leading-5.6 ease block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-blue-500 focus:outline-none"
                                                placeholder="1 xx"
                                            />
                                        </div>
                                    </div>
                                    <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-6/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="ram"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                                >Ram</label
                                            >
                                            <input
                                                required
                                                type="text"
                                                v-model="form.ram"
                                                name="ram"
                                                class="focus:shadow-primary-outline dark:bg-slate-850 dark:text-white text-sm leading-5.6 ease block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-blue-500 focus:outline-none"
                                                placeholder="16 xx"
                                            />
                                        </div>
                                    </div>
                                    <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-6/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="vga"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                                >Vga</label
                                            >
                                            <input
                                                required
                                                type="text"
                                                v-model="form.vga"
                                                name="vga"
                                                class="focus:shadow-primary-outline dark:bg-slate-850 dark:text-white text-sm leading-5.6 ease block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-blue-500 focus:outline-none"
                                                placeholder="NVIDIA GEFORCE RTX 4Xxx"
                                            />
                                        </div>
                                    </div>
                                    <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-6/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="warna_laptop"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                                >Warna Laptop</label
                                            >
                                            <input
                                                required
                                                type="text"
                                                v-model="form.warna_laptop"
                                                name="warna_laptop"
                                                class="focus:shadow-primary-outline dark:bg-slate-850 dark:text-white text-sm leading-5.6 ease block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-blue-500 focus:outline-none"
                                                placeholder="Hitxx"
                                            />
                                        </div>
                                    </div>
                                    <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-6/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="os_laptop"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                                >Os Laptop</label
                                            >
                                            <input
                                                required
                                                type="text"
                                                v-model="form.os_laptop"
                                                name="os_laptop"
                                                class="focus:shadow-primary-outline dark:bg-slate-850 dark:text-white text-sm leading-5.6 ease block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-blue-500 focus:outline-none"
                                                placeholder="WINDOWS 1x PRO"
                                            />
                                        </div>
                                    </div>
                                    <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-6/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="serial-number"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                                >Serial Number</label
                                            >
                                            <input
                                                required
                                                type="text"
                                                v-model="form.serial_number"
                                                name="serial_number"
                                                class="focus:shadow-primary-outline dark:bg-slate-850 dark:text-white text-sm leading-5.6 ease block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-blue-500 focus:outline-none"
                                                placeholder="423424242xxx"
                                            />
                                        </div>
                                    </div>
                                    <!-- <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-6/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="aplikasi"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                                >Aplikasi</label
                                            >
                                            <input
                                                required
                                                type="text"
                                                v-model="form.aplikasi"
                                                name="aplikasi"
                                                class="focus:shadow-primary-outline dark:bg-slate-850 dark:text-white text-sm leading-5.6 ease block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-blue-500 focus:outline-none"
                                                placeholder="Standart Progxx"
                                            />
                                        </div>
                                    </div> -->
                                    <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-6/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="license"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                                >License</label
                                            >
                                            <input
                                                required
                                                type="text"
                                                v-model="form.license"
                                                name="license"
                                                class="focus:shadow-primary-outline dark:bg-slate-850 dark:text-white text-sm leading-5.6 ease block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-blue-500 focus:outline-none"
                                                placeholder="Office xx"
                                            />
                                        </div>
                                    </div>

                                    <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-6/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="ip-address"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                                >Ip Address</label
                                            >
                                            <input
                                                type="text"
                                                v-model="form.ip_address"
                                                name="ip_address"
                                                class="focus:shadow-primary-outline dark:bg-slate-850 dark:text-white text-sm leading-5.6 ease block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-blue-500 focus:outline-none"
                                                placeholder="10.1.x.xx"
                                            />
                                        </div>
                                    </div>
                                    <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-6/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="date-of-inventory"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                                >Date Of Inventory</label
                                            >
                                            <VueDatePicker
                                                required
                                                v-model="selectedDateInv"
                                                :format="customFormat"
                                                name="date_of_inventory"
                                                placeholder="Select a date and time"
                                            />
                                        </div>
                                    </div>
                                    <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-6/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="date-of-deploy"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                                >Date Of Deploy</label
                                            >
                                            <VueDatePicker
                                                v-model="selectedDateDeploy"
                                                :format="customFormat"
                                                name="date_of_deploy"
                                                placeholder="Select a date and time"
                                            />
                                        </div>
                                    </div>
                                    <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-6/12 md:flex-0"
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
                                                placeholder="Markas ICT"
                                            />
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
                                                Status</label
                                            >
                                            <select
                                                required
                                                id="status"
                                                v-model="form.status"
                                                name="status"
                                                class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                            >
                                                <option value="READY_USED">
                                                    Ready Used
                                                </option>
                                                <option value="READY_STANdBY">
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
                                        class="w-full max-w-full px-3 shrink-0 md:w-6/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="condition"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                                >Condition</label
                                            >
                                            <input
                                                required
                                                type="text"
                                                v-model="form.condition"
                                                name="condition"
                                                class="focus:shadow-primary-outline dark:bg-slate-850 dark:text-white text-sm leading-5.6 ease block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-blue-500 focus:outline-none"
                                                placeholder="BAGUS / RUSAK"
                                            />
                                        </div>
                                    </div>
                                    <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-6/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="link_documentation_asset_image"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                                >Documentation Assets</label
                                            >
                                            <input
                                                required
                                                type="file"
                                                ref="fileInput"
                                                class="focus:shadow-primary-outline dark:bg-slate-850 dark:text-white text-sm leading-5.6 ease block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-blue-500 focus:outline-none"
                                                placeholder="2.4 / 5.8 Ghz"
                                                @change="handleFileUpload"
                                            />
                                        </div>
                                    </div>
                                    <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-6/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="user_id"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                            >
                                                Select User</label
                                            >
                                            <VueMultiselect
                                                v-model="selectedValues"
                                                :options="options"
                                                :multiple="false"
                                                :close-on-select="true"
                                                placeholder="pick name of user"
                                                track-by="name"
                                                label="name"
                                            />
                                        </div>
                                        <span
                                            v-if="
                                                !selectedOption && formSubmitted
                                            "
                                            class="text-red-500"
                                            >Pengguna Tidak boleh kosong!</span
                                        >
                                    </div>
                                    <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-6/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="device-location"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                                >Note</label
                                            >
                                            <textarea
                                                id="message"
                                                name="note"
                                                v-model="form.note"
                                                rows="4"
                                                class="block p-2.5 w-full text-sm text-gray-900 bg-white rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                                placeholder="Leave a note..."
                                            ></textarea>
                                        </div>
                                    </div>
                                </div>
                                <hr
                                    class="h-px mx-0 my-4 bg-transparent border-0 opacity-25 bg-gradient-to-r from-transparent via-black/40 to-transparent dark:bg-gradient-to-r dark:from-transparent dark:via-white dark:to-transparent"
                                />
                                <div class="flex flex-nowrap mt-6 justify-between">
                                    
                                    <Link
                                        :href="route('laptopMlp.page')"
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
