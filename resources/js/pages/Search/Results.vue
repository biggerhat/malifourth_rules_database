<script setup lang="ts">
import { router } from "@inertiajs/vue3"
import { Link } from "@inertiajs/vue3"
import { computed } from "vue"

interface ResultItem {
    id: string | number
    type: string
    title: string
    slug: string
    season_slug?: string | null
    snippet: string | null
}

interface Pagination {
    current_page: number
    last_page: number
    total: number
}

const props = defineProps<{
    query: string
    queryTerms: string[]
    types: string[]
    selectedTypes: string[]
    counts: Record<string, number>
    results: ResultItem[]
    pagination: Pagination
}>()

const typeMeta: Record<string, { label: string; htmlTitle: boolean; href: (item: ResultItem) => string }> = {
    pages: { label: 'Page', htmlTitle: true, href: (i) => route('rules.page.view', i.slug) },
    sections: { label: 'Section', htmlTitle: true, href: (i) => route('rules.section.view', i.slug) },
    indices: { label: 'Index', htmlTitle: true, href: (i) => route('rules.index.view', i.slug) },
    faqs: { label: 'FAQ', htmlTitle: false, href: (i) => route('rules.faq.view', i.slug) },
    seasons: { label: 'Season', htmlTitle: false, href: (i) => route('rules.gaining-grounds.season', i.slug) },
    strategies: { label: 'Strategy', htmlTitle: false, href: (i) => route('rules.gaining-grounds.strategy', i.slug) },
    season_pages: { label: 'Season Page', htmlTitle: false, href: (i) => route('rules.gaining-grounds.season-page', [i.season_slug, i.slug]) },
    schemes: { label: 'Scheme', htmlTitle: false, href: (i) => route('rules.gaining-grounds.scheme', i.slug) },
    errata: { label: 'Errata', htmlTitle: false, href: (i) => route('errata.view', i.slug) },
}

function label(type: string): string {
    return typeMeta[type]?.label ?? type
}

function isHtmlTitle(type: string): boolean {
    return typeMeta[type]?.htmlTitle ?? false
}

function href(item: ResultItem): string {
    return typeMeta[item.type]?.href(item) ?? '#'
}

function isChecked(type: string): boolean {
    return props.selectedTypes.includes(type)
}

function toggleType(type: string) {
    const next = isChecked(type)
        ? props.selectedTypes.filter((t) => t !== type)
        : [...props.selectedTypes, type]

    // Selecting nothing is treated the same as selecting everything server-side,
    // so reflect that back into the URL rather than sending an empty filter.
    const types = next.length === 0 ? props.types : next

    router.get(route('search'), { q: props.query, types: types.join(',') }, { preserveState: true, preserveScroll: true, replace: true })
}

function goToPage(page: number) {
    router.get(
        route('search'),
        { q: props.query, types: props.selectedTypes.join(','), page },
        { preserveState: true, preserveScroll: true },
    )
}

const allTypesSelected = computed(() => props.selectedTypes.length === props.types.length)

function highlightSnippet(snippet: string): string {
    if (!snippet || !props.queryTerms?.length) return snippet

    let result = snippet
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')

    for (const term of props.queryTerms) {
        const escaped = term.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')
        const regex = new RegExp(`(${escaped})`, 'gi')
        result = result.replace(regex, '<mark class="bg-primary/25 text-inherit rounded px-0.5">$1</mark>')
    }

    return result
}
</script>

<template>
    <div class="max-w-4xl mx-auto px-2 sm:px-4 py-6 space-y-6">
        <div>
            <h1 class="font-[family-name:var(--font-display)] text-2xl sm:text-3xl font-medium text-balance">Search results for &ldquo;{{ props.query }}&rdquo;</h1>
            <p class="text-sm text-muted-foreground mt-1">{{ props.pagination.total }} result{{ props.pagination.total !== 1 ? 's' : '' }} found</p>
        </div>

        <div class="flex flex-wrap gap-1.5 print:hidden">
            <button
                v-for="type in props.types"
                :key="type"
                type="button"
                @click="toggleType(type)"
                :aria-pressed="isChecked(type)"
                class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs font-medium transition-colors"
                :class="isChecked(type)
                    ? 'border-primary bg-primary/10 text-primary'
                    : 'border-border text-muted-foreground hover:text-foreground'"
            >
                {{ label(type) }}
                <span class="tabular-nums opacity-70">{{ props.counts[type] ?? 0 }}</span>
            </button>
            <button
                v-if="!allTypesSelected"
                type="button"
                @click="router.get(route('search'), { q: props.query, types: props.types.join(',') }, { preserveState: true, preserveScroll: true, replace: true })"
                class="text-xs text-muted-foreground underline underline-offset-2 hover:text-foreground px-1"
            >
                Reset filters
            </button>
        </div>

        <div v-if="props.results.length === 0" class="text-muted-foreground">
            No results found. Try a different search term{{ !allTypesSelected ? ' or clear your type filters' : '' }}.
        </div>

        <div v-else class="grid gap-3">
            <div v-for="item in props.results" :key="`${item.type}-${item.id}`" class="rounded-md border-t-2 border-primary bg-card shadow-sm px-4 py-3">
                <div class="text-base font-medium flex items-center gap-2">
                    <span class="inline-flex items-center rounded-full border border-border px-2 py-0.5 text-[0.65rem] font-medium uppercase tracking-wide text-muted-foreground shrink-0">{{ label(item.type) }}</span>
                    <Link :href="href(item)" class="hover:text-primary transition-colors">
                        <span v-if="isHtmlTitle(item.type)" v-html="item.title"></span>
                        <template v-else>{{ item.title }}</template>
                    </Link>
                </div>
                <p v-if="item.snippet" class="text-sm text-muted-foreground mt-1.5" v-html="highlightSnippet(item.snippet)" />
            </div>
        </div>

        <div v-if="props.pagination.last_page > 1" class="flex items-center justify-center gap-2 print:hidden">
            <button
                v-if="props.pagination.current_page > 1"
                type="button"
                @click="goToPage(props.pagination.current_page - 1)"
                class="text-sm rounded-md border px-3 py-1.5 hover:bg-muted/50 transition-colors"
            >
                Previous
            </button>
            <span class="text-xs text-muted-foreground">Page {{ props.pagination.current_page }} of {{ props.pagination.last_page }}</span>
            <button
                v-if="props.pagination.current_page < props.pagination.last_page"
                type="button"
                @click="goToPage(props.pagination.current_page + 1)"
                class="text-sm rounded-md border px-3 py-1.5 hover:bg-muted/50 transition-colors"
            >
                Next
            </button>
        </div>
    </div>
</template>
