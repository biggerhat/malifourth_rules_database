<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { h, ref } from 'vue';
import type { ColumnDef } from '@tanstack/vue-table';
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import AdminActions from '@/components/AdminActions.vue';
import { Check, Ban } from 'lucide-vue-next';

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

interface NavigationItem {
    id: number;
    title: string;
    linkable_type: string | null;
    linkable_id: number | null;
    linkable: { id: number; title: string } | null;
    sort_order: number;
    is_active: boolean;
}

const linkableLabel = (item: NavigationItem): string => {
    if (!item.linkable_type || !item.linkable) return '-';
    const type = item.linkable_type.split('\\').pop();
    return `${type}: ${item.linkable.title}`;
};

const columns: ColumnDef<NavigationItem>[] = [
    {
        accessorKey: 'title',
        header: () => h('div', {}, 'Title'),
        cell: ({ row }) => h('div', {}, row.getValue('title')),
    },
    {
        id: 'links_to',
        header: () => h('div', {}, 'Links To'),
        cell: ({ row }) => h('div', {}, linkableLabel(row.original)),
    },
    {
        accessorKey: 'sort_order',
        header: () => h('div', {}, 'Sort Order'),
        cell: ({ row }) => h('div', {}, row.getValue('sort_order')),
    },
    {
        accessorKey: 'is_active',
        header: () => h('div', {}, 'Active'),
        cell: ({ row }) => {
            return row.getValue('is_active')
                ? h(Check, { class: 'h-4 w-4 text-green-500' })
                : h(Ban, { class: 'h-4 w-4 text-red-500' });
        },
    },
    {
        id: 'actions',
        enableHiding: false,
        enableGlobalFilter: false,
        header: () => h('div', {}, 'Actions'),
        cell: ({ row }) => {
            const item = row.original;
            return h('div', { class: 'relative' }, h(AdminActions, {
                name: item.title,
                modelName: 'navigationItem',
                editRoute: route('admin.navigation.edit', item.id),
                deleteRoute: route('admin.navigation.delete', item.id),
            }));
        },
    },
];

const props = defineProps<{
    navigationItems: NavigationItem[];
}>();

const globalFilter = ref('');

const table = useVueTable({
    get data() { return props.navigationItems },
    get columns() { return columns },
    getCoreRowModel: getCoreRowModel(),
    getPaginationRowModel: getPaginationRowModel(),
    getFilteredRowModel: getFilteredRowModel(),
    globalFilterFn: 'includesString',
    state: {
        get globalFilter() { return globalFilter.value },
    },
});
</script>

<template>
    <Head title="Navigation - Admin" />

    <div class="container mx-auto mt-6">
        <div class="flex items-center justify-between py-4">
            <Input class="max-w-sm" placeholder="Search..."
                   v-model="globalFilter" />
            <Button @click="router.get(route('admin.navigation.create'))">
                Create Navigation Item
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
