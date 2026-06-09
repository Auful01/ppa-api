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
import { ref, onMounted, computed, watch, nextTick } from "vue";
import { Inertia } from "@inertiajs/inertia";
import axios from "axios";

const selectedOptionCategory = ref({ name: "PC/NB" });

const pages = ref("Pages");
const subMenu = ref("KPI Perangkat Breakdown");
const mainMenu = ref("Chart KPI Perangkat Breakdown");
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

const kategoriList = ref([]);
const kpiData = ref({});
const dataBreakdown = ref([]);

const searchData = async () => {
    const params = {
        device_category: selectedOptionCategory.value.name,
        start_date: customFormat(startDate.value),
        end_date: customFormat(endDate.value),
    };

    try {
        const response = await axios.get(route("kpi.perangkatBibData"), {
            params,
        });

        kpiData.value = response.data;
        console.log(response.data.breakdown_details)
        dataBreakdown.value = response.data.breakdown_details.map(
            (item, index) => ({
                ...item,
                no: index + 1,
            })
        );

        // === Format data highchart dengan warna khusus ===
        const formattedChartData = response.data.highchart.map((item) => {
            if (item.name.toLowerCase().includes("breakdown")) {
                item.color = "#EF4444"; // merah Tailwind red-500
            } else if (item.name.toLowerCase().includes("ready")) {
                item.color = "#22C55E"; // hijau Tailwind green-500
            }
            return item;
        });

        kategoriList.value = Object.entries(response.data.root_cause_count).map(
            ([kategori, total]) => ({
                kategori,
                time: total.toString().padStart(2),
            })
        );

        await nextTick();

        if (chartInstance.value) {
            chartInstance.value.series[0].setData(formattedChartData);

            const totalReady = formattedChartData
                .filter((p) => p.name.toLowerCase().includes("ready"))
                .reduce((sum, p) => sum + p.y, 0);

            if (chartInstance.value.customText) {
                chartInstance.value.customText.destroy();
            }
            chartInstance.value.customText = chartInstance.value.renderer
                .text(
                    `Ready<br><b>${totalReady}</b>`,
                    chartInstance.value.plotWidth / 2 +
                        chartInstance.value.plotLeft,
                    chartInstance.value.plotHeight / 2 +
                        chartInstance.value.plotTop
                )
                .css({
                    textAlign: "center",
                    fontSize: "14px",
                })
                .attr({ align: "center" })
                .add();
        } else {
            chartInstance.value = Highcharts.chart("kpi-pie-chart", {
                chart: {
                    type: "pie",
                    events: {
                        load: function () {
                            const chart = this;
                            const totalReady = formattedChartData
                                .filter((p) =>
                                    p.name.toLowerCase().includes("ready")
                                )
                                .reduce((sum, p) => sum + p.y, 0);
                            chart.customText = chart.renderer
                                .text(
                                    `Ready<br><b>${totalReady}</b>`,
                                    chart.plotWidth / 2 + chart.plotLeft,
                                    chart.plotHeight / 2 + chart.plotTop
                                )
                                .css({
                                    textAlign: "center",
                                    fontSize: "14px",
                                })
                                .attr({ align: "center" })
                                .add();
                        },
                    },
                },
                title: { text: "Chart Persentase Perangkat Breakdown" },
                tooltip: {
                    pointFormat:
                        "{series.name}: <b>{point.percentage:.1f}%</b>",
                },
                plotOptions: {
                    pie: {
                        allowPointSelect: true,
                        cursor: "pointer",
                        innerSize: "60%",
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
                },
                series: [
                    {
                        name: "Status",
                        colorByPoint: true,
                        data: formattedChartData,
                    },
                ],
            });
        }
    } catch (error) {
        console.error(error);
        Swal.fire("Gagal", "Tidak dapat mengambil data KPI", "error");
    }
};

const headers = [
    { text: "No", value: "no" },
    { text: "Device Category", value: "device_category" },
    { text: "Inventory Number", value: "laptop_code" },
    { text: "Username", value: "user_name" },
    { text: "Department", value: "user_department" },
    { text: "B/D Time", value: "bd_time_unit_total_hours_fmt", sortable: true },
    { text: "Target Time", value: "target_time_unit_hours_fmt" },
    { text: "Running Time", value: "running_time_unit_hours_fmt" },
    { text: "Lokasi", value: "location" },
    { text: "Percentage", value: "percentage_unit_running_fmt" },
];

const summaryItems = computed(() => [
    {
        label: "PERIODE",
        value: `${kpiData.value.periode?.start || "-"} sd ${
            kpiData.value.periode?.end || "-"
        }`,
    },
    { label: "JUMLAH PERANGKAT", value: kpiData.value.jumlah_perangkat || 0 },
    {
        label: "TOTAL TARGET TIME (JAM)",
        value: kpiData.value.total_target_time_jam || "0",
    },
    {
        label: "TOTAL RUNNING TIME (JAM)",
        value: kpiData.value.total_running_time_jam || "0",
    },
    {
        label: "TOTAL BREAKDOWN TIME (JAM)",
        value: kpiData.value.total_breakdown_time_jam || "0",
    },
    {
        label: "TOTAL BREAKDOWN PERANGKAT",
        value: kpiData.value.total_breakdown_perangkat || 0,
    },
    { label: "ACHIEVEMENT", value: `${kpiData.value.persentase?.ready ?? 0}%` },
]);

const chartInstance = ref(null);

onMounted(() => {
    const now = new Date();
    const firstDay = new Date(now.getFullYear(), now.getMonth(), 1);
    const lastDay = new Date(now.getFullYear(), now.getMonth() + 1, 0);
    startDate.value = firstDay;
    endDate.value = lastDay;

    chartInstance.value = Highcharts.chart("kpi-pie-chart", {
        chart: {
            type: "pie",
            events: {
                load: function () {
                    const chart = this;
                    const total = chart.series[0].data.reduce(
                        (sum, p) => sum + p.y,
                        0
                    );
                    chart.customText = chart.renderer
                        .text(
                            `Total<br><b>${total}</b>`,
                            chart.plotWidth / 2 + chart.plotLeft,
                            chart.plotHeight / 2 + chart.plotTop
                        )
                        .css({
                            textAlign: "center",
                            fontSize: "14px",
                            color: "#333",
                        })
                        .attr({
                            align: "center",
                        })
                        .add();
                },
            },
        },
        title: { text: "Chart Persentase Perangkat Breakdown" },
        tooltip: {
            pointFormat: "{series.name}: <b>{point.percentage:.1f}%</b>",
        },
        plotOptions: {
            pie: {
                allowPointSelect: true,
                cursor: "pointer",
                innerSize: "60%", // ✅ ubah jadi DONUT
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
        },
        series: [
            {
                name: "Status",
                colorByPoint: true,
                data: [],
            },
        ],
    });

    // ambil data pertama kali
    searchData();
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
                                        v-model="selectedOptionCategory"
                                        :options="[
                                            { name: 'PC/NB' },
                                            { name: 'PRINTER' },
                                            { name: 'SCANNER' },
                                        ]"
                                        :multiple="false"
                                        :close-on-select="true"
                                        placeholder="Select Category"
                                        track-by="name"
                                        label="name"
                                    />
                                </div>
                                <div
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
                                            KPI PERANGKAT BREAKDOWN PPA SITE
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
                                                KPI PERANGKAT BREAKDOWN ICT
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
                                                PERANGKAT BREAKDOWN PER KATEGORI
                                                ICT
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
                                            PERANGKAT BREAKDOWN DATA
                                        </h6>
                                    </div>

                                    <div class="overflow-x-auto p-3">
                                        <EasyDataTable
                                            table-class-name="customize-table"
                                            :headers="headers"
                                            :items="dataBreakdown"
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
