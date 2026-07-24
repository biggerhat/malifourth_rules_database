<script setup lang="ts">
import { computed, ref } from 'vue';

const props = defineProps({
    frontImage: {
        type: String,
        required: false,
        default: null
    },
    backImage: {
        type: String,
        required: false,
        default: null
    },
    alt: {
        type: String,
        required: false,
        default: ''
    }
});

const flipped = ref(false);
const canFlip = computed(() => !!props.frontImage && !!props.backImage);

const flip = () => {
    if (canFlip.value) {
        flipped.value = !flipped.value;
    }
};
</script>

<template>
    <div>
        <div
            class="relative mx-auto w-full"
            :class="canFlip ? 'cursor-pointer' : ''"
            style="perspective: 1000px"
            :role="canFlip ? 'button' : undefined"
            :tabindex="canFlip ? 0 : undefined"
            @click="flip"
            @keydown.enter="flip"
        >
            <div
                class="card-flip-inner relative w-full aspect-[5/7]"
                :class="{ 'card-flipped': flipped }"
                style="transition: transform 0.5s; transform-style: preserve-3d"
            >
                <div class="card-face absolute inset-0" style="backface-visibility: hidden">
                    <img
                        v-if="props.frontImage"
                        :src="props.frontImage"
                        :alt="props.alt"
                        loading="lazy"
                        decoding="async"
                        class="h-full w-full rounded-md border bg-muted object-contain"
                    />
                    <div v-else class="h-full w-full rounded-md border border-dashed flex items-center justify-center text-sm text-muted-foreground">
                        No card image
                    </div>
                </div>
                <div class="card-face absolute inset-0" style="backface-visibility: hidden; transform: rotateY(180deg)">
                    <img
                        v-if="props.backImage"
                        :src="props.backImage"
                        :alt="`${props.alt} back`"
                        loading="lazy"
                        decoding="async"
                        class="h-full w-full rounded-md border bg-muted object-contain"
                    />
                </div>
            </div>
        </div>
        <div v-if="canFlip" class="text-center text-xs text-muted-foreground mt-2">
            Click to flip
        </div>
    </div>
</template>

<style scoped>
.card-flipped {
    transform: rotateY(180deg);
}
</style>
