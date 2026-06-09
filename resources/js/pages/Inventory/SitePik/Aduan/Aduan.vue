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
#dt-length-0 {
    width: 60px !important;
}

/* Untuk VueDatePicker */
.dp__input {
    height: 3rem; /* sama dengan h-12 */
    line-height: 3rem;
}

.multiselect {
    min-height: 3rem; /* h-12 */
}

.multiselect__tags {
    min-height: 3rem !important;
    display: flex;
    align-items: center;
}

.custom-image-zoom-out {
    max-width: 80vw;
    max-height: 80vh;
    object-fit: contain;
    transition: transform 0.3s ease;
    transform: scale(0.95); /* sedikit dikecilkan */
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
import VueDatePicker from "@vuepic/vue-datepicker";
import "@vuepic/vue-datepicker/dist/main.css";
import { ref } from "vue";
import { Inertia } from "@inertiajs/inertia";
import { onMounted } from "vue";
import axios from "axios";

const pages = ref("Pages");
const subMenu = ref("Aduan Pages");
const mainMenu = ref("Aduan Data");

// Fungsi untuk format tanggal
function formattedDate(date) {
    return moment(date).format("MMMM Do, YYYY"); // Sesuaikan format sesuai kebutuhan
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
    aduan: {
        type: Array,
    },
    crew: {
        type: Array,
    },
    open: {
        type: Object,
    },
    closed: {
        type: Object,
    },
    progress: {
        type: Object,
    },
    cancel: {
        type: Object,
    },
});

const form = useForm({});

const deleteData = (id) => {
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
            form.delete(route("aduanPik.delete", { id: id }), {
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
            form.get(route("aduanPik.edit", { id: id }));
        }
    });
};

const progressAduan = (id) => {
    form.get(route("aduanPik.progress", { id: id }));
};

const acceptAduan = (id) => {
    Swal.fire({
        title: "Accept Aduan?",
        html: `
        Proses ini akan otomatis mengisi <b>Start Response</b> dengan jam sekarang,
        dan mengubah status menjadi <b>Progress</b>
      `,
        showCancelButton: true,
        confirmButtonText: "Accept",
        cancelButtonText: "Cancel",
        focusConfirm: false,
        allowOutsideClick: false,
        allowEscapeKey: false,
        allowEnterKey: false,
        backdrop: `
            rgba(0,0,0,0.5)
            left top
            no-repeat
        `,
        customClass: {
            confirmButton:
                "bg-slate-700 hover:bg-slate-900 text-white font-semibold px-4 py-2 rounded",
            cancelButton:
                "bg-red-600 hover:bg-red-700 text-white font-semibold px-4 py-2 rounded ml-2",
        },
        buttonsStyling: false,
    }).then((result) => {
        if (result.isConfirmed) {
            axios
                .post(route("aduanPik.accept", { id: id }))
                .then((response) => {
                    Swal.fire({
                        icon: "success",
                        title: "Berhasil!",
                        text: response.data.message,
                        timer: 2000,
                        showConfirmButton: false,
                        willClose: () => {
                            // refresh page setelah Swal tertutup
                            location.reload();
                        },
                    });
                })
                .catch((error) => {
                    Swal.fire({
                        icon: "error",
                        title: "Gagal!",
                        text:
                            error.response?.data?.message ??
                            "Terjadi kesalahan.",
                    });
                });
        }
    });
};

onMounted(() => {
    // cek apakah sudah pernah ditampilkan di sessionStorage
    if (!sessionStorage.getItem("guideShownPik")) {
        Swal.fire({
            title: "Panduan",
            html: `
        Jika ada aduan masuk dan ingin memberikan response cepat,
        klik tombol <b>Accept</b> pada kolom progress.
      `,
            icon: "info",
            confirmButtonText: "Oke",
            allowOutsideClick: false,
            allowEscapeKey: false,
            backdrop: `rgba(0,0,0,0.6)`,
            customClass: {
                confirmButton:
                    "bg-slate-700 hover:bg-slate-900 text-white font-semibold px-4 py-2 rounded",
            },
            buttonsStyling: false,
        }).then(() => {
            // simpan flag agar tidak tampil lagi selama sesi
            sessionStorage.setItem("guideShownPik", "true");
        });
    }
});

