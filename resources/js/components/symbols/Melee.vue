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
    <img v-if="props.mode === 'light'" src='/Images/Symbols/M4E-Symbol_Melee-Black.png' :class="className" class="my-auto inline-block" alt="Melee" />
    <img v-else src='/Images/Symbols/M4E-Symbol_Melee-White.png' :class="className" class="my-auto inline-block" alt="Melee" />
</template>
