<script setup lang="ts">
import {Label} from "@/components/ui/label";
import {Button} from "@/components/ui/button";
import {Textarea} from "@/components/ui/textarea";
import { computed, ref } from 'vue';
import axios from 'axios';
import { Check, Search, ChevronsUpDown } from 'lucide-vue-next'
import { cn } from '@/lib/utils'
import { Combobox, ComboboxAnchor, ComboboxTrigger, ComboboxEmpty, ComboboxGroup, ComboboxInput, ComboboxItem, ComboboxItemIndicator, ComboboxList } from '@/components/ui/combobox'
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

const model = defineModel();
const indices = ref(null);
const sections = ref(null);
const pages = ref(null);

const selectedIndex = ref(null);
const indexText = ref(null);

const selectedSection = ref(null);
const sectionText = ref(null);

const selectedPage = ref(null);
const pageText = ref(null);

const selectionStart = ref(null);
const selectionEnd = ref(null);

const boldStarted = ref(false);
const bold = () => {
    const textarea = document.getElementById('text_editor');

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
    const textarea = document.getElementById('text_editor');

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
    const textarea = document.getElementById('text_editor');

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
    const textarea = document.getElementById('text_editor');

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
    const textarea = document.getElementById('text_editor');

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
    const textarea = document.getElementById('text_editor');

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
    const textarea = document.getElementById('text_editor');

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
    const textarea = document.getElementById('text_editor');
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
    const textarea = document.getElementById('text_editor');

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

    if (indices.value) {
        return
    }

    axios.get(route('admin.indices.list'))
        .then(function (response) {
            indices.value = response.data;
        });
}

const loadSections = () => {
    clearValues();
    const textarea = document.getElementById('text_editor');

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

    if (sections.value) {
        return
    }

    axios.get(route('admin.sections.list'))
        .then(function (response) {
            sections.value = response.data;
        });
}

const loadPages = () => {
    clearValues();
    const textarea = document.getElementById('text_editor');

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

    if (pages.value) {
        return
    }

    axios.get(route('admin.pages.list'))
        .then(function (response) {
            pages.value = response.data;
        });
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
}

const insertIndexTooltip = () => {
    if (!selectedIndex.value) {
        return;
    }
    const textarea = document.getElementById('text_editor');
    const text = indexText.value.length > 0 ? indexText.value : selectedIndex.value.title;
    const replacement = "{{indexTooltip=" + selectedIndex.value.slug + "}}" + text + "{{/indexTooltip}}";

    const start = selectionStart.value;
    const end = selectionEnd.value;

    // If no text selected
    if (start === end) {
        model.value += replacement;
        textarea.focus();
        return;
    }

    // Replace selected text
    model.value = model.value.substring(0, start)
        + replacement
        + model.value.substring(end);

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
    const textarea = document.getElementById('text_editor');
    const replacement = "{{index=" + selectedIndex.value.slug + " /}}";

    const start = selectionStart.value;
    const end = selectionEnd.value;

    // Replace selected text
    model.value = model.value.substring(0, start)
        + replacement
        + model.value.substring(end);

    // Move the cursor after the replacement
    textarea.start = textarea.end = start + replacement.length;

    clearValues();
    // Optionally focus the textarea again
    textarea.focus();
};

const addIcon = (icon) => {
    const textarea = document.getElementById('text_editor');
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
    const textarea = document.getElementById('text_editor');
    const replacement = "{{section=" + selectedSection.value.slug + " /}}";

    const start = selectionStart.value;
    const end = selectionEnd.value;

    // Replace selected text
    model.value = model.value.substring(0, start)
        + replacement
        + model.value.substring(end);

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
    const textarea = document.getElementById('text_editor');
    const text = sectionText.value.length > 0 ? sectionText.value : selectedSection.value.title;
    const replacement = "{{sectionLink=" + selectedSection.value.slug + "}}" + text + "{{/sectionLink}}";

    const start = selectionStart.value;
    const end = selectionEnd.value;

    // If no text selected
    if (start === end) {
        model.value += replacement;
        textarea.focus();
        return;
    }

    // Replace selected text
    model.value = model.value.substring(0, start)
        + replacement
        + model.value.substring(end);

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
    const textarea = document.getElementById('text_editor');
    const text = pageText.value.length > 0 ? pageText.value : selectedPage.value.title;
    const replacement = "{{pageLink=" + selectedPage.value.slug + "}}" + text + "{{/pageLink}}";

    const start = selectionStart.value;
    const end = selectionEnd.value;

    // If no text selected
    if (start === end) {
        model.value += replacement;
        textarea.focus();
        return;
    }

    // Replace selected text
    model.value = model.value.substring(0, start)
        + replacement
        + model.value.substring(end);

    // Move the cursor after the replacement
    textarea.start = textarea.end = start + replacement.length;

    clearValues();
    // Optionally focus the textarea again
    textarea.focus();
};

