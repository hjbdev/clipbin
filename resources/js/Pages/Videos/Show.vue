<script setup>
import BreezeAuthenticatedLayout from "@/Layouts/Authenticated.vue";
import { Head } from "@inertiajs/inertia-vue3";
import Plyr from "plyr";
import { onMounted } from "vue";

const props = defineProps({
    video: Object,
});

onMounted(() => {
    const player = new Plyr("#player");

    player.source = {
        type: 'video',
        sources: props.video.conversions?.map((conversion) => ({
            src: conversion.url,
            type: 'video/mp4',
            size: parseInt(conversion.name)
        }))
    }
});
</script>

<template>
    <Head title="Videos" />

    <BreezeAuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Video
            </h2>
        </template>

        <div class="py-12">
            <div
                class="max-w-7xl mx-auto sm:px-6 lg:px-8"
            >
                <video
                    id="player"
                    playsinline
                    controls
                    :data-poster="video.thumbnail_url"
                    class="w-full"
                >
                </video>
            </div>
        </div>
    </BreezeAuthenticatedLayout>
</template>
