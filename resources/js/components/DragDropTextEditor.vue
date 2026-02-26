<script setup lang="ts">
import { Check, X, Bold, Italic, Underline, Minus } from "lucide-vue-next";
import { ref, onMounted, computed, toRef } from "vue";
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from "@/components/ui/tooltip";
import { SymbolBar, TooltipSheet, LinkSheet } from "@/components/editor";
import { useFilteredEntities } from "@/composables/useFilteredEntities";

const props = defineProps({
    content: {
        type: [Object, Array, String],
        required: false,
        default() { return ""; }
    },
    uniqueIndex: {
        type: String,
        required: false,
        default() { return null; }
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

const emits = defineEmits(['closeEditor', 'saveContent']);

const closeEditor = () => { emits('closeEditor'); };

const editor = ref(null);
const selectedText = ref("");
const text = ref("");
const caretStart = ref(0);
const caretEnd = ref(0);

const boldToggled = ref(false);
const italicToggled = ref(false);
const underlineToggled = ref(false);

function updateCaret() {
    if (!editor.value) return;
    caretStart.value = editor.value.selectionStart;
    caretEnd.value = editor.value.selectionEnd;
    selectedText.value = editor.value.value.slice(caretStart.value, caretEnd.value);
}

function wrapWithTag(tag, tagText = "", tagSlug = null) {
    if (!editor.value) return;
    const val = editor.value.value;
    let openingTag = `{{${tag}`;
    if (tagSlug !== null) {
        openingTag += `=${tagSlug}}}`;
    } else {
        openingTag += `}}`;
    }
    let closingTag = `{{/${tag}}}`;

    if (caretStart.value !== caretEnd.value) {
        const newVal =
            val.slice(0, caretStart.value) +
            openingTag +
            val.slice(caretStart.value, caretEnd.value) +
            closingTag +
            val.slice(caretEnd.value);

        editor.value.value = newVal;
        const newCaret = caretEnd.value + tag.length + 5;
        editor.value.setSelectionRange(newCaret, newCaret);
    } else {
        const insertion = `${openingTag}${tagText}${closingTag}`;
        editor.value.value =
            val.slice(0, caretStart.value) + insertion + val.slice(caretStart.value);
        const newCaret = caretStart.value + tag.length + 4;
        editor.value.setSelectionRange(newCaret, newCaret);
    }

    text.value = editor.value.value;
    editor.value.focus();
    updateCaret();
}

function insertTag(tag) {
    if (!editor.value) return;
    const val = editor.value.value;

    const insertion = `{{${tag} /}}`;
    editor.value.value =
        val.slice(0, caretStart.value) + insertion + val.slice(caretStart.value);

    const newCaret = caretStart.value + tag.length + 6;
    editor.value.setSelectionRange(newCaret, newCaret);

    text.value = editor.value.value;
    editor.value.focus();
    updateCaret();
}

onMounted(() => { text.value = props.content; });

function save() {
    const normalized = text.value.replace(/\n/g, "<br />");
    emits('saveContent', normalized);
}

// Filtered entities
const {
    indexFilterText, filteredIndices,
    sectionFilterText, filteredSections,
    pageFilterText, filteredPages,
} = useFilteredEntities(
    toRef(props, 'indices'),
    toRef(props, 'sections'),
    toRef(props, 'pages'),
);

// Link state
const linkSheetOpen = ref(false);
const linkText = ref("");
const linkUrl = ref(null);
const selectedSection = ref(null);
const selectedPage = ref(null);

const openLinkSheet = () => {
    linkText.value = selectedText.value;
    linkSheetOpen.value = true;
};

const insertSectionLink = () => {
    if (!selectedSection.value) return;
    wrapWithTag('sectionLink', linkText.value.length > 0 ? linkText.value : selectedSection.value.title, selectedSection.value.slug);
    linkSheetOpen.value = false;
    clearValues();
};

const insertPageLink = () => {
    if (!selectedPage.value) return;
    wrapWithTag('pageLink', linkText.value.length > 0 ? linkText.value : selectedPage.value.title, selectedPage.value.slug);
    linkSheetOpen.value = false;
    clearValues();
};

const insertExternalLink = () => {
    if (!linkUrl.value) return;
    wrapWithTag('Link', linkText.value.length > 0 ? linkText.value : linkUrl.value, linkUrl.value);
    linkSheetOpen.value = false;
    clearValues();
};

// Tooltip state
const tooltipSheetOpen = ref(false);
const tooltipText = ref("");
const selectedIndex = ref(null);

const clearValues = () => {
    tooltipText.value = "";
    selectedIndex.value = null;
    selectedSection.value = null;
    selectedPage.value = null;
    indexFilterText.value = "";
    sectionFilterText.value = "";
    pageFilterText.value = "";
    linkText.value = "";
};

const openTooltipSheet = () => {
    tooltipText.value = selectedText.value;
    tooltipSheetOpen.value = true;
};

const insertTooltip = () => {
    if (!selectedIndex.value) return;
    wrapWithTag('indexTooltip', tooltipText.value.length > 0 ? tooltipText.value : selectedIndex.value.title, selectedIndex.value.slug);
    tooltipSheetOpen.value = false;
    clearValues();
};
</script>

<template>
    <div>
        <div class="flex gap-1">
            <div>
                <TooltipProvider>
                    <Tooltip>
                        <TooltipTrigger>
                            <div class="bg-primary rounded !p-1 mx-1 border border-primary" :class="boldToggled ? 'bg-secondary border-secondary text-primary' : 'text-secondary'" @click="wrapWithTag('b')"><Bold class="mx-auto w-4 h-4" /></div>
                        </TooltipTrigger>
                        <TooltipContent>Bold</TooltipContent>
                    </Tooltip>
                </TooltipProvider>
                <TooltipProvider>
                    <Tooltip>
                        <TooltipTrigger>
                            <div class="bg-primary rounded !p-1 mx-1 border border-primary" :class="italicToggled ? 'bg-secondary border-secondary text-primary' : 'text-secondary'" @click="wrapWithTag('i')"><Italic class="mx-auto w-4 h-4" /></div>
                        </TooltipTrigger>
                        <TooltipContent>Italic</TooltipContent>
                    </Tooltip>
                </TooltipProvider>
                <TooltipProvider>
                    <Tooltip>
                        <TooltipTrigger>
                            <div class="bg-primary rounded !p-1 mx-1 border border-primary" :class="underlineToggled ? 'bg-secondary border-secondary text-primary' : 'text-secondary'" @click="wrapWithTag('u')"><Underline class="mx-auto w-4 h-4" /></div>
                        </TooltipTrigger>
                        <TooltipContent>Underline</TooltipContent>
                    </Tooltip>
                </TooltipProvider>
                <TooltipProvider>
                    <Tooltip>
                        <TooltipTrigger>
                            <div class="bg-primary rounded !p-1 mx-1 border border-primary text-secondary" @click="insertTag('hr')"><Minus class="mx-auto w-4 h-4" /></div>
                        </TooltipTrigger>
                        <TooltipContent>Horizontal Line</TooltipContent>
                    </Tooltip>
                </TooltipProvider>
                <TooltipSheet
                    :open="tooltipSheetOpen"
                    :filtered-indices="filteredIndices"
                    :index-filter-text="indexFilterText"
                    :tooltip-text="tooltipText"
                    :selected-index="selectedIndex"
                    @open-sheet="openTooltipSheet"
                    @insert-tooltip="insertTooltip"
                    @cancel="tooltipSheetOpen = false; clearValues()"
                    @update:index-filter-text="indexFilterText = $event"
                    @update:tooltip-text="tooltipText = $event"
                    @update:selected-index="selectedIndex = $event"
                />
                <LinkSheet
                    :open="linkSheetOpen"
                    :link-text="linkText"
                    :link-url="linkUrl"
                    :selected-section="selectedSection"
                    :selected-page="selectedPage"
                    :filtered-sections="filteredSections"
                    :filtered-pages="filteredPages"
                    :section-filter-text="sectionFilterText"
                    :page-filter-text="pageFilterText"
                    @open-sheet="openLinkSheet"
                    @insert-section-link="insertSectionLink"
                    @insert-page-link="insertPageLink"
                    @insert-external-link="insertExternalLink"
                    @cancel="linkSheetOpen = false; clearValues()"
                    @update:link-text="linkText = $event"
                    @update:link-url="linkUrl = $event"
                    @update:selected-section="selectedSection = $event"
                    @update:selected-page="selectedPage = $event"
                    @update:section-filter-text="sectionFilterText = $event"
                    @update:page-filter-text="pageFilterText = $event"
                />
            </div>
        </div>
        <SymbolBar @insert-tag="insertTag" />
        <textarea
            class="rounded border border-secondary w-full min-h-60 p-1"
            ref="editor"
            @input="updateCaret"
            @keyup="updateCaret"
            @mouseup="updateCaret"
            v-model="text"
        ></textarea>
        <div class="flex justify-end my-1">
            <Check class="text-green-500 w-6 h-6 p-1 border rounded bg-secondary mr-2" @click="save" />
            <X class="text-red-500 w-6 h-6 p-1 border rounded bg-secondary" @click="closeEditor" />
        </div>
    </div>
</template>
