<script setup>
import { onMounted, onUnmounted } from 'vue';
import { Link } from '@inertiajs/inertia-vue3'
import PendingIcon from './PendingIcon.vue';
import ProcessingIcon from './ProcessingIcon.vue';

const props = defineProps({
    video: Object
})

const emit = defineEmits(['update']);

function progressBarWidth(video) {
    const completedJobs = video?.incomplete_batch?.total_jobs - video?.incomplete_batch.pending_jobs;
    return completedJobs / video?.incomplete_batch?.total_jobs * 100 + '%'
}

onMounted(() => {
    window.Echo.channel(`videos.${props.video.hashed_id}`).listen('App\\Events\\VideoUpdated', (data) => {
        emit('updated', data.video);
    });
})

onUnmounted(() => {
    window.Echo.leave(`videos.${props.video.hashed_id}`);
})
</script>
<template>
    <Link
        :href="`/videos/${video.hashed_id}`"
        class="block bg-white overflow-hidden shadow-sm sm:rounded-lg relative"
        :disabled="video.status !== 'complete'"
    >
        <div
            v-if="video.incomplete_batch"
            class="h-1 transition bg-blue-500 absolute left-0"
            :style="{ width: progressBarWidth(video) }"
        ></div>
        <img class="sm:rounded-t-lg aspect-video" :src="video.thumbnail_url" />
        <div class="p-6 bg-white border-b border-gray-200 flex justify-between">
            <div>
                {{ video.title }}
            </div>
            <div v-if="video.status !== 'complete'">
                <PendingIcon
                    v-if="video.status === 'pending'"
                    class="w-5 h-5 text-gray-500"
                />
                <ProcessingIcon
                    v-if="video.status === 'processing'"
                    class="animate-spin w-5 h-5 text-gray-500"
                />
            </div>
        </div>
    </Link>
</template>
