<template>
    <div class="overflow-x-auto">
        <div class="mb-2">
            <input
                type="text"
                v-model="searchQuery"
                placeholder="Search Data Job"
                class="border rounded-lg px-2 py-1 w-full sm:w-auto"
            />
        </div>

        <table class="min-w-full text-sm border border-gray-200">
            <thead class="bg-red-200 text-black">
                <tr>
                    <th class="border px-2 py-1">No.</th>
                    <th v-if="type === 'assignment'" class="border px-2 py-1">
                        Nama Job
                    </th>
                    <th v-if="type === 'unschedule'" class="border px-2 py-1">
                        Start Progress
                    </th>
                    <th class="border px-2 py-1">Action</th>
                    <th class="border px-2 py-1">Remark</th>
                    <th v-if="type === 'assignment'" class="border px-2 py-1">
                        Team
                    </th>
                    <th v-if="type === 'assignment'" class="border px-2 py-1">
                        Category Job
                    </th>
                    <th v-if="type === 'unschedule'" class="border px-2 py-1">
                        Issue
                    </th>
                    <th v-if="type === 'unschedule'" class="border px-2 py-1">
                        Job
                    </th>
                    <th v-if="type === 'unschedule'" class="border px-2 py-1">
                        Team
                    </th>
                    <th class="border px-2 py-1">Status</th>
                </tr>
            </thead>
            <tbody>
                <tr
                    v-for="(job, index) in filteredJobs"
                    :key="job.id"
                    class="hover:bg-red-50"
                >
                    <td class="border px-2 py-1 text-center">
                        {{ index + 1 }}
                    </td>
                    <td v-if="type === 'assignment'" class="border px-2 py-1">
                        {{ job.description }}
                    </td>
                    <td v-if="type === 'unschedule'" class="border px-2 py-1">
                        {{ formattedDate(job.start_progress) }}
                    </td>
                    <td class="border px-2 py-1">{{ job.action_taken }}</td>
                    <td class="border px-2 py-1">{{ job.remark }}</td>
                    <td v-if="type === 'assignment'" class="border px-2 py-1">
                        {{ getCrewNames(job.crew) }}
                    </td>
                    <td v-if="type === 'unschedule'" class="border px-2 py-1">
                        {{ job.issue }}
                    </td>
                    <td v-if="type === 'unschedule'" class="border px-2 py-1">
                        {{ job.description }}
                    </td>
                    <td v-if="type === 'unschedule'" class="border px-2 py-1">
                        {{ getCrewNames(job.crew) }}
                    </td>
                    <td v-if="type === 'assignment'" class="border px-2 py-1">
                        {{ job.category_job }}
                    </td>
                    <td class="border px-2 py-1 text-center">
                        <span
                            class="text-xs font-semibold px-2 py-1 rounded bg-yellow-200 text-yellow-800"
                            v-if="job.status === 'continue'"
                        >
                            CONTINUE
                        </span>
                        <span
                            class="text-xs font-semibold px-2 py-1 rounded bg-green-200 text-green-800"
                            v-if="job.status === 'closed'"
                        >
                            CLOSED
                        </span>
                        <span
                            class="text-xs font-semibold px-2 py-1 rounded bg-blue-200 text-blue-800"
                            v-if="job.status === 'open'"
                        >
                            OPEN
                        </span>
                    </td>
                </tr>
                <tr v-if="filteredJobs.length === 0">
                    <td
                        :colspan="type === 'assignment' ? 6 : 7"
                        class="text-center py-3"
                    >
                        No data available in table
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script setup>
import { ref, computed } from "vue";
import moment from "moment";

const props = defineProps({
    jobs: Array,
    type: String,
    users: Array,
});

const searchQuery = ref("");

const filteredJobs = computed(() => {
    return props.jobs.filter((job) =>
        Object.values(job).some((val) =>
            String(val).toLowerCase().includes(searchQuery.value.toLowerCase())
        )
    );
});

function getCrewNames(crew) {
    if (!crew || crew.length === 0) return "";
    return crew
        .map((id) => {
            const user = props.users.find((u) => u.id === id);
            return user ? user.name : `Unknown(${id})`;
        })
        .join(", ");
}

function formattedDate(date) {
    return date == null ? "-" : moment(date).format("D MMMM YYYY HH:mm:ss");
}
</script>
