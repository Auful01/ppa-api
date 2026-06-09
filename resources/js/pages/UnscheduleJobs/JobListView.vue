<template>
    <Head title="Daily Jobs" />

    <AuthenticatedLayout
        v-model:pages="pages"
        v-model:subMenu="subMenu"
        v-model:mainMenu="mainMenu"
    >
        <div class="py-12">
            <div class="flex flex-wrap -mx-3">
                <div class="flex-none w-full max-w-full px-3">
                    <div class="min-w-7xl mx-auto sm:px-6 lg:px-8">
                        <div class="flex flex-wrap md:flex-nowrap gap-4">
                            <div class="flex flex-wrap gap-4 mb-6">
                                <!-- Start Date -->
                                <div class="mb-4">
                                    <label
                                        class="block text-sm font-medium text-black mb-1"
                                        for="start-date"
                                        >Start Date</label
                                    >
                                    <input
                                        id="start-date"
                                        type="date"
                                        v-model="filters.start_date"
                                        class="border rounded px-3 py-2 w-full"
                                    />
                                </div>

                                <!-- End Date -->
                                <div class="mb-4">
                                    <label
                                        class="block text-sm font-medium text-black mb-1"
                                        for="end-date"
                                        >End Date</label
                                    >
                                    <input
                                        id="end-date"
                                        type="date"
                                        v-model="filters.end_date"
                                        class="border rounded px-3 py-2 w-full"
                                    />
                                </div>

                                <!-- Shift -->
                                <div class="mb-4">
                                    <label
                                        class="block text-sm font-medium text-black mb-1"
                                        for="shift"
                                        >Shift</label
                                    >
                                    <select
                                        id="shift"
                                        v-model="filters.shift"
                                        class="border rounded px-3 py-2 w-full"
                                    >
                                        <option value="">All Shifts</option>
                                        <option value="SHIFT_1">SHIFT_1</option>
                                        <option value="SHIFT_2">SHIFT_2</option>
                                    </select>
                                </div>

                                <!-- Status -->
                                <div class="mb-4">
                                    <label
                                        class="block text-sm font-medium text-black mb-1"
                                        for="status"
                                        >Status</label
                                    >
                                    <select
                                        id="status"
                                        v-model="filters.status"
                                        class="border rounded px-3 py-2 w-full"
                                    >
                                        <option value="">All Status</option>
                                        <option value="open">Open</option>
                                        <option value="continue">
                                            Continue
                                        </option>
                                        <option value="closed">Closed</option>
                                        <option value="outstanding">
                                            Outstanding
                                        </option>
                                        <option value="cancel">Cancel</option>
                                    </select>
                                </div>

                                <!-- Buttons -->
                                <div
                                    class="mt-2 flex justify-center items-center gap-2"
                                >
                                    <button
                                        @click="applyFilters"
                                        type="button"
                                        class="bg-blue-600 text-white text-sm px-2 py-0 rounded hover:bg-blue-700 transition h-10 leading-none"
                                    >
                                        Apply
                                    </button>
                                    <button
                                        @click="resetFilters"
                                        type="button"
                                        class="bg-gray-500 text-white text-sm px-2 py-0 rounded hover:bg-gray-600 transition h-10 leading-none"
                                    >
                                        Reset
                                    </button>
                                </div>

                                <div
                                    v-if="loading"
                                    class="text-center text-white mb-4"
                                >
                                    <i
                                        class="fas fa-spinner fa-spin text-lg mr-2"
                                    ></i>
                                    Loading jobs...
                                </div>
                            </div>
                        </div>

                        <div
                            class="relative flex flex-col min-w-0 mb-6 break-words bg-white border-0 border-transparent border-solid shadow-xl dark:bg-slate-850 dark:shadow-dark-xl rounded-2xl bg-clip-border"
                        >
                            <div
                                class="p-6 pb-0 mb-0 border-b-0 border-b-solid rounded-t-2xl border-b-transparent flex justify-end"
                            >
                                <Link
                                    v-if="props.role != 'ict_ho'"
                                    :href="
                                        route(
                                            `unschedule-jobs.${site_link}.create`
                                        )
                                    "
                                    class="inline-block px-5 py-2.5 font-bold leading-normal text-center text-white align-middle transition-all bg-transparent rounded-lg cursor-pointer text-sm ease-in shadow-md bg-150 bg-gradient-to-tl from-zinc-800 to-zinc-700 dark:bg-gradient-to-tl dark:from-slate-750 dark:to-gray-850 hover:shadow-xs active:opacity-85 hover:-translate-y-px tracking-tight-rem bg-x-25"
                                >
                                    <i class="fas fa-plus"></i>&nbsp;&nbsp;Add
                                    Job
                                </Link>
                            </div>

                            <!-- TABLE VIEW FOR MD+ -->
                            <div class="hidden md:block">
                                <div class="flex-auto px-0 pt-0 pb-2">
                                    <div class="p-6 text-gray-900">
                                        <table
                                            id="tableData"
                                            class="stripe hover row-border order-column w-full"
                                        >
                                            <thead>
                                                <tr>
                                                    <th>Job</th>
                                                    <th>Team</th>
                                                    <th>Issue</th>
                                                    <th>Action</th>
                                                    <th>Job Date</th>
                                                    <th>Category</th>
                                                    <th>Root Cause</th>
                                                    <th>Status</th>
                                                    <th>Remark</th>
                                                    <th>Shift</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr
                                                    v-for="job in jobs"
                                                    :key="job.id"
                                                    :class="getRowClass(job)"
                                                >
                                                    <td>
                                                        {{ job.description }}
                                                    </td>
                                                    <td>
                                                        {{
                                                            getCrewNames(
                                                                job.crew
                                                            )
                                                        }}
                                                    </td>
                                                    <td>{{ job.issue }}</td>
                                                    <td>
                                                        {{ job.action_taken }}
                                                    </td>
                                                    <td>
                                                        {{
                                                            formattedDate(
                                                                job.date
                                                            )
                                                        }}
                                                    </td>
                                                    <td>{{ job.category }}</td>
                                                    <td>
                                                        {{ job.root_cause }}
                                                    </td>
                                                    <td>{{ job.status }}</td>
                                                    <td>{{ job.remark }}</td>
                                                    <td>{{ job.shift }}</td>
                                                    <td>
                                                        <div
                                                            class="flex space-x-2"
                                                        >
                                                            <button
                                                                v-if="
                                                                    props.role !=
                                                                    'ict_ho'
                                                                "
                                                                @click="
                                                                    editData(
                                                                        job.code
                                                                    )
                                                                "
                                                                class="px-4 py-2 bg-yellow-500 text-white rounded-md hover:bg-yellow-600"
                                                            >
                                                                <i
                                                                    class="fa-solid fa-file-pen"
                                                                ></i>
                                                            </button>
                                                            <button
                                                                v-if="
                                                                    props.role !=
                                                                    'ict_ho'
                                                                "
                                                                @click="
                                                                    deleteData(
                                                                        job.code
                                                                    )
                                                                "
                                                                class="px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600"
                                                            >
                                                                <i
                                                                    class="fa-solid fa-trash"
                                                                ></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- ACCORDION VIEW FOR MOBILE -->
                            <div class="block md:hidden w-full mx-auto">
                                <h5 class="text-center m-4">
                                    List Job Unschedule
                                </h5>
                                <div
                                    v-for="job in jobs"
                                    :key="job.id"
                                    class="m-4 rounded border border-slate-200"
                                >
                                    <button
                                        @click="toggleAccordion(job.id)"
                                        class="w-full p-4 font-semibold text-left text-slate-700 flex justify-between items-center"
                                    >
                                        {{ job.description }}
                                        <i
                                            :class="
                                                openAccordion === job.id
                                                    ? 'fa fa-caret-up'
                                                    : 'fa fa-caret-down'
                                            "
                                        ></i>
                                    </button>
                                    <div
                                        v-if="openAccordion === job.id"
                                        class="p-4 text-sm leading-normal text-slate-600 bg-slate-50"
                                    >
                                        <p>
                                            <strong>Team:</strong>
                                            {{ getCrewNames(job.crew) }}
                                        </p>
                                        <p>
                                            <strong>Issue:</strong>
                                            {{ job.issue }}
                                        </p>
                                        <p>
                                            <strong>Action:</strong>
                                            {{ job.action_taken }}
                                        </p>
                                        <p>
                                            <strong>Job Date:</strong>
                                            {{ formattedDate(job.date) }}
                                        </p>
                                        <p>
                                            <strong>Category:</strong>
                                            {{ job.category }}
                                        </p>
                                        <p>
                                            <strong>Root Cause:</strong>
                                            {{ job.root_cause }}
                                        </p>
                                        <p>
                                            <strong>Status:</strong>
                                            {{ job.status }}
                                        </p>
                                        <p>
                                            <strong>Remark:</strong>
                                            {{ job.remark }}
                                        </p>
                                        <p>
                                            <strong>Shift:</strong>
                                            {{ job.shift }}
                                        </p>
                                        <div class="mt-2 flex space-x-2">
                                            <button
                                                v-if="props.role != 'ict_ho'"
                                                @click="editData(job.code)"
                                                class="px-4 py-2 bg-yellow-500 text-white rounded-md hover:bg-yellow-600"
                                            >
                                                <i
                                                    class="fa-solid fa-file-pen"
                                                ></i>
                                            </button>
                                            <button
                                                v-if="props.role != 'ict_ho'"
                                                @click="deleteData(job.code)"
                                                class="px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600"
                                            >
                                                <i
                                                    class="fa-solid fa-trash"
                                                ></i>
                                            </button>
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

