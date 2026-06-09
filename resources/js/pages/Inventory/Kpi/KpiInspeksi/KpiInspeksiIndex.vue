<style>
@import "datatables.net-dt";

/* Sembunyikan teks 'Press enter to select' */
.multiselect__option--highlight::after {
    display: none !important;
}

/* Tambahan styling-mu sebelumnya */
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
</style>

<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, useForm, router, usePage } from "@inertiajs/vue3";
import NavLinkCustom from "@/Components/NavLinkCustom.vue";
import moment from "moment";
import Swal from "sweetalert2";
import VueMultiselect from "vue-multiselect";
import Highcharts from "highcharts";
import brandDark from "highcharts/themes/brand-dark";
import brandLight from "highcharts/themes/brand-light";
import { ref, onMounted, computed, watch } from "vue";
import { Inertia } from "@inertiajs/inertia";
import axios from "axios";

const selectedOptionDept = ref({ name: "Laptop" });

const isComputer = computed(() => {
    return selectedOptionDept.value?.name === "Computer";
});

const isPrinter = computed(() => {
    return selectedOptionDept.value?.name === "Printer";
});

const isMt = computed(() => {
    return selectedOptionDept.value?.name === "Mobile Tower";
});

const pages = ref("Pages");
const subMenu = ref("KPI Inspeksi");
const mainMenu = ref("Chart KPI Inspeksi");
const props = defineProps({
    site: String,
});

// Fungsi untuk format tanggal
function formattedDate(date) {
    return moment(date).format("MMMM Do, YYYY HH:mm"); // Sesuaikan format sesuai kebutuhan
}

const year = ref(""); // State untuk input year
const triwulan = ref(""); // State untuk input triwulan
const bulan = ref(""); // State untuk input bulan

const validateYear = (event) => {
    const value = event.target.value;
    if (!/^\d*$/.test(value)) {
        year.value = value.replace(/\D/g, ""); // Hapus karakter selain angka
    }
};

const isADW = computed(() => {
    return props.site?.toUpperCase() === "ADW";
});

const searchData = async () => {
    const params = {
        device: selectedOptionDept.value.name,
        year: year.value,
    };

    if (isComputer.value && isADW.value) {
        params.month = bulan.value;
    } else if (isComputer.value != isADW.value) {
        params.quarter = triwulan.value;
    } else {
        params.month = bulan.value;
    }
    console.log(params);
    try {
        const site =
            props.site.charAt(0).toUpperCase() +
            props.site.slice(1).toLowerCase();

        const routeName = `kpi.inspeksiShow${site}`;

        const response = await axios.post(route(routeName), params);
        page.props.chartData = response.data.chartData;
    } catch (error) {
        console.log(error);
        Swal.fire("Gagal", "Tidak dapat mengambil data KPI", "error");
    }
};

const chartInspeksi = ref(null);

const page = usePage();

watch(
    () => page.props.chartData,
    (chartData) => {
        console.log(isADW.value);
        if (chartInspeksi.value && chartData?.labels?.length) {
            let periode = "";

            if (isComputer.value) {
                if (isADW.value) {
                    // Komputer dan site ADW -> bulan + tahun
                    periode = `COMPUTER BULAN ${bulan.value} ${year.value}`;
                } else {
                    // Komputer dan site bukan ADW -> hanya tahun
                    periode = `COMPUTER QUARTER ${triwulan.value} ${year.value}`;
                }
            } else if (isPrinter.value) {
                periode = `PRINTER BULAN ${bulan.value} ${year.value}`;
            } else if (isMt.value) {
                periode = `MOBILE TOWER BULAN ${bulan.value} ${year.value}`;
            } else {
                periode = `LAPTOP PERIODE ${year.value}`;
            }

            Highcharts.chart(chartInspeksi.value, {
                chart: {
                    type: "column",
                },
                title: {
                    text: `ACHIEVEMENT INSPEKSI ${periode}`,
                },
                xAxis: {
                    categories: chartData.labels,
                },
                yAxis: {
                    min: 0,
                    title: {
                        text: "Persentase (%)",
                    },
                },
                plotOptions: {
                    column: {
                        dataLabels: {
                            enabled: true,
                            format: "{y}%", // atau '{point.y}' jika tidak pakai simbol persen
                            style: {
                                fontSize: "12px",
                                color: "#000000",
                            },
                        },
                    },
                },
                series: [
                    {
                        name: "Sudah Inspeksi",
                        data: chartData.sudah_inspeksi,
                        color: "#28a745",
                    },
                    {
                        name: "Belum Inspeksi",
                        data: chartData.belum_inspeksi,
                        color: "#dc3545",
                    },
                ],
                credits: {
                    enabled: true,
                    text: "ICT PPA-AMM Development System",
                    href: null,
                    position: {
                        align: "right",
                        x: -10,
                        verticalAlign: "bottom",
                        y: -5,
                    },
                    style: {
                        fontSize: "10px",
                        color: "#666666",
                    },
                },
            });
        }
    }
);

