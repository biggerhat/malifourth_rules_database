<script setup lang="ts">
import { defineProps } from "vue";
import CrossReferencePreview from "@/components/CrossReferencePreview.vue";

const props = defineProps({
    slug: String,
    text: {
        type: [Object,Array,String],
        required: false,
        default() {
            return '';
        }
    },
    title: {
        type: String,
        required: false,
        default() {
            return '';
        }
    },
    content: {
        type: [Array, Object, String],
        required: false,
        default() {
            return null;
        }
    }
});

</script>

<template>
    <CrossReferencePreview
        :href="route('rules.page.view', props.slug)"
        :text="props.text"
        :title="props.title"
        :content="props.content"
    />
</template>
