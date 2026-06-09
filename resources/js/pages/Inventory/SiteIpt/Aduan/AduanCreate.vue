<style src="vue-multiselect/dist/vue-multiselect.css"></style>
<script setup>
import AuthenticatedLayoutForm from "@/Layouts/AuthenticatedLayoutForm.vue";
import { Link } from "@inertiajs/vue3";
import { Head, useForm } from "@inertiajs/vue3";
import VueMultiselect from "vue-multiselect";
import VueDatePicker from "@vuepic/vue-datepicker";
import "@vuepic/vue-datepicker/dist/main.css";
import Swal from "sweetalert2";
import { Inertia } from "@inertiajs/inertia";
import { ref, computed } from "vue";

const props = defineProps({
    crew: {
        type: Array,
    },
    categories: {
        type: Array,
    },
    ticket: {
        type: Object,
    },
});

const form = useForm({
    complaint_name: "",
    complaint_code: props.ticket,
    nrp: "",
    phone_number: "",
    inventory_number: "",
    category_name: "",
    crew: "",
    location: "",
    complaint_note: "",
    location_detail: "",
});

const isDisabled = ref(true);
const file = ref(null);

const handleFileUpload = (event) => {
    file.value = event.target.files[0];
};

const selectedValues = ref([]); // Awalnya array kosong
const crewString = computed(() => {
    return selectedValues.value.map((option) => option.name).join(", ");
});

const formSubmitted = ref(false);

const selectedDate = ref(null);

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

