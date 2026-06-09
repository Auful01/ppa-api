<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import NavLinkCustom from "@/Components/NavLinkCustom.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import moment from "moment";
import Swal from "sweetalert2";
import { onMounted, ref } from "vue";

const pages = ref("Pages");
const subMenu = ref("Pica Inspeksi Pages");
const mainMenu = ref("Detail Pica Inspeksi");

const props = defineProps(["dataInspeksi"]);

function formattedDate(date) {
    return moment(date).format("MMMM Do, YYYY"); // Sesuaikan format sesuai kebutuhan
}
</script>

<template>
    <Head title="Detail Inspeksi" />

    <AuthenticatedLayout
        v-model:pages="pages"
        v-model:subMenu="subMenu"
        v-model:mainMenu="mainMenu"
    >
        <div class="py-12">
            <div class="min-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="flex flex-wrap -mx-3">
                    <div
                        class="w-full max-w-full px-3 mt-6 md:w-12/12 md:flex-none"
                    >
                        <div
                            class="relative flex flex-col min-w-0 break-words bg-white border-0 shadow-xl dark:bg-slate-850 dark:shadow-dark-xl rounded-2xl bg-clip-border"
                        >
                            <div
                                class="flex flex-row p-6 px-4 pb-0 mb-0 border-b-0 rounded-t-2xl"
                            >
                                <h6 class="mb-0 mr-3 dark:text-white">
                                    Detail Pica Inspeksi
                                </h6>
                                <NavLinkCustom
                                    class="text-red-700"
                                    :href="
                                        route('picaInspeksi.page', {
                                            site: 'BA',
                                        })
                                    "
                                >
                                    Move to home page
                                </NavLinkCustom>
                            </div>
                            <div class="flex flex-wrap -mx-3 p-12">
                                <div
                                    class="border-black/12.5 rounded-t-2xl border-b-0 border-solid p-6 pb-0"
                                >
                                    <div class="flex items-center">
                                        <p
                                            class="mb-0 font-black dark:text-white/80"
                                        >
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
                                                <div class="grid grid-cols-2">
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
                                                                dataInspeksi.device_pengguna
                                                            }}
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="grid grid-cols-2">
                                                    <div>
                                                        <p class="text-base">
                                                            Dept
                                                        </p>
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
                                                        <p v-if="dataInspeksi.device_type === 'Laptop'" class="text-base">
                                                            Laptop Name
                                                        </p>
                                                        <p v-if="dataInspeksi.device_type === 'Computer'" class="text-base">
                                                            Computer Name
                                                        </p>
                                                        <p v-if="dataInspeksi.device_type === 'Printer'" class="text-base">
                                                            Printer Name
                                                        </p>
                                                        <p v-if="dataInspeksi.device_type === 'Mobile Tower'" class="text-base">
                                                            Mobile Tower Name
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
                                                <div class="grid grid-cols-2">
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
                                                <div class="grid grid-cols-2">
                                                    <div>
                                                        <p class="text-base">
                                                            Serial Number
                                                        </p>
                                                    </div>
                                                    <div>
                                                        <p>
                                                            :
                                                            {{
                                                                dataInspeksi.device_sn
                                                            }}
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
                                                        <p class="text-base">
                                                            Status
                                                        </p>
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
                                                        <p class="text-base">
                                                            Note
                                                        </p>
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
                                <!-- <div
                                    class="border-black/12.5 rounded-t-2xl border-b-0 border-solid p-6 pb-0"
                                >
                                    <div class="flex items-center">
                                        <p
                                            class="mb-0 font-black dark:text-white/80"
                                        >
                                            USER & ASSET DETAIL
                                        </p>
                                    </div>
                                </div> -->
                                <div
                                    class="border-black/12.5 rounded-t-2xl border-b-0 border-solid p-6 pb-0 w-full max-w-full px-3 shrink-0 md:w-12/12 md:flex-0"
                                >
                                 <div class="flex items-center">
                                        <p
                                            class="mb-10 font-black dark:text-white/80"
                                        >
                                            USER & ASSET DETAIL
                                        </p>
                                    </div>
                                </div>

                                <div
                                    class="w-full max-w-full px-3 shrink-0 md:w-4/12 md:flex-0"
                                >
                                    <div class="mb-4">
                                        <label
                                            for="temuan"
                                            class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                            >Deskripsi Temuan</label
                                        >
                                    </div>
                                </div>
                                <div
                                    class="w-full max-w-full px-3 shrink-0 md:w-6/12 md:flex-0"
                                >
                                    <div class="mb-4">
                                        <label
                                            for="device-name"
                                            class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80 font-bold"
                                        >
                                            :
                                            {{
                                                dataInspeksi.findings == null
                                                    ? "-"
                                                    : dataInspeksi.findings
                                            }}</label
                                        >
                                    </div>
                                </div>

                                <div
                                    class="w-full max-w-full px-3 shrink-0 md:w-4/12 md:flex-0"
                                >
                                    <div class="mb-4">
                                        <label
                                            for="findings_image"
                                            class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                            >Lampiran Foto Temuan</label
                                        >
                                    </div>
                                </div>
                                <div
                                    class="w-full max-w-full px-3 shrink-0 md:w-6/12 md:flex-0"
                                >
                                    <div
                                        v-if="
                                            dataInspeksi.findings_image != null
                                        "
                                        class="mb-4"
                                    >
                                        <img
                                            :src="dataInspeksi.findings_image"
                                            alt="Foto Temuan"
                                            class="w-50 h-30 shadow-2xl rounded-xl"
                                        />
                                    </div>
                                </div>

                                <div
                                    class="w-full max-w-full px-3 shrink-0 md:w-4/12 md:flex-0"
                                >
                                    <div class="mb-4">
                                        <label
                                            for="tindakan"
                                            class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                            >Tindak Lanjut</label
                                        >
                                    </div>
                                </div>
                                <div
                                    class="w-full max-w-full px-3 shrink-0 md:w-6/12 md:flex-0"
                                >
                                    <div class="mb-4">
                                        <label
                                            for="device-name"
                                            class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80 font-bold"
                                        >
                                            :
                                            {{
                                                dataInspeksi.findings_action ==
                                                null
                                                    ? "-"
                                                    : dataInspeksi.findings_action
                                            }}</label
                                        >
                                    </div>
                                </div>

                                <div
                                    class="w-full max-w-full px-3 shrink-0 md:w-4/12 md:flex-0"
                                >
                                    <div class="mb-4">
                                        <label
                                            for="action_image"
                                            class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                            >Lampiran Foto Tindakan</label
                                        >
                                    </div>
                                </div>
                                <div
                                    class="w-full max-w-full px-3 shrink-0 md:w-6/12 md:flex-0"
                                >
                                    <div
                                        v-if="dataInspeksi.action_image != null"
                                        class="mb-4"
                                    >
                                        <img
                                            :src="dataInspeksi.action_image"
                                            alt="Foto Tindakan"
                                            class="w-50 h-30 shadow-2xl rounded-xl"
                                        />
                                    </div>
                                </div>

                                <div
                                    class="w-full max-w-full px-3 shrink-0 md:w-4/12 md:flex-0"
                                >
                                    <div class="mb-4">
                                        <label
                                            for="date-of-inventory"
                                            class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                            >Due Date</label
                                        >
                                    </div>
                                </div>
                                <div
                                    class="w-full max-w-full px-3 shrink-0 md:w-6/12 md:flex-0"
                                >
                                    <div class="mb-4">
                                        <label
                                            for="device-name"
                                            class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80 font-bold"
                                        >
                                            :
                                            {{
                                                dataInspeksi.due_date == null
                                                    ? "-"
                                                    : formattedDate(
                                                          dataInspeksi.due_date
                                                      )
                                            }}</label
                                        >
                                    </div>
                                </div>

                                <div
                                    class="w-full max-w-full px-3 shrink-0 md:w-4/12 md:flex-0"
                                >
                                    <div class="mb-4">
                                        <label
                                            for="findings_status"
                                            class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                        >
                                            Status Temuan</label
                                        >
                                    </div>
                                </div>
                                <div
                                    class="w-full max-w-full px-3 shrink-0 md:w-6/12 md:flex-0"
                                >
                                    <div class="mb-4">
                                        <label
                                            for="device-name"
                                            class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80 font-bold"
                                        >
                                            :
                                            {{
                                                dataInspeksi.findings_status ==
                                                null
                                                    ? "-"
                                                    : dataInspeksi.findings_status
                                            }}</label
                                        >
                                    </div>
                                </div>

                                <div
                                    class="w-full max-w-full px-3 shrink-0 md:w-4/12 md:flex-0"
                                >
                                    <div class="mb-4">
                                        <label
                                            for="device-location"
                                            class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                            >Remark</label
                                        >
                                    </div>
                                </div>
                                <div
                                    class="w-full max-w-full px-3 shrink-0 md:w-6/12 md:flex-0"
                                >
                                    <div class="mb-4">
                                        <label
                                            for="device-name"
                                            class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80 font-bold"
                                        >
                                            :
                                            {{
                                                dataInspeksi.remarks == null
                                                    ? "-"
                                                    : dataInspeksi.remarks
                                            }}</label
                                        >
                                    </div>
                                </div>

                                <div
                                    class="w-full max-w-full px-3 shrink-0 md:w-4/12 md:flex-0"
                                >
                                    <div class="mb-4">
                                        <label
                                            for="select_pic"
                                            class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                        >
                                            PIC</label
                                        >
                                    </div>
                                </div>
                                <div
                                    class="w-full max-w-full px-3 shrink-0 md:w-6/12 md:flex-0"
                                >
                                    <div class="mb-4">
                                        <label
                                            for="device-name"
                                            class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80 font-bold"
                                        >
                                            :
                                            {{
                                                dataInspeksi.inspector == ""
                                                    ? "-"
                                                    : dataInspeksi.inspector
                                            }}</label
                                        >
                                    </div>
                                </div>

                                <div
                                    class="w-full max-w-full px-3 shrink-0 md:w-4/12 md:flex-0"
                                >
                                    <div class="mb-4">
                                        <label
                                            for="inspection_image"
                                            class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80"
                                            >Lampiran Foto Inspeksi</label
                                        >
                                    </div>
                                </div>
                                <div
                                    class="w-full max-w-full px-3 shrink-0 md:w-6/12 md:flex-0"
                                >
                                    <div
                                        v-if="
                                            dataInspeksi.inspection_image !=
                                            null
                                        "
                                        class="mb-4"
                                    >
                                        <img
                                            :src="dataInspeksi.inspection_image"
                                            alt="Foto Inspeksi"
                                            class="w-50 h-30 shadow-2xl rounded-xl"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
