<script setup>
import AuthenticatedLayoutForm from "@/Layouts/AuthenticatedLayoutForm.vue";
import { Link, router } from "@inertiajs/vue3";
import { Head, useForm, usePage } from "@inertiajs/vue3";
import VueMultiselect from "vue-multiselect";
import VueDatePicker from "@vuepic/vue-datepicker";
import "@vuepic/vue-datepicker/dist/main.css";
import Swal from "sweetalert2";
import axios from "axios";
import { Inertia } from "@inertiajs/inertia";
import { ref, computed, nextTick, watch, onMounted } from "vue";

const props = defineProps({
    pengguna: {
        type: Array,
    },
    dataPengalihan: {
        type: Object,
    },
    department: {
        type: Object,
    },
    dataPengalihanPrev: {
        type: Object,
    },
});

const form = useForm({
    spek: "",
    prevNrp: "",
    prevUsername: "",
    newInvNumberPengalihan: "",
    nextJabatan: "",
    nextDept: "",
    nextUsername: "",
    note: "",
});

const page = usePage();
const selectedOption = ref({ name: "" });
const optionsDept = props.department;
const optionsInvNumber = ref([]);
const selectedOptionInvNumber = ref(null);
const selectedOptionDeptPrev = ref(
    props.department.find(
        (item) => item.code === props.dataPengalihanPrev.deptPrev
    )
);

const optionsPengguna = props.pengguna;
const selectedOptionDeptNext = ref(null);
const selectedOptionUserPengguna = ref(null);
const file = ref(null);

onMounted(async () => {
        selectedOption.value = {
        name: props.dataPengalihan?.device || ""
    };

    // ⏳ Tunggu sampai data department & device_type sudah siap (max 5 detik)
    const waitUntilReady = async (timeout = 5000) => {
        const start = Date.now();
        while (
            (!selectedOptionDeptPrev.value || !selectedOption.value) &&
            Date.now() - start < timeout
        ) {
            await new Promise((resolve) => setTimeout(resolve, 100)); // Cek ulang tiap 100ms
        }
    };

    await waitUntilReady();

    if (selectedOptionDeptPrev.value && selectedOption.value) {
        try {
            const response = await axios.get(
                route("pengalihanAsset.getDataPrev"),
                {
                    params: {
                        device_type: selectedOption.value.name,
                        department: selectedOptionDeptPrev.value.code,
                        site: page.props.site,
                    },
                }
            );

            // ✅ Simpan data dari response
            optionsInvNumber.value = response.data.inventoryData || [];
            console.log("📦 Semua inventory options:", optionsInvNumber.value.slice(0, 5));

            // 🔍 Ambil target code dan normalisasi
            const targetCode = props.dataPengalihanPrev.noInvPrev?.trim().toUpperCase();
            console.log("🎯 Target code:", `"${targetCode}"`);

            // 🧱 Clone untuk hilangkan Proxy agar bisa dibanding langsung
            const invList = JSON.parse(JSON.stringify(optionsInvNumber.value));

            // 🔎 Coba temukan kode yang sesuai
            selectedOptionInvNumber.value = invList.find((item) => {
                const itemCode = item.code?.trim().toUpperCase();
                console.log(`🧪 Bandingkan item: "${itemCode}" === "${targetCode}"`);
                return itemCode === targetCode;
            }) || null;

            console.log("📌 Hasil seleksi:", selectedOptionInvNumber.value);

            if (!selectedOptionInvNumber.value) {
                console.warn("⚠️ Inventory dengan code ini tidak ditemukan:", targetCode);
            } else {
                console.log("✅ Auto-selected inventory:", selectedOptionInvNumber.value);
            }

        } catch (error) {
            console.error("❌ Error saat auto-select inventory:", error);
        }
    } else {
        console.warn("⚠️ Data device_type atau department belum siap, auto-select dibatalkan.");
    }
});


// Watch perubahan department
watch(selectedOptionDeptPrev, async (newDept) => {
    if (!selectedOption.value || !selectedOption.value.name) {
        await Swal.fire({
            icon: "warning",
            title: "Oops!",
            text: "Harus memilih device terlebih dahulu",
            confirmButtonColor: "#334155",
        });
        selectedOptionDeptPrev.value = null;
        return;
    }

    try {
        const response = await axios.get(route("pengalihanAsset.getDataPrev"), {
            params: {
                device_type: selectedOption.value.name,
                department: newDept.code,
                site: page.props.site,
            },
        });

        optionsInvNumber.value = response.data.inventoryData || [];

        // ⬇⬇ Tambahkan DI SINI ⬇⬇
        selectedOptionInvNumber.value = optionsInvNumber.value.find(
            (item) => item.code === props.dataPengalihanPrev.noInvPrev
        );
        console.log("🎯 Auto-selected inv:", selectedOptionInvNumber.value);
    } catch (error) {
        console.error("❌ Gagal mengambil data inventory:", error);
        Swal.fire({
            icon: "error",
            title: "Terjadi Kesalahan",
            text: "Gagal mengambil data dari server, silahkan select salah satu departemen",
            confirmButtonColor: "#334155",
        });
    }
});

