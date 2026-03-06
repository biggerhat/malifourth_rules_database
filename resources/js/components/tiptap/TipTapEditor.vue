<script setup lang="ts">
import { ref, onBeforeUnmount, watch } from 'vue';
import { useEditor, EditorContent } from '@tiptap/vue-3';
import StarterKit from '@tiptap/starter-kit';
import Underline from '@tiptap/extension-underline';
import Placeholder from '@tiptap/extension-placeholder';
import { GameSymbol } from './extensions/GameSymbol';
import { EntityReference } from './extensions/EntityReference';
import { EntityEmbed } from './extensions/EntityEmbed';
import { TextSize } from './extensions/TextSize';
import EditorToolbar from './EditorToolbar.vue';
import EntitySearchDialog from './EntitySearchDialog.vue';
import type { EntitySearchMode } from './EntitySearchDialog.vue';
import { Label } from '@/components/ui/label';
import { legacyToTipTap, isTipTapJson } from '@/lib/legacyToTipTap';

const props = withDefaults(
    defineProps<{
        modelValue: string;
        label?: string;
        mode?: 'full' | 'inline';
    }>(),
    {
        label: '',
        mode: 'full',
    },
);

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

const searchOpen = ref(false);
const searchMode = ref<EntitySearchMode>('tooltip');
const pendingEmbedType = ref<'index' | 'section'>('index');

function getInitialContent(): string {
    if (!props.modelValue || props.modelValue.trim() === '') {
        return JSON.stringify({ type: 'doc', content: [{ type: 'paragraph' }] });
    }
    if (isTipTapJson(props.modelValue)) {
        return props.modelValue;
    }
    return legacyToTipTap(props.modelValue);
}

const extensions = [
    StarterKit.configure({
        heading: props.mode === 'inline' ? false : undefined,
        horizontalRule: props.mode === 'inline' ? false : undefined,
    }),
    Underline,
    Placeholder.configure({ placeholder: 'Start typing...' }),
    GameSymbol,
    EntityReference,
    TextSize,
    ...(props.mode !== 'inline' ? [EntityEmbed] : []),
];

const editor = useEditor({
    content: JSON.parse(getInitialContent()),
    extensions,
    editorProps: {
        attributes: {
            class: 'prose prose-sm max-w-none focus:outline-none min-h-[100px] px-3 py-2',
        },
    },
    onUpdate: ({ editor: e }) => {
        emit('update:modelValue', JSON.stringify(e.getJSON()));
    },
});

// Watch for external modelValue changes (e.g. form reset)
watch(
    () => props.modelValue,
    (newVal) => {
        if (!editor.value) return;
        const currentJson = JSON.stringify(editor.value.getJSON());
        if (newVal === currentJson) return;
        // Only reset if value is substantively different
        if (!newVal || newVal.trim() === '') {
            editor.value.commands.setContent({ type: 'doc', content: [{ type: 'paragraph' }] });
            return;
        }
        const content = isTipTapJson(newVal) ? newVal : legacyToTipTap(newVal);
        try {
            editor.value.commands.setContent(JSON.parse(content));
        } catch (e) {
            console.warn('[TipTapEditor] Failed to parse content:', e);
        }
    },
);

onBeforeUnmount(() => {
    editor.value?.destroy();
});

const openSearch = (mode: EntitySearchMode) => {
    searchMode.value = mode;
    searchOpen.value = true;
};

const openEmbed = (type: 'index' | 'section') => {
    pendingEmbedType.value = type;
    searchMode.value = 'embed';
    searchOpen.value = true;
};

const onEntitySelect = (data: {
    referenceType: string;
    slug: string;
    label: string;
    url?: string;
    embedType?: string;
    title?: string;
}) => {
    if (!editor.value) return;

    if (data.referenceType === 'embed' && data.embedType) {
        editor.value
            .chain()
            .focus()
            .insertContent({
                type: 'entityEmbed',
                attrs: { embedType: data.embedType, slug: data.slug, title: data.title || data.label },
            })
            .run();
    } else {
        editor.value
            .chain()
            .focus()
            .insertContent({
                type: 'entityReference',
                attrs: {
                    referenceType: data.referenceType,
                    slug: data.slug,
                    label: data.label,
                    url: data.url || null,
                },
            })
            .run();
    }
};
</script>

<template>
    <div>
        <Label v-if="label">{{ label }}</Label>
        <div class="mt-1 rounded-md border">
            <EditorToolbar
                v-if="editor"
                :editor="editor"
                :mode="mode"
                @open-tooltip="openSearch('tooltip')"
                @open-link="openSearch('link')"
                @open-embed="openEmbed"
            />
            <EditorContent :editor="editor" />
        </div>

        <EntitySearchDialog
            :open="searchOpen"
            :mode="searchMode"
            @update:open="searchOpen = $event"
            @select="onEntitySelect"
        />
    </div>
</template>
