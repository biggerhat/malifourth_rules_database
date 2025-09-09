<script setup lang="ts">
import { defineProps, h, ref } from 'vue';
import IndexTooltip from '@/components/IndexTooltip.vue';
import Warding from '@/components/symbols/Warding.vue';
import Crow from '@/components/symbols/Crow.vue';
import Magic from '@/components/symbols/Magic.vue';
import Mask from '@/components/symbols/Mask.vue';
import Melee from '@/components/symbols/Melee.vue';
import Missile from '@/components/symbols/Missile.vue';
import Negative from '@/components/symbols/Negative.vue';
import Fortitude from '@/components/symbols/Fortitude.vue';
import Positive from '@/components/symbols/Positive.vue';
import Pulse from '@/components/symbols/Pulse.vue';
import Ram from '@/components/symbols/Ram.vue';
import SignatureAction from '@/components/symbols/SignatureAction.vue';
import Soulstone from '@/components/symbols/Soulstone.vue';
import Tome from '@/components/symbols/Tome.vue';
import UnusualDefense from '@/components/symbols/UnusualDefense.vue';
import ParsedContent from '@/components/ParsedContent.vue';
import IndexContent from '@/components/IndexContent.vue';
import SectionContent from '@/components/SectionContent.vue';
import SectionLink from '@/components/SectionLink.vue';
import PageLink from '@/components/PageLink.vue';
import ExternalLink from '@/components/ExternalLink.vue';
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
        default() {
            return {};
        },
    },
    pages: {
        type: [Object, Array],
        required: false,
        default() {
            return {};
        },
    },
    sections: {
        type: [Object, Array],
        required: false,
        default() {
            return {};
        },
    },
});

// Maps tag names to component files
const componentMap = {
    indexTooltip: IndexTooltip,
    index: IndexContent,
    section: SectionContent,
    sectionLink: SectionLink,
    pageLink: PageLink,
    Link: ExternalLink,
    crow: Crow,
    magic: Magic,
    warding: Warding,
    mask: Mask,
    melee: Melee,
    missile: Missile,
    negative: Negative,
    fortitude: Fortitude,
    positive: Positive,
    pulse: Pulse,
    ram: Ram,
    signatureaction: SignatureAction,
    soulstone: Soulstone,
    tome: Tome,
    unusualdefense: UnusualDefense,
};

const editorOpen = ref(false);

// Fallback component for unknown tags
const UnknownTag = {
    props: ['text'],
    template: `<span style="color: red;">[Unknown tag]</span>`,
};

// Determines the component to use for a content item
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
    return componentMap[tag] || UnknownTag;
}

// Extracts props from a content item and supports recursive rendering
function getProps(item) {
    if (item.text !== undefined) {
        return { text: item.text };
    }

    const tag = Object.keys(item)[0];
    const data = item[tag];

    const props = { ...data };

    if (Array.isArray(data.text)) {
        props.text = h(ParsedContent, { content: data.text });
    }

    return props;
}

const collapsed = ref(false);

const toggleCollapse = () => {
    collapsed.value = !collapsed.value;
};

const emit = defineEmits(['update:elementContent', 'delete:element']);

const normalizedContent = ref(null);
const openEditor = () => {
    normalizedContent.value = normalizeElement(props.element.text);
    editorOpen.value = true;
};

const normalizeElement = (segments) => {
    let normalized = "";
    segments.forEach((segment) => {
        const key = Object.keys(segment)[0];
        const value = segment[key];

        if (key === 'text') {
            normalized += htmlToTags(segment[key]);
        } else if (value.content)  {
            normalized += `{{${key}=${segment[key]['slug']}}}`;
            let content = normalizeElement(segment[key]['content']);
            normalized += `${content}{{/${key}}}`;
        } else {
            normalized += `{{${key} /}}`;
        }
    })

    return normalized;
}

const htmlToTags = (text) => {
    const replacements = {
        '<strong>': '{{b}}',
        '</strong>': '{{/b}}',
        '<b>': '{{b}}',
        '</b>': '{{/b}}',
        '<em>': '{{i}}',
        '</em>': '{{/i}}',
        '<i>': '{{i}}',
        '</i>': '{{/i}}',
        '<br>': '\n',
        '<br/>': '\n',
        '<br />': '\n',
        '<u>': '{{u}}',
        '</u>': '{{/u}}',
    };

    let output = text;

    for (const [htmlTag, customTag] of Object.entries(replacements)) {
        const regex = new RegExp(htmlTag, 'gi');
        output = output.replace(regex, customTag);
    }

    return output;
}

