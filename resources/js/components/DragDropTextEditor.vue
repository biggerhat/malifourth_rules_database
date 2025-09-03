<script setup lang="ts">
import {
    Check,
    LetterTextIcon,
    X,
    Bold,
    Italic,
    Underline,
    Minus,
    SquareMinus,
    MousePointerClick, SquarePlus, Link2
} from "lucide-vue-next";
import { ref, onMounted, nextTick, watch, computed } from "vue";
import Mask from "@/components/symbols/Mask.vue";
import {Tooltip, TooltipContent, TooltipProvider, TooltipTrigger} from "@/components/ui/tooltip";
import {Sheet, SheetContent, SheetDescription, SheetHeader, SheetTitle, SheetTrigger} from "@/components/ui/sheet";
import {Button} from "@/components/ui/button";
import {Input} from "@/components/ui/input";
import {Label} from "@/components/ui/label";
import {Tabs, TabsContent, TabsList, TabsTrigger} from "@/components/ui/tabs";
import Missile from "@/components/symbols/Missile.vue";
import Crow from "@/components/symbols/Crow.vue";
import Ram from "@/components/symbols/Ram.vue";
import Melee from "@/components/symbols/Melee.vue";
import Fortitude from "@/components/symbols/Fortitude.vue";
import Tome from "@/components/symbols/Tome.vue";
import SignatureAction from "@/components/symbols/SignatureAction.vue";
import Pulse from "@/components/symbols/Pulse.vue";
import Warding from "@/components/symbols/Warding.vue";
import Magic from "@/components/symbols/Magic.vue";
import UnusualDefense from "@/components/symbols/UnusualDefense.vue";
import Negative from "@/components/symbols/Negative.vue";
import Soulstone from "@/components/symbols/Soulstone.vue";
import Positive from "@/components/symbols/Positive.vue";

const props = defineProps({
    content: {
        type: [Object, Array, String],
        required: false,
        default() {
            return "";
        }
    },
    uniqueIndex: {
        type: String,
        required: false,
        default() {
            return null;
        }
    },
    indices: {
        type: [Object, Array],
        required: false,
        default() {
            return {};
        }
    },
    pages: {
        type: [Object, Array],
        required: false,
        default() {
            return {};
        }
    },
    sections: {
        type: [Object, Array],
        required: false,
        default() {
            return {};
        }
    }
});

const currentTheme = computed(() => {
    return localStorage.appearance;
});

const emits = defineEmits([
    'closeEditor',
    'saveContent',
]);

const closeEditor = () => {
    emits('closeEditor');
}

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
        // wrap selected text
        const newVal =
            val.slice(0, caretStart.value) +
            openingTag +
            val.slice(caretStart.value, caretEnd.value) +
            closingTag +
            val.slice(caretEnd.value);

        editor.value.value = newVal;

        // Move caret after wrapped text
        const newCaret = caretEnd.value + tag.length + 5;
        editor.value.setSelectionRange(newCaret, newCaret);
    } else {
        // insert tag at caret
        const insertion = `${openingTag}${tagText}${closingTag}`;
        editor.value.value =
            val.slice(0, caretStart.value) + insertion + val.slice(caretStart.value);

        // move caret inside inserted tags
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

    // move caret inside inserted tags
    const newCaret = caretStart.value + tag.length + 6;
    editor.value.setSelectionRange(newCaret, newCaret);

    text.value = editor.value.value;
    editor.value.focus();
    updateCaret();
}

onMounted(() => {
    text.value = props.content;
});

function save() {
    const normalized = text.value.replace(/\n/g, "<br />");
    emits('saveContent', normalized);
}

//Link Functions
const linkSheetOpen = ref(false);
const linkText = ref("");
const linkUrl = ref(null);
const selectedSection = ref(null);
const selectedPage = ref(null);

const openLinkSheet = () => {
    linkText.value = selectedText.value;
    linkSheetOpen.value = true;
}

const insertSectionLink = () => {
    if (!selectedSection.value) {
        return;
    }

    wrapWithTag(
        'sectionLink',
        linkText.value.length > 0 ? linkText.value : selectedSection.value.title,
        selectedSection.value.slug,
    );
    linkSheetOpen.value = false;
    clearValues();
}

