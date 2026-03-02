<script setup lang="ts">
import ContentReferences from "@/components/ContentReferences.vue";
import ParsedContent from "@/components/ParsedContent.vue";
import ScrollToTop from "@/components/ScrollToTop.vue";
import {
    Card,
    CardContent,
    CardFooter,
} from '@/components/ui/card'
import { Alert, AlertDescription } from "@/components/ui/alert";
import { ChevronLeft } from "lucide-vue-next";
import { computed } from "vue";

const props = defineProps({
    strategy: {
        type: Object,
        required: true,
    },
    season: {
        type: Object,
        required: false,
        default() {
            return null;
        }
    },
    references: {
        type: Object,
        required: false,
        default() {
            return null;
        }
    },
    viewing_old_version: {
        type: Boolean,
        required: false,
        default() {
            return false;
        }
    },
    current_version_url: {
        type: String,
        required: false,
        default() {
            return null;
        }
    }
});

const suitBadge = (suit: string) => {
    switch (suit) {
        case 'rams': return 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-200';
        case 'crows': return 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-200';
        case 'masks': return 'bg-purple-100 text-purple-800 dark:bg-purple-900/50 dark:text-purple-200';
        case 'tomes': return 'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-200';
        default: return 'bg-muted text-muted-foreground';
    }
};

const sections = computed(() => {
    return [
        { key: 'setup', label: 'Setup', content: props.strategy.setup },
        { key: 'rules', label: 'Rules', content: props.strategy.rules },
        { key: 'scoring', label: 'Scoring', content: props.strategy.scoring },
        { key: 'additional', label: 'Additional VP', content: props.strategy.additional },
    ].filter(s => s.content && s.content.length > 0);
});

const hasImages = computed(() =>
    props.strategy.front_image || props.strategy.back_image || props.strategy.combination_image
);
</script>

<template>
    <Head :title="props.strategy.title" />

    <div class="max-w-4xl mx-auto px-2 sm:px-4 text-primary leading-6 text-md">
        <Alert v-if="props.viewing_old_version" variant="destructive" class="mb-4">
            <AlertDescription>
                You are viewing an older version of this content.
                <Link :href="props.current_version_url" class="underline font-medium ml-1">View the current version &rarr;</Link>
            </AlertDescription>
        </Alert>

        <!-- Back link -->
        <div class="pt-4 mb-2">
            <Link
                v-if="props.season"
                :href="route('rules.gaining-grounds.season', props.season.slug)"
                class="inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-primary transition-colors"
            >
                <ChevronLeft class="size-4" />
                {{ props.season.title }}
            </Link>
        </div>

        <!-- Banner -->
        <div class="w-full text-center text-xl py-4">
            <img src='/Images/page_banner_top.png' alt="" class="w-3/4 sm:w-1/2 lg:w-1/3 mx-auto" />
            <span>{{ props.strategy.title }}</span>
            <img src='/Images/page_banner_bottom.png' alt="" class="w-3/4 sm:w-1/2 lg:w-1/3 mx-auto" />
        </div>

        <!-- Suit badge -->
        <div v-if="props.strategy.suit_label" class="flex justify-center mb-4">
            <span
                class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium"
                :class="suitBadge(props.strategy.suit)"
            >
                {{ props.strategy.suit_label }}
            </span>
        </div>

        <!-- Content -->
        <Card>
            <CardContent>
                <div
                    v-for="(section, idx) in sections"
                    :key="section.key"
                    :class="idx > 0 ? 'border-t pt-4 mt-4' : ''"
                >
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-muted-foreground mb-2">{{ section.label }}</h3>
                    <div class="text-sm leading-relaxed">
                        <ParsedContent :content="section.content" />
                    </div>
                </div>

                <!-- Images -->
                <div
                    v-if="hasImages"
                    class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3"
                    :class="sections.length > 0 ? 'border-t pt-4 mt-4' : ''"
                >
                    <img v-if="props.strategy.front_image" :src="props.strategy.front_image" :alt="props.strategy.title + ' Front'" class="rounded-lg shadow-sm" />
                    <img v-if="props.strategy.back_image" :src="props.strategy.back_image" :alt="props.strategy.title + ' Back'" class="rounded-lg shadow-sm" />
                    <img v-if="props.strategy.combination_image" :src="props.strategy.combination_image" :alt="props.strategy.title" class="rounded-lg shadow-sm" />
                </div>
            </CardContent>

            <CardFooter class="border-t px-6 py-3 text-xs text-muted-foreground justify-end">
                Last updated {{ props.strategy.published_at }} by {{ props.strategy.published_by }}
            </CardFooter>
        </Card>

        <ContentReferences
            v-if="props.references"
            :references="props.references.references"
            :referenced_by="props.references.referenced_by"
            :revision_history="props.references.revision_history"
        />

        <ScrollToTop />
    </div>
</template>