const detailData = (id) => {
    form.get(route("aduanPik.detail", { id: id }));
};

const file = ref(null);

const handleFileUpload = (event) => {
    file.value = event.target.files[0];
};

const submitCsv = () => {
    const formData = new FormData();
    formData.append("file", file.value);
    Inertia.post(route("aduanPik.import"), formData, {
        forceFormData: true,
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

const formatComplaint = (text) => {
    if (!text) return "";
    return text.replace(/(.{25})/g, "$1\n"); // Menyisipkan baris baru setiap 25 karakter
};

function formatData(text) {
    const maxLength = 20; // Set your limit here
    return text.length > maxLength ? text.slice(0, maxLength) + "..." : text;
}

function updateUrgency(id, value) {
    Swal.fire({
        title: "Memproses...",
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        },
    });
    axios
        .post(route("aduanPik.updateUrgency"), {
            id: id,
            urgency: value,
        })
        .then((res) => {
            Swal.fire({
                icon: "success",
                title: "Berhasil",
                text: "Urgency berhasil diupdate!",
                timer: 1500,
                showConfirmButton: false,
            });
        })
        .catch((err) => {
            console.error(err);
            Swal.fire({
                icon: "error",
                title: "Gagal",
                text: "Urgency gagal diupdate!",
            });
        });
}

const options = props.crew;
const startDate = ref(null);
const endDate = ref(null);
const selectedValues = ref(null); // Awalnya array kosong
const showValidation = ref(false);
const data = ref([]);

const customFormat = (date) => {
    if (!date) return null;
    const d = new Date(date);
    return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(
        2,
        "0"
    )}-${String(d.getDate()).padStart(2, "0")}`;
};

const exportPdf = () => {
    console.log(customFormat(startDate.value));
    console.log(customFormat(endDate.value));
    console.log(props.site);

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

    // Tampilkan loading popup
    Swal.fire({
        title: "Menyiapkan PDF...",
        text: "Harap tunggu sebentar.",
        allowOutsideClick: false,
        showConfirmButton: false, // Hapus tombol "Close" agar tidak bisa ditutup manual
        didOpen: () => {
            Swal.showLoading();
        },
    });

    // Kirim permintaan ke backend untuk enkripsi tahun
    router.post(
        route("export.aduanData"),
        {
            startDate: customFormat(startDate.value),
            endDate: customFormat(endDate.value),
            site: "PIK",
            pic: selectPic,
        },
        {
            onSuccess: ({ props }) => {
                if (encryptedYear) {
                    Swal.close(); // Tutup popup loading setelah selesai
                    window.open(
                        route("export.aduan", {
                            startDate: customFormat(startDate.value),
                            endDate: customFormat(endDate.value),
                            site: "PIK",
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
</script>

<template>
    <Head title="Complaint" />

    <AuthenticatedLayout
        v-model:pages="pages"
        v-model:subMenu="subMenu"
        v-model:mainMenu="mainMenu"
    >
        <div class="py-12">
            <div class="min-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="flex flex-wrap -mx-3 mb-8">
                    <!-- card1 -->
                    <div
                        class="w-full max-w-full px-3 mb-6 sm:w-1/2 sm:flex-none xl:mb-0 xl:w-1/4"
                    >
                        <div
                            class="relative flex flex-col min-w-0 break-words bg-white shadow-xl dark:bg-slate-850 dark:shadow-dark-xl rounded-2xl bg-clip-border"
                        >
                            <div class="flex-auto p-4">
                                <div class="flex flex-row -mx-3">
                                    <div
                                        class="flex-none w-2/3 max-w-full px-3"
                                    >
                                        <div>
                                            <p
                                                class="mb-0 font-sans text-sm font-semibold leading-normal uppercase dark:text-white dark:opacity-60"
                                            >
                                                ADUAN OPEN
                                            </p>
                                            <h5
                                                class="mb-2 font-bold dark:text-white"
                                            >
                                                {{ props.open }}
                                            </h5>
                                            <!-- <p
                                                class="mb-0 dark:text-white dark:opacity-60"
                                            >
                                                <span
                                                    class="text-sm font-bold leading-normal text-emerald-500"
                                                    >+55%</span
                                                >
                                                since yesterday
                                            </p> -->
                                        </div>
                                    </div>
                                    <div class="px-3 text-right basis-1/3">
                                        <div
                                            class="inline-block w-12 h-12 text-center rounded-circle bg-gradient-to-tl from-blue-500 to-violet-500"
                                        >
                                            <i
                                                class="fas fa-envelope-open text-lg relative top-2.5 text-white"
                                            ></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- card2 -->
                    <div
                        class="w-full max-w-full px-3 mb-6 sm:w-1/2 sm:flex-none xl:mb-0 xl:w-1/4"
                    >
                        <div
                            class="relative flex flex-col min-w-0 break-words bg-white shadow-xl dark:bg-slate-850 dark:shadow-dark-xl rounded-2xl bg-clip-border"
                        >
                            <div class="flex-auto p-4">
                                <div class="flex flex-row -mx-3">
                                    <div
                                        class="flex-none w-2/3 max-w-full px-3"
                                    >
                                        <div>
                                            <p
                                                class="mb-0 font-sans text-sm font-semibold leading-normal uppercase dark:text-white dark:opacity-60"
                                            >
                                                ADUAN PROGRESS
                                            </p>
                                            <h5
                                                class="mb-2 font-bold dark:text-white"
                                            >
                                                {{ props.progress }}
                                            </h5>
                                            <!-- <p
                                                class="mb-0 dark:text-white dark:opacity-60"
                                            >
                                                <span
                                                    class="text-sm font-bold leading-normal text-red-600"
                                                    >-2%</span
                                                >
                                                since last quarter
                                            </p> -->
                                        </div>
                                    </div>
                                    <div class="px-3 text-right basis-1/3">
                                        <div
                                            class="inline-block w-12 h-12 text-center rounded-circle bg-gradient-to-tl from-yellow-500 to-yellow-400"
                                        >
                                            <i
                                                class="fas fa-clock text-lg relative top-2.5 text-white"
                                            ></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- card3 -->

                    <div
                        class="w-full max-w-full px-3 mb-6 sm:w-1/2 sm:flex-none xl:mb-0 xl:w-1/4"
                    >
                        <div
                            class="relative flex flex-col min-w-0 break-words bg-white shadow-xl dark:bg-slate-850 dark:shadow-dark-xl rounded-2xl bg-clip-border"
                        >
                            <div class="flex-auto p-4">
                                <div class="flex flex-row -mx-3">
                                    <div
                                        class="flex-none w-2/3 max-w-full px-3"
                                    >
                                        <div>
                                            <p
                                                class="mb-0 font-sans text-sm font-semibold leading-normal uppercase dark:text-white dark:opacity-60"
                                            >
                                                ADUAN CLOSED
                                            </p>
                                            <h5
                                                class="mb-2 font-bold dark:text-white"
                                            >
                                                {{ props.closed }}
                                            </h5>
                                            <!-- <p
                                                class="mb-0 dark:text-white dark:opacity-60"
                                            >
                                                <span
                                                    class="text-sm font-bold leading-normal text-emerald-500"
                                                    >+3%</span
                                                >
                                                since last week
                                            </p> -->
                                        </div>
                                    </div>
                                    <div class="px-3 text-right basis-1/3">
                                        <div
                                            class="inline-block w-12 h-12 text-center rounded-circle bg-gradient-to-tl from-green-600 to-green-600"
                                        >
                                            <i
                                                class="fas fa-check-square text-lg relative top-2.5 text-white"
                                            ></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- card4 -->
                    <div
                        class="w-full max-w-full px-3 sm:w-1/2 sm:flex-none xl:w-1/4"
                    >
                        <div
                            class="relative flex flex-col min-w-0 break-words bg-white shadow-xl dark:bg-slate-850 dark:shadow-dark-xl rounded-2xl bg-clip-border"
                        >
                            <div class="flex-auto p-4">
                                <div class="flex flex-row -mx-3">
                                    <div
                                        class="flex-none w-2/3 max-w-full px-3"
                                    >
                                        <div>
                                            <p
                                                class="mb-0 font-sans text-sm font-semibold leading-normal uppercase dark:text-white dark:opacity-60"
                                            >
                                                ADUN CANCEL
                                            </p>
                                            <h5
                                                class="mb-2 font-bold dark:text-white"
                                            >
                                                {{ props.cancel }}
                                            </h5>
                                            <!-- <p
                                                class="mb-0 dark:text-white dark:opacity-60"
                                            >
                                                <span
                                                    class="text-sm font-bold leading-normal text-emerald-500"
                                                    >+5%</span
                                                >
                                                than last month
                                            </p> -->
                                        </div>
                                    </div>
                                    <div class="px-3 text-right basis-1/3">
                                        <div
                                            class="inline-block w-12 h-12 text-center rounded-circle bg-gradient-to-tl from-red-500 to-red-500"
                                        >
                                            <i
                                                class="fas fa-ban text-lg relative top-2.5 text-white"
                                            ></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex flex-wrap -mx-3">
                    <div class="flex-none w-full max-w-full px-3">
                        <div
                            class="relative flex flex-col min-w-0 mb-6 break-words bg-white border-0 border-transparent border-solid shadow-xl dark:bg-slate-850 dark:shadow-dark-xl rounded-2xl bg-clip-border"
                        >
                            <div
                                class="p-6 pb-0 mb-0 border-b-0 border-b-solid rounded-t-2xl border-b-transparent"
                            >
                                <Link
                                    :href="route('aduanPik.create')"
                                    class="inline-block px-5 py-2.5 font-bold leading-normal text-center text-white align-middle transition-all bg-transparent rounded-lg cursor-pointer text-sm ease-in shadow-md bg-150 bg-gradient-to-tl from-zinc-800 to-zinc-700 dark:bg-gradient-to-tl dark:from-slate-750 dark:to-gray-850 hover:shadow-xs active:opacity-85 hover:-translate-y-px tracking-tight-rem bg-x-25"
                                >
                                    <i class="fas fa-plus"> </i>&nbsp;&nbsp;Add
                                    New Data
                                </Link>

                                                                <div
                                    class="flex flex-wrap md:flex-nowrap gap-3 mt-4"
                                >
                                    <div
                                        class="relative flex flex-wrap items-stretch w-48 transition-all rounded-lg ease"
                                    >
                                        <VueDatePicker
                                            required
                                            v-model="startDate"
                                            :format="customFormat"
                                            placeholder="Start Date"
                                            :enable-time-picker="false"
                                        />
                                    </div>
                                    <div
                                        class="relative flex flex-wrap items-stretch w-48 transition-all rounded-lg ease"
                                    >
                                        <VueDatePicker
                                            required
                                            v-model="endDate"
                                            :format="customFormat"
                                            placeholder="End Date"
                                            :enable-time-picker="false"
                                        />
                                    </div>
                                    <div
                                        class="relative flex flex-wrap items-stretch w-48 transition-all rounded-lg ease"
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
                                        @click="exportPdf"
                                        class="flex items-center text-sm justify-center gap-2 w-40 h-12 bg-gray-800 text-white font-semibold rounded-lg shadow-md transition-all duration-300 ease-in-out transform hover:bg-slate-850 hover:scale-105"
                                    >
                                        <i class="fas fa-download"></i>
                                        Rekap Aduan
                                    </button>
                                </div>
                            </div>
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
                                                        class="px-6 py-3 pl-2 font-bold text-left uppercase align-middle mb-0 text-sm leading-tight dark:text-white dark:opacity-80"
                                                    >
                                                        Progress
                                                    </th>
                                                    <th
                                                        class="px-6 py-3 pl-2 font-bold text-left uppercase align-middle mb-0 text-sm leading-tight dark:text-white dark:opacity-80"
                                                    >
                                                        Ticket Code
                                                    </th>
                                                    <th
                                                        class="px-6 py-3 pl-2 font-bold text-left uppercase align-middle mb-0 text-sm leading-tight dark:text-white dark:opacity-80"
                                                    >
                                                        Urgency
                                                    </th>
                                                    <th
                                                        class="px-6 py-3 font-bold text-center uppercase align-middle mb-0 text-sm leading-tight dark:text-white dark:opacity-80"
                                                    >
                                                        Nrp
                                                    </th>
                                                    <th
                                                        class="px-6 py-3 font-bold text-center uppercase align-middle mb-0 text-sm leading-tight dark:text-white dark:opacity-80"
                                                    >
                                                        Name
                                                    </th>
                                                    <th
                                                        class="px-6 py-3 font-bold text-center uppercase align-middle mb-0 text-sm leading-tight dark:text-white dark:opacity-80"
                                                    >
                                                        Category
                                                    </th>
                                                       <th
                                                        class="px-6 py-3 font-bold text-center uppercase align-middle mb-0 text-sm leading-tight dark:text-white dark:opacity-80"
                                                    >
                                                        Root Cause
                                                    </th>
                                                    <th
                                                        class="px-6 py-3 font-bold text-center uppercase align-middle mb-0 text-sm leading-tight dark:text-white dark:opacity-80"
                                                    >
                                                        Action Repair
                                                    </th>
                                                    <th
                                                        style="
                                                            min-width: 250px;
                                                            max-width: 400px;
                                                            word-break: break-word;
                                                            white-space: normal;
                                                            padding: 10px;
                                                        "
                                                        class="px-6 py-3 font-bold text-center uppercase align-middle mb-0 text-sm leading-tight dark:text-white dark:opacity-80"
                                                    >
                                                        Issue
                                                    </th>
                                                    <th
                                                        class="px-6 py-3 font-bold text-center uppercase align-middle mb-0 text-sm leading-tight dark:text-white dark:opacity-80"
                                                    >
                                                        crew
                                                    </th>
                                                    <th
                                                        class="px-6 py-3 font-bold text-center uppercase align-middle mb-0 text-sm leading-tight dark:text-white dark:opacity-80"
                                                    >
                                                        Status
                                                    </th>
                                                    <th
                                                        class="px-6 py-3 pl-2 font-bold text-left uppercase align-middle mb-0 text-sm leading-tight dark:text-white dark:opacity-80"
                                                    >
                                                        Report Date
                                                    </th>
                                                    <th
                                                        class="px-6 py-3 pl-2 font-bold text-left uppercase align-middle mb-0 text-sm leading-tight dark:text-white dark:opacity-80"
                                                    >
                                                        Start Response
                                                    </th>
                                                    <th
                                                        class="px-6 py-3 font-bold text-center uppercase align-middle mb-0 text-sm leading-tight dark:text-white dark:opacity-80"
                                                    >
                                                        Response Time
                                                    </th>
                                                    <th
                                                        class="px-6 py-3 font-bold text-center uppercase align-middle mb-0 text-sm leading-tight dark:text-white dark:opacity-80"
                                                    >
                                                        Start Progres
                                                    </th>
                                                    <th
                                                        class="px-6 py-3 font-bold text-center uppercase align-middle mb-0 text-sm leading-tight dark:text-white dark:opacity-80"
                                                    >
                                                        Finish Progress
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
                                                            {{ index + 1 }}
                                                        </span>
                                                    </td>
                                                    <td
                                                        class="p-2 text-sm leading-normal text-center align-middle bg-transparent border-b dark:border-white/40 whitespace-nowrap shadow-transparent"
                                                    >
                                                        <NavLinkCustom
                                                            v-if="
                                                                aduans.status !=
                                                                'CLOSED'
                                                            "
                                                            @click="
                                                                progressAduan(
                                                                    aduans.id
                                                                )
                                                            "
                                                            class="mr-3 mb-0 text-sm font-semibold leading-tight dark:text-white dark:opacity-80"
                                                        >
                                                            Progress Aduan
                                                        </NavLinkCustom>
                                                            <NavLinkCustom
                                                            v-if="
                                                                aduans.status ===
                                                                'OPEN'
                                                            "
                                                            @click="
                                                                acceptAduan(
                                                                    aduans.id
                                                                )
                                                            "
                                                            class="accept-btn mr-3 mb-0 text-sm font-semibold leading-tight dark:text-white dark:opacity-80"
                                                        >
                                                            Accept
                                                        </NavLinkCustom>
                                                    </td>
                                                    <td
                                                        class="p-2 align-middle bg-transparent border-b dark:border-white/40 whitespace-nowrap shadow-transparent"
                                                    >
                                                        <p
                                                            class="mb-0 text-sm font-semibold leading-tight dark:text-white dark:opacity-80"
                                                        >
                                                            {{
                                                                aduans.complaint_code
                                                            }}
                                                        </p>
                                                    </td>
                                                    <td
                                                        class="p-2 align-middle bg-transparent border-b dark:border-white/40"
                                                    >
                                                        <select
                                                            class="text-sm font-semibold leading-tight dark:text-white rounded-2"
                                                            @change="
                                                                updateUrgency(
                                                                    aduans.id,
                                                                    $event
                                                                        .target
                                                                        .value
                                                                )
                                                            "
                                                        >
                                                            <option
                                                                value="NORMAL"
                                                                :selected="
                                                                    aduans.urgency ===
                                                                    'NORMAL'
                                                                "
                                                            >
                                                                NORMAL
                                                            </option>
                                                            <option
                                                                value="URGENT"
                                                                :selected="
                                                                    aduans.urgency ===
                                                                    'URGENT'
                                                                "
                                                            >
                                                                URGENT
                                                            </option>
                                                        </select>
                                                    </td>
                                                    <td
                                                        class="p-2 align-middle bg-transparent border-b dark:border-white/40 whitespace-nowrap shadow-transparent"
                                                    >
                                                        <p
                                                            class="mb-0 text-sm font-semibold leading-tight dark:text-white dark:opacity-80"
                                                        >
                                                            {{ aduans.nrp }}
                                                        </p>
                                                    </td>
                                                    <td
                                                        class="p-2 text-center align-middle bg-transparent border-b dark:border-white/40 whitespace-nowrap shadow-transparent"
                                                    >
                                                        <span
                                                            class="mb-0 text-sm font-semibold leading-tight dark:text-white dark:opacity-80"
                                                        >
                                                            {{
                                                                aduans.complaint_name
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
                                                                aduans.category_name
                                                            }}
                                                        </span>
                                                    </td>
                                                       <td
                                                        class="p-2 text-center align-middle bg-transparent border-b dark:border-white/40 whitespace-nowrap shadow-transparent"
                                                    >
                                                        <span
                                                            class="mb-0 text-sm font-semibold leading-tight dark:text-white dark:opacity-80"
                                                        >
                                                              {{ aduans.root_cause?.root_cause_problem ?? '-' }}
                                                        </span>
                                                    </td>
                                                    <td
                                                        class="p-2 text-center align-middle bg-transparent border-b dark:border-white/40 whitespace-nowrap shadow-transparent"
                                                    >
                                                        <span
                                                            class="mb-0 text-sm font-semibold leading-tight dark:text-white dark:opacity-80"
                                                        >
                                                            {{
                                                                aduans.action_repair
                                                            }}
                                                        </span>
                                                    </td>
                                                    <td
                                                        class="p-2 align-middle bg-transparent border-b dark:border-white/40 whitespace-nowrap shadow-transparent"
                                                    >
                                                        <span
                                                            class="mb-0 text-sm break-words whitespace-pre-wrap font-semibold leading-tight dark:text-white dark:opacity-80"
                                                        >
                                                            {{
                                                                formatComplaint(
                                                                    aduans.complaint_note
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
                                                            {{ aduans.crew }}
                                                        </span>
                                                    </td>
                                                    <td
                                                        class="p-2 text-center align-middle bg-transparent border-b dark:border-white/40 whitespace-nowrap shadow-transparent"
                                                    >
                                                        <span
                                                            :class="{
                                                                'bg-gradient-to-tl from-emerald-500 to-teal-400 px-2.5 text-xs rounded-1.8 py-1.4 inline-block whitespace-nowrap text-center align-baseline font-bold uppercase leading-none text-white':
                                                                    aduans.status ===
                                                                    'CLOSED',
                                                                'bg-gradient-to-tl from-yellow-500 to-yellow-400 px-2.5 text-xs rounded-1.8 py-1.4 inline-block whitespace-nowrap text-center align-baseline font-bold uppercase leading-none text-white':
                                                                    aduans.status ===
                                                                    'PROGRESS',
                                                                'bg-gradient-to-tl from-blue-500 to-purple-400 px-2.5 text-xs rounded-1.8 py-1.4 inline-block whitespace-nowrap text-center align-baseline font-bold uppercase leading-none text-white':
                                                                    aduans.status ===
                                                                    'OPEN',
                                                                'bg-gradient-to-tl from-rose-500 to-rose-400 px-2.5 text-xs rounded-1.8 py-1.4 inline-block whitespace-nowrap text-center align-baseline font-bold uppercase leading-none text-white':
                                                                    aduans.status ===
                                                                    'CANCEL',
                                                                'bg-gradient-to-tl from-rose-500 to-rose-800 px-2.5 text-xs rounded-1.8 py-1.4 inline-block whitespace-nowrap text-center align-baseline font-bold uppercase leading-none text-white':
                                                                    aduans.status ===
                                                                    'OUTSTANDING',
                                                            }"
                                                        >
                                                            {{ aduans.status }}
                                                        </span>
                                                    </td>
                                                    <td
                                                        class="p-2 align-middle bg-transparent border-b dark:border-white/40 whitespace-nowrap shadow-transparent"
                                                    >
                                                        <p
                                                            class="mb-0 text-sm font-semibold leading-tight dark:text-white dark:opacity-80"
                                                        >
                                                            {{
                                                                aduans.date_of_complaint
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
                                                                aduans.start_response
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
                                                                aduans.response_time
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
                                                                aduans.start_progress
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
                                                                aduans.end_progress
                                                            }}
                                                        </span>
                                                    </td>

                                                    <td
                                                        class="p-2 text-sm leading-normal text-center align-middle bg-transparent border-b dark:border-white/40 whitespace-nowrap shadow-transparent"
                                                    >
                                                        <NavLinkCustom
                                                            @click="
                                                                detailData(
                                                                    aduans.id
                                                                )
                                                            "
                                                            class="ml-3 mb-0 text-sm font-semibold leading-tight dark:text-white dark:opacity-80"
                                                        >
                                                            Detail
                                                        </NavLinkCustom>

                                                        <NavLinkCustom
                                                            @click="
                                                                editData(
                                                                    aduans.id
                                                                )
                                                            "
                                                            class="ml-3 mb-0 text-sm font-semibold leading-tight dark:text-white dark:opacity-80"
                                                        >
                                                            Edit
                                                        </NavLinkCustom>

                                                        <NavLinkCustom
                                                            @click="
                                                                deleteData(
                                                                    aduans.id
                                                                )
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
