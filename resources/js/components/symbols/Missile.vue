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
    <img v-if="props.mode === 'light'" src='/Images/Symbols/M4E-Symbol_Missile-Black.png' :class="className" class="my-auto inline-block" alt="Missile" />
    <img v-else src='/Images/Symbols/M4E-Symbol_Missile-White.png' :class="className" class="my-auto inline-block" alt="Missile" />
</template>
