<style>
@import 'datatables.net-dt';

.dt-search {
    margin-bottom: 1em;
    float: right !important;
    text-align: center !important;

}
.dt-paging {
    margin-top: 1em;
    float: right !important;
    text-align: right !important;
}
.dt-buttons {
    margin-top: 1em;
}
</style>

<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import NavLinkCustom from "@/Components/NavLinkCustom.vue";
import moment from "moment";
import Swal from "sweetalert2";
import { ref } from "vue";
import { Inertia } from "@inertiajs/inertia";
import { onMounted } from "vue";
import { PerfectScrollbar } from 'vue3-perfect-scrollbar';

const pages = ref("Pages");
const subMenu = ref("Printer Pages");
const mainMenu = ref("Printer Data");

// Fungsi untuk format tanggal
function formattedDate(date) {
    return moment(date).format("MMMM Do, YYYY"); // Sesuaikan format sesuai kebutuhan
}

const mount = onMounted(() => {
    // Inisialisasi DataTable tanpa AJAX
    $("#tableData").DataTable({
        dom: 'fBrtilp',
        buttons: [
                {
                    extend: 'spacer',
                    style: 'bar',
                    text: 'Export files:'
                },
                'csvHtml5',
                'excelHtml5',
                'spacer'
            ],
        initComplete: function () {
            var btns = $('.dt-button');
            btns.addClass('text-white bg-gradient-to-r from-green-600 via-green-700 to-green-900 hover:bg-gradient-to-br focus:ring-4 focus:outline-none focus:ring-green-300 dark:focus:ring-green-800 font-medium rounded-lg text-sm px-5 py-2.5 text-center me-2 mb-2');
            btns.removeClass('dt-button');

        }
    });
});

const props = defineProps({
    printer: {
        type: Array,
    },
    site: {
        type: Object,
    },
    role: {
        type: Object,
    },
});

const form = useForm({});

const restore = (id) => {
    // Call SweetAlert for confirmation
    Swal.fire({
        title: "Are you sure?",
        text: "You won't be able to restore this!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, resore it!",
    }).then((result) => {
        if (result.isConfirmed) {
            // Perform the delete operation, e.g., by making a request to the server
            form.patch(route("printerRcBin.restore", { id: id }), {
                onSuccess: () => {
                    Swal.fire({
                        title: "Restored!",
                        text: "Your file has been restore.",
                        icon: "success",
                        confirmButtonColor: "#3085d6",
                    });
                },
            });
        }
    });
};

const forceDeleted = (id) => {
    // Call SweetAlert for confirmation
    Swal.fire({
        title: "Are you sure?",
        text: "You won't be able to revert this!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, delete it!",
    }).then((result) => {
        if (result.isConfirmed) {
            // Perform the delete operation, e.g., by making a request to the server
            form.delete(route("printerRcBin.forceDelete", { id: id }), {
                onSuccess: () => {
                    Swal.fire({
                        title: "Deleted!",
                        text: "Your file has been deleted.",
                        icon: "success",
                        confirmButtonColor: "#3085d6",
                    });
                },
            });
        }
    });
};

const detailData = (id) => {
    form.get(route("printerRcBin.detail", { id: id }));
};
</script>

