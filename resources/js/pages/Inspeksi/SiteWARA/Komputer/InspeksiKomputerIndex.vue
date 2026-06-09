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
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, useForm, router } from "@inertiajs/vue3";
import NavLinkCustom from "@/Components/NavLinkCustom.vue";
import moment from "moment";
import Swal from "sweetalert2";
import { ref } from "vue";
import { Inertia } from "@inertiajs/inertia";
import { onMounted } from "vue";

const pages = ref("Pages");
const subMenu = ref("Inspeksi Komputer Pages");
const mainMenu = ref("Inspeksi Komputer Data");

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
    computer: {
        type: Array,
    },
    crew: {
        type: Array,
    },
});
const options = props.crew;
const selectedValues = ref(null); // Awalnya array kosong
const showValidation = ref(false);

const form = useForm({});

const editData = (id) => {
    form.get(route("inspeksiKomputerWARA.inspection", { id: id }));
};

const editDataInspeksi = (id) => {
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
            form.get(route("inspeksiKomputerWARA.edit", { id: id }));
        }
    });
};

const year = ref(""); // State untuk input year
const triwulan = ref(""); // State untuk input year

const validateYear = (event) => {
    const value = event.target.value;
    if (!/^\d*$/.test(value)) {
        year.value = value.replace(/\D/g, ""); // Hapus karakter selain angka
    }
};

const getEncryptedYear = () => {
    if (!selectedValues.value || !selectedValues.value.name) {
        showValidation.value = true;

        // Hilangkan efek setelah beberapa saat
        setTimeout(() => {
            showValidation.value = false;
        }, 1000);

        Swal.fire({
            icon: "warning",
            title: "Wajib Memilih PIC",
            text: "Silakan pilih PIC terlebih dahulu sebelum mengekspor data.",
            toast: true,
            position: "top-end",
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
        });
        return;
    }

    const selectPic = selectedValues.value.name;

    // Ambil tahun yang dimasukkan atau gunakan tahun saat ini
    const selectedYear = year.value
        ? parseInt(year.value)
        : new Date().getFullYear();

    // Ambil triwulan (quarter) yang diinput user atau auto-deteksi dari bulan sekarang
    const selectedQuarter = triwulan.value
        ? parseInt(triwulan.value)
        : (() => {
              const month = new Date().getMonth() + 1;
              if (month >= 1 && month <= 3) return 1;
              if (month >= 4 && month <= 6) return 2;
              if (month >= 7 && month <= 9) return 3;
              return 4;
          })();

    // Validasi tahun (tidak boleh lebih dari 2500)
    if (selectedYear > 2500) {
        Swal.fire({
            icon: "error",
            title: "Tahun Tidak Valid!",
            text: "Tahun tidak terdeteksi, silakan masukkan tahun yang benar (<=2500).",
        });
        return; // Stop eksekusi jika tahun tidak valid
    }

    // Validasi quarter juga (optional, kalau mau)
    if (selectedQuarter < 1 || selectedQuarter > 4) {
        Swal.fire({
            icon: "error",
            title: "Triwulan Tidak Valid!",
            text: "Triwulan harus antara 1 sampai 4.",
        });
        return;
    }

    // Tampilkan loading popup
    Swal.fire({
        title: "Menyiapkan PDF...",
        text: "Harap tunggu sebentar.",
        allowOutsideClick: false,
        showConfirmButton: false,
        timer: 2000, // Timer 5 detik
        timerProgressBar: true, // Tampilkan garis waktu
        didOpen: () => {
            Swal.showLoading();
        },
        willClose: () => {
            console.log("Popup PDF selesai otomatis."); // Opsional
        },
    });

    // Kirim permintaan ke backend untuk enkripsi tahun
    router.post(
        route("encrypt.yearComputer"),
        {
            year: selectedYear,
            quarter: selectedQuarter,
            site: "ADW",
            pic: selectPic,
        },
        {
            onSuccess: ({ props }) => {
                const encryptedYear = props.encryptedYear;
                if (encryptedYear) {
                    Swal.close(); // Tutup popup loading setelah selesai
                    window.open(
                        route("export.inspectionComputerAll", {
                            year: encryptedYear,
                            quarter: selectedQuarter,
                            site: "ADW",
                            pic: selectPic,
                        }),
                        "_blank"
                    );
                } else {
                    Swal.fire(
                        "Terjadi Kesalahan",
                        "Gagal mengenkripsi tahun.",
                        "error"
                    );
                }
            },
            onError: () => {
                Swal.close(); // Tutup popup loading jika ada error
                Swal.fire(
                    "Gagal!",
                    "Terjadi kesalahan dalam permintaan.",
                    "error"
                );
            },
        }
    );
};

