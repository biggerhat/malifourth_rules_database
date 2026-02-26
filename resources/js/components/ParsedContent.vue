<script setup lang="ts">
import { h, defineProps } from 'vue';
import { COMPONENT_MAP } from '@/lib/content-tags';
import ParsedContent from '@/components/ParsedContent.vue';

const props = defineProps({
    content: {
        type: [Object, Array, String],
        required: true
    }
});

const UnknownTag = {
    props: ['text'],
    template: `<span style="color: red;">[Unknown tag]</span>`
};

function resolveComponent(item) {
    if (item.text !== undefined) {
        return {
            props: ['text'],
            render() {
                return h('span', { innerHTML: this.text });
            }
        };
    }

    const tag = Object.keys(item)[0];
    return COMPONENT_MAP[tag] || UnknownTag;
}

function getProps(item) {
    if (item.text !== undefined) {
        return { text: item.text };
    }

    const tag = Object.keys(item)[0];
    const data = item[tag];
    const itemProps = { ...data };

    if (Array.isArray(data.text)) {
        itemProps.text = h(ParsedContent, { content: data.text });
    }

    return itemProps;
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
