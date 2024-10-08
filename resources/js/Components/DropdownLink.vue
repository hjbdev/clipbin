<script setup>
import { Link } from "@inertiajs/vue3";

defineProps({
    href: String,
});
</script>

<template>
    <component
        :is="href ? Link : 'div'"
        :href="href"
        class="cursor-pointer block w-full px-4 py-2 text-left text-sm leading-5 text-zinc-200 hover:bg-zinc-700 focus:outline-none focus:bg-zinc-600 transition duration-150 ease-in-out"
    >
        <slot />
    </component>
</template>
