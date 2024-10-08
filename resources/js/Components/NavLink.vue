<script setup>
import { computed } from "vue";
import { Link } from "@inertiajs/vue3";

const props = defineProps(["href", "active"]);

const classes = computed(() =>
    props.active
        ? "inline-flex items-center px-1 pt-1 border-b-2 border-purple-700 text-sm font-medium leading-5 text-zinc-100 focus:outline-none focus:border-purple-800 transition  duration-150 ease-in-out"
        : "inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-zinc-400 hover:text-zinc-200 hover:border-zinc-300 focus:outline-none focus:text-zinc-200 focus:border-zinc-300 transition duration-150 ease-in-out"
);
</script>

<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>
