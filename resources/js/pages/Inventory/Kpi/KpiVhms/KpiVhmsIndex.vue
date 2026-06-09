<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, usePage } from "@inertiajs/vue3";
import VueMultiselect from "vue-multiselect";
import Highcharts from "highcharts";
import "@vuepic/vue-datepicker/dist/main.css";
import EasyDataTable from "vue3-easy-data-table";
import "vue3-easy-data-table/dist/style.css";
import { ref, onMounted, computed, watch, nextTick } from "vue";
import axios from "axios";

const selectedOption = ref({ name: "Month" });
const selectedOptionUnit = ref({ name: "ALL UNIT" });
const isAllUnit = ref(true);
const showChart = ref(true);
const isLoadingChart = ref(false);
const isLoadingUnit = ref(false);

const pages = ref("Pages");
const subMenu = ref("KPI VHMS");
const mainMenu = ref("Chart KPI VHMS");
const props = defineProps({
    site: String,
});

// Access page data (from controller)
const page = usePage();
const dataVhms = page.props.data || {};

const currentMonth = new Date().getMonth() + 1;
const currentYear = new Date().getFullYear();

const selectedMonth = ref(null);
const year = ref(currentYear);

const validateYear = (event) => {
    const value = event.target.value;
    if (!/^\d*$/.test(value)) {
        year.value = value.replace(/\D/g, "");
    }
};

const vhmsNotDowload = ref([]);

const headers = [
    { text: "SN", value: "sn", sortable: true },
    { text: "CN", value: "cn", sortable: true },
    { text: "Model", value: "model", sortable: true },
    { text: "Status", value: "status", sortable: true },
    { text: "Feedback", value: "feedback", sortable: true },
    { text: "Last Dnld.", value: "last_dnld", sortable: true },
    { text: "Lst. Operation", value: "last_operation", sortable: true },
    { text: "Lst. Payload", value: "last_payload", sortable: true },
    { text: "Lst. Trend", value: "last_trend", sortable: true },
    { text: "Lst. Fault", value: "last_fault", sortable: true },
    { text: "Lst. HIS", value: "last_his", sortable: true },
    { text: "Date", value: "date", sortable: true },
];

const feedbackOptions = [
    "UNIT_BREAKDOWN",
    "UNIT_SERVICE",
    "UNIT_STANDBY",
    "CONTROLLER_PROBLEM",
    "DELETE_FEEDBACK",
];

const globalSearch = ref("");
const selectedDate = ref("");

const filteredItems = computed(() => {
    const text = globalSearch.value.trim().toLowerCase();
    const date = selectedDate.value;

    return vhmsNotDowload.value.filter((item) => {
        // Text match
        const textMatch =
            !text ||
            ["sn", "cn", "model"].some((key) =>
                String(item[key] ?? "")
                    .toLowerCase()
                    .includes(text)
            );

        // Date match (exact date)
        const dateMatch = !date || item.date === date;

        return textMatch && dateMatch;
    });
});

const chartInstance = ref(null);

