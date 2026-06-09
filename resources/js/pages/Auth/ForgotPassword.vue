<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link, Head, useForm } from '@inertiajs/vue3';

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <GuestLayout>

        <Head title="Forgot Password" />

        <div
            class="flex flex-col w-full max-w-full px-3 mx-auto lg:mx-0 shrink-0 md:flex-0 md:w-7/12 lg:w-5/12 xl:w-4/12">

            <div class="mb-4 text-sm text-gray-600">
                <Link :href="route('login')"
                    class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <i class="fa fa-arrow-circle-left mr-2"> </i> BACK
                </Link>
            </div>
            <div
                class="relative flex flex-col min-w-0 break-words bg-transparent border-0 shadow-none lg:py4 dark:bg-gray-950 rounded-2xl bg-clip-border">
                <div class="p-6 pb-0 mb-0">
                    <h4 class="font-bold">Forget Password</h4>
                    <p class="mb-0">Forgot your password? No problem. Just let us know your email address and we will
                        email you a password reset
                        link that will allow you to choose a new one.</p>
                </div>
                <div class="flex-auto p-6">

                    <div class="mb-4 text-sm text-gray-600">

                        <div v-if="status" class="mb-4 font-medium text-sm text-green-600">
                            {{ status }}
                        </div>

                        <form @submit.prevent="submit">
                            <div>
                                <InputLabel for="nrp" value="NRP" />

                                <TextInput id="nrp" type="nrp" class="mt-1 block w-full" v-model="form.nrp" required
                                    autofocus placeholder="masukan nrp anda" autocomplete="username" />

                                <InputError class="mt-2" :message="form.errors.nrp" />
                            </div>

                            <div class="flex items-center justify-end mt-4">
                                <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                    NRP Password Reset Link
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>


    </GuestLayout>
</template>
