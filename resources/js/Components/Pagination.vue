<template>
    <div class="text-right">
        <Component
            :is="link.url ? 'Link' : 'span'"
            v-for="(link, linkIndex) in links"
            :href="link.url"
            v-html="link.label"
            class="bg-zinc-800 shadow py-1 px-2"
            :class="{
                'text-zinc-300': !link.url,
                'font-bold bg-zinc-600': link.active,
                'rounded-l': linkIndex === 0,
                'rounded-r': linkIndex === links.length - 1,
            }"
        />
    </div>
</template>

<script>
import { Link } from "@inertiajs/vue3";

export default {
    components: { Link },
    props: {
        links: Array,
    },
};
</script>
