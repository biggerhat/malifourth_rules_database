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
import { ChevronLeft, ChevronRight } from "lucide-vue-next";
import { computed } from "vue";

const props = defineProps({
    scheme: {
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
    next_schemes: {
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

const sections = computed(() => {
    return [
        { key: 'prerequisites', label: 'Prerequisites', content: props.scheme.prerequisites },
        { key: 'reveal', label: 'Reveal', content: props.scheme.reveal },
        { key: 'scoring', label: 'Scoring', content: props.scheme.scoring },
        { key: 'additional', label: 'Additional VP', content: props.scheme.additional },
    ].filter(s => s.content && s.content.length > 0);
});

const hasImages = computed(() =>
    props.scheme.front_image || props.scheme.back_image || props.scheme.combination_image
);
</script>

<template>
    <Head :title="props.scheme.title" />

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
            <span>{{ props.scheme.title }}</span>
            <img src='/Images/page_banner_bottom.png' alt="" class="w-3/4 sm:w-1/2 lg:w-1/3 mx-auto" />
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
                    <img v-if="props.scheme.front_image" :src="props.scheme.front_image" :alt="props.scheme.title + ' Front'" class="rounded-lg shadow-sm" />
                    <img v-if="props.scheme.back_image" :src="props.scheme.back_image" :alt="props.scheme.title + ' Back'" class="rounded-lg shadow-sm" />
                    <img v-if="props.scheme.combination_image" :src="props.scheme.combination_image" :alt="props.scheme.title" class="rounded-lg shadow-sm" />
                </div>
            </CardContent>

            <CardFooter class="border-t px-6 py-3 text-xs text-muted-foreground justify-end">
                Last updated {{ props.scheme.published_at }} by {{ props.scheme.published_by }}
            </CardFooter>
        </Card>

        <!-- Next Available Schemes -->
        <div v-if="props.next_schemes.length > 0" class="mt-6">
            <h2 class="font-semibold text-base mb-3">Next Available Schemes</h2>
            <div class="space-y-2">
                <Link
                    v-for="next in props.next_schemes"
                    :key="next.id"
                    :href="route('rules.gaining-grounds.scheme', next.slug)"
                    class="flex items-center gap-3 rounded-lg border bg-card px-4 py-3 hover:bg-muted/50 transition-colors"
                >
                    <img v-if="next.front_image" :src="next.front_image" :alt="next.title" class="size-8 rounded object-cover shrink-0" />
                    <span class="text-sm font-medium">{{ next.title }}</span>
                    <ChevronRight class="size-4 shrink-0 text-muted-foreground ml-auto" />
                </Link>
            </div>
        </div>

        <ContentReferences
            v-if="props.references"
            :references="props.references.references"
            :referenced_by="props.references.referenced_by"
            :revision_history="props.references.revision_history"
        />

        <ScrollToTop />
    </div>
</template>
