<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, useForm, router, usePage } from "@inertiajs/vue3";
import moment from "moment";
import Swal from "sweetalert2";
import Highcharts from "highcharts";
import PatternFill from "highcharts/modules/pattern-fill";

PatternFill(Highcharts);

import { ref, onMounted, computed, watch } from "vue";
import axios from "axios";

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

const searchData = async () => {
    const params = {
        year: year.value,
    };

    try {
        const site =
            props.site.charAt(0).toUpperCase() +
            props.site.slice(1).toLowerCase();

        // const routeName = `chart.inspeksiShow${site}`;
        const routeName = `chartMip.inspeksiShow`;

        const response = await axios.post(route(routeName), params);
        page.props.chartData = response.data.chartData;
    } catch (error) {
        console.log(error);
        Swal.fire("Gagal", "Tidak dapat mengambil data KPI", "error");
    }
};

const chartInspeksi = ref(null);
const chartLaptop = ref(null);
const chartKomputer = ref(null);
const chartPrinter = ref(null);
const chartMobileTower = ref(null);

const page = usePage();

watch(
    () => page.props.chartData,
    (chartData) => {
        const baseColumnOptions = {
            chart: {
                type: "column",
            },
            legend: {
                enabled: true,
            },
            yAxis: {
                min: 0,
                max: 100,
                tickInterval: 10,
                title: {
                    text: "Persentase (%)",
                },
            },
            plotOptions: {
                column: {
                    dataLabels: {
                        enabled: true,
                        format: "{y}%",
                    },
                },
            },
            tooltip: {
                useHTML: true,
                pointFormat:
                    '<span style="color:{series.color}">\u25CF</span> ' +
                    "<b>{series.name}</b>: {point.y}%",
            },
            credits: {
                enabled: true,
                text: "ICT PPA-AMM Development System",
                href: null,
            },
        };

        Highcharts.chart(chartLaptop.value, {
            ...baseColumnOptions,

            title: {
                text: "Historical Laptop Achievement " + year.value,
            },

            xAxis: {
                categories: chartData.labelsLaptop,
            },

            series: [
                {
                    name: "Plan",
                    color: { patternIndex: 0 },
                    data: [95],
                },
                {
                    name: "Actual",
                    color: "#007bff",
                    data: chartData.persenLaptop,
                },
            ],
        });

        Highcharts.chart(chartKomputer.value, {
            ...baseColumnOptions,

            title: {
                text: "Historical Komputer Achievement " + year.value,
            },

            xAxis: {
                categories: [
                    "Triwulan 1",
                    "Triwulan 2",
                    "Triwulan 3",
                    "Triwulan 4",
                ],
            },

            series: [
                {
                    name: "Plan",
                    color: { patternIndex: 2 },
                    data: [95, 95, 95, 95],
                },
                {
                    name: "Actual",
                    color: "#28a745",
                    data: chartData.persenKomputer,
                },
            ],
        });

        Highcharts.chart(chartPrinter.value, {
            ...baseColumnOptions,

            title: {
                text: "Historical Printer Achievement " + year.value,
            },

            xAxis: {
                categories: [
                    "Januari",
                    "Februari",
                    "Maret",
                    "April",
                    "Mei",
                    "Juni",
                    "Juli",
                    "Agustus",
                    "September",
                    "Oktober",
                    "November",
                    "Desember",
                ],
            },

            series: [
                {
                    name: "Plan",
                    color: { patternIndex: 3 },
                    data: [95, 95, 95, 95, 95, 95, 95, 95, 95, 95, 95, 95],
                },
                {
                    name: "Actual",
                    color: "#ff3f00",
                    data: chartData.persenPrinter,
                },
            ],
        });

        Highcharts.chart(chartMobileTower.value, {
            ...baseColumnOptions,

            title: {
                text: "Historical MT Achievement " + year.value,
            },

            xAxis: {
                categories: [
                    "Januari",
                    "Februari",
                    "Maret",
                    "April",
                    "Mei",
                    "Juni",
                    "Juli",
                    "Agustus",
                    "September",
                    "Oktober",
                    "November",
                    "Desember",
                ],
            },

            series: [
                {
                    name: "Plan",
                    color: { patternIndex: 1 },
                    data: [95, 95, 95, 95, 95, 95, 95, 95, 95, 95, 95, 95],
                },
                {
                    name: "Actual",
                    color: "#544FC5",
                    data: chartData.persenMT,
                },
            ],
        });
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
    <Head title="Grafik Inspeksi" />

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
                                                GRAFIK PENCAPAIAN INSPEKSI
                                                LAPTOP PPA SITE
                                                {{ props.site }}
                                            </h6>
                                        </div>
                                    </div>
                                    <div class="overflow-x-auto">
                                        <div
                                            ref="chartLaptop"
                                            style="width: 100%; height: 400px"
                                        ></div>
                                    </div>
                                </div>
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
                                                GRAFIK PENCAPAIAN INSPEKSI
                                                KOMPUTER PPA SITE
                                                {{ props.site }}
                                            </h6>
                                        </div>
                                    </div>
                                    <div class="overflow-x-auto">
                                        <div
                                            ref="chartKomputer"
                                            style="width: 100%; height: 400px"
                                        ></div>
                                    </div>
                                </div>
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
                                                GRAFIK PENCAPAIAN INSPEKSI
                                                PRINTER PPA SITE
                                                {{ props.site }}
                                            </h6>
                                        </div>
                                    </div>
                                    <div class="overflow-x-auto">
                                        <div
                                            ref="chartPrinter"
                                            style="width: 100%; height: 400px"
                                        ></div>
                                    </div>
                                </div>
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
                                                GRAFIK PENCAPAIAN INSPEKSI
                                                MOBILE TOWER PPA SITE
                                                {{ props.site }}
                                            </h6>
                                        </div>
                                    </div>
                                    <div class="overflow-x-auto">
                                        <div
                                            ref="chartMobileTower"
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
