<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { computed, h, ref } from 'vue';
import type { ColumnDef } from '@tanstack/vue-table';
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Checkbox } from "@/components/ui/checkbox";
import { valueUpdater } from '@/lib/utils';
import AdminActions from '@/components/AdminActions.vue';
import BulkActionBar from '@/components/BulkActionBar.vue';
import {Ban, Check} from "lucide-vue-next";
import { hasPermission } from "@/composables/hasPermission";
import CardErrataView from "@/pages/Errata/CardErrata/CardErrataView.vue";

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

const columns: ColumnDef<any>[] = [
    {
        id: 'select',
        header: ({ table }) => h(Checkbox, {
            'checked': table.getIsAllPageRowsSelected() || (table.getIsSomePageRowsSelected() && 'indeterminate'),
            'onUpdate:checked': (value: boolean) => table.toggleAllPageRowsSelected(!!value),
        }),
        cell: ({ row }) => h(Checkbox, {
            'checked': row.getIsSelected(),
            'onUpdate:checked': (value: boolean) => row.toggleSelected(!!value),
        }),
        enableGlobalFilter: false,
    },
    {
        accessorKey: 'card_name',
        header: () => h('div', {}, 'Card'),
        cell: ({ row }) => {
            const cardErrata = row.original;

            if (cardErrata.internal_notes) {
                return h('div', {}, h(AdminInternalNotes, {
                    title: cardErrata.card_name,
                    internal_note: cardErrata.internal_notes,
                }));
            }

            return h('div', {}, row.getValue('card_name'));
        },
    },{
        accessorKey: 'faction',
        header: () => h('div', {}, 'Faction'),
        cell: ({ row }) => h('div', { class: 'capitalize' }, String(row.getValue('faction')).replace(/_/g, ' ')),
    },{
        accessorFn: (row) => row.entries?.length ?? 0,
        id: 'entries',
        enableGlobalFilter: false,
        header: () => h('div', { class: 'text-center' }, 'Errata Entries'),
        cell: ({ row }) => h('div', { class: 'text-center' }, row.original.entries?.length ?? 0),
    },{
        accessorFn: (row) => row.batch?.title ?? '',
        id: 'batch',
        header: () => h('div', {}, 'Batch'),
        cell: ({ row }) => {
            const cardErrata = row.original;
            return h('div', {}, cardErrata.batch?.title)
        },
    },{
        accessorKey: 'published_at',
        enableGlobalFilter: false,
        header: () => h('div', {class: 'text-center'}, 'Published'),
        cell: ({ row }) => {
            return h('div', {}, row.getValue('published_at') ? h(Check, {class: 'text-green-500 mx-auto'}) : h(Ban, {class: 'text-red-500 mx-auto'}))
        },
    },{
        accessorKey: 'approved',
        enableGlobalFilter: false,
        header: () => h('div', {class: 'text-center'}, 'Approved'),
        cell: ({ row }) => {
            const cardErrata = row.original;

            return h('div', {}, cardErrata.approval?.approved_at ? h(Check, {class: 'text-green-500 mx-auto'}) : h(Ban, {class: 'text-red-500 mx-auto'}))
        },
    },{
        id: 'actions',
        enableHiding: false,
        enableGlobalFilter: false,
        header: () => h('div', {}, 'Actions'),
        cell: ({ row }) => {
            const cardErrata = row.original;
            const publishable = cardErrata.approval?.approved_at && !cardErrata.published_at;

            return h('div', { class: 'relative' }, h(AdminActions, {
                name: cardErrata.card_name,
                modelName: 'card_errata',
                viewRoute: route('admin.card-errata.view', cardErrata.slug),
                viewComponent: CardErrataView,
                editRoute: route('admin.card-errata.edit', cardErrata.slug),
                deleteRoute: cardErrata.published_at ? null : route('admin.card-errata.delete', cardErrata.slug),
                approvalRoute: cardErrata.approval?.approved_at ? null : route('admin.approvals.update', cardErrata.approval?.id),
                publishRoute: publishable ? route('admin.card-errata.publish', cardErrata.slug) : null,
            }))
        },
    },
];

const props = defineProps<{
    cardErratas: TData[]
}>();

const globalFilter = ref('')
const rowSelection = ref({})

const table = useVueTable({
    get data() { return props.cardErratas },
    get columns() { return columns },
    getCoreRowModel: getCoreRowModel(),
    getPaginationRowModel: getPaginationRowModel(),
    getFilteredRowModel: getFilteredRowModel(),
    globalFilterFn: 'includesString',
    enableRowSelection: true,
    onRowSelectionChange: updaterOrValue => valueUpdater(updaterOrValue, rowSelection),
    state: {
        get globalFilter() { return globalFilter.value },
        get rowSelection() { return rowSelection.value },
    }
});

const selectedIds = computed(() =>
    Object.keys(rowSelection.value).map(idx => props.cardErratas[Number(idx)]?.id).filter(Boolean)
);

const clearSelection = () => { rowSelection.value = {} };
</script>

<template>
    <Head title="Card Errata - Admin" />

    <div class="container mx-auto mt-6">
        <div class="flex items-center justify-between py-4">
            <Input class="max-w-sm" placeholder="Search..."
                   v-model="globalFilter" />
            <div class="flex gap-1">
                <Button @click="router.get(route('admin.card-errata.create'))" v-if="hasPermission('add_card_errata')">
                    Create New Card Errata
                </Button>
            </div>
        </div>
        <BulkActionBar
            v-if="Object.keys(rowSelection).length > 0"
            :selected-count="selectedIds.length"
            :selected-ids="selectedIds"
            model-name="card_errata"
            :bulk-approve-route="route('admin.card-errata.bulk-approve')"
            :bulk-publish-route="route('admin.card-errata.bulk-publish')"
            :bulk-delete-route="route('admin.card-errata.bulk-delete')"
            @clear="clearSelection"
            class="mb-4"
        />
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
