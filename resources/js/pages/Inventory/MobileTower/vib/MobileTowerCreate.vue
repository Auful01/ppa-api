<script setup>
import AuthenticatedLayoutForm from "@/Layouts/AuthenticatedLayoutForm.vue";
import { Head, useForm, usePage, Link, router } from "@inertiajs/vue3";
import VueDatePicker from "@vuepic/vue-datepicker";
import "@vuepic/vue-datepicker/dist/main.css";
import Swal from "sweetalert2";
import { ref, nextTick, watch, reactive } from "vue";

const props = defineProps(["inventoryNumber"]);

const radioButton = reactive({
    gps: "",
    lampu_led: "",
    kondisi: "",
});

const errors = reactive({});

const form = useForm({
    inventory_number: "",
    kode_mt: "",
    tipe_mt: "",
    lokasi_mt: "",
    detail_lokasi: "",
    kode_gembok: "",
    gps: "",
    lampu_led: "",
    kondisi: "",
    status: "",
    note: "",
});

const page = usePage();

const isDisabled = ref(true);

const save = () => {
    errors.gps = form.gps ? "" : "Pilih salah satu kondisi GPS";
    errors.lampu_led = form.lampu_led
        ? ""
        : "Pilih salah satu kondisi Lampu LED";
    errors.kondisi = form.kondisi ? "" : "Pilih salah satu Kondisi";

    if (!errors.gps && !errors.lampu_led && !errors.kondisi) {
        form.post(route("mobileTowerVib.store"), {
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
};
</script>

<template>
    <Head title="Tambah Data - MT" />

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
                        :href="route('mobileTowerVib.page')"
                        class="text-sm pl-2 capitalize leading-normal text-white before:float-left before:pr-2 before:text-white before:content-['/']"
                        aria-current="page"
                    >
                        Mobile Tower
                    </Link>
                </ol>
                <h6 class="mb-0 font-bold text-white capitalize">
                    Mobile Tower Create Pages
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
                                    Form Create Mobile Tower
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
                                                for="inventory_number"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                                >Inventory Number</label
                                            >
                                            <input
                                                required
                                                type="text"
                                                name="inventory_number"
                                                v-model="form.inventory_number"
                                                class="focus:shadow-primary-outline dark:bg-slate-850 dark:text-white text-sm leading-5.6 ease block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-blue-500 focus:outline-none"
                                                placeholder="VIB-MT-COE-001"
                                            />
                                        </div>
                                    </div>
                                    <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-6/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="kode_mt"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                                >Kode MT</label
                                            >
                                            <input
                                                required
                                                type="text"
                                                name="kode_mt"
                                                v-model="form.kode_mt"
                                                @keydown.space.prevent
                                                @input="
                                                    form.kode_mt =
                                                        form.kode_mt.replace(
                                                            /\s+/g,
                                                            ''
                                                        )
                                                "
                                                class="focus:shadow-primary-outline dark:bg-slate-850 dark:text-white text-sm leading-5.6 ease block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-blue-500 focus:outline-none"
                                                placeholder="MTW01"
                                            />
                                        </div>
                                    </div>
                                    <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-6/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="tipe_mt"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                                >Tipe MT</label
                                            >
                                            <input
                                                required
                                                type="text"
                                                name="tipe_mt"
                                                v-model="form.tipe_mt"
                                                class="focus:shadow-primary-outline dark:bg-slate-850 dark:text-white text-sm leading-5.6 ease block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-blue-500 focus:outline-none"
                                                placeholder="MTS HITAM"
                                            />
                                        </div>
                                    </div>

                                    <!-- Lokasi MT -->
                                    <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-6/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="lokasi_mt"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                            >
                                                Lokasi MT
                                            </label>
                                            <input
                                                required
                                                type="text"
                                                name="lokasi_mt"
                                                v-model="form.lokasi_mt"
                                                class="focus:shadow-primary-outline dark:bg-slate-850 dark:text-white text-sm leading-5.6 ease block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-blue-500 focus:outline-none"
                                                placeholder="Lokasi MT"
                                            />
                                        </div>
                                    </div>

                                    <!-- Detail Lokasi -->
                                    <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-6/12 md:flex-0"
                                    >
                                        <div class="mb-8">
                                            <label
                                                for="detail_lokasi"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                            >
                                                Detail Lokasi
                                            </label>
                                            <input
                                                required
                                                type="text"
                                                name="detail_lokasi"
                                                v-model="form.detail_lokasi"
                                                class="focus:shadow-primary-outline dark:bg-slate-850 dark:text-white text-sm leading-5.6 ease block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-blue-500 focus:outline-none"
                                                placeholder="Detail Lokasi"
                                            />
                                        </div>
                                    </div>

                                    <!-- Kode Gembok (Numbers Only) -->
                                    <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-6/12 md:flex-0"
                                    >
                                        <div class="mb-8">
                                            <label
                                                for="kode_gembok"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                            >
                                                Kode Gembok
                                            </label>
                                            <input
                                                required
                                                type="number"
                                                name="kode_gembok"
                                                v-model="form.kode_gembok"
                                                class="focus:shadow-primary-outline dark:bg-slate-850 dark:text-white text-sm leading-5.6 ease block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-blue-500 focus:outline-none"
                                                placeholder="12345"
                                            />
                                        </div>
                                    </div>

                                    <!-- GPS -->
                                    <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-4/12 md:flex-0"
                                    >
                                        <div class="mb-8">
                                            <label
                                                class="block text-center mb-3 text-sm font-semibold uppercase tracking-wide text-slate-700 dark:text-white/80 border-b border-gray-300 pb-1"
                                            >
                                                GPS
                                            </label>

                                            <div
                                                class="flex justify-center space-x-6"
                                            >
                                                <label
                                                    class="inline-flex items-center"
                                                >
                                                    <input
                                                        type="radio"
                                                        value="TERPASANG"
                                                        v-model="form.gps"
                                                        class="mr-2"
                                                    />
                                                    TERPASANG
                                                </label>
                                                <label
                                                    class="inline-flex items-center"
                                                >
                                                    <input
                                                        type="radio"
                                                        value="TIDAK TERPASANG"
                                                        v-model="form.gps"
                                                        class="mr-2"
                                                    />
                                                    TIDAK TERPASANG
                                                </label>
                                            </div>
                                        </div>
                                        <p
                                            v-if="errors.gps"
                                            class="text-red-500 text-sm text-center"
                                        >
                                            {{ errors.gps }}
                                        </p>
                                    </div>

                                    <!-- Lampu LED -->
                                    <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-4/12 md:flex-0"
                                    >
                                        <div class="mb-8">
                                            <label
                                                class="block text-center mb-3 text-sm font-semibold uppercase tracking-wide text-slate-700 dark:text-white/80 border-b border-gray-300 pb-1"
                                            >
                                                Lampu LED
                                            </label>
                                            <div
                                                class="flex justify-center space-x-6"
                                            >
                                                <label
                                                    class="inline-flex items-center"
                                                >
                                                    <input
                                                        type="radio"
                                                        value="TERPASANG"
                                                        v-model="form.lampu_led"
                                                        class="mr-2"
                                                    />
                                                    TERPASANG
                                                </label>
                                                <label
                                                    class="inline-flex items-center"
                                                >
                                                    <input
                                                        type="radio"
                                                        value="TIDAK TERPASANG"
                                                        v-model="form.lampu_led"
                                                        class="mr-2"
                                                    />
                                                    TIDAK TERPASANG
                                                </label>
                                            </div>
                                        </div>
                                        <p
                                            v-if="errors.lampu_led"
                                            class="text-red-500 text-sm text-center"
                                        >
                                            {{ errors.lampu_led }}
                                        </p>
                                    </div>

                                    <!-- Kondisi -->
                                    <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-4/12 md:flex-0"
                                    >
                                        <div class="mb-8">
                                            <label
                                                class="block text-center mb-3 text-sm font-semibold uppercase tracking-wide text-slate-700 dark:text-white/80 border-b border-gray-300 pb-1"
                                            >
                                                Kondisi
                                            </label>
                                            <div
                                                class="flex justify-center space-x-6"
                                            >
                                                <label
                                                    class="inline-flex items-center"
                                                >
                                                    <input
                                                        type="radio"
                                                        value="LAYAK"
                                                        v-model="form.kondisi"
                                                        class="mr-2"
                                                    />
                                                    LAYAK
                                                </label>
                                                <label
                                                    class="inline-flex items-center"
                                                >
                                                    <input
                                                        type="radio"
                                                        value="TIDAK LAYAK"
                                                        v-model="form.kondisi"
                                                        class="mr-2"
                                                    />
                                                    TIDAK LAYAK
                                                </label>
                                            </div>
                                        </div>
                                        <p
                                            v-if="errors.kondisi"
                                            class="text-red-500 text-sm text-center"
                                        >
                                            {{ errors.kondisi }}
                                        </p>
                                    </div>

                                    <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-6/12 md:flex-0"
                                    >
                                        <div class="mb-8">
                                            <label
                                                for="status"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                            >
                                                Status MT</label
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
                                <div
                                    class="flex flex-nowrap mt-6 justify-between"
                                >
                                    <Link
                                        :href="route('mobileTowerVib.page')"
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
