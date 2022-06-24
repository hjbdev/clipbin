<script setup>
import BreezeAuthenticatedLayout from '@/Layouts/Authenticated.vue';
import { Head, Link } from '@inertiajs/inertia-vue3';

defineProps({
    videos: Array
})
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
                <Link v-for="video in videos" :href="`/videos/${video.id}`" class="block bg-white overflow-hidden shadow-sm sm:rounded-lg" :disabled="video.status !== 'complete'">
                    <img class="sm:rounded-t-lg aspect-video" :src="video.thumbnail_url">
                    <div class="p-6 bg-white border-b border-gray-200 flex justify-between">
                        <div>
                            {{ video.title }}
                        </div>
                        <div>
                            {{ video.status }}
                        </div>
                    </div>
                </Link>
                <div v-if="videos.length === 0" class="md:col-span-3 lg:col-span-4 text-center text-gray-700">
                    No videos yet!
                </div>
            </div>
        </div>
    </BreezeAuthenticatedLayout>
</template>
