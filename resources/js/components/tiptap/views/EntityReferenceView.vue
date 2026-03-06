<script setup lang="ts">
import { NodeViewWrapper } from '@tiptap/vue-3';
import type { ReferenceType } from '../extensions/EntityReference';
import { computed } from 'vue';

const props = defineProps<{
    node: {
        attrs: {
            referenceType: ReferenceType;
            slug: string;
            label: string;
            url?: string | null;
        };
    };
}>();

const colorClass = computed(() => {
    switch (props.node.attrs.referenceType) {
        case 'indexTooltip':
            return 'bg-purple-100 text-purple-800 border-purple-300';
        case 'sectionLink':
            return 'bg-blue-100 text-blue-800 border-blue-300';
        case 'pageLink':
            return 'bg-green-100 text-green-800 border-green-300';
        case 'Link':
            return 'bg-gray-100 text-gray-800 border-gray-300';
        default:
            return 'bg-gray-100 text-gray-800 border-gray-300';
    }
});

const typeLabel = computed(() => {
    switch (props.node.attrs.referenceType) {
        case 'indexTooltip':
            return 'Tooltip';
        case 'sectionLink':
            return 'Section';
        case 'pageLink':
            return 'Page';
        case 'Link':
            return 'Link';
        default:
            return 'Ref';
    }
});
</script>

<template>
    <NodeViewWrapper as="span" class="inline">
        <span
            class="inline-flex items-center rounded-sm border px-1 py-0.5 text-xs font-medium"
            :class="colorClass"
        >
            <span class="mr-1 opacity-60">{{ typeLabel }}:</span>
            {{ node.attrs.label }}
        </span>
    </NodeViewWrapper>
</template>
