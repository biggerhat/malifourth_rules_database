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
import {LoaderCircle, CircleX, CheckCheckIcon, ChevronsUpDown, Search, Check} from "lucide-vue-next";
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

const props = defineProps({
    page: {
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
    }
});

const form = useForm({
    title: '',
    left_column: '',
    right_column: '',
    internal_notes: '',
    page_number: null,
    book_page_numbers: null,
    change_notes: '',
    batch_id: null,
    publish_directly: false,
    approve_directly: false,
});

const back = () => {
    history.back();
};

onMounted(() => {
    form.title = props.page?.title ?? null;
    form.left_column = props.page?.left_column ?? '';
    form.right_column = props.page?.right_column ?? '';
    form.page_number = props.page?.page_number ?? null;
    form.book_page_numbers = props.page?.book_page_numbers ?? null;
    form.internal_notes = props.page?.internal_notes ?? '';
    form.change_notes = props.page?.published_at ? '' : props.page?.approval?.change_notes ?? '';
    form.batch_id = props.page?.published_at ? null : props.page?.batch_id ?? null;
});

const addToBatch = () => {
    if (form.batch_id) {
        submitPage();
    }

    return;
};

const updateUnpublished = () => {
    form.publish_directly = false;
    submitPage();
};

const publishDirectly = () => {
    form.batch_id = null;
    form.publish_directly = true;
    form.approve_directly = true;
    submitPage();
};

const approveDirectly = () => {
    form.batch_id = null;
    form.publish_directly = false;
    form.approve_directly = true;
    submitPage();
}

const submitForApprovalDirectly = () => {
    form.batch_id = null;
    form.publish_directly = false;
    form.approve_directly = false;
    submitPage();
}

const submitPage = () => {
    if (props.page) {
        form.post(route('admin.pages.update', {page: props.page.slug}));
    } else {
        form.post(route('admin.pages.store'));
    }
};

</script>