const save = () => {
    if (selectedValues.value == "") {
        formSubmitted.value = true;
        console.log("ga oke");
        return; // Stop execution if validation fails
    }
    const formData = new FormData();
    const formattedDate = customFormat(selectedDate.value);
    formData.append("image", file.value);
    formData.append("complaint_name", form.complaint_name);
    formData.append("complaint_code", form.complaint_code);
    formData.append("nrp", form.nrp);
    formData.append("phone_number", form.phone_number);
    formData.append("inventory_number", form.inventory_number);
    formData.append("category_name", form.category_name);
    formData.append("crew", crewString.value);
    formData.append("date_of_complaint", formattedDate);
    formData.append("location", form.location);
    formData.append("complaint_note", form.complaint_note);
    formData.append("location_detail", form.location_detail);
    Inertia.post(route("aduanIpt.store"), formData, {
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

const options = props.crew;

// Computed property untuk mendapatkan placeholder
const getPlaceholder = computed(() => {
    if (form.category_name === "PC/NB") {
        return "Masukkan No Inventory PC/NoteBook";
    }
});

const showAlertTrue = () => {
    Swal.fire({
        title: 'Tabel Role Akses User!',
        html: '<img src="/step_no_inv.jpg" class="inline transition-all duration-200 ease-nav-brand max-h-60 mr-2" alt="main_logo" />',
    });
}
</script>

<template>
    <Head title="Dashboard" />

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
                        :href="route('aduanIpt.page')"
                        class="text-sm pl-2 capitalize leading-normal text-white before:float-left before:pr-2 before:text-white before:content-['/']"
                        aria-current="page"
                    >
                        Aduan
                    </Link>
                </ol>
                <h6 class="mb-0 font-bold text-white capitalize">
                    Aduan Create Pages
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
                                    Form Create Aduan
                                </p>
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
                                                for="complaint-name"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                                >Complaint Name</label
                                            >
                                            <input
                                                required
                                                type="text"
                                                name="complaint_name"
                                                v-model="form.complaint_name"
                                                class="focus:shadow-primary-outline dark:bg-slate-850 dark:text-white text-sm leading-5.6 ease block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-blue-500 focus:outline-none"
                                                placeholder="Nama Pelapor"
                                            />
                                        </div>
                                    </div>
                                    <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-6/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="ticket-code"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                                >Ticket Code</label
                                            >
                                            <input
                                                :disabled="isDisabled"
                                                required
                                                type="text"
                                                name="complaint_code"
                                                v-model="form.complaint_code"
                                                class="mb-5 bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 cursor-not-allowed dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-gray-400 dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                                placeholder="Auto Generate Ticket Code"
                                            />
                                        </div>
                                    </div>

                                    <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-6/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="nrp"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                                >NRP</label
                                            >
                                            <input
                                                required
                                                type="text"
                                                v-model="form.nrp"
                                                name="nrp"
                                                class="focus:shadow-primary-outline dark:bg-slate-850 dark:text-white text-sm leading-5.6 ease block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-blue-500 focus:outline-none"
                                                placeholder="NRP Pelapor"
                                            />
                                        </div>
                                    </div>

                                    <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-6/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="phone-number"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                                >Active WhatsApp Number</label
                                            >
                                            <input
                                                required
                                                type="text"
                                                name="phone_number"
                                                v-model="form.phone_number"
                                                class="focus:shadow-primary-outline dark:bg-slate-850 dark:text-white text-sm leading-5.6 ease block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-blue-500 focus:outline-none"
                                                placeholder="Nomer Hp"
                                            />
                                        </div>
                                    </div>

                                    <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-6/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="nvr-id"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                            >
                                                Category Aduan</label
                                            >
                                            <select
                                                @change="handleCategoryChange"
                                                required
                                                id="category_name"
                                                v-model="form.category_name"
                                                name="category_name"
                                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                            >
                                                <option
                                                    selected
                                                    value=""
                                                >
                                                    SELECT CATEGORY
                                                </option>
                                                <option
                                                    v-for="category in categories"
                                                    :key="category.id"
                                                    :value="category.category_root_cause"
                                                >
                                                    {{ category.category_root_cause }}
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                    <div
                                        v-if="
                                            form.category_name == 'PC/NB'
                                        "
                                        class="w-full max-w-full px-3 shrink-0 md:w-3/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="inventory-number"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                                >Inventory Number</label
                                            >
                                            <a style="cursor: pointer;" class="icon-button" @click="showAlertTrue">
                                                <i class="ms-3 mt-1 text-red-700 fas fa-question-circle"></i>
                                            </a>
                                            <input
                                                required
                                                type="text"
                                                name="inventory_number"
                                                v-model="form.inventory_number"
                                                class="focus:shadow-primary-outline dark:bg-slate-850 dark:text-white text-sm leading-5.6 ease block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-blue-500 focus:outline-none"
                                                :placeholder="getPlaceholder"
                                            />
                                        </div>
                                    </div>
                                    <div
                                        :class="
                                            form.category_name ===
                                                'PC/NB'
                                                ? 'w-full max-w-full px-3 shrink-0 md:w-3/12 md:flex-0'
                                                : 'w-full max-w-full px-3 shrink-0 md:w-6/12 md:flex-0'
                                        "
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="nvr-id"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                            >
                                                Crew</label
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
                                                !selectedOption && formSubmitted
                                            "
                                            class="text-red-500"
                                            >Crew Has Required!</span
                                        >
                                    </div>
                                    <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-3/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="date-of-complaint"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                                >Date & Time Complaint</label
                                            >
                                            <VueDatePicker
                                                required
                                                v-model="selectedDate"
                                                :format="customFormat"
                                                placeholder="Select a date and time"
                                            />
                                        </div>
                                    </div>
                                    <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-3/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="complaint_image"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                                >Complaint Image</label
                                            >
                                            <input
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
                                                placeholder="Lokasi Pelapor"
                                            />
                                        </div>
                                    </div>
                                    <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-6/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="issue"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                                >Issue</label
                                            >
                                            <textarea
                                                required
                                                id="message"
                                                name="complaint_note"
                                                v-model="form.complaint_note"
                                                rows="4"
                                                class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                                placeholder="Complaint Issue"
                                            ></textarea>
                                        </div>
                                    </div>
                                    <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-6/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="location_-detail"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                                >Detail Location</label
                                            >
                                            <textarea
                                                id="message"
                                                name="location_detail"
                                                v-model="form.location_detail"
                                                rows="4"
                                                class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                                placeholder="Detail Location"
                                            ></textarea>
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
                                        :href="route('aduanIpt.page')"
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
