<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import { ref, onMounted, watchEffect, nextTick  } from "vue";
import Highcharts from "highcharts";
import brandDark from "highcharts/themes/brand-dark";
import brandLight from "highcharts/themes/brand-light";
import { useDark } from '@vueuse/core';

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
    readyUsed_array: {
        type: Array,
    },
    loginSession: {
        type: String,
    },
});

const chartContainer = ref(null);

const theme = ref(null);

const isDark = useDark();

watchEffect(() => {
  
    if (isDark.value === true) {
        // dark
        theme.value = 'dark';
        
        nextTick(() => {
            initChartDark();
        });
        
    }else{
        // light
        theme.value = 'light';
        nextTick(() => {
            initChartLight();
        });
    }
    
});


const initChartDark = () => {
    Highcharts.chart(chartContainer.value, {
            chart: {
                type: "bar",
                backgroundColor: "#111C44"
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
                "color": "#fff",
                "hover": "#fff",
                "cursor": "pointer",
                
                }
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
    
}
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
    
}
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
                <div
                    class="relative flex flex-col min-w-0 break-words bg-white border-0 border-solid shadow-xl dark:bg-slate-850 dark:shadow-dark-xl dark:bg-gray-950 border-black-125 rounded-2xl bg-clip-border"
                >
                    <div class="p-4 pb-0 mb-0 rounded-t-4">
                        <div class="flex justify-between">
                            <h6 class="mb-2 dark:text-white">
                                Table Monitoring Device PPA
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
            </div>
        </div>

        <!-- <div class="flex flex-wrap mt-6 -mx-3">
                <div
                    class="w-full max-w-full px-3 mt-0 mb-6 lg:mb-0 lg:w-6/12 lg:flex-none"
                >
                    <div
                        class="relative flex flex-col min-w-0 break-words bg-white border-0 border-solid shadow-xl dark:bg-slate-850 dark:shadow-dark-xl dark:bg-gray-950 border-black-125 rounded-2xl bg-clip-border"
                    >
                        <div class="p-4 pb-0 mb-0 rounded-t-4">
                            <div class="flex justify-between">
                                <h6 class="mb-2 dark:text-white">
                                    Table Monitoring Device PPA - AMY
                                </h6>
                            </div>
                        </div>
                         <div class="overflow-x-auto">
                            <table
                                class="items-center w-full mb-4 align-top border-collapse border-gray-200 dark:border-white/40"
                            >
                                <tbody>
                                    <tr>
                                        <td
                                            class="p-2 align-middle bg-transparent border-b w-3/10 whitespace-nowrap dark:border-white/40"
                                        >
                                            <div
                                                class="flex items-center px-2 py-1"
                                            >
                                                <div>
                                                    <img
                                                        src="/assets/img/logoppa.png"
                                                        alt="Country flag"
                                                        class="inline transition-all duration-200 dark:hidden ease-nav-brand max-h-8 mr-2"
                                                    />
                                                </div>
                                                <div class="ml-6">
                                                    <h6
                                                        class="mb-0 text-sm leading-normal dark:text-white"
                                                    >
                                                        Access Point
                                                    </h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td
                                            class="p-2 align-middle bg-transparent border-b whitespace-nowrap dark:border-white/40"
                                        >
                                            <div class="text-center">
                                                <p
                                                    class="mb-0 text-xs font-semibold leading-tight dark:text-white dark:opacity-60"
                                                >
                                                    Ready Used:
                                                </p>
                                                <h6
                                                    class="mb-0 text-sm leading-normal dark:text-white"
                                                >
                                                    100
                                                </h6>
                                            </div>
                                        </td>
                                        <td
                                            class="p-2 align-middle bg-transparent border-b whitespace-nowrap dark:border-white/40"
                                        >
                                            <div class="text-center">
                                                <p
                                                    class="mb-0 text-xs font-semibold leading-tight dark:text-white dark:opacity-60"
                                                >
                                                    Ready Stanby:
                                                </p>
                                                <h6
                                                    class="mb-0 text-sm leading-normal dark:text-white"
                                                >
                                                    30
                                                </h6>
                                            </div>
                                        </td>
                                        <td
                                            class="p-2 text-sm leading-normal align-middle bg-transparent border-b whitespace-nowrap dark:border-white/40"
                                        >
                                            <div class="flex-1 text-center">
                                                <p
                                                    class="mb-0 text-xs font-semibold leading-tight dark:text-white dark:opacity-60"
                                                >
                                                    Scrap:
                                                </p>
                                                <h6
                                                    class="mb-0 text-sm leading-normal dark:text-white"
                                                >
                                                    10
                                                </h6>
                                            </div>
                                        </td>
                                        <td
                                            class="p-2 text-sm leading-normal align-middle bg-transparent border-b whitespace-nowrap dark:border-white/40"
                                        >
                                            <div class="flex-1 text-center">
                                                <p
                                                    class="mb-0 text-xs font-semibold leading-tight dark:text-white dark:opacity-60"
                                                >
                                                    Breakdown:
                                                </p>
                                                <h6
                                                    class="mb-0 text-sm leading-normal dark:text-white"
                                                >
                                                    15
                                                </h6>
                                            </div>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td
                                            class="p-2 align-middle bg-transparent border-b w-3/10 whitespace-nowrap dark:border-white/40"
                                        >
                                            <div
                                                class="flex items-center px-2 py-1"
                                            >
                                                <div>
                                                    <img
                                                        src="/assets/img/logoppa.png"
                                                        alt="Country flag"
                                                        class="inline transition-all duration-200 dark:hidden ease-nav-brand max-h-8 mr-2"
                                                    />
                                                </div>
                                                <div class="ml-6">
                                                    <h6
                                                        class="mb-0 text-sm leading-normal dark:text-white"
                                                    >
                                                        Switch
                                                    </h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td
                                            class="p-2 align-middle bg-transparent border-b whitespace-nowrap dark:border-white/40"
                                        >
                                            <div class="text-center">
                                                <p
                                                    class="mb-0 text-xs font-semibold leading-tight dark:text-white dark:opacity-60"
                                                >
                                                    Ready Used:
                                                </p>
                                                <h6
                                                    class="mb-0 text-sm leading-normal dark:text-white"
                                                >
                                                    100
                                                </h6>
                                            </div>
                                        </td>
                                        <td
                                            class="p-2 align-middle bg-transparent border-b whitespace-nowrap dark:border-white/40"
                                        >
                                            <div class="text-center">
                                                <p
                                                    class="mb-0 text-xs font-semibold leading-tight dark:text-white dark:opacity-60"
                                                >
                                                    Ready Stanby:
                                                </p>
                                                <h6
                                                    class="mb-0 text-sm leading-normal dark:text-white"
                                                >
                                                    30
                                                </h6>
                                            </div>
                                        </td>
                                        <td
                                            class="p-2 text-sm leading-normal align-middle bg-transparent border-b whitespace-nowrap dark:border-white/40"
                                        >
                                            <div class="flex-1 text-center">
                                                <p
                                                    class="mb-0 text-xs font-semibold leading-tight dark:text-white dark:opacity-60"
                                                >
                                                    Scrap:
                                                </p>
                                                <h6
                                                    class="mb-0 text-sm leading-normal dark:text-white"
                                                >
                                                    10
                                                </h6>
                                            </div>
                                        </td>
                                        <td
                                            class="p-2 text-sm leading-normal align-middle bg-transparent border-b whitespace-nowrap dark:border-white/40"
                                        >
                                            <div class="flex-1 text-center">
                                                <p
                                                    class="mb-0 text-xs font-semibold leading-tight dark:text-white dark:opacity-60"
                                                >
                                                    Breakdown:
                                                </p>
                                                <h6
                                                    class="mb-0 text-sm leading-normal dark:text-white"
                                                >
                                                    15
                                                </h6>
                                            </div>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td
                                            class="p-2 align-middle bg-transparent border-b w-3/10 whitespace-nowrap dark:border-white/40"
                                        >
                                            <div
                                                class="flex items-center px-2 py-1"
                                            >
                                                <div>
                                                    <img
                                                        src="/assets/img/logoppa.png"
                                                        alt="Country flag"
                                                        class="inline transition-all duration-200 dark:hidden ease-nav-brand max-h-8 mr-2"
                                                    />
                                                </div>
                                                <div class="ml-6">
                                                    <h6
                                                        class="mb-0 text-sm leading-normal dark:text-white"
                                                    >
                                                        Wirelless
                                                    </h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td
                                            class="p-2 align-middle bg-transparent border-b whitespace-nowrap dark:border-white/40"
                                        >
                                            <div class="text-center">
                                                <p
                                                    class="mb-0 text-xs font-semibold leading-tight dark:text-white dark:opacity-60"
                                                >
                                                    Ready Used:
                                                </p>
                                                <h6
                                                    class="mb-0 text-sm leading-normal dark:text-white"
                                                >
                                                    100
                                                </h6>
                                            </div>
                                        </td>
                                        <td
                                            class="p-2 align-middle bg-transparent border-b whitespace-nowrap dark:border-white/40"
                                        >
                                            <div class="text-center">
                                                <p
                                                    class="mb-0 text-xs font-semibold leading-tight dark:text-white dark:opacity-60"
                                                >
                                                    Ready Stanby:
                                                </p>
                                                <h6
                                                    class="mb-0 text-sm leading-normal dark:text-white"
                                                >
                                                    30
                                                </h6>
                                            </div>
                                        </td>
                                        <td
                                            class="p-2 text-sm leading-normal align-middle bg-transparent border-b whitespace-nowrap dark:border-white/40"
                                        >
                                            <div class="flex-1 text-center">
                                                <p
                                                    class="mb-0 text-xs font-semibold leading-tight dark:text-white dark:opacity-60"
                                                >
                                                    Scrap:
                                                </p>
                                                <h6
                                                    class="mb-0 text-sm leading-normal dark:text-white"
                                                >
                                                    10
                                                </h6>
                                            </div>
                                        </td>
                                        <td
                                            class="p-2 text-sm leading-normal align-middle bg-transparent border-b whitespace-nowrap dark:border-white/40"
                                        >
                                            <div class="flex-1 text-center">
                                                <p
                                                    class="mb-0 text-xs font-semibold leading-tight dark:text-white dark:opacity-60"
                                                >
                                                    Breakdown:
                                                </p>
                                                <h6
                                                    class="mb-0 text-sm leading-normal dark:text-white"
                                                >
                                                    15
                                                </h6>
                                            </div>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td
                                            class="p-2 align-middle bg-transparent border-b w-3/10 whitespace-nowrap dark:border-white/40"
                                        >
                                            <div
                                                class="flex items-center px-2 py-1"
                                            >
                                                <div>
                                                    <img
                                                        src="/assets/img/logoppa.png"
                                                        alt="Country flag"
                                                        class="inline transition-all duration-200 dark:hidden ease-nav-brand max-h-8 mr-2"
                                                    />
                                                </div>
                                                <div class="ml-6">
                                                    <h6
                                                        class="mb-0 text-sm leading-normal dark:text-white"
                                                    >
                                                        Printer
                                                    </h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td
                                            class="p-2 align-middle bg-transparent border-b whitespace-nowrap dark:border-white/40"
                                        >
                                            <div class="text-center">
                                                <p
                                                    class="mb-0 text-xs font-semibold leading-tight dark:text-white dark:opacity-60"
                                                >
                                                    Ready Used:
                                                </p>
                                                <h6
                                                    class="mb-0 text-sm leading-normal dark:text-white"
                                                >
                                                    100
                                                </h6>
                                            </div>
                                        </td>
                                        <td
                                            class="p-2 align-middle bg-transparent border-b whitespace-nowrap dark:border-white/40"
                                        >
                                            <div class="text-center">
                                                <p
                                                    class="mb-0 text-xs font-semibold leading-tight dark:text-white dark:opacity-60"
                                                >
                                                    Ready Stanby:
                                                </p>
                                                <h6
                                                    class="mb-0 text-sm leading-normal dark:text-white"
                                                >
                                                    30
                                                </h6>
                                            </div>
                                        </td>
                                        <td
                                            class="p-2 text-sm leading-normal align-middle bg-transparent border-b whitespace-nowrap dark:border-white/40"
                                        >
                                            <div class="flex-1 text-center">
                                                <p
                                                    class="mb-0 text-xs font-semibold leading-tight dark:text-white dark:opacity-60"
                                                >
                                                    Scrap:
                                                </p>
                                                <h6
                                                    class="mb-0 text-sm leading-normal dark:text-white"
                                                >
                                                    10
                                                </h6>
                                            </div>
                                        </td>
                                        <td
                                            class="p-2 text-sm leading-normal align-middle bg-transparent border-b whitespace-nowrap dark:border-white/40"
                                        >
                                            <div class="flex-1 text-center">
                                                <p
                                                    class="mb-0 text-xs font-semibold leading-tight dark:text-white dark:opacity-60"
                                                >
                                                    Breakdown:
                                                </p>
                                                <h6
                                                    class="mb-0 text-sm leading-normal dark:text-white"
                                                >
                                                    15
                                                </h6>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="w-full max-w-full px-3 mt-0 lg:w-6/12 lg:flex-none">
                    <div
                        class="border-black/12.5 shadow-xl dark:bg-slate-850 dark:shadow-dark-xl relative flex min-w-0 flex-col break-words rounded-2xl border-0 border-solid bg-white bg-clip-border"
                    >
                        <div class="p-4 pb-0 mb-0 rounded-t-4">
                            <div class="flex justify-between">
                                <h6 class="mb-2 dark:text-white">
                                    Table Monitoring Device PPA - ABP
                                </h6>
                            </div>
                        </div>
                         <div class="overflow-x-auto">
                            <table
                                class="items-center w-full mb-4 align-top border-collapse border-gray-200 dark:border-white/40"
                            >
                                <tbody>
                                    <tr>
                                        <td
                                            class="p-2 align-middle bg-transparent border-b w-3/10 whitespace-nowrap dark:border-white/40"
                                        >
                                            <div
                                                class="flex items-center px-2 py-1"
                                            >
                                                <div>
                                                    <img
                                                        src="/assets/img/logoppa.png"
                                                        alt="Country flag"
                                                        class="inline transition-all duration-200 dark:hidden ease-nav-brand max-h-8 mr-2"
                                                    />
                                                </div>
                                                <div class="ml-6">
                                                    <h6
                                                        class="mb-0 text-sm leading-normal dark:text-white"
                                                    >
                                                        Access Point
                                                    </h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td
                                            class="p-2 align-middle bg-transparent border-b whitespace-nowrap dark:border-white/40"
                                        >
                                            <div class="text-center">
                                                <p
                                                    class="mb-0 text-xs font-semibold leading-tight dark:text-white dark:opacity-60"
                                                >
                                                    Ready Used:
                                                </p>
                                                <h6
                                                    class="mb-0 text-sm leading-normal dark:text-white"
                                                >
                                                    100
                                                </h6>
                                            </div>
                                        </td>
                                        <td
                                            class="p-2 align-middle bg-transparent border-b whitespace-nowrap dark:border-white/40"
                                        >
                                            <div class="text-center">
                                                <p
                                                    class="mb-0 text-xs font-semibold leading-tight dark:text-white dark:opacity-60"
                                                >
                                                    Ready Stanby:
                                                </p>
                                                <h6
                                                    class="mb-0 text-sm leading-normal dark:text-white"
                                                >
                                                    30
                                                </h6>
                                            </div>
                                        </td>
                                        <td
                                            class="p-2 text-sm leading-normal align-middle bg-transparent border-b whitespace-nowrap dark:border-white/40"
                                        >
                                            <div class="flex-1 text-center">
                                                <p
                                                    class="mb-0 text-xs font-semibold leading-tight dark:text-white dark:opacity-60"
                                                >
                                                    Scrap:
                                                </p>
                                                <h6
                                                    class="mb-0 text-sm leading-normal dark:text-white"
                                                >
                                                    10
                                                </h6>
                                            </div>
                                        </td>
                                        <td
                                            class="p-2 text-sm leading-normal align-middle bg-transparent border-b whitespace-nowrap dark:border-white/40"
                                        >
                                            <div class="flex-1 text-center">
                                                <p
                                                    class="mb-0 text-xs font-semibold leading-tight dark:text-white dark:opacity-60"
                                                >
                                                    Breakdown:
                                                </p>
                                                <h6
                                                    class="mb-0 text-sm leading-normal dark:text-white"
                                                >
                                                    15
                                                </h6>
                                            </div>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td
                                            class="p-2 align-middle bg-transparent border-b w-3/10 whitespace-nowrap dark:border-white/40"
                                        >
                                            <div
                                                class="flex items-center px-2 py-1"
                                            >
                                                <div>
                                                    <img
                                                        src="/assets/img/logoppa.png"
                                                        alt="Country flag"
                                                        class="inline transition-all duration-200 dark:hidden ease-nav-brand max-h-8 mr-2"
                                                    />
                                                </div>
                                                <div class="ml-6">
                                                    <h6
                                                        class="mb-0 text-sm leading-normal dark:text-white"
                                                    >
                                                        Switch
                                                    </h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td
                                            class="p-2 align-middle bg-transparent border-b whitespace-nowrap dark:border-white/40"
                                        >
                                            <div class="text-center">
                                                <p
                                                    class="mb-0 text-xs font-semibold leading-tight dark:text-white dark:opacity-60"
                                                >
                                                    Ready Used:
                                                </p>
                                                <h6
                                                    class="mb-0 text-sm leading-normal dark:text-white"
                                                >
                                                    100
                                                </h6>
                                            </div>
                                        </td>
                                        <td
                                            class="p-2 align-middle bg-transparent border-b whitespace-nowrap dark:border-white/40"
                                        >
                                            <div class="text-center">
                                                <p
                                                    class="mb-0 text-xs font-semibold leading-tight dark:text-white dark:opacity-60"
                                                >
                                                    Ready Stanby:
                                                </p>
                                                <h6
                                                    class="mb-0 text-sm leading-normal dark:text-white"
                                                >
                                                    30
                                                </h6>
                                            </div>
                                        </td>
                                        <td
                                            class="p-2 text-sm leading-normal align-middle bg-transparent border-b whitespace-nowrap dark:border-white/40"
                                        >
                                            <div class="flex-1 text-center">
                                                <p
                                                    class="mb-0 text-xs font-semibold leading-tight dark:text-white dark:opacity-60"
                                                >
                                                    Scrap:
                                                </p>
                                                <h6
                                                    class="mb-0 text-sm leading-normal dark:text-white"
                                                >
                                                    10
                                                </h6>
                                            </div>
                                        </td>
                                        <td
                                            class="p-2 text-sm leading-normal align-middle bg-transparent border-b whitespace-nowrap dark:border-white/40"
                                        >
                                            <div class="flex-1 text-center">
                                                <p
                                                    class="mb-0 text-xs font-semibold leading-tight dark:text-white dark:opacity-60"
                                                >
                                                    Breakdown:
                                                </p>
                                                <h6
                                                    class="mb-0 text-sm leading-normal dark:text-white"
                                                >
                                                    15
                                                </h6>
                                            </div>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td
                                            class="p-2 align-middle bg-transparent border-b w-3/10 whitespace-nowrap dark:border-white/40"
                                        >
                                            <div
                                                class="flex items-center px-2 py-1"
                                            >
                                                <div>
                                                    <img
                                                        src="/assets/img/logoppa.png"
                                                        alt="Country flag"
                                                        class="inline transition-all duration-200 dark:hidden ease-nav-brand max-h-8 mr-2"
                                                    />
                                                </div>
                                                <div class="ml-6">
                                                    <h6
                                                        class="mb-0 text-sm leading-normal dark:text-white"
                                                    >
                                                        Wirelless
                                                    </h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td
                                            class="p-2 align-middle bg-transparent border-b whitespace-nowrap dark:border-white/40"
                                        >
                                            <div class="text-center">
                                                <p
                                                    class="mb-0 text-xs font-semibold leading-tight dark:text-white dark:opacity-60"
                                                >
                                                    Ready Used:
                                                </p>
                                                <h6
                                                    class="mb-0 text-sm leading-normal dark:text-white"
                                                >
                                                    100
                                                </h6>
                                            </div>
                                        </td>
                                        <td
                                            class="p-2 align-middle bg-transparent border-b whitespace-nowrap dark:border-white/40"
                                        >
                                            <div class="text-center">
                                                <p
                                                    class="mb-0 text-xs font-semibold leading-tight dark:text-white dark:opacity-60"
                                                >
                                                    Ready Stanby:
                                                </p>
                                                <h6
                                                    class="mb-0 text-sm leading-normal dark:text-white"
                                                >
                                                    30
                                                </h6>
                                            </div>
                                        </td>
                                        <td
                                            class="p-2 text-sm leading-normal align-middle bg-transparent border-b whitespace-nowrap dark:border-white/40"
                                        >
                                            <div class="flex-1 text-center">
                                                <p
                                                    class="mb-0 text-xs font-semibold leading-tight dark:text-white dark:opacity-60"
                                                >
                                                    Scrap:
                                                </p>
                                                <h6
                                                    class="mb-0 text-sm leading-normal dark:text-white"
                                                >
                                                    10
                                                </h6>
                                            </div>
                                        </td>
                                        <td
                                            class="p-2 text-sm leading-normal align-middle bg-transparent border-b whitespace-nowrap dark:border-white/40"
                                        >
                                            <div class="flex-1 text-center">
                                                <p
                                                    class="mb-0 text-xs font-semibold leading-tight dark:text-white dark:opacity-60"
                                                >
                                                    Breakdown:
                                                </p>
                                                <h6
                                                    class="mb-0 text-sm leading-normal dark:text-white"
                                                >
                                                    15
                                                </h6>
                                            </div>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td
                                            class="p-2 align-middle bg-transparent border-b w-3/10 whitespace-nowrap dark:border-white/40"
                                        >
                                            <div
                                                class="flex items-center px-2 py-1"
                                            >
                                                <div>
                                                    <img
                                                        src="/assets/img/logoppa.png"
                                                        alt="Country flag"
                                                        class="inline transition-all duration-200 dark:hidden ease-nav-brand max-h-8 mr-2"
                                                    />
                                                </div>
                                                <div class="ml-6">
                                                    <h6
                                                        class="mb-0 text-sm leading-normal dark:text-white"
                                                    >
                                                        Printer
                                                    </h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td
                                            class="p-2 align-middle bg-transparent border-b whitespace-nowrap dark:border-white/40"
                                        >
                                            <div class="text-center">
                                                <p
                                                    class="mb-0 text-xs font-semibold leading-tight dark:text-white dark:opacity-60"
                                                >
                                                    Ready Used:
                                                </p>
                                                <h6
                                                    class="mb-0 text-sm leading-normal dark:text-white"
                                                >
                                                    100
                                                </h6>
                                            </div>
                                        </td>
                                        <td
                                            class="p-2 align-middle bg-transparent border-b whitespace-nowrap dark:border-white/40"
                                        >
                                            <div class="text-center">
                                                <p
                                                    class="mb-0 text-xs font-semibold leading-tight dark:text-white dark:opacity-60"
                                                >
                                                    Ready Stanby:
                                                </p>
                                                <h6
                                                    class="mb-0 text-sm leading-normal dark:text-white"
                                                >
                                                    30
                                                </h6>
                                            </div>
                                        </td>
                                        <td
                                            class="p-2 text-sm leading-normal align-middle bg-transparent border-b whitespace-nowrap dark:border-white/40"
                                        >
                                            <div class="flex-1 text-center">
                                                <p
                                                    class="mb-0 text-xs font-semibold leading-tight dark:text-white dark:opacity-60"
                                                >
                                                    Scrap:
                                                </p>
                                                <h6
                                                    class="mb-0 text-sm leading-normal dark:text-white"
                                                >
                                                    10
                                                </h6>
                                            </div>
                                        </td>
                                        <td
                                            class="p-2 text-sm leading-normal align-middle bg-transparent border-b whitespace-nowrap dark:border-white/40"
                                        >
                                            <div class="flex-1 text-center">
                                                <p
                                                    class="mb-0 text-xs font-semibold leading-tight dark:text-white dark:opacity-60"
                                                >
                                                    Breakdown:
                                                </p>
                                                <h6
                                                    class="mb-0 text-sm leading-normal dark:text-white"
                                                >
                                                    15
                                                </h6>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div> -->
        <!-- end cards -->
    </AuthenticatedLayout>
</template>