onMounted(async () => {
    vhmsNotDowload.value = (dataVhms.vhmsNotDownload || []).map((item) => ({
        ...item,
        feedback: item.feedback ?? null,
        status: item.status ?? "",
        id: item.id,
    }));

    await nextTick();

    const categories = dataVhms.categories || [];

    // --- HD ---
    // const kosongHD = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
    // const updateHD = [
    //     90, 83, 65, 100, 120, 95, 110, 87, 92, 105, 99, 130, 115, 123,
    // ];
    // const waitingHD = [68, 79, 72, 88, 97, 84, 90, 75, 82, 93, 88, 102, 95, 99];
    // const notUpdateHD = [
    //     55, 60, 65, 70, 72, 68, 75, 64, 66, 70, 71, 73, 69, 72,
    // ];

    // // --- PC1250 ---
    // const kosongPC1250 = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
    // const updatePC1250 = [
    //     58, 88, 75, 250, 176, 165, 189, 200, 210, 230, 190, 205, 198, 220,
    // ];
    // const waitingPC1250 = [
    //     68, 38, 45, 250, 146, 130, 140, 125, 135, 145, 160, 150, 155, 165,
    // ];
    // const notUpdatePC1250 = [
    //     68, 79, 72, 240, 167, 180, 175, 170, 160, 155, 150, 145, 140, 135,
    // ];

    // // --- PC2000 ---
    // const kosongPC2000 = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
    // const updatePC2000 = [
    //     90, 83, 65, 228, 184, 170, 160, 175, 190, 195, 185, 200, 210, 205,
    // ];
    // const waitingPC2000 = [
    //     68, 79, 72, 240, 167, 180, 190, 200, 210, 220, 230, 225, 215, 205,
    // ];
    // const notUpdatePC2000 = [
    //     68, 79, 72, 240, 167, 160, 165, 170, 175, 180, 185, 190, 195, 200,
    // ];

    // --- HD ---
    const kosongHD = dataVhms.HD?.kosong ?? Array(categories.length).fill(0);
    const updateHD = dataVhms.HD?.update ?? Array(categories.length).fill(0);
    const waitingHD = dataVhms.HD?.waiting ?? Array(categories.length).fill(0);
    const notUpdateHD =
        dataVhms.HD?.not_update ?? Array(categories.length).fill(0);

    // --- PC1250 ---
    const kosongPC1250 =
        dataVhms.PC1250?.kosong ?? Array(categories.length).fill(0);
    const updatePC1250 =
        dataVhms.PC1250?.update ?? Array(categories.length).fill(0);
    const waitingPC1250 =
        dataVhms.PC1250?.waiting ?? Array(categories.length).fill(0);
    const notUpdatePC1250 =
        dataVhms.PC1250?.not_update ?? Array(categories.length).fill(0);

    // --- PC2000 ---
    const kosongPC2000 =
        dataVhms.PC2000?.kosong ?? Array(categories.length).fill(0);
    const updatePC2000 =
        dataVhms.PC2000?.update ?? Array(categories.length).fill(0);
    const waitingPC2000 =
        dataVhms.PC2000?.waiting ?? Array(categories.length).fill(0);
    const notUpdatePC2000 =
        dataVhms.PC2000?.not_update ?? Array(categories.length).fill(0);

    const percentageHD = updateHD.map((val, i) => {
        const total = updateHD[i] + waitingHD[i] + notUpdateHD[i];
        return total > 0 ? (val / total) * 100 : 0; // percentage value
    });

    const percentagePC1250 = updatePC1250.map((val, i) => {
        const total = updatePC1250[i] + waitingPC1250[i] + notUpdatePC1250[i];
        return total > 0 ? (val / total) * 100 : 0; // percentage value
    });

    const percentagePC2000 = updatePC2000.map((val, i) => {
        const total = updatePC2000[i] + waitingPC2000[i] + notUpdatePC2000[i];
        return total > 0 ? (val / total) * 100 : 0; // percentage value
    });

    const allValues = [
        ...updateHD,
        ...waitingHD,
        ...notUpdateHD,
        ...updatePC1250,
        ...waitingPC1250,
        ...notUpdatePC1250,
        ...updatePC2000,
        ...waitingPC2000,
        ...notUpdatePC2000,
    ];

    const maxY = Math.max(...allValues);
    const paddedMax = Math.ceil((maxY * 1.1) / 10) * 9 || 100;

    // Inisialisasi chart
    chartInstance.value = Highcharts.chart("kpi-chart", {
        chart: {
            type: "column",
            zoomType: "x",
            scrollablePlotArea: {
                minWidth: 2000,
            },
            events: {
                render() {
                    const chart = this;

                    if (chart.customLine) chart.customLine.destroy();
                    if (chart.customMarkers)
                        chart.customMarkers.forEach((m) => m.destroy());

                    const updateSeries = chart.series.filter(
                        (s) =>
                            (s.name === "update" || s.name === "updateTOTAL") &&
                            s.visible &&
                            s.type === "column"
                    );

                    const linePoints = [];

                    updateSeries.forEach((series) => {
                        series.points.forEach((p) => {
                            if (p.shapeArgs) {
                                const colCenter =
                                    p.shapeArgs.x + p.shapeArgs.width / 2;
                                const x = chart.plotLeft + colCenter;
                                const y = chart.plotTop + p.plotY;
                                linePoints.push({ x, y });
                            }
                        });
                    });

                    if (!linePoints.length) return;

                    linePoints.sort((a, b) => a.x - b.x);

                    const path = [];
                    const tension = 0.35;

                    for (let i = 0; i < linePoints.length - 1; i++) {
                        const p0 = linePoints[i - 1] || linePoints[i];
                        const p1 = linePoints[i];
                        const p2 = linePoints[i + 1];
                        const p3 = linePoints[i + 2] || p2;

                        if (i === 0) path.push("M", p1.x, p1.y);

                        const cp1x = p1.x + ((p2.x - p0.x) / 6) * tension;
                        const cp1y = p1.y + ((p2.y - p0.y) / 6) * tension;
                        const cp2x = p2.x - ((p3.x - p1.x) / 6) * tension;
                        const cp2y = p2.y - ((p3.y - p1.y) / 6) * tension;

                        path.push("C", cp1x, cp1y, cp2x, cp2y, p2.x, p2.y);
                    }

                    // === Draw the line ===
                    const line = chart.renderer
                        .path(path)
                        .attr({
                            stroke: "#3A416F",
                            "stroke-width": 3,
                            fill: "none",
                            zIndex: 10,
                        })
                        .add();

                    // === Animate stroke ===
                    const totalLength = line.element.getTotalLength
                        ? line.element.getTotalLength()
                        : 1000;

                    line.attr({
                        "stroke-dasharray": totalLength,
                        "stroke-dashoffset": totalLength,
                    });

                    line.animate(
                        { "stroke-dashoffset": 0 },
                        { duration: 4000, easing: "easeOutCubic" }
                    );

                    chart.customLine = line;

                    chart.customMarkers = linePoints.map((pt) =>
                        chart.renderer
                            .circle(pt.x, pt.y, 4)
                            .attr({
                                fill: "#FFFFFF",
                                stroke: "#3A416F",
                                "stroke-width": 2,
                                zIndex: 11,
                                opacity: 0,
                            })
                            .add()
                            .animate(
                                { opacity: 1 },
                                { duration: 1500, delay: 400 }
                            )
                    );
                },
            },
        },

        title: {
            text: "",
        },
        xAxis: {
            categories,
            offset: 50,
            labels: {
                y: 20,
                x: -10,
            },
        },
        yAxis: {
            title: {
                text: "Total unit",
            },
            allowDecimals: false,
            tickInterval: null,
            min: 0,
            max: paddedMax,
            endOnTick: true,
            startOnTick: true,
        },

        legend: {
            enabled: false,
        },
        credits: {
            enabled: false,
        },
        tooltip: {
            shared: true,
            useHTML: true,
            formatter: function () {
                const allSeriesNames = ["update", "waiting", "not-update"];
                const groupedByStack = {};

                // Group points by stack (equipment)
                this.points.forEach((p) => {
                    // Skip unwanted series
                    if (
                        p.series.name.includes("percentage") ||
                        p.series.name.includes("kosong") ||
                        p.series.name === "Update Line"
                    ) {
                        return;
                    }

                    const stack = p.series.options.stack || "Unknown";
                    if (!groupedByStack[stack]) groupedByStack[stack] = {};
                    groupedByStack[stack][p.series.name] = p;
                });

                let html = `<b>${this.x}</b><br/>`;

                Object.keys(groupedByStack).forEach((stack) => {
                    html += `<b>${stack}</b><br/>`;

                    allSeriesNames.forEach((name) => {
                        const point = groupedByStack[stack][name];
                        const color = point ? point.color : "#ccc"; // grey for missing
                        const value = point ? point.y : 0;
                        html += `
                    <span style="color:${color}">●</span> 
                    ${name}: <b>${value}</b> total unit<br/>
                `;
                    });

                    html += `<br/>`; // spacing between groups
                });

                return html;
            },
        },
        plotOptions: {
            series: {
                borderRadius: "25%",
            },
            column: {
                stacking: "normal",
                dataLabels: {
                    enabled: true,
                    style: {
                        color: "#FFFFFF",
                        textOutline: "2px contrast",
                        fontWeight: "bold",
                        fontSize: "9px",
                    },
                },
            },
        },
        series: [
            // ==== HD ====
            {
                name: "percentageHD",
                data: percentageHD.map((y, i) => ({
                    x: i,
                    y:
                        notUpdateHD[i] +
                        waitingHD[i] +
                        updateHD[i] +
                        kosongHD[i],
                    label: `${y.toFixed(0)}%`,
                })),
                // color: "transparent",
                stack: "HD",
                pointPlacement: -0.25,
                color: "transparent",
                enableMouseTracking: false,
                dataLabels: {
                    enabled: true,
                    verticalAlign: "bottom",
                    overflow: "none",
                    crop: false,
                    y: -10,
                    style: {
                        color: "#FFFFFF",
                        textOutline: "2px contrast",
                        fontWeight: "bold",
                        fontSize: "9px",
                    },
                    formatter() {
                        return this.point.label;
                    },
                },
            },

            {
                name: "not-update",
                data: notUpdateHD,
                stack: "HD",
                pointPlacement: -0.25,
                color: "#F45050",
            },
            {
                name: "waiting",
                data: waitingHD,
                stack: "HD",
                pointPlacement: -0.25,
                color: "#F99417",
            },
            {
                name: "update",
                data: updateHD,
                stack: "HD",
                pointPlacement: -0.25,
                color: "#1B9C85",
            },
            {
                name: "kosong",
                data: kosongHD,
                color: "transparent",
                stack: "HD",
                pointPlacement: -0.25,
                enableMouseTracking: false,
                dataLabels: {
                    enabled: true,
                    verticalAlign: "bottom",
                    overflow: "none",
                    crop: false,
                    rotation: -90,
                    y: 40,
                    style: {
                        color: "#000000",
                    },
                    formatter() {
                        return "HD";
                    },
                },
            },

            // ==== PC1250 ====
            {
                name: "percentagePC1250",
                data: percentagePC1250.map((y, i) => ({
                    x: i,
                    y:
                        notUpdatePC1250[i] +
                        waitingPC1250[i] +
                        updatePC1250[i] +
                        kosongPC1250[i],
                    label: `${y.toFixed(0)}%`,
                })),
                // color: "transparent",
                stack: "PC1250",
                pointPlacement: -0.15,
                color: "transparent",
                enableMouseTracking: false,
                dataLabels: {
                    enabled: true,
                    verticalAlign: "bottom",
                    overflow: "none",
                    crop: false,
                    y: -10,
                    style: {
                        color: "#FFFFFF",
                        textOutline: "2px contrast",
                        fontWeight: "bold",
                        fontSize: "9px",
                    },
                    formatter() {
                        return this.point.label;
                    },
                },
            },

            {
                name: "not-update",
                data: notUpdatePC1250,
                stack: "PC1250",
                pointPlacement: -0.15,
                color: "#F45050",
            },
            {
                name: "waiting",
                data: waitingPC1250,
                stack: "PC1250",
                pointPlacement: -0.15,
                color: "#F99417",
            },
            {
                name: "update",
                data: updatePC1250,
                stack: "PC1250",
                pointPlacement: -0.15,
                color: "#1B9C85",
            },
            {
                name: "kosong",
                data: kosongPC1250,
                color: "transparent",
                stack: "PC1250",
                pointPlacement: -0.15,
                enableMouseTracking: false,
                dataLabels: {
                    enabled: true,
                    verticalAlign: "bottom",
                    overflow: "none",
                    crop: false,
                    rotation: -90,
                    y: 40,
                    style: {
                        color: "#000000",
                    },
                    formatter() {
                        return "PC1250";
                    },
                },
            },

            // ==== PC2000 ====
            {
                name: "percentagePC2000",
                data: percentagePC2000.map((y, i) => ({
                    x: i,
                    y:
                        notUpdatePC2000[i] +
                        waitingPC2000[i] +
                        updatePC2000[i] +
                        kosongPC2000[i],
                    label: `${y.toFixed(0)}%`,
                })),
                // color: "transparent",
                stack: "PC2000",
                pointPlacement: -0.05,
                color: "transparent",
                enableMouseTracking: false,
                dataLabels: {
                    enabled: true,
                    verticalAlign: "bottom",
                    overflow: "none",
                    crop: false,
                    y: -10,
                    style: {
                        color: "#FFFFFF",
                        textOutline: "2px contrast",
                        fontWeight: "bold",
                        fontSize: "9px",
                    },
                    formatter() {
                        return this.point.label;
                    },
                },
            },

            {
                name: "not-update",
                data: notUpdatePC2000,
                stack: "PC2000",
                pointPlacement: -0.05,
                color: "#F45050",
            },

            {
                name: "waiting",
                data: waitingPC2000,
                stack: "PC2000",
                pointPlacement: -0.05,
                color: "#F99417",
            },

            {
                name: "update",
                data: updatePC2000,
                stack: "PC2000",
                pointPlacement: -0.05,
                color: "#1B9C85",
            },
            {
                name: "kosong",
                data: kosongPC2000,
                color: "transparent",
                stack: "PC2000",
                pointPlacement: -0.05,
                enableMouseTracking: false,
                dataLabels: {
                    enabled: true,
                    verticalAlign: "bottom",
                    overflow: "none",
                    crop: false,
                    rotation: -90,
                    y: 40,
                    style: {
                        color: "#000000",
                    },
                    formatter() {
                        return "PC2000";
                    },
                },
            },
            {
                type: "line",
                name: "Update Line",
                color: "transparent",
                enableMouseTracking: false,
                showInLegend: true,
            },
        ],
    });
});

