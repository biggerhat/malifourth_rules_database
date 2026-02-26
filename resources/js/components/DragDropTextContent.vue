<script setup lang="ts">
import { COMPONENT_MAP, isSymbolTag } from '@/lib/content-tags';

defineOptions({ name: 'DragDropTextContent' });

const props = defineProps({
    content: {
        type: [Object, Array, String],
        required: false,
    },
    root: {
        type: Boolean,
        default: false,
    },
});

const replaceNewline = (text) => {
    return text.replace(/\n/g, '');
};
</script>

<template>
    <component :is="root ? 'div' : 'span'" :class="root ? 'm-2 leading-8' : undefined">
        <template v-for="(element, idx) in content" :key="idx">
            <!-- Formatting with children (tree segments) -->
            <strong v-if="element.type === 'strong' && element.children">
                <DragDropTextContent :content="element.children" />
            </strong>
            <i v-else-if="element.type === 'i' && element.children">
                <DragDropTextContent :content="element.children" />
            </i>
            <u v-else-if="element.type === 'u' && element.children">
                <DragDropTextContent :content="element.children" />
            </u>
            <span v-else-if="['xl', 'lg', 'sm', 'xs'].includes(element.type) && element.children" :class="`text-${element.type}`">
                <DragDropTextContent :content="element.children" />
            </span>

            <!-- Backward compat: flat formatting segments with string content -->
            <strong v-else-if="element.type === 'strong' && typeof element.content === 'string'" class="bg-secondary p-1 rounded inline">
                {{ ' ' + element.content }}
            </strong>
            <i v-else-if="element.type === 'i' && typeof element.content === 'string'" class="bg-secondary p-1 rounded inline">
                {{ ' ' + element.content }}
            </i>
            <u v-else-if="element.type === 'u' && typeof element.content === 'string'" class="bg-secondary p-1 rounded inline">
                {{ ' ' + element.content }}
            </u>

            <!-- Text -->
            <span v-else-if="element.type === 'text'" class="border-0">
                {{ ' ' + replaceNewline(element.content) }}
            </span>

            <!-- Line break -->
            <br v-else-if="element.type === 'br'" />

            <!-- Tooltip -->
            <div
                v-else-if="element.type === 'indexTooltip'"
                class="bg-secondary p-1 rounded inline"
            >
                {{ ' ' + element.content.text }} (Tooltip)
            </div>

            <!-- Links -->
            <div
                v-else-if="element.type === 'sectionLink' || element.type === 'pageLink' || element.type === 'Link'"
                class="bg-secondary p-1 rounded inline"
            >
                {{ ' ' + element.content.text }} (Link)
            </div>

            <!-- Symbols -->
            <div
                v-else-if="isSymbolTag(element.type)"
                class="bg-secondary p-1 rounded inline"
            >
                <component :is="COMPONENT_MAP[element.type]" class="h-4" />
            </div>

            <!-- Fallback -->
            <div
                v-else
                class="bg-secondary p-1 rounded inline"
            >
                {{ ' ' + element.content }}
            </div>
        </template>
    </component>
</template>
