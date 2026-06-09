<script setup>
import Checkbox from "@/Components/Checkbox.vue";
import GuestLayout from "@/Layouts/GuestLayout.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import { ref, onMounted, defineProps } from "vue";
import Swal from "sweetalert2";
import axios from "axios";
import Cookies from "js-cookie";

const props = defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
    errorMessage: {
        type: String,
    },
    errorLoginUnamePasswr: {
        type: String,
    },
    errorLoginUnamePassnf: {
        type: String,
    },
    errorLoginKoneksi: {
        type: String,
    },
    login: {
        type: String,
    },
});

const form = useForm({
    nrp: "",
    password: "",
    local: "Y",
});

const isLocal = ref(false);
const message = ref("");
const messageClass = ref("");

const submit = () => {
    form.post(route("login"), {
        onFinish: () => form.reset("password"),
    });
};

onMounted(() => {
    console.log(props.login);
    console.log(isLocal);
    if (props.errorMessage) {
        Swal.fire({
            position: "top-end",
            icon: "warning",
            title: "Terlalu Banyak Melakukan Percobaan! Silahkan Refresh Halaman.",
            showConfirmButton: false,
            timer: 3600,
        });
    }
});
</script>

<template>
    <GuestLayout>
        <Head title="Log in" />

        <div v-if="props.status" class="font-medium text-sm text-green-600">
            {{ props.status }}
        </div>

        <div class="flex-auto p-12 pt-0 pb-6 text-center">
            <!-- <div class="mb-6 text-center text-slate-500">
                <small>Or sign in with credentials</small>
            </div> -->
            <form class="text-left" @submit.prevent="submit">
                <div class="mb-4">
                    <InputLabel for="nrp" value="NRP" />

                    <TextInput
                        id="nrp"
                        type="nrp"
                        class="mt-1 block w-full"
                        style="color: black !important"
                        v-model="form.nrp"
                        required
                        autofocus
                        autocomplete="username"
                        placeholder="nrp"
                    />
                </div>
                <div class="mb-4">
                    <InputLabel for="password" value="Password" />

                    <TextInput
                        id="password"
                        type="password"
                        class="mt-1 block w-full"
                        style="color: black !important"
                        v-model="form.password"
                        required
                        autocomplete="current-password"
                        placeholder="password"
                    />

                    <!-- <InputError
                                class="mt-2"
                                :message="form.errors.password"
                            /> -->

                    <p
                        v-if="message"
                        :class="messageClass"
                        class="mt-4 text-center"
                    >
                        {{ message }}
                    </p>
                    <p v-if="errorMessage" class="text-red-500 mt-3">
                        {{ errorMessage }}
                    </p>
                    <p v-if="errorLoginUnamePasswr" class="text-red-500 mt-3">
                        {{ errorLoginUnamePasswr }}
                    </p>
                    <p v-if="errorLoginUnamePassnf" class="text-red-500 mt-3">
                        {{ errorLoginUnamePassnf }}
                    </p>
                    <p v-if="errorLoginKoneksi" class="text-red-500 mt-3">
                        {{ errorLoginKoneksi }}
                    </p>
                </div>
                <!-- <div class="min-h-6 mb-0.5 block pl-12 text-left">
                    <input
                        id="rememberMe"
                        class="mt-0.5 rounded-10 duration-250 ease-in-out after:rounded-circle after:shadow-2xl after:duration-250 checked:after:translate-x-5.3 h-5 relative float-left -ml-12 w-10 cursor-pointer appearance-none border border-solid border-gray-200 bg-slate-800/10 bg-none bg-contain bg-left bg-no-repeat align-top transition-all after:absolute after:top-px after:h-4 after:w-4 after:translate-x-px after:bg-white after:content-[''] checked:border-blue-500/95 checked:bg-blue-500/95 checked:bg-none checked:bg-right"
                        type="checkbox"
                        v-model="isLocal"
                        @change="form.local = isLocal ? 'Y' : ''"
                    />
                    <label
                        class="ml-1 text-sm font-normal cursor-pointer select-none text-slate-700"
                        for="rememberMe"
                        >Non SS6</label
                    >
                </div> -->
                <div class="text-center mb-5">
                    <PrimaryButton
                        class="inline-block w-full px-5 py-2.5 mt-6 mb-2 text-sm font-bold text-center text-white align-middle transition-all ease-in border-0 rounded-lg shadow-md cursor-pointer active:-translate-y-px active:hover:text-white active:text-black hover:-translate-y-px hover:shadow-xs leading-normal tracking-tight-rem bg-150 bg-x-25 bg-slate-700 hover:border-blue-500 hover:bg-slate-700 hover:text-white"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                    >
                        Sign in
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </GuestLayout>
</template>
