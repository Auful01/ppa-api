<script setup>
import GuestLayoutCrud from "@/Layouts/GuestLayoutDashboard.vue";
import { Head, Link, useForm, router } from "@inertiajs/vue3";
import NavLinkCustom from "@/Components/NavLinkCustom.vue";
import moment from "moment";
import Swal from "sweetalert2";
import { ref, onMounted } from "vue";
import { Inertia } from "@inertiajs/inertia";

// Fungsi untuk format tanggal
function formattedDate(date) {
    return moment(date).format("MMMM Do, YYYY"); // Sesuaikan format sesuai kebutuhan
}

const mount = onMounted(() => {
    // Inisialisasi DataTable tanpa AJAX
    $("#tableData").DataTable();
});

const props = defineProps({
    aduan: {
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

// State pencarian
const formSearch = useForm({
    search: "",
});

// Fungsi untuk melakukan pencarian
const search = () => {
    Swal.fire({
        title: "Sedang Mencari Data...",
        text: "Mohon tunggu sebentar...",
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        },
    });
    formSearch.get(route("guestAduan.page"), {
        preserveState: true,
        onSuccess: (response) => {
            console.log(response.props.aduan.length);
            if (response.props.aduan.length === 1) {
                Swal.fire({
                    title: "Success",
                    text: "Data complaint ditemukan!",
                    icon: "success",
                    confirmButtonText: "OK",
                    confirmButtonColor: "#3085d6",
                }).then(() => {
                    window.location.reload();
                });
            } else {
                Swal.fire({
                    title: "error!",
                    text: "Data tidak ditemukan!",
                    icon: "error",
                    confirmButtonText: "OK",
                    confirmButtonColor: "#3085d6",
                }).then(() => {
                    Inertia.get(route("guestAduan.page")); // Reload halaman setelah konfirmasi SweetAlert
                });
            }
        },
        onError: () => {
            Swal.fire({
                title: "error!",
                text: "Data tidak ditemukan!",
                icon: "waring",
                confirmButtonText: "OK",
                confirmButtonColor: "#3085d6",
            });
        },
    });
};

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
            form.delete(route("guestAduan.delete", { id: id }), {
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

const file = ref(null);

const handleFileUpload = (event) => {
    file.value = event.target.files[0];
};

function formatData(text) {
    const maxLength = 20; // Set your limit here
    return text.length > maxLength ? text.slice(0, maxLength) + "..." : text;
}

const canAddData = ref(true);

// Fungsi tampilkan rating bintang dengan SweetAlert2
const showStarRating = async (ticketCode, complaintId, complaint_note) => {
    return new Promise((resolve) => {
        let selectedRating = 0;

        const renderStars = () => {
            let html = "";
            for (let i = 1; i <= 5; i++) {
                html += `
          <i 
            class="fa-star fa-2x fas star ${
                i <= selectedRating ? "text-yellow-400" : "text-gray-400"
            }"
            data-value="${i}"
            style="cursor: pointer; margin: 0 4px;"
          ></i>`;
            }
            return html;
        };

        Swal.fire({
            title: "Beri Rating Kepuasan",
            html: `
        <p>Untuk tiket: <strong>${ticketCode}</strong></p>
        <p>Note aduan: <strong>${complaint_note}</strong></p>
        <div id="star-container" style="display: flex; justify-content: center; margin-top: 10px;">
          ${renderStars()}
        </div>
      `,
            confirmButtonText: "Kirim",
            focusConfirm: false,
            allowOutsideClick: false, // ⛔ tidak bisa klik luar
            allowEscapeKey: false, // ⛔ tidak bisa ESC
            customClass: {
                confirmButton:
                    "bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded",
            },
            didOpen: () => {
                const container =
                    Swal.getHtmlContainer().querySelector("#star-container");
                container.addEventListener("click", (e) => {
                    if (e.target.classList.contains("star")) {
                        selectedRating = parseInt(e.target.dataset.value);
                        container.innerHTML = renderStars();
                    }
                });
            },
            preConfirm: () => {
                if (selectedRating === 0) {
                    Swal.showValidationMessage("Pilih minimal 1 bintang");
                    return false;
                }
                return selectedRating;
            },
        }).then((result) => {
            if (result.isConfirmed && result.value > 0) {
                resolve(result.value);
            }
        });
    });
};

// Ambil semua aduan CLOSED milik user yang belum diberi rating
const fetchUnratedComplaints = async () => {
    try {
        const { data } = await axios.get("/user-unrated-closed-complaints");

        if (data.length > 0) {
            canAddData.value = false;

            for (const complaint of data) {
                const rating = await showStarRating(
                    complaint.complaint_code,
                    complaint.id,
                    complaint.complaint_note
                );

                if (rating) {
                    // console.log(rating);
                    router.post(
                        route("guestAduan.storeRating"),
                        {
                            complaint_id: complaint.id,
                            rating: rating,
                        },
                        {
                            preserveScroll: true,
                            preserveState: true,
                            onSuccess: () => {
                                console.log("Rating dikirim via Inertia");
                            },
                        }
                    );
                }
            }

            canAddData.value = true;
            Swal.fire({
                title: "Terima kasih!",
                text: "Rating Anda berhasil dikirim.",
                icon: "success",
                confirmButtonText: "OK",
                allowOutsideClick: false, // ⛔ tidak bisa klik luar
                allowEscapeKey: false, // ⛔ tidak bisa ESC
                customClass: {
                    confirmButton:
                        "bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded",
                },
            });
        }
    } catch (error) {
        console.error("Gagal cek rating:", error);
    }
};

// Jalankan saat halaman dimuat
onMounted(() => {
    fetchUnratedComplaints();
});

// Fungsi ketika klik tombol
const handleAddClick = async () => {
    if (!canAddData.value) {
        await fetchUnratedComplaints();
    }

    if (canAddData.value) {
        router.visit(route("guestAduan.create"));
    }
};
</script>

<template>
    <Head title="Complaint User" />

    <GuestLayoutCrud>
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
                        Complaint Pages
                    </li>
                </ol>
                <h6 class="mb-0 font-bold text-white capitalize">
                    All Complaint
                </h6>
            </nav>
        </template>

        <div class="py-12">
            <div class="min-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="flex flex-wrap -mx-3 mb-3">
                    <form
                        @submit.prevent="search"
                        enctype="multipart/form-data"
                    >
                        <div class="flex flex-wrap">
                            <div
                                class="max-w-full sm:w-2/3 md:w-2/3 lg:w-2/3 px-3"
                            >
                                <div
                                    class="h-11 relative flex flex-wrap items-stretch w-full transition-all rounded-lg ease"
                                >
                                    <span
                                        class="text-sm ease leading-5.6 absolute z-50 -ml-px flex h-full items-center whitespace-nowrap rounded-lg rounded-tr-none rounded-br-none border border-r-0 border-transparent bg-transparent py-2 px-2.5 text-center font-normal text-slate-500 transition-all"
                                    >
                                        <i class="fas fa-search"></i>
                                    </span>
                                    <input
                                        type="text"
                                        v-model="formSearch.search"
                                        class="pl-9 text-sm focus:shadow-primary-outline ease w-1/100 leading-5.6 relative -ml-px block min-w-0 flex-auto rounded-lg border border-solid border-gray-300 dark:bg-slate-850 dark:text-white bg-white bg-clip-padding py-2 pr-3 text-gray-700 transition-all placeholder:text-gray-500 focus:border-blue-500 focus:outline-none focus:transition-shadow"
                                        placeholder="Find Ticket Code..."
                                    />
                                </div>
                            </div>
                            <div
                                class="max-w-full mobile-sm:px-3 mobile-sm:mt-1"
                            >
                                <button
                                    type="submit"
                                    class="h-11 htext-gray-900 bg-white border border-gray-300 focus:outline-none hover:bg-gray-100 focus:ring-4 focus:ring-gray-100 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 dark:bg-gray-800 dark:text-white dark:border-gray-600 dark:hover:bg-gray-700 dark:hover:border-gray-600 dark:focus:ring-gray-700"
                                >
                                    Search
                                </button>
                            </div>
                        </div>
                    </form>
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
                                    :href="route('guestAduan.create')"
                                    class="inline-block px-5 py-2.5 font-bold leading-normal text-center text-white align-middle transition-all bg-transparent rounded-lg cursor-pointer text-sm ease-in shadow-md bg-150 bg-gradient-to-tl from-zinc-800 to-zinc-700 dark:bg-gradient-to-tl dark:from-slate-750 dark:to-gray-850 hover:shadow-xs active:opacity-85 hover:-translate-y-px tracking-tight-rem bg-x-25"
                                >
                                    <i class="fas fa-plus"> </i>&nbsp;&nbsp;Add
                                    New Data
                                </Link>
                            </div>
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
                                                        class="px-6 py-3 font-bold uppercase align-middle mb-0 text-sm leading-tight dark:text-white dark:opacity-80"
                                                    >
                                                        #
                                                    </th>
                                                    <th
                                                        class="px-6 py-3 pl-2 font-bold text-left uppercase align-middle mb-0 text-sm leading-tight dark:text-white dark:opacity-80"
                                                    >
                                                        Ticket Code
                                                    </th>
                                                    <th
                                                        class="px-6 py-3 pl-2 font-bold text-left uppercase align-middle mb-0 text-sm leading-tight dark:text-white dark:opacity-80"
                                                    >
                                                        Issue
                                                    </th>
                                                    <th
                                                        class="px-6 py-3 pl-2 font-bold text-left uppercase align-middle mb-0 text-sm leading-tight dark:text-white dark:opacity-80"
                                                    >
                                                        Report Date
                                                    </th>
                                                    <th
                                                        class="px-6 py-3 font-bold text-center uppercase align-middle mb-0 text-sm leading-tight dark:text-white dark:opacity-80"
                                                    >
                                                        Status
                                                    </th>
                                                    <th
                                                        class="px-6 py-3 font-bold text-center uppercase align-middle mb-0 text-sm leading-tight dark:text-white dark:opacity-80"
                                                    >
                                                        Rating
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
                                                        class="p-2 align-middle bg-transparent border-b dark:border-white/40 whitespace-nowrap shadow-transparent"
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
                                                                aduans.complaint_code
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
                                                                aduans.date_of_complaint
                                                            }}
                                                        </p>
                                                    </td>
                                                    <td
                                                        class="p-2 align-middle bg-transparent border-b dark:border-white/40 whitespace-nowrap shadow-transparent"
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
                                                            }"
                                                        >
                                                            {{ aduans.status }}
                                                        </span>
                                                    </td>

                                                    <td
                                                        class="p-2 space-x-1 text-yellow-500"
                                                    >
                                                        <template
                                                            v-for="i in 5"
                                                        >
                                                            <svg
                                                                v-if="
                                                                    i <=
                                                                    aduans.user_rating
                                                                "
                                                                :key="
                                                                    'filled-' +
                                                                    i
                                                                "
                                                                xmlns="http://www.w3.org/2000/svg"
                                                                class="h-5 w-5 fill-current"
                                                                viewBox="0 0 20 20"
                                                            >
                                                                <path
                                                                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.2 3.685h3.875c.969 0 1.371 1.24.588 1.81l-3.136 2.28 1.2 3.685c.3.921-.755 1.688-1.54 1.118L10 13.347l-3.136 2.28c-.785.57-1.84-.197-1.54-1.118l1.2-3.685-3.136-2.28c-.783-.57-.38-1.81.588-1.81H7.85l1.2-3.685z"
                                                                />
                                                            </svg>
                                                            <svg
                                                                v-else
                                                                :key="
                                                                    'empty-' + i
                                                                "
                                                                xmlns="http://www.w3.org/2000/svg"
                                                                class="h-5 w-5 text-gray-300"
                                                                fill="none"
                                                                viewBox="0 0 24 24"
                                                                stroke="currentColor"
                                                            >
                                                                <path
                                                                    stroke-linecap="round"
                                                                    stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.2 3.685h3.875c.969 0 1.371 1.24.588 1.81l-3.136 2.28 1.2 3.685c.3.921-.755 1.688-1.54 1.118L12 13.347l-3.136 2.28c-.785.57-1.84-.197-1.54-1.118l1.2-3.685-3.136-2.28c-.783-.57-.38-1.81.588-1.81h3.875l1.2-3.685z"
                                                                />
                                                            </svg>
                                                        </template>
                                                    </td>

                                                    <td
                                                        class="p-2 text-sm leading-normal align-middle bg-transparent border-b dark:border-white/40 whitespace-nowrap shadow-transparent"
                                                    >
                                                        <NavLinkCustom
                                                            @click="
                                                                deleteData(
                                                                    aduans.id
                                                                )
                                                            "
                                                            class="mb-0 text-sm font-semibold leading-tight dark:text-white dark:opacity-80"
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
    </GuestLayoutCrud>
</template>
