<template>
    <Head title="Create Daily Jobs" />

    <AuthenticatedLayout
        v-model:pages="pages"
        v-model:subMenu="subMenu"
        v-model:mainMenu="mainMenu"
    >
        <div class="py-12">
            <div class="flex flex-wrap -mx-3">
                <div class="flex-none w-full max-w-full px-3">
                    <div class="min-w-7xl mx-auto sm:px-6 lg:px-8">
                        <div
                            class="relative flex flex-col min-w-0 mb-6 break-words bg-white border-0 border-transparent border-solid shadow-xl dark:bg-slate-850 dark:shadow-dark-xl rounded-2xl bg-clip-border"
                        >
                            <div
                                class="p-6 pb-0 mb-0 border-b-0 border-b-solid rounded-t-2xl border-b-transparent"
                            >
                                <h3 class="text-xl font-bold">
                                    Create Daily Jobs
                                </h3>
                            </div>
                            <div class="flex-auto px-0 pt-0 pb-2">
                                <form
                                    @submit.prevent="submitJobs"
                                    class="p-6 space-y-6"
                                >
                                    <!-- Job List -->
                                    <div
                                        v-for="(job, index) in form.jobs"
                                        :key="index"
                                        class="mb-6 p-4 border rounded-lg space-y-4"
                                    >
                                        <!-- Header -->
                                        <div
                                            class="flex justify-between items-center"
                                        >
                                            <h4 class="text-lg font-semibold">
                                                Job #{{ index + 1 }}
                                            </h4>
                                            <button
                                                v-if="index > 0"
                                                @click="removeJob(index)"
                                                type="button"
                                                class="text-red-500 hover:text-red-700"
                                            >
                                                Remove
                                            </button>
                                        </div>

                                        <!-- Hidden Job Code -->
                                        <input
                                            type="hidden"
                                            v-model="job.code"
                                        />

                                        <!-- Hidden Date (submit as today) -->
                                        <input
                                            type="hidden"
                                            v-model="job.date"
                                        />

                                        <!-- Inline Fields -->
                                        <div class="flex flex-wrap gap-4">
                                            <!-- Job -->
                                            <div class="flex-1 min-w-[200px]">
                                                <label
                                                    class="block text-sm font-medium text-gray-700 mb-1"
                                                    >Job Assignment</label
                                                >
                                                <textarea
                                                    v-model="job.job"
                                                    class="w-full p-2 border border-gray-300 rounded-md"
                                                    rows="1"
                                                    required
                                                ></textarea>
                                            </div>

                                            <!-- Remark -->
                                            <div class="flex-1 min-w-[200px]">
                                                <label
                                                    class="block text-sm font-medium text-gray-700 mb-1"
                                                    >Remark</label
                                                >
                                                <textarea
                                                    v-model="job.remark"
                                                    class="w-full p-2 border border-gray-300 rounded-md"
                                                    rows="1"
                                                    required
                                                ></textarea>
                                            </div>

                                            <!-- Due Date -->
                                            <div class="flex-1 min-w-[200px]">
                                                <label
                                                    class="block text-sm font-medium text-gray-700 mb-1"
                                                    >Due Date</label
                                                >
                                                <input
                                                    type="date"
                                                    @focus="openPicker($event)"
                                                    @click="openPicker($event)"
                                                    v-model="job.due_date"
                                                    :min="minDate"
                                                    class="w-full p-2 border border-gray-300 rounded-md"
                                                    required
                                                />
                                            </div>

                                            <!-- Status -->
                                            <div class="flex-1 min-w-[200px]">
                                                <label
                                                    class="block text-sm font-medium text-gray-700 mb-1"
                                                    >Status</label
                                                >
                                                <select
                                                    v-model="job.status"
                                                    class="w-full p-2 border border-gray-300 rounded-md"
                                                    required
                                                >
                                                    <option value="open">
                                                        Open
                                                    </option>
                                                    <option value="continue">
                                                        Continue
                                                    </option>
                                                </select>
                                            </div>
                                            <!-- Category Job -->
                                            <div class="flex-1 min-w-[200px]">
                                                <label
                                                    class="block text-sm font-medium text-gray-700 mb-1"
                                                    >Kategori Job</label
                                                >
                                                <select
                                                    v-model="job.categoryJob"
                                                    class="w-full p-2 border border-gray-300 rounded-md"
                                                    required
                                                >
                                                    <option value="assignment">
                                                        Assignment
                                                    </option>
                                                    <option value="support">
                                                        support
                                                    </option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Add Job -->
                                    <div>
                                        <button
                                            @click="addJob"
                                            type="button"
                                            class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600"
                                        >
                                            Add Another Job
                                        </button>
                                    </div>

                                    <!-- Shared Fields -->
                                    <div
                                        class="grid grid-cols-1 md:grid-cols-3 gap-4 border-t pt-4"
                                    >
                                        <!-- Crew -->
                                        <div>
                                            <label
                                                class="block text-sm font-medium text-gray-700 mb-1"
                                                >Crew</label
                                            >
                                            <VueMultiselect
                                                v-model="sharedFields.crew"
                                                :options="users"
                                                :multiple="true"
                                                :close-on-select="true"
                                                placeholder="Nama - Site - NRP"
                                                track-by="id"
                                                label="label"
                                                class="multiselect-custom"
                                            />
                                            <p
                                                class="text-xs text-gray-500 mt-1"
                                            >
                                                Crew bisa lebih dari satu
                                            </p>
                                        </div>

                                        <!-- Sarana -->
                                        <div>
                                            <label
                                                class="block text-sm font-medium text-gray-700 mb-1"
                                                >Sarana</label
                                            >
                                            <input
                                                type="text"
                                                v-model="sharedFields.sarana"
                                                class="w-full p-2 border border-gray-300 rounded-md"
                                                required
                                            />
                                        </div>

                                        <!-- Shift -->
                                        <div>
                                            <label
                                                class="block text-sm font-medium text-gray-700 mb-1"
                                                >Shift</label
                                            >
                                            <select
                                                v-model="sharedFields.shift"
                                                class="w-full p-2 border border-gray-300 rounded-md"
                                                required
                                            >
                                                <option value="SHIFT_1">
                                                    SHIFT_1
                                                </option>
                                                <option value="SHIFT_2">
                                                    SHIFT_2
                                                </option>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Submit Actions -->
                                    <div class="flex justify-between space-x-4">
                                        <Link
                                            :href="
                                                route(
                                                    `daily-jobs.${site_link}.index`
                                                )
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
                                            Submit All Jobs
                                        </button>
                                    </div>
                                </form>
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
import { Head, Link, useForm, usePage } from "@inertiajs/vue3";
import { ref, computed } from "vue";
import { router } from "@inertiajs/vue3";
import VueMultiselect from "vue-multiselect";
import { Inertia } from "@inertiajs/inertia";

