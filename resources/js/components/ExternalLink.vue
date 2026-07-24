<script setup lang="ts">
import { defineProps } from "vue";

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
    }
});

</script>

<template>
    <a :href="props.slug" target="_blank" class="text-primary underline decoration-primary/40 hover:decoration-primary">
        {{ props.text }}
    </a>
</template>
