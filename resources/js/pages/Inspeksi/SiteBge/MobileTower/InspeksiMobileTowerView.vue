<style>
@import "datatables.net-dt";

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

@keyframes shake {
    0% {
        transform: translateX(0);
    }
    25% {
        transform: translateX(-5px);
    }
    50% {
        transform: translateX(5px);
    }
    75% {
        transform: translateX(-5px);
    }
    100% {
        transform: translateX(0);
    }
}

.shake-border {
    animation: shake 0.3s ease;
    box-shadow: 0 0 10px rgba(59, 130, 246, 0.6); /* biru lembut (tailwind blue-500) */
    border: 1px solid rgba(59, 130, 246, 1); /* biru solid */
    border-radius: 0.5rem;
}
</style>
<script setup>
import axios from "axios";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, useForm, router } from "@inertiajs/vue3";
import NavLinkCustom from "@/Components/NavLinkCustom.vue";
import moment from "moment";
import Swal from "sweetalert2";
import { ref, onMounted, watch } from "vue";

const pages = ref("Pages");
const subMenu = ref("Inspeksi Mobile Tower Pages");
const mainMenu = ref("Inspeksi Mobile Tower");

// Fungsi untuk format tanggal
function formattedDate(date) {
    return moment(date).format("MMMM Do, YYYY HH:mm"); // Sesuaikan format sesuai kebutuhan
}

const mount = onMounted(() => {
    // Inisialisasi DataTable tanpa AJAX
    $("#tableData").DataTable({
        dom: "fBrtilp",
        scrollY: "40vh",
        scrollCollapse: true,
        buttons: [
            {
                extend: "spacer",
                style: "bar",
                text: "Export files:",
            },
            "csvHtml5",
            "excelHtml5",
            "spacer",
        ],
        initComplete: function () {
            var btns = $(".dt-button");
            btns.addClass(
                "text-white bg-gradient-to-r from-green-600 via-green-700 to-green-900 hover:bg-gradient-to-br focus:ring-4 focus:outline-none focus:ring-green-300 dark:focus:ring-green-800 font-medium rounded-lg text-sm px-5 py-2.5 text-center me-2 mb-2"
            );
            btns.removeClass("dt-button");
        },
    });
});

const props = defineProps({
    inspeksiMobileTowerx: {
        type: Array,
    },
    monthNow: Number,
    yearNow: Number,
    bulan_sekarang: Number,
    tahun_sekarang: Number,
    site: String,
});

const form = useForm({});

const year = ref(props.yearNow);

const monthOptions = [
    { name: "Januari", value: 1 },
    { name: "Februari", value: 2 },
    { name: "Maret", value: 3 },
    { name: "April", value: 4 },
    { name: "Mei", value: 5 },
    { name: "Juni", value: 6 },
    { name: "Juli", value: 7 },
    { name: "Agustus", value: 8 },
    { name: "September", value: 9 },
    { name: "Oktober", value: 10 },
    { name: "November", value: 11 },
    { name: "Desember", value: 12 },
];
const selectedMonth = ref(monthOptions.find((m) => m.value === props.monthNow));

const validateYear = (event) => {
    const value = event.target.value;
    if (!/^\d*$/.test(value)) {
        year.value = value.replace(/\D/g, ""); // Hapus karakter selain angka
    }
};

watch([selectedMonth, year], ([newMonth, newYear]) => {
    if (newMonth && newYear) {
        router.get(
            route("inspeksiMobileTowerBge.page"),
            {
                month: newMonth.value,
                year: newYear,
            },
            {
                preserveState: false,
                replace: true,
            }
        );
    }
});