const insertPageLink = () => {
    if (!selectedPage.value) {
        return;
    }

    wrapWithTag(
        'pageLink',
        linkText.value.length > 0 ? linkText.value : selectedPage.value.title,
        selectedPage.value.slug,
    );
    linkSheetOpen.value = false;
    clearValues();
}

const insertExternalLink = () => {
    if (!linkUrl.value) {
        return;
    }

    wrapWithTag(
        'Link',
        linkText.value.length > 0 ? linkText.value : linkUrl.value,
        linkUrl.value,
    );
    linkSheetOpen.value = false;
    clearValues();
}

//Tooltip Functions
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
}

const openTooltipSheet = () => {
    tooltipText.value = selectedText.value;
    tooltipSheetOpen.value = true;
}

const insertTooltip = () => {
    if (!selectedIndex.value) {
        return;
    }

    wrapWithTag(
        'indexTooltip',
        tooltipText.value.length > 0 ? tooltipText.value : selectedIndex.value.title,
        selectedIndex.value.slug,
    );
    tooltipSheetOpen.value = false;
    clearValues();
};

//Filtered Props
const indexFilterText = ref('');
const filteredIndices = computed(() => {
    const filter = indexFilterText.value;

    if (!filter.length) {
        return props.indices;
    }

    const filtered = props.indices;

    return filtered.filter(index => {
        return index.title.toLowerCase().includes(filter.toLowerCase());
    });
});

const sectionFilterText = ref('');
const filteredSections = computed(() => {
    const filter = sectionFilterText.value;

    if (!filter.length) {
        return props.sections;
    }

    const filtered = props.sections;

    return filtered.filter(section => {
        return section.title.toLowerCase().includes(filter.toLowerCase());
    });
});

