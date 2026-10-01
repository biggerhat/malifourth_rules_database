<script setup lang="ts">
import ScrollToTop from "@/components/ScrollToTop.vue";
import SeoHead from "@/components/SeoHead.vue";
import { Link } from "@inertiajs/vue3";
import { Rss } from "lucide-vue-next";

interface ChangelogEntry {
    type: string;
    type_label: string;
    title: string;
    excerpt: string | null;
    url: string;
    published_at: string;
}

interface Pagination {
    current_page: number;
    last_page: number;
    total: number;
}

const props = defineProps<{
    entries: ChangelogEntry[];
    pagination: Pagination;
}>();

const typeStyles: Record<string, string> = {
    errata: "bg-amber-500/10 text-amber-700 dark:text-amber-400",
    faq: "bg-sky-500/10 text-sky-700 dark:text-sky-400",
    card_errata: "bg-violet-500/10 text-violet-700 dark:text-violet-400",
};
</script>

<template>
    <SeoHead
        title="Changelog"
        description="Recent errata, FAQ, and card errata updates to the Malifaux 4th Edition rules."
    />

    <div class="max-w-3xl mx-auto px-2 sm:px-4 text-foreground leading-6 text-md">
        <div class="w-full max-w-2xl mx-auto text-center py-4">
            <img src='/Images/page_banner_top.png' alt="" class="w-40 sm:w-48 mx-auto opacity-90" />
            <h1 class="font-[family-name:var(--font-display)] text-2xl sm:text-3xl font-medium text-balance my-1">Changelog</h1>
            <img src='/Images/page_banner_bottom.png' alt="" class="w-40 sm:w-48 mx-auto opacity-90" />
        </div>

        <div class="flex justify-end mb-4 print:hidden">
            <a
                :href="route('changelog.feed')"
                class="inline-flex items-center gap-1.5 text-xs text-muted-foreground hover:text-foreground transition-colors"
            >
                <Rss class="size-3.5" />
                RSS feed
            </a>
        </div>

        <div v-if="props.entries.length" class="space-y-2">
            <Link
                v-for="entry in props.entries"
                :key="entry.type + entry.url"
                :href="entry.url"
                class="block rounded-lg border bg-card px-4 py-3 sm:px-5 sm:py-4 hover:bg-muted/50 transition-colors"
            >
                <div class="flex items-center gap-2 mb-1">
                    <span :class="['text-[0.65rem] font-medium uppercase tracking-wide rounded px-1.5 py-0.5', typeStyles[entry.type] ?? 'bg-muted text-muted-foreground']">
                        {{ entry.type_label }}
                    </span>
                    <span class="text-xs text-muted-foreground">Published {{ entry.published_at }}</span>
                </div>
                <div class="text-sm sm:text-base font-medium">{{ entry.title }}</div>
                <p v-if="entry.excerpt" class="text-xs sm:text-sm text-muted-foreground mt-1 line-clamp-2">{{ entry.excerpt }}</p>
            </Link>
        </div>

        <div v-else class="rounded-lg border border-dashed py-10 text-center">
            <p class="text-sm text-muted-foreground">No updates have been published yet.</p>
        </div>

        <div v-if="props.pagination.last_page > 1" class="flex items-center justify-center gap-2 mt-6 print:hidden">
            <Link
                v-if="props.pagination.current_page > 1"
                :href="route('changelog.index', { page: props.pagination.current_page - 1 })"
                class="text-sm rounded-md border px-3 py-1.5 hover:bg-muted/50 transition-colors"
            >
                Newer
            </Link>
            <span class="text-xs text-muted-foreground">Page {{ props.pagination.current_page }} of {{ props.pagination.last_page }}</span>
            <Link
                v-if="props.pagination.current_page < props.pagination.last_page"
                :href="route('changelog.index', { page: props.pagination.current_page + 1 })"
                class="text-sm rounded-md border px-3 py-1.5 hover:bg-muted/50 transition-colors"
            >
                Older
            </Link>
        </div>

        <ScrollToTop />
    </div>
</template>
