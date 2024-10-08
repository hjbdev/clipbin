<script setup>
import AuthenticatedLayout from "@/Layouts/Authenticated.vue";
import { Head, router } from "@inertiajs/vue3";
import Pagination from "../Components/Pagination.vue";
import FeedVideo from "@/Components/FeedVideo.vue";

defineOptions({ layout: AuthenticatedLayout });

const props = defineProps({
    videos: Object,
    title: String,
    user: Object,
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
    <Head :title="title" />

    <div class="pb-12 pt-6">
        <div class="max-w-4xl mx-auto px-6 lg:px-8 flex flex-col gap-6">
            <div v-if="user">
                <h1 class="text-2xl md:text-4xl font-bold">
                    Videos uploaded by {{ user.name }}
                </h1>
            </div>
            <FeedVideo
                v-for="video in videos.data"
                :video="video"
                class="w-full"
            ></FeedVideo>
            <div
                v-if="videos.data?.length === 0"
                class="md:col-span-2 lg:col-span-3 text-center text-zinc-400"
            >
                No public videos uploaded yet!
            </div>
            <div
                v-if="videos.links?.length > 3"
                class="md:col-span-2 lg:col-span-3 text-right"
            >
                <Pagination :links="videos.links"></Pagination>
            </div>
        </div>
    </div>
</template>