const detailData = (id) => {
    form.get(route("inspeksiKomputerWARA.detail", { id: id }));
};

const file = ref(null);

const handleFileUpload = (event) => {
    file.value = event.target.files[0];
};

const getBadgeClassStatusInspeksi = (status) => {
    return status === "Y"
        ? "bg-gradient-to-tl from-emerald-500 to-teal-400 px-2.5 text-xs rounded-1.8 py-1.4 inline-block whitespace-nowrap text-center align-baseline font-bold uppercase leading-none text-white"
        : "bg-gradient-to-tl from-red-500 to-orange-400 px-2.5 text-xs rounded-1.8 py-1.4 inline-block whitespace-nowrap text-center align-baseline font-bold uppercase leading-none text-white";
};

const getBadgeTextStatusInspeksi = (status) => {
    return status === "Y" ? "INSPECTED" : "NOT INSPECTED YET";
};

const getBadgeClassStatusFindings = (temuan) => {
    if (temuan === "" || temuan === null) {
        return "bg-gradient-to-tl from-cyan-500 to-sky-400 px-2.5 text-xs rounded-1.8 py-1.4 inline-block whitespace-nowrap text-center align-baseline font-bold uppercase leading-none text-white";
    } else {
        return "bg-gradient-to-tl from-red-500 to-orange-400 px-2.5 text-xs rounded-1.8 py-1.4 inline-block whitespace-nowrap text-center align-baseline font-bold uppercase leading-none text-white"; // Default untuk status yang tidak dikenal
    }
};

const getBadgeTextStatusFindings = (temuan) => {
    if (temuan === "" || temuan === null) {
        return "Tidak ada temuan";
    } else {
        return temuan; // Default teks untuk status yang tidak dikenal
    }
};

