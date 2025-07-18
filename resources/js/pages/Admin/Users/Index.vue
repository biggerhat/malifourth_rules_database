<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { h, ref } from 'vue';
import type { ColumnDef, ColumnFiltersState } from '@tanstack/vue-table';
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { valueUpdater } from '@/lib/utils'
import AdminActions from '@/components/AdminActions.vue';
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

const columns: ColumnDef<Users>[] = [
    {
        accessorKey: 'name',
        header: () => h('div', {}, 'User'),
        cell: ({ row }) => {
            return h('div', {}, row.getValue('name'))
        },
    },{
        accessorKey: 'email',
        header: () => h('div', {}, 'Email'),
        cell: ({ row }) => {
            return h('div', {}, row.getValue('email'))
        },
    },{
        accessorKey: 'roles',
        header: () => h('div', {}, 'Role'),
        cell: ({ row }) => {
            const roles = row.original.roles;

            return h('div', {}, roles.map(role => role.name).join(', '))
        },
    },{
        id: 'actions',
        enableHiding: false,
        header: () => h('div', {}, 'Actions'),
        cell: ({ row }) => {
            const user = row.original;

            return h('div', { class: 'relative' }, h(AdminActions, {
                name: user.name,
                modelName: 'user',
                editRoute: route('admin.users.edit', user.id),
                deleteRoute: route('admin.users.delete', user.id)
            }))
        },
    },
];

const props = defineProps<{
    users: TData[]
}>();

const columnFilters = ref<ColumnFiltersState>([])

const table = useVueTable({
    get data() { return props.users },
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
    <Head title="Users - Admin" />

    <div class="container mx-auto mt-6">
        <div class="flex items-center justify-between py-4">
            <Input class="max-w-sm" placeholder="Filter Users"
                   :model-value="table.getColumn('name')?.getFilterValue() as string"
                   @update:model-value=" table.getColumn('name')?.setFilterValue($event)" />
            <Button @click="router.get(route('admin.users.create'))" v-if="hasPermission('add_user')">
                Create New User
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
