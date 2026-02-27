<script setup lang="ts">
import ContentReferences from "@/components/ContentReferences.vue";
import ParsedContent from "@/components/ParsedContent.vue";
import ScrollToTop from "@/components/ScrollToTop.vue";
import Card from "@/components/ui/card/Card.vue";
import CardContent from "@/components/ui/card/CardContent.vue";
import CardHeader from "@/components/ui/card/CardHeader.vue";
import CardTitle from "@/components/ui/card/CardTitle.vue";
import { Alert, AlertDescription } from "@/components/ui/alert";
import { router } from "@inertiajs/vue3";
import { computed, ref, watch } from "vue";
import { ChevronLeft, ChevronRight } from 'lucide-vue-next';
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectLabel,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select'

const props = defineProps({
    pages: {
        type: [Object, Array],
        required: false,
        default() {
            return {};
        }
    },
    title: {
        type: String,
        required: false,
        default() {
            return '';
        }
    },
    title_text: {
        type: String,
        required: false,
        default() {
            return '';
        }
    },
    slug: {
        type: String,
        required: false,
        default() {
            return '';
        }
    },
    content: {
        type: [Object, Array, String],
        required: false,
        default() {
            return '';
        }
    },
    page_number: {
        type: Number,
        required: false,
        default() {
            return null;
        }
    },
    book_page_numbers: {
        type: String,
        required: false,
        default() {
            return null;
        }
    },
    published_at: {
        type: String,
        required: false,
        default() {
            return '';
        }
    },
    published_by: {
        type: String,
        required: false,
        default() {
            return '';
        }
    },
    previous_page: {
        type: String,
        required: false,
        default() {
            return null;
        }
    },
    next_page: {
        type: String,
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

const pageParam = ref(props.slug);

const previousPageTitle = computed(() => {
    if (!props.previous_page) return null;
    return props.pages.find(p => p.slug === props.previous_page)?.title ?? null;
});

const nextPageTitle = computed(() => {
    if (!props.next_page) return null;
    return props.pages.find(p => p.slug === props.next_page)?.title ?? null;
});

watch(pageParam, () => {
    window.location.href = route('rules.page.view', pageParam.value);
});
</script>

<template>
    <Head :title="props.title_text || props.title" />

    <div class="px-2 sm:px-4 lg:px-2 text-primary leading-6 text-md" :class="props.viewing_old_version ? 'max-w-5xl mx-auto' : 'grid grid-cols-1 lg:grid-cols-8 lg:gap-2'">
        <div v-if="!props.viewing_old_version" class="lg:col-span-2 hidden lg:block">
            <Card class="sticky top-20 max-h-[calc(100vh-6rem)] overflow-y-auto">
                <CardHeader class="pb-0">
                    <CardTitle class="text-sm">Table of Contents</CardTitle>
                </CardHeader>
                <CardContent class="px-3">
                    <Link
                        v-for="page in props.pages"
                        :key="page.slug"
                        :href="route('rules.page.view', page.slug)"
                        class="p-2 block text-sm rounded-md transition-colors hover:bg-muted"
                        :class="page.slug === props.slug ? 'bg-primary text-primary-foreground' : ''"
                    ><span v-html="page.title"></span></Link>
                </CardContent>
            </Card>
        </div>
        <div :class="props.viewing_old_version ? '' : 'lg:col-span-6'">
            <Alert v-if="props.viewing_old_version" variant="destructive" class="mb-4">
                <AlertDescription>
                    You are viewing an older version of this content.
                    <Link :href="props.current_version_url" class="underline font-medium ml-1">View the current version &rarr;</Link>
                </AlertDescription>
            </Alert>
            <div v-if="!props.viewing_old_version" class="lg:hidden block mb-4 mx-2">
                <Select v-model="pageParam">
                    <SelectTrigger class="w-full">
                        <SelectValue placeholder="Select a Section" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectGroup>
                            <SelectLabel>Sections</SelectLabel>
                            <SelectItem v-for="page in props.pages" :key="page.slug" :value="page.slug">
                                <span v-html="page.title"></span>
                            </SelectItem>
                        </SelectGroup>
                    </SelectContent>
                </Select>
            </div>
            <div class="w-full text-center text-xl py-4">
                <img src='/Images/page_banner_top.png' alt="Banner Top" class="w-3/4 sm:w-1/2 lg:w-1/3 mx-auto" />
                <span v-html="props.title"></span>
                <img src='/Images/page_banner_bottom.png' alt="Banner Bottom" class="w-3/4 sm:w-1/2 lg:w-1/3 mx-auto" />
            </div>
            <div class="py-2">
                <ParsedContent :content="props.content" />
            </div>
            <div v-if="!props.viewing_old_version" class="flex flex-col sm:flex-row items-stretch border rounded-lg mt-8 mb-4 divide-y sm:divide-y-0 sm:divide-x">
                <button
                    @click="router.get(route('rules.page.view', props.previous_page))"
                    :disabled="!previous_page"
                    class="flex-1 flex items-center gap-2 p-3 sm:p-4 hover:bg-muted/50 transition text-left disabled:opacity-40 disabled:pointer-events-none"
                >
                    <ChevronLeft class="size-5 shrink-0" />
                    <div class="min-w-0">
                        <div class="text-xs text-muted-foreground">Previous</div>
                        <div class="text-sm font-medium truncate" v-html="previousPageTitle"></div>
                    </div>
                </button>
                <button
                    @click="router.get(route('rules.page.view', props.next_page))"
                    :disabled="!next_page"
                    class="flex-1 flex items-center sm:justify-end gap-2 p-3 sm:p-4 hover:bg-muted/50 transition sm:text-right disabled:opacity-40 disabled:pointer-events-none"
                >
                    <ChevronRight class="size-5 shrink-0 sm:hidden" />
                    <div class="min-w-0">
                        <div class="text-xs text-muted-foreground">Next</div>
                        <div class="text-sm font-medium truncate" v-html="nextPageTitle"></div>
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
