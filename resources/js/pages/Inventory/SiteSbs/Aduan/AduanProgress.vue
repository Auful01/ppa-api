<style src="vue-multiselect/dist/vue-multiselect.css"></style>
<script setup>
import AuthenticatedLayoutForm from "@/Layouts/AuthenticatedLayoutForm.vue";
import { Link } from "@inertiajs/vue3";
import { Head, useForm } from "@inertiajs/vue3";
import Swal from "sweetalert2";
import dayjs from "dayjs";
import VueDatePicker from "@vuepic/vue-datepicker";
import "@vuepic/vue-datepicker/dist/main.css";
import VueMultiselect from "vue-multiselect";
import { Inertia } from "@inertiajs/inertia";
import { ref, computed } from "vue";

const props = defineProps({
    crew: {
        type: Array,
    },
    aduan: {
        type: Array,
    },
        rootCause: {
        type: Array,
    },
});
const form = useForm({
    id: props.aduan.id,
    location: props.aduan.location,
    location_detail: props.aduan.detail_location,
    status: props.aduan.status,
    complaint_note: props.aduan.complaint_note,
    actionRepair: props.aduan.action_repair,
    repair_note: props.aduan.repair_note,
    root_cause_id: "", // awalnya kosong
    category: props.aduan.category_name,
});

const isDisabled = ref(true);

const file = ref(null);

const handleFileUpload = (event) => {
    file.value = event.target.files[0];
};

const userTimezone = Intl.DateTimeFormat().resolvedOptions().timeZone;

// Fungsi konversi dari WITA (Asia/Makassar) ke zona waktu lokal browser
function toLocalTime(date) {
    if (!date) return null;
    return dayjs.tz(date, "Asia/Makassar").tz(userTimezone).toDate();
}

// Fungsi konversi balik ke WITA sebelum dikirim ke server
function toServerTime(date) {
    if (!date) return null;
    return dayjs(date).tz("Asia/Makassar").format("YYYY-MM-DD HH:mm:ss");
}
const dateFormat = "yyyy-MM-dd HH:mm:ss";

const dateOfComplaint = ref(props.aduan.date_of_complaint);
const dateOfComplaintLocal = ref(null);
const startResponse = ref(props.aduan.start_response);
const startResponseLocal = ref(null);
const startProgress = ref(null);
const startProgressLocal = ref(null);
const endProgress = ref(null);
const endProgressLocal = ref(null);

const isDateRequired = computed(() => props.aduan.start_response !== null);

const selectedValues = ref([]); // Awalnya array kosong
const crewString = computed(() => {
    return selectedValues.value.map((option) => option.name).join(", ");
});

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

