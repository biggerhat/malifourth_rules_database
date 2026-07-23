<script setup lang="ts">
import ScrollToTop from "@/components/ScrollToTop.vue";
import { ChevronLeft, ChevronRight } from "lucide-vue-next";

const props = defineProps({
    factions: {
        type: [Object, Array],
        required: false,
        default() {
            return [];
        }
    },
});
</script>

<template>
    <Head title="Card Errata" />

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

        <div class="w-full text-center text-xl py-4">
            <img src='/Images/page_banner_top.png' alt="" class="w-3/4 sm:w-1/2 lg:w-1/3 mx-auto" />
            <span>Card Errata</span>
            <img src='/Images/page_banner_bottom.png' alt="" class="w-3/4 sm:w-1/2 lg:w-1/3 mx-auto" />
        </div>

        <div v-for="faction in props.factions" :key="faction.value" class="mb-8">
            <h2 class="font-semibold text-lg mb-4 pb-2 border-b capitalize">{{ faction.label }}</h2>
            <div class="space-y-3">
                <div v-for="card in faction.cards" :key="card.slug" class="rounded-lg border bg-card px-4 py-3 sm:px-5 sm:py-4">
                    <Link
                        :href="route('errata.cards.view', card.slug)"
                        class="flex items-center justify-between hover:text-primary/80 transition-colors"
                    >
                        <div class="text-sm sm:text-base font-medium">{{ card.card_name }}</div>
                        <ChevronRight class="size-4 shrink-0 text-muted-foreground ml-3" />
                    </Link>
                    <ul v-if="card.entry_summaries?.length" class="mt-2 space-y-1 pl-4 border-l">
                        <li v-for="(summary, index) in card.entry_summaries" :key="index" class="text-xs text-muted-foreground">
                            {{ summary }}
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div v-if="!props.factions.length" class="rounded-lg border border-dashed py-10 text-center">
            <p class="text-sm text-muted-foreground">No card errata have been published yet.</p>
        </div>

        <ScrollToTop />
    </div>
</template>