const originalData = ref(null);

function createChart() {
    // Destroy previous chart if exists
    if (chartInstance.value) {
        chartInstance.value.destroy();
        chartInstance.value = null;
    }

    const categories = dataVhms.categories || [];

    // --- HD ---
    const kosongHD = dataVhms.HD?.kosong ?? Array(categories.length).fill(0);
    const updateHD = dataVhms.HD?.update ?? Array(categories.length).fill(0);
    const waitingHD = dataVhms.HD?.waiting ?? Array(categories.length).fill(0);
    const notUpdateHD =
        dataVhms.HD?.not_update ?? Array(categories.length).fill(0);

    // --- PC1250 ---
    const kosongPC1250 =
        dataVhms.PC1250?.kosong ?? Array(categories.length).fill(0);
    const updatePC1250 =
        dataVhms.PC1250?.update ?? Array(categories.length).fill(0);
    const waitingPC1250 =
        dataVhms.PC1250?.waiting ?? Array(categories.length).fill(0);
    const notUpdatePC1250 =
        dataVhms.PC1250?.not_update ?? Array(categories.length).fill(0);

    // --- PC2000 ---
    const kosongPC2000 =
        dataVhms.PC2000?.kosong ?? Array(categories.length).fill(0);
    const updatePC2000 =
        dataVhms.PC2000?.update ?? Array(categories.length).fill(0);
    const waitingPC2000 =
        dataVhms.PC2000?.waiting ?? Array(categories.length).fill(0);
    const notUpdatePC2000 =
        dataVhms.PC2000?.not_update ?? Array(categories.length).fill(0);

    const percentageHD = updateHD.map((val, i) => {
        const total = updateHD[i] + waitingHD[i] + notUpdateHD[i];
        return total > 0 ? (val / total) * 100 : 0; // percentage value
    });

    const percentagePC1250 = updatePC1250.map((val, i) => {
        const total = updatePC1250[i] + waitingPC1250[i] + notUpdatePC1250[i];
        return total > 0 ? (val / total) * 100 : 0; // percentage value
    });

    const percentagePC2000 = updatePC2000.map((val, i) => {
        const total = updatePC2000[i] + waitingPC2000[i] + notUpdatePC2000[i];
        return total > 0 ? (val / total) * 100 : 0; // percentage value
    });

    const allValues = [
        ...updateHD,
        ...waitingHD,
        ...notUpdateHD,
        ...updatePC1250,
        ...waitingPC1250,
        ...notUpdatePC1250,
        ...updatePC2000,
        ...waitingPC2000,
        ...notUpdatePC2000,
    ];

    const maxY = Math.max(...allValues);
    const paddedMax = Math.ceil((maxY * 1.1) / 10) * 9 || 100;

    // Inisialisasi chart
    chartInstance.value = Highcharts.chart("kpi-chart", {
        chart: {
            type: "column",
            zoomType: "none", // default
            scrollablePlotArea: { minWidth: null },
            events: {
                render() {
                    const chart = this;

                    if (chart.customLine) chart.customLine.destroy();
                    if (chart.customMarkers)
                        chart.customMarkers.forEach((m) => m.destroy());

                    const updateSeries = chart.series.filter(
                        (s) =>
                            (s.name === "update" || s.name === "updateTOTAL") &&
                            s.visible &&
                            s.type === "column"
                    );

                    const linePoints = [];

                    updateSeries.forEach((series) => {
                        series.points.forEach((p) => {
                            if (p.shapeArgs) {
                                const colCenter =
                                    p.shapeArgs.x + p.shapeArgs.width / 2;
                                const x = chart.plotLeft + colCenter;
                                const y = chart.plotTop + p.plotY;
                                linePoints.push({ x, y });
                            }
                        });
                    });

                    if (!linePoints.length) return;

                    linePoints.sort((a, b) => a.x - b.x);

                    const path = [];
                    const tension = 0.35;

                    for (let i = 0; i < linePoints.length - 1; i++) {
                        const p0 = linePoints[i - 1] || linePoints[i];
                        const p1 = linePoints[i];
                        const p2 = linePoints[i + 1];
                        const p3 = linePoints[i + 2] || p2;

                        if (i === 0) path.push("M", p1.x, p1.y);

                        const cp1x = p1.x + ((p2.x - p0.x) / 6) * tension;
                        const cp1y = p1.y + ((p2.y - p0.y) / 6) * tension;
                        const cp2x = p2.x - ((p3.x - p1.x) / 6) * tension;
                        const cp2y = p2.y - ((p3.y - p1.y) / 6) * tension;

                        path.push("C", cp1x, cp1y, cp2x, cp2y, p2.x, p2.y);
                    }

                    // === Draw the line ===
                    const line = chart.renderer
                        .path(path)
                        .attr({
                            stroke: "#3A416F",
                            "stroke-width": 3,
                            fill: "none",
                            zIndex: 10,
                        })
                        .add();

                    // === Animate stroke ===
                    const totalLength = line.element.getTotalLength
                        ? line.element.getTotalLength()
                        : 1000;

                    line.attr({
                        "stroke-dasharray": totalLength,
                        "stroke-dashoffset": totalLength,
                    });

                    line.animate(
                        { "stroke-dashoffset": 0 },
                        { duration: 4000, easing: "easeOutCubic" }
                    );

                    chart.customLine = line;

                    chart.customMarkers = linePoints.map((pt) =>
                        chart.renderer
                            .circle(pt.x, pt.y, 4)
                            .attr({
                                fill: "#FFFFFF",
                                stroke: "#3A416F",
                                "stroke-width": 2,
                                zIndex: 11,
                                opacity: 0,
                            })
                            .add()
                            .animate(
                                { opacity: 1 },
                                { duration: 1500, delay: 400 }
                            )
                    );
                },
            },
        },

        title: {
            text: "",
        },
        xAxis: {
            categories,
            offset: 50,
            labels: {
                y: 20,
                x: -10,
            },
        },
        yAxis: {
            title: {
                text: "Total unit",
            },
            allowDecimals: false,
            tickInterval: null,
            min: 0,
            max: paddedMax,
            endOnTick: true,
            startOnTick: true,
        },

        legend: {
            enabled: false,
        },
        credits: {
            enabled: false,
        },
        tooltip: {
            shared: true,
            useHTML: true,
            formatter: function () {
                let html = `<b>${this.x}</b><br/>`;

                // Only include relevant series
                this.points
                    .filter(
                        (p) =>
                            !p.series.name.includes("percentage") &&
                            !p.series.name.includes("kosong") &&
                            p.series.name !== "Update Line"
                    )
                    .forEach((p) => {
                        const color = p.color || "#ccc";
                        const value = p.y ?? 0;

                        html += `
                    <span style="color:${color}">●</span> 
                    ${p.series.name}: <b>${value}</b> total unit<br/>
                `;
                    });

                return html;
            },
        },
        plotOptions: {
            series: {
                borderRadius: "25%",
            },
            column: {
                stacking: "normal",
                dataLabels: {
                    enabled: true,
                    style: {
                        color: "#FFFFFF",
                        textOutline: "2px contrast",
                        fontWeight: "bold",
                        fontSize: "9px",
                    },
                },
            },
        },
        series: [
            // ==== HD ====
            {
                name: "percentageHD",
                data: percentageHD.map((y, i) => ({
                    x: i,
                    y:
                        notUpdateHD[i] +
                        waitingHD[i] +
                        updateHD[i] +
                        kosongHD[i],
                    label: `${y.toFixed(0)}%`,
                })),
                // color: "transparent",
                stack: "HD",
                pointPlacement: -0.25,
                color: "transparent",
                enableMouseTracking: false,
                dataLabels: {
                    enabled: true,
                    verticalAlign: "bottom",
                    overflow: "none",
                    crop: false,
                    y: -10,
                    style: {
                        color: "#FFFFFF",
                        textOutline: "2px contrast",
                        fontWeight: "bold",
                        fontSize: "9px",
                    },
                    formatter() {
                        return this.point.label;
                    },
                },
            },

            {
                name: "not-update",
                data: notUpdateHD,
                stack: "HD",
                pointPlacement: -0.25,
                color: "#F45050",
            },
            {
                name: "waiting",
                data: waitingHD,
                stack: "HD",
                pointPlacement: -0.25,
                color: "#F99417",
            },
            {
                name: "update",
                data: updateHD,
                stack: "HD",
                pointPlacement: -0.25,
                color: "#1B9C85",
            },
            {
                name: "kosong",
                data: kosongHD,
                color: "transparent",
                stack: "HD",
                pointPlacement: -0.25,
                enableMouseTracking: false,
                dataLabels: {
                    enabled: true,
                    verticalAlign: "bottom",
                    overflow: "none",
                    crop: false,
                    rotation: -90,
                    y: 40,
                    style: {
                        color: "#000000",
                    },
                    formatter() {
                        return "HD";
                    },
                },
            },

            // ==== PC1250 ====
            {
                name: "percentagePC1250",
                data: percentagePC1250.map((y, i) => ({
                    x: i,
                    y:
                        notUpdatePC1250[i] +
                        waitingPC1250[i] +
                        updatePC1250[i] +
                        kosongPC1250[i],
                    label: `${y.toFixed(0)}%`,
                })),
                // color: "transparent",
                stack: "PC1250",
                pointPlacement: -0.15,
                color: "transparent",
                enableMouseTracking: false,
                dataLabels: {
                    enabled: true,
                    verticalAlign: "bottom",
                    overflow: "none",
                    crop: false,
                    y: -10,
                    style: {
                        color: "#FFFFFF",
                        textOutline: "2px contrast",
                        fontWeight: "bold",
                        fontSize: "9px",
                    },
                    formatter() {
                        return this.point.label;
                    },
                },
            },

            {
                name: "not-update",
                data: notUpdatePC1250,
                stack: "PC1250",
                pointPlacement: -0.15,
                color: "#F45050",
            },
            {
                name: "waiting",
                data: waitingPC1250,
                stack: "PC1250",
                pointPlacement: -0.15,
                color: "#F99417",
            },
            {
                name: "update",
                data: updatePC1250,
                stack: "PC1250",
                pointPlacement: -0.15,
                color: "#1B9C85",
            },
            {
                name: "kosong",
                data: kosongPC1250,
                color: "transparent",
                stack: "PC1250",
                pointPlacement: -0.15,
                enableMouseTracking: false,
                dataLabels: {
                    enabled: true,
                    verticalAlign: "bottom",
                    overflow: "none",
                    crop: false,
                    rotation: -90,
                    y: 40,
                    style: {
                        color: "#000000",
                    },
                    formatter() {
                        return "PC1250";
                    },
                },
            },

            // ==== PC2000 ====
            {
                name: "percentagePC2000",
                data: percentagePC2000.map((y, i) => ({
                    x: i,
                    y:
                        notUpdatePC2000[i] +
                        waitingPC2000[i] +
                        updatePC2000[i] +
                        kosongPC2000[i],
                    label: `${y.toFixed(0)}%`,
                })),
                // color: "transparent",
                stack: "PC2000",
                pointPlacement: -0.05,
                color: "transparent",
                enableMouseTracking: false,
                dataLabels: {
                    enabled: true,
                    verticalAlign: "bottom",
                    overflow: "none",
                    crop: false,
                    y: -10,
                    style: {
                        color: "#FFFFFF",
                        textOutline: "2px contrast",
                        fontWeight: "bold",
                        fontSize: "9px",
                    },
                    formatter() {
                        return this.point.label;
                    },
                },
            },

            {
                name: "not-update",
                data: notUpdatePC2000,
                stack: "PC2000",
                pointPlacement: -0.05,
                color: "#F45050",
            },

            {
                name: "waiting",
                data: waitingPC2000,
                stack: "PC2000",
                pointPlacement: -0.05,
                color: "#F99417",
            },

            {
                name: "update",
                data: updatePC2000,
                stack: "PC2000",
                pointPlacement: -0.05,
                color: "#1B9C85",
            },
            {
                name: "kosong",
                data: kosongPC2000,
                color: "transparent",
                stack: "PC2000",
                pointPlacement: -0.05,
                enableMouseTracking: false,
                dataLabels: {
                    enabled: true,
                    verticalAlign: "bottom",
                    overflow: "none",
                    crop: false,
                    rotation: -90,
                    y: 40,
                    style: {
                        color: "#000000",
                    },
                    formatter() {
                        return "PC2000";
                    },
                },
            },
            {
                type: "line",
                name: "Update Line",
                color: "transparent",
                enableMouseTracking: false,
                showInLegend: true,
            },
        ],
    });
}

