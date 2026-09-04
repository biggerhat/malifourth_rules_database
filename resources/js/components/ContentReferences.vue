<script setup lang="ts">
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from '@/components/ui/card'
import FavoriteButton from '@/components/FavoriteButton.vue'
import SuggestEditButton from '@/components/SuggestEditButton.vue'
import { usePage } from '@inertiajs/vue3'
import { computed, type PropType } from 'vue'

interface ReferenceItem {
    title: string;
    type: string;
    url: string;
}

interface RevisionItem {
    title: string;
    published_at: string | null;
    published_by: string | null;
    change_notes: string | null;
    current: boolean;
    active: boolean;
    url: string;
}

interface FavoriteState {
    type: string;
    id: number;
    is_favorited: boolean;
}

interface SuggestionState {
    type: string;
    id: number;
}

const props = defineProps({
    references: {
        type: Array as PropType<ReferenceItem[]>,
        required: false,
        default() { return []; }
    },
    referenced_by: {
        type: Array as PropType<ReferenceItem[]>,
        required: false,
        default() { return []; }
    },
    revision_history: {
        type: Array as PropType<RevisionItem[]>,
        required: false,
        default() { return []; }
    },
    favorite: {
        type: Object as PropType<FavoriteState | null>,
        required: false,
        default: null,
    },
    suggestion: {
        type: Object as PropType<SuggestionState | null>,
        required: false,
        default: null,
    }
});

const page = usePage();
const isLoggedIn = computed(() => Boolean(page.props.auth?.user));

const typeBadgeClass = () => 'border border-border text-muted-foreground';
</script>

<template>
    <div class="flex justify-end gap-2 mt-6 print:hidden" v-if="(props.favorite || props.suggestion) && isLoggedIn">
        <SuggestEditButton v-if="props.suggestion" :type="props.suggestion.type" :id="props.suggestion.id" />
        <FavoriteButton v-if="props.favorite" :type="props.favorite.type" :id="props.favorite.id" :is-favorited="props.favorite.is_favorited" />
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4 print:hidden" v-if="references.length > 0 || referenced_by.length > 0">
        <Card>
            <CardHeader class="pb-3">
                <CardTitle class="text-base">Referenced By</CardTitle>
            </CardHeader>
            <CardContent>
                <ul class="space-y-1" v-if="referenced_by.length > 0">
                    <li v-for="(item, idx) in referenced_by" :key="'by-' + idx">
                        <Link :href="item.url" class="flex items-center gap-2 rounded-md px-2 py-1.5 text-sm hover:bg-muted transition-colors">
                            <span class="shrink-0 rounded-full px-2 py-0.5 text-[0.65rem] font-medium uppercase tracking-wide" :class="typeBadgeClass()">
                                {{ item.type }}
                            </span>
                            <span v-html="item.title" class="truncate"></span>
                        </Link>
                    </li>
                </ul>
                <p v-else class="text-sm text-muted-foreground italic">No items reference this.</p>
            </CardContent>
        </Card>
        <Card>
            <CardHeader class="pb-3">
                <CardTitle class="text-base">References</CardTitle>
            </CardHeader>
            <CardContent>
                <ul class="space-y-1" v-if="references.length > 0">
                    <li v-for="(item, idx) in references" :key="'ref-' + idx">
                        <Link :href="item.url" class="flex items-center gap-2 rounded-md px-2 py-1.5 text-sm hover:bg-muted transition-colors">
                            <span class="shrink-0 rounded-full px-2 py-0.5 text-[0.65rem] font-medium uppercase tracking-wide" :class="typeBadgeClass()">
                                {{ item.type }}
                            </span>
                            <span v-html="item.title" class="truncate"></span>
                        </Link>
                    </li>
                </ul>
                <p v-else class="text-sm text-muted-foreground italic">No references found.</p>
            </CardContent>
        </Card>
    </div>

    <Card class="mt-4 print:hidden" v-if="revision_history.length > 1">
        <CardHeader class="pb-3">
            <CardTitle class="text-base">Revision History</CardTitle>
        </CardHeader>
        <CardContent>
            <ul class="space-y-1">
                <li v-for="(rev, idx) in revision_history" :key="'rev-' + idx">
                    <Link
                        v-if="!rev.active"
                        :href="rev.url"
                        class="flex items-center justify-between rounded-md px-2 py-1.5 text-sm hover:bg-muted transition-colors"
                    >
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="shrink-0 text-xs text-muted-foreground tabular-nums">v{{ revision_history.length - idx }}</span>
                            <span v-html="rev.title" class="truncate"></span>
                            <span v-if="rev.current" class="shrink-0 rounded px-1.5 py-0.5 text-xs font-medium bg-primary/10 text-primary">Current</span>
                        </div>
                        <div class="shrink-0 text-xs text-muted-foreground ml-4">
                            <span v-if="rev.published_at">{{ rev.published_at }}</span>
                            <span v-if="rev.published_by"> by {{ rev.published_by }}</span>
                            <span v-if="rev.change_notes" class="ml-2 italic">&mdash; {{ rev.change_notes }}</span>
                        </div>
                    </Link>
                    <div
                        v-else
                        class="flex items-center justify-between rounded-md px-2 py-1.5 text-sm bg-muted font-medium"
                    >
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="shrink-0 text-xs text-muted-foreground tabular-nums">v{{ revision_history.length - idx }}</span>
                            <span v-html="rev.title" class="truncate"></span>
                            <span v-if="rev.current" class="shrink-0 rounded px-1.5 py-0.5 text-xs font-medium bg-primary/10 text-primary">Current</span>
                        </div>
                        <div class="shrink-0 text-xs text-muted-foreground ml-4">
                            <span v-if="rev.published_at">{{ rev.published_at }}</span>
                            <span v-if="rev.published_by"> by {{ rev.published_by }}</span>
                            <span v-if="rev.change_notes" class="ml-2 italic">&mdash; {{ rev.change_notes }}</span>
                        </div>
                    </div>
                </li>
            </ul>
        </CardContent>
    </Card>
</template>
