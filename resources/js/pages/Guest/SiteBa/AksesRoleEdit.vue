<style src="vue-multiselect/dist/vue-multiselect.css"></style>
<script setup>
import AuthenticatedLayoutForm from "@/Layouts/AuthenticatedLayoutForm.vue";
import { Link } from "@inertiajs/vue3";
import { Head, useForm } from "@inertiajs/vue3";
import VueMultiselect from "vue-multiselect";
import Swal from "sweetalert2";
import { ref, computed, onMounted } from "vue";

const props = defineProps(["dataUsers"]);

const form = useForm({
    id: props.dataUsers.id,
    id_user: props.dataUsers.id_user,
    nrp_user: props.dataUsers.nrp_user,
    nama_user: props.dataUsers.nama_user,
    divisi: props.dataUsers.divisi,
    dept: props.dataUsers.dept,
    site: props.dataUsers.site,
    status: props.dataUsers.status
});

const mount = onMounted(() => {
    showAlertTrue();
});

const isDisabled = ref(true);

const showAlertTrue = () => {
    Swal.fire({
        title: 'Tabel Role Akses User!',
        html: '<table class="tg"><thead> <tr> <th class="tg-lboi" rowspan="2">\</th> <th class="tg-9wq8" colspan="4">ADUAN</th> <th class="tg-c3ow" colspan="5">INVENTORY</th> <th class="tg-c3ow" colspan="5">INSPEKSI</th> </tr> <tr> <th class="tg-8g55">C</th> <th class="tg-ng7p">R</th> <th class="tg-ng7p">U</th> <th class="tg-ng7p">D</th> <th class="tg-8g55">C</th> <th class="tg-ng7p">R</th> <th class="tg-ng7p">U</th> <th class="tg-ng7p">D</th> <th class="tg-ng7p">A</th> <th class="tg-8g55">C</th> <th class="tg-ng7p">R</th> <th class="tg-ng7p">U</th> <th class="tg-ng7p">D</th> <th class="tg-ng7p">A</th> </tr></thead> <tbody> <tr> <td class="tg-0pky">ICT HO</td> <td class="tg-mak0">o</td> <td class="tg-rleq">o</td> <td class="tg-rleq">o</td> <td class="tg-rleq">o</td> <td class="tg-ngei">o</td> <td class="tg-ik58">o</td> <td class="tg-ik58">o</td> <td class="tg-ik58">o</td> <td class="tg-ik58">o</td> <td class="tg-kfkg">x</td> <td class="tg-x6qq">o</td> <td class="tg-x6qq">x</td> <td class="tg-x6qq">x</td> <td class="tg-x6qq">o</td> </tr> <tr> <td class="tg-0pky">ICT Dept Head</td> <td class="tg-mak0">o</td> <td class="tg-rleq">o</td> <td class="tg-rleq">o</td> <td class="tg-rleq">x</td> <td class="tg-ngei">x</td> <td class="tg-ik58">o</td> <td class="tg-ik58">x</td> <td class="tg-ik58">x</td> <td class="tg-ik58">o</td> <td class="tg-kfkg">x</td> <td class="tg-x6qq">o</td> <td class="tg-x6qq">x</td> <td class="tg-x6qq">x</td> <td class="tg-x6qq">o</td> </tr> <tr> <td class="tg-0pky">ICT Section Head</td> <td class="tg-mak0">o</td> <td class="tg-rleq">o</td> <td class="tg-rleq">o</td> <td class="tg-rleq">x</td> <td class="tg-ngei">x</td> <td class="tg-ik58">o</td> <td class="tg-ik58">x</td> <td class="tg-ik58">x</td> <td class="tg-ik58">o</td> <td class="tg-kfkg">x</td> <td class="tg-x6qq">o</td> <td class="tg-x6qq">x</td> <td class="tg-x6qq">x</td> <td class="tg-x6qq">o</td> </tr> <tr> <td class="tg-0pky">ICT Group Leader</td> <td class="tg-mak0">o</td> <td class="tg-rleq">o</td> <td class="tg-rleq">o</td> <td class="tg-rleq">o</td> <td class="tg-ngei">o</td> <td class="tg-ik58">o</td> <td class="tg-ik58">o</td> <td class="tg-ik58">o</td> <td class="tg-ik58">o</td> <td class="tg-kfkg">x</td> <td class="tg-x6qq">o</td> <td class="tg-x6qq">x</td> <td class="tg-x6qq">x</td> <td class="tg-x6qq">o</td> </tr> <tr> <td class="tg-0pky">ICT Technician</td> <td class="tg-mak0">o</td> <td class="tg-rleq">o</td> <td class="tg-rleq">o</td> <td class="tg-rleq">o</td> <td class="tg-ngei">o</td> <td class="tg-ik58">o</td> <td class="tg-ik58">o</td> <td class="tg-ik58">x</td> <td class="tg-ik58">x</td> <td class="tg-kfkg">x</td> <td class="tg-x6qq">o</td> <td class="tg-x6qq">o</td> <td class="tg-x6qq">x</td> <td class="tg-x6qq">x</td> </tr> <tr> <td class="tg-0pky">ICT Admin</td> <td class="tg-mak0">o</td> <td class="tg-rleq">o</td> <td class="tg-rleq">o</td> <td class="tg-rleq">o</td> <td class="tg-ngei">o</td> <td class="tg-ik58">o</td> <td class="tg-ik58">o</td> <td class="tg-ik58">x</td> <td class="tg-ik58">x</td> <td class="tg-kfkg">x</td> <td class="tg-x6qq">o</td> <td class="tg-x6qq">o</td> <td class="tg-x6qq">x</td> <td class="tg-x6qq">x</td> </tr> <tr> <td class="tg-0pky">Admin DEPT</td> <td class="tg-mak0">o</td> <td class="tg-rleq">o</td> <td class="tg-rleq">o</td> <td class="tg-rleq">o</td> <td class="tg-ngei">o</td> <td class="tg-ik58">o</td> <td class="tg-ik58">o</td> <td class="tg-ik58">x</td> <td class="tg-ik58">x</td> <td class="tg-kfkg">x</td> <td class="tg-x6qq">x</td> <td class="tg-x6qq">x</td> <td class="tg-x6qq">x</td> <td class="tg-x6qq">x</td> </tr> <tr> <td class="tg-0pky">Dept Head Site DEPT</td> <td class="tg-mak0">o</td> <td class="tg-rleq">o</td> <td class="tg-rleq">o</td> <td class="tg-rleq">x</td> <td class="tg-ngei">x</td> <td class="tg-ik58">o</td> <td class="tg-ik58">x</td> <td class="tg-ik58">x</td> <td class="tg-ik58">o</td> <td class="tg-kfkg">x</td> <td class="tg-x6qq">x</td> <td class="tg-x6qq">x</td> <td class="tg-x6qq">x</td> <td class="tg-x6qq">x</td> </tr> <tr> <td class="tg-0pky">PJO Site</td> <td class="tg-mak0">o</td> <td class="tg-rleq">o</td> <td class="tg-rleq">o</td> <td class="tg-rleq">x</td> <td class="tg-ngei">x</td> <td class="tg-ik58">o</td> <td class="tg-ik58">x</td> <td class="tg-ik58">x</td> <td class="tg-ik58">o</td> <td class="tg-kfkg">x</td> <td class="tg-x6qq">o</td> <td class="tg-x6qq">x</td> <td class="tg-x6qq">x</td> <td class="tg-x6qq">x</td> </tr> <tr> <td class="tg-0pky">HC Site</td> <td class="tg-mak0">x</td> <td class="tg-rleq">x</td> <td class="tg-rleq">x</td> <td class="tg-rleq">x</td> <td class="tg-ngei">x</td> <td class="tg-ik58">o</td> <td class="tg-ik58">x</td> <td class="tg-ik58">x</td> <td class="tg-ik58">x</td> <td class="tg-kfkg">x</td> <td class="tg-x6qq">x</td> <td class="tg-x6qq">x</td> <td class="tg-x6qq">x</td> <td class="tg-x6qq">x</td> </tr> </tbody></table><br><p style="font-size:0.5em"><b>C = CREATE, R = READ, U = UPDATE, D = DELETE, A = APPROVE</b></p>',
        confirmButtonText: "OK",
        confirmButtonColor: "#3085d6",
    });
}