<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, usePage, useForm } from "@inertiajs/vue3";
import NavLinkCustom from "@/Components/NavLinkCustom.vue";
import { onMounted, ref } from "vue";
import moment from "moment";
import Swal from "sweetalert2";
import { Inertia } from "@inertiajs/inertia";

const loading = ref(false);

const openAccordion = ref(null);

function toggleAccordion(id) {
    openAccordion.value = openAccordion.value === id ? null : id;
}

const props = defineProps({ jobs: Array, role: Object });
const users = usePage().props.users;
const site_link = usePage().props.site_link;

const pages = ref("Operation");
const subMenu = ref("Daily Jobs");
const mainMenu = ref("Job Unschedule");

function getRowClass(job) {
    const today = new Date();
    const jobDate = new Date(job.date);
    const dueDate = new Date(job.due_date);
    const isClosed = job.status?.toLowerCase() === "closed";

    const isToday =
        jobDate.getDate() === today.getDate() &&
        jobDate.getMonth() === today.getMonth() &&
        jobDate.getFullYear() === today.getFullYear();

    if (isToday) return ""; //bg-blue-important
    if (!isClosed && dueDate >= today) return "";
    if (!isClosed && dueDate < today) return "";

    return "";
}

function formattedDate(date) {
    return date == null ? "-" : moment(date).format("D MMMM YYYY");
}

