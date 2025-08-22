<script setup lang="ts">
import {h, defineProps, onMounted, ref} from 'vue'
import draggable from "vuedraggable";
import IndexTooltip from "@/components/IndexTooltip.vue";
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
import ParsedContent from "@/components/ParsedContent.vue";
import IndexContent from "@/components/IndexContent.vue";
import SectionView from "@/pages/Rules/SectionView.vue";
import SectionContent from "@/components/SectionContent.vue";
import SectionLink from "@/components/SectionLink.vue";
import PageLink from "@/components/PageLink.vue";
import ExternalLink from "@/components/ExternalLink.vue";
import { CircleXIcon, CircleMinusIcon } from "lucide-vue-next";
import DragDropEditorContent from "@/components/DragDropEditorContent.vue";

const props = defineProps({
    content: {
        type: [Object, Array, String],
        required: true
    },
    title: {
        type: [Object, Array, String],
        required: false,
        default () {
            return '';
        }
    }
});

const parsedContent = ref([]);

onMounted(() => {
    const contentCopy = JSON.parse(JSON.stringify(props.content));
    parsedContent.value = mergeTextAndInline(contentCopy);
});

const emit = defineEmits([
    'update:contentOrder'
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

function serializeContent(blocks) {
    let result = ""

    for (const block of blocks) {
        if (block.text) {
            for (const item of block.text) {
                switch (item.type) {
                    case "text":
                        result += htmlToMarkdown(item.content)
                        break
                    case "b":
                    case "strong":
                        result += `{{b}}${htmlToMarkdown(item.content)}{{/b}}`
                        break
                    case "i":
                    case "em":
                        result += `{{i}}${htmlToMarkdown(item.content)}{{/i}}`
                        break;
                    case "u":
                        result += `{{u}}${htmlToMarkdown(item.content)}{{/u}}`
                        break;
                    case "xl":
                        result += `{{xl}}${htmlToMarkdown(item.content)}{{/xl}}`;
                        break;
                    case "lg":
                        result += `{{lg}}${htmlToMarkdown(item.content)}{{/lg}}`;
                        break;
                    case "sm":
                        result += `{{sm}}${htmlToMarkdown(item.content)}{{/sm}}`;
                        break;
                    case "xs":
                        result += `{{xs}}${htmlToMarkdown(item.content)}{{/xs}}`;
                        break;
                    case "mask":
                        result += `{{mask /}}`;
                        break;
                    case "crow":
                        result += `{{crow /}}`;
                        break;
                    case "magic":
                        result += `{{magic /}}`;
                        break;
                    case "magicaldefense":
                        result += `{{magicaldefense /}}`;
                        break;
                    case "melee":
                        result += `{{melee /}}`;
                        break;
                    case "missile":
                        result += `{{missile /}}`;
                        break;
                    case "negative":
                        result += `{{negative /}}`;
                        break;
                    case "physicaldefense":
                        result += `{{physicaldefense /}}`;
                        break;
                    case "positive":
                        result += `{{positive /}}`;
                        break;
                    case "pulse":
                        result += `{{pulse /}}`;
                        break;
                    case "ram":
                        result += `{{ram /}}`;
                        break;
                    case "signatureaction":
                        result += `{{signatureaction /}}`;
                        break;
                    case "soulstone":
                        result += `{{soulstone /}}`;
                        break;
                    case "tome":
                        result += `{{tome /}}`;
                        break;
                    case "unusualdefense":
                        result += `{{unusualdefense /}}`;
                        break;
                    case "br":
                        result += "\n"
                        break
                    case "indexTooltip":
                        if (item.content && typeof item.content === "object") {
                            result += `{{indexTooltip=${item.content.slug}}}${htmlToMarkdown(item.content.text)}{{/indexTooltip}}`
                        }
                        break
                    default:
                        result += htmlToMarkdown(item.content) || ""
                }
            }
        } else if (block.index) {
            // Normalize an index as a self-closing tag
            result += `{{index=${block.index.slug} /}}`
        }
    }

    return result
}


const mergeTextAndInline = (input) => {
    const output = [];
    let currentTextParts = [];
    let currentIndex = 0;

    const flushText = () => {
        if (currentTextParts.length) {
            output.push({ text: currentTextParts, 'uniqueIndex': currentIndex });
            currentTextParts = [];
            currentIndex++;
        }
    };

    for (const item of input) {
        const key = Object.keys(item)[0];
        const value = item[key];

        // Treat 'text' keys as raw HTML to parse,
        // and inline elements (inline: true) as part of the text group
        if (key === 'text') {
            currentTextParts.push(...parseHtmlToTypedParts(value));
        } else if (value && value.inline === true) {
            currentTextParts.push({ type: key, content: value });
        } else {
            flushText();
            output.push({ [key]: value, 'uniqueIndex': currentIndex });
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
        .replace(/<\/b>/gi, '{{/b}}')
        .replace(/\[mask]/gi, '{{mask /}}')

        // Italic <i> → {{i}}
        .replace(/<i>/gi, '{{i}}')
        .replace(/<\/i>/gi, '{{/i}}')

        // Underline <u> → {{u}}
        .replace(/<u>/gi, '{{u}}')
        .replace(/<\/u>/gi, '{{/u}}')

        // Strong → {{b}}
        .replace(/<strong>/gi, '{{b}}')
        .replace(/<\/strong>/gi, '{{/b}}')

        // Emphasis → {{i}}
        .replace(/<em>/gi, '{{i}}')
        .replace(/<\/em>/gi, '{{/i}}')
}
</script>

<template>
    <div>
        <draggable
            @change="contentChange"
            v-model="parsedContent"
            item-key="slug"
        >
            <template #item="{element}">
                <DragDropEditorContent
                    @update:element-content="updateElementContent"
                    :element="element"
                    :unique-index="element.uniqueIndex"
                />
            </template>
        </draggable>
    </div>
</template>
