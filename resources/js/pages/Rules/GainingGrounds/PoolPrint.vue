<script setup lang="ts">
import SeoHead from "@/components/SeoHead.vue";
import { Printer } from "lucide-vue-next";

const props = defineProps({
    season: {
        type: Object,
        required: true,
    },
    strategies: {
        type: [Object, Array],
        required: false,
        default() {
            return [];
        }
    },
    schemes: {
        type: [Object, Array],
        required: false,
        default() {
            return [];
        }
    },
});

const printPage = () => window.print();
</script>

<template>
    <SeoHead
        :title="`${props.season.title} Pool — Print`"
        :description="`Printable strategy and scheme pool for the ${props.season.title} season of Malifaux.`"
        :noindex="true"
    />

    <div class="max-w-4xl mx-auto px-4 text-foreground leading-6 text-md">
        <div class="flex items-center justify-between py-4 print:hidden">
            <h1 class="text-xl font-medium">{{ props.season.title }} — Strategy &amp; Scheme Pool</h1>
            <button
                type="button"
                class="inline-flex items-center gap-1.5 rounded-md border border-border bg-card px-3 py-1.5 text-sm font-medium hover:bg-muted/50 transition-colors"
                @click="printPage"
            >
                <Printer class="size-4" />
                Print
            </button>
        </div>
        <h1 class="hidden print:block text-xl font-medium py-4">{{ props.season.title }} — Strategy &amp; Scheme Pool</h1>

        <div v-if="props.strategies.length > 0" class="mb-6">
            <h2 class="text-[0.7rem] font-medium uppercase tracking-[0.14em] text-primary mb-3 pb-2 border-b border-border">
                Strategies
            </h2>
            <div class="grid grid-cols-3 sm:grid-cols-4 print:grid-cols-4 gap-3">
                <div v-for="strategy in props.strategies" :key="strategy.id" class="break-inside-avoid">
                    <img
                        v-if="strategy.front_image"
                        :src="strategy.front_image"
                        :alt="strategy.title"
                        class="w-full rounded border border-border"
                    />
                    <div class="text-xs font-medium text-center mt-1">{{ strategy.title }}</div>
                    <div v-if="strategy.suit_label" class="text-[10px] text-muted-foreground text-center">{{ strategy.suit_label }}</div>
                </div>
            </div>
        </div>

        <div v-if="props.schemes.length > 0" class="mb-6">
            <h2 class="text-[0.7rem] font-medium uppercase tracking-[0.14em] text-primary mb-3 pb-2 border-b border-border">
                Schemes
            </h2>
            <div class="grid grid-cols-3 sm:grid-cols-4 print:grid-cols-4 gap-3">
                <div v-for="scheme in props.schemes" :key="scheme.id" class="break-inside-avoid">
                    <img
                        v-if="scheme.front_image"
                        :src="scheme.front_image"
                        :alt="scheme.title"
                        class="w-full rounded border border-border"
                    />
                    <div class="text-xs font-medium text-center mt-1">{{ scheme.title }}</div>
                </div>
            </div>
        </div>

        <div v-if="props.strategies.length === 0 && props.schemes.length === 0" class="text-sm text-muted-foreground py-8 text-center">
            No strategies or schemes are published for this season yet.
        </div>
    </div>
</template>
