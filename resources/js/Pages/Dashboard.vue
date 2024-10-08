<script setup>
import AuthenticatedLayout from "@/Layouts/Authenticated.vue";
import { Head, router } from "@inertiajs/vue3";
import VideoItem from "../Components/VideoItem.vue";
import Pagination from "../Components/Pagination.vue";

defineOptions({ layout: AuthenticatedLayout });

const props = defineProps({
    videos: Object,
    title: String,
});

function checkForIncompleteVideos() {
    if (
        props.videos.data.find(
            (video) => video.status !== "complete" && video.status !== "error"
        )
    ) {
        setTimeout(() => {
            router.reload({
                only: ["videos"],
                onSuccess() {
                    checkForIncompleteVideos();
                },
            });
        }, 5000);
    }
}

checkForIncompleteVideos();
</script>

<template>
    <Head title="Videos" />

    <div class="py-12">
        <div
            class="max-w-7xl mx-auto px-6 lg:px-8 grid md:grid-cols-2 lg:grid-cols-3 gap-6"
        >
            <VideoItem v-for="video in videos.data" :video="video"></VideoItem>
            <div
                v-if="videos.length === 0"
                class="md:col-span-2 lg:col-span-3 text-center text-gray-700"
            >
                No videos yet!
            </div>
            <div class="md:col-span-2 lg:col-span-3 text-right">
                <Pagination :links="videos.links"></Pagination>
            </div>
        </div>
    </div>
</template>
