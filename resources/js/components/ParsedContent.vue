<script setup lang="ts">
import {h, defineProps, onMounted} from 'vue'


import IndexTooltip from "@/components/IndexTooltip.vue";
import Warding from "@/components/symbols/Warding.vue";
import Crow from "@/components/symbols/Crow.vue";
import Magic from "@/components/symbols/Magic.vue";
import Mask from "@/components/symbols/Mask.vue";
import Melee from "@/components/symbols/Melee.vue";
import Missile from "@/components/symbols/Missile.vue";
import Negative from "@/components/symbols/Negative.vue";
import Fortitude from "@/components/symbols/Fortitude.vue";
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

const props = defineProps({
    content: {
        type: [Object, Array, String],
        required: true
    }
})

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
                return h('span', { innerHTML: this.text })
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
</script>

<template>
    <div>
        <template v-for="(item, index) in content" :key="index">
            <component
                :is="resolveComponent(item)"
                v-bind="getProps(item)"
            />
        </template>
    </div>
</template>
