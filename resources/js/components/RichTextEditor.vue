<script setup lang="ts">
import {Label} from "@/components/ui/label";
import {Button} from "@/components/ui/button";
import {Textarea} from "@/components/ui/textarea";
import {computed, onMounted, ref} from 'vue';
import axios from 'axios';
import {Check, Search, ChevronsUpDown, SquarePlus, SquareMinus, Link2, MousePointerClick, Pencil, FileChartColumnIncreasing, SeparatorHorizontal } from 'lucide-vue-next'
import { cn } from '@/lib/utils'
import {
    Combobox,
    ComboboxAnchor,
    ComboboxTrigger,
    ComboboxEmpty,
    ComboboxGroup,
    ComboboxInput,
    ComboboxItem,
    ComboboxItemIndicator,
    ComboboxList } from '@/components/ui/combobox'
import MagicalDefense from "@/components/symbols/MagicalDefense.vue";
import Crow from "@/components/symbols/Crow.vue";
import Magic from "@/components/symbols/Magic.vue";
import Mask from "@/components/symbols/Mask.vue";
import Melee from "@/components/symbols/Melee.vue";
import Missile from "@/components/symbols/Missile.vue";
import Negative from "@/components/symbols/Negative.vue";
import PhysicalDefense from "@/components/symbols/PhysicalDefense.vue";
import Positive from "@/components/symbols/Positive.vue";
import Pulse from "@/components/symbols/Pulse.vue";
import Ram from "@/components/symbols/Ram.vue";
import SignatureAction from "@/components/symbols/SignatureAction.vue";
import Soulstone from "@/components/symbols/Soulstone.vue";
import Tome from "@/components/symbols/Tome.vue";
import UnusualDefense from "@/components/symbols/UnusualDefense.vue";
import {
    Tabs,
    TabsContent,
    TabsList,
    TabsTrigger,
} from '@/components/ui/tabs'
import {
    Sheet,
    SheetContent,
    SheetDescription,
    SheetHeader,
    SheetTitle,
    SheetTrigger,
} from '@/components/ui/sheet'
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
    DialogClose,
} from '@/components/ui/dialog'
import {Input} from "@/components/ui/input";
import {hasPermission} from "@/composables/hasPermission";
import {router} from "@inertiajs/vue3";
import {Tooltip, TooltipContent, TooltipProvider, TooltipTrigger} from "@/components/ui/tooltip";

