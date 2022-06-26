<template>
    <div class="text-right">
        <Component
            :is="link.url ? 'Link' : 'span'"
            v-for="(link, linkIndex) in links"
            :href="link.url"
            v-html="link.label"
            class="bg-white shadow py-1 px-2 md:py-3 md:px-4"
            :class="{
                'text-gray-500': !link.url,
                'font-bold bg-gray-100': link.active,
                'rounded-l': linkIndex === 0,
                'rounded-r': linkIndex === links.length - 1,
            }"
        />
    </div>
</template>

<script>
import { Link } from "@inertiajs/inertia-vue3";

export default {
    components: { Link },
    props: {
        links: Array,
    },
};
</script>
