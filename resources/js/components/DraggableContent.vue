<script setup lang="ts">
import {h, defineProps, onMounted, ref, computed} from 'vue'
import draggable from "vuedraggable";
import DragDropEditorContent from "@/components/DragDropEditorContent.vue";
import {Label} from "@/components/ui/label";
import {Sheet, SheetContent, SheetDescription, SheetHeader, SheetTitle, SheetTrigger} from "@/components/ui/sheet";
import {Tooltip, TooltipContent, TooltipProvider, TooltipTrigger} from "@/components/ui/tooltip";
import {FileChartColumnIncreasing, SquareMinus, SquarePlus, LetterTextIcon, Check, X} from "lucide-vue-next";
import {Button} from "@/components/ui/button";
import {Tabs, TabsContent, TabsList, TabsTrigger} from "@/components/ui/tabs";
import {Input} from "@/components/ui/input";
import DragDropTextEditor from "@/components/DragDropTextEditor.vue";

const props = defineProps({
    content: {
        type: [Object, Array, String],
        required: true
    },
    label: {
        type: String,
        required: false,
        default () {
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
});

const parsedContent = ref([]);
const inlinedContent = ref([]);

onMounted(() => {
    const contentCopy = JSON.parse(JSON.stringify(props.content));
    parsedContent.value = mergeTextAndInline(contentCopy);
    inlinedContent.value = createDragDropObject(contentCopy);
});

const emit = defineEmits([
    'update:contentOrder',
    'update:newContent'
]);

const contentChange = (event) => {
    emit('update:contentOrder', serializeContent(parsedContent.value));
};

const updateElementContent = (newContent) => {
    parsedContent.value.map(block => {
        if (block.uniqueIndex === newContent.uniqueIndex) {
            block.text = newContent.text;
        }
        return block;
    });
    contentChange();
}

const removeElement = (uniqueIndex) => {
    const index = parsedContent.value.findIndex(item => item.uniqueIndex === uniqueIndex);
    if (index !== -1) {
        parsedContent.value.splice(index, 1);
        let currentContent = serializeContent(parsedContent.value);
        emit('update:newContent', currentContent);
    }
};

function serializeContent(blocks) {
    let result = ""

    for (const block of blocks) {
        if (block.text) {
            for (const item of block.text) {
                switch (item.type) {
                    case "text":
                        result += htmlToMarkdown(item.content);
                        break;
                    case "b":
                    case "strong":
                        result += `{{b}}${htmlToMarkdown(item.content)}{{/b}} `
                        break;
                    case "i":
                    case "em":
                        result += `{{i}}${htmlToMarkdown(item.content)}{{/i}} `
                        break;
                    case "u":
                        result += `{{u}}${htmlToMarkdown(item.content)}{{/u}} `
                        break;
                    case "xl":
                        result += `{{xl}}${htmlToMarkdown(item.content)}{{/xl}} `;
                        break;
                    case "lg":
                        result += `{{lg}}${htmlToMarkdown(item.content)}{{/lg}} `;
                        break;
                    case "sm":
                        result += `{{sm}}${htmlToMarkdown(item.content)}{{/sm}} `;
                        break;
                    case "xs":
                        result += `{{xs}}${htmlToMarkdown(item.content)}{{/xs}} `;
                        break;
                    case "mask":
                        result += `{{mask /}} `;
                        break;
                    case "crow":
                        result += `{{crow /}} `;
                        break;
                    case "magic":
                        result += `{{magic /}} `;
                        break;
                    case "warding":
                        result += `{{warding /}} `;
                        break;
                    case "melee":
                        result += `{{melee /}} `;
                        break;
                    case "missile":
                        result += `{{missile /}} `;
                        break;
                    case "negative":
                        result += `{{negative /}} `;
                        break;
                    case "fortitude":
                        result += `{{fortitude /}} `;
                        break;
                    case "positive":
                        result += `{{positive /}} `;
                        break;
                    case "pulse":
                        result += `{{pulse /}} `;
                        break;
                    case "ram":
                        result += `{{ram /}} `;
                        break;
                    case "signatureaction":
                        result += `{{signatureaction /}} `;
                        break;
                    case "soulstone":
                        result += `{{soulstone /}} `;
                        break;
                    case "tome":
                        result += `{{tome /}} `;
                        break;
                    case "unusualdefense":
                        result += `{{unusualdefense /}} `;
                        break;
                    case "br":
                        result += "\n"
                        break;
                    case "indexTooltip":
                        if (item.content && typeof item.content === "object") {
                            result += `{{indexTooltip=${item.content.slug}}}${htmlToMarkdown(item.content.text)}{{/indexTooltip}} `;
                        }
                        break;
                    case "sectionLink":
                        if (item.content && typeof item.content === "object") {
                            result += `{{sectionLink=${item.content.slug}}}${htmlToMarkdown(item.content.text)}{{/sectionLink}} `;
                        }
                        break;
                    case "pageLink":
                        if (item.content && typeof item.content === "object") {
                            result += `{{pageLink=${item.content.slug}}}${htmlToMarkdown(item.content.text)}{{/pageLink}} `;
                        }
                        break;
                    case "Link":
                        if (item.content && typeof item.content === "object") {
                            result += `{{Link=${item.content.slug}}}${htmlToMarkdown(item.content.text)}{{/Link}} `;
                        }
                        break;
                    default:
                        result += htmlToMarkdown(item.content) + ' ' || ""
                }
            }
        } else if (block.index) {
            // Normalize an index as a self-closing tag
            result += `{{index=${block.index.slug} /}} `;
        } else if (block.section) {
            // Normalize a section as a self-closing tag
            result += `{{section=${block.section.slug} /}} `;
        }
    }

    return result
}

const createDragDropObject = (input) => {
    const output = [];
    let currentIndex = 0;
    let currentInlineParts = [];

    const flushInline = () => {
        if (currentInlineParts.length) {
            output.push({text: currentInlineParts, uniqueIndex: currentIndex});
            currentInlineParts = [];
            currentIndex++;
        }
    }

    for (const item of input) {
        const key = Object.keys(item)[0];
        const value = item[key];

        if (key === "text") {
            currentInlineParts.push({text: value});
        } else if (value && value.inline === true) {
            currentInlineParts.push({[key]: value});
        } else {
            flushInline();
            output.push({ [key]: value, uniqueIndex: currentIndex });
            currentIndex++;
        }
    }

    flushInline();
    return output;
}

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
                if (part.type === "text") {
                    // split into words, ignore empty/space-only
                    const words = part.content.split(/\s+/).filter(Boolean);
                    for (const word of words) {
                        currentTextParts.push({ type: "text", content: word + ' ' });
                    }
                } else {
                    // inline elements remain intact
                    currentTextParts.push(part);
                }
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

const parseHtmlToTypedParts = (html) => {
    const parser = new DOMParser();
    const doc = parser.parseFromString(`<div>${html}</div>`, 'text/html');
    const container = doc.body.firstChild;
    const parts = [];

    function walk(node) {
        if (node.nodeType === Node.TEXT_NODE) {
            if (node.textContent.trim() !== '') {
                parts.push({ type: 'text', content: node.textContent });
            }
        } else if (node.nodeType === Node.ELEMENT_NODE) {
            const tagName = node.tagName.toLowerCase();

            // Self-closing tags or empty elements
            if (!node.hasChildNodes()) {
                parts.push({ type: tagName, content: '' });
            } else {
                // For tags with children, gather inner text content as 'content'
                // If you want nested tags split, modify this to walk children recursively
                parts.push({ type: tagName, content: node.textContent });
            }
        }
    }

    for (const child of container.childNodes) {
        walk(child);
    }

    return parts;
};

const normalizeJson = (json) => {
    return json.map(block => {
        // If it's just text
        if (block.text !== undefined) {
            return htmlToMarkdown(block.text);
        }

        // Otherwise, it's a tag object
        const key = Object.keys(block)[0];
        const value = block[key];

        // Ensure slug is there
        const slug = value.slug || '';

        if (value.text !== undefined) {
            const text = htmlToMarkdown(value.text);
            return `{{${key}=${slug}}}${text}{{/${key}}}`;
        }
        // If it has no content or empty array → self-closing
        if (!value.content || value.content.length === 0) {
            return `{{${key}=${slug} /}}`;
        }

        // Otherwise, wrap serialized content
        const inner = normalizeJson(value.content);
        return `{{${key}=${slug}}}${inner}{{/${key}}}`;
    }).join('');
};

const htmlToMarkdown = (html) => {
    return html
        // Handle <br> and <br />
        // .replace(/<br \/>/gi, '')

        // Bold <b> → {{b}}
        .replace(/<b>/gi, '{{b}}')
        .replace(/<\/b>/gi, '{{/b}} ')
        .replace(/\[mask]/gi, '{{mask /}} ')

        // Italic <i> → {{i}}
        .replace(/<i>/gi, '{{i}}')
        .replace(/<\/i>/gi, '{{/i}} ')

        // Underline <u> → {{u}}
        .replace(/<u>/gi, '{{u}}')
        .replace(/<\/u>/gi, '{{/u}} ')

        // Strong → {{b}}
        .replace(/<strong>/gi, '{{b}}')
        .replace(/<\/strong>/gi, '{{/b}} ')

        // Emphasis → {{i}}
        .replace(/<em>/gi, '{{i}}')
        .replace(/<\/em>/gi, '{{/i}} ')
}

const selectedIndex = ref(null);
const indexText = ref(null);
const selectedSection = ref(null);
const sectionText = ref(null);
const elementSheetOpen = ref(false);

const loadElements = () => {
    clearValues();
    elementSheetOpen.value = true;
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

const insertPageSection = () => {
    let currentContent = serializeContent(parsedContent.value);
    currentContent += `{{section=${selectedSection.value.slug} /}}`;
    elementSheetOpen.value = false;
    emit('update:newContent', currentContent);
}

const insertPageIndex = () => {
    let currentContent = serializeContent(parsedContent.value);
    currentContent += `{{index=${selectedIndex.value.slug} /}}`;
    elementSheetOpen.value = false;
    emit('update:newContent', currentContent);
}

const addNewTextContent = (newContent) => {
    let currentContent = serializeContent(parsedContent.value);
    currentContent += newContent;
    emit('update:newContent', currentContent);
};

//Start Text Editor Related Functions
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
            <Sheet :open="elementSheetOpen">
                <SheetTrigger>
                    <TooltipProvider>
                        <Tooltip>
                            <TooltipTrigger>
                                <div class="bg-primary rounded !p-1 border border-primary text-secondary" @click="elementSheetOpen = true"><FileChartColumnIncreasing class="mx-auto w-4 h-4" /></div>
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
    </div>
    <div>
        <!--        <draggable-->
        <!--            @change="contentChange"-->
        <!--            v-model="parsedContent"-->
        <!--            item-key="slug"-->
        <!--        >-->
        <!--            <template #item="{element}">-->
        <!--                <DragDropEditorContent-->
        <!--                    @update:element-content="updateElementContent"-->
        <!--                    @delete:element="removeElement"-->
        <!--                    :element="element"-->
        <!--                    :unique-index="element.uniqueIndex"-->
        <!--                    :indices="props.indices"-->
        <!--                    :sections="props.sections"-->
        <!--                    :pages="props.pages"-->
        <!--                />-->
        <!--            </template>-->
        <!--        </draggable>-->
        <draggable
            @change="contentChange"
            v-model="inlinedContent"
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
