<script setup lang="ts">
import ScrollToTop from "@/components/ScrollToTop.vue";
import { ChevronRight } from "lucide-vue-next";

const props = defineProps({
    rulesErrata: {
        type: [Object, Array],
        required: false,
        default() {
            return [];
        }
    },
    generalErrata: {
        type: [Object, Array],
        required: false,
        default() {
            return [];
        }
    },
});
</script>

<template>
    <Head title="Errata" />

    <div class="max-w-4xl mx-auto px-2 sm:px-4 text-primary leading-6 text-md">
        <div class="w-full text-center text-xl py-4">
            <img src='/Images/page_banner_top.png' alt="" class="w-3/4 sm:w-1/2 lg:w-1/3 mx-auto" />
            <span>Errata</span>
            <img src='/Images/page_banner_bottom.png' alt="" class="w-3/4 sm:w-1/2 lg:w-1/3 mx-auto" />
        </div>

        <!-- Rules Errata Section -->
        <div v-if="props.rulesErrata.length" class="mb-8">
            <h2 class="font-semibold text-lg mb-4 pb-2 border-b">Rules Errata</h2>
            <div class="space-y-2">
                <Link
                    v-for="batch in props.rulesErrata"
                    :key="batch.id"
                    :href="route('errata.batch', batch.slug)"
                    class="flex items-center justify-between rounded-lg border bg-card px-4 py-3 sm:px-5 sm:py-4 hover:bg-muted/50 transition-colors"
                >
                    <div class="min-w-0">
                        <div class="text-sm sm:text-base font-medium">{{ batch.title }}</div>
                        <div class="text-xs text-muted-foreground mt-0.5">Published {{ batch.published_at }}</div>
                    </div>
                    <ChevronRight class="size-4 shrink-0 text-muted-foreground ml-3" />
                </Link>
            </div>
        </div>

        <!-- General Errata Section -->
        <div v-if="props.generalErrata.length" class="mb-8">
            <h2 class="font-semibold text-lg mb-4 pb-2 border-b">General Errata</h2>
            <div class="space-y-2">
                <Link
                    v-for="errata in props.generalErrata"
                    :key="errata.id"
                    :href="route('errata.view', errata.slug)"
                    class="flex items-center justify-between rounded-lg border bg-card px-4 py-3 sm:px-5 sm:py-4 hover:bg-muted/50 transition-colors"
                >
                    <div class="min-w-0">
                        <div class="text-sm sm:text-base font-medium">{{ errata.title }}</div>
                        <div class="text-xs text-muted-foreground mt-0.5">Published {{ errata.published_at }}</div>
                    </div>
                    <ChevronRight class="size-4 shrink-0 text-muted-foreground ml-3" />
                </Link>
            </div>
        </div>

        <div v-if="!props.rulesErrata.length && !props.generalErrata.length" class="rounded-lg border border-dashed py-10 text-center">
            <p class="text-sm text-muted-foreground">No errata have been published yet.</p>
        </div>

        <ScrollToTop />
    </div>
</template>
