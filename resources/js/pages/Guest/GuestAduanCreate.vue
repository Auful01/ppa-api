<style src="vue-multiselect/dist/vue-multiselect.css"></style>
<script setup>
import GuestLayoutForm from "@/Layouts/GuestLayoutForm.vue";
import { Link } from "@inertiajs/vue3";
import { Head, useForm } from "@inertiajs/vue3";
import VueMultiselect from "vue-multiselect";
import VueDatePicker from "@vuepic/vue-datepicker";
import "@vuepic/vue-datepicker/dist/main.css";
import { usePage } from "@inertiajs/vue3";
import Swal from "sweetalert2";
import { Inertia } from "@inertiajs/inertia";
import { ref, computed, watch, onMounted, onUnmounted } from "vue";

const page = usePage();
const authUser = page.props.auth.user;

const props = defineProps({
    complaintDataNrp: {
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
    nrp: "",
    complaint_code: props.ticket,
    complaint_name: "",
    phone_number: "",
    inventory_number: "",
    category_name: "",
    location: "",
    complaint_note: "",
    location_detail: "",
    image: "",
});

const notifyAdmin = () => {
    localStorage.setItem("aduanSubmitted", Date.now().toString()); // Simpan timestamp di localStorage
    console.log("✅ Notifikasi aduan dikirim ke admin.");
};

onMounted(() => {
    const submitButton = document.querySelector('button[type="submit"]');
    if (submitButton) {
        submitButton.addEventListener("click", notifyAdmin);
    }
});

//start auto generate nama by nik
const complaintDataNrp = props.complaintDataNrp;
const nrp = ref(authUser.nrp);
const complaintName = ref(authUser.name);

watch(nrp, (newNrp) => {
    const found = complaintDataNrp.find((data) => data.nrp === newNrp);
    complaintName.value = found ? found.username : "";
});
// end auto generate nama by nik

const isDisabled = ref(true);
const file = ref(null);

const handleFileUpload = (event) => {
    file.value = event.target.files[0];
};

const isSubmitting = ref(false);

const save = () => {
    if (!file.value) {
        Swal.fire({
            icon: "error",
            title: "Oops...",
            text: "Wajib Melampirkan Evidence Aduan!",
            confirmButtonColor: "#1e293b", // slate-800 (Tailwind hex)
            confirmButtonText: "OK",
        });
        return;
    }

    if (isSubmitting.value) {
        Swal.fire({
            icon: "info",
            title: "Harap tunggu...",
            text: "Data sedang dikirim ke server.",
            confirmButtonColor: "#3085d6",
        });
        return; // blokir klik kedua
    }

    isSubmitting.value = true; // aktifkan flag

    form.image = file.value;
    form.nrp = nrp.value;
    form.complaint_name = complaintName.value;

    form.post(route("guestAduan.store"), {
        onSuccess: () => {
            const newAduan = {
                id: Date.now(),
                message: "Aduan Baru telah dikirim!",
            };
            localStorage.setItem("lastAduan", JSON.stringify(newAduan));
            console.log("✅ Data disimpan di localStorage:", newAduan);

            Swal.fire({
                title: "Complaint has been submited!",
                text: "Thank you for contacting the ICT centre, your complaint will be handled immediately by the team.",
                icon: "success",
                confirmButtonColor: "#3085d6",
                confirmButtonText: "OK",
            }).then(() => {
                isSubmitting.value = false; // reset setelah sukses
                form.get(route("guestAduan.page"));
            });
        },
        onError: () => {
            isSubmitting.value = false; // reset agar bisa submit ulang
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

function handleCategoryChange(event) {
    form.category_name = event.target.value;
}

// Computed property untuk mendapatkan placeholder
const getPlaceholder = computed(() => {
    if (form.category_name === "PC/NB") {
        return "Masukkan No Inventory PC/Note Bool";
    }
});

const showAlertTrue = () => {
    Swal.fire({
        title: "Tabel Role Akses User!",
        html: '<img src="/step_no_inv.jpg" class="inline transition-all duration-200 ease-nav-brand max-h-60 mr-2" alt="main_logo" />',
    });
};
</script>

<template>
    <Head title="Complaint Pages" />

    <GuestLayoutForm>
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
                        :href="route('aduan.page')"
                        class="text-sm pl-2 capitalize leading-normal text-white before:float-left before:pr-2 before:text-white before:content-['/']"
                        aria-current="page"
                    >
                        Complaint
                    </Link>
                </ol>
                <h6 class="mb-0 font-bold text-white capitalize">
                    Complaint Create Pages
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
                            <div class="flex items-center justify-between">
                                <p class="mb-0 font-bold dark:text-white/80">
                                    Form Create Complaint
                                </p>
                                <div class="order-last">
                                    <Link
                                        :href="route('guestAduan.page')"
                                        class="relative inline-flex items-center justify-center p-0.5 mb-2 me-2 overflow-hidden text-sm font-medium text-gray-900 rounded-lg group bg-gradient-to-br from-orange-200 via-orange-300 to-yellow-200 group-hover:from-orange-200 group-hover:via-orange-300 group-hover:to-yellow-200 dark:text-white dark:hover:text-gray-900 focus:ring-4 focus:outline-none focus:ring-orange-100 dark:focus:ring-orange-400"
                                    >
                                        <span
                                            class="relative px-5 py-2.5 transition-all ease-in duration-75 bg-white dark:bg-gray-900 rounded-md group-hover:bg-opacity-0"
                                        >
                                            Complaint Status
                                        </span>
                                    </Link>
                                </div>
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
                                                for="nrp"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                                >NRP</label
                                            >
                                            <input
                                                required
                                                :disabled="isDisabled"
                                                type="text"
                                                v-model="nrp"
                                                name="nrp"
                                                class="mb-5 bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 cursor-not-allowed dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-gray-400 dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                                placeholder="NRP Pelapor"
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
                                                for="complaint-name"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                                >Complaint Name</label
                                            >
                                            <input
                                                required
                                                :disabled="isDisabled"
                                                type="text"
                                                name="complaint_name"
                                                v-model="complaintName"
                                                class="mb-5 bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 cursor-not-allowed dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-gray-400 dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                                placeholder="Nama Pelapor Otomatis Generate!, Masukkan NRP Terlebih Dahulu!"
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
                                                Category Complaint</label
                                            >
                                            <select
                                                @change="handleCategoryChange"
                                                required
                                                id="category_name"
                                                v-model="form.category_name"
                                                name="category_name"
                                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                            >
                                                <option selected value="">
                                                    SELECT CATEGORY
                                                </option>
                                                <option
                                                    v-for="category in categories"
                                                    :key="category.id"
                                                    :value="
                                                        category.category_root_cause
                                                    "
                                                >
                                                    {{
                                                        category.category_root_cause
                                                    }}
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                    <div
                                        v-if="form.category_name == 'PC/NB'"
                                        class="w-full max-w-full px-3 shrink-0 md:w-6/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="inventory-number"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                                >Inventory Number</label
                                            >
                                            <a
                                                style="cursor: pointer"
                                                class="icon-button"
                                                @click="showAlertTrue"
                                            >
                                                <i
                                                    class="ms-3 mt-1 text-red-700 fas fa-question-circle"
                                                ></i>
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
                                            form.category_name === 'PC/NB'
                                                ? 'w-full max-w-full px-3 shrink-0 md:w-6/12 md:flex-0'
                                                : 'w-full max-w-full px-3 shrink-0 md:w-3/12 md:flex-0'
                                        "
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
                                                required
                                            />
                                        </div>
                                    </div>
                                    <div
                                        :class="
                                            form.category_name === 'PC/NB'
                                                ? 'w-full max-w-full px-3 shrink-0 md:w-6/12 md:flex-0'
                                                : 'w-full max-w-full px-3 shrink-0 md:w-3/12 md:flex-0'
                                        "
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
                                        :href="route('guestAduan.page')"
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
                                        @click.prevent="save"
                                        :disabled="isSubmitting"
                                        class="relative inline-flex items-center justify-center p-0.5 mb-2 me-2 overflow-hidden text-sm font-medium text-gray-900 rounded-lg group bg-gradient-to-br from-purple-600 to-blue-500 group-hover:from-purple-600 group-hover:to-blue-500 hover:text-white dark:text-white focus:ring-4 focus:outline-none focus:ring-blue-300 dark:focus:ring-blue-800 disabled:opacity-50 disabled:cursor-not-allowed"
                                    >
                                        <span
                                            class="relative px-5 py-2.5 transition-all ease-in duration-75 bg-white dark:bg-gray-900 rounded-md group-hover:bg-opacity-0"
                                        >
                                            {{
                                                isSubmitting
                                                    ? "Sending..."
                                                    : "Save"
                                            }}
                                        </span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </GuestLayoutForm>
</template>
