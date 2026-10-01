<script setup lang="ts">
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger
} from '@/components/ui/tooltip'
import { computed, defineProps } from "vue";
import ParsedContent from "@/components/ParsedContent.vue";

const props = defineProps({
    href: {
        type: String,
        required: true
    },
    text: {
        type: [Object, Array, String],
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
    },
    left_column: {
        type: [Array, Object, String],
        required: false,
        default() {
            return null;
        }
    },
    right_column: {
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

const previewContent = computed(() => {
    if (Array.isArray(props.left_column) && props.left_column.length > 0) {
        return props.left_column;
    }
    if (Array.isArray(props.content) && props.content.length > 0) {
        return props.content;
    }

    return null;
});

const hasPreview = computed(() => !!props.title || !!previewContent.value);
</script>

<template>
    <TooltipProvider v-if="hasPreview">
        <Tooltip>
            <TooltipTrigger as-child>
                <Link :href="props.href" class="text-primary underline decoration-primary/40 hover:decoration-primary">
                    <component :is="textComponent" />
                </Link>
            </TooltipTrigger>
            <TooltipContent class="max-w-80 text-primary bg-background border-primary border rounded p-3 text-left">
                <div v-if="props.title" class="font-semibold mb-1 border-b border-primary/20 pb-1" v-html="props.title"></div>
                <div v-if="previewContent" class="preview-fade relative max-h-40 overflow-hidden text-sm">
                    <ParsedContent :content="previewContent" />
                </div>
            </TooltipContent>
        </Tooltip>
    </TooltipProvider>
    <Link v-else :href="props.href" class="text-primary underline decoration-primary/40 hover:decoration-primary">
        <component :is="textComponent" />
    </Link>
</template>

<style scoped>
.preview-fade {
    mask-image: linear-gradient(to bottom, black 65%, transparent 100%);
    -webkit-mask-image: linear-gradient(to bottom, black 65%, transparent 100%);
}
</style>