const updateProgress = () => {
    const formData = new FormData();
    const formattedDateDateOfComplaint = customFormat(dateOfComplaint.value);
    const formattedDateStartResponse = customFormat(startResponse.value);
    const formattedDateStartProgress = customFormat(startProgress.value);
    const formattedDateEndProgress = customFormat(endProgress.value);
    formData.append("id", form.id);
    formData.append("crew", crewString.value);
    formData.append("image", file.value);
    formData.append("actionRepair", form.actionRepair);
    formData.append("dateOfComplaint", formattedDateDateOfComplaint);
    formData.append("startResponse", formattedDateStartResponse);
    formData.append("startProgress", formattedDateStartProgress);
    formData.append("endProgress", formattedDateEndProgress);
    formData.append("status", form.status);
    formData.append("location", form.location);
    formData.append("detail_location", form.location_detail);
    formData.append("repair_note", form.repair_note);
    formData.append("complaint_note", form.complaint_note);
    formData.append("rootCause", form.root_cause_id);
    formData.append("category", form.category);
    Inertia.post(route("aduanSbs.updateProgress"), formData, {
        forceFormData: true,
        onSuccess: () => {
            // Show SweetAlert2 success notification
            Swal.fire({
                title: "Success!",
                text: "Data has been successfully updated!",
                icon: "success",
                confirmButtonText: "OK",
                confirmButtonColor: "#3085d6",
            });
        },
        onError: () => {
            Swal.fire({
                title: "error!",
                text: "Data not updated!",
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
                        :href="route('aduanSbs.page')"
                        class="text-sm pl-2 capitalize leading-normal text-white before:float-left before:pr-2 before:text-white before:content-['/']"
                        aria-current="page"
                    >
                        Aduan
                    </Link>
                </ol>
                <h6 class="mb-0 font-bold text-white capitalize">
                    Aduan Progress Pages
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
                                    Form Progress Aduan
                                </p>
                            </div>
                        </div>
                        <div class="flex-auto p-6">
                            <form @submit.prevent="updateProgress">
                                <hr
                                    class="h-px mx-0 my-4 bg-transparent border-0 opacity-25 bg-gradient-to-r from-transparent via-black/40 to-transparent dark:bg-gradient-to-r dark:from-transparent dark:via-white dark:to-transparent"
                                />
                                <div class="flex flex-row mb-3">
                                    <div class="basis-1/2">
                                        Ticket Code
                                        <span class="ml-12">
                                            : {{ props.aduan.complaint_code }}
                                        </span>
                                    </div>
                                    <div class="basis-1/2">
                                        NRP
                                        <span class="ml-28">
                                            :
                                            {{ props.aduan.nrp }}
                                        </span>
                                    </div>
                                </div>
                                <div class="flex flex-row mb-3">
                                    <div class="basis-1/2">
                                        Category Aduan
                                        <span class="ml-4">
                                            : {{ props.aduan.category_name }}
                                        </span>
                                    </div>
                                    <div class="basis-1/2">
                                        Complaint Name
                                        <span class="ml-4.5">
                                            : {{ props.aduan.complaint_name }}
                                        </span>
                                    </div>
                                </div>
                                <div class="flex flex-row mb-3">
                                    <div class="basis-1/2">
                                        Complaint Note
                                        <span class="ml-4.5">
                                            : {{ props.aduan.complaint_note }}
                                        </span>
                                    </div>
                                    <div class="basis-1/2">
                                        WhatsApp Number
                                        <span>
                                            : 0{{ props.aduan.phone_number }}
                                        </span>
                                    </div>
                                </div>
                                <div class="flex flex-row mb-3">
                                    <div class="basis-1/2">
                                        Complaint Date
                                        <span class="ml-4.5">
                                            :
                                            {{ props.aduan.date_of_complaint }}
                                        </span>
                                    </div>
                                    <div class="basis-1/2">
                                        Complaint Position
                                        <span class="ml-0.5">
                                            :
                                            {{ props.aduan.complaint_position }}
                                        </span>
                                    </div>
                                </div>
                                <div class="flex flex-row mb-3">
                                    <div class="basis-1/2">
                                        Crew
                                        <span class="ml-24">
                                            :
                                            {{ props.aduan.crew }}
                                        </span>
                                    </div>
                                    <div class="basis-1/2">
                                        Complaint Image
                                        <span class="ml-4">
                                            :
                                            <img
                                                :src="
                                                    props.aduan.complaint_image
                                                "
                                                alt="documentation image"
                                                class="ml-40 w-50 h-30 shadow-2xl rounded-xl"
                                            />
                                        </span>
                                    </div>
                                </div>
                                <hr
                                    class="h-px mx-0 my-4 bg-transparent border-0 opacity-25 bg-gradient-to-r from-transparent via-black/40 to-transparent dark:bg-gradient-to-r dark:from-transparent dark:via-white dark:to-transparent"
                                />
                                <div class="flex flex-wrap -mx-3 mt-3">
                                    <div
                                        :class="
                                            form.category_name === 'PC/LAPTOP'
                                                ? 'w-full max-w-full px-3 shrink-0 md:w-3/12 md:flex-0'
                                                : 'w-full max-w-full px-3 shrink-0 md:w-3/12 md:flex-0'
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
                                                placeholder="Select For Update Crew"
                                                track-by="name"
                                                label="name"
                                            />
                                        </div>
                                    </div>
                                    <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-3/12 md:flex-0"
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
                                                v-model="dateOfComplaintLocal"
                                                :model-value="
                                                    toLocalTime(dateOfComplaint)
                                                "
                                                @update:model-value="
                                                    (val) =>
                                                        (dateOfComplaint =
                                                            toServerTime(val))
                                                "
                                                :enable-time-picker="true"
                                                :is-24="true"
                                                :format="dateFormat"
                                                placeholder="Select a date and time"
                                            />
                                        </div>
                                    </div>
                                    <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-3/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="nvr-id"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                            >
                                                Status</label
                                            >
                                            <select
                                                required
                                                id="status"
                                                v-model="form.status"
                                                name="status"
                                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                            >
                                                <option value="OPEN">
                                                    OPEN
                                                </option>
                                                <option value="PROGRESS">
                                                    PROGRESS
                                                </option>
                                                <option value="CANCEL">
                                                    CANCEL
                                                </option>
                                                <option value="CLOSED">
                                                    CLOSED
                                                </option>
                                                <option value="OUTSTANDING">
                                                    OUTSTANDING
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                          <!-- ROOT CAUSE -->
                                    <div
                                        v-if="form.status === 'CLOSED'"
                                        class="w-full max-w-full px-3 shrink-0 md:w-3/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="root_cause_id"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                            >
                                                Root Cause
                                            </label>
                                            <select
                                                :required="
                                                    form.status === 'CLOSED' &&
                                                    props.aduan
                                                        .category_name !==
                                                        'OTHER'
                                                "
                                                id="root_cause_id"
                                                v-model="form.root_cause_id"
                                                name="root_cause_id"
                                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                            >
                                                <option disabled value="">
                                                    Select Root Cause Problem
                                                </option>
                                                <option
                                                    v-for="rc in props.rootCause"
                                                    :key="rc.id"
                                                    :value="rc.id"
                                                >
                                                    {{ rc.name }}
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                    <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-3/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="start-response"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                                >Start Response</label
                                            >
                                            <VueDatePicker
                                                required
                                                v-model="startResponseLocal"
                                                :model-value="
                                                    toLocalTime(startResponse)
                                                "
                                                @update:model-value="
                                                    (val) =>
                                                        (startResponse =
                                                            toServerTime(val))
                                                "
                                                :enable-time-picker="true"
                                                :is-24="true"
                                                :format="dateFormat"
                                                placeholder="Select Strat Response"
                                            />
                                        </div>
                                    </div>
                                    <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-3/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="start-progress"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                                >Start Progress</label
                                            >
                                            <VueDatePicker
                                                :required="isDateRequired"
                                                v-model="startProgressLocal"
                                                :model-value="
                                                    toLocalTime(startProgress)
                                                "
                                                @update:model-value="
                                                    (val) =>
                                                        (startProgress =
                                                            toServerTime(val))
                                                "
                                                :enable-time-picker="true"
                                                :is-24="true"
                                                :format="dateFormat"
                                                placeholder="Select Start Progress"
                                            />
                                        </div>
                                    </div>
                                    <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-3/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="end-progress"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                                >End Progress</label
                                            >
                                            <VueDatePicker
                                                :required="isDateRequired"
                                                v-model="endProgressLocal"
                                                :model-value="
                                                    toLocalTime(endProgress)
                                                "
                                                @update:model-value="
                                                    (val) =>
                                                        (endProgress =
                                                            toServerTime(val))
                                                "
                                                :enable-time-picker="true"
                                                :is-24="true"
                                                :format="dateFormat"
                                                placeholder="Select End Progress"
                                            />
                                        </div>
                                    </div>
                                    <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-6/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="action"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                                >Action Repair</label
                                            >
                                            <textarea
                                                :required="isDateRequired"
                                                id="message"
                                                name="actionRepair"
                                                v-model="form.actionRepair"
                                                rows="4"
                                                class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                                placeholder="Aksi Perbaikan"
                                            ></textarea>
                                        </div>
                                    </div>
                                    <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-6/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="repair-note"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                                >Repair Note</label
                                            >
                                            <textarea
                                                id="message"
                                                name="repair_note"
                                                v-model="form.repair_note"
                                                rows="4"
                                                class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                                placeholder="Remark Perbaikan"
                                            ></textarea>
                                        </div>
                                    </div>
                                    <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-4/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="issue"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                                >Issue/Complaint Note</label
                                            >
                                            <textarea
                                                readonly
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
                                        class="w-full max-w-full px-3 shrink-0 md:w-4/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="location-detail"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                                >Detail Location</label
                                            >
                                            <textarea
                                                readonly
                                                id="message"
                                                name="location_detail"
                                                v-model="form.location_detail"
                                                rows="4"
                                                class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                                placeholder="Detail Location"
                                            ></textarea>
                                        </div>
                                    </div>
                                    <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-4/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="complaint_image"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                                >Repair Image</label
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
                                </div>
                                <hr
                                    class="h-px mx-0 my-4 bg-transparent border-0 opacity-25 bg-gradient-to-r from-transparent via-black/40 to-transparent dark:bg-gradient-to-r dark:from-transparent dark:via-white dark:to-transparent"
                                />
                                <div
                                    class="flex flex-nowrap mt-6 justify-between"
                                >
                                    <Link
                                        :href="route('aduanSbs.page')"
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