const sendRekapInspeksi = async () => {
    if (!selectedMonth.value) {
        Swal.fire("Bulan belum dipilih!", "Silakan pilih bulan.", "warning");
        return;
    }

    if (!year.value) {
        Swal.fire("Tahun belum diisi!", "Silakan masukkan tahun.", "warning");
        return;
    }

    const selectedYear = parseInt(year.value);
    const selectedMonthValue = selectedMonth.value.value;
    const selectedSite = props.site;

    if (selectedYear > 2500 || selectedYear < 2000) {
        Swal.fire(
            "Tahun Tidak Valid!",
            "Tahun harus antara 2000–2500.",
            "error"
        );
        return;
    }

    Swal.fire({
        title: "Menyiapkan PDF...",
        text: "Harap tunggu sebentar.",
        allowOutsideClick: false,
        showConfirmButton: false,
        didOpen: () => Swal.showLoading(),
    });

    try {
        const { data } = await axios.post(route("pdf.mt"), {
            year: selectedYear,
            month: selectedMonthValue,
            site: selectedSite,
        });

        Swal.close();

        if (data.url) {
            window.open(data.url, "_blank");
        } else {
            Swal.fire("Error", "URL PDF tidak ditemukan.", "error");
        }
    } catch (error) {
        Swal.close();
        console.error(error);
        Swal.fire("Gagal!", "Terjadi kesalahan saat membuat PDF.", "error");
    }
};

// Mencegah Swal muncul kembali setelah menekan tombol "Back" di browser
window.addEventListener("pageshow", () => {
    Swal.close();
});

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
            form.get(route("inspeksiMobileTowerBge.edit", { id: id }));
        }
    });
};

const getBadgeClassStatusInspeksi = (status) => {
    return status === "Y"
        ? "bg-gradient-to-tl from-emerald-500 to-teal-400 px-2.5 text-xs rounded-1.8 py-1.4 inline-block whitespace-nowrap text-center align-baseline font-bold uppercase leading-none text-white"
        : "bg-gradient-to-tl from-red-500 to-orange-400 px-2.5 text-xs rounded-1.8 py-1.4 inline-block whitespace-nowrap text-center align-baseline font-bold uppercase leading-none text-white";
};

const getBadgeTextStatusInspeksi = (status) => {
    return status === "Y" ? "INSPECTED" : "NOT INSPECTED YET";
};

const getBadgeClassStatusInventory = (status) => {
    if (status === "READY_USED") {
        return "bg-gradient-to-tl from-emerald-500 to-teal-400 px-2.5 text-xs rounded-1.8 py-1.4 inline-block whitespace-nowrap text-center align-baseline font-bold uppercase leading-none text-white";
    } else if (status === "READY_STANDBY") {
        return "bg-gradient-to-tl from-yellow-500 to-yellow-400 px-2.5 text-xs rounded-1.8 py-1.4 inline-block whitespace-nowrap text-center align-baseline font-bold uppercase leading-none text-white";
    } else if (status === "SCRAP") {
        return "bg-gradient-to-tl from-red-500 to-orange-400 px-2.5 text-xs rounded-1.8 py-1.4 inline-block whitespace-nowrap text-center align-baseline font-bold uppercase leading-none text-white";
    } else if (status === "BREAKDOWN") {
        return "bg-gradient-to-tl from-rose-500 to-rose-400 px-2.5 text-xs rounded-1.8 py-1.4 inline-block whitespace-nowrap text-center align-baseline font-bold uppercase leading-none text-white";
    }
};

const getBadgeTextStatusInventory = (status) => {
    if (status === "READY_USED") {
        return "READY_USED";
    } else if (status === "READY_STANDBY") {
        return "READY_STANDBY";
    } else if (status === "SCRAP") {
        return "SCRAP";
    } else if (status === "BREAKDOWN") {
        return "BREAKDOWN";
    }
};

const getBadgeClassStatusFindings = (temuan) => {
    if (temuan === "" || temuan === null) {
        return "bg-gradient-to-tl from-cyan-500 to-sky-400 px-2.5 text-xs rounded-1.8 py-1.4 inline-block whitespace-nowrap text-center align-baseline font-bold uppercase leading-none text-white";
    } else {
        return "bg-gradient-to-tl from-red-500 to-orange-400 px-2.5 text-xs rounded-1.8 py-1.4 inline-block whitespace-nowrap text-center align-baseline font-bold uppercase leading-none text-white"; // Default untuk status yang tidak dikenal
    }
};

const parseFindings = (temuan) => {
    if (!temuan || temuan.length === 0) {
        return [];
    }

    // kalau masih berupa string JSON array
    if (typeof temuan === "string" && temuan.startsWith("[")) {
        try {
            return JSON.parse(temuan);
        } catch (e) {
            return [temuan]; // kalau gagal parse, jadikan array biasa
        }
    }

    // kalau sudah array asli
    if (Array.isArray(temuan)) {
        return temuan;
    }

    // fallback: bungkus jadi array
    return [temuan];
};

