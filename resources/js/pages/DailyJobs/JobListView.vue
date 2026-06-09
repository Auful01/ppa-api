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
                                    v-if="canCreate && props.role != 'ict_ho'"
                                    :href="
                                        route(`daily-jobs.${site_link}.create`)
                                    "
                                    class="inline-block px-5 py-2.5 font-bold leading-normal text-center text-white align-middle transition-all bg-transparent rounded-lg cursor-pointer text-sm ease-in shadow-md bg-150 bg-gradient-to-tl from-zinc-800 to-zinc-700 dark:bg-gradient-to-tl dark:from-slate-750 dark:to-gray-850 hover:shadow-xs active:opacity-85 hover:-translate-y-px tracking-tight-rem bg-x-25"
                                >
                                    <i class="fas fa-plus"></i>&nbsp;&nbsp;Add
                                    Job
                                </Link>
                            </div>

                            <!-- Table View: Shown on screens md and up -->
                            <div class="hidden md:block">
                                <div class="flex-auto px-0 pt-0 pb-2">
                                    <div class="p-0">
                                        <div class="p-6 text-gray-900">
                                            <table
                                                id="tableData"
                                                class="stripe hover row-border order-column w-full"
                                            >
                                                <thead>
                                                    <tr>
                                                        <th>Job Assignment</th>
                                                        <th>Team</th>
                                                        <th>Sarana</th>
                                                        <th>Job Date</th>
                                                        <th>Due Date</th>
                                                        <th>Status</th>
                                                        <th>Remark</th>
                                                        <th>Category</th>
                                                        <th>Shift</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr
                                                        v-for="job in jobs"
                                                        :key="job.id"
                                                        :class="
                                                            getRowClass(job)
                                                        "
                                                    >
                                                        <td>
                                                            {{
                                                                job.description
                                                            }}
                                                        </td>
                                                        <td>
                                                            {{
                                                                getCrewNames(
                                                                    job.crew
                                                                )
                                                            }}
                                                        </td>
                                                        <td>
                                                            {{ job.sarana }}
                                                        </td>
                                                        <td>
                                                            {{
                                                                formattedDate(
                                                                    job.date
                                                                )
                                                            }}
                                                        </td>
                                                        <td>
                                                            {{
                                                                formattedDate(
                                                                    job.due_date
                                                                )
                                                            }}
                                                        </td>
                                                        <td>
                                                            {{ job.status }}
                                                        </td>
                                                        <td>
                                                            {{ job.remark }}
                                                        </td>
                                                        <td>
                                                            {{
                                                                job.category_job
                                                            }}
                                                        </td>
                                                        <td>{{ job.shift }}</td>
                                                        <td>
                                                            <div
                                                                v-if="
                                                                    job.status !==
                                                                        'closed' &&
                                                                    props.role !=
                                                                        'ict_ho'
                                                                "
                                                                class="flex space-x-2"
                                                            >
                                                                <button
                                                                    @click="
                                                                        editData(
                                                                            job.code
                                                                        )
                                                                    "
                                                                    class="px-4 py-2 bg-yellow-500 text-white rounded-md hover:bg-yellow-600"
                                                                >
                                                                    <i
                                                                        :class="
                                                                            canCreate
                                                                                ? 'fa-solid fa-file-pen'
                                                                                : 'fa-solid fa-file-import'
                                                                        "
                                                                    ></i>
                                                                </button>
                                                                <button
                                                                    v-if="
                                                                        canCreate &&
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
                            </div>

                            <!-- Accordion View: Shown on mobile screens only -->
                            <div class="block md:hidden">
                                <div class="w-full mx-auto">
                                    <h5 class="text-center mb-4">
                                        List Job Assigment
                                    </h5>
                                    <div
                                        v-for="job in jobs"
                                        :key="job.id"
                                        class="m-4 rounded-lg shadow"
                                    >
                                        <div
                                            class="border border-gray-200 rounded-md"
                                            :class="getRowClass(job)"
                                        >
                                            <button
                                                class="w-full px-4 py-3 text-left font-semibold flex justify-between items-center"
                                                @click="toggleAccordion(job.id)"
                                            >
                                                <span>{{
                                                    job.description
                                                }}</span>
                                                <i
                                                    :class="[
                                                        'fa',
                                                        openAccordions.includes(
                                                            job.id
                                                        )
                                                            ? 'fa-caret-up'
                                                            : 'fa-caret-down',
                                                    ]"
                                                ></i>
                                            </button>
                                            <div
                                                v-if="
                                                    openAccordions.includes(
                                                        job.id
                                                    )
                                                "
                                                class="px-4 pb-4 text-sm text-gray-600"
                                            >
                                                <p>
                                                    <strong>Team:</strong>
                                                    {{ getCrewNames(job.crew) }}
                                                </p>
                                                <p>
                                                    <strong>Sarana:</strong>
                                                    {{ job.sarana }}
                                                </p>
                                                <p>
                                                    <strong>Job Date:</strong>
                                                    {{
                                                        formattedDate(job.date)
                                                    }}
                                                </p>
                                                <p>
                                                    <strong>Due Date:</strong>
                                                    {{
                                                        formattedDate(
                                                            job.due_date
                                                        )
                                                    }}
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
                                                    <strong>Category:</strong>
                                                    {{ job.category_job }}
                                                </p>
                                                <p>
                                                    <strong>Shift:</strong>
                                                    {{ job.shift }}
                                                </p>
                                                <div
                                                    v-if="
                                                        job.status !== 'closed'
                                                    "
                                                    class="flex mt-2 space-x-2"
                                                >
                                                    <button
                                                        @click="
                                                            editData(job.code)
                                                        "
                                                        class="px-3 py-1 bg-yellow-500 text-white rounded-md hover:bg-yellow-600"
                                                    >
                                                        <i
                                                            :class="
                                                                canCreate
                                                                    ? 'fa-solid fa-file-pen'
                                                                    : 'fa-solid fa-file-import'
                                                            "
                                                        ></i>
                                                    </button>
                                                    <button
                                                        v-if="canCreate"
                                                        @click="
                                                            deleteData(job.code)
                                                        "
                                                        class="px-3 py-1 bg-red-500 text-white rounded-md hover:bg-red-600"
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

