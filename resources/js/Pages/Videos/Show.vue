<script setup>
import BreezeAuthenticatedLayout from "@/Layouts/Authenticated.vue";
import { Head, router } from "@inertiajs/vue3";
import Plyr from "plyr";
import { onMounted, onUnmounted } from "vue";

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
        setTimeout(router.reload());
    }
});

onUnmounted(() => {
    player.destroy();
    player = null;
});
</script>

<template>
    <Head :title="video.title" />

    <BreezeAuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ video.title }}
            </h2>
        </template>

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
    </BreezeAuthenticatedLayout>
</template>
