<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import { ref, onMounted, watchEffect, nextTick } from "vue";
import Highcharts from "highcharts";
import brandDark from "highcharts/themes/brand-dark";
import brandLight from "highcharts/themes/brand-light";
import { useDark } from "@vueuse/core";

const pages = ref("Pages");
const subMenu = ref("Dashboard Pages");
const mainMenu = ref("Dashboard");

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
    breakdown_array: {
        type: Array,
    },
    scrap_array: {
        type: Array,
    },
    readyStandby_array: {
        type: Array,
    },
    sudahInspeksi: {
        type: Array,
    },
    belumInspeksi: {
        type: Array,
    },
    readyUsed_array: {
        type: Array,
    },
    loginSession: {
        type: String,
    },
    totalAduan: {
        type: Number,
    },
    telkomsel: {
        type: Number,
    },
    server: {
        type: Number,
    },
    ss6: {
        type: Number,
    },
    website: {
        type: Number,
    },
    network: {
        type: Number,
    },
    radio: {
        type: Number,
    },
    sap: {
        type: Number,
    },
    pcNb: {
        type: Number,
    },
    printer: {
        type: Number,
    },
    other: {
        type: Number,
    },
    aduanTelkomsel: {
        type: Number,
    },
    aduanRadio: {
        type: Number,
    },
    aduanServer: {
        type: Number,
    },
    aduanSs6: {
        type: Number,
    },
    aduanWebsite: {
        type: Number,
    },
    aduanNetwork: {
        type: Number,
    },
    aduanSap: {
        type: Number,
    },
    aduanPcNb: {
        type: Number,
    },
    aduanPrinter: {
        type: Number,
    },
    aduanOther: {
        type: Number,
    },
});

const chartContainer = ref(null);
const chartAchievement = ref(null);
const chartAduanAnalys = ref(null);

const theme = ref(null);

const isDark = useDark();

watchEffect(() => {
    if (isDark.value === true) {
        // dark
        theme.value = "dark";

        nextTick(() => {
            initChartDark();
            initChartAchievementDark();
            initChartAduanAnalysDark();
        });
    } else {
        // light
        theme.value = "light";
        nextTick(() => {
            initChartLight();
            initChartAchievementLight();
            initChartAduanAnalysLight();
        });
    }
});

const initChartDark = () => {
    Highcharts.chart(chartContainer.value, {
        chart: {
            type: "bar",
            backgroundColor: "#334155",
        },
        title: {
            text: "",
            align: "left",
        },
        xAxis: {
            categories: [
                "Access Point",
                "Switch",
                "Wirelless",
                "Printer",
                "CCTV",
                "Komputer",
                "Laptop",
            ],
            labels: {
                style: {
                    color: "#fff",
                },
            },
        },
        yAxis: {
            min: 0,
            title: {
                text: "",
            },
            labels: {
                style: {
                    color: "#fff",
                },
            },
        },
        legend: {
            reversed: true,
            itemStyle: {
                color: "#fff",
                hover: "#fff",
                cursor: "pointer",
            },
        },
        plotOptions: {
            series: {
                stacking: "normal",
                dataLabels: {
                    enabled: true,
                },
            },
        },
        series: [
            {
                name: "Breakdown",
                color: "#F64865",
                data: props.breakdown_array,
            },
            {
                name: "Scrap",
                color: "#FA7B3A",
                data: props.scrap_array,
            },
            {
                name: "Ready Stanby",
                color: "#FBCB33",
                data: props.readyStandby_array,
            },
            {
                name: "Ready Used",
                color: "#2FCFA4",
                data: props.readyUsed_array,
            },
        ],
        credits: {
            enabled: false,
        },
    });
};
const initChartLight = () => {
    Highcharts.chart(chartContainer.value, {
        chart: {
            type: "bar",
        },
        title: {
            text: "",
            align: "left",
        },
        xAxis: {
            categories: [
                "Access Point",
                "Switch",
                "Wirelless",
                "Printer",
                "CCTV",
                "Komputer",
                "Laptop",
            ],
            labels: {
                style: {
                    // color: "#fff",
                },
            },
        },
        yAxis: {
            min: 0,
            title: {
                text: "",
            },
        },
        legend: {
            reversed: true,
        },
        plotOptions: {
            series: {
                stacking: "normal",
                dataLabels: {
                    enabled: true,
                },
            },
        },
        series: [
            {
                name: "Breakdown",
                color: "#F64865",
                data: props.breakdown_array,
            },
            {
                name: "Scrap",
                color: "#FA7B3A",
                data: props.scrap_array,
            },
            {
                name: "Ready Stanby",
                color: "#FBCB33",
                data: props.readyStandby_array,
            },
            {
                name: "Ready Used",
                color: "#2FCFA4",
                data: props.readyUsed_array,
            },
        ],
        credits: {
            enabled: false,
        },
    });
};