watch(selectedOptionUnit, (val) => {
    isAllUnit.value =
        val?.name === "ALL UNIT" && selectedOption.value.name != "Year";
    console.log(isAllUnit.value);

    isLoadingUnit.value = true;

    if (isAllUnit.value) {
    } else {
        createChart();

        console.log("updatee 0");
    }

    if (!chartInstance.value) return;
    const chart = chartInstance.value;

    if (isAllUnit.value) {
        chart.update(
            {
                chart: {
                    zoomType: "x",
                    scrollablePlotArea: {
                        minWidth: 2000,
                    },
                },
                tooltip: {
                    shared: true,
                    useHTML: true,
                    formatter: function () {
                        const allSeriesNames = [
                            "update",
                            "waiting",
                            "not-update",
                        ];
                        const groupedByStack = {};

                        // Group points by stack (equipment)
                        this.points.forEach((p) => {
                            // Skip unwanted series
                            if (
                                p.series.name.includes("percentage") ||
                                p.series.name.includes("kosong") ||
                                p.series.name === "Update Line"
                            ) {
                                return;
                            }

                            const stack = p.series.options.stack || "Unknown";
                            if (!groupedByStack[stack])
                                groupedByStack[stack] = {};
                            groupedByStack[stack][p.series.name] = p;
                        });

                        let html = `<b>${this.x}</b><br/>`;

                        Object.keys(groupedByStack).forEach((stack) => {
                            html += `<b>${stack}</b><br/>`;

                            allSeriesNames.forEach((name) => {
                                const point = groupedByStack[stack][name];
                                const color = point ? point.color : "#ccc"; // grey for missing
                                const value = point ? point.y : 0;
                                html += `
                    <span style="color:${color}">●</span> 
                    ${name}: <b>${value}</b> total unit<br/>
                `;
                            });

                            html += `<br/>`; // spacing between groups
                        });

                        return html;
                    },
                },
            },
            false
        );
        console.log("updatee 2000 " + selectedOption.value);
    }

    if (!originalData.value) {
        originalData.value = {
            categories: [...chart.xAxis[0].categories], // ✅ Save original xAxis categories
            series: chart.series.map((s) => ({
                name: s.name,
                stack: s.userOptions.stack,
                color: s.color,
                pointPlacement: s.userOptions.pointPlacement,
                enableMouseTracking: s.userOptions.enableMouseTracking,
                type: s.type,
                data: s.options.data.map((d) =>
                    typeof d === "object" ? { ...d } : d
                ),
                dataLabels: s.options.dataLabels
                    ? { ...s.options.dataLabels }
                    : undefined,
                userOptions: { ...s.userOptions },
            })),
        };

        // console.log(originalData.value);
    }

    const selectedName = typeof val === "string" ? val : val?.name;

    chart.series.forEach((s) => s.setVisible(false, false));

    if (selectedName === "ALL UNIT") {
        if (selectedOption.value.name == "Year") {
            chart.update(
                {
                    tooltip: {
                        shared: true,
                        useHTML: true,
                        formatter: function () {
                            const allSeriesNames = [
                                "update",
                                "waiting",
                                "not-update",
                            ];
                            const groupedByStack = {};

                            // Group points by stack (equipment)
                            this.points.forEach((p) => {
                                // Skip unwanted series
                                if (
                                    p.series.name.includes("percentage") ||
                                    p.series.name.includes("kosong") ||
                                    p.series.name === "Update Line"
                                ) {
                                    return;
                                }

                                const stack =
                                    p.series.options.stack || "Unknown";
                                if (!groupedByStack[stack])
                                    groupedByStack[stack] = {};
                                groupedByStack[stack][p.series.name] = p;
                            });

                            let html = `<b>${this.x}</b><br/>`;

                            Object.keys(groupedByStack).forEach((stack) => {
                                html += `<b>${stack}</b><br/>`;

                                allSeriesNames.forEach((name) => {
                                    const point = groupedByStack[stack][name];
                                    const color = point ? point.color : "#ccc"; // grey for missing
                                    const value = point ? point.y : 0;
                                    html += `
                    <span style="color:${color}">●</span> 
                    ${name}: <b>${value}</b> total unit<br/>
                `;
                                });

                                html += `<br/>`; // spacing between groups
                            });

                            return html;
                        },
                    },
                },
                false
            );
        }
        chart.xAxis[0].setCategories(originalData.value.categories, false);

        chart.series.forEach((s) => {
            const original = originalData.value.series.find(
                (o) => o.name === s.name && o.stack === s.userOptions.stack
            );
            if (original) s.setData(original.data, false);
        });

        chart.series
            .filter((s) => s.userOptions.stack !== "TOTAL")
            .forEach((s) => s.setVisible(true, false));

        chart.series
            .filter((s) => s.userOptions.stack === "TOTAL")
            .forEach((s) => s.remove(false));

        const visibleSeries = chart.series.filter((s) => s.visible);
        const categories = chart.xAxis[0].categories;
        const totalPerStackPerCategory = {};

        visibleSeries.forEach((series) => {
            const stack = series.userOptions.stack || "default";
            if (!totalPerStackPerCategory[stack]) {
                totalPerStackPerCategory[stack] = Array(categories.length).fill(
                    0
                );
            }
            series.yData.forEach((y, i) => {
                totalPerStackPerCategory[stack][i] += y || 0;
            });
        });

        const maxY = Math.max(
            ...Object.values(totalPerStackPerCategory)
                .flat()
                .filter((v) => typeof v === "number" && v > 0)
        );
        let paddedMax = 0;
        if (selectedOption.value.name === "Month") {
            paddedMax = Math.ceil((maxY * 1.1) / 10) * 4 || 100;
        } else {
            paddedMax = Math.ceil((maxY * 1.1) / 10) * 5 || 100;
        }
        chart.yAxis[0].update({ max: paddedMax });
    } else if (selectedName === "AKUMULASI") {
        // ✅ Restore the original X-axis categories
        chart.xAxis[0].setCategories(originalData.value.categories, false);

        const getSeriesData = (stack, name) => {
            const s = originalData.value.series.find(
                (item) => item.stack === stack && item.name === name
            );
            return s ? s.data : Array(chart.xAxis[0].categories.length).fill(0);
        };

        const updateHD = getSeriesData("HD", "update");
        const waitingHD = getSeriesData("HD", "waiting");
        const notUpdateHD = getSeriesData("HD", "not-update");

        const updatePC1250 = getSeriesData("PC1250", "update");
        const waitingPC1250 = getSeriesData("PC1250", "waiting");
        const notUpdatePC1250 = getSeriesData("PC1250", "not-update");

        const updatePC2000 = getSeriesData("PC2000", "update");
        const waitingPC2000 = getSeriesData("PC2000", "waiting");
        const notUpdatePC2000 = getSeriesData("PC2000", "not-update");

        const updateTOTAL = updateHD.map(
            (val, i) =>
                (val || 0) + (updatePC1250[i] || 0) + (updatePC2000[i] || 0)
        );
        const waitingTOTAL = waitingHD.map(
            (val, i) =>
                (val || 0) + (waitingPC1250[i] || 0) + (waitingPC2000[i] || 0)
        );
        const notUpdateTOTAL = notUpdateHD.map(
            (val, i) =>
                (val || 0) +
                (notUpdatePC1250[i] || 0) +
                (notUpdatePC2000[i] || 0)
        );

        const percentageTOTAL = updateTOTAL.map((val, i) => {
            const total =
                (updateTOTAL[i] || 0) +
                (waitingTOTAL[i] || 0) +
                (notUpdateTOTAL[i] || 0);
            return total > 0 ? (val / total) * 100 : 0;
        });

        // ✅ Remove any previous TOTAL stacks before redrawing
        const totalSeries = chart.series.filter(
            (s) => s.userOptions.stack === "TOTAL"
        );
        totalSeries.forEach((s) => s.remove(false));

        const combined = [
            {
                name: "not-update",
                stack: "TOTAL",
                data: notUpdateTOTAL,
                color: "#F45050",
                type: "column",
                pointPlacement: -0.25,
            },
            {
                name: "waiting",
                stack: "TOTAL",
                data: waitingTOTAL,
                color: "#F99417",
                type: "column",
                pointPlacement: -0.25,
            },
            {
                name: "update",
                stack: "TOTAL",
                data: updateTOTAL,
                color: "#1B9C85",
                type: "column",
                pointPlacement: -0.25,
            },
        ];

        const percentageSeries = {
            name: "percentageTOTAL",
            data: percentageTOTAL.map((y, i) => ({
                x: i,
                y: notUpdateTOTAL[i] + waitingTOTAL[i] + updateTOTAL[i],
                label: `${y.toFixed(0)}%`,
            })),
            stack: "TOTAL",
            pointPlacement: -0.25,
            color: "transparent",
            enableMouseTracking: false,
            dataLabels: {
                enabled: true,
                verticalAlign: "bottom",
                overflow: "none",
                crop: false,
                y: -10,
                style: {
                    color: "#FFFFFF",
                    textOutline: "2px contrast",
                    fontWeight: "bold",
                    fontSize: "9px",
                },
                formatter() {
                    return this.point.label;
                },
            },
        };

        // ✅ Add the updated TOTAL series back to chart
        chart.addSeries(percentageSeries, false);
        combined.forEach((s) => chart.addSeries(s, false));

        // ✅ Adjust Y-axis max
        const allValues = [
            ...updateHD,
            ...waitingHD,
            ...notUpdateHD,
            ...updatePC1250,
            ...waitingPC1250,
            ...notUpdatePC1250,
            ...updatePC2000,
            ...waitingPC2000,
            ...notUpdatePC2000,
        ];

        const maxY = Math.max(
            ...allValues.filter((v) => typeof v === "number" && v > 0)
        );
        const paddedMax = Math.ceil((maxY * 1.1) / 10) * 12 || 100;
        chart.yAxis[0].update({ max: paddedMax });

        // ✅ Redraw chart
        chart.redraw();
    } else {
        console.log(originalData.value);
        if (originalData.value) {
            chart.xAxis[0].setCategories(originalData.value.categories, false);
            chart.series.forEach((s) => {
                const original = originalData.value.series.find(
                    (o) => o.name === s.name && o.stack === s.userOptions.stack
                );
                if (original) s.setData(original.data, false);
            });
        }

        chart.series
            .filter((s) => s.userOptions.stack === val.name)
            .forEach((s) => s.setVisible(true, false));

        chart.series
            .filter(
                (s) =>
                    s.userOptions.stack !== val.name &&
                    s.userOptions.stack !== "TOTAL"
            )
            .forEach((s) => s.setVisible(false, false));

        const totalSeries = chart.series.filter(
            (s) => s.userOptions.stack === "TOTAL"
        );
        totalSeries.forEach((s) => s.remove(false));

        const visibleSeries = chart.series.filter((s) => s.visible);
        const categoryCount = chart.xAxis[0].categories.length;
        const totalPerCategory = Array(categoryCount).fill(0);

        visibleSeries.forEach((series) => {
            series.yData.forEach((y, i) => {
                totalPerCategory[i] += y || 0;
            });
        });

        const maxY = Math.max(...totalPerCategory);
        const paddedMax = Math.ceil((maxY * 1.1) / 10) * 5 || 100;
        chart.yAxis[0].update({ max: paddedMax });
    }

    isLoadingUnit.value = false;

    chart.redraw();
});

