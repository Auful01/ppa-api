<!-- <style src="vue-multiselect/dist/vue-multiselect.css"></style> -->
<script setup>
import AuthenticatedLayoutForm from "@/Layouts/AuthenticatedLayoutForm.vue";
import { Link } from "@inertiajs/vue3";
import { Head, useForm } from "@inertiajs/vue3";
import VueDatePicker from "@vuepic/vue-datepicker";
import "@vuepic/vue-datepicker/dist/main.css";
import VueMultiselect from "vue-multiselect";
import Swal from "sweetalert2";
import { Inertia } from "@inertiajs/inertia";
import { ref, computed } from "vue";

const props = defineProps([
    "komputer",
    "model",
    "processor",
    "hdd",
    "ssd",
    "ram",
    "ram",
    "vga",
    "warna_komputer",
    "os_komputer",
    "pengguna_all",
    "pengguna_selected"
]);

const form = useForm({
    id: props.komputer.id,
    max_id: props.komputer.max_id,
    computer_name: props.komputer.computer_name,
    computer_code: props.komputer.computer_code,
    number_asset_ho: props.komputer.number_asset_ho,
    assets_category: props.komputer.assets_category,
    model: props.model,
    processor: props.processor,
    hdd: props.hdd,
    ssd: props.ssd,
    ram: props.ram,
    vga: props.vga,
    warna_komputer: props.warna_komputer,
    os_komputer: props.os_komputer,
    serial_number: props.komputer.serial_number,
    aplikasi: props.komputer.aplikasi,
    license: props.komputer.license,
    ip_address: props.komputer.ip_address,
    date_of_inventory: props.komputer.date_of_inventory,
    date_of_deploy: props.komputer.date_of_deploy,
    location: props.komputer.location,
    status: props.komputer.status,
    condition: props.komputer.condition,
    note: props.komputer.note,
    link_documentation_asset_image: props.komputer.link_documentation_asset_image,
    user_alls_id: props.pengguna_all,
});

const isDisabled = ref(true);
const file = ref(null);

const selectedDateInv = ref(props.komputer.date_of_inventory);
const selectedDateDeploy = ref(props.komputer.date_of_deploy);

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

const handleFileUpload = (event) => {
    file.value = event.target.files[0];
};

const formSubmitted = ref(false);

const options = props.pengguna_all;

const selectedValues = ref(
    props.pengguna_all.filter((option) => props.pengguna_selected.includes(option.nrp))
);

const penggunaString = computed(() => {
    return selectedValues.value.map((option) => option.nrp).join("");
});

const update = () => {
    const formData = new FormData();

    if (selectedValues.value == null) {
        formSubmitted.value = true;
        return; 
    }else{
        if(props.pengguna_selected.includes('data tidak ada !')){
            formData.append("user_alls_id", selectedValues.value.nrp);
        }else{

            if(props.pengguna_selected.includes(selectedValues.value.nrp) == false && selectedValues.value.nrp != undefined) {
                formData.append("user_alls_id", selectedValues.value.nrp);
            }else{
                formData.append("user_alls_id", penggunaString.value);
            }

        }
    }

    const fixTanggalInv = customFormat(selectedDateInv.value);
    const fixTanggalDeploy = customFormat(selectedDateDeploy.value);

    formData.append("id", form.id);
    formData.append("max_id", form.max_id);
    formData.append("computer_name", form.computer_name);
    formData.append("computer_code", form.computer_code);
    formData.append("number_asset_ho", form.number_asset_ho);
    formData.append("assets_category", form.assets_category);
    formData.append("model", form.model);
    formData.append("processor", form.processor);
    formData.append("hdd", form.hdd);
    formData.append("ssd", form.ssd);
    formData.append("ram", form.ram);
    formData.append("vga", form.vga);
    formData.append("warna_komputer", form.warna_komputer);
    formData.append("os_komputer", form.os_komputer);
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

    if (file.value) {
        formData.append("image", file.value); // Append the file
    }

    Inertia.post(route("komputerBge.update", props.komputer.id), formData, {
        // Use route name here
        onProgress: (progress) => {
            console.log(formData.append); // Track the upload progress
        },
    });
};
</script>

<template>
    <Head title="Edit data Komputer" />

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
                        :href="route('komputerBge.page')"
                        class="text-sm pl-2 capitalize leading-normal text-white before:float-left before:pr-2 before:text-white before:content-['/']"
                        aria-current="page"
                    >
                        Komputer
                    </Link>
                </ol>
                <h6 class="mb-0 font-bold text-white capitalize">
                    Komputer Edit Pages
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
                                <p class="mb-0 font-bold dark:text-white/80">
                                    Form Edit Komputer
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
                                        class="w-full max-w-full px-3 shrink-0 md:w-6/12 md:flex-0"
                                    >
                                    <div class="mb-4">
                                            <label
                                                for="komputer-code"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                                >Komputer Code</label
                                            >
                                            <input
                                                :disabled="isDisabled"
                                                required
                                                type="text"
                                                name="computer_code"
                                                v-model="form.computer_code"
                                                value="1"
                                                class="mb-5 bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 cursor-not-allowed dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-gray-400 dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                                placeholder="Auto Generate Komputer Code"
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
                                                >Brand Komputer</label
                                            >
                                            <input
                                                required
                                                type="text"
                                                name="computer_name"
                                                v-model="form.computer_name"
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
                                                class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                            >
                                                <option selected value="STANDART">
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
                                                :disabled="isDisabled"
                                                type="text"
                                                v-model="form.number_asset_ho"
                                                name="number_asset_ho"
                                                class="mb-5 bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 cursor-not-allowed dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-gray-400 dark:focus:ring-blue-500 dark:focus:border-blue-500"
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
                                                for="warna_komputer"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                                >Warna Komputer</label
                                            >
                                            <input
                                                required
                                                type="text"
                                                v-model="form.warna_komputer"
                                                name="warna_komputer"
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
                                                for="os_komputer"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                                >Os Komputer</label
                                            >
                                            <input
                                                required
                                                type="text"
                                                v-model="form.os_komputer"
                                                name="os_komputer"
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
                                    <div
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
                                                placeholder="STANDART Progxx"
                                            />
                                        </div>
                                    </div>
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
                                                placeholder="Bagus / Rusak"
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
                                                name="image"
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
                                                track-by="nrp"
                                                label="label"
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
                                                for="link_documentation_asset_image"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                                >Previous Images Documentation
                                                Assets</label
                                            >
                                            <img
                                                :src="
                                                    form.link_documentation_asset_image
                                                "
                                                alt="documentation image"
                                                class="max-w-48 max-h-64 shadow-2xl rounded-xl"
                                            />
                                        </div>
                                    </div>
                                    <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-12/12 md:flex-0"
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
                                        :href="route('komputerBge.page')"
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
