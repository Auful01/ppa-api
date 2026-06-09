<template>
    <NavLink :href="url" v-bind="$attrs">
        <div class="icon icon-shape icon-sm text-center d-flex align-items-center justify-content-center">
            <slot name="icon"></slot>
        </div>
        <span class="nav-link-text" :class="'ms-1'">{{ navText }}</span>
    </NavLink>
</template>
<script>
import NavLink from "@/Components/NavLink.vue";

export default {
    name: "sidenav-item",
    components: {
        NavLink
    },
    props: {
        url: {
            type: String,
            required: true
        },
        navText: {
            type: String,
            required: true
        },
        active: {
            type: Boolean,
        },
    }
};
</script>