watch(selectedOption, (val) => {
    createChart();
    if (val.name == "Month") {
        if (!chartInstance.value) return;
        const chart = chartInstance.value;

        chart.update(
            {
                chart: {
                    zoomType: "x",
                    scrollablePlotArea: {
                        minWidth: 2000,
                    },
                },
                tooltip: {
                    shared: true,
                    useHTML: true,
                    formatter: function () {
                        const allSeriesNames = [
                            "update",
                            "waiting",
                            "not-update",
                        ];
                        const groupedByStack = {};

                        // Group points by stack (equipment)
                        this.points.forEach((p) => {
                            // Skip unwanted series
                            if (
                                p.series.name.includes("percentage") ||
                                p.series.name.includes("kosong") ||
                                p.series.name === "Update Line"
                            ) {
                                return;
                            }

                            const stack = p.series.options.stack || "Unknown";
                            if (!groupedByStack[stack])
                                groupedByStack[stack] = {};
                            groupedByStack[stack][p.series.name] = p;
                        });

                        let html = `<b>${this.x}</b><br/>`;

                        Object.keys(groupedByStack).forEach((stack) => {
                            html += `<b>${stack}</b><br/>`;

                            allSeriesNames.forEach((name) => {
                                const point = groupedByStack[stack][name];
                                const color = point ? point.color : "#ccc"; // grey for missing
                                const value = point ? point.y : 0;
                                html += `
                    <span style="color:${color}">●</span> 
                    ${name}: <b>${value}</b> total unit<br/>
                `;
                            });

                            html += `<br/>`; // spacing between groups
                        });

                        return html;
                    },
                },
            },
            false
        );
        selectedOptionUnit.value = { name: "ALL UNIT" };
        showChart.value = false;
    } else {
        selectedOptionUnit.value = { name: "ALL UNIT" };
        showChart.value = false;
    }
});

