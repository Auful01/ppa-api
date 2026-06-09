<template>
    <Head title="Monitoring Jobs" />
    <AuthenticatedLayout
        v-model:pages="pages"
        v-model:subMenu="subMenu"
        v-model:mainMenu="mainMenu"
    >
        <div class="space-y-10 p-6">
            <div class="flex flex-wrap md:flex-nowrap gap-4">
                <div
                    class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 mb-6"
                >
                    <!-- Start Date -->
                    <div>
                        <label
                            class="block text-sm font-medium text-black mb-1"
                            for="start-date"
                        >
                            Start Date
                        </label>
                        <input
                            id="start-date"
                            type="date"
                            v-model="filters.start_date"
                            class="border rounded px-3 py-2 w-full"
                        />
                    </div>

                    <!-- End Date -->
                    <div>
                        <label
                            class="block text-sm font-medium text-black mb-1"
                            for="end-date"
                        >
                            End Date
                        </label>
                        <input
                            id="end-date"
                            type="date"
                            v-model="filters.end_date"
                            class="border rounded px-3 py-2 w-full"
                        />
                    </div>

                    <!-- Shift -->
                    <div>
                        <label
                            class="block text-sm font-medium text-black mb-1"
                            for="shift"
                        >
                            Shift
                        </label>
                        <select
                            id="shift"
                            v-model="filters.shift"
                            class="border rounded px-3 py-2 w-full"
                        >
                            <option value="">All Shifts</option>
                            <option value="pagi">Pagi</option>
                            <option value="malam">Malam</option>
                        </select>
                    </div>

                    <!-- Status -->
                    <div>
                        <label
                            class="block text-sm font-medium text-black mb-1"
                            for="status"
                        >
                            Status
                        </label>
                        <select
                            id="status"
                            v-model="filters.status"
                            class="border rounded px-3 py-2 w-full"
                        >
                            <option value="">All Status</option>
                            <option value="open">Open</option>
                            <option value="continue">Continue</option>
                            <option value="closed">Closed</option>
                            <option value="outstanding">Outstanding</option>
                            <option value="cancel">Cancel</option>
                        </select>
                    </div>

                    <!-- Buttons - span full width on small screens -->
                    <div
                        class="sm:col-span-2 md:col-span-4 flex flex-wrap justify-start items-center gap-2"
                    >
                        <button
                            @click="applyFilters"
                            type="button"
                            class="bg-blue-600 text-white text-sm px-4 py-2 rounded hover:bg-blue-700 transition"
                        >
                            Apply
                        </button>
                        <button
                            @click="resetFilters"
                            type="button"
                            class="bg-gray-500 text-white text-sm px-4 py-2 rounded hover:bg-gray-600 transition"
                        >
                            Reset
                        </button>
                        <button
                            @click="getEncryptedYear"
                            class="flex items-center gap-2 bg-gray-800 text-white text-sm font-semibold px-4 py-2 rounded hover:bg-slate-850 transition transform hover:scale-105"
                        >
                            <i class="fas fa-download"></i>
                            Export All Report
                        </button>
                    </div>

                    <!-- Loading -->
                    <div
                        v-if="loading"
                        class="sm:col-span-2 md:col-span-4 text-center text-black"
                    >
                        <i class="fas fa-spinner fa-spin text-lg mr-2"></i>
                        Loading jobs...
                    </div>
                </div>
            </div>

            <!-- Job Assignment -->
            <div class="border rounded-2xl shadow bg-white p-4">
                <h2 class="text-lg font-semibold mb-4">
                    Monitoring Job Assignment
                </h2>
                <JobTable
                    :jobs="scheduledJobs"
                    :users="users"
                    :type="'assignment'"
                />
            </div>

            <!-- Job Un-Schedule -->
            <div class="border rounded-2xl shadow bg-white p-4">
                <h2 class="text-lg font-semibold mb-4">
                    Monitoring Job Un-Schedule
                </h2>
                <JobTable
                    :jobs="unscheduledJobs"
                    :users="users"
                    :type="'unschedule'"
                />
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, usePage, router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import JobTable from "@/Components/JobTable.vue";
import { ref, onMounted } from "vue";
import { Inertia } from "@inertiajs/inertia";

const pages = ref("Operation");
const subMenu = ref("Daily Jobs");
const mainMenu = ref("Monitoring Jobs");

const site_link = usePage().props.site_link;

const loading = ref(false);

const filters = ref({
    start_date: usePage().props.filters?.start_date || "",
    end_date: usePage().props.filters?.end_date || "",
    shift: usePage().props.filters?.shift || "",
    status: usePage().props.filters?.status || "",
});

const applyFilters = () => {
    loading.value = true;

    Inertia.get(route(`monitoring-jobs.${site_link}.index`), filters.value, {
        preserveState: true,
        replace: true,
        onFinish: () => {
            loading.value = false;
        },
    });
};

const resetFilters = () => {
    filters.value = {
        start_date: "",
        end_date: "",
        shift: "",
        status: "",
    };
    applyFilters();
};

defineProps({
    scheduledJobs: Array,
    unscheduledJobs: Array,
    users: Array,
});

const getEncryptedYear = () => {
    const url = route(`monitoring-jobs.${site_link}.export`, filters.value);
    window.open(url, "_blank");
};
</script>
