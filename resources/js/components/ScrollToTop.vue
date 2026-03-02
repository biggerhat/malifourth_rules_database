<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import { ArrowUp } from 'lucide-vue-next'

const showButton = ref(false)

const toggleVisibility = () => {
    showButton.value = window.scrollY > 300
};

const scrollToTop = () => {
    window.scrollTo({ top: 0, behavior: 'smooth' })
};

onMounted(() => {
    window.addEventListener('scroll', toggleVisibility)
});

onUnmounted(() => {
    window.removeEventListener('scroll', toggleVisibility)
});
</script>

<template>
    <transition name="fade">
        <button
            v-if="showButton"
            @click="scrollToTop"
            class="fixed bottom-6 right-4 sm:bottom-8 sm:right-6 z-50 size-10 inline-flex items-center justify-center rounded-full bg-primary text-primary-foreground shadow-lg opacity-70 hover:opacity-100 transition"
            aria-label="Scroll to Top"
        >
            <ArrowUp class="size-4" />
        </button>
    </transition>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
