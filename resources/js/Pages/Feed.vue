<script setup>
import BreezeAuthenticatedLayout from "@/Layouts/Authenticated.vue";
import { Head, router } from "@inertiajs/vue3";
import Pagination from "../Components/Pagination.vue";
import FeedVideo from "@/Components/FeedVideo.vue";

const props = defineProps({
    videos: Object,
    title: String
});

function checkForIncompleteVideos() {
    if (props.videos.data.find((video) => video.status !== "complete" && video.status !== "error")) {
        setTimeout(() => {
            router.reload({ 
                only: ["videos"],
                onSuccess() {
                    checkForIncompleteVideos();
                }
            });
        }, 5000);
    }
}

checkForIncompleteVideos();
</script>

<template>
    <Head :title="title" />

    <BreezeAuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ title }}
            </h2>
        </template>

        <div class="py-12">
            <div
                class="max-w-4xl mx-auto px-6 lg:px-8 flex flex-col gap-6"
            >
                <FeedVideo
                    v-for="video in videos.data"
                    :video="video"
                    class="w-full"
                ></FeedVideo>
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
    </BreezeAuthenticatedLayout>
</template>
