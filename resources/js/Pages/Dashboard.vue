<script setup>
import BreezeAuthenticatedLayout from '@/Layouts/Authenticated.vue';
import { Inertia } from '@inertiajs/inertia';
import { Head } from '@inertiajs/inertia-vue3';
import VideoItem from '../Components/VideoItem.vue';

defineProps({
    videos: Array
})

function updateVideo(index, video) {
    Inertia.reload({only: ['videos']});
}
</script>

<template>
    <Head title="Videos" />

    <BreezeAuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Your Videos
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 grid md:grid-cols-3 lg:grid-cols-4 gap-6">
               <VideoItem v-for="(video, videoIndex) in videos" :video="video" @updated="updateVideo(index, $event)"></VideoItem>
                <div v-if="videos.length === 0" class="md:col-span-3 lg:col-span-4 text-center text-gray-700">
                    No videos yet!
                </div>
            </div>
        </div>
    </BreezeAuthenticatedLayout>
</template>
