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
import InputError from "@/components/InputError.vue";
import {LoaderCircle, ChevronDown} from "lucide-vue-next";
import { Textarea } from '@/components/ui/textarea'
import { Switch } from '@/components/ui/switch'
import { Head, useForm } from '@inertiajs/vue3';
import { h, ref } from 'vue';
import type { ColumnDef } from '@tanstack/vue-table';
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
import AdminInternalNotes from "@/components/AdminInternalNotes.vue";
import TipTapEditor from "@/components/tiptap/TipTapEditor.vue";
import { Separator } from '@/components/ui/separator';
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';

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
    is_public: false,
});

onMounted(() => {
    form.title = props.batch?.title ?? null;
    form.release_notes = props.batch?.release_notes ?? '';
    form.internal_notes = props.batch?.internal_notes ?? '';
    form.is_public = props.batch?.is_public ?? false;
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
        enableGlobalFilter: false,
        header: () => h('div', {class: 'text-center'}, 'Approved'),
        cell: ({ row }) => {
            const batchable = row.original;

            return h('div', {}, batchable.approved_at ? h(Check, {class: 'text-green-500 mx-auto'}) : h(Ban, {class: 'text-red-500 mx-auto'}))
        },
    },{
        id: 'actions',
        enableHiding: false,
        enableGlobalFilter: false,
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
                approvalRoute: batchable.is_approvable ? route('admin.approvals.update', batchable.approval_id) : null,
                publishRoute: null,
            }))
        },
    },
];

const globalFilter = ref('')

const table = useVueTable({
    get data() { return props.batchables },
    get columns() { return columns },
    getCoreRowModel: getCoreRowModel(),
    getPaginationRowModel: getPaginationRowModel(),
    getFilteredRowModel: getFilteredRowModel(),
    globalFilterFn: 'includesString',
    state: {
        get globalFilter() { return globalFilter.value },
    }
});

</script>

<template>
    <Head title="Batch Information" />

    <Card>
        <CardHeader>
            <CardTitle>{{ props.batch ? 'Edit' : 'New' }} Batch</CardTitle>
            <CardDescription>
                <span class="text-destructive" v-if="!props.batch">
                    You are creating a new Batch item. To update an existing one, find it in the list and click Edit.
                </span>
                <span v-else>Editing: {{ props.batch.title }}</span>
            </CardDescription>
        </CardHeader>
        <CardContent>
            <form @submit.prevent class="space-y-6">
                <div class="space-y-4">
                    <div class="space-y-2">
                        <Label for="title">Title</Label>
                        <Input id="title" type="text" required autofocus autocomplete="title" v-model="form.title" placeholder="Batch Name" />
                        <InputError :message="form.errors.title" />
                    </div>
                    <div class="flex items-center justify-between">
                        <Label for="is_public">Public Errata Batch</Label>
                        <Switch id="is_public" v-model="form.is_public" />
                    </div>
                </div>

                <Separator />

                <div class="space-y-4">
                    <TipTapEditor v-model="form.release_notes" label="Release Notes" />
                    <InputError :message="form.errors.release_notes" />
                </div>

                <template v-if="props.batchables">
                    <Separator />

                    <div class="container mx-auto">
                        <div class="flex items-center justify-between py-4">
                            <Input class="max-w-sm" placeholder="Search..."
                                   v-model="globalFilter" />
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

                <Separator />

                <Collapsible>
                    <CollapsibleTrigger class="flex items-center gap-2 text-sm font-medium text-muted-foreground hover:text-foreground transition-colors">
                        <ChevronDown class="h-4 w-4" />
                        Notes
                    </CollapsibleTrigger>
                    <CollapsibleContent class="space-y-4 pt-4">
                        <div class="space-y-2">
                            <Label for="internal_notes">Internal Notes</Label>
                            <Textarea class="min-h-32" id="internal_notes" v-model="form.internal_notes" placeholder="Add internal notes..." />
                            <InputError :message="form.errors.internal_notes" />
                        </div>
                    </CollapsibleContent>
                </Collapsible>
            </form>
        </CardContent>
        <CardFooter class="flex justify-between border-t pt-6">
            <Button variant="outline" @click="back()">Cancel</Button>
            <Button type="submit" @click="submit" :disabled="form.processing">
                <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                <span v-if="props.batch">Update Batch</span>
                <span v-else>Create Batch</span>
            </Button>
        </CardFooter>
    </Card>
</template>
