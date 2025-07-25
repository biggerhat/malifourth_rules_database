<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import Button from "@/components/ui/button/Button.vue";

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
        <Button
            v-if="showButton"
            @click="scrollToTop"
            class="fixed bottom-14 right-6 z-50 p-3 rounded-full bg-blue-600 text-white shadow-lg hover:bg-blue-700 opacity-70 hover:opacity-100 transition"
            aria-label="Scroll to Top"
        >
            ↑
        </Button>
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