<template>
    <Head title="RecycleBin Inv Printer" />

    <AuthenticatedLayout
        v-model:pages="pages"
        v-model:subMenu="subMenu"
        v-model:mainMenu="mainMenu"
    >
        <div class="py-12">
            <div class="min-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="flex flex-wrap -mx-3">

                    <div class="flex-none w-full max-w-full px-3">
                        <div
                            class="relative flex flex-col min-w-0 mb-6 break-words bg-white border-0 border-transparent border-solid shadow-xl dark:bg-slate-850 dark:shadow-dark-xl rounded-2xl bg-clip-border"
                        >

                            <div class="flex-auto px-0 pt-0 pb-2">
                                <PerfectScrollbar style="position: relative;">
                                    <div class="p-0">
                                        <div class="p-6 text-gray-900">
                                            <div
                                                v-if="$page.props.flash.message"
                                                class="relative w-full p-4 mb-4 text-white border border-solid rounded-lg bg-gradient-to-tl from-emerald-500 to-teal-400 border-emerald-300"
                                            >
                                                {{ $page.props.flash.message }}
                                            </div>
                                            <table
                                                id="tableData"
                                                class="table table-striped"
                                            >
                                                <thead class="align-bottom">
                                                    <tr>
                                                        <th
                                                            class="px-6 py-3 font-bold text-center uppercase align-middle mb-0 text-sm leading-tight dark:text-white dark:opacity-80"
                                                        >
                                                            #
                                                        </th>
                                                        <th
                                                            class="px-6 py-3 pl-2 font-bold text-left uppercase align-middle mb-0 text-sm leading-tight dark:text-white dark:opacity-80"
                                                        >
                                                            Site
                                                        </th>
                                                        <th
                                                            class="px-6 py-3 pl-2 font-bold text-left uppercase align-middle mb-0 text-sm leading-tight dark:text-white dark:opacity-80"
                                                        >
                                                            Inventory Number
                                                        </th>
                                                        <th
                                                            class="px-6 py-3 pl-2 font-bold text-left uppercase align-middle mb-0 text-sm leading-tight dark:text-white dark:opacity-80"
                                                        >
                                                            Asset Ho Number
                                                        </th>
                                                        <th
                                                            class="px-6 py-3 font-bold text-center uppercase align-middle mb-0 text-sm leading-tight dark:text-white dark:opacity-80"
                                                        >
                                                            Printer Brand
                                                        </th>
                                                        <th
                                                            class="px-6 py-3 font-bold text-center uppercase align-middle mb-0 text-sm leading-tight dark:text-white dark:opacity-80"
                                                        >
                                                            Printer Type
                                                        </th>
                                                        <th
                                                            class="px-6 py-3 font-bold text-center uppercase align-middle mb-0 text-sm leading-tight dark:text-white dark:opacity-80"
                                                        >
                                                            Location
                                                        </th>
                                                        <th
                                                            class="px-6 py-3 font-bold text-center uppercase align-middle mb-0 text-sm leading-tight dark:text-white dark:opacity-80"
                                                        >
                                                            Note
                                                        </th>
                                                        <th
                                                            class="px-6 py-3 font-bold text-center uppercase align-middle mb-0 text-sm leading-tight dark:text-white dark:opacity-80"
                                                        >
                                                            Inspection remark
                                                        </th>
                                                        <th
                                                            class="px-6 py-3 font-bold text-center uppercase align-middle mb-0 text-sm leading-tight dark:text-white dark:opacity-80"
                                                        >
                                                            Device Status
                                                        </th>

                                                        <th
                                                            class="px-6 py-3 font-bold text-center uppercase align-middle mb-0 text-sm leading-tight dark:text-white dark:opacity-80"
                                                        >
                                                            Last Edit At
                                                        </th>
                                                        <th
                                                            class="px-6 py-3 font-bold text-center uppercase align-middle mb-0 text-sm leading-tight dark:text-white dark:opacity-80"
                                                        >
                                                            Action
                                                        </th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr
                                                        v-for="(
                                                            printers, index
                                                        ) in printer"
                                                        :key="index"
                                                    >
                                                        <td
                                                            class="p-2 text-center align-middle bg-transparent border-b dark:border-white/40 whitespace-nowrap shadow-transparent"
                                                        >
                                                            <span
                                                                class="mb-0 text-sm font-semibold leading-tight dark:text-white dark:opacity-80"
                                                            >
                                                                {{ index + 1 }}
                                                            </span>
                                                        </td>
                                                        <td
                                                            class="p-2 align-middle bg-transparent border-b dark:border-white/40 whitespace-nowrap shadow-transparent"
                                                        >
                                                            <p
                                                                class="mb-0 text-sm font-semibold leading-tight dark:text-white dark:opacity-80"
                                                            >
                                                                {{
                                                                    printers.site
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
                                                                    printers.printer_code
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
                                                                    printers.asset_ho_number
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
                                                                    printers.printer_brand
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
                                                                    printers.printer_type
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
                                                                    printers.location
                                                                }}
                                                            </span>
                                                        </td>
                                                        <td
                                                            class="p-2 text-center align-middle bg-transparent border-b dark:border-white/40 whitespace-nowrap shadow-transparent"
                                                        >
                                                            <span
                                                                class="mb-0 text-sm font-semibold leading-tight dark:text-white dark:opacity-80"
                                                            >
                                                                {{ printers.note }}
                                                            </span>
                                                        </td>
                                                        <td
                                                            class="p-2 text-center align-middle bg-transparent border-b dark:border-white/40 whitespace-nowrap shadow-transparent"
                                                        >
                                                            <span
                                                                class="mb-0 text-sm font-semibold leading-tight dark:text-white dark:opacity-80"
                                                            >
                                                                {{
                                                                    printers.inspection_remark
                                                                }}
                                                            </span>
                                                        </td>
                                                        <td
                                                            class="p-2 text-center align-middle bg-transparent border-b dark:border-white/40 whitespace-nowrap shadow-transparent"
                                                        >
                                                            <span
                                                                :class="{
                                                                    'bg-gradient-to-tl from-emerald-500 to-teal-400 px-2.5 text-xs rounded-1.8 py-1.4 inline-block whitespace-nowrap text-center align-baseline font-bold uppercase leading-none text-white':
                                                                        printers.status ===
                                                                        'READY_USED',
                                                                    'bg-gradient-to-tl from-yellow-500 to-yellow-400 px-2.5 text-xs rounded-1.8 py-1.4 inline-block whitespace-nowrap text-center align-baseline font-bold uppercase leading-none text-white':
                                                                        printers.status ===
                                                                        'READY_STANDBY',
                                                                    'bg-gradient-to-tl from-red-500 to-orange-400 px-2.5 text-xs rounded-1.8 py-1.4 inline-block whitespace-nowrap text-center align-baseline font-bold uppercase leading-none text-white':
                                                                        printers.status ===
                                                                        'SCRAP',
                                                                    'bg-gradient-to-tl from-rose-500 to-rose-400 px-2.5 text-xs rounded-1.8 py-1.4 inline-block whitespace-nowrap text-center align-baseline font-bold uppercase leading-none text-white':
                                                                        printers.status ===
                                                                        'BREAKDOWN',
                                                                }"
                                                            >
                                                                {{
                                                                    printers.status
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
                                                                        printers.updated_at
                                                                    )
                                                                }}
                                                            </span>
                                                        </td>
                                                        <td
                                                            class="p-2 text-sm leading-normal text-center align-middle bg-transparent border-b dark:border-white/40 whitespace-nowrap shadow-transparent"
                                                        >
                                                            <NavLinkCustom
                                                                @click="
                                                                    detailData(
                                                                        printers.id
                                                                    )
                                                                "
                                                                class="mb-0 text-sm font-semibold leading-tight dark:text-white dark:opacity-80"
                                                            >
                                                                Detail
                                                            </NavLinkCustom>

                                                            <NavLinkCustom
                                                                @click="
                                                                    restore(
                                                                        printers.id
                                                                    )
                                                                "
                                                                class="ml-3 mb-0 text-sm font-semibold leading-tight dark:text-white dark:opacity-80"
                                                            >
                                                                Restore
                                                            </NavLinkCustom>

                                                            <NavLinkCustom
                                                                @click="
                                                                    forceDelete(
                                                                        printers.id
                                                                    )
                                                                "
                                                                v-if="props.role !== 'ict_technician'"
                                                                class="ml-3 mb-0 text-sm font-semibold leading-tight dark:text-white dark:opacity-80"
                                                            >
                                                                Delete
                                                            </NavLinkCustom>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </PerfectScrollbar>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
<style>
@import '/public/assets/css/perfect-scrollbar.css';

</style>
