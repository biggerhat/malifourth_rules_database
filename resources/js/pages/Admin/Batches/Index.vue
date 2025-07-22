<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { h, ref } from 'vue';
import type { ColumnDef, ColumnFiltersState } from '@tanstack/vue-table';
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { valueUpdater } from '@/lib/utils'
import AdminActions from '@/components/AdminActions.vue';
import {Ban, Check} from "lucide-vue-next";

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
import {hasPermission} from "@/composables/hasPermission";
import AdminInternalNotes from "@/components/AdminInternalNotes.vue";

const columns: ColumnDef<Batches>[] = [
    {
        accessorKey: 'title',
        header: () => h('div', {}, 'Batch'),
        cell: ({ row }) => {
            const batch = row.original;

            if (batch.internal_notes) {
                return h('div', {}, h(AdminInternalNotes, {
                    title: batch.title,
                    internal_note: batch.internal_notes,
                }));
            }

            return h('div', {}, row.getValue('title'));
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
            const batch = row.original;

            return h('div', {}, batch.approval?.approved_at ? h(Check, {class: 'text-green-500 mx-auto'}) : h(Ban, {class: 'text-red-500 mx-auto'}))
        },
    },{
        id: 'actions',
        enableHiding: false,
        header: () => h('div', {}, 'Actions'),
        cell: ({ row }) => {
            const batch = row.original;
            const publishable = batch.approval?.approved_at && !batch.published_at;

            return h('div', { class: 'relative' }, h(AdminActions, {
                name: batch.title,
                modelName: 'batch',
                editRoute: batch.published_at ? null : route('admin.batches.edit', batch.slug),
                deleteRoute: batch.published_at ? null : route('admin.batches.delete', batch.slug),
                approvalRoute: batch.is_approvable ? route('admin.approvals.update', batch.approval?.id) : null,
                publishRoute: publishable ? route('admin.batches.publish', batch.slug) : null,
            }))
        },
    },
];

const props = defineProps<{
    batches: TData[]
}>();

const columnFilters = ref<ColumnFiltersState>([])

const table = useVueTable({
    get data() { return props.batches },
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
    <Head title="Batches - Admin" />

    <div class="container mx-auto mt-6">
        <div class="flex items-center justify-between py-4">
            <Input class="max-w-sm" placeholder="Filter Batches"
                   :model-value="table.getColumn('title')?.getFilterValue() as string"
                   @update:model-value=" table.getColumn('title')?.setFilterValue($event)" />
            <Button @click="router.get(route('admin.batches.create'))" v-if="hasPermission('add_batch')">
                Create New Batch
            </Button>
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