const getBadgeClassStatusInventory = (status) => {
    if (status === "READY_USED") {
        return "bg-gradient-to-tl from-emerald-500 to-teal-400 px-2.5 text-xs rounded-1.8 py-1.4 inline-block whitespace-nowrap text-center align-baseline font-bold uppercase leading-none text-white";
    } else if (status === "READY_STANBY") {
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

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')

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
                .post(route('inspeksiKomputerWARA.approval', {}, Ziggy), {
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                    }
                })
                .then((response) => {
                    Swal.fire({
                        icon: "success",
                        title: "Berhasil!",
                        confirmButtonColor: "#16a34a",
                        text: response.data.message,
                        timer: 3000,
                        showConfirmButton: false,
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
    <Head title="Inspeksi Komputer" />

    <AuthenticatedLayout
        v-model:pages="pages"
        v-model:subMenu="subMenu"
        v-model:mainMenu="mainMenu"
    >
        <div class="py-12">
            <div class="min-w-7xl mx-auto sm:px-6 lg:px-8">
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
                                        v-model="triwulan"
                                        type="number"
                                        min="1"
                                        max="4"
                                        step="1"
                                        class="pl-9 text-sm focus:shadow-primary-outline ease w-1/100 leading-5.6 relative -ml-px block min-w-0 flex-auto rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding py-2 pr-3 text-gray-700 transition-all placeholder:text-gray-500 focus:border-blue-500 focus:outline-none focus:transition-shadow"
                                        placeholder="Masukkan Triwulan"
                                        @input="validateYear"
                                    />
                                </div>
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
                                    class="relative flex flex-wrap items-stretch w-50 transition-all rounded-lg ease mb-4"
                                >
                                    <VueMultiselect
                                        ref="picSelect"
                                        v-model="selectedValues"
                                        :options="options"
                                        :multiple="false"
                                        :close-on-select="true"
                                        placeholder="Pilih PIC"
                                        track-by="name"
                                        label="name"
                                        :class="{
                                            'shake-border': showValidation,
                                        }"
                                    />
                                </div>
                                <button
                                    @click="getEncryptedYear"
                                    class="flex items-center text-sm justify-center gap-2 w-40 h-12 bg-gray-800 text-white font-semibold rounded-lg shadow-md transition-all duration-300 ease-in-out transform hover:bg-slate-850 hover:scale-105"
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
                                                            class="px-6 py-3 pl-2 font-bold text-left uppercase align-middle mb-0 text-sm leading-tight dark:text-white dark:opacity-80"
                                                        >
                                                            User
                                                        </th>
                                                        <th
                                                            class="px-6 py-3 pl-2 font-bold text-left uppercase align-middle mb-0 text-sm leading-tight dark:text-white dark:opacity-80"
                                                        >
                                                            Department
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
                                                            Komp Status
                                                        </th>
                                                        <th
                                                            class="px-6 py-3 font-bold text-center uppercase align-middle mb-0 text-sm leading-tight dark:text-white dark:opacity-80"
                                                        >
                                                            Komp Condition
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
                                                            computers, index
                                                        ) in computer"
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
                                                            class="p-2 text-center align-middle bg-transparent border-b dark:border-white/40 whitespace-nowrap shadow-transparent"
                                                        >
                                                            <NavLinkCustom
                                                                v-if="
                                                                    computers.inspection_status ===
                                                                    'N'
                                                                "
                                                                @click="
                                                                    editData(
                                                                        computers.id
                                                                    )
                                                                "
                                                                class="mb-0 text-sm font-semibold leading-tight dark:text-white dark:opacity-80"
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
                                                                    computers
                                                                        .computer
                                                                        .computer_code
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
                                                                    computers
                                                                        .computer
                                                                        .pengguna
                                                                        .username
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
                                                                    computers
                                                                        .computer
                                                                        .pengguna
                                                                        .department
                                                                }}
                                                            </p>
                                                        </td>

                                                        <td
                                                            class="p-2 text-center align-middle bg-transparent border-b dark:border-white/40 whitespace-nowrap shadow-transparent"
                                                        >
                                                            <span
                                                                :class="
                                                                    getBadgeClassStatusFindings(
                                                                        computers.findings
                                                                    )
                                                                "
                                                            >
                                                                {{
                                                                    getBadgeTextStatusFindings(
                                                                        computers.findings
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
                                                                    computers.updated_at ==
                                                                    null
                                                                        ? "-"
                                                                        : formattedDate(
                                                                              computers.updated_at
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
                                                                        computers.inspection_status
                                                                    )
                                                                "
                                                            >
                                                                {{
                                                                    getBadgeTextStatusInspeksi(
                                                                        computers.inspection_status
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
                                                                        computers.inventory_status
                                                                    )
                                                                "
                                                            >
                                                                {{
                                                                    getBadgeTextStatusInventory(
                                                                        computers.inventory_status
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
                                                                    computers.conditions
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
                                                                    computers.remarks
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
                                                                    computers.status_approval
                                                                }}
                                                            </span>
                                                        </td>
                                                        <td
                                                            class="p-2 text-sm leading-normal text-center align-middle bg-transparent border-b dark:border-white/40 whitespace-nowrap shadow-transparent"
                                                        >
                                                            <NavLinkCustom
                                                                @click="
                                                                    editDataInspeksi(
                                                                        computers.id
                                                                    )
                                                                "
                                                                v-if="
                                                                    computers.inspection_status ===
                                                                    'Y'
                                                                "
                                                                class="mb-0 text-sm font-semibold leading-tight dark:text-white dark:opacity-80"
                                                            >
                                                                Edit
                                                            </NavLinkCustom>

                                                            <NavLinkCustom
                                                                v-if="
                                                                    computers.inspection_status ===
                                                                    'Y'
                                                                "
                                                                @click="
                                                                    detailData(
                                                                        computers.id
                                                                    )
                                                                "
                                                                class="ml-3 mb-0 text-sm font-semibold leading-tight dark:text-white dark:opacity-80"
                                                            >
                                                                Detail
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
        </div>
    </AuthenticatedLayout>
</template>
