<template>
    <div
        v-if="isOverDropZone"
        class="absolute inset-0 bg-black/70 backdrop-blur text-white z-50 pointer-events-none flex items-center justify-center text-2xl"
    >
        Upload a Video
    </div>
    <div class="flex items-center gap-2">
        <template v-if="uploadQueue.length">
            <div class="w-2 h-2 rounded-full bg-purple-600 animate-ping"></div>
            <div class="text-xs mr-4 opacity-50">
                Uploading {{ uploadQueue.length }} files...
            </div>
        </template>
        <!-- <PrimaryButton ref="button" :disabled="uploading"
            >Upload{{
                uploading ? `ing (${Math.round(progress)}%)` : ""
            }}</PrimaryButton
        >
        <input
            ref="fileInput"
            type="file"
            class="hidden"
            @change="fileSelected"
        /> -->
    </div>
</template>
<script setup>
import { PrimaryButton } from "hjb-ui";
import { onMounted, ref } from "vue";
import Resumable from "resumablejs";
import { usePage, router } from "@inertiajs/vue3";
import { useDropZone } from "@vueuse/core";

const dropZoneRef = ref(document.body);
let resumable = null;

function onDrop(files) {
    if (!resumable) {
        return;
    }

    resumable.addFiles(files);
}

const { isOverDropZone } = useDropZone(dropZoneRef, {
    onDrop,
    // specify the types of data to be received.
    dataTypes: ["video/mp4"],
    // control multi-file drop
    multiple: true,
    // whether to prevent default behavior for unhandled events
    preventDefaultForUnhandled: false,
});

const fileInput = ref(null);
const button = ref(null);

function uploadVideo() {
    fileInput.value.click();
}

const uploading = ref(false);
const progress = ref(0);

const uploadQueue = ref([]);

onMounted(() => {
    resumable = new Resumable({
        // Use chunk size that is smaller than your maximum limit due a resumable issue
        // https://github.com/23/resumable.js/issues/51
        chunkSize: 5 * 1024 * 1024, // 5MB
        maxFiles: 10,
        simultaneousUploads: 3,
        testChunks: false,
        throttleProgressCallbacks: 1,
        // Get the url from data-url tag
        target: "/videos",
        // Append token to the request - required for web routes
        query: {
            _token: usePage().props.csrf,
        },
        preprocess: function (chunk) {
            chunk.resumableObj.opts.query.originally_created_at = chunk.fileObj.file.lastModified;
            chunk.preprocessFinished();
        }
    });

    if (!resumable.support) {
        alert("Your browser doesnt support HTML5 file uploads");
        return;
    }

    // resumable.assignBrowse(button.value.$el);

    // Handle file add event
    resumable.on("fileAdded", function (file) {
        uploading.value = true;
        progress.value = file.progress() * 100;
        uploadQueue.value.push(file);
        resumable.upload();
    });
    resumable.on("fileSuccess", function (file, message) {
        console.log('file', file);
        uploading.value = false;
        uploadQueue.value.splice(uploadQueue.value.findIndex(f => f.uniqueIdentifier === file.uniqueIdentifier), 1);
        router.reload({ only: ["videos"] });
    });
    resumable.on("fileError", function (file, message) {
        uploading.value = false;
        uploadQueue.value.splice(uploadQueue.value.findIndex(f => f.uniqueIdentifier === file.uniqueIdentifier), 1);
        alert("error while uploading file");
        console.log(message);
    });
    resumable.on("fileProgress", function (file) {
        progress.value = file.progress() * 100;
    });
});
</script>
