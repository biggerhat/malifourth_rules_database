<script setup lang="ts">
import {
    Crow, Fortitude, Magic, Mask, Melee, Missile, Negative,
    Positive, Pulse, Ram, SignatureAction, Soulstone, Tome,
    UnusualDefense, Warding,
} from '@/components/symbols';
import { SYMBOL_TAGS } from '@/lib/content-tags';
import type { Component } from 'vue';

const symbolComponents: Record<string, Component> = {
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

const emit = defineEmits<{
    'insert-tag': [tag: string];
}>();
</script>

<template>
    <div class="flex gap-1 mb-2">
        <div
            v-for="tag in SYMBOL_TAGS"
            :key="tag"
            class="bg-primary rounded min-w-6 border border-primary text-secondary text-center"
            @click="emit('insert-tag', tag)"
        >
            <component :is="symbolComponents[tag]" />
        </div>
    </div>
</template>
