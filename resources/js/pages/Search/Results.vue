<script setup lang="ts">
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"
import { computed } from "vue"

const props = defineProps({
    query: String,
    queryTerms: {
        type: Array as () => string[],
        required: false,
        default: () => [],
    },
    pages: {
        type: [Object, Array],
        required: false,
        default: () => [],
    },
    sections: {
        type: [Object, Array],
        required: false,
        default: () => [],
    },
    indices: {
        type: [Object, Array],
        required: false,
        default: () => [],
    },
    faqs: {
        type: [Object, Array],
        required: false,
        default: () => [],
    },
    seasons: {
        type: [Object, Array],
        required: false,
        default: () => [],
    },
    strategies: {
        type: [Object, Array],
        required: false,
        default: () => [],
    },
    season_pages: {
        type: [Object, Array],
        required: false,
        default: () => [],
    },
    schemes: {
        type: [Object, Array],
        required: false,
        default: () => [],
    },
    errata: {
        type: [Object, Array],
        required: false,
        default: () => [],
    },
})

interface ResultItem {
    id: string | number
    type: string
    title: string
    htmlTitle: boolean
    snippet: string | null
    href: string
}

interface ResultGroup {
    label: string
    items: ResultItem[]
}

const groups = computed<ResultGroup[]>(() => {
    const raw: ResultGroup[] = [
        {
            label: 'Pages',
            items: (props.pages as any[]).map(p => ({
                id: p.id,
                type: 'Page',
                title: p.title,
                htmlTitle: true,
                snippet: p.snippet,
                href: route('rules.page.view', p.slug),
            })),
        },
        {
            label: 'Sections',
            items: (props.sections as any[]).map(s => ({
                id: s.id,
                type: 'Section',
                title: s.title,
                htmlTitle: true,
                snippet: s.snippet,
                href: route('rules.section.view', s.slug),
            })),
        },
        {
            label: 'Indices',
            items: (props.indices as any[]).map(i => ({
                id: i.id,
                type: 'Index',
                title: i.title,
                htmlTitle: true,
                snippet: i.snippet,
                href: route('rules.index.view', i.slug),
            })),
        },
        {
            label: 'FAQs',
            items: (props.faqs as any[]).map(f => ({
                id: f.id,
                type: 'FAQ',
                title: f.title,
                htmlTitle: true,
                snippet: f.snippet,
                href: route('rules.faq.view', f.slug),
            })),
        },
        {
            label: 'Seasons',
            items: (props.seasons as any[]).map(s => ({
                id: s.id,
                type: 'Season',
                title: s.title,
                htmlTitle: false,
                snippet: s.snippet,
                href: route('rules.gaining-grounds.season', s.slug),
            })),
        },
        {
            label: 'Strategies',
            items: (props.strategies as any[]).map(s => ({
                id: s.id,
                type: 'Strategy',
                title: s.title,
                htmlTitle: false,
                snippet: s.snippet,
                href: route('rules.gaining-grounds.strategy', s.slug),
            })),
        },
        {
            label: 'Season Pages',
            items: (props.season_pages as any[]).map(sp => ({
                id: sp.id,
                type: 'Season Page',
                title: sp.title,
                htmlTitle: false,
                snippet: sp.snippet,
                href: route('rules.gaining-grounds.season-page', [sp.season_slug, sp.slug]),
            })),
        },
        {
            label: 'Schemes',
            items: (props.schemes as any[]).map(s => ({
                id: s.id,
                type: 'Scheme',
                title: s.title,
                htmlTitle: false,
                snippet: s.snippet,
                href: route('rules.gaining-grounds.scheme', s.slug),
            })),
        },
        {
            label: 'Errata',
            items: (props.errata as any[]).map(e => ({
                id: e.id,
                type: 'Errata',
                title: e.title,
                htmlTitle: false,
                snippet: e.snippet,
                href: route('errata.view', e.slug),
            })),
        },
    ]

    return raw.filter(g => g.items.length > 0)
})

const totalResults = computed(() => groups.value.reduce((sum, g) => sum + g.items.length, 0))

function highlightSnippet(snippet: string): string {
    if (!snippet || !props.queryTerms?.length) return snippet

    let result = snippet
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')

    for (const term of props.queryTerms) {
        const escaped = term.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')
        const regex = new RegExp(`(${escaped})`, 'gi')
        result = result.replace(regex, '<mark class="bg-yellow-200 dark:bg-yellow-800 rounded px-0.5">$1</mark>')
    }

    return result
}
</script>

<template>
    <div class="max-w-4xl mx-auto px-2 sm:px-4 py-6 space-y-6">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold">Search results for "{{ props.query }}"</h1>
            <p class="text-sm text-muted-foreground mt-1">{{ totalResults }} result{{ totalResults !== 1 ? 's' : '' }} found</p>
        </div>

        <div v-if="totalResults === 0" class="text-muted-foreground">
            No results found. Try a different search term.
        </div>

        <div v-for="group in groups" :key="group.label" class="space-y-3">
            <h2 class="text-lg font-semibold text-muted-foreground">{{ group.label }}</h2>
            <div class="grid gap-3">
                <Card v-for="item in group.items" :key="item.id" class="shadow-md py-4 gap-2">
                    <CardHeader>
                        <CardTitle class="text-base flex items-center gap-2">
                            <span class="inline-flex items-center rounded-md bg-muted px-2 py-0.5 text-xs font-medium text-muted-foreground shrink-0">{{ item.type }}</span>
                            <Link :href="item.href">
                                <span v-if="item.htmlTitle" v-html="item.title"></span>
                                <template v-else>{{ item.title }}</template>
                            </Link>
                        </CardTitle>
                    </CardHeader>
                    <CardContent v-if="item.snippet" class="text-sm text-muted-foreground">
                        <p v-html="highlightSnippet(item.snippet)" />
                    </CardContent>
                </Card>
            </div>
        </div>
    </div>
</template>
