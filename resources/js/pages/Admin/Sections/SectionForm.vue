<script setup lang='ts'>
import { ref, onMounted } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {Select, SelectContent, SelectItem, SelectTrigger, SelectValue} from "@/components/ui/select";
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import InputError from "@/components/InputError.vue";
import {LoaderCircle, CircleX, CheckCheckIcon, ChevronsUpDown, Search, Check, Component, Eye} from "lucide-vue-next";
import { Textarea } from '@/components/ui/textarea'
import { Checkbox } from "@/components/ui/checkbox";
import RichTextEditor from "@/components/RichTextEditor.vue";
import {hasPermission} from "@/composables/hasPermission";
import {
    Dialog, DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter, DialogHeader,
    DialogTitle,
    DialogTrigger
} from "@/components/ui/dialog";
import {Tooltip, TooltipContent, TooltipProvider, TooltipTrigger} from "@/components/ui/tooltip";
import {cn} from "@/lib/utils";
import {
    Combobox,
    ComboboxAnchor,
    ComboboxEmpty,
    ComboboxGroup, ComboboxInput,
    ComboboxItem,
    ComboboxList, ComboboxTrigger
} from "@/components/ui/combobox";
import {
    Drawer, DrawerClose, DrawerContent,
    DrawerDescription,
    DrawerFooter,
    DrawerHeader,
    DrawerTitle,
    DrawerTrigger
} from "@/components/ui/drawer";
import {Switch} from "@/components/ui/switch";
import axios from "axios";
import sectionView from "@/pages/Rules/SectionView.vue";
import DragDropEditor from "@/components/DragDropEditor.vue";
import DraggableContent from "@/components/DraggableContent.vue";

const props = defineProps({
    section: {
        type: [Object, Array],
        required: false,
        default() {
            return null;
        }
    },
    batches: {
        type: [Object, Array],
        required: false,
        default() {
            return {};
        }
    },
    indices: {
        type: [Object, Array],
        required: false,
        default() {
            return {};
        }
    },
    pages: {
        type: [Object, Array],
        required: false,
        default() {
            return {};
        }
    },
    sections: {
        type: [Object, Array],
        required: false,
        default() {
            return {};
        }
    }
});

const form = useForm({
    title: '',
    left_column: '',
    right_column: '',
    internal_notes: '',
    change_notes: '',
    batch_id: null,
    publish_directly: false,
    approve_directly: false,
});

const back = () => {
    history.back();
};

onMounted(() => {
    form.title = props.section?.title ?? null;
    form.left_column = replaceBrWithNewline(props.section?.left_column ?? '');
    form.right_column = replaceBrWithNewline(props.section?.right_column ?? '');
    form.internal_notes = props.section?.internal_notes ?? '';
    form.change_notes = props.section?.published_at ? '' : props.section?.approval?.change_notes ?? '';
    form.batch_id = props.section?.published_at ? null : props.section?.batch_id ?? null;
});

const submitSection = () => {
    if (props.section) {
        form.post(route('admin.sections.update', {section: props.section.slug}));
    } else {
        form.post(route('admin.sections.store'));
    }
};

const viewData = ref(null);

const fetchViewData = () => {
    axios.post(route('admin.sections.preview'), { title: form.title, left_column: form.left_column, right_column: form.right_column }).then((response) => {
        viewData.value = response.data;
        viewData.value = JSON.parse(JSON.stringify(response.data));
    });
};

onMounted(() => {
    fetchViewData();
});

const leftColumnUpdate = (newOrder) => {
    form.left_column = newOrder;
};

const rightColumnUpdate = (newOrder) => {
    form.right_column = newOrder;
};

const replaceBrWithNewline = (text) => {
    return text.replace(/<br\s*\/?>/gi, '\n');
};


</script>

