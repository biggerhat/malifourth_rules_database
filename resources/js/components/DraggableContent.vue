<script setup lang="ts">
import { defineProps, onMounted, ref, toRef } from 'vue';
import draggable from "vuedraggable";
import DragDropEditorContent from "@/components/DragDropEditorContent.vue";
import { Label } from "@/components/ui/label";
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from "@/components/ui/tooltip";
import { LetterTextIcon } from "lucide-vue-next";
import DragDropTextEditor from "@/components/DragDropTextEditor.vue";
import { ElementSheet } from "@/components/editor";
import { useFilteredEntities } from "@/composables/useFilteredEntities";
import { blocksToCustomTags, htmlToCustomTags } from "@/lib/tag-serializer";

const props = defineProps({
    content: {
        type: [Object, Array, String],
        required: true
    },
    label: {
        type: String,
        required: false,
        default() { return ''; }
    },
    indices: {
        type: [Object, Array],
        required: false,
        default() { return {}; }
    },
    pages: {
        type: [Object, Array],
        required: false,
        default() { return {}; }
    },
    sections: {
        type: [Object, Array],
        required: false,
        default() { return {}; }
    }
});

const parsedContent = ref([]);

onMounted(() => {
    const contentCopy = JSON.parse(JSON.stringify(props.content));
    parsedContent.value = mergeTextAndInline(contentCopy);
});

const emit = defineEmits([
    'update:contentOrder',
    'update:newContent'
]);

const contentChange = () => {
    emit('update:contentOrder', blocksToCustomTags(parsedContent.value));
};

const updateElementContent = (newContent) => {
    parsedContent.value.map(block => {
        if (block.uniqueIndex === newContent.uniqueIndex) {
            block.text = newContent.text;
        }
        return block;
    });
    contentChange();
};

const removeElement = (uniqueIndex) => {
    const index = parsedContent.value.findIndex(item => item.uniqueIndex === uniqueIndex);
    if (index !== -1) {
        parsedContent.value.splice(index, 1);
        let currentContent = blocksToCustomTags(parsedContent.value);
        emit('update:newContent', currentContent);
    }
};

const mergeTextAndInline = (input) => {
    const output = [];
    let currentTextParts = [];
    let currentIndex = 0;

    const flushText = () => {
        if (currentTextParts.length) {
            output.push({ text: currentTextParts, uniqueIndex: currentIndex });
            currentTextParts = [];
            currentIndex++;
        }
    };

    for (const item of input) {
        const key = Object.keys(item)[0];
        const value = item[key];

        if (key === "text") {
            const parsedParts = parseHtmlToTypedParts(value);

            for (const part of parsedParts) {
                currentTextParts.push(part);
            }
        } else if (value && value.inline === true) {
            currentTextParts.push({ type: key, content: value });
        } else {
            flushText();
            output.push({ [key]: value, uniqueIndex: currentIndex });
            currentIndex++;
        }
    }

    flushText();
    return output;
};

const SIZE_CLASS_MAP = {
    'text-xl': 'xl',
    'text-lg': 'lg',
    'text-sm': 'sm',
    'text-xs': 'xs',
};

const parseHtmlToTypedParts = (html) => {
    const parser = new DOMParser();
    const doc = parser.parseFromString(`<div>${html}</div>`, 'text/html');
    const container = doc.body.firstChild;

    function walkChildren(el) {
        const parts = [];
        for (const node of el.childNodes) {
            if (node.nodeType === Node.TEXT_NODE) {
                if (node.textContent.trim() !== '') {
                    parts.push({ type: 'text', content: node.textContent });
                }
            } else if (node.nodeType === Node.ELEMENT_NODE) {
                const tagName = node.tagName.toLowerCase();

                if (tagName === 'strong' || tagName === 'b') {
                    parts.push({ type: 'strong', children: walkChildren(node) });
                } else if (tagName === 'i' || tagName === 'em') {
                    parts.push({ type: 'i', children: walkChildren(node) });
                } else if (tagName === 'u') {
                    parts.push({ type: 'u', children: walkChildren(node) });
                } else if (tagName === 'hr') {
                    parts.push({ type: 'hr', content: '' });
                } else if (tagName === 'br') {
                    parts.push({ type: 'br', content: '' });
                } else if (tagName === 'span') {
                    // Check for size classes
                    let sizeType = null;
                    for (const [cls, type] of Object.entries(SIZE_CLASS_MAP)) {
                        if (node.classList.contains(cls)) {
                            sizeType = type;
                            break;
                        }
                    }
                    if (sizeType) {
                        parts.push({ type: sizeType, children: walkChildren(node) });
                    } else {
                        // Unwrap span — inline its children
                        parts.push(...walkChildren(node));
                    }
                } else {
                    // Unknown element — unwrap
                    parts.push(...walkChildren(node));
                }
            }
        }
        return parts;
    }

    return walkChildren(container);
};

