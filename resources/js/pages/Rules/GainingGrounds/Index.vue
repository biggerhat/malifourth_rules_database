<script setup lang="ts">
import ContentReferences from "@/components/ContentReferences.vue";
import ParsedContent from "@/components/ParsedContent.vue";
import ScrollToTop from "@/components/ScrollToTop.vue";
import { ChevronRight } from "lucide-vue-next";
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectLabel,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select'
import { ref, watch } from "vue";

const props = defineProps({
    seasons: {
        type: [Object, Array],
        required: false,
        default() {
            return [];
        }
    },
    season: {
        type: Object,
        required: false,
        default() {
            return null;
        }
    },
    strategies: {
        type: [Object, Array],
        required: false,
        default() {
            return [];
        }
    },
    schemes: {
        type: [Object, Array],
        required: false,
        default() {
            return [];
        }
    },
    seasonPages: {
        type: [Object, Array],
        required: false,
        default() {
            return [];
        }
    },
    references: {
        type: Object,
        required: false,
        default() {
            return null;
        }
    },
});

const seasonParam = ref(props.season?.slug ?? '');

watch(seasonParam, (newSlug) => {
    if (newSlug && newSlug !== props.season?.slug) {
        window.location.href = route('rules.gaining-grounds.season', newSlug);
    }
});

const mobilePageSlug = ref('');
watch(mobilePageSlug, (newSlug) => {
    if (newSlug && props.season) {
        window.location.href = route('rules.gaining-grounds.season-page', [props.season.slug, newSlug]);
    }
});

const suitAccent = (suit: string) => {
    switch (suit) {
        case 'rams': return 'border-l-rose-800/60 dark:border-l-rose-400/50';
        case 'crows': return 'border-l-emerald-800/60 dark:border-l-emerald-400/50';
        case 'masks': return 'border-l-violet-800/60 dark:border-l-violet-400/50';
        case 'tomes': return 'border-l-sky-800/60 dark:border-l-sky-400/50';
        default: return '';
    }
};

const suitBadge = (suit: string) => {
    switch (suit) {
        case 'rams': return 'border border-border text-rose-700 dark:text-rose-300';
        case 'crows': return 'border border-border text-emerald-700 dark:text-emerald-300';
        case 'masks': return 'border border-border text-violet-700 dark:text-violet-300';
        case 'tomes': return 'border border-border text-sky-700 dark:text-sky-300';
        default: return 'border border-border text-muted-foreground';
    }
};

const suitSymbol = (suit: string) => {
    switch (suit) {
        case 'rams': return 'r';
        case 'crows': return 'c';
        case 'masks': return 'm';
        case 'tomes': return 't';
        default: return '';
    }
};
</script>

