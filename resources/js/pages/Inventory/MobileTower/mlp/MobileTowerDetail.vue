<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import NavLinkCustom from "@/Components/NavLinkCustom.vue";
import { Head, Link } from "@inertiajs/vue3";
import moment from "moment";
import { onMounted, ref } from "vue";

const pages = ref("Pages");
const subMenu = ref("Mobile Tower Pages");
const mainMenu = ref("Detail Mobile Tower");

const props = defineProps({
    mobileTowers: {
        type: Array,
    },
});

// Fungsi untuk format tanggal
function formattedDate(date) {
    return moment(date).format("MMMM Do, YYYY"); // Sesuaikan format sesuai kebutuhan
}
</script>

<template>
    <Head title="Inv Mobile Tower" />

    <AuthenticatedLayout
        v-model:pages="pages"
        v-model:subMenu="subMenu"
        v-model:mainMenu="mainMenu"
    >
        <div class="py-12">
            <div class="min-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="flex flex-wrap -mx-3">
                    <div class="w-full max-w-full px-3 mt-6">
                        <div
                            class="w-full max-w-full px-3 mt-6 md:w-12/12 md:flex-none"
                        >
                            <div
                                class="relative flex flex-col min-w-0 break-words bg-white border-0 shadow-xl dark:bg-slate-850 dark:shadow-dark-xl rounded-2xl bg-clip-border"
                            >
                                <div
                                    class="flex flex-row p-6 px-4 pb-0 mb-0 border-b-0 rounded-t-2xl"
                                >
                                    <h6 class="mb-4 mr-3 dark:text-white">
                                        Detail Mobile Tower
                                    </h6>
                                    <NavLinkCustom
                                        class="text-red-700"
                                        :href="route('mobileTowerMlp.page')"
                                    >
                                        Move to home page
                                    </NavLinkCustom>
                                </div>
                                <div
                                    class="p-6 px-4 pb-0 grid grid-cols-1 gap-4 md:grid-cols-2"
                                >
                                    <div class="grid grid-cols-2">
                                        <div>
                                            <p class="text-base">
                                                Inventory Number
                                            </p>
                                        </div>
                                        <div>
                                            <p>
                                                :
                                                {{
                                                    mobileTowers.inventory_number
                                                }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2">
                                        <div>
                                            <p class="text-base">Kode MT</p>
                                        </div>
                                        <div>
                                            <p>
                                                :
                                                {{ mobileTowers.mt_code }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2">
                                        <div>
                                            <p class="text-base">Tipe MT</p>
                                        </div>
                                        <div>
                                            <p>: {{ mobileTowers.type_mt }}</p>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2">
                                        <div>
                                            <p class="text-base">Lokasi MT</p>
                                        </div>
                                        <div>
                                            <p>
                                                :
                                                {{ mobileTowers.location }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2">
                                        <div>
                                            <p class="text-base">
                                                Detail Lokasi
                                            </p>
                                        </div>
                                        <div>
                                            <p>
                                                :
                                                {{
                                                    mobileTowers.detail_location
                                                }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2">
                                        <div>
                                            <p class="text-base">GPS</p>
                                        </div>
                                        <div>
                                            <p>: {{ mobileTowers.gps }}</p>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2">
                                        <div>
                                            <p class="text-base">Lampu LED</p>
                                        </div>
                                        <div>
                                            <p>: {{ mobileTowers.led_lamp }}</p>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2">
                                        <div>
                                            <p class="text-base">Kode Gembok</p>
                                        </div>
                                        <div>
                                            <p>
                                                :
                                                {{ mobileTowers.padlock_code }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-2">
                                        <div>
                                            <p class="text-base">Kondisi</p>
                                        </div>
                                        <div>
                                            <p>
                                                :
                                                {{ mobileTowers.condition }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2">
                                        <div>
                                            <p class="text-base">
                                                Inspection Remark
                                            </p>
                                        </div>
                                        <div>
                                            <p>
                                                :
                                                {{
                                                    mobileTowers.inspection_remark
                                                }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-2">
                                        <div>
                                            <p class="text-base">Status</p>
                                        </div>
                                        <div>
                                            <p>: {{ mobileTowers.status }}</p>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-2">
                                        <div>
                                            <p class="text-base">
                                                Last Edit At
                                            </p>
                                        </div>
                                        <div>
                                            <p>
                                                :
                                                {{
                                                    formattedDate(
                                                        mobileTowers.updated_at
                                                    )
                                                }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2">
                                        <div>
                                            <p class="text-base">Note</p>
                                        </div>
                                        <div>
                                            <p>: {{ mobileTowers.note }}</p>
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
