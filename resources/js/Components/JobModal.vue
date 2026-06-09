<template>
    <div
        class="fixed top-0 left-0 w-full h-full overflow-x-hidden overflow-y-auto transition-opacity ease-linear z-sticky outline-0"
        :class="{
            hidden: !show,
            'opacity-0': !show,
            'opacity-100': show,
        }"
    >
        <div
            class="relative w-auto m-2 transition-transform duration-300 ease-out pointer-events-none sm:m-7 sm:max-w-125 sm:mx-auto lg:mt-48 -translate-y-13"
        >
            <div
                class="relative flex flex-col w-full bg-white border border-solid pointer-events-auto dark:bg-gray-950 bg-clip-padding border-black/20 rounded-xl outline-0"
            >
                <div
                    class="flex items-center justify-between p-4 border-b border-solid shrink-0 border-slate-100 rounded-t-xl"
                >
                    <h5 class="mb-0 leading-normal dark:text-white">
                        {{ title }}
                    </h5>
                    <button
                        type="button"
                        @click="$emit('close')"
                        class="fa fa-close w-4 h-4 ml-auto box-content p-2 text-black dark:text-white border-0 rounded-1.5 opacity-50 cursor-pointer -m-2 hover:opacity-100"
                    ></button>
                </div>

                <div class="relative flex-auto p-4">
                    <slot></slot>
                </div>

                <div
                    class="flex flex-wrap items-center justify-end p-3 border-t border-solid shrink-0 border-slate-100 rounded-b-xl"
                >
                    <slot name="footer"></slot>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    show: Boolean,
    title: String,
});

defineEmits(["close"]);
</script>
