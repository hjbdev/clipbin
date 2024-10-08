<script setup>
import { ref, onMounted, computed, onUnmounted } from "vue";
import Toastify from "toastify-js";
import PendingIcon from "./PendingIcon.vue";
import ProcessingIcon from "./ProcessingIcon.vue";
import ErrorIcon from "./ErrorIcon.vue";
import { Link, router } from "@inertiajs/vue3";
import LinkIcon from "./LinkIcon.vue";
import Plyr from "plyr";
import { UserCircleIcon } from "@heroicons/vue/20/solid";

const props = defineProps({
    video: Object,
});

let player = null;

onMounted(() => {
    if (props.video.status === "complete") {
        player = new Plyr(`#player-${props.video?.hashed_id}`);

        player.source = {
            type: "video",
            poster: props.video.thumbnail_url,
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
    player.destroy();
    player = null;
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
        class="block bg-zinc-800 overflow-hidden shadow-sm rounded-lg relative"
    >
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
        <div class="p-3 bg-zinc-800 cursor-pointer" @click="onClick">
            <div class="flex justify-between">
                <div>
                    <h1 class="text-lg font-bold">
                        {{ video.title }}
                    </h1>
                    <p class="opacity-50 text-xs">
                        Uploaded {{ video.created_at_ago }}
                    </p>
                </div>
                <div class="flex flex-col items-end gap-1">
                    <Link
                        v-if="video.creator"
                        :href="`/users/${video.creator?.id}`"
                        class="flex items-center justify-end gap-1.5"
                    >
                        <div class="text-xs">{{ video.creator?.name }}</div>
                        <UserCircleIcon
                            class="w-4 h-4 inline-block text-purple-500"
                        />
                    </Link>
                    <div class="flex items-center justify-end gap-3 mt-1">
                        <button
                            class="outline-none text-zinc-500 hover:text-zinc-600"
                            @click="copyLink"
                        >
                            <LinkIcon class="w-5 h-5"></LinkIcon>
                        </button>
                        <template v-if="video.status !== 'complete'">
                            <PendingIcon
                                v-if="video.status === 'pending'"
                                class="w-5 h-5 text-zinc-500"
                            />
                            <ProcessingIcon
                                v-if="video.status === 'processing'"
                                class="animate-spin w-5 h-5 text-zinc-500"
                            />
                            <ErrorIcon
                                v-if="video.status === 'error'"
                                class="w-5 h-5 text-red-600"
                            />
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
