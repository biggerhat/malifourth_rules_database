<script setup lang="ts">
import ParsedContent from "@/components/ParsedContent.vue";
import ScrollToTop from "@/components/ScrollToTop.vue";
import SeoHead from "@/components/SeoHead.vue";
import { Card, CardContent } from '@/components/ui/card'
import { Alert, AlertDescription } from "@/components/ui/alert";
import { ChevronLeft } from "lucide-vue-next";

const props = defineProps({
    faction: {
        type: String,
        required: false,
        default() {
            return '';
        }
    },
    faction_label: {
        type: String,
        required: false,
        default() {
            return '';
        }
    },
    card_name: {
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
    image: {
        type: String,
        required: false,
        default() {
            return null;
        }
    },
    entries: {
        type: [Object, Array],
        required: false,
        default() {
            return [];
        }
    },
    published_at: {
        type: String,
        required: false,
        default() {
            return null;
        }
    },
    published_by: {
        type: String,
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
</script>

<template>
    <SeoHead :title="`${props.card_name} Errata`" :description="`Card errata for ${props.card_name} (${props.faction_label})`" />

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
                :href="route('errata.cards.index')"
                class="inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-primary transition-colors"
            >
                <ChevronLeft class="size-4" />
                Card Errata
            </Link>
        </div>

        <!-- Banner -->
        <div class="w-full text-center text-xl py-4">
            <img src='/Images/page_banner_top.png' alt="" class="w-3/4 sm:w-1/2 lg:w-1/3 mx-auto" />
            <span>{{ props.card_name }}</span>
            <img src='/Images/page_banner_bottom.png' alt="" class="w-3/4 sm:w-1/2 lg:w-1/3 mx-auto" />
        </div>
        <p class="text-center text-sm text-muted-foreground capitalize -mt-2 mb-4">{{ props.faction_label }}</p>

        <Card class="mb-8">
            <CardContent class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-6">
                    <div v-for="(entry, index) in props.entries" :key="entry.id ?? index" :class="index > 0 ? 'pt-6 border-t' : ''">
                        <h3 class="font-medium mb-2">Errata {{ index + 1 }}</h3>
                        <div v-if="entry.what_changed?.length" class="mb-3">
                            <div class="text-xs uppercase text-muted-foreground font-semibold mb-1">What Changed</div>
                            <ParsedContent :content="entry.what_changed" />
                        </div>
                        <div v-if="entry.what_it_was?.length" class="mb-3">
                            <div class="text-xs uppercase text-muted-foreground font-semibold mb-1">What It Was</div>
                            <ParsedContent :content="entry.what_it_was" />
                        </div>
                        <div v-if="entry.what_it_is_now?.length" class="mb-3">
                            <div class="text-xs uppercase text-muted-foreground font-semibold mb-1">What It Is Now</div>
                            <ParsedContent :content="entry.what_it_is_now" />
                        </div>
                    </div>
                    <div v-if="!props.entries?.length" class="text-sm text-muted-foreground">
                        No errata entries recorded for this card.
                    </div>
                </div>
                <div>
                    <img v-if="props.image" :src="props.image" :alt="props.card_name" class="rounded-md border w-full" />
                    <div v-else class="rounded-md border border-dashed h-full min-h-40 flex items-center justify-center text-sm text-muted-foreground">
                        No card image
                    </div>
                </div>
            </CardContent>
        </Card>

        <div v-if="!props.viewing_old_version && props.published_at" class="text-xs text-muted-foreground text-right mb-8">
            Last updated {{ props.published_at }} by {{ props.published_by }}
        </div>

        <ScrollToTop />
    </div>
</template>
