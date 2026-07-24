<script setup lang="ts">
import CardFlip from "@/components/CardFlip.vue";
import ParsedContent from "@/components/ParsedContent.vue";
import ScrollToTop from "@/components/ScrollToTop.vue";
import SeoHead from "@/components/SeoHead.vue";
import { Alert, AlertDescription } from "@/components/ui/alert";
import { ChevronLeft } from "lucide-vue-next";

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
    front_image: {
        type: String,
        required: false,
        default() {
            return null;
        }
    },
    back_image: {
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

    <div class="max-w-4xl mx-auto px-2 sm:px-4 text-foreground leading-6 text-md">
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
        <div class="w-full max-w-2xl mx-auto text-center py-4">
            <img src='/Images/page_banner_top.png' alt="" class="w-40 sm:w-48 mx-auto opacity-90" />
            <h1 class="font-[family-name:var(--font-display)] text-2xl sm:text-3xl font-medium text-balance my-1">{{ props.card_name }}</h1>
            <img src='/Images/page_banner_bottom.png' alt="" class="w-40 sm:w-48 mx-auto opacity-90" />
        </div>
        <p class="flex items-center justify-center gap-2 text-center text-[0.65rem] font-medium uppercase tracking-[0.18em] text-primary -mt-2 mb-4">
            <img v-if="factionLogos[props.faction]" :src="factionLogos[props.faction]" alt="" class="h-4 w-auto" />
            {{ props.faction_label }}
        </p>

        <div class="mb-8 rounded-md border-t-2 border-primary bg-card shadow-sm">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start p-6">
                <div class="space-y-6">
                    <div v-for="(entry, index) in props.entries" :key="entry.id ?? index" :class="index > 0 ? 'pt-6 border-t border-border' : ''">
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
                <div class="md:sticky md:top-4">
                    <CardFlip :front-image="props.front_image" :back-image="props.back_image" :alt="props.card_name" />
                </div>
            </div>
        </div>

        <div v-if="!props.viewing_old_version && props.published_at" class="text-xs text-muted-foreground text-right mb-8">
            Last updated {{ props.published_at }} by {{ props.published_by }}
        </div>

        <ScrollToTop />
    </div>
</template>

<style scoped>
:deep(.font-\[symbolFont\]) {
    font-size: 1.25rem;
}
</style>