const detailData = (id) => {
    form.get(route("inspeksiMobileTowerBge.detail", { id: id }));
};

const processData = (id) => {
    form.get(route("inspeksiMobileTowerBge.process", { id: id }));
};

function formatData(text) {
    const maxLength = 20; // Set your limit here
    return text.length > maxLength ? text.slice(0, maxLength) + "..." : text;
}

const csrfToken = document
    .querySelector('meta[name="csrf-token"]')
    ?.getAttribute("content");

const approved = () => {
    Swal.fire({
        title: "Yakin ingin approve semua data?",
        text: "Tindakan ini akan menyetujui semua data yang sudah di inspeksi!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#16a34a",
        cancelButtonColor: "#d33",
        confirmButtonText: "Ya, approve semua!",
        cancelButtonText: "Batal",
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: "Sedang memproses...",
                timerProgressBar: true,
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                },
            });

            axios
                .post(
                    route(
                        "inspeksiMobileTowerBge.approval",
                        {
                            month: selectedMonth.value.value,
                            year: year.value,
                        },
                        Ziggy
                    ),
                    {
                        headers: {
                            "X-CSRF-TOKEN": csrfToken,
                            "X-Requested-With": "XMLHttpRequest",
                        },
                    }
                )
                .then((response) => {
                    Swal.fire({
                        icon: "success",
                        title: "Berhasil!",
                        confirmButtonColor: "#16a34a",
                        text: response.data.message,
                        timer: 3000,
                        showConfirmButton: false,
                        willClose: () => {
                            router.reload({ only: ["inspeksiMobileTowerx"] });
                        },
                    });
                })
                .catch((error) => {
                    Swal.fire({
                        icon: "error",
                        title: "Gagal!",
                        confirmButtonColor: "#16a34a",
                        text:
                            error.response?.data?.message ||
                            "Terjadi kesalahan.",
                    });
                });
        }
    });
};
</script>