const initChartAchievementLight = () => {
    Highcharts.chart(chartAchievement.value, {
        chart: {
            type: "bar",
            events: {
                load: function () {
                    const chart = this;
                    drawCustomLegend(chart);
                },
                render: function () {
                    const chart = this;
                    if (chart.customLegendTexts) {
                        chart.customLegendTexts.forEach((el) => el.destroy());
                    }
                    drawCustomLegend(chart);
                },
            },
        },
        title: {
            text: "",
            align: "left",
        },
        xAxis: {
            categories: ["Laptop", "Komputer"],
            labels: {
                style: {
                    // color: "#fff",
                },
            },
        },
        yAxis: {
            min: 0,
            max: 100,
            title: {
                text: "Inspection (%)",
            },
            labels: {
                format: "{value}%",
            },
        },
        legend: {
            reversed: true,
        },
        tooltip: {
            pointFormat: "{series.name}: <b>{point.percentage:.1f}%</b>",
        },
        plotOptions: {
            series: {
                stacking: "percent",
                dataLabels: {
                    enabled: true, // Menampilkan angka di dalam batang
                    format: "{point.percentage:.1f}%", // Menampilkan nilai dalam persen
                    style: {
                        fontWeight: "bold",
                        color: "black", // Warna teks untuk kontras dengan batang
                        textOutline: "3px contrast",
                    },
                },
            },
        },

        series: [
            {
                name: "Belum Inspeksi",
                color: "#F64865",
                data: props.belumInspeksi,
            },
            {
                name: "Sudah Inspeksi",
                color: "#2FCFA4",
                data: props.sudahInspeksi,
            },
        ],
        credits: {
            enabled: false,
        },
    });
};
const initChartAchievementDark = () => {
    Highcharts.chart(chartAchievement.value, {
        chart: {
            type: "bar",
            backgroundColor: "#334155",
            events: {
                load: function () {
                    const chart = this;
                    drawCustomLegend(chart);
                },
                render: function () {
                    const chart = this;
                    if (chart.customLegendTexts) {
                        chart.customLegendTexts.forEach((el) => el.destroy());
                    }
                    drawCustomLegend(chart);
                },
            },
        },
        title: {
            text: "",
            align: "left",
        },
        xAxis: {
            categories: ["Laptop", "Komputer"],
            labels: {
                style: {
                    color: "#fff",
                },
            },
        },
        yAxis: {
            min: 0,
            max: 100,
            title: {
                text: "Inspection (%)",
                style: {
                    color: "#fff", // Mengubah warna title menjadi putih
                    fontWeight: "bold",
                },
            },
            labels: {
                format: "{value}%",
                style: {
                    color: "#fff", // Mengubah warna title menjadi putih
                    fontWeight: "bold",
                },
            },
        },
        legend: {
            reversed: true,
            itemStyle: {
                color: "#fff",
                hover: "#fff",
                cursor: "pointer",
            },
        },
        tooltip: {
            pointFormat: "{series.name}: <b>{point.percentage:.1f}%</b>",
        },
        plotOptions: {
            series: {
                stacking: "percent",
                dataLabels: {
                    enabled: true, // Menampilkan angka di dalam batang
                    format: "{point.percentage:.1f}%", // Menampilkan nilai dalam persen
                    style: {
                        fontWeight: "bold",
                        color: "black", // Warna teks untuk kontras dengan batang
                        textOutline: "3px contrast",
                    },
                },
            },
        },

        series: [
            {
                name: "Belum Inspeksi",
                color: "#F64865",
                data: props.belumInspeksi,
            },
            {
                name: "Sudah Inspeksi",
                color: "#2FCFA4",
                data: props.sudahInspeksi,
            },
        ],
        credits: {
            enabled: false,
        },
    });
};

