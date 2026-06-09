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
import EasyDataTable from "vue3-easy-data-table";
import "vue3-easy-data-table/dist/style.css";
import brandLight from "highcharts/themes/brand-light";
import { ref, onMounted, computed, watch } from "vue";
import { Inertia } from "@inertiajs/inertia";
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

        const routeName = `kpi.jobAnalysisShow${site}`;

        const response = await axios.post(route(routeName), params);
        page.props.chartData = response.data.chartData;
    } catch (error) {
        Swal.fire("Gagal", "Tidak dapat mengambil data KPI", "error");
    }
};

const chartInspeksi = ref(null);

const page = usePage();

const getMonthNumber = (monthName) => {
    const months = {
        JAN: "01",
        FEB: "02",
        MAR: "03",
        APR: "04",
        MEI: "05",
        JUN: "06",
        JUL: "07",
        AGU: "08",
        SEP: "09",
        OKT: "10",
        NOV: "11",
        DES: "12",
    };
    return months[monthName.toUpperCase()] || "00";
};

const detailData = ref([]);
const selectedCategory = ref(null);

const fetchDetailData = async (category, month, year, site) => {
    // Array nama bulan
    const monthNames = [
        "JANUARI",
        "FEBRUARI",
        "MARET",
        "APRIL",
        "MEI",
        "JUNI",
        "JULI",
        "AGUSTUS",
        "SEPTEMBER",
        "OKTOBER",
        "NOVEMBER",
        "DESEMBER",
    ];

    // Convert "01" → 0, "02" → 1, dst.
    const monthIndex = parseInt(month, 10) - 1;
    const monthName = monthNames[monthIndex] || month;

    selectedCategory.value = `${category} ${monthName} ${year}`;
    try {
        const site =
            props.site.charAt(0).toUpperCase() +
            props.site.slice(1).toLowerCase();

        const routeName = `kpi.jobAnalysis${site}Detail`;
        const response = await axios.post(route(routeName), {
            category,
            month,
            year,
            site,
        });
        detailData.value = response.data.complaints.map((item, index) => ({
            ...item,
            no: index + 1,
        })); // sesuaikan dengan response-mu
    } catch (error) {
        console.error("Gagal ambil detail KPI", error);
    }
};

const headers = [
    { text: "No", value: "no" },
    { text: "Kode Tiket", value: "complaint_code" },
    { text: "Issues", value: "complaint_note" },
    { text: "Tgl Permohonan", value: "date_of_complaint" },
    { text: "Start Response", value: "start_response" },
    { text: "Response Time", value: "response_time", sortable: true },
    { text: "Tgl Selesai", value: "end_progress" },
    { text: "Nama Pelapor", value: "complaint_name" },
    { text: "Lokasi", value: "location" },
    { text: "Jenis Permohonan", value: "category_name" },
    { text: "Crew", value: "crew" },
    { text: "Action Problem", value: "action_repair" },
];

// watch(
//     () => page.props.chartData,
//     (chartData) => {
//         if (chartInspeksi.value && chartData?.labels?.length) {
//             Highcharts.chart(chartInspeksi.value, {
//                 chart: {
//                     type: "column",
//                 },
//                 title: {
//                     text: "KPI ADUAN ANALYSIS",
//                 },
//                 xAxis: {
//                     categories: chartData.labels,
//                 },
//                 yAxis: {
//                     min: 0,
//                     title: {
//                         text: "Jumlah",
//                     },
//                 },
//                 series: chartData.series,
//                 plotOptions: {
//                     series: {
//                         cursor: "pointer",
//                         dataLabels: {
//                             enabled: true,
//                             format: "{y}", // atau '{y}%' jika kamu ingin pakai persen
//                             style: {
//                                 fontSize: "12px",
//                                 color: "#000000",
//                             },
//                         },
//                         point: {
//                             events: {
//                                 click: function () {
//                                     const category = this.series.name;
//                                     const monthTeks = this.category;
//                                     const month = getMonthNumber(monthTeks);
//                                     const yearKlik = year.value;
//                                     const site = props.site;

//                                     fetchDetailData(
//                                         category,
//                                         month,
//                                         yearKlik,
//                                         site
//                                     );
//                                 },
//                             },
//                         },
//                     },
//                 },
//                 credits: {
//                     enabled: true,
//                     text: "ICT PPA-AMM Development System",
//                     href: null,
//                     position: {
//                         align: "right",
//                         x: -10,
//                         verticalAlign: "bottom",
//                         y: -5,
//                     },
//                     style: {
//                         fontSize: "10px",
//                         color: "#666666",
//                     },
//                 },
//             });
//         }
//     }
// );

// watch(
//     () => page.props.chartData,
//     (chartData) => {
//         if (chartInspeksi.value && chartData?.labels?.length) {
//             const currentMonth = new Date().getMonth() + 1; // bulan berjalan (1-12)

//             // Buat salinan series dengan 0 diubah menjadi null, kecuali untuk bulan berjalan
//             const filteredSeries = chartData.series.map((serie) => {
//                 const filteredData = serie.data.map((value, idx) => {
//                     const monthLabel = chartData.labels[idx];
//                     const monthNumber = getMonthNumber(monthLabel);
//                     if (monthNumber === currentMonth) {
//                         return value;
//                     } else {
//                         return value === 0 ? null : value;
//                     }
//                 });
//                 return { ...serie, data: filteredData };
//             });

