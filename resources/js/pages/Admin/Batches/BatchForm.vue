<script setup lang='ts'>
import { onMounted } from 'vue';
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Label } from '@/components/ui/label'
import { valueUpdater } from '@/lib/utils'
import InputError from "@/components/InputError.vue";
import {LoaderCircle} from "lucide-vue-next";
import { Textarea } from '@/components/ui/textarea'
import { Checkbox } from "@/components/ui/checkbox";
import RichTextEditor from "@/components/RichTextEditor.vue";
import { Head, router, useForm } from '@inertiajs/vue3';
import { h, ref } from 'vue';
import type { ColumnDef, ColumnFiltersState } from '@tanstack/vue-table';
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import AdminActions from '@/components/AdminActions.vue';
import IndexView from "@/pages/Rules/IndexView.vue";
import SectionView from "@/pages/Rules/SectionView.vue";
import PageView from "@/pages/Rules/PageView.vue";
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

const props = defineProps({
    batch: {
        type: [Object, Array],
        required: false,
        default() {
            return null;
        }
    },
    batchables: {
        type: [Object, Array],
        required: false,
        default() {
            return null;
        }
    },
});

const back = () => {
    history.back();
};

const form = useForm({
    title: '',
    release_notes: '',
    internal_notes: '',
});

onMounted(() => {
    form.title = props.batch?.title ?? null;
    form.release_notes = props.batch?.release_notes ?? '';
    form.internal_notes = props.batch?.internal_notes ?? '';
});

const submit = () => {
    if (props.batch) {
        form.post(route('admin.batches.update', {batch: props.batch.slug}));
    } else {
        form.post(route('admin.batches.store', {}));
    }
}

const columns: ColumnDef<Batches>[] = [
    {
        accessorKey: 'title',
        header: () => h('div', {}, 'Batch Item'),
        cell: ({ row }) => {
            const batchable = row.original;

            if (batchable.internal_notes) {
                return h('div', {}, h(AdminInternalNotes, {
                    title: batchable.title,
                    internal_note: batchable.internal_notes,
                }));
            }

            return h('div', {}, row.getValue('title'));
        },
    },{
        accessorKey: 'type',
        header: () => h('div', {class: 'text-center'}, 'Type'),
        cell: ({ row }) => {
            return h('div', {}, row.getValue('type'))
        },
    },{
        accessorKey: 'approved',
        header: () => h('div', {class: 'text-center'}, 'Approved'),
        cell: ({ row }) => {
            const batchable = row.original;

            return h('div', {}, batchable.approved_at ? h(Check, {class: 'text-green-500 mx-auto'}) : h(Ban, {class: 'text-red-500 mx-auto'}))
        },
    },{
        id: 'actions',
        enableHiding: false,
        header: () => h('div', {}, 'Actions'),
        cell: ({ row }) => {
            const batchable = row.original;

            let viewComponent  = null;
            switch(batchable.component) {
                case 'indexView':
                    viewComponent = IndexView;
                    break;
                case 'sectionView':
                    viewComponent = SectionView;
                    break;
                case 'pageView':
                    viewComponent = PageView;
                    break;
                default:
                    viewComponent = null;
            }


            return h('div', { class: 'relative' }, h(AdminActions, {
                name: batchable.title,
                modelName: batchable.model,
                viewRoute: route(batchable.route_prefix + '.view', batchable.slug),
                viewComponent: viewComponent,
                editRoute: batchable.published_at ? null : route(batchable.route_prefix + '.edit', batchable.slug),
                deleteRoute: batchable.published_at ? null : route(batchable.route_prefix + '.delete', batchable.slug),
                approvalRoute: batchable.is_approvable ? route(batchable.route_prefix + '.update', batchable.approval_id) : null,
                publishRoute: null,
            }))
        },
    },
];

const columnFilters = ref<ColumnFiltersState>([])

const table = useVueTable({
    get data() { return props.batchables },
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
    <Head title="Batch Information" />

    <Card>
        <CardHeader>
            <CardTitle>Batch Form</CardTitle>
            <CardDescription>Create and Edit Batch Information</CardDescription>
        </CardHeader>
        <CardContent>
            <form @submit.prevent>
                <div class="grid items-center w-full gap-4">
                    <div class="flex flex-col space-y-1.5">
                        <Label for="title">Batch</Label>
                        <Input id="title" type="text" required autofocus :tabindex="1" autocomplete="title" v-model="form.title" placeholder="Batch Name" />
                        <InputError :message="form.errors.title" />
                    </div>
                    <div class="flex flex-col space-y-1.5">
                        <RichTextEditor placeholder="Add Release Notes" label="Release Notes" v-model="form.release_notes" />
                        <InputError :message="form.errors.release_notes" />
                    </div>
                    <div class="flex flex-col space-y-1.5">
                        <Label for="internal_notes">Internal Notes</Label>
                        <Textarea class="min-h-48" id="internal_notes" v-model="form.internal_notes" placeholder="Add Internal Notes" />
                        <InputError :message="form.errors.internal_notes" />
                    </div>

                    <!-- Batchables Table -->
                    <div class="container mx-auto mt-6">
                        <div class="flex items-center justify-between py-4">
                            <Input class="max-w-sm" placeholder="Filter Batch Entries"
                                   :model-value="table.getColumn('title')?.getFilterValue() as string"
                                   @update:model-value="table.getColumn('title')?.setFilterValue($event)" />
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

                </div>
                <div class="flex justify-end mt-6">
                    <Button type="submit" @click="submit" class="my-auto" tabindex="5" :disabled="form.processing">
                        <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                        <span v-if="props.batch">Update Batch</span>
                        <span v-else>Create Batch</span>
                    </Button>
                    <Button @click="back()" class="bg-destructive my-auto ml-2">
                        Cancel
                    </Button>
                </div>
            </form>
        </CardContent>
    </Card>
</template>
