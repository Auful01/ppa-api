<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import NavLinkCustom from "@/Components/NavLinkCustom.vue";
import { Link } from "@inertiajs/vue3";
import moment from "moment";
import { onMounted } from "vue";

const props = defineProps(["laptop", "aduan", "inspeksi", "unschedule"]);

// Fungsi untuk format tanggal
function formattedDate(date) {
    return moment(date).format("MMMM Do, YYYY"); // Sesuaikan format sesuai kebutuhan
}

const mount = onMounted(() => {
    // Inisialisasi DataTable tanpa AJAX
    $("#tableData").DataTable();
    $("#tableData2").DataTable();
    $("#tableData3").DataTable();
});
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <nav>
                <!-- breadcrumb -->
                <ol
                    class="flex flex-wrap pt-1 mr-12 bg-transparent rounded-lg sm:mr-16"
                >
                    <li class="text-sm leading-normal">
                        <a class="text-white opacity-50" href="javascript:;"
                            >Pages</a
                        >
                    </li>
                    <li
                        class="text-sm pl-2 capitalize leading-normal text-white before:float-left before:pr-2 before:text-white before:content-['/']"
                        aria-current="page"
                    >
                        Laptop Pages
                    </li>
                </ol>
                <h6 class="mb-0 font-bold text-white capitalize">
                    Detail Laptop
                </h6>
            </nav>
        </template>

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
                                    Detail Laptop
                                </h6>
                                <NavLinkCustom class="text-red-700" :href="route('laptop.page')">
                                    Move to home page
                                </NavLinkCustom>
                            </div>
                            <div class="flex-auto p-4 pt-6">
                                <div class="grid grid-cols-2">
                                    <div>
                                        <p class="text-base">Iventory Number</p>
                                    </div>
                                    <div>
                                        <p>: {{ laptop.laptop_code }}</p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2">
                                    <div>
                                        <p class="text-base">Asset Ho Number</p>
                                    </div>
                                    <div>
                                        <p>: {{ laptop.number_asset_ho }}</p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2">
                                    <div>
                                        <p class="text-base">Laptop Name</p>
                                    </div>
                                    <div>
                                        <p>: {{ laptop.laptop_name }}</p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2">
                                    <div>
                                        <p class="text-base">Category Asset</p>
                                    </div>
                                    <div>
                                        <p>: {{ laptop.assets_category }}</p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2">
                                    <div>
                                        <p class="text-base">Spesifikasi</p>
                                    </div>
                                    <div>
                                        <p>: {{ laptop.spesifikasi }}</p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2">
                                    <div>
                                        <p class="text-base">Serial Number</p>
                                    </div>
                                    <div>
                                        <p>: {{ laptop.serial_number }}</p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2">
                                    <div>
                                        <p class="text-base">Aplikasi</p>
                                    </div>
                                    <div>
                                        <p>: {{ laptop.aplikasi }}</p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2">
                                    <div>
                                        <p class="text-base">License</p>
                                    </div>
                                    <div>
                                        <p>: {{ laptop.license }}</p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2">
                                    <div>
                                        <p class="text-base">Ip Address</p>
                                    </div>
                                    <div>
                                        <p>: {{ laptop.ip_address }}</p>
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
                                                    laptop.date_of_inventory
                                                )
                                            }}
                                        </p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2">
                                    <div>
                                        <p class="text-base">Date Of Deploy</p>
                                    </div>
                                    <div>
                                        <p>
                                            :
                                            {{
                                                formattedDate(
                                                    laptop.date_of_deploy
                                                )
                                            }}
                                        </p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2">
                                    <div>
                                        <p class="text-base">Location</p>
                                    </div>
                                    <div>
                                        <p>: {{ laptop.location }}</p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2">
                                    <div>
                                        <p class="text-base">Status</p>
                                    </div>
                                    <div>
                                        <p>: {{ laptop.status }}</p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2">
                                    <div>
                                        <p class="text-base">Condition</p>
                                    </div>
                                    <div>
                                        <p>: {{ laptop.condition }}</p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2">
                                    <div>
                                        <p class="text-base">Note</p>
                                    </div>
                                    <div>
                                        <p>: {{ laptop.note }}</p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2">
                                    <div>
                                        <p class="text-base">
                                            Documentation Asset
                                        </p>
                                    </div>
                                    <div>
                                        <p>
                                            :
                                            <img
                                                :src="
                                                    laptop.link_documentation_asset_image
                                                "
                                                alt="documentation image"
                                                class="h-200 w-200 shadow-2xl rounded-xl"
                                            />
                                        </p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2">
                                    <div>
                                        <p class="text-base">Last Edit At</p>
                                    </div>
                                    <div>
                                        <p>
                                            :
                                            {{
                                                formattedDate(laptop.updated_at)
                                            }}
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
                                            History Trouble Laptop by Complaint
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

                    <!-- start table inspeksi -->
                    <div
                        class="w-full max-w-full px-3 mt-6 md:w-6/12 md:flex-none"
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
                                            History Inspeksi Laptop
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
                                                    id="tableData2"
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
                                                                Condition
                                                            </th>
                                                            <th
                                                                class="px-6 py-3 font-bold text-center uppercase align-middle mb-0 text-sm leading-tight dark:text-white dark:opacity-80"
                                                            >
                                                                Inspector
                                                            </th>
                                                            <th
                                                                class="px-6 py-3 font-bold text-center uppercase align-middle mb-0 text-sm leading-tight dark:text-white dark:opacity-80"
                                                            >
                                                                Inspection
                                                                Status
                                                            </th>
                                                            <th
                                                                class="px-6 py-3 font-bold text-center uppercase align-middle mb-0 text-sm leading-tight dark:text-white dark:opacity-80"
                                                            >
                                                                Inspection At
                                                            </th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr
                                                            v-for="(
                                                                inspeksis, index
                                                            ) in inspeksi"
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
                                                                        inspeksis
                                                                            .inventory
                                                                            .laptop_code
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
                                                                        inspeksis.condition
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
                                                                        inspeksis.inspector
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
                                                                        inspeksis.inspection_status
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
                                                                            inspeksis.inspection_at
                                                                        )
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
                    <!-- end table inspeksi -->

                    <!-- start table unschedule -->
                    <div
                        class="w-full max-w-full px-3 mt-6 md:w-6/12 md:flex-none"
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
                                            History Unschedule Job Trouble
                                            Laptop
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
                                                    id="tableData3"
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
                                                                Start Progress
                                                            </th>
                                                            <th
                                                                class="px-6 py-3 font-bold text-center uppercase align-middle mb-0 text-sm leading-tight dark:text-white dark:opacity-80"
                                                            >
                                                                End Progress
                                                            </th>
                                                            <th
                                                                class="px-6 py-3 font-bold text-center uppercase align-middle mb-0 text-sm leading-tight dark:text-white dark:opacity-80"
                                                            >
                                                                Crew
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
                                                                unschedules, index
                                                            ) in unschedule"
                                                            :key="index"
                                                        >
                                                        <td
                                                                class="p-2 text-center align-middle bg-transparent border-b dark:border-white/40 whitespace-nowrap shadow-transparent"
                                                            >
                                                                <span
                                                                    class="mb-0 text-sm font-semibold leading-tight dark:text-white dark:opacity-80"
                                                                >
                                                                    {{
                                                                        index+1
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
                                                                       unschedules.inventory_number
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
                                                                       unschedules.issue
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
                                                                       unschedules.root_cause
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
                                                                       unschedules.action
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
                                                                       unschedules.remark
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
                                                                           unschedules.start_progress
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
                                                                           unschedules.end_progress
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
                                                                       unschedules.crew
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
                                                                       unschedules.status
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
                    <!-- end table unschedule -->
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