<template>
    <Head title="Gaining Grounds" />

    <div class="px-2 sm:px-4 lg:px-2 text-foreground leading-6 text-md" :class="props.season ? 'grid grid-cols-1 lg:grid-cols-8 lg:gap-2' : 'max-w-4xl mx-auto'">
        <!-- Sidebar: Season list (desktop) -->
        <div v-if="props.season" class="lg:col-span-2 hidden lg:block">
            <div class="sticky top-20 max-h-[calc(100vh-6rem)] overflow-y-auto space-y-2">
                <div class="rounded-md border-t-2 border-primary bg-card shadow-sm">
                    <div class="px-4 pt-4 pb-2">
                        <span class="text-[0.7rem] font-medium uppercase tracking-[0.14em] text-primary">Seasons</span>
                    </div>
                    <div class="px-3 pb-3">
                        <Link
                            v-for="s in props.seasons"
                            :key="s.id"
                            :href="route('rules.gaining-grounds.season', s.slug)"
                            class="p-2 block text-sm rounded-md transition-colors hover:bg-muted"
                            :class="s.slug === props.season.slug ? 'bg-primary text-primary-foreground' : ''"
                        >{{ s.title }}</Link>
                    </div>
                </div>
                <div v-if="props.seasonPages.length > 0" class="rounded-md border-t-2 border-primary bg-card shadow-sm">
                    <div class="px-4 pt-4 pb-2">
                        <span class="text-[0.7rem] font-medium uppercase tracking-[0.14em] text-primary">Pages</span>
                    </div>
                    <div class="px-3 pb-3">
                        <Link
                            :href="route('rules.gaining-grounds.season', props.season.slug)"
                            class="p-2 block text-sm rounded-md transition-colors hover:bg-muted bg-primary text-primary-foreground"
                        >Season Overview</Link>
                        <Link
                            v-for="page in props.seasonPages"
                            :key="page.id"
                            :href="route('rules.gaining-grounds.season-page', [props.season.slug, page.slug])"
                            class="p-2 block text-sm rounded-md transition-colors hover:bg-muted"
                        >{{ page.title }}</Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main content -->
        <div :class="props.season ? 'lg:col-span-6' : ''">
            <!-- Mobile selectors -->
            <div v-if="props.season" class="lg:hidden mb-4 mx-2 space-y-2">
                <Select v-model="seasonParam">
                    <SelectTrigger class="w-full">
                        <SelectValue placeholder="Select a Season" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectGroup>
                            <SelectLabel>Seasons</SelectLabel>
                            <SelectItem v-for="s in props.seasons" :key="s.id" :value="s.slug">
                                {{ s.title }}
                            </SelectItem>
                        </SelectGroup>
                    </SelectContent>
                </Select>
                <Select v-if="props.seasonPages.length > 0" v-model="mobilePageSlug">
                    <SelectTrigger class="w-full">
                        <SelectValue placeholder="Select a Page" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectGroup>
                            <SelectLabel>Pages</SelectLabel>
                            <SelectItem v-for="page in props.seasonPages" :key="page.id" :value="page.slug">
                                {{ page.title }}
                            </SelectItem>
                        </SelectGroup>
                    </SelectContent>
                </Select>
            </div>

            <!-- Banner -->
            <div class="w-full max-w-2xl mx-auto text-center py-4">
                <img src='/Images/page_banner_top.png' alt="" class="w-40 sm:w-48 mx-auto opacity-90" />
                <h1 class="font-[family-name:var(--font-display)] text-2xl sm:text-3xl font-medium text-balance my-1">{{ props.season?.title ?? 'Gaining Grounds' }}</h1>
                <img src='/Images/page_banner_bottom.png' alt="" class="w-40 sm:w-48 mx-auto opacity-90" />
            </div>

            <template v-if="props.season">
                <!-- Season Content -->
                <div v-if="props.season.content && props.season.content.length > 0" class="mb-8 rounded-md border-t-2 border-primary bg-card shadow-sm">
                    <div class="p-6 season-content">
                        <ParsedContent :content="props.season.content" />
                    </div>
                    <div class="border-t border-border px-6 py-3 text-xs text-muted-foreground text-right">
                        Last updated {{ props.season.published_at }} by {{ props.season.published_by }}
                    </div>
                </div>

                <!-- Strategies -->
                <div v-if="props.strategies.length > 0" class="mb-8">
                    <h2 class="text-[0.7rem] font-medium uppercase tracking-[0.14em] text-primary mb-4 pb-2 border-b border-border">Strategies</h2>
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                        <Link
                            v-for="strategy in props.strategies"
                            :key="strategy.id"
                            :href="route('rules.gaining-grounds.strategy', strategy.slug)"
                            class="group block"
                        >
                            <div
                                class="rounded-lg border border-border border-l-4 bg-card p-3 sm:p-4 h-full transition-colors hover:bg-muted/50"
                                :class="suitAccent(strategy.suit)"
                            >
                                <img v-if="strategy.front_image" :src="strategy.front_image" :alt="strategy.title" class="w-full rounded mb-3" />
                                <div class="text-sm font-medium group-hover:underline">{{ strategy.title }}</div>
                                <span
                                    v-if="strategy.suit_label"
                                    class="inline-flex items-center gap-1 mt-1.5 text-[11px] px-1.5 py-0.5 rounded-full font-medium"
                                    :class="suitBadge(strategy.suit)"
                                >
                                    <span class="font-[symbolFont] text-sm">{{ suitSymbol(strategy.suit) }}</span>
                                    {{ strategy.suit_label }}
                                </span>
                            </div>
                        </Link>
                    </div>
                </div>

                <!-- Schemes -->
                <div v-if="props.schemes.length > 0" class="mb-8">
                    <h2 class="text-[0.7rem] font-medium uppercase tracking-[0.14em] text-primary mb-4 pb-2 border-b border-border">Schemes</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                        <Link
                            v-for="scheme in props.schemes"
                            :key="scheme.id"
                            :href="route('rules.gaining-grounds.scheme', scheme.slug)"
                            class="flex items-center gap-3 rounded-lg border border-border bg-card px-3 py-2.5 hover:bg-muted/50 transition-colors"
                        >
                            <img v-if="scheme.front_image" :src="scheme.front_image" :alt="scheme.title" class="size-8 rounded object-cover shrink-0" />
                            <span class="text-sm font-medium truncate">{{ scheme.title }}</span>
                            <ChevronRight class="size-3.5 shrink-0 text-muted-foreground ml-auto" />
                        </Link>
                    </div>
                </div>

                <ContentReferences
                    v-if="props.references"
                    :references="props.references.references"
                    :referenced_by="props.references.referenced_by"
                    :revision_history="props.references.revision_history"
                />
            </template>

            <!-- Empty state -->
            <div v-else class="rounded-lg border border-dashed py-10 text-center">
                <p class="text-sm text-muted-foreground">No Gaining Grounds seasons have been published yet.</p>
            </div>
        </div>

        <ScrollToTop />
    </div>
</template>

<style scoped>
.season-content :deep(.font-\[symbolFont\]) {
    font-size: 1.25rem;
}
</style>