const getDataFilter = async () => {
    isLoadingChart.value = true;
    showChart.value = true;
    selectedOptionUnit.value = { name: "ALL UNIT" };

    try {
        if (!selectedOption.value) {
            alert("Please select Month or Year first!");
            return;
        }

        const params = {
            type: selectedOption.value.name,
            month:
                selectedOption.value.name === "Month"
                    ? selectedMonth.value?.value
                    : null,
            year: year.value,
        };

        const { data } = await axios.get(route("kpi-vhms.data.filter"), {
            params,
        });
        isLoadingChart.value = false;

        const chart = chartInstance.value;
        if (!chart) return;

        chart.xAxis[0].setCategories(data.categories, false);

        const stacks = ["HD", "PC1250", "PC2000"];
        const seriesNames = ["update", "waiting", "not-update", "kosong"];

        const backendKeyMap = {
            update: "update",
            waiting: "waiting",
            "not-update": "not_update",
            kosong: "kosong",
        };

        stacks.forEach((stack) => {
            seriesNames.forEach((name) => {
                const series = chart.series.find(
                    (s) =>
                        s.userOptions.stack === stack &&
                        s.userOptions.name === name
                );
                if (series) {
                    const backendKey = backendKeyMap[name];
                    series.setData(data[stack][backendKey], false);
                }
            });
        });

        const calcPercentages = (stack) => {
            const update = data[stack].update;
            const waiting = data[stack].waiting;
            const notUpdate = data[stack].not_update;
            const kosong = data[stack].kosong;

            const percentages = update.map((val, i) => {
                const total = update[i] + waiting[i] + notUpdate[i];
                return total > 0 ? (val / total) * 100 : 0;
            });

            return percentages.map((y, i) => ({
                x: i,
                y: notUpdate[i] + waiting[i] + update[i] + kosong[i],
                label: `${y.toFixed(0)}%`,
            }));
        };

        const percentageData = {
            HD: calcPercentages("HD"),
            PC1250: calcPercentages("PC1250"),
            PC2000: calcPercentages("PC2000"),
        };

        ["HD", "PC1250", "PC2000"].forEach((stack) => {
            const series = chart.series.find(
                (s) =>
                    s.userOptions.name === `percentage${stack}` &&
                    s.userOptions.stack === stack
            );
            if (series) {
                series.setData(percentageData[stack], false);
            }
        });

        const allValues = [
            ...data.HD.update,
            ...data.HD.waiting,
            ...data.HD.not_update,
            ...data.PC1250.update,
            ...data.PC1250.waiting,
            ...data.PC1250.not_update,
            ...data.PC2000.update,
            ...data.PC2000.waiting,
            ...data.PC2000.not_update,
        ];

        const maxY = Math.max(...allValues);
        const paddedMax = Math.ceil((maxY * 1.1) / 10) * 9 || 100;
        chart.yAxis[0].update({ max: paddedMax }, false);

        // === 6️⃣ Redraw chart ===
        chart.redraw();

        originalData.value = {
            categories: [...chart.xAxis[0].categories], // ✅ Save original xAxis categories
            series: chart.series.map((s) => ({
                name: s.name,
                stack: s.userOptions.stack,
                color: s.color,
                pointPlacement: s.userOptions.pointPlacement,
                enableMouseTracking: s.userOptions.enableMouseTracking,
                type: s.type,
                data: s.options.data.map((d) =>
                    typeof d === "object" ? { ...d } : d
                ),
                dataLabels: s.options.dataLabels
                    ? { ...s.options.dataLabels }
                    : undefined,
                userOptions: { ...s.userOptions },
            })),
        };

        vhmsNotDowload.value = (data.vhmsNotDownload || []).map((item) => ({
            ...item,
            feedback: item.feedback ?? null,
            status: item.status ?? "",
            id: item.id,
        }));

        selectedDate.value = "";
        globalSearch.value = "";
    } catch (error) {
        console.error("Failed to load VHMS data:", error);
        alert("Gagal memuat data VHMS.");
    }
};

