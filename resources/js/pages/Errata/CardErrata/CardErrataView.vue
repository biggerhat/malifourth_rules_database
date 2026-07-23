<script setup lang="ts">
import ScrollToTop from "@/components/ScrollToTop.vue";
import SeoHead from "@/components/SeoHead.vue";
import {
    Card,
    CardContent,
    CardHeader,
} from '@/components/ui/card'
import { Alert, AlertDescription } from "@/components/ui/alert";
import { ChevronLeft } from "lucide-vue-next";

const props = defineProps({
    card: {
        type: Object,
        required: true,
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
    <SeoHead :title="`${props.card.card_name} Errata`" :description="`Card errata for ${props.card.card_name} (${props.card.faction_label})`" />

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
            <span>{{ props.card.card_name }}</span>
            <img src='/Images/page_banner_bottom.png' alt="" class="w-3/4 sm:w-1/2 lg:w-1/3 mx-auto" />
        </div>
        <p class="text-center text-sm text-muted-foreground capitalize -mt-2 mb-4">{{ props.card.faction_label }}</p>

        <Card v-for="(entry, index) in props.card.entries" :key="entry.id ?? index" class="mb-6">
            <CardHeader>
                <h3 class="font-medium">Errata {{ index + 1 }}</h3>
            </CardHeader>
            <CardContent class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-4">
                    <div v-if="entry.what_changed">
                        <div class="text-xs uppercase text-muted-foreground font-semibold mb-1">What Changed</div>
                        <p>{{ entry.what_changed }}</p>
                    </div>
                    <div v-if="entry.what_it_was">
                        <div class="text-xs uppercase text-muted-foreground font-semibold mb-1">What It Was</div>
                        <p>{{ entry.what_it_was }}</p>
                    </div>
                    <div v-if="entry.what_it_is_now">
                        <div class="text-xs uppercase text-muted-foreground font-semibold mb-1">What It Is Now</div>
                        <p>{{ entry.what_it_is_now }}</p>
                    </div>
                </div>
                <div class="flex flex-col gap-4" v-if="entry.front_image || entry.back_image">
                    <img v-if="entry.front_image" :src="entry.front_image" :alt="`${props.card.card_name} front`" class="rounded-md border w-full" />
                    <img v-if="entry.back_image" :src="entry.back_image" :alt="`${props.card.card_name} back`" class="rounded-md border w-full" />
                </div>
            </CardContent>
        </Card>

        <div v-if="!props.card.entries?.length" class="rounded-lg border border-dashed py-10 text-center mb-6">
            <p class="text-sm text-muted-foreground">No errata entries recorded for this card.</p>
        </div>

        <div v-if="!props.viewing_old_version" class="text-xs text-muted-foreground text-right mb-8">
            Last updated {{ props.card.published_at }} by {{ props.card.published_by }}
        </div>

        <ScrollToTop />
    </div>
</template>
