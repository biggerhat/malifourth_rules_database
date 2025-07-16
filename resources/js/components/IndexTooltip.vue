<script setup lang="ts">
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger
} from '@/components/ui/tooltip'
import { onMounted, computed, defineProps } from "vue";
import ParsedContent from "@/components/ParsedContent.vue";

const props = defineProps({
    slug: String,
    text: {
        type: [Object,Array,String],
        required: false,
        default() {
            return '';
        }
    },
    type: {
        type: String,
        required: false,
        default() {
            return 'text';
        }
    },
    title: {
        type: String,
        required: false,
        default() {
            return '';
        }
    },
    image: {
        type: String,
        required: false,
        default() {
            return null;
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

const textComponent = computed(() => {
    return typeof props.text === 'string'
        ? { render: () => props.text }
        : props.text
});
</script>

<template>
    <TooltipProvider>
        <Tooltip>
            <TooltipTrigger class="border-b border-blue-500 text-blue-500">
                <slot>
                    <component :is="textComponent" />
                </slot>
            </TooltipTrigger>
            <TooltipContent class="max-w-80 text-primary bg-background border-primary border rounded">
                <span v-if="props.type === 'text'">
                    <ParsedContent :content="props.content" />
                </span>
                <span v-else-if="props.type === 'image'">
                    <img :src="props.image" :alt="props.title" class="" />
                </span>
            </TooltipContent>
        </Tooltip>
    </TooltipProvider>
</template>
