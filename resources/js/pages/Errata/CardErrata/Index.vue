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

const factionLogos: Record<string, string> = {
    arcanists: '/Images/Logos/M4E-Logo_Arcanists-B.png',
    bayou: '/Images/Logos/M4E-Logo_Bayou-B.png',
    guild: '/Images/Logos/M4E-Logo_Guild-B.png',
    explorers_society: '/Images/Logos/M4E-Logo_Explorers-B.png',
    neverborn: '/Images/Logos/M4E-Logo_Neverborn-B.png',
    outcasts: '/Images/Logos/M4E-Logo_Outcasts-B.png',
    resurrectionists: '/Images/Logos/M4E-Logo_Resurrectionists-B.png',
    ten_thunders: '/Images/Logos/M4E-Logo_Ten-Thunders-B.png',
};
</script>

<template>
    <Head title="Card Errata" />

    <div class="max-w-4xl mx-auto px-2 sm:px-4 text-foreground leading-6 text-md">
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

        <div class="w-full max-w-2xl mx-auto text-center py-4">
            <img src='/Images/page_banner_top.png' alt="" class="w-40 sm:w-48 mx-auto opacity-90" />
            <h1 class="font-[family-name:var(--font-display)] text-2xl sm:text-3xl font-medium text-balance my-1">Card Errata</h1>
            <img src='/Images/page_banner_bottom.png' alt="" class="w-40 sm:w-48 mx-auto opacity-90" />
        </div>

        <div v-for="faction in props.factions" :key="faction.value" class="mb-8">
            <h2 class="flex items-center gap-2 text-[0.7rem] font-medium uppercase tracking-[0.14em] text-primary mb-4 pb-2 border-b border-border">
                <img v-if="factionLogos[faction.value]" :src="factionLogos[faction.value]" alt="" class="h-4 w-auto" />
                {{ faction.label }}
            </h2>
            <div class="space-y-3">
                <div v-for="card in faction.cards" :key="card.slug" class="rounded-lg border border-border bg-card px-4 py-3 sm:px-5 sm:py-4">
                    <Link
                        :href="route('errata.cards.view', card.slug)"
                        class="flex items-center justify-between hover:text-primary transition-colors"
                    >
                        <div class="text-sm sm:text-base font-medium">{{ card.card_name }}</div>
                        <ChevronRight class="size-4 shrink-0 text-muted-foreground ml-3" />
                    </Link>
                    <ul v-if="card.entry_summaries?.length" class="mt-2 space-y-1 pl-4 border-l border-border">
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
