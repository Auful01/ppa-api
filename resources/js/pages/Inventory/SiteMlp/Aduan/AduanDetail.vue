<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import NavLinkCustom from "@/Components/NavLinkCustom.vue";
import { Head, Link } from "@inertiajs/vue3";
import dayjs from "dayjs";
import moment from "moment";
import { onMounted, ref } from "vue";

const pages = ref("Pages");
const subMenu = ref("Aduan Pages");
const mainMenu = ref("Detail Aduan");

const props = defineProps(["aduan"]);

const timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;

function convertToUserTime(date) {
    if (!date) return ""; // jika null/undefined/kosong, langsung return string kosong
    return dayjs
        .tz(date, "Asia/Makassar")
        .tz(timezone)
        .format("YYYY-MM-DD HH:mm:ss");
}

const mount = onMounted(() => {
    $("#tableData").DataTable();
});
</script>

<template>
    <Head title="Aduan" />

    <AuthenticatedLayout
        v-model:pages="pages"
        v-model:subMenu="subMenu"
        v-model:mainMenu="mainMenu"
    >
        <div class="py-12">
            <div class="min-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="flex flex-wrap -mx-3">
                    <div
                        class="w-full max-w-full px-3 mt-6 md:w-12/12 md:flex-none"
                    >
                        <div
                            class="relative flex flex-col min-w-0 break-words bg-white border-0 shadow-xl dark:bg-slate-850 dark:shadow-dark-xl rounded-2xl bg-clip-border"
                        >
                            <div
                                class="flex flex-row p-6 px-4 pb-0 mb-0 border-b-0 rounded-t-2xl"
                            >
                                <h6 class="mb-0 mr-3 dark:text-white">
                                    Detail Aduan
                                </h6>
                                <NavLinkCustom
                                    class="text-red-700"
                                    :href="route('aduanMlp.page')"
                                >
                                    Move to home page
                                </NavLinkCustom>
                            </div>
                            <div class="flex-auto p-12 pt-6 gap-4">
                                <div
                                    class="pb-4 grid grid-cols-1 md:grid-cols-2"
                                >
                                    <div class="grid grid-cols-2">
                                        <div>
                                            <p class="text-base">Ticket Code</p>
                                        </div>
                                        <div>
                                            <p>
                                                :
                                                {{ props.aduan.complaint_code }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2">
                                        <div>
                                            <p class="text-base">NRP</p>
                                        </div>
                                        <div>
                                            <p>: {{ props.aduan.nrp }}</p>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2">
                                        <div>
                                            <p class="text-base">
                                                Category Aduan
                                            </p>
                                        </div>
                                        <div>
                                            <p>
                                                :
                                                {{ props.aduan.category_name }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2">
                                        <div>
                                            <p class="text-base">
                                                Complaint Name
                                            </p>
                                        </div>
                                        <div>
                                            <p>
                                                :
                                                {{ props.aduan.complaint_name }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2">
                                        <div>
                                            <p class="text-base">
                                                Complaint Date
                                            </p>
                                        </div>
                                        <div>
                                            <p>
                                                :
                                                {{
                                                    convertToUserTime(
                                                        props.aduan
                                                            .date_of_complaint
                                                    )
                                                }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2">
                                        <div>
                                            <p class="text-base">
                                                WhatsApp Number
                                            </p>
                                        </div>
                                        <div>
                                            <p>
                                                : {{ props.aduan.phone_number }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2">
                                        <div>
                                            <p class="text-base">Crew</p>
                                        </div>
                                        <div>
                                            <p>: {{ props.aduan.crew }}</p>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-2">
                                        <div>
                                            <p class="text-base">
                                                Complaint Image
                                            </p>
                                        </div>
                                        <div>
                                            <p
                                                v-if="
                                                    props.aduan
                                                        .complaint_image != null
                                                "
                                            >
                                                :
                                                <img
                                                    :src="
                                                        props.aduan
                                                            .complaint_image
                                                    "
                                                    alt="No Document"
                                                    class="ml-40 w-50 h-30 shadow-2xl rounded-xl"
                                                />
                                            </p>
                                            <p
                                                v-if="
                                                    props.aduan
                                                        .complaint_image == null
                                                "
                                            >
                                                : -
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <hr
                                    class="h-px mx-0 my-4 bg-transparent border-0 opacity-25 bg-gradient-to-r from-transparent via-black/40 to-transparent dark:bg-gradient-to-r dark:from-transparent dark:via-white dark:to-transparent"
                                />

                                <div class="grid grid-cols-2">
                                    <div>
                                        <p class="text-base">
                                            Complaint Position
                                        </p>
                                    </div>
                                    <div>
                                        <p>
                                            :
                                            {{ props.aduan.complaint_position }}
                                        </p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2">
                                    <div>
                                        <p class="text-base">Status</p>
                                    </div>
                                    <div>
                                        <p>: {{ props.aduan.status }}</p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2">
                                    <div>
                                        <p class="text-base">Root Cause</p>
                                    </div>
                                    <div>
                                        <p>
                                            :
                                            {{
                                                props.aduan.root_cause
                                                    ?.root_cause_problem ?? "-"
                                            }}
                                        </p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2">
                                    <div>
                                        <p class="text-base">Response Time</p>
                                    </div>
                                    <div>
                                        <p>: {{ props.aduan.response_time }}</p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2">
                                    <div>
                                        <p class="text-base">Start Response</p>
                                    </div>
                                    <div>
                                        <p>
                                            :
                                            {{
                                                convertToUserTime(
                                                    props.aduan.start_response
                                                )
                                            }}
                                        </p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2">
                                    <div>
                                        <p class="text-base">Start Progress</p>
                                    </div>
                                    <div>
                                        <p>
                                            :
                                            {{
                                                convertToUserTime(
                                                    props.aduan.start_progress
                                                )
                                            }}
                                        </p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2">
                                    <div>
                                        <p class="text-base">End Progress</p>
                                    </div>
                                    <div>
                                        <p>
                                            :
                                            {{
                                                convertToUserTime(
                                                    props.aduan.end_progress
                                                )
                                            }}
                                        </p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2">
                                    <div>
                                        <p class="text-base">
                                            Issue/Complaint Note
                                        </p>
                                    </div>
                                    <div>
                                        <p>
                                            : {{ props.aduan.complaint_note }}
                                        </p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2">
                                    <div>Action Repair</div>
                                    <div>
                                        <p>: {{ props.aduan.action_repair }}</p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2">
                                    <div>
                                        <p class="text-base">Repair Note</p>
                                    </div>
                                    <div>
                                        <p>: {{ props.aduan.repair_note }}</p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2">
                                    <div>
                                        <p class="text-base">
                                            Location & Detail Location
                                        </p>
                                    </div>
                                    <div>
                                        <p>
                                            : {{ props.aduan.location }} (
                                            {{ props.aduan.detail_location }})
                                        </p>
                                    </div>
                                </div>

                                <div
                                    v-if="props.aduan.status === 'CLOSED'"
                                    class="grid grid-cols-2 items-start"
                                >
                                    <div>
                                        <p class="text-base">Repair Image</p>
                                    </div>

                                    <div class="flex items-start gap-2">
                                        <span>:</span>
                                        <img
                                            :src="props.aduan.repair_image"
                                            alt="No Document"
                                            class="w-50 h-30 shadow-2xl rounded-xl"
                                        />
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