const updateFeedback = async (id, feedback) => {
    // console.log(`✅ Feedback updated for ID ${id}: ${feedback}`);
    if (!id) {
        console.warn("⚠️ Missing ID for feedback update");
        return;
    }

    try {
        await axios.post("/inventory/kpi-vhms/feedback", { id, feedback });
        const row = vhmsNotDowload.value.find((r) => r.id === id);
        if (row) row.feedback = feedback === "DELETE" ? null : feedback;
        console.log(`✅ Feedback updated for ID ${id}: ${feedback}`);

        await getDataFilter();
    } catch (error) {
        console.error("❌ Failed to update feedback:", error);
    }
};

const monthOptions = [
    { name: "Januari", value: 1 },
    { name: "Februari", value: 2 },
    { name: "Maret", value: 3 },
    { name: "April", value: 4 },
    { name: "Mei", value: 5 },
    { name: "Juni", value: 6 },
    { name: "Juli", value: 7 },
    { name: "Agustus", value: 8 },
    { name: "September", value: 9 },
    { name: "Oktober", value: 10 },
    { name: "November", value: 11 },
    { name: "Desember", value: 12 },
];

// Set default month to current
selectedMonth.value = monthOptions.find((m) => m.value === currentMonth);
</script>

<template>
    <Head title="KPI VHMS" />

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
                                            { name: 'Month' },
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
                                    v-show="selectedOption?.name === 'Month'"
                                    class="relative w-48 transition-all rounded-lg ease mb-4"
                                >
                                    <VueMultiselect
                                        v-model="selectedMonth"
                                        :options="monthOptions"
                                        :multiple="false"
                                        :close-on-select="true"
                                        placeholder="Select Month"
                                        track-by="value"
                                        label="name"
                                    />
                                </div>
                                <div
                                    v-show="selectedOption.name === 'Month'"
                                    class="relative flex flex-wrap items-stretch w-48 transition-all rounded-lg ease mb-4"
                                >
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
                                    @click="getDataFilter"
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
                                    <div
                                        class="p-4 pb-0 mb-0 rounded-t-4 mt-3"
                                        :class="{
                                            'min-h-[500px]':
                                                selectedOption?.name ===
                                                    'Year' &&
                                                showChart == false,
                                        }"
                                    >
                                        <h6
                                            class="mb-2 dark:text-white text-center font-semibold text-lg"
                                        >
                                            KPI VHMS SITE
                                            {{ props.site }}
                                        </h6>

                                        <div
                                            class="relative flex flex-wrap items-stretch w-48 transition-all rounded-lg ease mb-4"
                                            :class="
                                                showChart
                                                    ? 'opacity-100 h-auto'
                                                    : 'opacity-0 h-0 overflow-hidden'
                                            "
                                        >
                                            <div
                                                v-if="isLoadingUnit"
                                                class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-70 rounded-lg z-10"
                                            >
                                                <span
                                                    class="text-gray-600 text-sm animate-pulse"
                                                    >Loading...</span
                                                >
                                            </div>

                                            <VueMultiselect
                                                v-model="selectedOptionUnit"
                                                :options="[
                                                    { name: 'ALL UNIT' },
                                                    { name: 'HD' },
                                                    { name: 'PC1250' },
                                                    { name: 'PC2000' },
                                                    { name: 'AKUMULASI' },
                                                ]"
                                                :multiple="false"
                                                :close-on-select="true"
                                                placeholder="Filter Unit"
                                                track-by="name"
                                                label="name"
                                                class="w-full"
                                            />
                                        </div>
                                    </div>
                                    <div class="">
                                        <!-- Pie Chart Highcharts -->
                                        <div
                                            class="bg-white rounded-xl shadow p-4 flex justify-center items-center min-h-[500px] relative"
                                        >
                                            <!-- Loading overlay -->
                                            <div
                                                v-if="isLoadingChart"
                                                class="absolute inset-0 flex justify-center items-center bg-white/80 z-10"
                                            >
                                                <div
                                                    class="animate-spin rounded-full h-12 w-12 border-4 border-blue-500 border-t-transparent"
                                                ></div>
                                            </div>

                                            <!-- Chart (hidden while loading) -->
                                            <div
                                                v-show="showChart"
                                                id="kpi-chart"
                                                class="w-full"
                                            ></div>
                                        </div>
                                    </div>

                                    <div v-if="selectedOption.name === 'Month'">
                                        <div
                                            class="p-4 pb-0 mb-0 rounded-t-4 mt-3"
                                        >
                                            <h6
                                                class="mb-2 dark:text-white text-center font-semibold text-lg"
                                            >
                                                HISTORICAL VHMS STATUS DATA
                                            </h6>
                                        </div>

                                        <div class="overflow-x-auto p-3">
                                            <div
                                                class="flex justify-between mb-3 gap-3"
                                            >
                                                <input
                                                    v-model="globalSearch"
                                                    type="text"
                                                    placeholder="Search SN, CN, or Model"
                                                    class="border rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 w-72"
                                                />
                                                <input
                                                    v-model="selectedDate"
                                                    type="date"
                                                    class="border rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400"
                                                />
                                            </div>
                                            <EasyDataTable
                                                table-class-name="customize-table"
                                                :headers="headers"
                                                :items="filteredItems"
                                                :rows-per-page="10"
                                                :body-cell-style="{
                                                    whiteSpace: 'nowrap',
                                                }"
                                                :columns-using-html="[
                                                    'status',
                                                    'feedback',
                                                ]"
                                            >
                                                <template #item-feedback="item">
                                                    <select
                                                        :value="item.feedback"
                                                        @change="
                                                            updateFeedback(
                                                                item.id,
                                                                $event.target
                                                                    .value
                                                            )
                                                        "
                                                        :class="[
                                                            'border rounded-md text-sm px-2 py-1 focus:ring-2 focus:ring-blue-400 transition-colors duration-200',
                                                            item.feedback
                                                                ? 'bg-green-100 border-green-400 text-green-800'
                                                                : 'bg-white border-gray-300 text-gray-700',
                                                        ]"
                                                    >
                                                        <option
                                                            disabled
                                                            value=""
                                                        >
                                                            Select feedback
                                                        </option>
                                                        <option
                                                            v-for="option in feedbackOptions"
                                                            :key="option"
                                                            :value="option"
                                                        >
                                                            {{ option }}
                                                        </option>
                                                    </select>
                                                </template>
                                            </EasyDataTable>
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