function drawCustomLegend(chart) {
    const leftX = 10;
    const bottomY = chart.chartHeight; // titik bawah chart

    const text1 = chart.renderer
        .text("🟩 Data Inspeksi Laptop Per-Year", leftX, bottomY - 40)
        .css({ color: "#fff", fontSize: "12px" })
        .add();

    const text2 = chart.renderer
        .text("🟥 Data Inspeksi Computer Per-Quarter", leftX, bottomY - 25)
        .css({ color: "#fff", fontSize: "12px" })
        .add();

    chart.customLegendTexts = [text1, text2];
}

const initChartAduanAnalysLight = () => {
    Highcharts.chart(chartAduanAnalys.value, {
        chart: {
            type: "pie",
            custom: {},
            events: {
                render() {
                    const chart = this,
                        series = chart.series[0];
                    let customLabel = chart.options.chart.custom.label;

                    if (!customLabel) {
                        customLabel = chart.options.chart.custom.label =
                            chart.renderer
                                .label(
                                    `Total Aduan<br/><strong>${props.totalAduan}</strong>`
                                )
                                .css({
                                    color: "#000",
                                    textAnchor: "middle",
                                })
                                .add();
                    }

                    const x = series.center[0] + chart.plotLeft,
                        y =
                            series.center[1] +
                            chart.plotTop -
                            customLabel.attr("height") / 2;

                    customLabel.attr({
                        x,
                        y,
                    });
                    // Set font size based on chart diameter
                    customLabel.css({
                        fontSize: `${series.center[2] / 12}px`,
                    });
                },
            },
        },
        accessibility: {
            point: {
                valueSuffix: "%",
            },
        },
        title: {
            text: "Site MLP",
        },
        subtitle: {
            // text: 'Source: <a href="https://www.ssb.no/transport-og-reiseliv/faktaside/bil-og-transport">SSB</a>',
        },
        tooltip: {
            pointFormat: "{series.name}: <b>{point.y:.1f}%</b>",
        },
        legend: {
            layout: "vertical",
            align: "right",
            verticalAlign: "middle",
            labelFormatter: function () {
                // Contoh: Ambil nilai dari props
                let customValues = {
                    TELKOMSEL: props.aduanTelkomsel,
                    RADIO: props.aduanRadio,
                    SERVER: props.aduanServer,
                    SS6: props.aduanSs6,
                    WEBSITE: props.aduanWebsite,
                    NETWORK: props.aduanNetwork,
                    SAP: props.aduanSap,
                    "PC/NB": props.aduanPcNb,
                    PRINTER: props.aduanPrinter,
                    OTHER: props.aduanOther,
                };

                // Gabungkan nama kategori + nilai dari props
                return this.name + " (" + (customValues[this.name] || 0) + ")";
            },
        },
        plotOptions: {
            series: {
                allowPointSelect: true,
                cursor: "pointer",
                borderRadius: 8,
                dataLabels: [
                    {
                        enabled: true,
                        distance: 20,
                        format: "{point.name}",
                    },
                    {
                        enabled: true,
                        distance: -15,
                        format: "{point.y:.1f}%",
                        style: {
                            fontSize: "0.9em",
                        },
                    },
                ],
                showInLegend: true,
            },
        },
        series: [
            {
                name: "Complaint",
                colorByPoint: true,
                innerSize: "75%",
                data: [
                    {
                        name: "TELKOMSEL",
                        y: props.telkomsel,
                    },
                    {
                        name: "RADIO",
                        y: props.radio,
                    },
                    {
                        name: "SERVER",
                        y: props.server,
                    },
                    {
                        name: "SS6",
                        y: props.ss6,
                    },
                    {
                        name: "WEBSITE",
                        y: props.website,
                    },
                    {
                        name: "NETWORK",
                        y: props.network,
                    },
                    {
                        name: "SAP",
                        y: props.sap,
                    },
                    {
                        name: "PC/NB",
                        y: props.pcNb,
                    },
                    {
                        name: "PRINTER",
                        y: props.printer,
                    },
                    {
                        name: "OTHER",
                        y: props.other,
                    },
                ],
            },
        ],
        credits: {
            enabled: false,
        },
    });
};
const initChartAduanAnalysDark = () => {
    Highcharts.chart(chartAduanAnalys.value, {
        chart: {
            type: "pie",
            backgroundColor: "#334155",
            custom: {},
            events: {
                render() {
                    const chart = this,
                        series = chart.series[0];
                    let customLabel = chart.options.chart.custom.label;

                    if (!customLabel) {
                        customLabel = chart.options.chart.custom.label =
                            chart.renderer
                                .label(
                                    `Total Aduan<br/><strong>${props.totalAduan}</strong>`
                                )
                                .css({
                                    color: "#000",
                                    textAnchor: "middle",
                                })
                                .add();
                    }

                    const x = series.center[0] + chart.plotLeft,
                        y =
                            series.center[1] +
                            chart.plotTop -
                            customLabel.attr("height") / 2;

                    customLabel.attr({
                        x,
                        y,
                    });
                    // Set font size based on chart diameter
                    customLabel.css({
                        fontSize: `${series.center[2] / 12}px`,
                        color: "#fff",
                    });
                },
            },
        },
        accessibility: {
            point: {
                valueSuffix: "%",
            },
        },
        title: {
            text: "Site MLP",
            style: {
                color: "#fff", // Mengubah warna title menjadi putih
                fontWeight: "bold",
            },
        },
        subtitle: {
            // text: 'Source: <a href="https://www.ssb.no/transport-og-reiseliv/faktaside/bil-og-transport">SSB</a>',
        },
        tooltip: {
            pointFormat: "{series.name}: <b>{point.y:.1f}%</b>",
        },
        legend: {
            layout: "vertical",
            align: "right",
            verticalAlign: "middle",
            labelFormatter: function () {
                // Contoh: Ambil nilai dari props
                let customValues = {
                    TELKOMSEL: props.aduanTelkomsel,
                    RADIO: props.aduanRadio,
                    SERVER: props.aduanServer,
                    SS6: props.aduanSs6,
                    WEBSITE: props.aduanWebsite,
                    NETWORK: props.aduanNetwork,
                    SAP: props.aduanSap,
                    "PC/NB": props.aduanPcNb,
                    PRINTER: props.aduanPrinter,
                    OTHER: props.aduanOther,
                };

                // Gabungkan nama kategori + nilai dari props
                return this.name + " (" + (customValues[this.name] || 0) + ")";
            },
            itemStyle: {
                color: "#fff",
                hover: "#fff",
                cursor: "pointer",
            },
        },
        plotOptions: {
            series: {
                allowPointSelect: true,
                cursor: "pointer",
                borderRadius: 8,
                dataLabels: [
                    {
                        enabled: true,
                        distance: 20,
                        format: "{point.name}",
                        style: {
                            color: "#fff", // Warna putih untuk nama
                            fontSize: "0.9em",
                        },
                    },
                    {
                        enabled: true,
                        distance: -15,
                        format: "{point.y:.1f}%",
                        style: {
                            color: "#fff", // Warna putih untuk persentase
                            fontSize: "0.9em",
                        },
                    },
                ],
                showInLegend: true,
            },
        },
        series: [
            {
                name: "Complaint",
                colorByPoint: true,
                innerSize: "75%",
                data: [
                    {
                        name: "TELKOMSEL",
                        y: props.telkomsel,
                    },
                    {
                        name: "RADIO",
                        y: props.radio,
                    },
                    {
                        name: "SERVER",
                        y: props.server,
                    },
                    {
                        name: "SS6",
                        y: props.ss6,
                    },
                    {
                        name: "WEBSITE",
                        y: props.website,
                    },
                    {
                        name: "NETWORK",
                        y: props.network,
                    },
                    {
                        name: "SAP",
                        y: props.sap,
                    },
                    {
                        name: "PC/NB",
                        y: props.pcNb,
                    },
                    {
                        name: "PRINTER",
                        y: props.printer,
                    },
                    {
                        name: "OTHER",
                        y: props.other,
                    },
                ],
            },
        ],
        credits: {
            enabled: false,
        },
    });
};
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout
        v-model:pages="pages"
        v-model:subMenu="subMenu"
        v-model:mainMenu="mainMenu"
    >
        <!-- cards -->
        <!-- row 1 -->
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
                            <div class="flex-none w-2/3 max-w-full px-3">
                                <div>
                                    <p
                                        class="mb-0 font-sans text-sm font-semibold leading-normal uppercase dark:text-white dark:opacity-60"
                                    >
                                        ALL ADUAN OPEN
                                    </p>
                                    <h5 class="mb-2 font-bold dark:text-white">
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
                            <div class="flex-none w-2/3 max-w-full px-3">
                                <div>
                                    <p
                                        class="mb-0 font-sans text-sm font-semibold leading-normal uppercase dark:text-white dark:opacity-60"
                                    >
                                        ALL ADUAN PROGRESS
                                    </p>
                                    <h5 class="mb-2 font-bold dark:text-white">
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
                            <div class="flex-none w-2/3 max-w-full px-3">
                                <div>
                                    <p
                                        class="mb-0 font-sans text-sm font-semibold leading-normal uppercase dark:text-white dark:opacity-60"
                                    >
                                        ALL ADUAN CLOSED
                                    </p>
                                    <h5 class="mb-2 font-bold dark:text-white">
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
            <div class="w-full max-w-full px-3 sm:w-1/2 sm:flex-none xl:w-1/4">
                <div
                    class="relative flex flex-col min-w-0 break-words bg-white shadow-xl dark:bg-slate-850 dark:shadow-dark-xl rounded-2xl bg-clip-border"
                >
                    <div class="flex-auto p-4">
                        <div class="flex flex-row -mx-3">
                            <div class="flex-none w-2/3 max-w-full px-3">
                                <div>
                                    <p
                                        class="mb-0 font-sans text-sm font-semibold leading-normal uppercase dark:text-white dark:opacity-60"
                                    >
                                        ALL ADUAN CANCEL
                                    </p>
                                    <h5 class="mb-2 font-bold dark:text-white">
                                        {{ props.cancel }}
                                    </h5>
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

        <!-- cards row 3 -->

        <div class="flex flex-wrap mt-6 -mx-3">
            <div
                class="w-full max-w-full px-3 mt-0 mb-6 lg:mb-0 lg:w-12/12 lg:flex-none"
            >
                <!-- Table Monitoring Device PPA Site MLP (Full Width) -->
                <div
                    class="relative flex flex-col min-w-0 break-words bg-white border-0 border-solid shadow-xl dark:bg-slate-850 dark:shadow-dark-xl dark:bg-gray-950 border-black-125 rounded-2xl bg-clip-border"
                >
                    <div class="p-4 pb-0 mb-0 rounded-t-4">
                        <div class="flex justify-between">
                            <h6 class="mb-2 dark:text-white">
                                Table Monitoring Device PPA Site MLP
                            </h6>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <div
                            ref="chartContainer"
                            style="width: 100%; height: 400px"
                        ></div>
                    </div>
                </div>

                <!-- Row Baru untuk 2 Chart di Satu Baris -->
                <div class="flex mt-10 gap-4">
                    <!-- Table Monitoring Achievement Inspeksi (50%) -->
                    <div
                        class="w-1/2 relative flex flex-col min-w-0 break-words bg-white border-0 border-solid shadow-xl dark:bg-slate-850 dark:shadow-dark-xl dark:bg-gray-950 border-black-125 rounded-2xl bg-clip-border"
                    >
                        <div class="p-4 pb-0 mb-0 rounded-t-4">
                            <div class="flex justify-between">
                                <h6 class="mb-2 dark:text-white">
                                    Table Monitoring Achievement Inspeksi/Periode
                                </h6>
                            </div>
                        </div>
                        <div class="overflow-x-auto">
                            <div
                                ref="chartAchievement"
                                style="width: 100%; height: 400px"
                            ></div>
                        </div>
                    </div>

                    <!-- Chart Tambahan (50%) -->
                    <div
                        class="w-1/2 relative flex flex-col min-w-0 break-words bg-white border-0 border-solid shadow-xl dark:bg-slate-850 dark:shadow-dark-xl dark:bg-gray-950 border-black-125 rounded-2xl bg-clip-border"
                    >
                        <div class="p-4 pb-0 mb-0 rounded-t-4">
                            <div class="flex justify-between">
                                <h6 class="mb-2 dark:text-white">
                                    Table Analysis Aduan/Month
                                </h6>
                            </div>
                        </div>
                        <div class="overflow-x-auto">
                            <div
                                ref="chartAduanAnalys"
                                style="width: 100%; height: 400px"
                            ></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