const props = defineProps({
    label: {
        type: String,
        required: true,
        default() {
            return '';
        }
    },
    placeholder: {
        type: String,
        required: true,
        default() {
            return '';
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
})

const element_id = ref(`textarea_${Date.now()}_${Math.floor(Math.random() * 1000000)}`);

const model = defineModel();

const selectedIndex = ref(null);
const indexText = ref(null);
const tooltipSheetOpen = ref(false);
const linkSheetOpen = ref(false);
const elementSheetOpen = ref(false);

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


const selectedElement = ref(null);
const linkText = ref(null);
const linkUrl = ref(null);
const linkType = ref(null);

const processLink = () => {
    if (linkType.value === 'section') {
        selectedSection.value = selectedElement.value;
        sectionText.value = linkText.value;
        insertSectionLink();

        return;
    } else if(linkType.value === 'page') {
        selectedPage.value = selectedElement.value;
        pageText.value = linkText.value;
        insertPageLink();

        return;
    }


}

const selectedSection = ref(null);
const sectionText = ref(null);

const selectedPage = ref(null);
const pageText = ref(null);

const selectionStart = ref(null);
const selectionEnd = ref(null);

const boldStarted = ref(false);
const bold = () => {
    const textarea = document.getElementById(element_id.value);

    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const selected  = textarea.value.substring(start, end);

    // If no text selected
    if (start === end) {
        if (boldStarted.value) {
            model.value += "{{/b}}";
        } else {
            model.value += "{{b}}";
        }
        boldStarted.value = !boldStarted.value;

        textarea.focus();
        return;
    }

    // Your replacement text
    const replacement = "{{b}}" + selected + "{{/b}}";

    // Replace selected text
    model.value = model.value.substring(0, start)
        + replacement
        + model.value.substring(end);

    // Move the cursor after the replacement
    textarea.selectionStart = textarea.selectionEnd = start + replacement.length;

    // Optionally focus the textarea again
    textarea.focus();
};

const textXlStarted = ref(false);
const textXl = () => {
    const textarea = document.getElementById(element_id.value);

    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const selected  = textarea.value.substring(start, end);

    // If no text selected
    if (start === end) {
        if (textXlStarted.value) {
            model.value += "{{/xl}}";
        } else {
            model.value += "{{xl}}";
        }
        textXlStarted.value = !textXlStarted.value;

        textarea.focus();
        return;
    }

    // Your replacement text
    const replacement = "{{xl}}" + selected + "{{/xl}}";

    // Replace selected text
    model.value = model.value.substring(0, start)
        + replacement
        + model.value.substring(end);

    // Move the cursor after the replacement
    textarea.selectionStart = textarea.selectionEnd = start + replacement.length;

    // Optionally focus the textarea again
    textarea.focus();
};

const textLgStarted = ref(false);
const textLg = () => {
    const textarea = document.getElementById(element_id.value);

    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const selected  = textarea.value.substring(start, end);

    // If no text selected
    if (start === end) {
        if (textLgStarted.value) {
            model.value += "{{/lg}}";
        } else {
            model.value += "{{lg}}";
        }
        textLgStarted.value = !textLgStarted.value;

        textarea.focus();
        return;
    }

    // Your replacement text
    const replacement = "{{lg}}" + selected + "{{/lg}}";

    // Replace selected text
    model.value = model.value.substring(0, start)
        + replacement
        + model.value.substring(end);

    // Move the cursor after the replacement
    textarea.selectionStart = textarea.selectionEnd = start + replacement.length;

    // Optionally focus the textarea again
    textarea.focus();
};

const textSmStarted = ref(false);
const textSm = () => {
    const textarea = document.getElementById(element_id.value);

    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const selected  = textarea.value.substring(start, end);

    // If no text selected
    if (start === end) {
        if (textSmStarted.value) {
            model.value += "{{/sm}}";
        } else {
            model.value += "{{sm}}";
        }
        textSmStarted.value = !textSmStarted.value;

        textarea.focus();
        return;
    }

    // Your replacement text
    const replacement = "{{sm}}" + selected + "{{/sm}}";

    // Replace selected text
    model.value = model.value.substring(0, start)
        + replacement
        + model.value.substring(end);

    // Move the cursor after the replacement
    textarea.selectionStart = textarea.selectionEnd = start + replacement.length;

    // Optionally focus the textarea again
    textarea.focus();
};

const textXsStarted = ref(false);
const textXs = () => {
    const textarea = document.getElementById(element_id.value);

    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const selected  = textarea.value.substring(start, end);

    // If no text selected
    if (start === end) {
        if (textXsStarted.value) {
            model.value += "{{/xs}}";
        } else {
            model.value += "{{xs}}";
        }
        textXsStarted.value = !textXsStarted.value;

        textarea.focus();
        return;
    }

    // Your replacement text
    const replacement = "{{xs}}" + selected + "{{/xs}}";

    // Replace selected text
    model.value = model.value.substring(0, start)
        + replacement
        + model.value.substring(end);

    // Move the cursor after the replacement
    textarea.selectionStart = textarea.selectionEnd = start + replacement.length;

    // Optionally focus the textarea again
    textarea.focus();
};

const italicStarted = ref(false);
const italic = () => {
    const textarea = document.getElementById(element_id.value);

    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const selected  = textarea.value.substring(start, end);

    // If no text selected
    if (start === end) {
        if (italicStarted.value) {
            model.value += "{{/i}}";
        } else {
            model.value += "{{i}}";
        }
        italicStarted.value = !italicStarted.value;

        textarea.focus();
        return;
    }

    // Your replacement text
    const replacement = "{{i}}" + selected + "{{/i}}";

    // Replace selected text
    model.value = model.value.substring(0, start)
        + replacement
        + model.value.substring(end);

    // Move the cursor after the replacement
    textarea.selectionStart = textarea.selectionEnd = start + replacement.length;

    // Optionally focus the textarea again
    textarea.focus();
};

const underlineStarted = ref(false);
const underline = () => {
    const textarea = document.getElementById(element_id.value);

    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const selected  = textarea.value.substring(start, end);

    // If no text selected
    if (start === end) {
        if (model.value) {
            model.value += "{{/u}}";
        } else {
            model.value += "{{u}}";
        }
        underlineStarted.value = !underlineStarted.value;

        textarea.focus();
        return;
    }

    // Your replacement text
    const replacement = "{{u}}" + selected + "{{/u}}";

    // Replace selected text
    model.value = model.value.substring(0, start)
        + replacement
        + model.value.substring(end);

    // Move the cursor after the replacement
    textarea.selectionStart = textarea.selectionEnd = start + replacement.length;

    // Optionally focus the textarea again
    textarea.focus();
};

const horizontal = () => {
    const textarea = document.getElementById(element_id.value);

    const replacement = "{{hr /}}";

    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;

    // Replace selected text
    model.value = model.value.substring(0, start)
        + replacement
        + model.value.substring(end);

    // Optionally focus the textarea again
    textarea.focus();
};

const loadIndices = () => {
    clearValues();
    const textarea = document.getElementById(element_id.value);

    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const selected  = textarea.value.substring(start, end);

    // If no text selected
    if (start === end) {
        indexText.value = null;
    }

    selectionStart.value = start;
    selectionEnd.value = end;

    indexText.value = selected;
    tooltipSheetOpen.value = true;
}

const loadLinks = () => {
    clearValues();
    const textarea = document.getElementById(element_id.value);

    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const selected  = textarea.value.substring(start, end);

    // If no text selected
    if (start === end) {
        sectionText.value = null;
        pageText.value = null;
        linkText.value = null;
    }

    selectionStart.value = start;
    selectionEnd.value = end;

    sectionText.value = selected;
    pageText.value = selected;
    linkText.value = selected;
    linkSheetOpen.value = true;
}

const loadElements = () => {
    clearValues();
    const textarea = document.getElementById(element_id.value);

    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const selected  = textarea.value.substring(start, end);

    selectionStart.value = start;
    selectionEnd.value = end;

    elementSheetOpen.value = true;
}

const loadSections = () => {
    clearValues();
    const textarea = document.getElementById(element_id.value);

    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const selected  = textarea.value.substring(start, end);

    // If no text selected
    if (start === end) {
        sectionText.value = null;
    }

    selectionStart.value = start;
    selectionEnd.value = end;

    sectionText.value = selected;
    linkSheetOpen.value = true;
}

const loadPages = () => {
    clearValues();
    const textarea = document.getElementById(element_id.value);

    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const selected  = textarea.value.substring(start, end);

    // If no text selected
    if (start === end) {
        pageText.value = null;
    }

    selectionStart.value = start;
    selectionEnd.value = end;

    pageText.value = selected;
    linkSheetOpen.value = true;
}

const clearValues = () => {
    selectedIndex.value = null;
    indexText.value = null;
    selectedSection.value = null;
    sectionText.value = null;
    selectedPage.value = null;
    pageText.value = null;
    selectionStart.value = null;
    selectionEnd.value = null;
    linkText.value = null;
    linkUrl.value = null;
}

const insertIndexTooltip = () => {
    if (!selectedIndex.value) {
        return;
    }
    const textarea = document.getElementById(element_id.value);
    const text = indexText.value && indexText.value?.length > 0 ? indexText.value : selectedIndex.value.title;
    const replacement = "{{indexTooltip=" + selectedIndex.value.slug + "}}" + text + "{{/indexTooltip}}";

    const start = selectionStart.value;
    const end = selectionEnd.value;

    // If no text selected
    if (start === end) {
        model.value += replacement;
        tooltipSheetOpen.value = false;

        clearValues();
        textarea.focus();
        return;
    }

    // Replace selected text
    model.value = model.value.substring(0, start)
        + replacement
        + model.value.substring(end);

    tooltipSheetOpen.value = false;

    // Move the cursor after the replacement
    textarea.start = textarea.end = start + replacement.length;

    clearValues();
    // Optionally focus the textarea again
    textarea.focus();
};

const insertPageIndex = () => {
    if (!selectedIndex.value) {
        return;
    }
    const textarea = document.getElementById(element_id.value);
    const replacement = "{{index=" + selectedIndex.value.slug + " /}}";

    const start = selectionStart.value;
    const end = selectionEnd.value;

    // Replace selected text
    model.value = model.value.substring(0, start)
        + replacement
        + model.value.substring(end);

    elementSheetOpen.value = false;

    // Move the cursor after the replacement
    textarea.start = textarea.end = start + replacement.length;

    clearValues();
    // Optionally focus the textarea again
    textarea.focus();
};

const addIcon = (icon) => {
    const textarea = document.getElementById(element_id.value);
    const replacement = `{{${icon} /}}`;

    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;

    // Replace selected text
    model.value = model.value.substring(0, start)
        + replacement
        + model.value.substring(end);

    // Optionally focus the textarea again
    textarea.focus();
};

const insertPageSection = () => {
    if (!selectedSection.value) {
        return;
    }
    const textarea = document.getElementById(element_id.value);
    const replacement = "{{section=" + selectedSection.value.slug + " /}}";

    const start = selectionStart.value;
    const end = selectionEnd.value;

    // Replace selected text
    model.value = model.value.substring(0, start)
        + replacement
        + model.value.substring(end);

    elementSheetOpen.value = false;

    // Move the cursor after the replacement
    textarea.start = textarea.end = start + replacement.length;

    clearValues();
    // Optionally focus the textarea again
    textarea.focus();
};

const insertSectionLink = () => {
    if (!selectedSection.value) {
        return;
    }
    const textarea = document.getElementById(element_id.value);
    const text = sectionText.value && sectionText.value?.length > 0 ? sectionText.value : selectedSection.value.title;
    const replacement = "{{sectionLink=" + selectedSection.value.slug + "}}" + text + "{{/sectionLink}}";

    const start = selectionStart.value;
    const end = selectionEnd.value;

    // If no text selected
    if (start === end) {
        model.value += replacement;

        linkSheetOpen.value = false;
        clearValues();

        textarea.focus();
        return;
    }

    // Replace selected text
    model.value = model.value.substring(0, start)
        + replacement
        + model.value.substring(end);

    linkSheetOpen.value = false;

    // Move the cursor after the replacement
    textarea.start = textarea.end = start + replacement.length;

    clearValues();
    // Optionally focus the textarea again
    textarea.focus();
};

const insertPageLink = () => {
    if (!selectedPage.value) {
        return;
    }
    const textarea = document.getElementById(element_id.value);
    const text = pageText.value && pageText.value?.length > 0 ? pageText.value : selectedPage.value.title;
    const replacement = "{{pageLink=" + selectedPage.value.slug + "}}" + text + "{{/pageLink}}";

    const start = selectionStart.value;
    const end = selectionEnd.value;

    // If no text selected
    if (start === end) {
        model.value += replacement;
        linkSheetOpen.value = false;

        clearValues();
        textarea.focus();
        return;
    }

    // Replace selected text
    model.value = model.value.substring(0, start)
        + replacement
        + model.value.substring(end);

    linkSheetOpen.value = false;

    // Move the cursor after the replacement
    textarea.start = textarea.end = start + replacement.length;

    clearValues();
    // Optionally focus the textarea again
    textarea.focus();
};

const insertExternalLink = () => {
    if (!linkUrl.value) {
        return;
    }
    const textarea = document.getElementById(element_id.value);
    const text = linkText.value && linkText.value?.length > 0 ? linkText.value : linkUrl.value;
    const replacement = "{{Link=" + linkUrl.value + "}}" + text + "{{/Link}}";

    const start = selectionStart.value;
    const end = selectionEnd.value;

    // If no text selected
    if (start === end) {
        model.value += replacement;
        linkSheetOpen.value = false;

        clearValues();
        textarea.focus();
        return;
    }

    // Replace selected text
    model.value = model.value.substring(0, start)
        + replacement
        + model.value.substring(end);

    linkSheetOpen.value = false;

    // Move the cursor after the replacement
    textarea.start = textarea.end = start + replacement.length;

    clearValues();
    // Optionally focus the textarea again
    textarea.focus();
}

const currentTheme = computed(() => {
    return localStorage.appearance;
});
</script>

<template>
    <Label :for="element_id">{{ props.label }}</Label>
    <div class="flex gap-1">
        <Button type="button" :variant="textXlStarted ? 'outline' : 'default'" @click="textXl" class="text-xl p-2">XL</Button>
        <Button type="button" :variant="textLgStarted ? 'outline' : 'default'" @click="textLg" class="text-lg p-2">LG</Button>
        <Button type="button" :variant="textSmStarted ? 'outline' : 'default'" @click="textSm" class="text-sm p-2">SM</Button>
        <Button type="button" :variant="textXsStarted ? 'outline' : 'default'" @click="textXs" class="text-xs p-2">XS</Button>
        <TooltipProvider>
            <Tooltip>
                <TooltipTrigger>
                    <Button type="button" :variant="boldStarted ? 'outline' : 'default'" @click="bold" class="font-bold min-w-10 p-2">
                        B
                    </Button>
                </TooltipTrigger>
                <TooltipContent>
                    Bold
                </TooltipContent>
            </Tooltip>
        </TooltipProvider>
        <TooltipProvider>
            <Tooltip>
                <TooltipTrigger>
                    <Button type="button" :variant="italicStarted ? 'outline' : 'default'" @click="italic" class="italic p-2 min-w-10">I</Button>
                </TooltipTrigger>
                <TooltipContent>
                    Italic
                </TooltipContent>
            </Tooltip>
        </TooltipProvider>
        <TooltipProvider>
            <Tooltip>
                <TooltipTrigger>
                    <Button type="button" :variant="underlineStarted ? 'outline' : 'default'" @click="underline" class="underline min-w-10 p-2">U</Button>
                </TooltipTrigger>
                <TooltipContent>
                    Italic
                </TooltipContent>
            </Tooltip>
        </TooltipProvider>
        <div>
            <Sheet :open="tooltipSheetOpen">
                <SheetTrigger>
                    <TooltipProvider>
                        <Tooltip>
                            <TooltipTrigger>
                                <Button type="button" class="p-1 mx-1 min-w-10" @click="loadIndices()"><MousePointerClick /></Button>
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
                                <Input id="label" type="text" autofocus v-model="indexText" placeholder="Tooltip Label" />
                            </div>
                            <div class="flex flex-col w-full mt-4 space-y-1.5">
                                <Label>Selected Index</Label>
                                <div class="w-full p-2 border border-secondary my-1 flex justify-between hover:bg-secondary" v-if="selectedIndex">
                                    <div @click="indexText = selectedIndex.title" class="my-auto">{{ selectedIndex.title }}</div>
                                    <SquareMinus class="my-auto" @click="selectedIndex = null" />
                                </div>
                                <div v-else class="w-full p-2 my-1 text-red-500">
                                    None
                                </div>
                            </div>
                            <div class="flex w-full mt-4 space-y-1.5 justify-end gap-1">
                                <Button :disabled="!selectedIndex" class="bg-green-500" @click="insertIndexTooltip">Add Tooltip</Button>
                                <Button class="bg-red-500" @click="tooltipSheetOpen = false;clearValues()">Cancel</Button>
                            </div>

                            <div class="flex flex-col w-full mt-12 space-y-1.5">
                                <hr class="border-primary" />
                                <Label for="indexFilter">Select An Index</Label>
                                <Input id="indexFilter" type="text" v-model="indexFilterText" placeholder="Search..." />
                            </div>
                            <div class="max-h-100 overflow-y-auto">
                                <div v-for="index in filteredIndices" :key="index.id" class="w-full p-2 border border-secondary my-1 flex justify-between hover:bg-secondary">
                                    <div @click="indexText = index.title" class="my-auto">{{ index.title }}</div>
                                    <SquarePlus class="my-auto" @click="selectedIndex = index" />
                                </div>
                            </div>
                        </SheetDescription>
                    </SheetHeader>
                </SheetContent>
            </Sheet>
        </div>
        <div>
            <Sheet :open="linkSheetOpen">
                <SheetTrigger>
                    <TooltipProvider>
                        <Tooltip>
                            <TooltipTrigger>
                                <Button type="button" class="p-1 mx-1 min-w-10" @click="loadLinks()"><Link2 /></Button>
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
                                        <Input id="label" type="text" autofocus v-model="sectionText" placeholder="Add Label" />
                                    </div>
                                    <div class="flex flex-col w-full mt-4 space-y-1.5">
                                        <Label>Selected Section</Label>
                                        <div class="w-full p-2 border border-secondary my-1 flex justify-between hover:bg-secondary" v-if="selectedSection">
                                            <div @click="sectionText = selectedSection.title" class="my-auto">{{ selectedSection.title }}</div>
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
                                            <div @click="sectionText = section.title" class="my-auto">{{ section.title }}</div>
                                            <SquarePlus class="my-auto" @click="selectedSection = section" />
                                        </div>
                                    </div>
                                </TabsContent>
                                <TabsContent value="page">
                                    <div class="flex flex-col w-full mt-4 space-y-1.5">
                                        <Label for="label">Label</Label>
                                        <Input id="label" type="text" autofocus v-model="pageText" placeholder="Add Label" />
                                    </div>
                                    <div class="flex flex-col w-full mt-4 space-y-1.5">
                                        <Label>Selected Page</Label>
                                        <div class="w-full p-2 border border-secondary my-1 flex justify-between hover:bg-secondary" v-if="selectedPage">
                                            <div @click="pageText = selectedPage.title" class="my-auto">{{ selectedPage.title }}</div>
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
                                            <div @click="pageText = page.title" class="my-auto">{{ page.title }}</div>
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
        <div>
            <Sheet :open="elementSheetOpen">
                <SheetTrigger>
                    <TooltipProvider>
                        <Tooltip>
                            <TooltipTrigger>
                                <Button type="button" class="p-1 mx-1 min-w-10" @click="loadElements()"><FileChartColumnIncreasing /></Button>
                            </TooltipTrigger>
                            <TooltipContent>
                                Add Page Elements
                            </TooltipContent>
                        </Tooltip>
                    </TooltipProvider>
                </SheetTrigger>
                <SheetContent>
                    <SheetHeader>
                        <SheetTitle>Add Page Elements</SheetTitle>
                        <SheetDescription class="text-primary">
                            <Tabs default-value="index" class="w-full">
                                <TabsList class="grid w-full grid-cols-2">
                                    <TabsTrigger value="index">
                                        Index
                                    </TabsTrigger>
                                    <TabsTrigger value="section">
                                        Section
                                    </TabsTrigger>
                                </TabsList>
                                <TabsContent value="index">
                                    <div class="flex flex-col w-full mt-4 space-y-1.5">
                                        <Label>Selected Index</Label>
                                        <div class="w-full p-2 border border-secondary my-1 flex justify-between hover:bg-secondary" v-if="selectedIndex">
                                            <div class="my-auto">{{ selectedIndex.title }}</div>
                                            <SquareMinus class="my-auto" @click="selectedIndex = null" />
                                        </div>
                                        <div v-else class="w-full p-2 my-1 text-red-500">
                                            None
                                        </div>
                                    </div>
                                    <div class="flex w-full mt-4 space-y-1.5 justify-end gap-1">
                                        <Button :disabled="!selectedIndex" class="bg-green-500" @click="insertPageIndex">Add Page Element</Button>
                                        <Button class="bg-red-500" @click="elementSheetOpen = false;clearValues()">Cancel</Button>
                                    </div>

                                    <div class="flex flex-col w-full mt-12 space-y-1.5">
                                        <hr class="border-primary" />
                                        <Label for="indexFilter">Select An Index</Label>
                                        <Input id="indexFilter" type="text" v-model="indexFilterText" placeholder="Search..." />
                                    </div>
                                    <div class="max-h-100 overflow-y-auto">
                                        <div v-for="index in filteredIndices" :key="index.id" class="w-full p-2 border border-secondary my-1 flex justify-between hover:bg-secondary">
                                            <div class="my-auto">{{ index.title }}</div>
                                            <SquarePlus class="my-auto" @click="selectedIndex = index" />
                                        </div>
                                    </div>
                                </TabsContent>
                                <TabsContent value="section">
                                    <div class="flex flex-col w-full mt-4 space-y-1.5">
                                        <Label>Selected Section</Label>
                                        <div class="w-full p-2 border border-secondary my-1 flex justify-between hover:bg-secondary" v-if="selectedSection">
                                            <div class="my-auto">{{ selectedSection.title }}</div>
                                            <SquareMinus class="my-auto" @click="selectedSection = null" />
                                        </div>
                                        <div v-else class="w-full p-2 my-1 text-red-500">
                                            None
                                        </div>
                                    </div>
                                    <div class="flex w-full mt-4 space-y-1.5 justify-end gap-1">
                                        <Button :disabled="!selectedSection" class="bg-green-500" @click="insertPageSection()">Add Page Element</Button>
                                        <Button class="bg-red-500" @click="elementSheetOpen = false;clearValues()">Cancel</Button>
                                    </div>

                                    <div class="flex flex-col w-full mt-12 space-y-1.5">
                                        <hr class="border-primary" />
                                        <Label for="sectionFilter">Select A Section</Label>
                                        <Input id="sectionFilter" type="text" v-model="sectionFilterText" placeholder="Search..." />
                                    </div>
                                    <div class="max-h-100 overflow-y-auto">
                                        <div v-for="section in filteredSections" :key="section.id" class="w-full p-2 border border-secondary my-1 flex justify-between hover:bg-secondary">
                                            <div class="my-auto">{{ section.title }}</div>
                                            <SquarePlus class="my-auto" @click="selectedSection = section" />
                                        </div>
                                    </div>
                                </TabsContent>
                            </Tabs>
                        </SheetDescription>
                    </SheetHeader>
                </SheetContent>
            </Sheet>
        </div>
        <TooltipProvider>
            <Tooltip>
                <TooltipTrigger>
                    <Button type="button" variant="default" @click="horizontal" class="p-2 min-w-10"><SeparatorHorizontal /></Button>
                </TooltipTrigger>
                <TooltipContent>
                    Add Horizontal Line
                </TooltipContent>
            </Tooltip>
        </TooltipProvider>
    </div>
    <div class="flex gap-1">
        <Button type="button" variant="default" class="p-2 min-w-10" @click="addIcon('crow')"><Crow class-name="h-5 my-auto" :mode="currentTheme === 'light' ? 'dark' : 'light'" /></Button>
        <Button type="button" variant="default" class="p-2 min-w-10" @click="addIcon('magic')"><Magic class-name="h-6" :mode="currentTheme === 'light' ? 'dark' : 'light'" /></Button>
        <Button type="button" variant="default" class="p-2 min-w-10" @click="addIcon('magicaldefense')"><MagicalDefense class-name="h-6" :mode="currentTheme === 'light' ? 'dark' : 'light'" /></Button>
        <Button type="button" variant="default" class="p-2 min-w-10" @click="addIcon('mask')"><Mask class-name="h-6" :mode="currentTheme === 'light' ? 'dark' : 'light'" /></Button>
        <Button type="button" variant="default" class="p-2 min-w-10" @click="addIcon('melee')"><Melee class-name="h-6" :mode="currentTheme === 'light' ? 'dark' : 'light'" /></Button>
        <Button type="button" variant="default" class="p-2 min-w-10" @click="addIcon('missile')"><Missile class-name="h-4" :mode="currentTheme === 'light' ? 'dark' : 'light'" /></Button>
        <Button type="button" variant="default" class="p-2 min-w-10" @click="addIcon('negative')"><Negative class-name="h-6" :mode="currentTheme === 'light' ? 'dark' : 'light'" /></Button>
        <Button type="button" variant="default" class="p-2 min-w-10" @click="addIcon('physicaldefense')"><PhysicalDefense class-name="h-6" :mode="currentTheme === 'light' ? 'dark' : 'light'" /></Button>
        <Button type="button" variant="default" class="p-2 min-w-10" @click="addIcon('positive')"><Positive class-name="h-6" :mode="currentTheme === 'light' ? 'dark' : 'light'" /></Button>
        <Button type="button" variant="default" class="p-2 min-w-10" @click="addIcon('pulse')"><Pulse class-name="h-6" :mode="currentTheme === 'light' ? 'dark' : 'light'" /></Button>
        <Button type="button" variant="default" class="p-2 min-w-10" @click="addIcon('ram')"><Ram class-name="h-6" :mode="currentTheme === 'light' ? 'dark' : 'light'" /></Button>
        <Button type="button" variant="default" class="p-2 min-w-10" @click="addIcon('signatureaction')"><SignatureAction class-name="h-6" :mode="currentTheme === 'light' ? 'dark' : 'light'" /></Button>
        <Button type="button" variant="default" class="p-2 min-w-10" @click="addIcon('soulstone')"><Soulstone class-name="h-6" :mode="currentTheme === 'light' ? 'dark' : 'light'" /></Button>
        <Button type="button" variant="default" class="p-2 min-w-10" @click="addIcon('tome')"><Tome class-name="h-6" :mode="currentTheme === 'light' ? 'dark' : 'light'" /></Button>
        <Button type="button" variant="default" class="p-2 min-w-10" @click="addIcon('unusualdefense')"><UnusualDefense class-name="h-6" :mode="currentTheme === 'light' ? 'dark' : 'light'" /></Button>
    </div>
    <Textarea class="min-h-75" :id="element_id" v-model="model" :placeholder="props.placeholder" />
</template>
