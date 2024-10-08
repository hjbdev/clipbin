<script setup>
import { Head, router } from "@inertiajs/vue3";
import { onMounted, onUnmounted } from "vue";
import Plyr from "plyr";
import AuthenticatedLayout from "@/Layouts/Authenticated.vue";

defineOptions({ layout: AuthenticatedLayout });

const props = defineProps({
    video: Object,
});

let player = null;

onMounted(() => {
    if (props.video.status === "complete") {
        player = new Plyr("#player");

        player.source = {
            type: "video",
            sources: props.video.conversions?.map((conversion) => ({
                src: conversion.url,
                type: "video/mp4",
                size: parseInt(conversion.name),
            })),
        };
    } else {
        setTimeout(router.reload({ only: ['video'] }));
    }
});

onUnmounted(() => {
    player.destroy();
    player = null;
});
</script>

<template>
    <Head :title="video.title" />

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <template v-if="video.status === 'complete'">
                <video
                    id="player"
                    playsinline
                    controls
                    :data-poster="video.thumbnail_url"
                    class="w-full"
                ></video>
            </template>
            <template v-else>
                <div class="text-center">Video Processing</div>
            </template>
        </div>
    </div>
</template>