function getCrewNames(crew) {
    if (!crew || crew.length === 0) return "";
    return crew
        .map((id) => {
            const user = users.find((u) => u.id === id);
            return user ? user.name : `Unknown(${id})`;
        })
        .join(", ");
}

const form = useForm({});

const editData = (code) => {
    form.get(`/unschedule-jobs-${site_link}/${code}/edit`);
};

const deleteData = (code) => {
    Swal.fire({
        title: "Are you sure?",
        text: "You want to delete this job?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes!",
    }).then((result) => {
        if (result.isConfirmed) {
            Inertia.delete(
                route(`unschedule-jobs.${site_link}.destroy`, code),
                {
                    onSuccess: () => {
                        Swal.fire(
                            "Deleted!",
                            "Job has been deleted.",
                            "success"
                        );
                    },
                    onError: () => {
                        Swal.fire(
                            "Failed!",
                            "There was an error deleting the job.",
                            "error"
                        );
                    },
                }
            );
        }
    });
};

const filters = ref({
    start_date: usePage().props.filters?.start_date || "",
    end_date: usePage().props.filters?.end_date || "",
    shift: usePage().props.filters?.shift || "",
    status: usePage().props.filters?.status || "",
});

const applyFilters = () => {
    loading.value = true;

    Inertia.get(route(`unschedule-jobs.${site_link}.index`), filters.value, {
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

onMounted(() => {
    $("#tableData").DataTable({
        dom: "fBrtilp",
        scrollY: "40vh",
        scrollCollapse: true,
        order: [],
        buttons: [
            {
                extend: "spacer",
                style: "bar",
                text: "Export files:",
            },
            "csvHtml5",
            "excelHtml5",
            "spacer",
        ],
        initComplete: function () {
            var btns = $(".dt-button");
            btns.addClass(
                "text-white bg-green-600 hover:bg-green-700 font-medium rounded text-sm px-4 py-2 mr-2"
            );
            btns.removeClass("dt-button");
        },
    });
});
</script>

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

.bg-danger-row td {
    background-color: rgb(254 226 226) !important;
}

.bg-yellow-row td {
    background-color: rgb(254, 250, 226) !important;
}

.bg-blue-important td {
    background-color: rgb(219 234 254) !important; /* blue-100 */
}
</style>