<template>
    <Head title="Page Information" />

    <Card>
        <CardHeader>
            <CardTitle>Page Form</CardTitle>
            <CardDescription>
                Create and Edit Page Information
                <span class="text-destructive" v-if="!props.page"><br />Make sure you want an entirely NEW Page. <br />
                    If you just want to update or change an existing Page, you need to edit it.</span>
            </CardDescription>
        </CardHeader>
        <CardContent>
            <form @submit.prevent>
                <div class="grid items-center w-full gap-4">
                    <div class="flex flex-col space-y-1.5">
                        <Label for="title">Page</Label>
                        <Input id="title" type="text" required autofocus :tabindex="1" autocomplete="title" v-model="form.title" placeholder="Page Title" />
                        <InputError :message="form.errors.title" />
                    </div>
                    <div class="flex flex-col space-y-1.5">
                        <Label for="page_number">
                            Page Number <br />
                            <span class="text-red-500">If No Page Number Is Given, This Page Will Be Added To The End</span>
                            <Input id="page_number" type="number" min="1" required v-model="form.page_number" />
                            <InputError :message="form.errors.page_number" />
                        </Label>
                    </div>
                    <div class="flex flex-col space-y-1.5">
                        <RichTextEditor placeholder="Add Left Column Content" label="Left Column Content" v-model="form.left_column" />
                        <InputError :message="form.errors.left_column" />
                    </div>
                    <div class="flex flex-col space-y-1.5">
                        <RichTextEditor placeholder="Add Right Column" label="Right Column Content" v-model="form.right_column" />
                        <InputError :message="form.errors.right_column" />
                    </div>
                    <div class="flex flex-col space-y-1.5" v-if="(props.page && props.page?.published_at) || props.page?.approval?.change_notes">
                        <RichTextEditor placeholder="Add Change Notes" label="Change Notes" v-model="form.change_notes" />
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
                <div v-if="props.page && props.page?.batch_id && !props.page?.published_at">
                    <Button class="bg-green-500" @click="updateUnpublished()">
                        Update Page
                    </Button>
                </div>
                <div v-else>
                    <Dialog>
                        <DialogTrigger>
                            <TooltipProvider>
                                <Tooltip>
                                    <TooltipTrigger>
                                        <Button class="bg-green-500">
                                            Add To Existing Batch
                                        </Button>
                                    </TooltipTrigger>
                                    <TooltipContent>
                                        <p>Add To Existing Batch</p>
                                    </TooltipContent>
                                </Tooltip>
                            </TooltipProvider>
                        </DialogTrigger>
                        <DialogContent>
                            <DialogHeader>
                                <DialogTitle>
                                    Add To Existing Batch
                                </DialogTitle>
                                <DialogDescription>
                                    <div class="flex">
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
                                </DialogDescription>
                            </DialogHeader>

                            <DialogFooter class="sm:justify-start">
                                <DialogClose as-child>
                                    <Button class="bg-green-500 ml-auto" :disabled="!form.batch_id" @click="addToBatch()">
                                        <CheckCheckIcon class="h-4 w-4" />Add To Batch
                                    </Button>
                                </DialogClose>
                            </DialogFooter>
                        </DialogContent>
                    </Dialog>
                </div>
                <div v-if="hasPermission('publish_page')" class="ml-2">
                    <Dialog>
                        <DialogTrigger>
                            <TooltipProvider>
                                <Tooltip>
                                    <TooltipTrigger>
                                        <Button class="bg-blue-500 my-auto">
                                            Approve & Publish<span v-if="props.page"> Update</span> Directly
                                        </Button>
                                    </TooltipTrigger>
                                    <TooltipContent>
                                        <p>Approve & Publish<span v-if="props.page"> Update</span> Directly</p>
                                    </TooltipContent>
                                </Tooltip>
                            </TooltipProvider>
                        </DialogTrigger>
                        <DialogContent>
                            <DialogHeader>
                                <DialogTitle>
                                    Approve & Publish<span v-if="props.page"> Update</span> Directly
                                </DialogTitle>
                                <DialogDescription>
                                    This Page will go directly live. Make sure this is what you want before submitting.
                                </DialogDescription>
                            </DialogHeader>

                            <DialogFooter class="sm:justify-start">
                                <DialogClose as-child>
                                    <Button class="bg-blue-500 ml-auto" @click="publishDirectly()">
                                        <CheckCheckIcon class="h-4 w-4" />Approve & Publish<span v-if="props.page"> Update</span> Directly
                                    </Button>
                                </DialogClose>
                            </DialogFooter>
                        </DialogContent>
                    </Dialog>
                </div>
                <div v-if="hasPermission('approve_page')" class="ml-2">
                    <Dialog>
                        <DialogTrigger>
                            <TooltipProvider>
                                <Tooltip>
                                    <TooltipTrigger>
                                        <Button class="bg-blue-500">
                                            Approve<span v-if="props.page"> Update</span> Directly
                                        </Button>
                                    </TooltipTrigger>
                                    <TooltipContent>
                                        <p>Approve<span v-if="props.page"> Update</span> Directly</p>
                                    </TooltipContent>
                                </Tooltip>
                            </TooltipProvider>
                        </DialogTrigger>
                        <DialogContent>
                            <DialogHeader>
                                <DialogTitle>
                                    Approve<span v-if="props.page"> Update</span> Directly
                                </DialogTitle>
                                <DialogDescription>
                                    This will not be added to an existing batch and be submitted for direct publishing.
                                </DialogDescription>
                            </DialogHeader>

                            <DialogFooter class="sm:justify-start">
                                <DialogClose as-child>
                                    <Button class="bg-blue-500 ml-auto" @click="approveDirectly()">
                                        <CheckCheckIcon class="h-4 w-4" />Approve<span v-if="props.page"> Update</span> Directly
                                    </Button>
                                </DialogClose>
                            </DialogFooter>
                        </DialogContent>
                    </Dialog>
                </div>
                <div v-else class="ml-2">
                    <Dialog>
                        <DialogTrigger>
                            <TooltipProvider>
                                <Tooltip>
                                    <TooltipTrigger>
                                        <Button class="bg-blue-500">
                                            Submit<span v-if="props.page"> Update</span> For Approval Directly
                                        </Button>
                                    </TooltipTrigger>
                                    <TooltipContent>
                                        <p>Submit<span v-if="props.page"> Update</span> For Approval Directly</p>
                                    </TooltipContent>
                                </Tooltip>
                            </TooltipProvider>
                        </DialogTrigger>
                        <DialogContent>
                            <DialogHeader>
                                <DialogTitle>
                                    Submit<span v-if="props.page"> Update</span> For Approval Directly
                                </DialogTitle>
                                <DialogDescription>
                                    <div class="flex">
                                        This will not be added to a batch and be submitted for Approval to be Published directly.
                                    </div>
                                </DialogDescription>
                            </DialogHeader>

                            <DialogFooter class="sm:justify-start">
                                <DialogClose as-child>
                                    <Button class="bg-blue-500 ml-auto" @click="submitForApprovalDirectly()">
                                        <CheckCheckIcon class="h-4 w-4" />Submit<span v-if="props.page"> Update</span> For Approval Directly
                                    </Button>
                                </DialogClose>
                            </DialogFooter>
                        </DialogContent>
                    </Dialog>
                </div>
                <div class="ml-2">
                    <Button @click="back()" class="bg-destructive my-auto">
                        Cancel
                    </Button>
                </div>
            </div>
        </CardFooter>
    </Card>
</template>