const jsonToCustomTags = (segments) => {
    let content = '';
    segments.forEach((seg) => {
        switch (seg.type) {
            case 'strong':
                content += `{{b}}${jsonToCustomTags(seg.content)}{{/b}}`;
                break;
            case 'i':
                content += `{{i}}${jsonToCustomTags(seg.content)}{{/i}}`;
                break;
            case 'u':
                content += `{{u}}${jsonToCustomTags(seg.content)}{{/i}}`;
                break;
            case 'hr':
                content += `{{hr /}}`;
            case 'br':
                content += '\n';
                break;
            case 'text':
                content += jsonToCustomTags(seg.content);
                break;
            case 'indexTooltip':
                content += `{{indexTooltip=${seg.content.slug}}}${jsonToCustomTags(seg.content)}{{/indexTooltip}}`;
                break;
            case 'sectionLink':
                content += `{{sectionLink=${seg.content.slug}}}${jsonToCustomTags(seg.content)}{{/sectionLink}}`;
                break;
            case 'pageLink':
                content += `{{pageLink=${seg.content.slug}}}${jsonToCustomTags(seg.content)}{{/pageLink}}`;
                break;
            case 'Link':
                content += `{{Link=${seg.content.slug}}}${jsonToCustomTags(seg.content)}{{/Link}}`;
                break;
            case 'crow':
                content += '{{crow /}}';
                break;
            case 'magic':
                content += '{{magic /}}';
                break;
            case 'warding':
                content += '{{warding /}}';
                break;
            case 'mask':
                content += '{{mask /}}';
                break;
            case 'melee':
                content += '{{melee /}}';
                break;
            case 'missile':
                content += '{{missile /}}';
                break;
            case 'negative':
                content += '{{negative /}}';
                break;
            case 'fortitude':
                content += '{{fortitude /}}';
                break;
            case 'positive':
                content += '{{positive /}}';
                break;
            case 'pulse':
                content += '{{pulse /}}';
                break;
            case 'ram':
                content += '{{ram /}}';
                break;
            case 'signatureaction':
                content += '{{signatureaction /}}';
                break;
            case 'soulstone':
                content += '{{soulstone /}}';
                break;
            case 'tome':
                content += '{{tome /}}';
                break;
            case 'unusualdefense':
                content += '{{unusualdefense /}}';
                break;
            default:
                content += '';
                break;
        }
        content += ' ';
    });

    return content;
};

function customTagsToJson(str) {
    const result = [];
    const stack = [];

    // Normalize <br />
    str = str.replace(/<br\s*\/?>/gi, "{{__br__}}");

    const tagPattern = /\{\{(\/?[a-zA-Z]+|[a-zA-Z]+=[^}]+|[a-zA-Z]+\s*\/|__br__)\}\}/g;

    let lastIndex = 0;
    let match;

    while ((match = tagPattern.exec(str)) !== null) {
        const before = str.slice(lastIndex, match.index);

        // Only push plain text if NOT inside an open tag
        if (before && stack.length === 0) {
            result.push({ type: "text", content: before });
        }

        const tag = match[1];
        lastIndex = tagPattern.lastIndex;

        if (tag === "__br__") {
            result.push({ type: "br", content: "" });
            continue;
        }

        // Closing tag
        if (tag.startsWith("/")) {
            const open = stack.pop();
            if (open) {
                const inner = str.slice(open.end, match.index);
                switch (open.type) {
                    case "b":
                        result.push({ type: "strong", content: inner });
                        break;
                    case "i":
                    case "u":
                        result.push({ type: open.type, content: inner });
                        break;
                    case "indexTooltip":
                    case "sectionLink":
                    case "pageLink":
                    case "Link":
                        result.push({
                            type: open.type,
                            content: {
                                slug: open.slug,
                                text: inner,
                                inline: true
                            }
                        });
                        break;
                    default:
                        result.push({ type: "text", content: inner });
                }
            }
        }
        // Self-closing tags (like {{mask /}})
        else if (tag.endsWith("/")) {
            const type = tag.replace("/", "").trim();
            result.push({ type, content: { inline: true } });
        }
        // Opening tag with slug (e.g. {{indexTooltip=slug}})
        else if (tag.includes("=")) {
            const [type, slug] = tag.split("=");
            stack.push({ type, slug, end: tagPattern.lastIndex });
        }
        // Simple opening tags ({{b}}, {{i}}, {{u}})
        else {
            stack.push({ type: tag, end: tagPattern.lastIndex });
        }
    }

    // Remaining plain text outside tags
    if (lastIndex < str.length && stack.length === 0) {
        result.push({ type: "text", content: str.slice(lastIndex) });
    }

    return result;
}

const removeElement = (uniqueIndex) => {
    emit('delete:element', uniqueIndex);
};

const updateTextOrder = (newOrder) => {
    emit('update:elementContent', { text: newOrder, uniqueIndex: props.uniqueIndex });
};

const changeTextContent = (content) => {
    let normalized = content.replace(/\n/g, "<br />");
    emit('update:elementContent', { text: customTagsToJson(normalized), uniqueIndex: props.uniqueIndex });
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
                <ParsedContent v-if="element.text !== undefined" :content="element.text" />
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