watch(selectedOptionInvNumber, async (newInvNumber) => {
    if (!newInvNumber || !newInvNumber.id) {
        console.log("⚠️ Inventory selection dibatalkan atau dikosongkan.");

        form.spek = "";
        form.prevNrp = "";
        form.prevUsername = "";
        return;
    }

    console.log("📥 Request params:", {
        idInv: newInvNumber.id,
        device_type: selectedOption.value.name,
    });

    try {
        const response = await axios.get(
            route("pengalihanAsset.getDataInvPrev"),
            {
                params: {
                    device_type: selectedOption.value.name,
                    idInv: newInvNumber.id,
                },
            }
        );

        console.log("🔥 Data dari backend:", response.data);

        const inventory = response.data.inventoryData;

        if (!inventory) {
            throw new Error("Inventory data kosong (null)");
        }

        form.spek = inventory.spesifikasi || "";
        form.prevNrp = inventory.pengguna?.nrp || "";
        form.prevUsername = inventory.pengguna?.username || "";

    } catch (error) {
        console.error("❌ Gagal mengambil data inventory:", error);

        Swal.fire({
            icon: "error",
            title: "Terjadi Kesalahan",
            text: "Gagal mengambil data dari server atau data tidak ditemukan.",
            confirmButtonColor: "#334155",
        });

        // Kosongkan form jika gagal
        form.spek = "";
        form.prevNrp = "";
        form.prevUsername = "";
    }
});

watch(selectedOptionDeptNext, async (newDept) => {
    if (!newDept || !newDept.code) {
        console.log("⚠️ Dept selection dibatalkan atau dikosongkan.");
        form.newInvNumberPengalihan = ""; // kosongkan field jika perlu
        return;
    }

    console.log("📥 Request params:", {
        device_type: selectedOption.value.name,
        dept: newDept.code,
        site: page.props.site,
    });

    try {
        const response = await axios.get(
            route("pengalihanAsset.generateInvNumber"),
            {
                params: {
                    device_type: selectedOption.value.name,
                    dept: newDept.code,
                    site: page.props.site,
                },
            }
        );

        console.log("🔥 Data dari backend:", response.data);

        form.newInvNumberPengalihan = response.data.inventoryData;
    } catch (error) {
        console.error("❌ Gagal Melakukan Generate Inventory Number:", error);

        Swal.fire({
            icon: "error",
            title: "Terjadi Kesalahan",
            text: "Gagal Melakukan Generate Inventory Number.",
            confirmButtonColor: "#334155",
        });
    }
});

const customLabel = (option) => `${option.name} (${option.nrp})`;

const customFilter = (option, label, search) => {
    const target = `${option.name} ${option.nrp}`.toLowerCase();
    return target.includes(search.toLowerCase());
};

watch(selectedOptionUserPengguna, async (newNrp) => {
    // Cegah error saat newNrp kosong
    if (!newNrp || !newNrp.nrp) {
        console.log("⚠️ Select dibatalkan atau dikosongkan.");

        form.nextDept = "";
        form.nextJabatan = "";
        form.nextUsername = "";
        return;
    }

    console.log("📥 Request params:", {
        nrp: newNrp.nrp,
    });

    try {
        const response = await axios.get(route("pengalihanAsset.getDataUser"), {
            params: {
                nrp: newNrp.nrp,
            },
        });

        console.log("🔥 Data dari backend:", response.data);

        form.nextDept = response.data.userData.department;
        form.nextJabatan = response.data.userData.position;
        form.nextUsername = response.data.userData.username;
    } catch (error) {
        console.error("❌ Gagal Melakukan Get Data User:", error);

        Swal.fire({
            icon: "error",
            title: "Terjadi Kesalahan",
            text: "Gagal Melakukan Get Data User.",
            confirmButtonColor: "#334155", // slate-800
        });
    }
});

const isDisabled = ref(true);

const handleFileUpload = (event) => {
    file.value = event.target.files[0];
};

const formSubmitted = ref(false);

const selected = "";

