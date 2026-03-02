<script setup lang="ts">
import ParsedContent from "@/components/ParsedContent.vue";
import ScrollToTop from "@/components/ScrollToTop.vue";
import {
    Card,
    CardContent,
    CardFooter,
} from '@/components/ui/card'
import { ChevronLeft } from "lucide-vue-next";

const props = defineProps({
    batch: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <Head :title="props.batch.title" />

    <div class="max-w-4xl mx-auto px-2 sm:px-4 text-primary leading-6 text-md">
        <!-- Back link -->
        <div class="pt-4 mb-2">
            <Link
                :href="route('errata.index')"
                class="inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-primary transition-colors"
            >
                <ChevronLeft class="size-4" />
                Errata
            </Link>
        </div>

        <!-- Banner -->
        <div class="w-full text-center text-xl py-4">
            <img src='/Images/page_banner_top.png' alt="" class="w-3/4 sm:w-1/2 lg:w-1/3 mx-auto" />
            <span>{{ props.batch.title }}</span>
            <img src='/Images/page_banner_bottom.png' alt="" class="w-3/4 sm:w-1/2 lg:w-1/3 mx-auto" />
        </div>

        <!-- Release Notes -->
        <Card v-if="props.batch.release_notes && props.batch.release_notes.length > 0" class="mb-8">
            <CardContent>
                <ParsedContent :content="props.batch.release_notes" />
            </CardContent>
            <CardFooter class="border-t px-6 py-3 text-xs text-muted-foreground justify-end">
                Published {{ props.batch.published_at }} by {{ props.batch.published_by }}
            </CardFooter>
        </Card>

        <!-- Item Change Notes -->
        <div v-if="props.batch.item_change_notes.length" class="space-y-4 mb-8">
            <h2 class="font-semibold text-lg pb-2 border-b">Changes</h2>
            <Card v-for="(item, idx) in props.batch.item_change_notes" :key="idx">
                <CardContent class="space-y-2">
                    <h3 class="font-medium" v-html="item.title"></h3>
                    <div class="text-sm text-muted-foreground">
                        <ParsedContent :content="item.change_notes" />
                    </div>
                </CardContent>
            </Card>
        </div>

        <ScrollToTop />
    </div>
</template>
