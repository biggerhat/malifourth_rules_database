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
    seasonPage: {
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

const currentIndex = computed(() =>
    props.seasonPages.findIndex(p => p.slug === props.seasonPage.slug)
);

const prevPage = computed(() =>
    currentIndex.value > 0 ? props.seasonPages[currentIndex.value - 1] : null
);

const nextPage = computed(() =>
    currentIndex.value >= 0 && currentIndex.value < props.seasonPages.length - 1
        ? props.seasonPages[currentIndex.value + 1]
        : null
);
</script>

<template>
    <Head :title="`${props.season?.title} - ${props.seasonPage.title}`" />

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
            <span>{{ props.seasonPage.title }}</span>
            <img src='/Images/page_banner_bottom.png' alt="" class="w-3/4 sm:w-1/2 lg:w-1/3 mx-auto" />
        </div>

        <!-- Season Pages Navigation -->
        <div v-if="props.seasonPages.length > 1 && !props.viewing_old_version" class="mb-6">
            <h2 class="font-semibold text-base sm:text-lg mb-3 pb-2 border-b">Pages</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                <Link
                    v-for="page in props.seasonPages"
                    :key="page.id"
                    :href="route('rules.gaining-grounds.season-page', [props.season.slug, page.slug])"
                    class="flex items-center gap-3 rounded-lg border bg-card px-3 py-2.5 hover:bg-muted/50 transition-colors"
                    :class="page.slug === props.seasonPage.slug ? 'border-primary bg-muted/50' : ''"
                >
                    <span class="text-sm font-medium truncate">{{ page.title }}</span>
                    <ChevronRight class="size-3.5 shrink-0 text-muted-foreground ml-auto" />
                </Link>
            </div>
        </div>

        <!-- Content -->
        <Card v-if="props.seasonPage.content && props.seasonPage.content.length > 0" class="mb-8">
            <CardContent>
                <ParsedContent :content="props.seasonPage.content" />
            </CardContent>
            <CardFooter v-if="!props.viewing_old_version" class="border-t px-6 py-3 text-xs text-muted-foreground justify-end">
                Last updated {{ props.seasonPage.published_at }} by {{ props.seasonPage.published_by }}
            </CardFooter>
        </Card>

        <!-- Previous/Next Navigation -->
        <div v-if="(prevPage || nextPage) && !props.viewing_old_version" class="flex justify-between mb-8">
            <Link
                v-if="prevPage"
                :href="route('rules.gaining-grounds.season-page', [props.season.slug, prevPage.slug])"
                class="inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-primary transition-colors"
            >
                <ChevronLeft class="size-4" />
                {{ prevPage.title }}
            </Link>
            <div v-else></div>
            <Link
                v-if="nextPage"
                :href="route('rules.gaining-grounds.season-page', [props.season.slug, nextPage.slug])"
                class="inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-primary transition-colors"
            >
                {{ nextPage.title }}
                <ChevronRight class="size-4" />
            </Link>
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
