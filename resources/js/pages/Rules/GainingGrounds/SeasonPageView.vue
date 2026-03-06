<script setup lang="ts">
import ContentReferences from "@/components/ContentReferences.vue";
import ParsedContent from "@/components/ParsedContent.vue";
import ScrollToTop from "@/components/ScrollToTop.vue";
import {
    Card,
    CardContent,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card'
import { Alert, AlertDescription } from "@/components/ui/alert";
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectLabel,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select'
import { router } from "@inertiajs/vue3";
import { ChevronLeft, ChevronRight } from "lucide-vue-next";
import { computed, ref, watch } from "vue";

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
    seasons: {
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

const mobilePageSlug = ref(props.seasonPage.slug);
watch(mobilePageSlug, (newSlug) => {
    if (newSlug && newSlug !== props.seasonPage.slug) {
        window.location.href = route('rules.gaining-grounds.season-page', [props.season.slug, newSlug]);
    }
});
</script>

<template>
    <Head :title="`${props.season?.title} - ${props.seasonPage.title}`" />

    <div class="px-2 sm:px-4 lg:px-2 text-primary leading-6 text-md" :class="props.viewing_old_version ? 'max-w-5xl mx-auto' : 'grid grid-cols-1 lg:grid-cols-8 lg:gap-2'">
        <!-- Sidebar: Season pages (desktop) -->
        <div v-if="!props.viewing_old_version" class="lg:col-span-2 hidden lg:block">
            <div class="sticky top-20 max-h-[calc(100vh-6rem)] overflow-y-auto space-y-2">
                <Card>
                    <CardHeader class="pb-0">
                        <CardTitle class="text-sm">Seasons</CardTitle>
                    </CardHeader>
                    <CardContent class="px-3">
                        <Link
                            v-for="s in props.seasons"
                            :key="s.id"
                            :href="route('rules.gaining-grounds.season', s.slug)"
                            class="p-2 block text-sm rounded-md transition-colors hover:bg-muted"
                            :class="s.slug === props.season.slug ? 'bg-primary text-primary-foreground' : ''"
                        >{{ s.title }}</Link>
                    </CardContent>
                </Card>
                <Card v-if="props.seasonPages.length > 0">
                    <CardHeader class="pb-0">
                        <CardTitle class="text-sm">Pages</CardTitle>
                    </CardHeader>
                    <CardContent class="px-3">
                        <Link
                            :href="route('rules.gaining-grounds.season', props.season.slug)"
                            class="p-2 block text-sm rounded-md transition-colors hover:bg-muted"
                        >Season Overview</Link>
                        <Link
                            v-for="page in props.seasonPages"
                            :key="page.id"
                            :href="route('rules.gaining-grounds.season-page', [props.season.slug, page.slug])"
                            class="p-2 block text-sm rounded-md transition-colors hover:bg-muted"
                            :class="page.slug === props.seasonPage.slug ? 'bg-primary text-primary-foreground' : ''"
                        >{{ page.title }}</Link>
                    </CardContent>
                </Card>
            </div>
        </div>

        <!-- Main content -->
        <div :class="props.viewing_old_version ? '' : 'lg:col-span-6'">
            <Alert v-if="props.viewing_old_version" variant="destructive" class="mb-4">
                <AlertDescription>
                    You are viewing an older version of this content.
                    <Link :href="props.current_version_url" class="underline font-medium ml-1">View the current version &rarr;</Link>
                </AlertDescription>
            </Alert>

            <!-- Mobile page selector -->
            <div v-if="!props.viewing_old_version && props.seasonPages.length > 0" class="lg:hidden block mb-4 mx-2">
                <Select v-model="mobilePageSlug">
                    <SelectTrigger class="w-full">
                        <SelectValue placeholder="Select a page..." />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectGroup>
                            <SelectLabel>{{ props.season?.title }} Pages</SelectLabel>
                            <SelectItem v-for="page in props.seasonPages" :key="page.id" :value="page.slug">
                                {{ page.title }}
                            </SelectItem>
                        </SelectGroup>
                    </SelectContent>
                </Select>
            </div>

            <!-- Banner -->
            <div class="w-full text-center text-xl py-4">
                <img src='/Images/page_banner_top.png' alt="" class="w-3/4 sm:w-1/2 lg:w-1/3 mx-auto" />
                <span>{{ props.seasonPage.title }}</span>
                <img src='/Images/page_banner_bottom.png' alt="" class="w-3/4 sm:w-1/2 lg:w-1/3 mx-auto" />
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
            <div v-if="!props.viewing_old_version" class="flex flex-col sm:flex-row items-stretch border rounded-lg mt-8 mb-4 divide-y sm:divide-y-0 sm:divide-x">
                <button
                    @click="router.get(route('rules.gaining-grounds.season-page', [props.season.slug, prevPage.slug]))"
                    :disabled="!prevPage"
                    class="flex-1 flex items-center gap-2 p-3 sm:p-4 hover:bg-muted/50 transition text-left disabled:opacity-40 disabled:pointer-events-none"
                >
                    <ChevronLeft class="size-5 shrink-0" />
                    <div class="min-w-0">
                        <div class="text-xs text-muted-foreground">Previous</div>
                        <div class="text-sm font-medium truncate">{{ prevPage?.title }}</div>
                    </div>
                </button>
                <button
                    @click="router.get(route('rules.gaining-grounds.season-page', [props.season.slug, nextPage.slug]))"
                    :disabled="!nextPage"
                    class="flex-1 flex items-center sm:justify-end gap-2 p-3 sm:p-4 hover:bg-muted/50 transition sm:text-right disabled:opacity-40 disabled:pointer-events-none"
                >
                    <ChevronRight class="size-5 shrink-0 sm:hidden" />
                    <div class="min-w-0">
                        <div class="text-xs text-muted-foreground">Next</div>
                        <div class="text-sm font-medium truncate">{{ nextPage?.title }}</div>
                    </div>
                    <ChevronRight class="size-5 shrink-0 hidden sm:block" />
                </button>
            </div>

            <ContentReferences
                v-if="props.references"
                :references="props.references.references"
                :referenced_by="props.references.referenced_by"
                :revision_history="props.references.revision_history"
            />
        </div>

        <ScrollToTop />
    </div>
</template>
