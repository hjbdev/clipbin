<template>
    <div>
        <PrimaryButton ref="button" :disabled="uploading">Upload{{ uploading ? `ing (${Math.round(progress)}%)` : '' }}</PrimaryButton>
        <input
            ref="fileInput"
            type="file"
            class="hidden"
            @change="fileSelected"
        />
    </div>
</template>
<script setup>
import { PrimaryButton } from "hjb-ui";
import { onMounted, ref } from "vue";
import { Inertia } from '@inertiajs/inertia';
import Resumable from "resumablejs";
import { usePage } from "@inertiajs/inertia-vue3";

const fileInput = ref(null);
const button = ref(null);

function uploadVideo() {
    fileInput.value.click();
}

const uploading = ref(false);
const progress = ref(0);

onMounted(() => {
    const resumable = new Resumable({
        // Use chunk size that is smaller than your maximum limit due a resumable issue
        // https://github.com/23/resumable.js/issues/51
        chunkSize: 1 * 1024 * 1024, // 1MB
        maxFiles: 1,
        simultaneousUploads: 3,
        testChunks: false,
        throttleProgressCallbacks: 1,
        // Get the url from data-url tag
        target: "/videos",
        // Append token to the request - required for web routes
        query: {
            _token: usePage().props.value.csrf
        },
    });

    if (!resumable.support) {
        alert("Your browser doesnt support HTML5 file uploads");
        return;
    }

    resumable.assignBrowse(button.value.$el);

    // Handle file add event
    resumable.on("fileAdded", function (file) {
        uploading.value = true;
        progress.value = file.progress() * 100;
        // Show progress pabr
        resumable.upload();
    });
    resumable.on("fileSuccess", function (file, message) {
        uploading.value = false;
        Inertia.reload({ only: ['videos'] })
    });
    resumable.on("fileError", function (file, message) {
        uploading.value = false;
        alert('error while uploading file');
    });
    resumable.on("fileProgress", function (file) {
        progress.value = file.progress() * 100;
    });
});
</script>
