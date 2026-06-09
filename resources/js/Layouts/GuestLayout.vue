<script setup>
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import { Link } from "@inertiajs/vue3";

const yearxyz = new Date().getFullYear();
</script>

<template>

    <main class="mt-0 transition-all duration-200 ease-in-out">
        <div
            class="pb-40 pt-8 m-4 min-h-50-screen items-start rounded-xl p-0 relative overflow-hidden flex bg-cover bg-center bg-[url('/IJZ_2751.jpg')]">
            <span
                class="absolute top-0 left-0 w-full h-full bg-center bg-cover opacity-60 bg-gradient-to-tl from-zinc-900 to-zinc-900"></span>
            <div class="container z-50">
                <div
                    class="flex items-center justify-center gap-8 w-full p-0 px-0 md:px-6 lg:px-6 mx-auto flex-wrap-inherit">
                    <img src="/images/logoppa.png" alt="message placeholder image"
                        class="size-10 md:size-24 lg:size-24" />
                    <img src="/images/POLICE_LOGO2.png" alt="message placeholder image"
                        class="size-16 md:size-40 lg:size-40" />

                </div>
                <div class="flex flex-wrap justify-center -mx-3">
                    <div class="w-full max-w-full px-5 mx-auto text-center shrink-0 lg:flex-0 lg:w-5/12">
                        <h1 class="mt-0 mb-2 text-white hidden md:block lg:block">PORTAL ICT CENTER</h1>
                        <h1 class="mt-0 mb-2 text-white md:hidden lg:hidden" style="font-size: 1.6em !important">PORTAL ICT CENTER</h1>
                        <p class="text-white ">PT. Putra Perkasa Abadi - All Jobsite</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="container">
            <div class="flex flex-wrap justify-center -mx-3 -mt-64 lg:-mt-36 md:-mt-56">
                <div class="w-full max-w-full px-6 mx-auto shrink-0 md:flex-0 md:w-7/12 lg:w-5/12 xl:w-4/12">
                    <div
                        class="relative z-0 flex flex-col min-w-0 break-words bg-white border-0 shadow-xl dark:bg-slate-850 dark:shadow-dark-xl rounded-2xl bg-clip-border">
                        <div class="border-black/12.5 rounded-t-2xl border-b-0 border-solid p-6 text-center">
                            <h3>Sign in</h3>
                        </div>
                        <div class="flex-auto p-6 pt-0 text-center">
                            <slot />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>


    <footer class="py-12">
        <div class="container">
            <div class="flex flex-wrap -mx-3"></div>
            <div class="flex flex-wrap -mx-3">
                <div class="w-8/12 max-w-full px-3 mx-auto mt-1 text-center flex-0">
                    <p class="mb-0 text-slate-400 text-sm md:text-base lg:text-base">
                        Copyright ©
                        {{ yearxyz }}
                        by ICT PPA AMM
                    </p>
                </div>
            </div>
        </div>
    </footer>
</template>