//             Highcharts.chart(chartInspeksi.value, {
//                 chart: {
//                     type: "column",
//                 },
//                 title: {
//                     text: "KPI ADUAN ANALYSIS",
//                 },
//                 xAxis: {
//                     categories: chartData.labels,
//                     maxPadding: 0.2, // tambahkan padding kanan agar tidak terpotong
//                 },
//                 yAxis: {
//                     min: 0,
//                     title: {
//                         text: "Jumlah",
//                     },
//                 },
//                 series: filteredSeries,
//                 plotOptions: {
//                     series: {
//                         cursor: "pointer",
//                         dataLabels: {
//                             enabled: true,
//                             format: "{y}",
//                             style: {
//                                 fontSize: "12px",
//                                 color: "#000000",
//                             },
//                         },
//                         point: {
//                             events: {
//                                 click: function () {
//                                     const category = this.series.name;
//                                     const monthTeks = this.category;
//                                     const month = getMonthNumber(monthTeks);
//                                     const yearKlik = year.value;
//                                     const site = props.site;

//                                     fetchDetailData(
//                                         category,
//                                         month,
//                                         yearKlik,
//                                         site
//                                     );
//                                 },
//                             },
//                         },
//                     },
//                 },
//                 credits: {
//                     enabled: true,
//                     text: "ICT PPA-AMM Development System",
//                     href: null,
//                     position: {
//                         align: "right",
//                         x: -10,
//                         verticalAlign: "bottom",
//                         y: -5,
//                     },
//                     style: {
//                         fontSize: "10px",
//                         color: "#666666",
//                     },
//                 },
//             });
//         }
//     }
// );

watch(
    () => page.props.chartData,
    (chartData) => {
        if (chartInspeksi.value && chartData?.labels?.length) {
            const currentMonth = new Date().getMonth() + 1;

            // 1. Buat salinan series dan filter data 0 => null (kecuali bulan sekarang)
            const processedSeries = chartData.series.map((serie) => {
                const newData = serie.data.map((value, idx) => {
                    const monthLabel = chartData.labels[idx];
                    const monthNumber = getMonthNumber(monthLabel);
                    if (monthNumber === currentMonth) return value;
                    return value === 0 ? null : value;
                });
                return { ...serie, data: newData };
            });

            // 2. Hapus series yang seluruh datanya null
            const filteredSeries = processedSeries.filter((serie) => {
                return serie.data.some((val) => val !== null && val !== undefined);
            });

            // 3. Hitung ulang max category berdasarkan labels yang masih memiliki data
            const validIndexes = chartData.labels.map((_, idx) =>
                filteredSeries.some((serie) => serie.data[idx] !== null && serie.data[idx] !== undefined)
            );

            // 4. Filter ulang labels agar sinkron
            const filteredLabels = chartData.labels.filter((_, idx) => validIndexes[idx]);

            // 5. Sinkronisasi data series berdasarkan validIndexes
            const finalSeries = filteredSeries.map((serie) => {
                const newData = serie.data.filter((_, idx) => validIndexes[idx]);
                return { ...serie, data: newData };
            });

            // 6. Render chart
            Highcharts.chart(chartInspeksi.value, {
                chart: {
                    type: "column",
                },
                title: {
                    text: "KPI ADUAN ANALYSIS",
                },
                xAxis: {
                    categories: filteredLabels,
                    maxPadding: 0.2,
                },
                yAxis: {
                    min: 0,
                    title: {
                        text: "Jumlah",
                    },
                },
                series: finalSeries,
                plotOptions: {
                    series: {
                        cursor: "pointer",
                        dataLabels: {
                            enabled: true,
                            format: "{y}",
                            style: {
                                fontSize: "12px",
                                color: "#000000",
                            },
                        },
                        point: {
                            events: {
                                click: function () {
                                    const category = this.series.name;
                                    const monthTeks = this.category;
                                    const month = getMonthNumber(monthTeks);
                                    const yearKlik = year.value;
                                    const site = props.site;

                                    fetchDetailData(category, month, yearKlik, site);
                                },
                            },
                        },
                    },
                },
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


const isChartEmpty = computed(() => {
    const chartData = page.props.chartData;
    if (!chartData || !chartData.series) return true;

    return chartData.series.every((series) => {
        return (
            !series.data ||
            series.data.every((value) => value === 0 || value === null)
        );
    });
});

watch(isChartEmpty, (newVal) => {
    if (newVal) {
        detailData.value = [];
    }
});

onMounted(() => {
    const currentYear = new Date().getFullYear();
    year.value = currentYear.toString(); // set default tahun sekarang
    searchData(); // langsung panggil data saat halaman dibuka
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
                                                KPI Aduan Analysis PPA Site
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

                                    <div
                                        v-if="detailData.length > 0"
                                        class="p-4 pb-0 mb-0 rounded-t-4 mt-3"
                                    >
                                        <h6
                                            class="mb-2 dark:text-white text-center font-semibold text-lg"
                                        >
                                            DATA CATEGORY {{ selectedCategory }}
                                        </h6>
                                    </div>

                                    <div
                                        v-if="detailData.length > 0"
                                        class="overflow-x-auto p-3"
                                    >
                                        <EasyDataTable
                                            table-class-name="customize-table"
                                            :headers="headers"
                                            :items="detailData"
                                            :rows-per-page="10"
                                            :search-field="true"
                                        />
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