const pageFilterText = ref('');
const filteredPages = computed(() => {
    const filter = pageFilterText.value;

    if (!filter.length) {
        return props.pages;
    }

    const filtered = props.pages;

    return filtered.filter(page => {
        return page.title.toLowerCase().includes(filter.toLowerCase());
    });
});
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
                        <TooltipContent>
                            Bold
                        </TooltipContent>
                    </Tooltip>
                </TooltipProvider>
                <TooltipProvider>
                    <Tooltip>
                        <TooltipTrigger>
                            <div class="bg-primary rounded !p-1 mx-1 border border-primary" :class="boldToggled ? 'bg-secondary border-secondary text-primary' : 'text-secondary'" @click="wrapWithTag('i')"><Italic class="mx-auto w-4 h-4" /></div>
                        </TooltipTrigger>
                        <TooltipContent>
                            Italic
                        </TooltipContent>
                    </Tooltip>
                </TooltipProvider>
                <TooltipProvider>
                    <Tooltip>
                        <TooltipTrigger>
                            <div class="bg-primary rounded !p-1 mx-1 border border-primary" :class="boldToggled ? 'bg-secondary border-secondary text-primary' : 'text-secondary'" @click="wrapWithTag('u')"><Underline class="mx-auto w-4 h-4" /></div>
                        </TooltipTrigger>
                        <TooltipContent>
                            Underline
                        </TooltipContent>
                    </Tooltip>
                </TooltipProvider>
                <TooltipProvider>
                    <Tooltip>
                        <TooltipTrigger>
                            <div class="bg-primary rounded !p-1 mx-1 border border-primary" :class="boldToggled ? 'bg-secondary border-secondary text-primary' : 'text-secondary'" @click="insertTag('hr')"><Minus class="mx-auto w-4 h-4" /></div>
                        </TooltipTrigger>
                        <TooltipContent>
                            Horizontal Line
                        </TooltipContent>
                    </Tooltip>
                </TooltipProvider>
                <Sheet :open="tooltipSheetOpen">
                    <SheetTrigger>
                        <TooltipProvider>
                            <Tooltip>
                                <TooltipTrigger>
                                    <div class="bg-primary rounded !p-1 mx-1 border border-primary" :class="boldToggled ? 'bg-secondary border-secondary text-primary' : 'text-secondary'" @click="openTooltipSheet"><MousePointerClick class="mx-auto w-4 h-4" /></div>
                                </TooltipTrigger>
                                <TooltipContent>
                                    Add Index Tooltip
                                </TooltipContent>
                            </Tooltip>
                        </TooltipProvider>
                    </SheetTrigger>
                    <SheetContent>
                        <SheetHeader>
                            <SheetTitle>Add Index Tooltip</SheetTitle>
                            <SheetDescription class="text-primary">
                                <div class="flex flex-col w-full mt-4 space-y-1.5">
                                    <Label for="label">Tooltip Label</Label>
                                    <Input id="label" type="text" autofocus v-model="tooltipText" placeholder="Tooltip Label" />
                                </div>
                                <div class="flex flex-col w-full mt-4 space-y-1.5">
                                    <Label>Selected Index</Label>
                                    <div class="w-full p-2 border border-secondary my-1 flex justify-between hover:bg-secondary" v-if="selectedIndex">
                                        <div @click="tooltipText = selectedIndex.title" class="my-auto">{{ selectedIndex.display_name }}</div>
                                        <SquareMinus class="my-auto" @click="selectedIndex = null" />
                                    </div>
                                    <div v-else class="w-full p-2 my-1 text-red-500">
                                        None
                                    </div>
                                </div>
                                <div class="flex w-full mt-4 space-y-1.5 justify-end gap-1">
                                    <Button :disabled="!selectedIndex" class="bg-green-500" @click="insertTooltip">Add Tooltip</Button>
                                    <Button class="bg-red-500" @click="tooltipSheetOpen = false;clearValues()">Cancel</Button>
                                </div>

                                <div class="flex flex-col w-full mt-12 space-y-1.5">
                                    <hr class="border-primary" />
                                    <Label for="indexFilter">Select An Index</Label>
                                    <Input id="indexFilter" type="text" v-model="indexFilterText" placeholder="Search..." />
                                </div>
                                <div class="max-h-100 overflow-y-auto">
                                    <div v-for="index in filteredIndices" :key="index.id" class="w-full p-2 border border-secondary my-1 flex justify-between hover:bg-secondary">
                                        <div @click="indexText = index.title" class="my-auto">{{ index.display_name }}</div>
                                        <SquarePlus class="my-auto" @click="selectedIndex = index" />
                                    </div>
                                </div>
                            </SheetDescription>
                        </SheetHeader>
                    </SheetContent>
                </Sheet>
                <Sheet :open="linkSheetOpen">
                    <SheetTrigger>
                        <TooltipProvider>
                            <Tooltip>
                                <TooltipTrigger>
                                    <div class="bg-primary rounded !p-1 mx-1 border border-primary text-secondary" @click="openLinkSheet"><Link2 class="mx-auto w-4 h-4" /></div>
                                </TooltipTrigger>
                                <TooltipContent>
                                    Add Link
                                </TooltipContent>
                            </Tooltip>
                        </TooltipProvider>
                    </SheetTrigger>
                    <SheetContent>
                        <SheetHeader>
                            <SheetTitle>Add Link</SheetTitle>
                            <SheetDescription class="text-primary">
                                <Tabs default-value="account" class="w-full">
                                    <TabsList class="grid w-full grid-cols-3">
                                        <TabsTrigger value="section">
                                            Section
                                        </TabsTrigger>
                                        <TabsTrigger value="page">
                                            Page
                                        </TabsTrigger>
                                        <TabsTrigger value="external">
                                            External
                                        </TabsTrigger>
                                    </TabsList>
                                    <TabsContent value="section">
                                        <div class="flex flex-col w-full mt-4 space-y-1.5">
                                            <Label for="label">Label</Label>
                                            <Input id="label" type="text" autofocus v-model="linkText" placeholder="Add Label" />
                                        </div>
                                        <div class="flex flex-col w-full mt-4 space-y-1.5">
                                            <Label>Selected Section</Label>
                                            <div class="w-full p-2 border border-secondary my-1 flex justify-between hover:bg-secondary" v-if="selectedSection">
                                                <div @click="sectionText = selectedSection.title" class="my-auto">{{ selectedSection.display_name }}</div>
                                                <SquareMinus class="my-auto" @click="selectedSection = null" />
                                            </div>
                                            <div v-else class="w-full p-2 my-1 text-red-500">
                                                None
                                            </div>
                                        </div>
                                        <div class="flex w-full mt-4 space-y-1.5 justify-end gap-1">
                                            <Button :disabled="!selectedSection" class="bg-green-500" @click="insertSectionLink">Add Link</Button>
                                            <Button class="bg-red-500" @click="linkSheetOpen = false;clearValues()">Cancel</Button>
                                        </div>

                                        <div class="flex flex-col w-full mt-12 space-y-1.5">
                                            <hr class="border-primary" />
                                            <Label for="sectionFilter">Select A Section</Label>
                                            <Input id="sectionFilter" type="text" v-model="sectionFilterText" placeholder="Search..." />
                                        </div>
                                        <div class="max-h-100 overflow-y-auto">
                                            <div v-for="section in filteredSections" :key="section.id" class="w-full p-2 border border-secondary my-1 flex justify-between hover:bg-secondary">
                                                <div @click="sectionText = section.title" class="my-auto">{{ section.display_name }}</div>
                                                <SquarePlus class="my-auto" @click="selectedSection = section" />
                                            </div>
                                        </div>
                                    </TabsContent>
                                    <TabsContent value="page">
                                        <div class="flex flex-col w-full mt-4 space-y-1.5">
                                            <Label for="label">Label</Label>
                                            <Input id="label" type="text" autofocus v-model="linkText" placeholder="Add Label" />
                                        </div>
                                        <div class="flex flex-col w-full mt-4 space-y-1.5">
                                            <Label>Selected Page</Label>
                                            <div class="w-full p-2 border border-secondary my-1 flex justify-between hover:bg-secondary" v-if="selectedPage">
                                                <div @click="pageText = selectedPage.title" class="my-auto">{{ selectedPage.display_name }}</div>
                                                <SquareMinus class="my-auto" @click="selectedPage = null" />
                                            </div>
                                            <div v-else class="w-full p-2 my-1 text-red-500">
                                                None
                                            </div>
                                        </div>
                                        <div class="flex w-full mt-4 space-y-1.5 justify-end gap-1">
                                            <Button :disabled="!selectedPage" class="bg-green-500" @click="insertPageLink">Add Link</Button>
                                            <Button class="bg-red-500" @click="linkSheetOpen = false;clearValues()">Cancel</Button>
                                        </div>

                                        <div class="flex flex-col w-full mt-12 space-y-1.5">
                                            <hr class="border-primary" />
                                            <Label for="pageFilter">Select A Page</Label>
                                            <Input id="pageFilter" type="text" v-model="pageFilterText" placeholder="Search..." />
                                        </div>
                                        <div class="max-h-100 overflow-y-auto">
                                            <div v-for="page in filteredPages" :key="page.id" class="w-full p-2 border border-secondary my-1 flex justify-between hover:bg-secondary">
                                                <div @click="pageText = page.title" class="my-auto">{{ page.display_name }}</div>
                                                <SquarePlus class="my-auto" @click="selectedPage = page" />
                                            </div>
                                        </div>
                                    </TabsContent>
                                    <TabsContent value="external">
                                        <div class="flex flex-col w-full mt-4 space-y-1.5">
                                            <Label for="label">Label</Label>
                                            <Input id="label" type="text" autofocus v-model="linkText" placeholder="Add Label" />
                                        </div>
                                        <div class="flex flex-col w-full mt-4 space-y-1.5">
                                            <Label for="url">URL</Label>
                                            <Input id="url" type="text" v-model="linkUrl" placeholder="Add URL" />
                                        </div>
                                        <div class="flex w-full mt-4 space-y-1.5 justify-end gap-1">
                                            <Button :disabled="!linkUrl || !linkText" class="bg-green-500" @click="insertExternalLink">Add Link</Button>
                                            <Button class="bg-red-500" @click="linkSheetOpen = false;clearValues()">Cancel</Button>
                                        </div>
                                    </TabsContent>
                                </Tabs>
                            </SheetDescription>
                        </SheetHeader>
                    </SheetContent>
                </Sheet>
            </div>
        </div>
        <div class="flex gap-1 mb-2">
            <div class="bg-primary rounded !p-1 mx-1 border border-primary text-secondary" @click="insertTag('crow')"><Crow class-name="w-4 h-4 my-auto" :mode="currentTheme === 'light' ? 'dark' : 'light'" /></div>
            <div class="bg-primary rounded !p-1 mx-1 border border-primary text-secondary" @click="insertTag('magic')"><Magic class-name="w-4 h-4 my-auto" :mode="currentTheme === 'light' ? 'dark' : 'light'" /></div>
            <div class="bg-primary rounded !p-1 mx-1 border border-primary text-secondary" @click="insertTag('warding')"><Warding class-name="w-4 h-4 my-auto" :mode="currentTheme === 'light' ? 'dark' : 'light'" /></div>
            <div class="bg-primary rounded !p-1 mx-1 border border-primary text-secondary" @click="insertTag('mask')"><Mask class-name="w-4 h-4 my-auto" :mode="currentTheme === 'light' ? 'dark' : 'light'" /></div>
            <div class="bg-primary rounded !p-1 mx-1 border border-primary text-secondary" @click="insertTag('melee')"><Melee class-name="w-4 h-4 my-auto" :mode="currentTheme === 'light' ? 'dark' : 'light'" /></div>
            <div class="bg-primary rounded !p-1 mx-1 border border-primary text-secondary" @click="insertTag('missile')"><Missile class-name="h-4" :mode="currentTheme === 'light' ? 'dark' : 'light'" /></div>
            <div class="bg-primary rounded !p-1 mx-1 border border-primary text-secondary" @click="insertTag('negative')"><Negative class-name="w-4 h-4 my-auto" :mode="currentTheme === 'light' ? 'dark' : 'light'" /></div>
            <div class="bg-primary rounded !p-1 mx-1 border border-primary text-secondary" @click="insertTag('fortitude')"><Fortitude class-name="w-4 h-4 my-auto" :mode="currentTheme === 'light' ? 'dark' : 'light'" /></div>
            <div class="bg-primary rounded !p-1 mx-1 border border-primary text-secondary" @click="insertTag('positive')"><Positive class-name="w-4 h-4 my-auto" :mode="currentTheme === 'light' ? 'dark' : 'light'" /></div>
            <div class="bg-primary rounded !p-1 mx-1 border border-primary text-secondary" @click="insertTag('pulse')"><Pulse class-name="w-4 h-4 my-auto" :mode="currentTheme === 'light' ? 'dark' : 'light'" /></div>
            <div class="bg-primary rounded !p-1 mx-1 border border-primary text-secondary" @click="insertTag('ram')"><Ram class-name="w-4 h-4 my-auto" :mode="currentTheme === 'light' ? 'dark' : 'light'" /></div>
            <div class="bg-primary rounded !p-1 mx-1 border border-primary text-secondary" @click="insertTag('signatureaction')"><SignatureAction class-name="w-4 h-4 my-auto" :mode="currentTheme === 'light' ? 'dark' : 'light'" /></div>
            <div class="bg-primary rounded !p-1 mx-1 border border-primary text-secondary" @click="insertTag('soulstone')"><Soulstone class-name="w-4 h-4 my-auto" :mode="currentTheme === 'light' ? 'dark' : 'light'" /></div>
            <div class="bg-primary rounded !p-1 mx-1 border border-primary text-secondary" @click="insertTag('tome')"><Tome class-name="w-4 h-4 my-auto" :mode="currentTheme === 'light' ? 'dark' : 'light'" /></div>
            <div class="bg-primary rounded !p-1 mx-1 border border-primary text-secondary" @click="insertTag('unusualdefense')"><UnusualDefense class-name="w-4 h-4" :mode="currentTheme === 'light' ? 'dark' : 'light'" /></div>
        </div>
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