<template>
    <Head title="Section Information" />

    <Card>
        <CardHeader>
            <CardTitle>Section Form</CardTitle>
            <CardDescription>
                Create and Edit Section Information
                <span class="text-destructive" v-if="!props.section"><br />Make sure you want an entirely NEW Section. <br />
                    If you just want to update or change an existing Section, you need to edit it.</span>
            </CardDescription>
        </CardHeader>
        <CardContent>
            <form @submit.prevent>
                <div class="grid items-center w-full gap-4">
                    <div class="flex flex-col space-y-1.5">
                        <Label for="title">Section</Label>
                        <Input id="title" type="text" required autofocus :tabindex="1" autocomplete="title" v-model="form.title" placeholder="Section Title" />
                        <InputError :message="form.errors.title" />
                    </div>
                    <div class="flex flex-col space-y-1.5">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-1">
                            <div>
                                <div class="flex flex-col space-y-1.5">
                                    <DraggableContent
                                        v-if="viewData"
                                        @update:content-order="leftColumnUpdate"
                                        :content="viewData.left_column ?? []" />
                                    <InputError :message="form.errors.left_column" />
                                </div>
                            </div>
                            <div>
                                <div class="flex flex-col space-y-1.5">
                                    <DraggableContent
                                        v-if="viewData"
                                        @update:content-order="rightColumnUpdate"
                                        :content="viewData.right_column ?? []" />
                                    <InputError :message="form.errors.right_column" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-col space-y-1.5" v-if="(props.section && props.section?.published_at) || props.section?.approval?.change_notes">
                        <RichTextEditor
                            placeholder="Add Change Notes"
                            label="Change Notes"
                            v-model="form.change_notes"
                            :indices="props.indices"
                            :sections="props.sections"
                            :pages="props.pages"
                        />
                        <InputError :message="form.errors.change_notes" />
                    </div>
                    <div class="flex flex-col space-y-1.5">
                        <Label for="internal_notes">Internal Notes</Label>
                        <Textarea class="min-h-48" id="internal_notes" v-model="form.internal_notes" placeholder="Add Internal Notes" />
                        <InputError :message="form.errors.internal_notes" />
                    </div>
                </div>
            </form>
        </CardContent>
        <CardFooter>
            <div class="flex ml-auto my-auto">
                <Drawer v-if="hasPermission('view_section')">
                    <DrawerTrigger as-child>
                        <Button class="bg-purple-500 mx-2" @click="fetchViewData()">
                            <Eye class="h-4 w-4" /> Preview
                        </Button>
                    </DrawerTrigger>
                    <DrawerContent>
                        <div class="mx-auto w-full mt-2 container overflow-y-auto">
                            <DrawerDescription>
                                <component
                                    v-if="viewData"
                                    :is="sectionView"
                                    v-bind="viewData"
                                />
                            </DrawerDescription>
                            <DrawerFooter>
                                <DrawerClose as-child>
                                    <Button type="button" class="mx-auto w-25" variant="destructive">
                                        Close
                                    </Button>
                                </DrawerClose>
                            </DrawerFooter>
                        </div>
                    </DrawerContent>
                </Drawer>
                <Drawer>
                    <DrawerTrigger>
                        <Button class="bg-green-500">{{ props.section ? 'Update' : 'Create' }} Section</Button>
                    </DrawerTrigger>
                    <DrawerContent class="max-w-lg mx-auto">
                        <DrawerHeader>
                            <DrawerTitle>{{ props.section ? 'Update' : 'Create' }} Section</DrawerTitle>
                            <DrawerDescription>
                                <div class="mx-auto max-w-lg mt-2 container overflow-y-auto">
                                    <div class="flex mb-4">
                                        <Select id="type" v-model="form.batch_id">
                                            <SelectTrigger class="w-full">
                                                <SelectValue placeholder="Select Batch" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem v-for="batch in props.batches" :value="batch.id" :key="batch.id">
                                                    {{ batch.title }}
                                                </SelectItem>
                                            </SelectContent>
                                        </Select>
                                        <CircleX class="text-destructive my-auto ml-2" v-if="form.batch_id" @click="form.batch_id = null" />
                                    </div>
                                    <div class="flex items-center mb-4 space-x-2" v-if="hasPermission('approve_section')">
                                        <Switch id="approve-directly" v-model="form.approve_directly" />
                                        <Label for="approve-directly">Approve Directly</Label>
                                    </div>
                                    <div class="flex items-center mb-4 space-x-2" v-if="hasPermission('publish_section')">
                                        <Switch id="publish-directly" v-model="form.publish_directly" />
                                        <Label for="publish-directly">Publish Directly</Label>
                                    </div>
                                </div>
                            </DrawerDescription>
                        </DrawerHeader>
                        <DrawerFooter class="container grid grid-cols-2">
                            <Button @click="submitSection">Submit</Button>
                            <DrawerClose>
                                <Button variant="destructive" class="w-full">
                                    Cancel
                                </Button>
                            </DrawerClose>
                        </DrawerFooter>
                    </DrawerContent>
                </Drawer>
                <div class="ml-2">
                    <Button @click="back()" class="bg-destructive my-auto">
                        Cancel
                    </Button>
                </div>
            </div>
        </CardFooter>
    </Card>
</template>