const save = () => {

    form.post(route("akses.update"), {
        onSuccess: () => {
            // Show SweetAlert2 success notification
            Swal.fire({
                title: "Success!",
                text: "Data has been successfully updated!",
                icon: "success",
                confirmButtonText: "OK",
                confirmButtonColor: "#3085d6",
            });
        },
        onError: () => {
            Swal.fire({
                title: "error!",
                text: "Data not updated!",
                icon: "waring",
                confirmButtonText: "OK",
                confirmButtonColor: "#3085d6",
            });
        },
    });
};
</script>

<template>

    <Head title="Edit Data - Role Akses" />

    <AuthenticatedLayoutForm>
        <template #header>
            <nav>
                <!-- breadcrumb -->
                <ol class="flex flex-wrap pt-1 mr-12 bg-transparent rounded-lg sm:mr-16">
                    <li class="text-sm leading-normal">
                        <a class="text-white opacity-50">Pages</a>
                    </li>
                    <Link :href="route('akses.page')"
                        class="text-sm pl-2 capitalize leading-normal text-white before:float-left before:pr-2 before:text-white before:content-['/']"
                        aria-current="page">
                    Akses User
                    </Link>
                </ol>
                <h6 class="mb-0 font-bold text-white capitalize">
                    Update Akses Role Pages
                </h6>
            </nav>
        </template>

        <div class="w-full p-6 mx-auto">
            <div class="flex flex-wrap -mx-3 justify-center">
                <div class="w-full max-w-full px-3 shrink-0 md:w-8/12 md:flex-0">
                    <div
                        class="relative flex flex-col min-w-0 break-words justify-self: center bg-white border-0 shadow-xl dark:bg-slate-850 dark:shadow-dark-xl rounded-2xl bg-clip-border">
                        <div class="border-black/12.5 rounded-t-2xl border-b-0 border-solid p-6 pb-0">
                            <div class="flex items-center">
                                <p class="mb-0 font-bold dark:text-white/80">
                                    Form Update Akses User
                                </p>
                            </div>
                        </div>
                        <div class="flex-auto p-6">

                            <form @submit.prevent="save">
                                <hr
                                    class="h-px mx-0 my-4 bg-transparent border-0 opacity-25 bg-gradient-to-r from-transparent via-black/40 to-transparent dark:bg-gradient-to-r dark:from-transparent dark:via-white dark:to-transparent" />
                                <div class="flex flex-wrap -mx-3">
                                    <div class="w-full max-w-full px-3 shrink-0 md:w-6/12 md:flex-0">
                                        <div class="mb-4">
                                            <label for="nrp_user"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80">NRP</label>
                                            <input :disabled="isDisabled" required type="text" name="nrp_user"
                                                v-model="form.nrp_user"
                                                class="mb-5 bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 cursor-not-allowed dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-gray-400 dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                                placeholder="NRP" />
                                        </div>
                                    </div>
                                    <div class="w-full max-w-full px-3 shrink-0 md:w-6/12 md:flex-0">
                                        <div class="mb-4">
                                            <label for="nama_user"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80">Nama
                                                User</label>
                                            <input :disabled="isDisabled" required type="text" name="nama_user"
                                                v-model="form.nama_user"
                                                class="mb-5 bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 cursor-not-allowed dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-gray-400 dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                                placeholder="Nama User" />
                                        </div>
                                    </div>
                                    <div class="w-full max-w-full px-3 shrink-0 md:w-6/12 md:flex-0">
                                        <div class="mb-4">
                                            <label for="dept"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80">Departemen</label>
                                            <input :disabled="isDisabled" required type="text" name="dept"
                                                v-model="form.dept"
                                                class="mb-5 bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 cursor-not-allowed dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-gray-400 dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                                placeholder="Departemen" />
                                        </div>
                                    </div>
                                    <div class="w-full max-w-full px-3 shrink-0 md:w-6/12 md:flex-0">
                                        <div class="mb-4">
                                            <label for="divisi"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80">Position</label>
                                            <input :disabled="isDisabled" required type="text" name="divisi"
                                                v-model="form.divisi"
                                                class="mb-5 bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 cursor-not-allowed dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-gray-400 dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                                placeholder="Position" />
                                        </div>
                                    </div>
                                    <div class="w-full max-w-full px-3 shrink-0 md:w-6/12 md:flex-0">
                                        <div class="mb-4">
                                            <label for="site"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80">Site</label>
                                            <input :disabled="isDisabled" required type="text" name="site"
                                                v-model="form.site"
                                                class="mb-5 bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 cursor-not-allowed dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-gray-400 dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                                placeholder="Site" />
                                        </div>
                                    </div>
                                    <div class="w-full max-w-full px-3 shrink-0 md:w-6/12 md:flex-0">
                                        <div class="mb-4">
                                            <label for="status"
                                                class="inline-block mb-2 ml-1 text-sm text-slate-700 dark:text-white/80">
                                                Update Role</label>
                                            <a style="cursor: pointer;" class="icon-button" @click="showAlertTrue">
                                                <i class="ms-3 mt-1 text-red-700 fas fa-question-circle"></i>
                                            </a>
                                            <select id="status" required v-model="form.status" name="status"
                                                class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                                                <option value="BELUM">
                                                    Pilih Role
                                                </option>
                                                <option value="ict_ho">
                                                    ICT HO
                                                </option>
                                                <option value="soc_ho">
                                                    SOC HO
                                                </option>
                                                <option value="ict_dept_head">
                                                    ICT Dept Head
                                                </option>
                                                <option value="ict_section_head">
                                                    ICT Section Head
                                                </option>
                                                <option value="ict_group_leader">
                                                    ICT Group Leader
                                                </option>
                                                <option value="ict_technician">
                                                    ICT Technician
                                                </option>
                                                <option value="ict_admin">
                                                    ICT Admin
                                                </option>
                                                <option value="admin_site_dept">
                                                    Admin DEPT
                                                </option>
                                                <option value="dept_head_site_dept">
                                                    Dept Head Site DEPT
                                                </option>
                                                <option value="pjo_site">
                                                    PJO Site
                                                </option>
                                                <option value="hc_site">
                                                    HC Site
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <hr
                                    class="h-px mx-0 my-4 bg-transparent border-0 opacity-25 bg-gradient-to-r from-transparent via-black/40 to-transparent dark:bg-gradient-to-r dark:from-transparent dark:via-white dark:to-transparent" />
                                <div class="flex flex-nowrap mt-6 justify-between">
                                    <Link :href="route('akses.page')"
                                        class="relative inline-flex items-center justify-center p-0.5 mb-2 me-2 overflow-hidden text-sm font-medium text-gray-900 rounded-lg group bg-gradient-to-br from-red-200 via-red-300 to-yellow-200 group-hover:from-red-200 group-hover:via-red-300 group-hover:to-yellow-200 dark:text-white dark:hover:text-gray-900 focus:ring-4 focus:outline-none focus:ring-red-100 dark:focus:ring-red-400">
                                    <span
                                        class="relative px-5 py-2.5 transition-all ease-in duration-75 bg-white dark:bg-gray-900 rounded-md group-hover:bg-opacity-0">
                                        Cancel
                                    </span>
                                    </Link>

                                    <button type="submit"
                                        class="relative inline-flex items-center justify-center p-0.5 mb-2 me-2 overflow-hidden text-sm font-medium text-gray-900 rounded-lg group bg-gradient-to-br from-purple-600 to-blue-500 group-hover:from-purple-600 group-hover:to-blue-500 hover:text-white dark:text-white focus:ring-4 focus:outline-none focus:ring-blue-300 dark:focus:ring-blue-800">
                                        <span
                                            class="relative px-5 py-2.5 transition-all ease-in duration-75 bg-white dark:bg-gray-900 rounded-md group-hover:bg-opacity-0">
                                            Save
                                        </span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayoutForm>
