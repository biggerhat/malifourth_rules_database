<script setup lang="ts">
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from '@/components/ui/card'
import type { PropType } from 'vue'

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
    url: string;
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
    }
});

const typeBadgeClass = (type: string) => {
    switch (type) {
        case 'Page': return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200';
        case 'Section': return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200';
        case 'Index': return 'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200';
        default: return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200';
    }
};
</script>

<template>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-6" v-if="references.length > 0 || referenced_by.length > 0">
        <Card>
            <CardHeader class="pb-3">
                <CardTitle class="text-base">Referenced By</CardTitle>
            </CardHeader>
            <CardContent>
                <ul class="space-y-1" v-if="referenced_by.length > 0">
                    <li v-for="(item, idx) in referenced_by" :key="'by-' + idx">
                        <Link :href="item.url" class="flex items-center gap-2 rounded-md px-2 py-1.5 text-sm hover:bg-muted transition-colors">
                            <span class="shrink-0 rounded px-1.5 py-0.5 text-xs font-medium" :class="typeBadgeClass(item.type)">
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
                            <span class="shrink-0 rounded px-1.5 py-0.5 text-xs font-medium" :class="typeBadgeClass(item.type)">
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

    <Card class="mt-4" v-if="revision_history.length > 1">
        <CardHeader class="pb-3">
            <CardTitle class="text-base">Revision History</CardTitle>
        </CardHeader>
        <CardContent>
            <ul class="space-y-1">
                <li v-for="(rev, idx) in revision_history" :key="'rev-' + idx">
                    <Link
                        v-if="!rev.current"
                        :href="rev.url"
                        class="flex items-center justify-between rounded-md px-2 py-1.5 text-sm hover:bg-muted transition-colors"
                    >
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="shrink-0 text-xs text-muted-foreground tabular-nums">v{{ revision_history.length - idx }}</span>
                            <span v-html="rev.title" class="truncate"></span>
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
                            <span class="shrink-0 rounded px-1.5 py-0.5 text-xs font-medium bg-primary/10 text-primary">Current</span>
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