<template>
    <Head title="Inspeksi MT" />

    <AuthenticatedLayout
        v-model:pages="pages"
        v-model:subMenu="subMenu"
        v-model:mainMenu="mainMenu"
    >
        <div class="py-12">
            <div class="flex flex-wrap -mx-3">
                <div class="flex-none w-full max-w-full px-3">
                    <div class="min-w-7xl mx-auto sm:px-6 lg:px-8">
                        <div class="flex flex-wrap md:flex-nowrap gap-4">
                            <div
                                class="relative flex flex-wrap items-stretch w-50 transition-all rounded-lg ease mb-4"
                            >
                                <span
                                    class="text-sm ease leading-5.6 absolute z-50 -ml-px flex h-full items-center whitespace-nowrap rounded-lg rounded-tr-none rounded-br-none border border-r-0 border-transparent bg-transparent py-2 px-2.5 text-center font-normal text-slate-500 transition-all"
                                >
                                    <i
                                        class="fas fa-search"
                                        aria-hidden="true"
                                    ></i>
                                </span>
                                <input
                                    v-model="year"
                                    type="number"
                                    min="2025"
                                    max="2500"
                                    step="1"
                                    class="pl-9 text-sm focus:shadow-primary-outline ease w-1/100 leading-5.6 relative -ml-px block min-w-0 flex-auto rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding py-2 pr-3 text-gray-700 transition-all placeholder:text-gray-500 focus:border-blue-500 focus:outline-none focus:transition-shadow"
                                    placeholder="Masukkan Tahun"
                                    @input="validateYear"
                                />
                            </div>
                            <div
                                class="relative w-48 transition-all rounded-lg ease mb-4"
                            >
                                <VueMultiselect
                                    v-model="selectedMonth"
                                    :options="monthOptions"
                                    :multiple="false"
                                    :close-on-select="true"
                                    placeholder="Select Month"
                                    track-by="value"
                                    label="name"
                                />
                            </div>
                            <button
                                @click="sendRekapInspeksi"
                                class="flex items-center text-sm justify-center gap-2 w-40 h-10 bg-gray-800 text-white font-semibold rounded-lg shadow-md transition-all duration-300 ease-in-out transform hover:bg-slate-850 hover:scale-105"
                            >
                                <i class="fas fa-download"></i>
                                Rekap Inspeksi
                            </button>
                            <button
                                @click="approved"
                                class="flex items-center text-sm justify-center gap-2 w-40 h-12 bg-green-700 text-white font-semibold rounded-lg shadow-md transition-all duration-300 ease-in-out transform hover:bg-green-850 hover:scale-105"
                            >
                                <i class="fas fa-check"></i>
                                Approval
                            </button>
                        </div>
                        <div
                            class="relative flex flex-col min-w-0 mb-6 break-words bg-white border-0 border-transparent border-solid shadow-xl dark:bg-slate-850 dark:shadow-dark-xl rounded-2xl bg-clip-border"
                        >
                            <div class="flex-auto px-0 pt-0 pb-2">
                                <div class="p-0">
                                    <div class="p-6 text-gray-900">
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
                                                        class="px-6 py-3 font-bold text-center uppercase align-middle mb-0 text-sm leading-tight dark:text-white dark:opacity-80"
                                                    >
                                                        Inspection
                                                    </th>
                                                    <th
                                                        class="px-6 py-3 pl-2 font-bold text-left uppercase align-middle mb-0 text-sm leading-tight dark:text-white dark:opacity-80"
                                                    >
                                                        Inventory Number
                                                    </th>

                                                    <th
                                                        class="px-6 py-3 font-bold text-center uppercase align-middle mb-0 text-sm leading-tight dark:text-white dark:opacity-80"
                                                    >
                                                        Temuan
                                                    </th>
                                                    <th
                                                        class="px-6 py-3 font-bold text-center uppercase align-middle mb-0 text-sm leading-tight dark:text-white dark:opacity-80"
                                                    >
                                                        Inspection date
                                                    </th>
                                                    <th
                                                        class="px-6 py-3 font-bold text-center uppercase align-middle mb-0 text-sm leading-tight dark:text-white dark:opacity-80"
                                                    >
                                                        Inspection Status
                                                    </th>
                                                    <th
                                                        class="px-6 py-3 font-bold text-center uppercase align-middle mb-0 text-sm leading-tight dark:text-white dark:opacity-80"
                                                    >
                                                        Mobile Tower Status
                                                    </th>
                                                    <th
                                                        class="px-6 py-3 font-bold text-center uppercase align-middle mb-0 text-sm leading-tight dark:text-white dark:opacity-80"
                                                    >
                                                        Mobile Tower Condition
                                                    </th>
                                                    <th
                                                        class="px-6 py-3 font-bold text-center uppercase align-middle mb-0 text-sm leading-tight dark:text-white dark:opacity-80"
                                                    >
                                                        Remark
                                                    </th>
                                                    <th
                                                        class="px-6 py-3 font-bold text-center uppercase align-middle mb-0 text-sm leading-tight dark:text-white dark:opacity-80"
                                                    >
                                                        Approval Status
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
                                                        inspeksiMobileTowers,
                                                        index
                                                    ) in inspeksiMobileTowerx"
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
                                                        class="p-2 text-sm leading-normal text-center align-middle bg-transparent border-b dark:border-white/40 whitespace-nowrap shadow-transparent"
                                                    >
                                                        <NavLinkCustom
                                                            @click="
                                                                processData(
                                                                    inspeksiMobileTowers.id
                                                                )
                                                            "
                                                            v-if="
                                                                inspeksiMobileTowers.inspection_status ===
                                                                    'N' &&
                                                                inspeksiMobileTowers.month ===
                                                                    props.bulan_sekarang &&
                                                                inspeksiMobileTowers.year ===
                                                                    props.tahun_sekarang
                                                            "
                                                            class="!px-4 !py-2 !rounded-md !border !border-cyan-600 !text-cyan-600 !font-semibold hover:!bg-cyan-50 dark:!border-cyan-600 dark:!text-cyan-600 dark:!hover:bg-sky-950"
                                                        >
                                                            Do Inspection
                                                        </NavLinkCustom>
                                                    </td>

                                                    <td
                                                        class="p-2 align-middle bg-transparent border-b dark:border-white/40 whitespace-nowrap shadow-transparent"
                                                    >
                                                        <p
                                                            class="mb-0 text-sm font-semibold leading-tight dark:text-white dark:opacity-80"
                                                        >
                                                            {{
                                                                inspeksiMobileTowers
                                                                    .mt.mt_code
                                                            }}
                                                        </p>
                                                    </td>

                                                    <td
                                                        class="p-2 text-left align-middle bg-transparent border-b dark:border-white/40 shadow-transparent"
                                                    >
                                                        <ul
                                                            v-if="
                                                                parseFindings(
                                                                    inspeksiMobileTowers.findings
                                                                ).length > 0
                                                            "
                                                            :class="
                                                                getBadgeClassStatusFindings(
                                                                    inspeksiMobileTowers.findings
                                                                )
                                                            "
                                                            class="list-decimal list-inside space-y-1 text-left"
                                                        >
                                                            <li
                                                                v-for="(
                                                                    item, index
                                                                ) in parseFindings(
                                                                    inspeksiMobileTowers.findings
                                                                )"
                                                                :key="index"
                                                            >
                                                                {{ item }}
                                                            </li>
                                                        </ul>

                                                        <span
                                                            v-else
                                                            :class="
                                                                getBadgeClassStatusFindings(
                                                                    inspeksiMobileTowers.findings
                                                                )
                                                            "
                                                        >
                                                            Tidak ada temuan
                                                        </span>
                                                    </td>

                                                    <td
                                                        class="p-2 text-center align-middle bg-transparent border-b dark:border-white/40 whitespace-nowrap shadow-transparent"
                                                    >
                                                        <span
                                                            class="mb-0 text-sm font-semibold leading-tight dark:text-white dark:opacity-80"
                                                        >
                                                            {{
                                                                inspeksiMobileTowers.inspection_at ==
                                                                null
                                                                    ? "-"
                                                                    : formattedDate(
                                                                          inspeksiMobileTowers.inspection_at
                                                                      )
                                                            }}
                                                        </span>
                                                    </td>
                                                    <td
                                                        class="p-2 text-center align-middle bg-transparent border-b dark:border-white/40 whitespace-nowrap shadow-transparent"
                                                    >
                                                        <span
                                                            :class="
                                                                getBadgeClassStatusInspeksi(
                                                                    inspeksiMobileTowers.inspection_status
                                                                )
                                                            "
                                                        >
                                                            {{
                                                                getBadgeTextStatusInspeksi(
                                                                    inspeksiMobileTowers.inspection_status
                                                                )
                                                            }}
                                                        </span>
                                                    </td>
                                                    <td
                                                        class="p-2 text-center align-middle bg-transparent border-b dark:border-white/40 whitespace-nowrap shadow-transparent"
                                                    >
                                                        <span
                                                            :class="
                                                                getBadgeClassStatusInventory(
                                                                    inspeksiMobileTowers
                                                                        .mt
                                                                        .status
                                                                )
                                                            "
                                                        >
                                                            {{
                                                                getBadgeTextStatusInventory(
                                                                    inspeksiMobileTowers
                                                                        .mt
                                                                        .status
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
                                                                inspeksiMobileTowers.condition
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
                                                                inspeksiMobileTowers.remarks
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
                                                                inspeksiMobileTowers.status_approval
                                                            }}
                                                        </span>
                                                    </td>

                                                    <td
                                                        class="p-2 text-sm leading-normal text-center align-middle bg-transparent border-b dark:border-white/40 whitespace-nowrap shadow-transparent"
                                                    >
                                                        <NavLinkCustom
                                                            @click="
                                                                detailData(
                                                                    inspeksiMobileTowers.id
                                                                )
                                                            "
                                                            v-if="
                                                                inspeksiMobileTowers.inspection_status ===
                                                                'Y'
                                                            "
                                                            class="ml-3 mb-0 text-sm font-semibold leading-tight dark:text-white dark:opacity-80"
                                                        >
                                                            Detail
                                                        </NavLinkCustom>

                                                        <NavLinkCustom
                                                            v-if="
                                                                inspeksiMobileTowers.inspection_status ===
                                                                    'Y' &&
                                                                inspeksiMobileTowers.status_approval !==
                                                                    'approved'
                                                            "
                                                            @click="
                                                                editData(
                                                                    inspeksiMobileTowers.id
                                                                )
                                                            "
                                                            class="ml-3 mr-3 mb-0 text-sm font-semibold leading-tight dark:text-white dark:opacity-80"
                                                        >
                                                            Edit
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