onMounted(() => {
    // Set default tahun ke tahun saat ini
    year.value = new Date().getFullYear();

    // Panggil data awal
    searchData();
});
</script>

<template>
    <Head title="KPI Inspeksi" />

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
                                    <VueMultiselect
                                        v-model="selectedOptionDept"
                                        :options="[
                                            { name: 'Computer' },
                                            { name: 'Laptop' },
                                            { name: 'Printer' },
                                            { name: 'Mobile Tower' },
                                        ]"
                                        :multiple="false"
                                        :close-on-select="true"
                                        placeholder="Select Device"
                                        track-by="name"
                                        label="name"
                                    />
                                </div>
                                <div
                                    v-show="isComputer && !isADW"
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
                                        placeholder="Masukkan Quarter"
                                        @input="validateYear"
                                    />
                                </div>

                                <div
                                    v-show="isPrinter"
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
                                        v-model="bulan"
                                        type="number"
                                        min="1"
                                        max="12"
                                        step="1"
                                        class="pl-9 text-sm focus:shadow-primary-outline ease w-1/100 leading-5.6 relative -ml-px block min-w-0 flex-auto rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding py-2 pr-3 text-gray-700 transition-all placeholder:text-gray-500 focus:border-blue-500 focus:outline-none focus:transition-shadow"
                                        placeholder="Masukkan Bulan"
                                        @input="validateYear"
                                    />
                                </div>

                                <div
                                    v-show="isMt"
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
                                        v-model="bulan"
                                        type="number"
                                        min="1"
                                        max="12"
                                        step="1"
                                        class="pl-9 text-sm focus:shadow-primary-outline ease w-1/100 leading-5.6 relative -ml-px block min-w-0 flex-auto rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding py-2 pr-3 text-gray-700 transition-all placeholder:text-gray-500 focus:border-blue-500 focus:outline-none focus:transition-shadow"
                                        placeholder="Masukkan Bulan"
                                        @input="validateYear"
                                    />
                                </div>

                                <div
                                    v-show="isComputer && isADW"
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
                                        v-model="bulan"
                                        type="number"
                                        min="1"
                                        max="12"
                                        step="1"
                                        class="pl-9 text-sm focus:shadow-primary-outline ease w-1/100 leading-5.6 relative -ml-px block min-w-0 flex-auto rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding py-2 pr-3 text-gray-700 transition-all placeholder:text-gray-500 focus:border-blue-500 focus:outline-none focus:transition-shadow"
                                        placeholder="Masukkan Bulan"
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
                                <button
                                    @click="searchData"
                                    class="flex items-center text-sm justify-center gap-2 w-40 h-10 bg-gray-800 text-white font-semibold rounded-lg shadow-md transition-all duration-300 ease-in-out transform hover:bg-slate-850 hover:scale-105"
                                >
                                    <i class="fas fa-search"></i>
                                    Tampilkan Data
                                </button>
                            </div>
                            <div
                                class="relative flex flex-col min-w-0 mb-6 break-words bg-white border-0 border-transparent border-solid shadow-xl dark:bg-slate-850 dark:shadow-dark-xl rounded-2xl bg-clip-border"
                            >
                                <div
                                    class="relative flex flex-col min-w-0 break-words bg-white border-0 border-solid shadow-xl dark:bg-slate-850 dark:shadow-dark-xl dark:bg-gray-950 border-black-125 rounded-2xl bg-clip-border"
                                >
                                    <div class="p-4 pb-0 mb-0 rounded-t-4">
                                        <div class="flex justify-between">
                                            <h6 class="mb-2 dark:text-white">
                                                MONITORING INSPEKSI PPA SITE
                                                {{ props.site }}
                                            </h6>
                                        </div>
                                    </div>
                                    <div class="overflow-x-auto">
                                        <div
                                            ref="chartInspeksi"
                                            style="width: 100%; height: 400px"
                                        ></div>
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