// Element sheet state
const selectedIndex = ref(null);
const selectedSection = ref(null);
const elementSheetOpen = ref(false);

const { indexFilterText, filteredIndices, sectionFilterText, filteredSections } = useFilteredEntities(
    toRef(props, 'indices'),
    toRef(props, 'sections'),
);

const clearValues = () => {
    selectedIndex.value = null;
    selectedSection.value = null;
};

const insertPageSection = () => {
    let currentContent = blocksToCustomTags(parsedContent.value);
    currentContent += `{{section=${selectedSection.value.slug} /}}`;
    elementSheetOpen.value = false;
    emit('update:newContent', currentContent);
};

const insertPageIndex = () => {
    let currentContent = blocksToCustomTags(parsedContent.value);
    currentContent += `{{index=${selectedIndex.value.slug} /}}`;
    elementSheetOpen.value = false;
    emit('update:newContent', currentContent);
};

const addNewTextContent = (newContent) => {
    let currentContent = blocksToCustomTags(parsedContent.value);
    currentContent += newContent;
    emit('update:newContent', currentContent);
};

const editorOpen = ref(false);
</script>

<template>
    <Label>{{ props.label }}</Label>
    <div class="flex gap-1">
        <div>
            <TooltipProvider>
                <Tooltip>
                    <TooltipTrigger>
                        <div class="bg-primary rounded !p-1 mx-1 border border-primary" :class="editorOpen ? 'bg-secondary border-secondary text-primary' : 'text-secondary'" @click="editorOpen = true"><LetterTextIcon class="mx-auto w-4 h-4" /></div>
                    </TooltipTrigger>
                    <TooltipContent>
                        Add Text
                    </TooltipContent>
                </Tooltip>
            </TooltipProvider>
            <ElementSheet
                :open="elementSheetOpen"
                :selected-index="selectedIndex"
                :selected-section="selectedSection"
                :filtered-indices="filteredIndices"
                :filtered-sections="filteredSections"
                :index-filter-text="indexFilterText"
                :section-filter-text="sectionFilterText"
                @update:open="elementSheetOpen = $event"
                @update:selected-index="selectedIndex = $event"
                @update:selected-section="selectedSection = $event"
                @update:index-filter-text="indexFilterText = $event"
                @update:section-filter-text="sectionFilterText = $event"
                @insert-index="insertPageIndex"
                @insert-section="insertPageSection"
                @cancel="elementSheetOpen = false; clearValues()"
            />
        </div>
    </div>
    <div>
        <draggable
            @change="contentChange"
            v-model="parsedContent"
            item-key="slug"
        >
            <template #item="{element}">
                <DragDropEditorContent
                    @update:element-content="updateElementContent"
                    @delete:element="removeElement"
                    :element="element"
                    :unique-index="element.uniqueIndex"
                    :indices="props.indices"
                    :sections="props.sections"
                    :pages="props.pages"
                />
            </template>
        </draggable>
    </div>
    <DragDropTextEditor
        @close-editor="editorOpen = false"
        @save-content="addNewTextContent"
        v-if="editorOpen"
        :indices="props.indices"
        :sections="props.sections"
        :pages="props.pages"
    />
</template>
