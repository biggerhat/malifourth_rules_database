<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { hasPermission } from '@/composables/hasPermission';

interface Suggestion {
    id: number;
    body: string;
    status: 'pending' | 'reviewed' | 'dismissed';
    submitted_by: string | null;
    submitted_at: string;
    reviewed_by: string | null;
    reviewed_at: string | null;
    content: { type: string; title: string; url: string } | null;
}

defineProps<{
    suggestions: Suggestion[];
}>();

const canReview = hasPermission('review_suggestion');

function review(id: number) {
    router.post(route('admin.suggestions.review', id), {}, { preserveScroll: true });
}

function dismiss(id: number) {
    router.post(route('admin.suggestions.dismiss', id), {}, { preserveScroll: true });
}

const statusClass: Record<Suggestion['status'], string> = {
    pending: 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300',
    reviewed: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300',
    dismissed: 'bg-muted text-muted-foreground',
};
</script>

<template>
    <Head title="Suggestions - Admin" />

    <div class="container mx-auto mt-6">
        <h1 class="text-2xl font-semibold mb-4">Reader Suggestions</h1>
        <div class="border rounded-md">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead>Content</TableHead>
                        <TableHead>Suggestion</TableHead>
                        <TableHead>Submitted</TableHead>
                        <TableHead>Status</TableHead>
                        <TableHead v-if="canReview">Actions</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <template v-if="suggestions.length">
                        <TableRow v-for="suggestion in suggestions" :key="suggestion.id">
                            <TableCell>
                                <Link v-if="suggestion.content" :href="suggestion.content.url" class="hover:underline">
                                    <span class="text-xs uppercase text-muted-foreground mr-1">{{ suggestion.content.type }}</span>
                                    <span v-html="suggestion.content.title"></span>
                                </Link>
                                <span v-else class="text-sm text-muted-foreground italic">No longer published</span>
                            </TableCell>
                            <TableCell class="max-w-md whitespace-pre-wrap">{{ suggestion.body }}</TableCell>
                            <TableCell class="text-sm text-muted-foreground">
                                {{ suggestion.submitted_by }}<br />{{ suggestion.submitted_at }}
                            </TableCell>
                            <TableCell>
                                <span class="rounded px-2 py-0.5 text-xs font-medium" :class="statusClass[suggestion.status]">
                                    {{ suggestion.status }}
                                </span>
                                <div v-if="suggestion.reviewed_by" class="text-xs text-muted-foreground mt-1">
                                    by {{ suggestion.reviewed_by }}
                                </div>
                            </TableCell>
                            <TableCell v-if="canReview">
                                <div class="flex gap-2" v-if="suggestion.status === 'pending'">
                                    <Button size="sm" variant="outline" @click="review(suggestion.id)">Mark reviewed</Button>
                                    <Button size="sm" variant="ghost" @click="dismiss(suggestion.id)">Dismiss</Button>
                                </div>
                            </TableCell>
                        </TableRow>
                    </template>
                    <TableRow v-else>
                        <TableCell :colspan="canReview ? 5 : 4" class="h-24 text-center">
                            No suggestions yet.
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>
    </div>
</template>
