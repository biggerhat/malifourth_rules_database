<script setup lang="ts">
import { defineProps, h, ref } from 'vue';
import { COMPONENT_MAP } from '@/lib/content-tags';
import { segmentsToCustomTags, customTagsToSegments } from '@/lib/tag-serializer';
import ParsedContent from '@/components/ParsedContent.vue';
import { CircleEllipsisIcon, CircleMinusIcon, CirclePlusIcon, CircleXIcon } from 'lucide-vue-next';
import DragDropTextContent from '@/components/DragDropTextContent.vue';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogTrigger,
} from '@/components/ui/alert-dialog';
import DragDropTextEditor from '@/components/DragDropTextEditor.vue';

const props = defineProps({
    element: {
        type: [Object, Array, String],
        required: true,
    },
    uniqueIndex: {
        type: Number,
        required: true,
    },
    indices: {
        type: [Object, Array],
        required: false,
        default() { return {}; },
    },
    pages: {
        type: [Object, Array],
        required: false,
        default() { return {}; },
    },
    sections: {
        type: [Object, Array],
        required: false,
        default() { return {}; },
    },
});

const UnknownTag = {
    props: ['text'],
    template: `<span style="color: red;">[Unknown tag]</span>`,
};

function resolveComponent(item) {
    if (item.text !== undefined) {
        return {
            props: ['text'],
            render() {
                return h('div', { innerHTML: this.text });
            },
        };
    }

    const tag = Object.keys(item)[0];
    return COMPONENT_MAP[tag] || UnknownTag;
}

function getProps(item) {
    if (item.text !== undefined) {
        return { text: item.text };
    }

    const tag = Object.keys(item)[0];
    const data = item[tag];
    const itemProps = { ...data };

    if (Array.isArray(data.text)) {
        itemProps.text = h(ParsedContent, { content: data.text });
    }

    return itemProps;
}

const editorOpen = ref(false);
const collapsed = ref(false);

const toggleCollapse = () => {
    collapsed.value = !collapsed.value;
};

const emit = defineEmits(['update:elementContent', 'delete:element']);

const normalizedContent = ref(null);
const openEditor = () => {
    normalizedContent.value = segmentsToCustomTags(props.element.text);
    editorOpen.value = true;
};

const removeElement = (uniqueIndex) => {
    emit('delete:element', uniqueIndex);
};

const changeTextContent = (content) => {
    let normalized = content.replace(/\n/g, "<br />");
    emit('update:elementContent', { text: customTagsToSegments(normalized), uniqueIndex: props.uniqueIndex });
    editorOpen.value = false;
};
</script>

<template>
    <div class="my-1 border border-blue-400" v-if="!editorOpen">
        <div class="bg-background block flex justify-between p-1 text-sm">
            <div>{{ getProps(element).title ?? 'Text' }}</div>
            <div>
                <CircleMinusIcon v-if="!collapsed" @click="toggleCollapse()" class="text-primary mr-1 inline w-5" />
                <CirclePlusIcon v-else @click="toggleCollapse()" class="text-primary mr-1 inline w-5" />
                <CircleEllipsisIcon v-if="element.text !== undefined" @click="openEditor" class="mr-1 inline w-5 text-green-500" />
                <AlertDialog>
                    <AlertDialogTrigger as-child>
                        <CircleXIcon class="inline w-5 text-red-500" />
                    </AlertDialogTrigger>
                    <AlertDialogContent>
                        <AlertDialogHeader>
                            <AlertDialogTitle>Delete {{ getProps(element).title ?? 'Text' }}</AlertDialogTitle>
                            <AlertDialogDescription> Are you sure you want to remove this? </AlertDialogDescription>
                        </AlertDialogHeader>
                        <AlertDialogFooter>
                            <AlertDialogCancel>Cancel</AlertDialogCancel>
                            <AlertDialogAction @click="removeElement(props.uniqueIndex)">Continue</AlertDialogAction>
                        </AlertDialogFooter>
                    </AlertDialogContent>
                </AlertDialog>
            </div>
        </div>
        <transition name="fade">
            <div class="px-1" v-show="!collapsed">
                <DragDropTextContent v-if="element.text !== undefined" :content="element.text" :root="true" />
                <component v-else :is="resolveComponent(element)" v-bind="getProps(element)" />
            </div>
        </transition>
    </div>
    <div class="px-1" v-else>
        <DragDropTextEditor
            @close-editor="editorOpen = false"
            @save-content="changeTextContent"
            v-if="editorOpen"
            :content="normalizedContent"
            :indices="props.indices"
            :sections="props.sections"
            :pages="props.pages"
        />
    </div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition:
        opacity 0.2s ease,
        transform 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
    transform: scaleY(0.95);
}
</style>
