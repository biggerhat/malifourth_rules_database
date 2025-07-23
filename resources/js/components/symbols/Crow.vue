<script setup lang="ts">
const props = defineProps({
    mode: {
        type: String,
        required: false,
        default() {
            return localStorage.appearance;
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
</script>

<template>
    <img v-if="props.mode === 'light'" src='/Images/Symbols/M4E-Symbol_Crow-Black.png' :class="className" class="my-auto inline-block" alt="Crow" />
    <img v-else src='/Images/Symbols/M4E-Symbol_Crow-White.png' :class="className" alt="Crow" class="my-auto inline-block" />
</template>
