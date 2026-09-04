<script setup lang="ts">
import ScrollToTop from "@/components/ScrollToTop.vue";
import SeoHead from "@/components/SeoHead.vue";
import { Link } from "@inertiajs/vue3";
import { ChevronRight } from "lucide-vue-next";

interface FavoriteItem {
    type: string;
    title: string;
    url: string;
    favorited_at: string;
}

const props = defineProps<{
    favorites: FavoriteItem[];
}>();
</script>

<template>
    <SeoHead
        title="My Favorites"
        description="Your saved Malifaux rules, FAQs, and errata."
        :noindex="true"
    />

    <div class="max-w-4xl mx-auto px-2 sm:px-4 text-foreground leading-6 text-md">
        <div class="w-full max-w-2xl mx-auto text-center py-4">
            <img src='/Images/page_banner_top.png' alt="" class="w-40 sm:w-48 mx-auto opacity-90" />
            <h1 class="font-[family-name:var(--font-display)] text-2xl sm:text-3xl font-medium text-balance my-1">My Favorites</h1>
            <img src='/Images/page_banner_bottom.png' alt="" class="w-40 sm:w-48 mx-auto opacity-90" />
        </div>

        <div v-if="props.favorites.length" class="space-y-2 mb-8">
            <Link
                v-for="(item, idx) in props.favorites"
                :key="idx"
                :href="item.url"
                class="flex items-center justify-between rounded-lg border bg-card px-4 py-3 sm:px-5 sm:py-4 hover:bg-muted/50 transition-colors"
            >
                <div class="min-w-0 flex items-center gap-3">
                    <span class="shrink-0 rounded-full border border-border px-2 py-0.5 text-[0.65rem] font-medium uppercase tracking-wide text-muted-foreground">
                        {{ item.type }}
                    </span>
                    <span class="text-sm sm:text-base font-medium truncate" v-html="item.title"></span>
                </div>
                <ChevronRight class="size-4 shrink-0 text-muted-foreground ml-3" />
            </Link>
        </div>

        <div v-else class="rounded-lg border border-dashed py-10 text-center">
            <p class="text-sm text-muted-foreground">You haven't favorited anything yet.</p>
            <p class="text-xs text-muted-foreground mt-1">Look for the "Favorite" button on any rule, FAQ, or errata page.</p>
        </div>

        <ScrollToTop />
    </div>
</template>
