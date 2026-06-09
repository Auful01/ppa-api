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

const pages = ref("Pages");
const subMenu = ref("Scanner Pages");
const mainMenu = ref("Scanner Data");

// Fungsi untuk format tanggal
function formattedDate(date) {
    return moment(date).format("MMMM Do, YYYY"); // Sesuaikan format sesuai kebutuhan
}

const mount = onMounted(() => {
    // Inisialisasi DataTable tanpa AJAX
    $("#tableData").DataTable({
        dom: 'fBrtilp',
        scrollY: '40vh',
        scrollCollapse: true,
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
    scanner: {
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

const deleteData = (id) => {
    // Call SweetAlert for confirmation
    Swal.fire({
        title: "Are you sure?",
        text: "Menghapus data ini akan berdampak pada table yang berelasi dengan data ini!, data pada table yang berelasi akan ikut terhapus!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, delete it!",
    }).then((result) => {
        if (result.isConfirmed) {
            // Perform the delete operation, e.g., by making a request to the server
            form.delete(route("scannerMlp.delete", { id: id }), {
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

const editData = (id) => {
    // Call SweetAlert for confirmation
    Swal.fire({
        title: "Are you sure?",
        text: "You want edit this data?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes!",
    }).then((result) => {
        if (result.isConfirmed) {
            form.get(route("scannerMlp.edit", { id: id }));
        }
    });
};

const detailData = (id) => {
    form.get(route("scannerMlp.detail", { id: id }));
};

const file = ref(null);

const handleFileUpload = (event) => {
    file.value = event.target.files[0];
};

const submitCsv = () => {
    let timerInterval;
    Swal.fire({
        title: "Mengimport Data...",
        text: "Mohon tunggu sebentar...",
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        },
    });

    const formx = useForm({
        file: file.value,
    });

    function reloadPage() {
        window.location.reload();
    }

    formx.post(route("scannerMlp.import"), {
        onSuccess: () => {
            // Show SweetAlert2 success notification
            Swal.fire({
                title: "Success!",
                text: "Data has been successfully import!",
                icon: "success",
                confirmButtonText: "OK",
                confirmButtonColor: "#3085d6",
            });

            setTimeout(function () {
                reloadPage();
            }, 2000);
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
    <Head title="Inv Scanner" />

    <AuthenticatedLayout
        v-model:pages="pages"
        v-model:subMenu="subMenu"
        v-model:mainMenu="mainMenu"
    >
        <div class="py-12">
            <div class="min-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="flex flex-wrap -mx-3">
                    <form
                        @submit.prevent="submitCsv"
                        enctype="multipart/form-data"
                    >
                        <div class="flex flex-wrap">
                            <div
                                class="max-w-full px-3"
                            >
                                <div class="mb-4">
                                    <input
                                        type="file"
                                        ref="fileInput"
                                        enctype="multipart/form-data"
                                        class="focus:shadow-primary-outline dark:bg-slate-850 dark:text-white text-sm leading-5.6 ease block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-blue-500 focus:outline-none"
                                        @change="handleFileUpload"
                                    />
                                </div>
                            </div>
                            <div class="max-w-full pl-3">
                                <button
                                    type="submit"
                                    class="text-gray-900 bg-white border border-gray-300 focus:outline-none hover:bg-gray-100 focus:ring-4 focus:ring-gray-100 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 dark:bg-gray-800 dark:text-white dark:border-gray-600 dark:hover:bg-gray-700 dark:hover:border-gray-600 dark:focus:ring-gray-700"
                                >
                                    <i class="fas fa-file-import"></i>
                                    Import
                                </button>
                            </div>
                            <div
                                class="max-w-full px-3"
                            >
                                <a
                                    href="/sampleScanner.xlsx"
                                    download="Format-Import-Data-scanner.xlsx"
                                    target="_blank"
                                    type="button"
                                    class="text-gray-900 bg-white border border-gray-300 focus:outline-none hover:bg-gray-100 focus:ring-4 focus:ring-gray-100 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 dark:bg-gray-800 dark:text-white dark:border-gray-600 dark:hover:bg-gray-700 dark:hover:border-gray-600 dark:focus:ring-gray-700"
                                >
                                    <i class="fas fa-download"></i>
                                    Format Excel Data
                                </a>
                            </div>
                        </div>
                    </form>

                    <div class="flex-none w-full max-w-full px-3">
                        <div
                            class="relative flex flex-col min-w-0 mb-6 break-words bg-white border-0 border-transparent border-solid shadow-xl dark:bg-slate-850 dark:shadow-dark-xl rounded-2xl bg-clip-border"
                        >
                            <div
                                class="p-6 pb-0 mb-0 border-b-0 border-b-solid rounded-t-2xl border-b-transparent"
                            >
                                <Link
                                    :href="route('scannerMlp.create')"
                                    class="inline-block px-5 py-2.5 font-bold leading-normal text-center text-white align-middle transition-all bg-transparent rounded-lg cursor-pointer text-sm ease-in shadow-md bg-150 bg-gradient-to-tl from-zinc-800 to-zinc-700 dark:bg-gradient-to-tl dark:from-slate-750 dark:to-gray-850 hover:shadow-xs active:opacity-85 hover:-translate-y-px tracking-tight-rem bg-x-25"
                                >
                                    <i class="fas fa-plus"> </i>&nbsp;&nbsp;Add
                                    New Data
                                </Link>
                            </div>

                            <div class="flex-auto px-0 pt-0 pb-2">
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
                                                            Scanner Brand
                                                        </th>
                                                        <th
                                                            class="px-6 py-3 font-bold text-center uppercase align-middle mb-0 text-sm leading-tight dark:text-white dark:opacity-80"
                                                        >
                                                            Scanner Type
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
                                                            Inventory Date
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
                                                            scanners, index
                                                        ) in scanner"
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
                                                                    scanners.scanner_code
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
                                                                    scanners.asset_ho_number
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
                                                                    scanners.scanner_brand
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
                                                                    scanners.scanner_type
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
                                                                    scanners.location
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
                                                                    scanners.note
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
                                                                    scanners.inspection_remark
                                                                }}
                                                            </span>
                                                        </td>
                                                        <td
                                                            class="p-2 text-center align-middle bg-transparent border-b dark:border-white/40 whitespace-nowrap shadow-transparent"
                                                        >
                                                            <span
                                                                :class="{
                                                                    'bg-gradient-to-tl from-emerald-500 to-teal-400 px-2.5 text-xs rounded-1.8 py-1.4 inline-block whitespace-nowrap text-center align-baseline font-bold uppercase leading-none text-white':
                                                                        scanners.status ===
                                                                        'READY_USED',
                                                                    'bg-gradient-to-tl from-yellow-500 to-yellow-400 px-2.5 text-xs rounded-1.8 py-1.4 inline-block whitespace-nowrap text-center align-baseline font-bold uppercase leading-none text-white':
                                                                        scanners.status ===
                                                                        'READY_STANDBY',
                                                                    'bg-gradient-to-tl from-red-500 to-orange-400 px-2.5 text-xs rounded-1.8 py-1.4 inline-block whitespace-nowrap text-center align-baseline font-bold uppercase leading-none text-white':
                                                                        scanners.status ===
                                                                        'SCRAP',
                                                                    'bg-gradient-to-tl from-rose-500 to-rose-400 px-2.5 text-xs rounded-1.8 py-1.4 inline-block whitespace-nowrap text-center align-baseline font-bold uppercase leading-none text-white':
                                                                        scanners.status ===
                                                                        'BREAKDOWN',
                                                                }"
                                                            >
                                                                {{
                                                                    scanners.status
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
                                                                    scanners.date_of_inventory
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
                                                                        scanners.updated_at
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
                                                                        scanners.id
                                                                    )
                                                                "
                                                                class="mb-0 text-sm font-semibold leading-tight dark:text-white dark:opacity-80"
                                                            >
                                                                Detail
                                                            </NavLinkCustom>

                                                            <NavLinkCustom
                                                                @click="
                                                                    editData(
                                                                        scanners.id
                                                                    )
                                                                "
                                                                class="ml-3 mb-0 text-sm font-semibold leading-tight dark:text-white dark:opacity-80"
                                                            >
                                                                Edit
                                                            </NavLinkCustom>

                                                            <NavLinkCustom
                                                                @click="
                                                                    deleteData(
                                                                        scanners.id
                                                                    )
                                                                "
                                                                v-if="
                                                                    props.role !==
                                                                    'ict_technician'
                                                                "
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
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>