<script setup lang="ts">
import ParsedContent from "@/components/ParsedContent.vue";
import { Share2 } from "lucide-vue-next";

const props =defineProps({
    title: {
        type: String,
        required: false,
        default() {
            return '';
        }
    },
    content: {
        type: [Object, Array, String],
        required: false,
        default() {
            return '';
        }
    },
    published_at: {
        type: String,
        required: false,
        default() {
            return '';
        }
    },
    published_by: {
        type: String,
        required: false,
        default() {
            return '';
        }
    }
});

</script>

<template>
    <div>
        <div class="flex justify-between">
            <div class="border-b text-xl mb-2">{{ props.title }}</div>
            <div><Share2 class="w-4" /></div>
        </div>

        <ParsedContent :content="props.content" />
    </div>
</template>