const props = defineProps({
    users: Array,
});

const site_link = usePage().props.site_link;

const selectedValues = ref([]);
const options = props.users;

const pages = ref("Operation");
const subMenu = ref("Daily Jobs");
const mainMenu = ref("Job Assignment");

function openPicker(event) {
    event.target.showPicker?.();
}

// Generate random alphanumeric string
const generateRandomString = (length) => {
    const chars = "0123456789abcdefghijklmnopqrstuvwxyz";
    let result = "";
    for (let i = length; i > 0; --i) {
        result += chars[Math.floor(Math.random() * chars.length)];
    }
    return result;
};

// Generate job code with JA prefix
const generateJobCode = () => {
    return `JA${generateRandomString(6)}`;
};

// Get current date in YYYY-MM-DD format
const getCurrentDate = () => {
    const today = new Date();
    return today.toISOString().split("T")[0];
};

const minDate = computed(() => {
    return getCurrentDate();
});

const form = useForm({
    jobs: [
        {
            code: generateJobCode(),
            job: "",
            categoryJob: "",
            date: getTodayDate(),
            due_date: "",
            status: "",
            remark: "",
        },
    ],
});

const sharedFields = ref({
    crew: [],
    sarana: "",
    shift: "",
});

const currentDate = getTodayDate();

function getTodayDate() {
    return new Date().toISOString().split("T")[0];
}

function addJob() {
    form.jobs.push({
        code: generateJobCode(),
        categoryJob: "", // <-- ensure this is added
        job: "",
        date: getTodayDate(),
        due_date: "",
        status: "",
        remark: "",
    });
}

function removeJob(index) {
    if (form.jobs.length > 1) {
        form.jobs.splice(index, 1);
    }
}

function submitJobs() {
    const payload = {
        jobs: form.jobs.map((job) => ({
            ...job,
            crew: sharedFields.value.crew.map((c) => c.id),
            sarana: sharedFields.value.sarana,
            shift: sharedFields.value.shift,
        })),
        shared: {
            crew: sharedFields.value.crew.map((c) => c.id),
            sarana: sharedFields.value.sarana,
            shift: sharedFields.value.shift,
        },
    };

    Inertia.post(route(`daily-jobs.${site_link}.store`), payload, {
        onSuccess: () => router.visit(route(`daily-jobs.${site_link}.index`)),
        onError: (errors) => {
            console.error("Validation errors:", errors);
            alert("Validation failed:\n" + Object.values(errors).join("\n"));
        },
    });
}
</script>

<style scoped>
/* Custom styles for multi-select dropdown */
select[multiple] {
    min-height: 100px;
    background-image: none;
}

/* Style for selected options */
select[multiple] option:checked {
    background-color: #3b82f6;
    color: white;
}
</style>
