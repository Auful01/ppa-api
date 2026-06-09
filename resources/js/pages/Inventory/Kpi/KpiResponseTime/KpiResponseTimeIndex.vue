<style>
/* tanpa scoped */
.customize-table thead tr {
  @apply bg-red-500 text-white;
}
</style>

<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Swal from "sweetalert2";
import VueMultiselect from "vue-multiselect";
import Highcharts from "highcharts";
import VueDatePicker from "@vuepic/vue-datepicker";
import "@vuepic/vue-datepicker/dist/main.css";
import EasyDataTable from "vue3-easy-data-table";
import "vue3-easy-data-table/dist/style.css";
import brandDark from "highcharts/themes/brand-dark";
import brandLight from "highcharts/themes/brand-light";
import { ref, onMounted, computed, watch } from "vue";
import { Inertia } from "@inertiajs/inertia";
import axios from "axios";

const selectedOption = ref({ name: "Date" });
const isYear = computed(() => {
    return selectedOption.value?.name === "Year";
});

const pages = ref("Pages");
const subMenu = ref("KPI Response Time");
const mainMenu = ref("Chart KPI Response Time");
const props = defineProps({
    site: String,
});

const startDate = ref(null);
const endDate = ref(null);
const year = ref(null);

const formatToIndonesianDate = (date) => {
    const d = new Date(date);
    const options = { day: "2-digit", month: "long", year: "numeric" };
    return d.toLocaleDateString("id-ID", options);
};

const periodLabel = computed(() => {
    if (isYear.value && year.value) {
        return `Tahun ${year.value}`;
    }

    if (!startDate.value || !endDate.value) return "";

    const start = formatToIndonesianDate(startDate.value);
    const end = formatToIndonesianDate(endDate.value);
    return `${start} sd ${end}`;
});

const customFormat = (date) => {
    const d = new Date(date);
    const year = d.getFullYear();
    const month = String(d.getMonth() + 1).padStart(2, "0");
    const day = String(d.getDate()).padStart(2, "0");

    return `${year}-${month}-${day}`;
};

const validateYear = (event) => {
    const value = event.target.value;
    if (!/^\d*$/.test(value)) {
        year.value = value.replace(/\D/g, ""); // Hapus karakter selain angka
    }
};

const kpiData = ref({
    countAduan: 0,
    countAduanClosed: 0,
    countAduanOpen: 0,
    countAduanProcess: 0,
    countAduanOutstanding: 0,
    avgResponseTime: 0,
    achievement30minPercent: 0,
});

const kategoriList = ref([]);
const aduanDiAtas30Menit = ref([]);

