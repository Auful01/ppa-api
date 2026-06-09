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
    inspector: {
        type: Object,
    },
    crew: {
        type: Array,
    },
    dataInspeksi: {
        type: Array,
    },
    laptop: {
        type: Array,
    },
    site: {
        type: Object,
    },
});

const form = useForm({
    id: props.dataInspeksi.id,
    temuan: props.dataInspeksi.findings,
    tindakan: props.dataInspeksi.findings_action,
    due_date: props.dataInspeksi.due_date,
    findings_status: props.dataInspeksi.findings_status,
    remark: props.dataInspeksi.remarks,
    inspector: props.dataInspeksi.inspector,
    inspection_status: props.dataInspeksi.inspection_status,
    device_type: props.dataInspeksi.device_type,
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

const formSubmittedPIC = ref(false);

const selectedDateDueDate = ref(props.dataInspeksi.due_date);

const options = props.crew;

const selectedValuesPIC = ref(
    props.crew.filter(option =>
        props.inspector.includes(option.name)
    )
);

const penggunaString = computed(() => {
    return selectedValuesPIC.value.map((option) => option.name).join("");
});

onMounted(() => {
    console.log("inspector from props:", props.inspector);
    console.log("crew options:", props.crew);
    console.log("selectedValuesPIC after filter:", selectedValuesPIC.value);
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

    form.post(route("picaInspeksi.update"), {
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
    <Head title="Edit Data Pica" />

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
                        :href="route('picaInspeksi.page', { site: props.site })"
                        class="text-sm pl-2 capitalize leading-normal text-white before:float-left before:pr-2 before:text-white before:content-['/']"
                        aria-current="page"
                    >
                        Pica Inspeksi
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
                                                    :
                                                    {{
                                                        dataInspeksi.device_code
                                                    }}
                                                </p>
                                            </div>
                                        </div>
                                        <div v-if="dataInspeksi.device_type !== 'Mobile Tower'" class="grid grid-cols-2">
                                            <div>
                                                <p class="text-base">
                                                    Asset Ho Number
                                                </p>
                                            </div>
                                            <div>
                                                <p>
                                                    :
                                                    {{
                                                        dataInspeksi.device_asset_ho
                                                    }}
                                                </p>
                                            </div>
                                        </div>
                                        <div v-if="dataInspeksi.device_type !== 'Mobile Tower'" class="grid grid-cols-2">
                                            <div>
                                                <p class="text-base">
                                                    Pengguna
                                                </p>
                                            </div>
                                            <div>
                                                <p>
                                                    :
                                                    {{
                                                        dataInspeksi.device_pengguna
                                                    }}
                                                </p>
                                            </div>
                                        </div>
                                        <div v-if="dataInspeksi.device_type !== 'Mobile Tower'" class="grid grid-cols-2">
                                            <div>
                                                <p class="text-base">Dept</p>
                                            </div>
                                            <div>
                                                <p>
                                                    :
                                                    {{
                                                        dataInspeksi.device_dept
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
                                                    :
                                                    {{
                                                        dataInspeksi.device_name
                                                    }}
                                                </p>
                                            </div>
                                        </div>
                                        <div v-if="dataInspeksi.device_type !== 'Mobile Tower'" class="grid grid-cols-2">
                                            <div>
                                                <p class="text-base">
                                                    Spesifikasi
                                                </p>
                                            </div>
                                            <div>
                                                <p>
                                                    :
                                                    {{
                                                        dataInspeksi.device_spesifikasi
                                                    }}
                                                </p>
                                            </div>
                                        </div>
                                        <div v-if="dataInspeksi.device_type !== 'Mobile Tower'" class="grid grid-cols-2">
                                            <div>
                                                <p class="text-base">
                                                    Serial Number
                                                </p>
                                            </div>
                                            <div>
                                                <p>
                                                    :
                                                    {{ dataInspeksi.device_sn }}
                                                </p>
                                            </div>
                                        </div>
                                        <div v-if="dataInspeksi.device_type !== 'Mobile Tower'" class="grid grid-cols-2">
                                            <div>
                                                <p class="text-base">
                                                    Ip Address
                                                </p>
                                            </div>
                                            <div>
                                                <p>
                                                    :
                                                    {{
                                                        dataInspeksi.device_ip_address
                                                    }}
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
                                                    :
                                                    {{
                                                        dataInspeksi.device_location
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
                                                    :
                                                    {{
                                                        dataInspeksi.device_status_inventory
                                                    }}
                                                </p>
                                            </div>
                                        </div>
                                        <div class="grid grid-cols-2">
                                            <div>
                                                <p class="text-base">
                                                    Condition
                                                </p>
                                            </div>
                                            <div>
                                                <p>
                                                    :
                                                    {{
                                                        dataInspeksi.device_condition
                                                    }}
                                                </p>
                                            </div>
                                        </div>
                                        <div class="grid grid-cols-2">
                                            <div>
                                                <p class="text-base">Note</p>
                                            </div>
                                            <div>
                                                <p>
                                                    :
                                                    {{
                                                        dataInspeksi.device_note
                                                    }}
                                                </p>
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
                                    Form Edit Pica Inspeksi
                                </p>
                            </div>
                        </div>
                        <div class="flex-auto p-6">
                            <form @submit.prevent="update">
                                <div class="flex flex-wrap -mx-3">
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
                                                alt="Lampiran
                                                Foto Temuan"
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
                                                alt="Lampiran
                                                Foto Tindakan"
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
                                        </div>
                                    </div>

                                    <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-6/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="date-of-inventory"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                                >Due Date</label
                                            >
                                            <VueDatePicker
                                                v-model="selectedDateDueDate"
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
                                                for="findings_status"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                            >
                                                Status Temuan</label
                                            >
                                            <select
                                                id="findings_status"
                                                v-model="form.findings_status"
                                                name="findings_status"
                                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                            >
                                                <option value="OPEN">
                                                    OPEN
                                                </option>
                                                <option value="PROGRESS">
                                                    PROGRESS
                                                </option>
                                                <option value="CLOSED">
                                                    CLOSED
                                                </option>
                                            </select>
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
                                                Select PIC</label
                                            >
                                           <VueMultiselect
                                                v-model="selectedValuesPIC"
                                                :options="options"
                                                :multiple="true"
                                                :max="1"
                                                :close-on-select="true"
                                                placeholder="Pilih PIC"
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
                                                alt="Lampiran
                                                Foto Inspeksi"
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
                                                >Update Lampiran Foto
                                                Inspeksi</label
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
                                        :href="route('picaInspeksi.page', { site: props.site })"
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