</template>
<style type="text/css">
.tg  {border-collapse:collapse;border-spacing:0;}
.tg td{border-color:black;border-style:solid;border-width:1px;font-family:Arial, sans-serif;font-size:14px;
  overflow:hidden;padding:10px 5px;word-break:normal;}
.tg th{border-color:black;border-style:solid;border-width:1px;font-family:Arial, sans-serif;font-size:14px;
  font-weight:normal;overflow:hidden;padding:10px 5px;word-break:normal;}
.tg .tg-lboi{border-color:inherit;text-align:left;vertical-align:middle}
.tg .tg-9wq8{border-color:inherit;text-align:center;vertical-align:middle}
.tg .tg-ng7p{border-color:#330001;text-align:left;vertical-align:top}
.tg .tg-c3ow{border-color:inherit;text-align:center;vertical-align:top}
.tg .tg-kfkg{background-color:#dae8fc;border-color:inherit;text-align:center;vertical-align:top}
.tg .tg-8g55{border-color:#330001;text-align:center;vertical-align:top}
.tg .tg-0pky{border-color:inherit;text-align:left;vertical-align:top}
.tg .tg-mak0{background-color:#68cbd0;border-color:inherit;text-align:center;vertical-align:top}
.tg .tg-rleq{background-color:#68cbd0;border-color:inherit;text-align:left;vertical-align:top}
.tg .tg-ngei{background-color:#ffcb2f;border-color:inherit;text-align:center;vertical-align:top}
.tg .tg-ik58{background-color:#ffcb2f;border-color:inherit;text-align:left;vertical-align:top}
.tg .tg-x6qq{background-color:#dae8fc;border-color:inherit;text-align:left;vertical-align:top}
</style>