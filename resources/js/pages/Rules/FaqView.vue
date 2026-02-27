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
import { ChevronLeft, ExternalLink } from "lucide-vue-next";
import { ref, watch, nextTick, onMounted } from "vue";

const props = defineProps({
    categories: {
        type: Array as () => Array<{ key: string; label: string; items: any[] }>,
        required: false,
        default() {
            return null;
        }
    },
    faq: {
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

const isListMode = !props.faq && props.categories;

const activeCategory = ref('');

const scrollToCategory = (categoryKey: string) => {
    const el = document.getElementById(`faq-category-${categoryKey}`);
    if (el) {
        el.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
};

const mobileCategory = ref('');
watch(mobileCategory, (val) => {
    if (val) scrollToCategory(val);
});

onMounted(() => {
    if (!props.categories) return;

    const observer = new IntersectionObserver(
        (entries) => {
            for (const entry of entries) {
                if (entry.isIntersecting) {
                    activeCategory.value = entry.target.id.replace('faq-category-', '');
                }
            }
        },
        { rootMargin: '-80px 0px -60% 0px', threshold: 0 }
    );

    nextTick(() => {
        props.categories.forEach(cat => {
            const el = document.getElementById(`faq-category-${cat.key}`);
            if (el) observer.observe(el);
        });
    });
});
</script>

<template>
    <Head :title="props.faq ? props.faq.title_text : 'FAQ'" />

    <div
        class="px-2 sm:px-4 lg:px-2 text-primary leading-6 text-md"
        :class="isListMode && !props.viewing_old_version
            ? 'grid grid-cols-1 lg:grid-cols-8 lg:gap-4'
            : 'max-w-4xl mx-auto'"
    >
        <!-- ─── Sidebar: Category Navigation (desktop) ─── -->
        <div v-if="isListMode && !props.viewing_old_version" class="lg:col-span-2 hidden lg:block">
            <Card class="sticky top-20 max-h-[calc(100vh-6rem)] overflow-y-auto">
                <CardHeader class="pb-2">
                    <CardTitle class="text-sm">Sections</CardTitle>
                </CardHeader>
                <CardContent class="px-2 pb-2">
                    <button
                        v-for="cat in props.categories"
                        :key="cat.key"
                        @click="scrollToCategory(cat.key)"
                        class="px-2.5 py-1.5 w-full text-left flex items-center justify-between text-sm rounded-md transition-colors hover:bg-muted"
                        :class="activeCategory === cat.key
                            ? 'bg-primary text-primary-foreground'
                            : ''"
                    >
                        <span class="truncate">{{ cat.label }}</span>
                        <span
                            v-if="cat.items.length"
                            class="text-[11px] tabular-nums ml-2 shrink-0 rounded-full px-1.5 min-w-[1.25rem] text-center"
                            :class="activeCategory === cat.key
                                ? 'bg-primary-foreground/20 text-primary-foreground'
                                : 'bg-muted text-muted-foreground'"
                        >{{ cat.items.length }}</span>
                    </button>
                </CardContent>
            </Card>
        </div>

        <!-- ─── Main Content Column ─── -->
        <div :class="isListMode && !props.viewing_old_version ? 'lg:col-span-6' : ''">

            <Alert v-if="props.viewing_old_version" variant="destructive" class="mb-4">
                <AlertDescription>
                    You are viewing an older version of this content.
                    <Link :href="props.current_version_url" class="underline font-medium ml-1">View the current version &rarr;</Link>
                </AlertDescription>
            </Alert>

            <!-- Mobile: Category dropdown -->
            <div v-if="isListMode && !props.viewing_old_version" class="lg:hidden mb-4">
                <Select v-model="mobileCategory">
                    <SelectTrigger class="w-full">
                        <SelectValue placeholder="Jump to section..." />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectGroup>
                            <SelectLabel>Sections</SelectLabel>
                            <SelectItem
                                v-for="cat in props.categories"
                                :key="cat.key"
                                :value="cat.key"
                            >{{ cat.label }} ({{ cat.items.length }})</SelectItem>
                        </SelectGroup>
                    </SelectContent>
                </Select>
            </div>

            <!-- Banner -->
            <div class="w-full text-center text-xl py-4">
                <img src='/Images/page_banner_top.png' alt="" class="w-3/4 sm:w-1/2 lg:w-1/3 mx-auto" />
                <span v-if="!props.faq">Frequently Asked Questions</span>
                <span v-else class="text-lg sm:text-xl">{{ props.faq.category_label }}</span>
                <img src='/Images/page_banner_bottom.png' alt="" class="w-3/4 sm:w-1/2 lg:w-1/3 mx-auto" />
            </div>

            <!-- ═══ Listing mode ═══ -->
            <template v-if="isListMode">
                <div
                    v-for="cat in props.categories"
                    :key="cat.key"
                    :id="`faq-category-${cat.key}`"
                    class="mb-10 scroll-mt-20"
                >
                    <h2 class="font-semibold text-base sm:text-lg mb-4 pb-2 border-b">
                        {{ cat.label }}
                    </h2>

                    <!-- FAQs -->
                    <div v-if="cat.items.length" class="space-y-4">
                        <div
                            v-for="(item) in cat.items"
                            :key="item.id"
                            class="rounded-lg border bg-card"
                        >
                            <!-- Question -->
                            <div class="px-4 py-3 sm:px-5 sm:py-4">
                                <div class="flex gap-3">
                                    <span class="text-primary font-bold text-sm mt-0.5 shrink-0 select-none">Q:</span>
                                    <div class="text-sm font-medium leading-relaxed min-w-0">
                                        <ParsedContent :content="item.title" />
                                    </div>
                                </div>
                            </div>

                            <!-- Answer -->
                            <div class="px-4 pb-3 sm:px-5 sm:pb-4">
                                <div class="flex gap-3">
                                    <span class="text-primary font-bold text-sm mt-0.5 shrink-0 select-none">A:</span>
                                    <div class="text-sm text-muted-foreground leading-relaxed min-w-0">
                                        <ParsedContent :content="item.answer" />
                                    </div>
                                </div>
                            </div>

                            <!-- Permalink -->
                            <div class="px-4 pb-3 sm:px-5 sm:pb-4 flex justify-end">
                                <Link
                                    :href="route('rules.faq.view', item.slug)"
                                    class="inline-flex items-center gap-1 text-xs text-muted-foreground hover:text-primary transition-colors"
                                >
                                    View details
                                    <ExternalLink class="size-3" />
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Empty category -->
                    <div v-else class="rounded-lg border border-dashed py-6 text-center">
                        <p class="text-sm text-muted-foreground">No FAQs at this time.</p>
                    </div>
                </div>
            </template>

            <!-- ═══ Individual mode ═══ -->
            <template v-if="props.faq">

                <!-- Back link -->
                <div class="mb-4">
                    <Link
                        :href="route('rules.faq.index')"
                        class="inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-primary transition-colors"
                    >
                        <ChevronLeft class="size-4" />
                        All FAQs
                    </Link>
                </div>

                <Card>
                    <CardContent class="px-4 pt-5 pb-5 sm:px-6 sm:pt-6 sm:pb-6">
                        <!-- Question -->
                        <div class="flex gap-3 mb-4">
                            <span class="text-primary font-bold text-sm mt-0.5 shrink-0 select-none">Q:</span>
                            <h1 class="text-sm sm:text-base font-semibold leading-relaxed min-w-0">
                                <ParsedContent :content="props.faq.title" />
                            </h1>
                        </div>

                        <!-- Divider -->
                        <div class="border-t my-4"></div>

                        <!-- Answer -->
                        <div class="flex gap-3">
                            <span class="text-primary font-bold text-sm mt-0.5 shrink-0 select-none">A:</span>
                            <div class="text-sm sm:text-base leading-relaxed min-w-0">
                                <ParsedContent :content="props.faq.answer" />
                            </div>
                        </div>
                    </CardContent>

                    <CardFooter class="border-t px-4 sm:px-6 py-3 text-xs text-muted-foreground justify-end gap-1">
                        Last updated {{ props.faq.published_at }}
                    </CardFooter>
                </Card>

                <ContentReferences
                    v-if="props.references"
                    :references="props.references.references"
                    :referenced_by="props.references.referenced_by"
                    :revision_history="props.references.revision_history"
                />
            </template>

            <ScrollToTop />
        </div>
    </div>
</template>