const searchData = async () => {
    const params = {};

    if (isYear.value) {
        params.year = year.value;
        startDate.value = "";
        endDate.value = "";
    } else {
        params.startDate = customFormat(startDate.value);
        params.endDate = customFormat(endDate.value);
    }
    console.log(params);
    try {
        const site =
            props.site.charAt(0).toUpperCase() +
            props.site.slice(1).toLowerCase();

        const routeName = `kpi.responseTimeShow${site}`;

        const response = await axios.post(route(routeName), params);
        kpiData.value = response.data;
        kategoriList.value = response.data.kategoriList;
        aduanDiAtas30Menit.value = response.data.aduanDiAtas30Menit.map(
            (item, index) => ({
                ...item,
                no: index + 1,
            })
        );
        console.log(response.data);
    } catch (error) {
        console.log(error);
        Swal.fire("Gagal", "Tidak dapat mengambil data KPI", "error");
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

const summaryItems = computed(() => [
    { label: "PERIODE", value: periodLabel.value },
    { label: "JUMLAH ADUAN", value: kpiData.value.countAduan },
    { label: "CLOSED", value: kpiData.value.countAduanClosed },
    { label: "OPEN", value: kpiData.value.countAduanOpen },
    { label: "PROCESS", value: kpiData.value.countAduanProcess },
    { label: "OUTSTANDING", value: kpiData.value.countAduanOutstanding },
    {
        label: "ACHIEVEMENT",
        value: kpiData.value.achievement30minPercent + "%",
    },
    { label: "RESPONSE TIME ADUAN ICT", value: kpiData.value.avgResponseTime },
    { label: "TRESHOLD", value: "00:30:00" },
]);

const chartInstance = ref(null);

const chartData = computed(() => [
    { name: "CLOSED", y: kpiData.value.countAduanClosed },
    { name: "OPEN", y: kpiData.value.countAduanOpen },
    { name: "PROCESS", y: kpiData.value.countAduanProcess },
    { name: "OUTSTANDING", y: kpiData.value.countAduanOutstanding },
]);

// onMounted(() => {
//     chartInstance.value = Highcharts.chart("kpi-pie-chart", {
//         chart: {
//             type: "pie",
//         },
//         title: {
//             text: "Chart Aduan",
//         },
//         tooltip: {
//             pointFormat: "{series.name}: <b>{point.percentage:.1f}%</b>",
//         },
//         accessibility: {
//             point: {
//                 valueSuffix: "%",
//             },
//         },
//         plotOptions: {
//             pie: {
//                 allowPointSelect: true,
//                 cursor: "pointer",
//                 dataLabels: {
//                     enabled: true,
//                     format: "<b>{point.name}</b>: {point.percentage:.1f} %",
//                 },
//             },
//         },
//         credits: {
//             enabled: true,
//             text: "ICT PPA-AMM Development System",
//             href: "https://ict.ppa-ho.net",
//             style: {
//                 color: "#666",
//                 fontSize: "11px",
//             },
//         },
//         series: [
//             {
//                 name: "Status",
//                 colorByPoint: true,
//                 data: chartData.value, // awalnya kosong / nol
//             },
//         ],
//     });
// });

onMounted(() => {
    // Set tanggal default ke awal dan akhir bulan sekarang
    const now = new Date();
    const firstDay = new Date(now.getFullYear(), now.getMonth(), 1);
    const lastDay = new Date(now.getFullYear(), now.getMonth() + 1, 0);
    startDate.value = firstDay;
    endDate.value = lastDay;

    // Panggil data langsung saat halaman dimuat
    searchData();

    // Inisialisasi chart
    chartInstance.value = Highcharts.chart("kpi-pie-chart", {
        chart: {
            type: "pie",
        },
        title: {
            text: "Chart Aduan",
        },
        tooltip: {
            pointFormat: "{series.name}: <b>{point.percentage:.1f}%</b>",
        },
        accessibility: {
            point: {
                valueSuffix: "%",
            },
        },
        plotOptions: {
            pie: {
                allowPointSelect: true,
                cursor: "pointer",
                dataLabels: {
                    enabled: true,
                    format: "<b>{point.name}</b>: {point.percentage:.1f} %",
                },
            },
        },
        credits: {
            enabled: true,
            text: "ICT PPA-AMM Development System",
            href: "https://ict.ppa-ho.net",
            style: {
                color: "#666",
                fontSize: "11px",
            },
        },
        series: [
            {
                name: "Status",
                colorByPoint: true,
                data: chartData.value,
            },
        ],
    });
});

// Watcher untuk update chart setiap kali kpiData berubah
watch(chartData, (newData) => {
    if (chartInstance.value) {
        chartInstance.value.series[0].setData(newData);
    }
});
</script>

<template>
    <Head title="KPI ResponseTime" />

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
                                    class="relative flex flex-wrap items-stretch w-48 transition-all rounded-lg ease mb-4"
                                >
                                    <VueMultiselect
                                        v-model="selectedOption"
                                        :options="[
                                            { name: 'Date' },
                                            { name: 'Year' },
                                        ]"
                                        :multiple="false"
                                        :close-on-select="true"
                                        placeholder="Select Periode"
                                        track-by="name"
                                        label="name"
                                    />
                                </div>
                                <div
                                    v-show="selectedOption.name === 'Date'"
                                    class="relative flex flex-wrap items-stretch w-48 transition-all rounded-lg ease mb-4"
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
                                    v-show="selectedOption.name === 'Date'"
                                    class="relative flex flex-wrap items-stretch w-48 transition-all rounded-lg ease mb-4"
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
                                    v-show="selectedOption.name === 'Year'"
                                    class="relative flex flex-wrap items-stretch w-48 transition-all rounded-lg ease mb-4"
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
                                    <div class="p-4 pb-0 mb-0 rounded-t-4 mt-3">
                                        <h6
                                            class="mb-2 dark:text-white text-center font-semibold text-lg"
                                        >
                                            KPI RESPONSE TIME PPA SITE
                                            {{ props.site }}
                                        </h6>
                                    </div>
                                    <div
                                        class="grid grid-cols-1 xl:grid-cols-3 gap-4 p-3"
                                    >
                                        <!-- Ringkasan KPI -->
                                        <div
                                            class="bg-white rounded-xl shadow p-4"
                                        >
                                            <p
                                                class="bg-slate-700 text-white text-center py-2 font-bold rounded"
                                            >
                                                KPI RESPONSE TIME ICT
                                            </p>
                                            <div class="divide-y">
                                                <div
                                                    v-for="(
                                                        item, i
                                                    ) in summaryItems"
                                                    :key="i"
                                                    class="flex justify-between px-2 py-1"
                                                >
                                                    <span>{{
                                                        item.label
                                                    }}</span>
                                                    <span
                                                        class="font-semibold"
                                                        >{{ item.value }}</span
                                                    >
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Pie Chart Highcharts -->
                                        <div
                                            class="bg-white rounded-xl shadow p-4 flex justify-center items-center"
                                        >
                                            <div
                                                id="kpi-pie-chart"
                                                class="w-full h-80"
                                            ></div>
                                        </div>

                                        <!-- Tabel Response per Kategori -->
                                        <div
                                            class="bg-white rounded-xl shadow p-4"
                                        >
                                            <p
                                                class="bg-slate-700 text-white text-center py-2 font-bold rounded"
                                            >
                                                RESPONSE TIME PER KATEGORI ICT
                                            </p>
                                            <div class="divide-y">
                                                <div
                                                    v-for="(
                                                        item, index
                                                    ) in kategoriList"
                                                    :key="index"
                                                    class="flex justify-between px-2 py-1"
                                                >
                                                    <span>{{
                                                        item.kategori
                                                    }}</span>
                                                    <span>{{ item.time }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="p-4 pb-0 mb-0 rounded-t-4 mt-3">
                                        <h6
                                            class="mb-2 dark:text-white text-center font-semibold text-lg"
                                        >
                                            RESPONSE TIME UP TO 30 MINUTES
                                        </h6>
                                    </div>

                                    <div class="overflow-x-auto p-3">
                                        <EasyDataTable
                                            table-class-name="customize-table"
                                            :headers="headers"
                                            :items="aduanDiAtas30Menit"
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
