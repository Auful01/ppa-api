<template>
    <Head title="Edit Daily Job" />
    <AuthenticatedLayout
        v-model:pages="pages"
        v-model:subMenu="subMenu"
        v-model:mainMenu="mainMenu"
    >
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white shadow-xl rounded-2xl p-6">
                    <h3 class="text-xl font-bold mb-6">Edit Daily Job</h3>

                    <form @submit.prevent="submitJob" class="space-y-6">
                        <div
                            class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6"
                        >
                            <!-- Description -->
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1"
                                >
                                    Job
                                </label>
                                <textarea
                                    v-model="form.description"
                                    class="w-full p-2 border border-gray-300 rounded-md"
                                    rows="1"
                                    required
                                ></textarea>
                            </div>

                            <!-- Remark -->
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1"
                                >
                                    Issue
                                </label>
                                <textarea
                                    v-model="form.issue"
                                    class="w-full p-2 border border-gray-300 rounded-md"
                                    rows="1"
                                    required
                                ></textarea>
                            </div>

                            <!-- Remark -->
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1"
                                >
                                    Action
                                </label>
                                <textarea
                                    v-model="form.action_taken"
                                    class="w-full p-2 border border-gray-300 rounded-md"
                                    rows="1"
                                    required
                                ></textarea>
                            </div>

                            <!-- Remark -->
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1"
                                >
                                    Remark
                                </label>
                                <textarea
                                    v-model="form.remark"
                                    class="w-full p-2 border border-gray-300 rounded-md"
                                    rows="1"
                                    required
                                ></textarea>
                            </div>
                            <!-- Start Progress -->
                            <div class="flex-1 min-w-[200px]">
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1"
                                >
                                    Start Progress
                                </label>
                                <input
                                    type="datetime-local"
                                    v-model="form.start_progress"
                                    class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    @focus="openPicker($event)"
                                    @click="openPicker($event)"
                                />
                            </div>

                            <!-- End Progress -->
                            <div class="flex-1 min-w-[200px]">
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1"
                                >
                                    End Progress
                                </label>
                                <input
                                    type="datetime-local"
                                    v-model="form.end_progress"
                                    class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    @focus="openPicker($event)"
                                    @click="openPicker($event)"
                                />
                            </div>

                            <!-- Category -->
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1"
                                >
                                    Category
                                </label>
                                <select
                                    v-model="form.category"
                                    @change="fetchProblems"
                                    class="w-full p-2 border border-gray-300 rounded-md"
                                    required
                                >
                                    <option value="">Select Category</option>
                                    <option
                                        v-for="category in props.categories"
                                        :key="category"
                                        :value="category"
                                    >
                                        {{ category }}
                                    </option>
                                </select>
                            </div>

                            <!-- Root Cause -->
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1"
                                >
                                    Root Cause
                                </label>
                                <select
                                    v-model="form.root_cause"
                                    class="w-full p-2 border border-gray-300 rounded-md"
                                    :required="job.category !== 'OTHER'"
                                >
                                    <option disabled value=""></option>
                                    <option
                                        v-for="(name, id) in form.problems"
                                        :key="name"
                                        :value="name"
                                    >
                                        {{ name }}
                                    </option>
                                </select>
                            </div>

                            <!-- Status -->
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1"
                                    >Status</label
                                >
                                <select
                                    v-model="form.status"
                                    class="w-full p-2 border border-gray-300 rounded-md"
                                    required
                                >
                                    <option value="open">Open</option>
                                    <option value="continue">Continue</option>
                                    <option value="closed">Closed</option>
                                </select>
                            </div>

                            <!-- Crew -->
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1"
                                    >Crew</label
                                >
                                <VueMultiselect
                                    v-model="form.crew"
                                    :options="users"
                                    :multiple="true"
                                    placeholder="Nama - Site - NRP"
                                    track-by="id"
                                    label="label"
                                    class="multiselect-custom"
                                />
                            </div>

                            <!-- Shift -->
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1"
                                    >Shift</label
                                >
                                <select
                                    v-model="form.shift"
                                    class="w-full p-2 border border-gray-300 rounded-md"
                                    required
                                >
                                    <option value="SHIFT_1">SHIFT_1</option>
                                    <option value="SHIFT_2">SHIFT_2</option>
                                </select>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex justify-end space-x-4 mt-6">
                            <Link
                                :href="
                                    route(`unschedule-jobs.${site_link}.index`)
                                "
                                class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400"
                            >
                                Cancel
                            </Link>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 disabled:opacity-50"
                            >
                                Update Job
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link, useForm, usePage } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import VueMultiselect from "vue-multiselect";
import { ref, onMounted } from "vue";
import { Inertia } from "@inertiajs/inertia";
import Swal from "sweetalert2";

