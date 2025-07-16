<script setup lang="ts">
import { computed } from 'vue';
import {HTMLAttributes} from "vue";

defineOptions({
    inheritAttrs: false,
});

const props = defineProps({
    enforceBlack: {
        type: Boolean,
        required: false,
        default() {
            return false;
        }
    },
    className: {
        type: String,
        required: false,
        default() {
            return 'h-6 mx-1';
        }
    }
});

const currentTheme = computed(() => {
    return localStorage.theme;
});
</script>

<template>
    <img v-if="currentTheme === 'light' || props.enforceBlack" src='/Images/Symbols/M4E-Symbol_Magical-Def-Black.png' :class="className" class="my-auto inline-block" alt="Magical Defense" />
    <img v-else src='/Images/Symbols/M4E-Symbol_Magical-Def-White.png' :class="className" class="my-auto inline-block" alt="Magical Defense" />
</template>
