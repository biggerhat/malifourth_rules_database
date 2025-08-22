<script setup lang="ts">
import {h, defineProps, onMounted, ref} from 'vue'
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
import {CircleXIcon, CircleMinusIcon, CirclePlusIcon, CircleEllipsisIcon} from "lucide-vue-next";
import DragDropTextContent from "@/components/DragDropTextContent.vue";

const props = defineProps({
    element: {
        type: [Object, Array, String],
        required: true,
    },
    uniqueIndex: {
        type: Number,
        required: true,
    }
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
    magicaldefense: MagicalDefense,
    mask: Mask,
    melee: Melee,
    missile: Missile,
    negative: Negative,
    physicaldefense: PhysicalDefense,
    positive: Positive,
    pulse: Pulse,
    ram: Ram,
    signatureaction: SignatureAction,
    soulstone: Soulstone,
    tome: Tome,
    unusualdefense: UnusualDefense,
}

// Fallback component for unknown tags
const UnknownTag = {
    props: ['text'],
    template: `<span style="color: red;">[Unknown tag]</span>`
}

// Determines the component to use for a content item
function resolveComponent(item) {
    if (item.text !== undefined) {
        return {
            props: ['text'],
            render() {
                return h('div', { innerHTML: this.text })
            }
        }
    }

    const tag = Object.keys(item)[0]
    return componentMap[tag] || UnknownTag
}

// Extracts props from a content item and supports recursive rendering
function getProps(item) {
    if (item.text !== undefined) {
        return { text: item.text }
    }

    const tag = Object.keys(item)[0]
    const data = item[tag]

    const props = { ...data }

    if (Array.isArray(data.text)) {
        props.text = h(ParsedContent, { content: data.text })
    }

    return props
}

const collapsed = ref(false);

const toggleCollapse = () => {
    collapsed.value = !collapsed.value;
}

const emit = defineEmits([
    'update:elementContent'
]);

const updateTextOrder = (newOrder) => {
    emit('update:elementContent', {'text': newOrder, 'uniqueIndex': props.uniqueIndex});
};
</script>

<template>
    <div class="border border-blue-400 my-1">
        <div class="block flex justify-between bg-background text-sm p-1">
            <div>{{ getProps(element).title ?? 'Text' }}</div>
            <div>
                <CircleMinusIcon v-if="!collapsed" @click="toggleCollapse()" class="w-5 text-primary inline mr-1" />
                <CirclePlusIcon v-else @click="toggleCollapse()" class="w-5 text-primary inline mr-1" />
                <CircleEllipsisIcon class="w-5 mr-1 text-green-500 inline" />
                <CircleXIcon class="w-5 text-red-500 inline" />
            </div>
        </div>
        <transition name="fade">
            <div class="px-1" v-show="!collapsed">
                <DragDropTextContent
                    @update:text-order="updateTextOrder"
                    v-if="element.text !== undefined"
                    :content="element.text"
                />
                <component
                    v-else
                    :is="resolveComponent(element)"
                    v-bind="getProps(element)"
                />
            </div>
        </transition>
    </div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease, transform 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
    transform: scaleY(0.95);
}
</style>
