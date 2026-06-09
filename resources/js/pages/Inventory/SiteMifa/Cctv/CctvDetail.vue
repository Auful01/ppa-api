<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import NavLinkCustom from "@/Components/NavLinkCustom.vue";
import { Link } from "@inertiajs/vue3";
import moment from "moment";
import { onMounted, ref } from "vue";

const pages = ref("Pages");
const subMenu = ref("Cctv Pages");
const mainMenu = ref("Detail Cctv");

const props = defineProps(["cctv", "aduan"]);

// Fungsi untuk format tanggal
function formattedDate(date) {
    return moment(date).format("MMMM Do, YYYY"); // Sesuaikan format sesuai kebutuhan
}

const mount = onMounted(() => {
    $("#tableData").DataTable();
});
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout
        v-model:pages="pages"
        v-model:subMenu="subMenu"
        v-model:mainMenu="mainMenu"
    >

        <div class="py-12">
            <div class="min-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="flex flex-wrap -mx-3">
                    <div
                        class="w-full max-w-full px-3 mt-6 md:w-7/12 md:flex-none"
                    >
                        <div
                            class="relative flex flex-col min-w-0 break-words bg-white border-0 shadow-xl dark:bg-slate-850 dark:shadow-dark-xl rounded-2xl bg-clip-border"
                        >
                            <div
                                class="flex flex-row p-6 px-4 pb-0 mb-0 border-b-0 rounded-t-2xl"
                            >
                                <h6 class="mb-0 mr-3 dark:text-white">
                                    Detail Cctv
                                </h6>
                                <NavLinkCustom
                                    class="text-red-700"
                                    :href="route('cctvMifa.page')"
                                >
                                    Move to home page
                                </NavLinkCustom>
                            </div>
                            <div class="flex-auto p-4 pt-6">
                                <div class="grid grid-cols-2">
                                    <div>
                                        <p class="text-base">Iventory Number</p>
                                    </div>
                                    <div>
                                        <p>: {{ cctv.cctv_code }}</p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2">
                                    <div>
                                        <p class="text-base">Asset Ho Number</p>
                                    </div>
                                    <div>
                                        <p>: {{ cctv.asset_ho_number }}</p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2">
                                    <div>
                                        <p class="text-base">Komputer Name</p>
                                    </div>
                                    <div>
                                        <p>: {{ cctv.cctv_name }}</p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2">
                                    <div>
                                        <p class="text-base">Cctv Brand</p>
                                    </div>
                                    <div>
                                        <p>: {{ cctv.cctv_brand }}</p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2">
                                    <div>
                                        <p class="text-base">Cctv Model</p>
                                    </div>
                                    <div>
                                        <p>: {{ cctv.cctv_model }}</p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2">
                                    <div>
                                        <p class="text-base">Type Cctv</p>
                                    </div>
                                    <div>
                                        <p>: {{ cctv.type_cctv }}</p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2">
                                    <div>Mac Address</div>
                                    <div>
                                        <p>: {{ cctv.mac_address }}</p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2">
                                    <div>
                                        <p class="text-base">ip_address</p>
                                    </div>
                                    <div>
                                        <p>: {{ cctv.ip_address }}</p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2">
                                    <div>
                                        <p class="text-base">Vlan</p>
                                    </div>
                                    <div>
                                        <p>: {{ cctv.vlan }}</p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2">
                                    <div>
                                        <p class="text-base">Nvr Name</p>
                                    </div>
                                    <div>
                                        <p>
                                            :
                                            {{ cctv.nvr_id }}
                                        </p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2">
                                    <div>
                                        <p class="text-base">Switch Name</p>
                                    </div>
                                    <div>
                                        <p>
                                            :
                                            {{ cctv.switch_id }}
                                        </p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2">
                                    <div>
                                        <p class="text-base">Uplink</p>
                                    </div>
                                    <div>
                                        <p>: {{ cctv.uplink }}</p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2">
                                    <div>
                                        <p class="text-base">Location</p>
                                    </div>
                                    <div>
                                        <p>: {{ cctv.location }}</p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2">
                                    <div>
                                        <p class="text-base">Location Detail</p>
                                    </div>
                                    <div>
                                        <p>: {{ cctv.location_detail }}</p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2">
                                    <div>
                                        <p class="text-base">
                                            Date Of Inventory
                                        </p>
                                    </div>
                                    <div>
                                        <p>
                                            :
                                            {{
                                                formattedDate(
                                                    cctv.date_of_inventory
                                                )
                                            }}
                                        </p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2">
                                    <div>
                                        <p class="text-base">Status</p>
                                    </div>
                                    <div>
                                        <p>: {{ cctv.status }}</p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2">
                                    <div>
                                        <p class="text-base">Note</p>
                                    </div>
                                    <div>
                                        <p>: {{ cctv.note }}</p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2">
                                    <div>
                                        <p class="text-base">Last Edit At</p>
                                    </div>
                                    <div>
                                        <p>
                                            :
                                            {{ formattedDate(cctv.updated_at) }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        class="w-full max-w-full px-3 mt-6 md:w-5/12 md:flex-none"
                    >
                        <div
                            class="relative flex flex-col h-full min-w-0 mb-6 break-words bg-white border-0 shadow-xl dark:bg-slate-850 dark:shadow-dark-xl rounded-2xl bg-clip-border"
                        >
                            <div
                                class="p-6 px-4 pb-0 mb-0 border-b-0 rounded-t-2xl"
                            >
                                <div class="flex flex-wrap -mx-3 mb-6">
                                    <div
                                        class="max-w-full px-3 md:w-1/2 md:flex-none"
                                    >
                                        <h6 class="mb-0 dark:text-white">
                                            History Trouble CCTV by Complaint
                                        </h6>
                                    </div>
                                    <div
                                        class="flex items-center justify-end max-w-full px-3 dark:text-white/80 md:w-1/2 md:flex-none"
                                    >
                                        <small>last maintenance: </small>
                                        <small class="ml-2"
                                            >23 - 30 March 2020</small
                                        >
                                    </div>
                                </div>
                            </div>
                            <div class="flex-none w-full max-w-full px-3">
                                <div
                                    class="relative flex flex-col min-w-0 mb-6 break-words bg-white border-0 border-transparent border-solid shadow-xl dark:bg-slate-850 dark:shadow-dark-xl rounded-2xl bg-clip-border"
                                >
                                    <div class="flex-auto px-0 pt-0 pb-2">
                                        <div class="p-0 overflow-x-auto">
                                            <div class="p-6 text-gray-900">
                                                <table
                                                    id="tableData"
                                                    class="table table-striped"
                                                >
                                                    <thead class="align-bottom">
                                                        <tr>
                                                            <th
                                                                class="px-6 py-3 pl-2 font-bold text-left uppercase align-middle mb-0 text-sm leading-tight dark:text-white dark:opacity-80"
                                                            >
                                                                #
                                                            </th>
                                                            <th
                                                                class="px-6 py-3 pl-2 font-bold text-left uppercase align-middle mb-0 text-sm leading-tight dark:text-white dark:opacity-80"
                                                            >
                                                                Inventory Number
                                                            </th>
                                                            <th
                                                                class="px-6 py-3 font-bold text-center uppercase align-middle mb-0 text-sm leading-tight dark:text-white dark:opacity-80"
                                                            >
                                                                Issue
                                                            </th>
                                                            <th
                                                                class="px-6 py-3 font-bold text-center uppercase align-middle mb-0 text-sm leading-tight dark:text-white dark:opacity-80"
                                                            >
                                                                Root Cause
                                                            </th>
                                                            <th
                                                                class="px-6 py-3 font-bold text-center uppercase align-middle mb-0 text-sm leading-tight dark:text-white dark:opacity-80"
                                                            >
                                                                Action Root
                                                                Cause
                                                            </th>
                                                            <th
                                                                class="px-6 py-3 font-bold text-center uppercase align-middle mb-0 text-sm leading-tight dark:text-white dark:opacity-80"
                                                            >
                                                                Note
                                                            </th>
                                                            <th
                                                                class="px-6 py-3 font-bold text-center uppercase align-middle mb-0 text-sm leading-tight dark:text-white dark:opacity-80"
                                                            >
                                                                Issue Date
                                                            </th>
                                                            <th
                                                                class="px-6 py-3 font-bold text-center uppercase align-middle mb-0 text-sm leading-tight dark:text-white dark:opacity-80"
                                                            >
                                                                Action Date
                                                            </th>
                                                            <th
                                                                class="px-6 py-3 font-bold text-center uppercase align-middle mb-0 text-sm leading-tight dark:text-white dark:opacity-80"
                                                            >
                                                                Status
                                                            </th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr
                                                            v-for="(
                                                                aduans, index
                                                            ) in aduan"
                                                            :key="index"
                                                        >
                                                            <td
                                                                class="p-2 text-center align-middle bg-transparent border-b dark:border-white/40 whitespace-nowrap shadow-transparent"
                                                            >
                                                                <span
                                                                    class="mb-0 text-sm font-semibold leading-tight dark:text-white dark:opacity-80"
                                                                >
                                                                    {{
                                                                        index +
                                                                        1
                                                                    }}
                                                                </span>
                                                            </td>
                                                            <td
                                                                class="p-2 text-center align-middle bg-transparent border-b dark:border-white/40 whitespace-nowrap shadow-transparent"
                                                            >
                                                                <span
                                                                    class="mb-0 text-sm font-semibold leading-tight dark:text-white dark:opacity-80"
                                                                >
                                                                    {{
                                                                        aduans.inventory_number
                                                                    }}
                                                                </span>
                                                            </td>
                                                            <td
                                                                class="p-2 align-middle bg-transparent border-b dark:border-white/40 whitespace-nowrap shadow-transparent"
                                                            >
                                                                <p
                                                                    class="mb-0 text-sm font-semibold leading-tight dark:text-white dark:opacity-80"
                                                                >
                                                                    {{
                                                                        aduans.complaint_note
                                                                    }}
                                                                </p>
                                                            </td>
                                                            <td
                                                                class="p-2 align-middle bg-transparent border-b dark:border-white/40 whitespace-nowrap shadow-transparent"
                                                            >
                                                                <p
                                                                    class="mb-0 text-sm font-semibold leading-tight dark:text-white dark:opacity-80"
                                                                >
                                                                    {{
                                                                        aduans.root_cause
                                                                    }}
                                                                </p>
                                                            </td>
                                                            <td
                                                                class="p-2 align-middle bg-transparent border-b dark:border-white/40 whitespace-nowrap shadow-transparent"
                                                            >
                                                                <p
                                                                    class="mb-0 text-sm font-semibold leading-tight dark:text-white dark:opacity-80"
                                                                >
                                                                    {{
                                                                        aduans.action_repair
                                                                    }}
                                                                </p>
                                                            </td>
                                                            <td
                                                                class="p-2 align-middle bg-transparent border-b dark:border-white/40 whitespace-nowrap shadow-transparent"
                                                            >
                                                                <p
                                                                    class="mb-0 text-sm font-semibold leading-tight dark:text-white dark:opacity-80"
                                                                >
                                                                    {{
                                                                        aduans.repair_note
                                                                    }}
                                                                </p>
                                                            </td>
                                                            <td
                                                                class="p-2 text-center align-middle bg-transparent border-b dark:border-white/40 whitespace-nowrap shadow-transparent"
                                                            >
                                                                <span
                                                                    class="mb-0 text-sm font-semibold leading-tight dark:text-white dark:opacity-80"
                                                                >
                                                                    {{
                                                                        formattedDate(
                                                                            aduans.date_of_complaint
                                                                        )
                                                                    }}
                                                                </span>
                                                            </td>
                                                            <td
                                                                class="p-2 text-center align-middle bg-transparent border-b dark:border-white/40 whitespace-nowrap shadow-transparent"
                                                            >
                                                                <span
                                                                    class="mb-0 text-sm font-semibold leading-tight dark:text-white dark:opacity-80"
                                                                >
                                                                    {{
                                                                        formattedDate(
                                                                            aduans.start_progress
                                                                        )
                                                                    }}
                                                                </span>
                                                            </td>
                                                            <td
                                                                class="p-2 text-center align-middle bg-transparent border-b dark:border-white/40 whitespace-nowrap shadow-transparent"
                                                            >
                                                                <span
                                                                    class="mb-0 text-sm font-semibold leading-tight dark:text-white dark:opacity-80"
                                                                >
                                                                    {{
                                                                        aduans.status
                                                                    }}
                                                                </span>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
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
