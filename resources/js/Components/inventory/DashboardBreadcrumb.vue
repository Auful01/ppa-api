<script setup>
import { usePage, Link } from "@inertiajs/vue3";
import { computed, onMounted, defineProps } from "vue";

const pages = defineModel("pages", {
    type: String,
});

const subMenu = defineModel("subMenu", {
    type: String,
});

const mainMenu = defineModel("mainMenu", {
    type: String,
});

const cekData = onMounted(() => {
    console.log("cek Bolo");
    console.log(subMenu.value);
});
</script>

<template>
    <nav>
        <!-- breadcrumb -->
        <ol
            class="flex flex-wrap pt-1 mr-12 bg-transparent rounded-lg sm:mr-16"
        >
            <li class="text-sm leading-normal">
                <a class="text-dark opacity-50">{{ pages }}</a>
            </li>
            <Link
                class="text-sm pl-2 capitalize leading-normal text-dark before:float-left before:pr-2 before:text-dark before:content-['/']"
                aria-current="page"
            >
                {{ subMenu }}
            </Link>
        </ol>
        <h6 class="mb-0 font-bold text-dark capitalize">
            {{ mainMenu }}
        </h6>
    </nav>
</template>
