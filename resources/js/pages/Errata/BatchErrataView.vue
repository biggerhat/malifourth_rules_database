<script setup lang="ts">
import ParsedContent from "@/components/ParsedContent.vue";
import ScrollToTop from "@/components/ScrollToTop.vue";
import { ChevronLeft } from "lucide-vue-next";

const props = defineProps({
    batch: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <Head :title="props.batch.title" />

    <div class="max-w-4xl mx-auto px-2 sm:px-4 text-foreground leading-6 text-md">
        <!-- Back link -->
        <div class="pt-4 mb-2">
            <Link
                :href="route('errata.index')"
                class="inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-primary transition-colors"
            >
                <ChevronLeft class="size-4" />
                Errata
            </Link>
        </div>

        <!-- Banner -->
        <div class="w-full max-w-2xl mx-auto text-center py-4">
            <img src='/Images/page_banner_top.png' alt="" class="w-40 sm:w-48 mx-auto opacity-90" />
            <h1 class="font-[family-name:var(--font-display)] text-2xl sm:text-3xl font-medium text-balance my-1">{{ props.batch.title }}</h1>
            <img src='/Images/page_banner_bottom.png' alt="" class="w-40 sm:w-48 mx-auto opacity-90" />
        </div>

        <!-- Release Notes -->
        <div v-if="props.batch.release_notes && props.batch.release_notes.length > 0" class="mb-8 rounded-md border-t-2 border-primary bg-card shadow-sm">
            <div class="p-6">
                <ParsedContent :content="props.batch.release_notes" />
            </div>
            <div class="border-t border-border px-6 py-3 text-xs text-muted-foreground text-right">
                Published {{ props.batch.published_at }} by {{ props.batch.published_by }}
            </div>
        </div>

        <!-- Item Change Notes -->
        <div v-if="props.batch.item_change_notes.length" class="space-y-4 mb-8">
            <h2 class="text-[0.7rem] font-medium uppercase tracking-[0.14em] text-primary pb-2 border-b border-border">Changes</h2>
            <div v-for="(item, idx) in props.batch.item_change_notes" :key="idx" class="rounded-md border-t-2 border-primary bg-card shadow-sm p-6 space-y-2">
                <h3 class="font-medium" v-html="item.title"></h3>
                <div class="text-sm text-muted-foreground">
                    <ParsedContent :content="item.change_notes" />
                </div>
            </div>
        </div>

        <ScrollToTop />
    </div>
</template>
