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

.swal2-close {
    color: red !important;
    font-size: 1.5rem !important;
    top: 10px !important;
    right: 10px !important;
}

.swal2-close:hover {
    color: darkred !important;
}

.swal2-popup {
    max-width: 90% !important; /* agar tidak full lebar */
    max-height: 90vh !important; /* batasi tinggi */
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
import VueDatePicker from "@vuepic/vue-datepicker";
import "@vuepic/vue-datepicker/dist/main.css";
import moment from "moment";
import Swal from "sweetalert2";
import axios from "axios";
import { ref, onMounted, watch, nextTick } from "vue";

const pages = ref("Pages");
const subMenu = ref("Pengalihan Asset Pages");
const mainMenu = ref("Pengalihan Asset");

function formatTanggal(dateStr) {
    if (!dateStr) return "-";
    const date = new Date(dateStr);

    const tanggal = date.getDate().toString().padStart(2, '0');
    const bulan = date.toLocaleString('id-ID', { month: 'long' });
    const tahun = date.getFullYear();
    const jam = date.getHours().toString().padStart(2, '0');
    const menit = date.getMinutes().toString().padStart(2, '0');

    return `${tanggal} ${bulan} ${tahun} ${jam}.${menit}`;
}

const props = defineProps({
    inspeksiLaptopx: Array,
    crew: Array,
    site: Object,
});

// const site = props.site;

const form = useForm({});

const options = props.crew;
const startDate = ref(null);
const endDate = ref(null);
const selectedValues = ref(null); // Awalnya array kosong
const showValidation = ref(false);
const selectedOption = ref({ name: "Computer" });
const selectedOptionTable = ref({ name: "Computer" });
const data = ref([]);
const tableRef = ref(null);
const renderTable = ref(true);
let dataTableInstance = null;

const customFormat = (date) => {
    if (!date) return null;
    const d = new Date(date);
    return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(
        2,
        "0"
    )}-${String(d.getDate()).padStart(2, "0")}`;
};

const isActive = (href) => {
    return window.location.pathname === href;
};

const navLinkClass = (href) => {
    return isActive(href)
        ? "inline-flex items-center px-1 pt-1 border-b-2 border-indigo-400 text-sm font-medium leading-5 text-gray-900 focus:outline-none focus:border-indigo-700 transition duration-150 ease-in-out"
        : "inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out";
};

window.showImagePopup = function (src) {
    Swal.fire({
        imageUrl: src,
        imageAlt: "Full Image",
        showCloseButton: true,
        showConfirmButton: false,
        background: "#f9f9f9",
        width: "auto",
        padding: "2rem", // menambahkan ruang di sekitar
        customClass: {
            image: "custom-image-zoom-out",
        },
    });
};

const initDataTable = () => {
    if (!tableRef.value) return;

    if ($.fn.DataTable.isDataTable(tableRef.value)) {
        dataTableInstance = $(tableRef.value).DataTable();
        return;
    }

    dataTableInstance = $(tableRef.value).DataTable({
        dom: "fBrtilp",
        scrollY: "40vh",
        scrollCollapse: true,
        data: [],
        buttons: [],
        initComplete: function () {
            const btns = $(".dt-button");
            btns.addClass(
                "text-white bg-gradient-to-r from-green-600 via-green-700 to-green-900 hover:bg-gradient-to-br focus:ring-4 focus:outline-none focus:ring-green-300 dark:focus:ring-green-800 font-medium rounded-lg text-sm px-5 py-2.5 text-center me-2 mb-2"
            );
            btns.removeClass("dt-button");
        },
    });
};

const populateDataTable = (rows) => {
    if (!dataTableInstance) return;

    const formattedRows = rows.map((item, index) => {
        const styledSpan = (text) => `
            <span class="mb-0 text-sm font-semibold leading-tight dark:text-white dark:opacity-80">
                ${text || "-"}
            </span>`;

        const renderImage = (src) => {
            if (!src) return styledSpan("-");
            return `
                <img
                    src="${src}"
                    alt="Image"
                    class="w-[75px] h-[75px] object-cover rounded border border-gray-300 shadow cursor-pointer"
                    onclick="window.showImagePopup('${src}')"
                />
            `;
        };

        const detailHref = `/pengalihan-asset/${item.id}`;
        const editHref = `/pengalihan-asset/${item.id}/edit`;

        return [
            styledSpan(index + 1),
            styledSpan(item.dept_prev),
            styledSpan(item.dept),
            styledSpan(item.no_inventory_sebelumnya),
            styledSpan(item.no_inventory_pengalihan),
            styledSpan(item.user_sebelumnya),
            styledSpan(item.user_pengalihan),
            styledSpan(item.tanggal_pengalihan),
            renderImage(item.foto_pengalihan),
            styledSpan(item.remark),
            `
            <a href="#" class="${navLinkClass(
                detailHref
            )} detail-link" data-id="${item.id}">Detail</a>
            <a href="#" class="${navLinkClass(editHref)} edit-link" data-id="${
                item.id
            }">Edit</a>
            `,
        ];
    });

    dataTableInstance.clear();
    dataTableInstance.rows.add(formattedRows).draw();

    $(document).off("click", ".edit-link");
    $(document).on("click", ".edit-link", function (e) {
        e.preventDefault();
        const id = this.getAttribute("data-id");
        if (id) editData(id);
    });

    $(document).off("click", ".detail-link");
    $(document).on("click", ".detail-link", function (e) {
        e.preventDefault();
        const id = this.getAttribute("data-id");
        if (id) detailData(id);
    });
};

const fetchData = async () => {
    const device = selectedOption.value?.name;
    if (!device) return;

    const start = startDate.value ? customFormat(startDate.value) : null;
    const end = endDate.value ? customFormat(endDate.value) : null;

    const params = {
        device_type: device,
        site: props.site,
    };

    if (start && !end) {
        params.startDate = start;
        params.endDate = customFormat(new Date());
    } else if (start && end) {
        params.startDate = start;
        params.endDate = end;
    } else if (!start && end) {
        params.endDate = end;
    }

    console.log("📤 Params yang dikirim:", params);

    try {
        const response = await axios.get("/pengalihan-asset-by-device-range", {
            params,
        });

        console.log("📥 Response dari server:", response.data);

        const codeColumn = selectedOption.value?.code_column || "laptop_code";

        const flatData = response.data.map((item) => {
            const p = item.pengalihan || {};
            const prev = item.inventory_prev || {};

            const userPrevName = item.user_prev_name || "-";
            const userPrevPosition = item.user_prev_position || "-";
            const userPrevDept = item.user_prev_dept || "-";
            const userNextName = item.user_next_name || "-";
            const userNextPosition = item.user_next_position || "-";
            const userNextDept = item.user_next_dept || "-";

            return {
                id: p.id,
                dept_prev: p.dept_prev || "-",
                dept: p.dept || "-",
                no_inventory_sebelumnya: prev || "-",
                no_inventory_pengalihan: p.inv_number_next || "-",
                user_sebelumnya: `${p.nrp_user_prev || "-"} - ${userPrevDept} - ${userPrevName} - ${userPrevPosition}`,
                user_pengalihan: `${p.nrp_user_new || "-"} - ${userNextDept} - ${userNextName} - ${userNextPosition}`,
                tanggal_pengalihan: formatTanggal(p.created_at) || "-",
                foto_pengalihan: p.foto_pengalihan || null,
                remark: p.remark || "-",
            };
        });

        data.value = flatData;

        renderTable.value = false;
        await nextTick();
        renderTable.value = true;
        await nextTick();

        initDataTable();
        populateDataTable(data.value);
    } catch (error) {
        if (error.response) {
            console.error("❌ Error response:", error.response.data);
            console.error("📄 Status:", error.response.status);
        } else if (error.request) {
            console.error("📡 No response received:", error.request);
        } else {
            console.error("🔧 Error setting up request:", error.message);
        }

        data.value = [];
        renderTable.value = false;
    }
};


onMounted(async () => {
    await nextTick();
    initDataTable();
    fetchData(); // Panggil walau startDate dan endDate belum diisi
});

watch([selectedOption, startDate, endDate], ([newOption, newStart, newEnd]) => {
    if (newOption?.name) {
        fetchData();
    }
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
            form.get(route("pengalihanAsset.edit", { id: id }));
        }
    });
};

const detailData = (id) => {
    form.get(route("pengalihanAsset.detail", { id: id }));
};

const exportPdf = () => {
    console.log(customFormat(startDate.value));
    console.log(customFormat(endDate.value));
    console.log(props.site);
    console.log(selectedOption.value.name);
    // console.log(selectedValues.value.name);

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
        route("pica.export"),
        {
            startDate: customFormat(startDate.value),
            endDate: customFormat(endDate.value),
            site: props.site,
            device: selectedOption.value.name,
            pic: selectPic,
        },
        {
            onSuccess: ({ props }) => {
                if (encryptedYear) {
                    Swal.close(); // Tutup popup loading setelah selesai
                    window.open(
                        route("export.picaInspeksi", {
                            startDate: customFormat(startDate.value),
                            endDate: customFormat(endDate.value),
                            site: props.site,
                            device: selectedOption.value.name,
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
    <Head title="Inspeksi Laptop" />

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
                                class="relative flex flex-wrap items-stretch w-48 transition-all rounded-lg ease mb-4"
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
                            <div
                                class="relative flex flex-wrap items-stretch w-48 transition-all rounded-lg ease mb-4"
                            >
                                <VueDatePicker
                                    v-model="startDate"
                                    :format="customFormat"
                                    placeholder="Start Date"
                                    :enable-time-picker="false"
                                />
                            </div>
                            <div
                                class="relative flex flex-wrap items-stretch w-48 transition-all rounded-lg ease mb-4"
                            >
                                <VueDatePicker
                                    v-model="endDate"
                                    :format="customFormat"
                                    placeholder="End Date"
                                    :enable-time-picker="false"
                                />
                            </div>
                            <!-- <div
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
                                @click="exportPdf"
                                class="flex items-center text-sm justify-center gap-2 w-40 h-12 bg-black text-white font-semibold rounded-lg shadow-md transition-all duration-300 ease-in-out transform hover:bg-slate-850 hover:scale-105"
                            >
                                <i class="fas fa-download"></i>
                                Rekap Inspeksi
                            </button> -->
                        </div>
                        <div
                            class="relative flex flex-col min-w-0 mb-6 break-words bg-white border-0 border-transparent border-solid shadow-xl dark:bg-slate-850 dark:shadow-dark-xl rounded-2xl bg-clip-border"
                        >
                            <div class="flex-auto px-0 pt-0 pb-2">
                                <div class="p-0">
                                    <div class="p-6 text-gray-900">
                                        <div
                                            class="flex flex-wrap md:flex-nowrap gap-4"
                                        >
                                            <div
                                                class="relative flex flex-wrap items-stretch w-48 transition-all rounded-lg ease mb-4"
                                            >
                                                <Link
                                                    :href="
                                                        route(
                                                            'pengalihanAsset.create',
                                                            { site: props.site }
                                                        )
                                                    "
                                                    class="flex items-center text-sm justify-center gap-2 w-40 h-12 bg-black text-white font-semibold rounded-lg shadow-md transition-all duration-300 ease-in-out transform hover:bg-slate-850 hover:scale-105"
                                                >
                                                    <i
                                                        class="fa-solid fa-arrow-right-arrow-left"
                                                    ></i>
                                                    &nbsp;&nbsp;Alihkan Asset
                                                </Link>
                                            </div>
                                        </div>

                                        <table
                                            ref="tableRef"
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
                                                        Dept Asal
                                                    </th>
                                                    <th
                                                        class="px-6 py-3 font-bold text-center uppercase align-middle mb-0 text-sm leading-tight dark:text-white dark:opacity-80"
                                                    >
                                                        Dept Baru
                                                    </th>
                                                    <th
                                                        class="px-6 py-3 pl-2 font-bold text-left uppercase align-middle mb-0 text-sm leading-tight dark:text-white dark:opacity-80"
                                                    >
                                                        No Inventory Sebelumnya
                                                    </th>
                                                    <th
                                                        class="px-6 py-3 pl-2 font-bold text-left uppercase align-middle mb-0 text-sm leading-tight dark:text-white dark:opacity-80"
                                                    >
                                                        No Inventory Pengalihan
                                                    </th>

                                                    <th
                                                        class="px-6 py-3 font-bold text-center uppercase align-middle mb-0 text-sm leading-tight dark:text-white dark:opacity-80"
                                                    >
                                                        User Sebelumnya
                                                    </th>
                                                    <th
                                                        class="px-6 py-3 font-bold text-center uppercase align-middle mb-0 text-sm leading-tight dark:text-white dark:opacity-80"
                                                    >
                                                        User Pengalihan
                                                    </th>
                                                    <th
                                                        class="px-6 py-3 font-bold text-center uppercase align-middle mb-0 text-sm leading-tight dark:text-white dark:opacity-80"
                                                    >
                                                        Tanggal Peengalihan
                                                    </th>
                                                    <th
                                                        class="px-6 py-3 font-bold text-center uppercase align-middle mb-0 text-sm leading-tight dark:text-white dark:opacity-80"
                                                    >
                                                        Foto Pengalihan
                                                    </th>
                                                    <th
                                                        class="px-6 py-3 font-bold text-center uppercase align-middle mb-0 text-sm leading-tight dark:text-white dark:opacity-80"
                                                    >
                                                        Remark
                                                    </th>
                                                    <th
                                                        class="px-6 py-3 font-bold text-center uppercase align-middle mb-0 text-sm leading-tight dark:text-white dark:opacity-80"
                                                    >
                                                        Action
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody>
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