onMounted(() => {
    if (form.category) {
        fetchProblems();
    }
});

const props = defineProps({
    job: Object,
    users: Array,
    categories: Array,
});

const site_link = usePage().props.site_link;

const pages = ref("Operation");
const subMenu = ref("Daily Jobs");
const mainMenu = ref("Job Unschedule");

function openPicker(event) {
    event.target.showPicker?.();
}

// Convert crew IDs to full objects for multiselect display
const crewObjects = props.users.filter((user) =>
    props.job.crew?.includes(user.id)
);

function formatDateTimeLocal(datetimeString) {
    if (!datetimeString) return "";

    const date = new Date(datetimeString);

    // Convert to local time in YYYY-MM-DDTHH:mm format
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, "0");
    const day = String(date.getDate()).padStart(2, "0");
    const hours = String(date.getHours()).padStart(2, "0");
    const minutes = String(date.getMinutes()).padStart(2, "0");

    return `${year}-${month}-${day}T${hours}:${minutes}`;
}

async function fetchProblems() {
    if (!form.category) return;
    try {
        const { data } = await axios.get("/root-cause-problems", {
            params: { category: form.category },
        });
        form.problems = data;

        // console.log(props.job.root_cause);
        // console.log(data.includes(props.job.root_cause));

        if (data.includes(props.job.root_cause)) {
            form.root_cause = props.job.root_cause;
        } else {
            form.root_cause = "";
        }
    } catch (error) {
        console.error("Error fetching root cause problems:", error);
    }
}

// async function fetchProblems() {
//     if (!form.category) return;
//     try {
//         const { data } = await axios.get("/root-cause-problems", {
//             params: { category: form.category },
//         });
//         form.problems = data;
//         if (!Object.keys(data).includes(form.root_cause)) {
//             form.root_cause = "";
//         }
//     } catch (error) {
//         console.error("Error fetching root cause problems:", error);
//     }
// }

const form = useForm({
    description: props.job.description,
    issue: props.job.issue,
    action_taken: props.job.action_taken,
    category: props.job.category || "",
    root_cause: props.job.root_cause || "",
    problems: [],
    remark: props.job.remark,
    status: props.job.status,
    crew: crewObjects,
    shift: props.job.shift,
    start_progress: formatDateTimeLocal(props.job.start_progress),
    end_progress: formatDateTimeLocal(props.job.end_progress),
});

function submitJob() {
    Swal.fire({
        title: "Are you sure?",
        text: "You want to edit this job?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes!",
    }).then((result) => {
        if (result.isConfirmed) {
            Inertia.put(
                route(`unschedule-jobs.${site_link}.update`, props.job.code),
                {
                    ...form.data(),
                    crew: form.crew.map((c) => c.id),
                },
                {
                    onSuccess: () =>
                        Inertia.visit(
                            route(`unschedule-jobs.${site_link}.index`)
                        ),
                    onError: (errors) => {
                        alert(
                            "Validation failed:\n" +
                                Object.values(errors).join("\n")
                        );
                    },
                }
            );
        }
    });
}
</script>
