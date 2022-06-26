<script setup>
import { ref, onMounted, computed } from "vue";
import Toastify from "toastify-js";
import autoResizeTextarea from "@/Helpers/autoResizeTextarea";
import PendingIcon from "./PendingIcon.vue";
import ProcessingIcon from "./ProcessingIcon.vue";
import ErrorIcon from "./ErrorIcon.vue";
import { Inertia } from "@inertiajs/inertia";
import DeleteIcon from "./DeleteIcon.vue";
import LinkIcon from "./LinkIcon.vue";

const textarea = ref(null);

const props = defineProps({
    video: Object,
});

onMounted(() => {
    autoResizeTextarea(textarea.value);
});

function progressBarWidth(video) {
    const completedJobs =
        video?.incomplete_batch?.total_jobs -
        video?.incomplete_batch.pending_jobs;
    return (completedJobs / video?.incomplete_batch?.total_jobs) * 100 + "%";
}

function onClick(e) {
    if (e.target.tagName === "TEXTAREA" || e.target.tagName === "BUTTON") {
        e.preventDefault();
        e.stopPropagation();
    } else {
        Inertia.visit(`/videos/${props.video.hashed_id}`);
    }
}

function saveTitle(e) {
    Inertia.patch(
        `/videos/${props.video.hashed_id}`,
        {
            title: e.target.value,
        },
        {
            preserveScroll: true,
            onError() {
                Toastify({
                    text: "Something went wrong",
                    duration: 2000,
                    gravity: "bottom",
                    position: "right",
                }).showToast();
            },
        }
    );
}

function deleteVideo() {
    const confirmed = confirm("Are you sure?");

    if (confirmed) {
        Inertia.delete(`/videos/${props.video.hashed_id}`, {
            preserveScroll: true,
        });
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

function blurInput(e) {
    e.target.blur();
}
</script>
<template>
    <div
        :href="`/videos/${video.hashed_id}`"
        class="block bg-white overflow-hidden shadow-sm rounded-lg relative cursor-pointer"
        :disabled="video.status !== 'complete'"
        @click="onClick"
    >
        <div
            v-if="video.incomplete_batch"
            class="h-1 transition bg-blue-500 absolute left-0"
            :style="{ width: progressBarWidth(video) }"
        ></div>
        <img
            class="rounded-t-lg aspect-video w-full"
            :src="video.thumbnail_url"
        />
        <div class="p-3 bg-white flex justify-between">
            <textarea
                ref="textarea"
                rows="1"
                class="flex-1 outline-none resize-none border border-transparent focus:border-gray-300 h-6 -m-1 p-1"
                @blur="saveTitle"
                @keydown.enter.prevent="blurInput"
                >{{ video.title }}</textarea
            >
            <div class="flex items-center justify-center gap-3">
                <button
                    class="outline-none text-gray-500 hover:text-gray-600"
                    @click="copyLink"
                >
                    <LinkIcon class="w-5 h-5"></LinkIcon>
                </button>
                <button
                    class="outline-none text-red-500 hover:text-red-600"
                    @click="deleteVideo"
                >
                    <DeleteIcon class="w-5 h-5" />
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
