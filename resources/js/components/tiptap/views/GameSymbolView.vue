<script setup lang="ts">
import { NodeViewWrapper } from '@tiptap/vue-3';
import { SYMBOL_FONT_MAP, type SymbolName } from '../extensions/GameSymbol';
import { computed } from 'vue';

const props = defineProps<{
    node: { attrs: { symbol: SymbolName } };
}>();

const char = computed(() => SYMBOL_FONT_MAP[props.node.attrs.symbol] ?? '?');
const label = computed(() => props.node.attrs.symbol);
</script>

<template>
    <NodeViewWrapper as="span" class="inline">
        <span class="font-[symbolFont] text-2xl" :title="label">{{ char }}</span>
    </NodeViewWrapper>
</template>
