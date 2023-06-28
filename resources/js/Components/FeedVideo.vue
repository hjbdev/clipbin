<script setup>
import { ref, onMounted, computed } from "vue";
import Toastify from "toastify-js";
import PendingIcon from "./PendingIcon.vue";
import ProcessingIcon from "./ProcessingIcon.vue";
import ErrorIcon from "./ErrorIcon.vue";
import { router } from "@inertiajs/vue3";
import LinkIcon from "./LinkIcon.vue";
import Plyr from "plyr";

const props = defineProps({
    video: Object,
});

onMounted(() => {
    if (props.video.status === "complete") {
        const player = new Plyr(`#player-${props.video?.hashed_id}`);

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

function onClick(e) {
    console.log("click", e);
    if (
        e.target.tagName.toLowerCase() === "textarea" ||
        e.target.tagName.toLowerCase() === "button" ||
        e.target.tagName.toLowerCase() === "svg"
    ) {
        e.preventDefault();
        e.stopPropagation();
    } else {
        router.visit(`/videos/${props.video.hashed_id}`);
    }
}

const videoUrl = computed(() => {
    return `${window.location.origin}/videos/${props.video.hashed_id}`;
});

function copyLink() {
    navigator.clipboard.writeText(videoUrl.value);

    Toastify({
        text: "Link copied to clipboard",
        duration: 2000,
        gravity: "bottom",
        position: "right",
    }).showToast();
}
</script>
<template>
    <div
        :href="`/videos/${video.hashed_id}`"
        class="block bg-white overflow-hidden shadow-sm rounded-lg relative"
    >
        <div class="p-3 flex justify-between gap-3">
            <div>
                <strong>{{ video.creator?.name }}</strong> uploaded
            </div>
            <div>
                {{ video.created_at_ago }}
            </div>
        </div>
        <template v-if="video.status === 'complete'">
            <video
                :id="`player-${video.hashed_id}`"
                playsinline
                controls
                :data-poster="video.thumbnail_url"
                class="w-full aspect-video"
            ></video>
        </template>
        <img v-else class="aspect-video w-full" :src="video.thumbnail_url" />
        <div
            class="p-3 bg-white flex justify-between cursor-pointer"
            @click="onClick"
        >
            <div
                class="flex-1 outline-none resize-none border border-transparent focus:border-gray-300 h-6"
            >
                {{ video.title }}
            </div>
            <div class="flex items-center justify-center gap-3">
                <button
                    class="outline-none text-gray-500 hover:text-gray-600"
                    @click="copyLink"
                >
                    <LinkIcon class="w-5 h-5"></LinkIcon>
                </button>
                <template v-if="video.status !== 'complete'">
                    <PendingIcon
                        v-if="video.status === 'pending'"
                        class="w-5 h-5 text-gray-500"
                    />
                    <ProcessingIcon
                        v-if="video.status === 'processing'"
                        class="animate-spin w-5 h-5 text-gray-500"
                    />
                    <ErrorIcon
                        v-if="video.status === 'error'"
                        class="w-5 h-5 text-red-600"
                    />
                </template>
            </div>
        </div>
    </div>
</template>