const currentTheme = computed(() => {
    return localStorage.appearance;
});
</script>

<template>
    <Label for="text_editor">{{ props.label }}</Label>
    <div class="flex gap-1">
        <div class="my-auto">Text Changes: </div>
        <Button type="button" :variant="textXlStarted ? 'outline' : 'default'" @click="textXl" class="text-xl p-2">XL</Button>
        <Button type="button" :variant="textLgStarted ? 'outline' : 'default'" @click="textLg" class="text-lg p-2">LG</Button>
        <Button type="button" :variant="textSmStarted ? 'outline' : 'default'" @click="textSm" class="text-sm p-2">SM</Button>
        <Button type="button" :variant="textXsStarted ? 'outline' : 'default'" @click="textXs" class="text-xs p-2">XS</Button>
        <Button type="button" :variant="boldStarted ? 'outline' : 'default'" @click="bold" class="font-bold p-2">Bold</Button>
        <Button type="button" :variant="italicStarted ? 'outline' : 'default'" @click="italic" class="italic p-2">Italic</Button>
        <Button type="button" :variant="underlineStarted ? 'outline' : 'default'" @click="underline" class="underline p-2">Underline</Button>
        <div>
            <Dialog>
                <DialogTrigger>
                    <Button type="button" @click="loadIndices" class="p-2">Add Index Tooltip</Button>
                </DialogTrigger>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Add Index Tooltip</DialogTitle>
                    </DialogHeader>
                        <DialogDescription>
                            <Combobox v-model="selectedIndex" by="label">
                                <ComboboxAnchor as-child>
                                    <ComboboxTrigger as-child>
                                        <Button variant="outline" class="justify-between w-full">
                                            {{ selectedIndex?.title ?? 'Select Index' }}
                                            <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                                        </Button>
                                    </ComboboxTrigger>
                                </ComboboxAnchor>

                                <ComboboxList class="max-h-80 w-full overflow-y-auto">
                                    <div class="relative w-full items-center">
                                        <ComboboxInput class="pl-9 focus-visible:ring-0 border-0 border-b rounded-none h-10" placeholder="Select Index..." />
                                        <span class="absolute start-0 inset-y-0 flex items-center justify-center px-3">
                                    <Search class="size-4 text-muted-foreground" />
                                  </span>
                                    </div>

                                    <ComboboxEmpty>
                                        No Indexes Found.
                                    </ComboboxEmpty>

                                    <ComboboxGroup>
                                        <ComboboxItem
                                            v-for="index in indices"
                                            :key="index.id"
                                            :value="index"
                                        >
                                            {{ index.title }}

                                            <Check v-if="index.slug === selectedIndex?.slug" :class="cn('ml-auto h-4 w-4')" />
                                        </ComboboxItem>
                                    </ComboboxGroup>
                                </ComboboxList>
                            </Combobox>
                            <div class="flex flex-col w-full mt-6 space-y-1.5">
                                <Label for="label">Index Label</Label>
                                <Input id="label" type="text" autofocus :tabindex="1" autocomplete="" v-model="indexText" placeholder="Index Label" />
                            </div>
                        </DialogDescription>

                    <DialogFooter>
                        <DialogClose as-child>
                            <Button @click="insertIndexTooltip">Add Tooltip</Button>
                        </DialogClose>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </div>
        <div>
            <Dialog>
                <DialogTrigger>
                    <Button type="button" @click="loadSections" class="p-2">Add Section Link</Button>
                </DialogTrigger>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Add Section Link</DialogTitle>
                    </DialogHeader>
                    <DialogDescription>
                        <Combobox v-model="selectedSection" by="label">
                            <ComboboxAnchor as-child>
                                <ComboboxTrigger as-child>
                                    <Button variant="outline" class="justify-between w-full">
                                        {{ selectedSection?.title ?? 'Select Section' }}
                                        <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                                    </Button>
                                </ComboboxTrigger>
                            </ComboboxAnchor>

                            <ComboboxList class="max-h-80 w-full overflow-y-auto">
                                <div class="relative w-full items-center">
                                    <ComboboxInput class="pl-9 focus-visible:ring-0 border-0 border-b rounded-none h-10" placeholder="Select Section..." />
                                    <span class="absolute start-0 inset-y-0 flex items-center justify-center px-3">
                                    <Search class="size-4 text-muted-foreground" />
                                  </span>
                                </div>

                                <ComboboxEmpty>
                                    No Sections Found.
                                </ComboboxEmpty>

                                <ComboboxGroup>
                                    <ComboboxItem
                                        v-for="section in sections"
                                        :key="section.slug"
                                        :value="section"
                                    >
                                        {{ section.title }}
                                        <Check v-if="section.slug === selectedSection?.slug" :class="cn('ml-auto h-4 w-4')" />
                                    </ComboboxItem>
                                </ComboboxGroup>
                            </ComboboxList>
                        </Combobox>
                        <div class="flex flex-col w-full mt-6 space-y-1.5">
                            <Label for="label">Section Label</Label>
                            <Input id="label" type="text" autofocus :tabindex="1" autocomplete="" v-model="sectionText" placeholder="Section Label" />
                        </div>
                    </DialogDescription>

                    <DialogFooter>
                        <DialogClose as-child>
                            <Button @click="insertSectionLink">Add Section Link</Button>
                        </DialogClose>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </div>
        <div>
            <Dialog>
                <DialogTrigger>
                    <Button type="button" @click="loadPages" class="p-2">Add Page Link</Button>
                </DialogTrigger>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Add Page Link</DialogTitle>
                    </DialogHeader>
                    <DialogDescription>
                        <Combobox v-model="selectedPage" by="label">
                            <ComboboxAnchor as-child>
                                <ComboboxTrigger as-child>
                                    <Button variant="outline" class="justify-between w-full">
                                        {{ selectedPage?.title ?? 'Select Page' }}
                                        <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                                    </Button>
                                </ComboboxTrigger>
                            </ComboboxAnchor>

                            <ComboboxList class="max-h-80 w-full overflow-y-auto">
                                <div class="relative w-full items-center">
                                    <ComboboxInput class="pl-9 focus-visible:ring-0 border-0 border-b rounded-none h-10" placeholder="Select Page..." />
                                    <span class="absolute start-0 inset-y-0 flex items-center justify-center px-3">
                                    <Search class="size-4 text-muted-foreground" />
                                  </span>
                                </div>

                                <ComboboxEmpty>
                                    No Pages Found.
                                </ComboboxEmpty>

                                <ComboboxGroup>
                                    <ComboboxItem
                                        v-for="page in pages"
                                        :key="page.slug"
                                        :value="page"
                                    >
                                        {{ page.title }}
                                        <Check v-if="page.slug === selectedPage?.slug" :class="cn('ml-auto h-4 w-4')" />
                                    </ComboboxItem>
                                </ComboboxGroup>
                            </ComboboxList>
                        </Combobox>
                        <div class="flex flex-col w-full mt-6 space-y-1.5">
                            <Label for="label">Page Link Label</Label>
                            <Input id="label" type="text" autofocus :tabindex="1" autocomplete="" v-model="pageText" placeholder="Page Link Label" />
                        </div>
                    </DialogDescription>

                    <DialogFooter>
                        <DialogClose as-child>
                            <Button @click="insertPageLink">Add Page Link</Button>
                        </DialogClose>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </div>
    </div>
    <div class="flex gap-1">
        <div class="my-auto">Page Elements: </div>
        <div>
            <Dialog>
                <DialogTrigger>
                    <Button type="button" @click="loadIndices" class="p-2">Add Index</Button>
                </DialogTrigger>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Add Index</DialogTitle>
                    </DialogHeader>
                    <DialogDescription>
                        <Combobox v-model="selectedIndex" by="label">
                            <ComboboxAnchor as-child>
                                <ComboboxTrigger as-child>
                                    <Button variant="outline" class="justify-between w-full">
                                        {{ selectedIndex?.title ?? 'Select Index' }}
                                        <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                                    </Button>
                                </ComboboxTrigger>
                            </ComboboxAnchor>

                            <ComboboxList class="max-h-80 w-full overflow-y-auto">
                                <div class="relative w-full items-center">
                                    <ComboboxInput class="pl-9 focus-visible:ring-0 border-0 border-b rounded-none h-10" placeholder="Select Index..." />
                                    <span class="absolute start-0 inset-y-0 flex items-center justify-center px-3">
                                    <Search class="size-4 text-muted-foreground" />
                                  </span>
                                </div>

                                <ComboboxEmpty>
                                    No Indexes Found.
                                </ComboboxEmpty>

                                <ComboboxGroup>
                                    <ComboboxItem
                                        v-for="index in indices"
                                        :key="index.slug"
                                        :value="index"
                                    >
                                        {{ index.title }}

                                        <Check v-if="index.slug === selectedIndex?.slug" :class="cn('ml-auto h-4 w-4')" />
                                    </ComboboxItem>
                                </ComboboxGroup>
                            </ComboboxList>
                        </Combobox>
                    </DialogDescription>

                    <DialogFooter>
                        <DialogClose as-child>
                            <Button @click="insertPageIndex">Add Index</Button>
                        </DialogClose>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </div>
        <div>
            <Dialog>
                <DialogTrigger>
                    <Button type="button" @click="loadSections" class="p-2">Add Section</Button>
                </DialogTrigger>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Add Section</DialogTitle>
                    </DialogHeader>
                    <DialogDescription>
                        <Combobox v-model="selectedSection" by="label">
                            <ComboboxAnchor as-child>
                                <ComboboxTrigger as-child>
                                    <Button variant="outline" class="justify-between w-full">
                                        {{ selectedSection?.title ?? 'Select Section' }}
                                        <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                                    </Button>
                                </ComboboxTrigger>
                            </ComboboxAnchor>

                            <ComboboxList class="max-h-80 w-full overflow-y-auto">
                                <div class="relative w-full items-center">
                                    <ComboboxInput class="pl-9 focus-visible:ring-0 border-0 border-b rounded-none h-10" placeholder="Select Section..." />
                                    <span class="absolute start-0 inset-y-0 flex items-center justify-center px-3">
                                    <Search class="size-4 text-muted-foreground" />
                                  </span>
                                </div>

                                <ComboboxEmpty>
                                    No Sections Found.
                                </ComboboxEmpty>

                                <ComboboxGroup>
                                    <ComboboxItem
                                        v-for="section in sections"
                                        :key="section.slug"
                                        :value="section"
                                    >
                                        {{ section.title }}

                                        <Check v-if="section.slug === selectedSection?.slug" :class="cn('ml-auto h-4 w-4')" />
                                    </ComboboxItem>
                                </ComboboxGroup>
                            </ComboboxList>
                        </Combobox>
                    </DialogDescription>

                    <DialogFooter>
                        <DialogClose as-child>
                            <Button @click="insertPageSection">Add Section</Button>
                        </DialogClose>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </div>
        <Button type="button" variant="default" @click="horizontal" class="p-2">Horizontal Split</Button>
    </div>
    <div class="flex gap-1">
        <div class="my-auto">Icons: </div>
        <Button type="button" variant="default" class="p-2" @click="addIcon('crow')"><Crow class-name="h-6" :mode="currentTheme === 'light' ? 'dark' : 'light'" /></Button>
        <Button type="button" variant="default" class="p-2" @click="addIcon('magic')"><Magic class-name="h-6" :mode="currentTheme === 'light' ? 'dark' : 'light'" /></Button>
        <Button type="button" variant="default" class="p-2" @click="addIcon('magicaldefense')"><MagicalDefense class-name="h-6" :mode="currentTheme === 'light' ? 'dark' : 'light'" /></Button>
        <Button type="button" variant="default" class="p-2" @click="addIcon('mask')"><Mask class-name="h-6" :mode="currentTheme === 'light' ? 'dark' : 'light'" /></Button>
        <Button type="button" variant="default" class="p-2" @click="addIcon('melee')"><Melee class-name="h-6" :mode="currentTheme === 'light' ? 'dark' : 'light'" /></Button>
        <Button type="button" variant="default" class="p-2" @click="addIcon('missile')"><Missile class-name="h-6" :mode="currentTheme === 'light' ? 'dark' : 'light'" /></Button>
        <Button type="button" variant="default" class="p-2" @click="addIcon('negative')"><Negative class-name="h-6" :mode="currentTheme === 'light' ? 'dark' : 'light'" /></Button>
        <Button type="button" variant="default" class="p-2" @click="addIcon('physicaldefense')"><PhysicalDefense class-name="h-6" :mode="currentTheme === 'light' ? 'dark' : 'light'" /></Button>
        <Button type="button" variant="default" class="p-2" @click="addIcon('positive')"><Positive class-name="h-6" :mode="currentTheme === 'light' ? 'dark' : 'light'" /></Button>
        <Button type="button" variant="default" class="p-2" @click="addIcon('pulse')"><Pulse class-name="h-6" :mode="currentTheme === 'light' ? 'dark' : 'light'" /></Button>
        <Button type="button" variant="default" class="p-2" @click="addIcon('ram')"><Ram class-name="h-6" :mode="currentTheme === 'light' ? 'dark' : 'light'" /></Button>
        <Button type="button" variant="default" class="p-2" @click="addIcon('signatureaction')"><SignatureAction class-name="h-6" :mode="currentTheme === 'light' ? 'dark' : 'light'" /></Button>
        <Button type="button" variant="default" class="p-2" @click="addIcon('soulstone')"><Soulstone class-name="h-6" :mode="currentTheme === 'light' ? 'dark' : 'light'" /></Button>
        <Button type="button" variant="default" class="p-2" @click="addIcon('tome')"><Tome class-name="h-6" :mode="currentTheme === 'light' ? 'dark' : 'light'" /></Button>
        <Button type="button" variant="default" class="p-2" @click="addIcon('unusualdefense')"><UnusualDefense class-name="h-6" :mode="currentTheme === 'light' ? 'dark' : 'light'" /></Button>
    </div>
    <Textarea class="min-h-75" id="text_editor" v-model="model" :placeholder="props.placeholder" />
</template>
