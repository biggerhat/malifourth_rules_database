<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { h, ref } from 'vue';
import type { ColumnDef, ColumnFiltersState } from '@tanstack/vue-table';
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { valueUpdater } from '@/lib/utils'
import AdminActions from '@/components/AdminActions.vue';
import {Ban, Check} from "lucide-vue-next";
import { hasPermission } from "@/composables/hasPermission";

import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';

import {
    FlexRender,
    getCoreRowModel,
    getPaginationRowModel,
    getFilteredRowModel,
    useVueTable,
} from '@tanstack/vue-table';
import AdminInternalNotes from "@/components/AdminInternalNotes.vue";

const columns: ColumnDef<Seasons>[] = [
    {
        accessorKey: 'title',
        header: () => h('div', {}, 'Season'),
        cell: ({ row }) => {
            const season = row.original;

            if (season.internal_notes) {
                return h('div', {}, h(AdminInternalNotes, {
                    title: season.title,
                    internal_note: season.internal_notes,
                }));
            }

            return h('div', {}, row.getValue('title'));
        },
    },{
        accessorKey: 'batch',
        header: () => h('div', {}, 'Batch'),
        cell: ({ row }) => {
            const season = row.original;
            return h('div', {}, season.batch?.title)
        },
    },{
        accessorKey: 'published_at',
        header: () => h('div', {class: 'text-center'}, 'Published'),
        cell: ({ row }) => {
            return h('div', {}, row.getValue('published_at') ? h(Check, {class: 'text-green-500 mx-auto'}) : h(Ban, {class: 'text-red-500 mx-auto'}))
        },
    },{
        accessorKey: 'approved',
        header: () => h('div', {class: 'text-center'}, 'Approved'),
        cell: ({ row }) => {
            const season = row.original;

            return h('div', {}, season.approval?.approved_at ? h(Check, {class: 'text-green-500 mx-auto'}) : h(Ban, {class: 'text-red-500 mx-auto'}))
        },
    },{
        id: 'actions',
        enableHiding: false,
        header: () => h('div', {}, 'Actions'),
        cell: ({ row }) => {
            const season = row.original;
            const publishable = season.approval?.approved_at && !season.published_at;

            return h('div', { class: 'relative' }, h(AdminActions, {
                name: season.title,
                modelName: 'season',
                viewRoute: route('admin.seasons.view', season.slug),
                editRoute: route('admin.seasons.edit', season.slug),
                deleteRoute: season.published_at ? null : route('admin.seasons.delete', season.slug),
                approvalRoute: season.approval?.approved_at ? null : route('admin.approvals.update', season.approval?.id),
                publishRoute: publishable ? route('admin.seasons.publish', season.slug) : null,
            }))
        },
    },
];

const props = defineProps<{
    seasons: TData[]
}>();

const columnFilters = ref<ColumnFiltersState>([])

const table = useVueTable({
    get data() { return props.seasons },
    get columns() { return columns },
    getCoreRowModel: getCoreRowModel(),
    getPaginationRowModel: getPaginationRowModel(),
    onColumnFiltersChange: updaterOrValue => valueUpdater(updaterOrValue, columnFilters),
    getFilteredRowModel: getFilteredRowModel(),
    state: {
        get columnFilters() { return columnFilters.value },
    }
});
</script>

<template>
    <Head title="Seasons - Admin" />

    <div class="container mx-auto mt-6">
        <div class="flex items-center justify-between py-4">
            <Input class="max-w-sm" placeholder="Filter Seasons"
                   :model-value="table.getColumn('title')?.getFilterValue() as string"
                   @update:model-value=" table.getColumn('title')?.setFilterValue($event)" />
            <div class="flex gap-1">
                <Button @click="router.get(route('admin.seasons.create'))" v-if="hasPermission('add_season')">
                    Create New Season
                </Button>
            </div>
        </div>
        <div class="border rounded-md">
            <Table>
                <TableHeader>
                    <TableRow v-for="headerGroup in table.getHeaderGroups()" :key="headerGroup.id">
                        <TableHead v-for="header in headerGroup.headers" :key="header.id">
                            <FlexRender
                                v-if="!header.isPlaceholder" :render="header.column.columnDef.header"
                                :props="header.getContext()"
                            />
                        </TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <template v-if="table.getRowModel().rows?.length">
                        <TableRow
                            v-for="row in table.getRowModel().rows" :key="row.id"
                            :data-state="row.getIsSelected() ? 'selected' : undefined"
                        >
                            <TableCell v-for="cell in row.getVisibleCells()" :key="cell.id">
                                <FlexRender :render="cell.column.columnDef.cell" :props="cell.getContext()" />
                            </TableCell>
                        </TableRow>
                    </template>
                    <template v-else>
                        <TableRow>
                            <TableCell :colspan="columns.length" class="h-24 text-center">
                                No results.
                            </TableCell>
                        </TableRow>
                    </template>
                </TableBody>
            </Table>
        </div>
        <div class="flex items-center justify-end py-4 space-x-2">
            <Button
                variant="outline"
                size="sm"
                :disabled="!table.getCanPreviousPage()"
                @click="table.previousPage()"
            >
                Previous
            </Button>
            <Button
                variant="outline"
                size="sm"
                :disabled="!table.getCanNextPage()"
                @click="table.nextPage()"
            >
                Next
            </Button>
        </div>
    </div>
</template>