const save = () => {
    if (selectedOptionDeptNext.value?.code !== form.nextDept) {
        Swal.fire({
            icon: "warning",
            title: "Validasi Gagal!",
            text: "Department user tidak sesuai dengan yang dipilih.",
            confirmButtonText: "OK",
            confirmButtonColor: "#334155",
        });
        return; // hentikan proses simpan
    }

    console.log("Form Data:", {
        selectedOption: selectedOption.value,
        selectedOptionInvNumber: selectedOptionInvNumber.value,
        selectedOptionDeptPrev: selectedOptionDeptPrev.value,
        selectedOptionDeptNext: selectedOptionDeptNext.value,
        selectedOptionUserPengguna: selectedOptionUserPengguna.value,
        newxtJabatan: form.nextJabatan,
        nextDept: form.nextDept,
        nextUname: form.nextUsername,
        spek: form.spek,
        prevNrp: form.prevNrp,
        pevUname: form.prevUsername,
        newInvNumberPengalihan: form.newInvNumberPengalihan,
    });
    const formData = new FormData();

    formData.append("deviceType", selectedOption.value.name);
    formData.append("deptNext", selectedOptionDeptNext.value.code);
    formData.append("deptPrev", selectedOptionDeptPrev.value.code);
    formData.append("id", selectedOptionInvNumber.value.id);
    formData.append("prevNrp", form.prevNrp);
    formData.append("prevUname", form.prevUsername);
    formData.append("idInvPrev", selectedOptionInvNumber.value.id);
    formData.append("userNext", selectedOptionUserPengguna.value.nrp);
    formData.append("spek", form.spek);
    formData.append("invNumberNext", form.newInvNumberPengalihan);
    formData.append("site", page.props.site);
    formData.append("remark", form.note);
    formData.append("image", file.value);

    console.log(formData);
    Inertia.post(route("pengalihanAsset.store"), formData, {
        forceFormData: true,
        onSuccess: () => {
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
                        <a class="text-white opacity-50"
                            >Pages {{ form.assets_category }}
                        </a>
                    </li>
                    <Link
                        :href="route('laptopBib.page')"
                        class="text-sm pl-2 capitalize leading-normal text-white before:float-left before:pr-2 before:text-white before:content-['/']"
                        aria-current="page"
                    >
                        Laptop
                    </Link>
                </ol>
                <h6 class="mb-0 font-bold text-white capitalize">
                    Pengalihan Asset Edit Pages
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
                                    Form Edit Pengalihan Asset
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
                                        class="w-full max-w-full px-3 shrink-0 md:w-12/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <h5>
                                                <u>Data Asset Sebelumnya</u>
                                            </h5>
                                        </div>
                                    </div>

                                    <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-4/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="assets-category"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                                >Device Type</label
                                            >
                                            <VueMultiselect
                                                v-model="selectedOption"
                                                :options="[
                                                    { name: 'Laptop' },
                                                    { name: 'Computer' },
                                                ]"
                                                :multiple="false"
                                                :close-on-select="true"
                                                placeholder="Select Device"
                                                track-by="name"
                                                label="name"
                                                class="w-full"
                                            />
                                        </div>
                                    </div>
                                    <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-4/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="assets-category"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                                >Departement</label
                                            >
                                            <VueMultiselect
                                                v-model="selectedOptionDeptPrev"
                                                :options="optionsDept"
                                                track-by="code"
                                                label="name"
                                                :reduce="(item) => item.code"
                                                :multiple="false"
                                                :close-on-select="true"
                                                placeholder="Select Department"
                                            />
                                        </div>
                                    </div>

                                    <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-4/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="assets-category"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                                >No Inventory Device</label
                                            >
                                            <VueMultiselect
                                                v-model="
                                                    selectedOptionInvNumber
                                                "
                                                :options="optionsInvNumber"
                                                :multiple="false"
                                                :close-on-select="true"
                                                placeholder="Select Inventory Number"
                                                track-by="code"
                                                label="code"
                                            />
                                        </div>
                                    </div>

                                    <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-4/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="laptop-code"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                                >Spesifikasi</label
                                            >
                                            <textarea
                                                :disabled="isDisabled"
                                                id="message"
                                                name="note"
                                                v-model="form.spek"
                                                rows="4"
                                                class="mb-5 bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 cursor-not-allowed dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-gray-400 dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                                placeholder="Leave a note..."
                                            ></textarea>
                                        </div>
                                    </div>
                                    <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-4/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="laptop-code"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                                >NRP Pengguna Sebelumnya</label
                                            >
                                            <input
                                                :disabled="isDisabled"
                                                required
                                                type="text"
                                                name="prevNrp"
                                                v-model="form.prevNrp"
                                                class="mb-5 bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 cursor-not-allowed dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-gray-400 dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                                placeholder="Auto Generate User NRP"
                                            />
                                        </div>
                                    </div>
                                    <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-4/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="laptop-code"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                                >Nama Pengguna Sebelumnya</label
                                            >
                                            <input
                                                :disabled="isDisabled"
                                                required
                                                type="text"
                                                name="prevUsername"
                                                v-model="form.prevUsername"
                                                class="mb-5 bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 cursor-not-allowed dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-gray-400 dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                                placeholder="Auto Generate Username"
                                            />
                                        </div>
                                    </div>
                                </div>

                                <hr
                                    class="h-px mx-0 my-4 bg-transparent border-0 opacity-25 bg-gradient-to-r from-transparent via-black/40 to-transparent dark:bg-gradient-to-r dark:from-transparent dark:via-white dark:to-transparent"
                                />
                                <div class="flex flex-wrap -mx-3">
                                    <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-12/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <h5>
                                                <u>Data Asset Pengalihan</u>
                                            </h5>
                                        </div>
                                    </div>

                                    <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-4/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="assets-category"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                                >Device Type</label
                                            >
                                            <VueMultiselect
                                                v-model="selectedOption"
                                                :options="[
                                                    { name: 'Laptop' },
                                                    { name: 'Computer' },
                                                ]"
                                                :multiple="false"
                                                :close-on-select="true"
                                                placeholder="Select Device"
                                                track-by="name"
                                                label="name"
                                                class="w-full"
                                            />
                                        </div>
                                    </div>
                                    <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-4/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="assets-category"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                                >Departement Pengalihan</label
                                            >
                                            <VueMultiselect
                                                v-model="selectedOptionDeptNext"
                                                :multiple="false"
                                                :options="optionsDept"
                                                :close-on-select="true"
                                                placeholder="Select Departement"
                                                track-by="name"
                                                label="name"
                                            />
                                        </div>
                                    </div>

                                    <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-4/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="laptop-code"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                                >No Inventory Pengalihan</label
                                            >
                                            <input
                                                :disabled="isDisabled"
                                                required
                                                type="text"
                                                name="newInvNumberPengalihan"
                                                v-model="
                                                    form.newInvNumberPengalihan
                                                "
                                                class="mb-5 bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 cursor-not-allowed dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-gray-400 dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                                placeholder="Auto Generate User NRP"
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
                                                >Select User Pengguna</label
                                            >
                                            <VueMultiselect
                                                v-model="
                                                    selectedOptionUserPengguna
                                                "
                                                :options="[
                                                    { name: 'Laptop' },
                                                    { name: 'Computer' },
                                                ]"
                                                :multiple="false"
                                                :close-on-select="true"
                                                placeholder="Select User Pengguna"
                                                track-by="nrp"
                                                label="name"
                                                :reduce="(user) => user.nrp"
                                                :custom-label="customLabel"
                                                :filter-method="customFilter"
                                            >
                                                <!-- <template #option="{ option }">
                                                    {{ option.name }} ({{
                                                        option.nrp
                                                    }})
                                                </template>

                                                <template
                                                    #singleLabel="{ option }"
                                                >
                                                    {{ option.name }} ({{
                                                        option.nrp
                                                    }})
                                                </template> -->
                                            </VueMultiselect>
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
                                        class="w-full max-w-full px-3 shrink-0 md:w-4/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="laptop-code"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                                >Nama Pengguna Pengalihan</label
                                            >
                                            <input
                                                :disabled="isDisabled"
                                                required
                                                type="text"
                                                name="nextUsername"
                                                v-model="form.nextUsername"
                                                class="mb-5 bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 cursor-not-allowed dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-gray-400 dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                                placeholder="Auto Generate Username"
                                            />
                                        </div>
                                    </div>
                                    <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-4/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="laptop-code"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                                >Departement Pengguna
                                                Pengalihan</label
                                            >
                                            <input
                                                :disabled="isDisabled"
                                                required
                                                type="text"
                                                name="nextDept"
                                                v-model="form.nextDept"
                                                class="mb-5 bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 cursor-not-allowed dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-gray-400 dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                                placeholder="Auto Generate User NRP"
                                            />
                                        </div>
                                    </div>
                                    <div
                                        class="w-full max-w-full px-3 shrink-0 md:w-4/12 md:flex-0"
                                    >
                                        <div class="mb-4">
                                            <label
                                                for="laptop-code"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                                >Jabatan Pengalihan</label
                                            >
                                            <input
                                                :disabled="isDisabled"
                                                required
                                                type="text"
                                                name="prevUsername"
                                                v-model="form.nextJabatan"
                                                class="mb-5 bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 cursor-not-allowed dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-gray-400 dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                                placeholder="Auto Generate Username"
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
                                <div
                                    class="flex flex-nowrap mt-6 justify-between"
                                >
                                    <Link
                                        :href="
                                            route('pengalihanAsset.page', {
                                                site: page.props.site,
                                            })
                                        "
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
