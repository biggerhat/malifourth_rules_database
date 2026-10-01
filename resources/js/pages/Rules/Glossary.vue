<script setup lang="ts">
import ParsedContent from "@/components/ParsedContent.vue";
import ScrollToTop from "@/components/ScrollToTop.vue";
import SeoHead from "@/components/SeoHead.vue";
import { computed } from "vue";

interface GlossaryEntry {
    slug: string;
    title: string;
    title_text: string;
    type: string;
    image: string | null;
    content: unknown;
}

const props = defineProps({
    entries: {
        type: Array as () => GlossaryEntry[],
        required: false,
        default() {
            return [];
        }
    }
});

const ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'.split('');

function letterFor(entry: GlossaryEntry): string {
    const first = (entry.title_text || '').trim().charAt(0).toUpperCase();
    return /[A-Z]/.test(first) ? first : '#';
}

const groups = computed(() => {
    const byLetter = new Map<string, GlossaryEntry[]>();
    for (const entry of props.entries) {
        const letter = letterFor(entry);
        if (!byLetter.has(letter)) byLetter.set(letter, []);
        byLetter.get(letter)!.push(entry);
    }
    return byLetter;
});

const availableLetters = computed(() => ALPHABET.filter((letter) => groups.value.has(letter)));
</script>

<template>
    <SeoHead
        title="Glossary"
        description="An A-Z glossary of Malifaux rules terms and keywords."
    />

    <div class="max-w-4xl mx-auto px-2 sm:px-4 text-foreground leading-6 text-md">
        <div class="w-full max-w-2xl mx-auto text-center py-4">
            <img src='/Images/page_banner_top.png' alt="" class="w-40 sm:w-48 mx-auto opacity-90" />
            <h1 class="font-[family-name:var(--font-display)] text-2xl sm:text-3xl font-medium text-balance my-1">Glossary</h1>
            <img src='/Images/page_banner_bottom.png' alt="" class="w-40 sm:w-48 mx-auto opacity-90" />
        </div>

        <div v-if="availableLetters.length > 1" class="print:hidden sticky top-16 z-10 -mx-2 mb-6 flex flex-wrap justify-center gap-1 bg-background/95 px-2 py-2 backdrop-blur">
            <a
                v-for="letter in availableLetters"
                :key="letter"
                :href="`#letter-${letter}`"
                class="flex size-7 items-center justify-center rounded text-xs font-medium text-muted-foreground hover:bg-muted hover:text-foreground"
            >{{ letter }}</a>
        </div>

        <p v-if="props.entries.length === 0" class="text-center text-muted-foreground italic py-8">
            No glossary terms have been published yet.
        </p>

        <div v-for="letter in availableLetters" :key="letter" :id="`letter-${letter}`" class="mb-8 scroll-mt-24">
            <div class="border-b border-primary/30 pb-1 mb-3 text-sm font-semibold uppercase tracking-[0.14em] text-primary">{{ letter }}</div>
            <div class="space-y-6">
                <div
                    v-for="entry in groups.get(letter)"
                    :key="entry.slug"
                    class="rounded-md border-t-2 border-primary bg-card shadow-sm p-4"
                >
                    <div class="font-semibold mb-1" v-html="entry.title"></div>
                    <img v-if="entry.type === 'image'" :src="entry.image" :alt="entry.title_text" class="mx-auto max-w-xs" />
                    <ParsedContent v-else :content="entry.content" />
                </div>
            </div>
        </div>

        <ScrollToTop />
    </div>
</template>
