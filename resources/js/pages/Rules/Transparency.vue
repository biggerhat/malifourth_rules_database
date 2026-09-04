<script setup lang="ts">
import ScrollToTop from "@/components/ScrollToTop.vue";
import SeoHead from "@/components/SeoHead.vue";
import { Link } from "@inertiajs/vue3";

interface TransparencyEntry {
    type: string;
    type_label: string;
    title: string;
    url: string;
    published_at: string;
    published_by: string | null;
    approved_by: string | null;
    change_notes: string | null;
}

interface Pagination {
    current_page: number;
    last_page: number;
    total: number;
}

const props = defineProps<{
    entries: TransparencyEntry[];
    pagination: Pagination;
}>();

const typeStyles: Record<string, string> = {
    page: "bg-emerald-500/10 text-emerald-700 dark:text-emerald-400",
    section: "bg-emerald-500/10 text-emerald-700 dark:text-emerald-400",
    index: "bg-emerald-500/10 text-emerald-700 dark:text-emerald-400",
    errata: "bg-amber-500/10 text-amber-700 dark:text-amber-400",
    card_errata: "bg-violet-500/10 text-violet-700 dark:text-violet-400",
    faq: "bg-sky-500/10 text-sky-700 dark:text-sky-400",
    season: "bg-rose-500/10 text-rose-700 dark:text-rose-400",
    season_page: "bg-rose-500/10 text-rose-700 dark:text-rose-400",
    strategy: "bg-rose-500/10 text-rose-700 dark:text-rose-400",
    scheme: "bg-rose-500/10 text-rose-700 dark:text-rose-400",
};
</script>

<template>
    <SeoHead
        title="Transparency Log"
        description="A public record of who approved and published every currently-live rules page, errata, and FAQ on this site."
    />

    <div class="max-w-3xl mx-auto px-2 sm:px-4 text-foreground leading-6 text-md">
        <div class="w-full max-w-2xl mx-auto text-center py-4">
            <img src='/Images/page_banner_top.png' alt="" class="w-40 sm:w-48 mx-auto opacity-90" />
            <h1 class="font-[family-name:var(--font-display)] text-2xl sm:text-3xl font-medium text-balance my-1">Transparency Log</h1>
            <img src='/Images/page_banner_bottom.png' alt="" class="w-40 sm:w-48 mx-auto opacity-90" />
        </div>

        <p class="text-sm text-muted-foreground text-center max-w-xl mx-auto mb-6">
            Every piece of content on this site goes through review before it goes live. This is the public
            record of who approved and published each currently-live version, and when.
        </p>

        <div v-if="props.entries.length" class="space-y-2">
            <div
                v-for="entry in props.entries"
                :key="entry.type + entry.url"
                class="block rounded-lg border bg-card px-4 py-3 sm:px-5 sm:py-4"
            >
                <div class="flex items-center gap-2 mb-1 flex-wrap">
                    <span :class="['text-[0.65rem] font-medium uppercase tracking-wide rounded px-1.5 py-0.5', typeStyles[entry.type] ?? 'bg-muted text-muted-foreground']">
                        {{ entry.type_label }}
                    </span>
                    <span class="text-xs text-muted-foreground">Published {{ entry.published_at }}</span>
                </div>
                <Link :href="entry.url" class="text-sm sm:text-base font-medium hover:underline">{{ entry.title }}</Link>
                <p v-if="entry.change_notes" class="text-xs sm:text-sm text-muted-foreground mt-1 line-clamp-2">{{ entry.change_notes }}</p>
                <div class="flex items-center gap-3 mt-2 text-xs text-muted-foreground">
                    <span v-if="entry.approved_by">Approved by {{ entry.approved_by }}</span>
                    <span v-if="entry.published_by">Published by {{ entry.published_by }}</span>
                </div>
            </div>
        </div>

        <div v-else class="rounded-lg border border-dashed py-10 text-center">
            <p class="text-sm text-muted-foreground">No published content yet.</p>
        </div>

        <div v-if="props.pagination.last_page > 1" class="flex items-center justify-center gap-2 mt-6 print:hidden">
            <Link
                v-if="props.pagination.current_page > 1"
                :href="route('transparency.index', { page: props.pagination.current_page - 1 })"
                class="text-sm rounded-md border px-3 py-1.5 hover:bg-muted/50 transition-colors"
            >
                Newer
            </Link>
            <span class="text-xs text-muted-foreground">Page {{ props.pagination.current_page }} of {{ props.pagination.last_page }}</span>
            <Link
                v-if="props.pagination.current_page < props.pagination.last_page"
                :href="route('transparency.index', { page: props.pagination.current_page + 1 })"
                class="text-sm rounded-md border px-3 py-1.5 hover:bg-muted/50 transition-colors"
            >
                Older
            </Link>
        </div>

        <ScrollToTop />
    </div>
</template>
