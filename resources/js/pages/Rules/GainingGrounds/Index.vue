<script setup lang="ts">
import ScrollToTop from "@/components/ScrollToTop.vue";
import { ChevronRight } from "lucide-vue-next";

const props = defineProps({
    seasons: {
        type: [Object, Array],
        required: false,
        default() {
            return [];
        }
    },
});
</script>

<template>
    <Head title="Gaining Grounds" />

    <div class="max-w-4xl mx-auto px-2 sm:px-4 text-primary leading-6 text-md">
        <div class="w-full text-center text-xl py-4">
            <img src='/Images/page_banner_top.png' alt="" class="w-3/4 sm:w-1/2 lg:w-1/3 mx-auto" />
            <span>Gaining Grounds</span>
            <img src='/Images/page_banner_bottom.png' alt="" class="w-3/4 sm:w-1/2 lg:w-1/3 mx-auto" />
        </div>

        <div class="space-y-2">
            <Link
                v-for="season in props.seasons"
                :key="season.id"
                :href="route('rules.gaining-grounds.season', season.slug)"
                class="flex items-center justify-between rounded-lg border bg-card px-4 py-3 sm:px-5 sm:py-4 hover:bg-muted/50 transition-colors"
            >
                <div class="min-w-0">
                    <div class="text-sm sm:text-base font-medium">{{ season.title }}</div>
                    <div class="text-xs text-muted-foreground mt-0.5">Published {{ season.published_at }}</div>
                </div>
                <ChevronRight class="size-4 shrink-0 text-muted-foreground ml-3" />
            </Link>

            <div v-if="!props.seasons.length" class="rounded-lg border border-dashed py-10 text-center">
                <p class="text-sm text-muted-foreground">No Gaining Grounds seasons have been published yet.</p>
            </div>
        </div>

        <ScrollToTop />
    </div>
</template>