const openAccordions = ref([]);

const toggleAccordion = (id) => {
    if (openAccordions.value.includes(id)) {
        openAccordions.value = openAccordions.value.filter((i) => i !== id);
    } else {
        openAccordions.value.push(id);
    }
};

const props = defineProps({ jobs: Array, canCreate: Boolean, role: Object });
const users = usePage().props.users;
const site_link = usePage().props.site_link;

const pages = ref("Operation");
const subMenu = ref("Daily Jobs");
const mainMenu = ref("Job Assigment");

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
    if (!isClosed && dueDate >= today) return "bg-yellow-row";
    if (!isClosed && dueDate < today) return "bg-danger-row";

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
    form.get(`/daily-jobs-${site_link}/${code}/edit`);
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
            Inertia.delete(route(`daily-jobs.${site_link}.destroy`, code), {
                onSuccess: () => {
                    Swal.fire("Deleted!", "Job has been deleted.", "success");
                },
                onError: () => {
                    Swal.fire(
                        "Failed!",
                        "There was an error deleting the job.",
                        "error"
                    );
                },
            });
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

    Inertia.get(route(`daily-jobs.${site_link}.index`), filters.value, {
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

.bg-danger-row {
    background-color: rgb(254 226 226) !important;
}

.bg-danger-row td {
    background-color: rgb(254 226 226) !important;
}

.bg-yellow-row {
    background-color: rgb(254, 250, 226) !important;
}

.bg-yellow-row td {
    background-color: rgb(254, 250, 226) !important;
}

.bg-blue-important td {
    background-color: rgb(219 234 254) !important; /* blue-100 */
}
</style>
