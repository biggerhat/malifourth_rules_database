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
    <Link :href="route('rules.section.view', props.slug)" class="text-primary underline decoration-primary/40 hover:decoration-primary">
        {{ props.text }}
    </Link>
</template>
