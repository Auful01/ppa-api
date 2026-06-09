<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { ref, computed, onMounted, watch } from "vue";
import { Head, router, usePage } from "@inertiajs/vue3";
import Swal from "sweetalert2";
import Highcharts from "highcharts";

// Props from Inertia
const props = defineProps({
    schedules: Array,
    summary: Array,
    sudahSesuai: Array,
    belumSesuai: Array,
    labelPeriode: Object,
    kategori: String,
});

const deviceOptions = [
    { name: "Laptop", value: "laptop" },
    { name: "Komputer", value: "computer" },
    { name: "Printer", value: "printer" },
    { name: "Mobile Tower", value: "mobile_tower" },
];

const selectedOptionDept = ref(
    deviceOptions.find((opt) => opt.value === props.kategori) ??
        deviceOptions[0] // fallback
);

const isMobileTower = computed(() => {
    return selectedOptionDept.value?.value === "mobile_tower";
});

const site_link = usePage().props.site_link;

const pages = ref("Inspeksi");
const subMenu = ref("Schedule");
const mainMenu = computed(() => {
    return selectedOptionDept.value
        ? `Schedule Inspeksi ${selectedOptionDept.value.name}`
        : "Schedule Inspeksi Device";
});

// dynamic table header
const deviceCodeLabel = computed(() => {
    return selectedOptionDept.value
        ? `${selectedOptionDept.value.name} Code`
        : "Device Code";
});

// Editing state
const editingRows = ref({});

// Filters
const activeMonth = ref(null);
const activeDept = ref(null);

function formatDate(dateStr) {
    if (!dateStr) return "";
    const date = new Date(dateStr);
    return new Intl.DateTimeFormat("en-GB", {
        day: "2-digit",
        month: "short",
        year: "numeric",
    }).format(date);
}

// Computed filtered schedules
const filteredSchedules = computed(() => {
    return props.schedules.filter((item) => {
        const matchMonth = activeMonth.value
            ? new Date(item.tanggal_inspection).toLocaleString("default", {
                  month: "long",
              }) === activeMonth.value
            : true;

        const matchDept = activeDept.value
            ? item.dept === activeDept.value
            : true;

        return matchMonth && matchDept;
    });
});

// Edit functions
const startEditing = (id, date) => {
    editingRows.value[id] = {
        original: date,
        newDate: date,
    };
};

const cancelEditing = (id) => {
    delete editingRows.value[id];
};

watch(selectedOptionDept, (device) => {
    router.get(
        route(`inspection-scheduler-all.${site_link}.index`),
        { device: device.value },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        }
    );
});

const saveDate = (id, newDate) => {
    router.put(
        `/inspection-scheduler-all-${site_link}/${id}`,
        {
            tanggal_inspection: newDate,
            kategori: props.kategori,
        },
        {
            onSuccess: () => {
                Swal.fire(
                    "Success",
                    "Schedule updated successfully",
                    "success"
                );
                delete editingRows.value[id];
            },
            onError: () => {
                Swal.fire("Error", "Failed to update schedule", "error");
            },
        }
    );
};

const filteredDepartments = computed(() => {
    if (!activeMonth.value) {
        return [...new Set(props.summary.flatMap((item) => item.departments))];
    }

    const monthData = props.summary.find(
        (item) => item.month === activeMonth.value
    );
    return monthData ? monthData.departments : [];
});

const clearFilters = () => {
    activeMonth.value = null;
    activeDept.value = null;
};

const exportPdf = () => {
    // window.open(`/inspection-scheduler-all-${site_link}/rekap/pdf`, "_blank");

    window.open(
        `/inspection-scheduler-all-${site_link}/rekap/pdf?device=${props.kategori}`,
        "_blank"
    );
};

const chartInspeksi = ref(null);
let chartInstance = null;

