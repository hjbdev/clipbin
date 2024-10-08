<script setup>
import { Head, router, Link } from "@inertiajs/vue3";
import { onMounted, onUnmounted } from "vue";
import Plyr from "plyr";
import { UserCircleIcon } from "@heroicons/vue/20/solid";
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
        setTimeout(router.reload({ only: ["video"] }));
    }
});

onUnmounted(() => {
    player?.destroy();
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

                <h1 class="text-2xl md:text-4xl font-bold mt-2">{{ video.title }}</h1>
                <p class="mt-1 opacity-50 text-sm">Uploaded {{ video.created_at_ago }}<template v-if="video.originally_created_at_ago"> &bull; Originally created {{ video.originally_created_at_ago }}</template></p>

                <Link v-if="video.creator" :href="`/users/${video.creator?.id}`" class="mt-1 flex items-center gap-1.5">
                    <UserCircleIcon class="w-6 h-6 inline-block text-purple-500" />
                    <div class="text-sm">{{ video.creator?.name }}</div>
                </Link>
            </template>
            <template v-else>
                <div class="text-center">Video Processing</div>
            </template>
        </div>
    </div>
</template>
