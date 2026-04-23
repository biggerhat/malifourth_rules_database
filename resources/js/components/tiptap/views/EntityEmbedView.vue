<script setup lang="ts">
import { NodeViewWrapper } from '@tiptap/vue-3';
import type { EmbedType } from '../extensions/EntityEmbed';
import { computed } from 'vue';

const props = defineProps<{
    node: {
        attrs: {
            embedType: EmbedType;
            slug: string;
            title: string;
        };
    };
}>();

const colorClass = computed(() =>
    props.node.attrs.embedType === 'index'
        ? 'border-purple-300 bg-purple-50'
        : 'border-blue-300 bg-blue-50',
);

const typeLabel = computed(() =>
    props.node.attrs.embedType === 'index' ? 'Index' : 'Section',
);
</script>

<template>
    <NodeViewWrapper class="my-2">
        <div class="flex items-center rounded border-2 border-dashed p-3" :class="colorClass">
            <span class="text-sm font-semibold opacity-60">{{ typeLabel }}:</span>
            <span class="ml-2 text-sm font-medium">{{ node.attrs.title || node.attrs.slug }}</span>
        </div>
    </NodeViewWrapper>
</template>