const renderChart = () => {
    if (!chartInspeksi.value) return;

    if (chartInstance) {
        chartInstance.destroy();
    }

    chartInstance = Highcharts.chart(chartInspeksi.value, {
        chart: {
            type: "column",
            marginTop: 80,
        },
        title: {
            text: "Achievement kesesuaian Plan & Aktual Inspeksi",
            margin: 30,
        },
        xAxis: {
            categories: [props.labelPeriode],
        },
        yAxis: {
            min: 0,
            max: 100,
            title: {
                text: "Persentase (%)",
            },
        },
        plotOptions: {
            column: {
                dataLabels: {
                    enabled: true,
                    format: "{y}%",
                    style: {
                        fontSize: "12px",
                        color: "#000000",
                    },
                },
            },
        },
        series: [
            {
                name: "Sesuai",
                data: props.sudahSesuai,
                color: "#28a745",
            },
            {
                name: "Tidak Sesuai",
                data: props.belumSesuai,
                color: "#dc3545",
            },
        ],
        tooltip: {
            useHTML: true,
            pointFormat:
                '<span style="color:{series.color}">●</span> ' +
                "<b>{series.name}</b>: {point.y}%",
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
};

onMounted(() => {
    renderChart();
});

watch(
    () => [
        props.kategori,
        props.sudahSesuai,
        props.belumSesuai,
        props.labelPeriode,
    ],
    () => {
        renderChart();
    },
    { deep: true }
);
</script>

<template>
    <Head :title="mainMenu" />

    <AuthenticatedLayout
        v-model:pages="pages"
        v-model:subMenu="subMenu"
        v-model:mainMenu="mainMenu"
    >
        <div class="py-12">
            <div class="flex flex-wrap -mx-3">
                <div class="flex-none w-full max-w-full px-3">
                    <div
                        class="relative flex flex-wrap items-stretch w-50 transition-all rounded-lg ease mb-4"
                    >
                        <VueMultiselect
                            v-model="selectedOptionDept"
                            :options="deviceOptions"
                            :multiple="false"
                            :close-on-select="true"
                            placeholder="Select Device"
                            track-by="name"
                            label="name"
                        />
                    </div>
                    <div
                        class="relative flex flex-col min-w-0 mb-6 break-words bg-white border-0 border-transparent border-solid shadow-xl dark:bg-slate-850 dark:shadow-dark-xl rounded-2xl bg-clip-border"
                    >
                        <div class="p-12">
                            <h1 class="text-2xl font-bold mb-6">
                                {{ mainMenu }}
                            </h1>

                            <!-- 🧩 Flex container for Summary + Table -->
                            <div class="flex flex-col lg:flex-row gap-8">
                                <!-- 🔷 Summary Filter (Left Column) -->
                                <div class="w-full lg:w-1/2">
                                    <div
                                        v-if="!isMobileTower"
                                        class="flex flex-col lg:flex-row gap-6 mb-4"
                                    >
                                        <!-- Months -->
                                        <div class="w-full lg:w-1/2">
                                            <h2
                                                class="text-lg font-semibold mb-3"
                                            >
                                                📅 Months
                                            </h2>
                                            <div
                                                class="space-y-3 overflow-y-auto pr-2 pb-3 h-48 lg:h-auto"
                                            >
                                                <div
                                                    v-for="month in summary"
                                                    :key="month.month"
                                                    @click="
                                                        activeMonth =
                                                            month.month
                                                    "
                                                    class="cursor-pointer p-4 bg-white border-l-4 rounded shadow-sm transition hover:bg-gray-50"
                                                    :class="{
                                                        'border-blue-500':
                                                            activeMonth ===
                                                            month.month,
                                                        'border-gray-200':
                                                            activeMonth !==
                                                            month.month,
                                                    }"
                                                >
                                                    <div
                                                        class="font-semibold text-gray-800"
                                                    >
                                                        {{ month.month }}
                                                    </div>
                                                    <div
                                                        class="text-sm text-gray-500"
                                                    >
                                                        {{
                                                            month.departments
                                                                .length
                                                        }}
                                                        departments
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Departments -->
                                        <div class="w-full lg:w-1/2">
                                            <h2
                                                class="text-lg font-semibold mb-3"
                                            >
                                                🏢 Departments
                                            </h2>
                                            <div
                                                class="space-y-3 overflow-y-auto pr-2 pb-3 h-48 lg:h-auto"
                                            >
                                                <div
                                                    v-for="dept in filteredDepartments"
                                                    :key="dept"
                                                    @click="activeDept = dept"
                                                    class="cursor-pointer p-4 bg-white border-l-4 rounded shadow-sm transition hover:bg-gray-50"
                                                    :class="{
                                                        'border-green-500':
                                                            activeDept === dept,
                                                        'border-gray-200':
                                                            activeDept !== dept,
                                                    }"
                                                >
                                                    <div
                                                        class="font-semibold text-gray-800"
                                                    >
                                                        {{ dept }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- 🔄 Clear Filters Button -->
                                    <div
                                        v-if="activeMonth || activeDept"
                                        class="mb-8"
                                    >
                                        <button
                                            @click="clearFilters"
                                            class="px-3 py-1 text-sm bg-gray-200 hover:bg-gray-300 text-gray-700 rounded"
                                        >
                                            Clear Filters
                                        </button>
                                    </div>

                                    <hr
                                        class="h-px mx-0 my-4 bg-transparent border-0 opacity-25 bg-gradient-to-r from-transparent via-black/40 to-transparent dark:bg-gradient-to-r dark:from-transparent dark:via-white dark:to-transparent"
                                    />
                                    <div class="overflow-x-auto">
                                        <div
                                            ref="chartInspeksi"
                                            style="width: 100%; height: 400px"
                                        ></div>
                                    </div>
                                </div>

                                <div
                                    class="hidden lg:block w-px h-auto bg-gradient-to-b from-transparent via-black/40 to-transparent dark:via-white"
                                ></div>

                                <!-- 📋 Table (Right Column) -->
                                <div class="w-full lg:w-1/2">
                                    <button
                                        @click="exportPdf"
                                        class="flex items-center justify-center gap-2 w-32 h-10 text-xs font-semibold bg-gray-800 text-white rounded-md shadow-md transition-all duration-300 ease-in-out transform hover:bg-slate-850 hover:scale-105 ml-auto mb-4"
                                    >
                                        <i class="fas fa-download"></i>
                                        Rekap Jadwal
                                    </button>
                                    <div
                                        class="overflow-x-auto bg-white shadow rounded"
                                    >
                                        <table
                                            class="min-w-full table-auto border-collapse"
                                        >
                                            <thead
                                                class="bg-gray-100 text-left"
                                            >
                                                <tr>
                                                    <th
                                                        v-if="!isMobileTower"
                                                        class="px-4 py-2 border text-center"
                                                    >
                                                        Department
                                                    </th>
                                                    <th
                                                        class="px-4 py-2 border text-center"
                                                    >
                                                        {{ deviceCodeLabel }}
                                                    </th>
                                                    <th
                                                        class="px-4 py-2 border text-center"
                                                    >
                                                        Schedule Inspection
                                                    </th>
                                                    <th
                                                        class="px-4 py-2 border text-center"
                                                    >
                                                        Actual Inspection
                                                    </th>
                                                    <th
                                                        class="px-4 py-2 border text-center"
                                                    >
                                                        Actions
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <template
                                                    v-if="
                                                        filteredSchedules.length
                                                    "
                                                >
                                                    <tr
                                                        v-for="item in filteredSchedules"
                                                        :key="item.id"
                                                        class="hover:bg-gray-50"
                                                    >
                                                        <td
                                                            v-if="
                                                                !isMobileTower
                                                            "
                                                            class="px-4 py-2 border text-center"
                                                        >
                                                            {{ item.dept }}
                                                        </td>
                                                        <td
                                                            class="px-4 py-2 border text-center"
                                                        >
                                                            {{
                                                                item.device_code
                                                            }}
                                                        </td>
                                                        <td
                                                            class="px-4 py-2 border text-center"
                                                        >
                                                            <template
                                                                v-if="
                                                                    editingRows[
                                                                        item.id
                                                                    ]
                                                                "
                                                            >
                                                                <input
                                                                    type="date"
                                                                    v-model="
                                                                        editingRows[
                                                                            item
                                                                                .id
                                                                        ]
                                                                            .newDate
                                                                    "
                                                                    class="border border-gray-300 rounded px-2 py-1"
                                                                />
                                                            </template>
                                                            <template v-else>
                                                                {{
                                                                    formatDate(
                                                                        item.tanggal_inspection
                                                                    )
                                                                }}
                                                            </template>
                                                        </td>
                                                        <td
                                                            class="px-4 py-2 border text-center"
                                                        >
                                                            <template
                                                                v-if="
                                                                    item.actual_inspection !=
                                                                    null
                                                                "
                                                            >
                                                                {{
                                                                    formatDate(
                                                                        item.actual_inspection
                                                                    )
                                                                }}
                                                            </template>
                                                            <template v-else>
                                                                <span
                                                                    class="text-center block"
                                                                    >-</span
                                                                >
                                                            </template>
                                                        </td>

                                                        <td
                                                            class="px-4 py-2 border text-center space-x-2"
                                                        >
                                                            <template
                                                                v-if="
                                                                    editingRows[
                                                                        item.id
                                                                    ]
                                                                "
                                                            >
                                                                <button
                                                                    @click="
                                                                        saveDate(
                                                                            item.id,
                                                                            editingRows[
                                                                                item
                                                                                    .id
                                                                            ]
                                                                                .newDate
                                                                        )
                                                                    "
                                                                    class="text-green-600 hover:underline font-semibold"
                                                                >
                                                                    Save
                                                                </button>
                                                                <button
                                                                    @click="
                                                                        cancelEditing(
                                                                            item.id
                                                                        )
                                                                    "
                                                                    class="text-gray-500 hover:underline"
                                                                >
                                                                    Cancel
                                                                </button>
                                                            </template>
                                                            <template v-else>
                                                                <button
                                                                    @click="
                                                                        startEditing(
                                                                            item.id,
                                                                            item.tanggal_inspection
                                                                        )
                                                                    "
                                                                    class="px-4 py-2 bg-yellow-500 text-white rounded-md hover:bg-yellow-600"
                                                                >
                                                                    <i
                                                                        class="fas fa-edit"
                                                                    ></i>
                                                                    Edit
                                                                </button>
                                                            </template>
                                                        </td>
                                                    </tr>
                                                </template>
                                                <template v-else>
                                                    <tr>
                                                        <td
                                                            colspan="4"
                                                            class="text-center py-8 text-gray-500"
                                                        >
                                                            No data available.
                                                        </td>
                                                    </tr>
                                                </template>
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
