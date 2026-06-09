<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <nav>
                <!-- breadcrumb -->
                <ol
                    class="flex flex-wrap pt-1 mr-12 bg-transparent rounded-lg sm:mr-16"
                >
                    <li class="text-sm leading-normal">
                        <a class="text-white opacity-50" href="javascript:;"
                            >Pages</a
                        >
                    </li>
                    <li
                        class="text-sm pl-2 capitalize leading-normal text-white before:float-left before:pr-2 before:text-white before:content-['/']"
                        aria-current="page"
                    >
                        Dashboard
                    </li>
                </ol>
                <h6
                    v-if="$page.props.auth.user.role === 'ict_developer'"
                    class="mb-0 font-bold text-white capitalize"
                >
                    Developer Dashboard
                </h6>
                <h6
                    v-if="$page.props.auth.user.role === 'ict_group_leader'"
                    class="mb-0 font-bold text-white capitalize"
                >
                    Group Leader Dashboard
                </h6>
                <h6
                    v-if="$page.props.auth.user.role === 'ict_section'"
                    class="mb-0 font-bold text-white capitalize"
                >
                    Section Dashboard
                </h6>
                <h6
                    v-if="$page.props.auth.user.role === 'ict_technician'"
                    class="mb-0 font-bold text-white capitalize"
                >
                    Ttechnician Dashboard
                </h6>
                <h6
                    v-if="$page.props.auth.user.role === 'ict_admin'"
                    class="mb-0 font-bold text-white capitalize"
                >
                    Admin Dashboard
                </h6>
            </nav>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">You're logged in! {{ $page.props.auth.user.name }}</div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>