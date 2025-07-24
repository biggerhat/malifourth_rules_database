<script setup lang="ts">
import { onMounted, computed, defineProps } from "vue";

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
    <a :href="props.slug" target="_blank" class="text-blue-500 hover:underline">
        {{ props.text }}
    </a>
</template>
