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
    }
});

const form = useForm({
    title: '',
    content: '',
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
    form.content = props.section?.content ?? '';
    form.internal_notes = props.section?.internal_notes ?? '';
    form.change_notes = props.section?.published_at ? '' : props.section?.approval?.change_notes ?? '';
    form.batch_id = props.section?.published_at ? null : props.section?.batch_id ?? null;
});

const addToBatch = () => {
    if (form.batch_id) {
        submitSection();
    }

    return;
};

const updateUnpublished = () => {
    form.publish_directly = false;
    submitSection();
};

const publishDirectly = () => {
    form.batch_id = null;
    form.publish_directly = true;
    form.approve_directly = true;
    submitSection();
};

const approveDirectly = () => {
    form.batch_id = null;
    form.publish_directly = false;
    form.approve_directly = true;
    submitSection();
}

const submitForApprovalDirectly = () => {
    form.batch_id = null;
    form.publish_directly = false;
    form.approve_directly = false;
    submitSection();
}

const submitSection = () => {
    if (props.section) {
        form.post(route('admin.sections.update', {section: props.section.slug}));
    } else {
        form.post(route('admin.sections.store'));
    }
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
                        <RichTextEditor placeholder="Add Section Content" label="Content" v-model="form.content" />
                        <InputError :message="form.errors.content" />
                    </div>
                    <div class="flex flex-col space-y-1.5" v-if="(props.section && props.section?.published_at) || props.section?.approval?.change_notes">
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
                <div v-if="props.section && props.section?.batch_id && !props.section?.published_at">
                    <Button class="bg-green-500" @click="updateUnpublished()">
                        Update Section
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
                <div v-if="hasPermission('publish_section')" class="ml-2">
                    <Dialog>
                        <DialogTrigger>
                            <TooltipProvider>
                                <Tooltip>
                                    <TooltipTrigger>
                                        <Button class="bg-blue-500 my-auto">
                                            Approve & Publish<span v-if="props.section"> Update</span> Directly
                                        </Button>
                                    </TooltipTrigger>
                                    <TooltipContent>
                                        <p>Approve & Publish<span v-if="props.section"> Update</span> Directly</p>
                                    </TooltipContent>
                                </Tooltip>
                            </TooltipProvider>
                        </DialogTrigger>
                        <DialogContent>
                            <DialogHeader>
                                <DialogTitle>
                                    Approve & Publish<span v-if="props.section"> Update</span> Directly
                                </DialogTitle>
                                <DialogDescription>
                                    This Section will go directly live. Make sure this is what you want before submitting.
                                </DialogDescription>
                            </DialogHeader>

                            <DialogFooter class="sm:justify-start">
                                <DialogClose as-child>
                                    <Button class="bg-blue-500 ml-auto" @click="publishDirectly()">
                                        <CheckCheckIcon class="h-4 w-4" />Approve & Publish<span v-if="props.section"> Update</span> Directly
                                    </Button>
                                </DialogClose>
                            </DialogFooter>
                        </DialogContent>
                    </Dialog>
                </div>
                <div v-if="hasPermission('approve_section')" class="ml-2">
                    <Dialog>
                        <DialogTrigger>
                            <TooltipProvider>
                                <Tooltip>
                                    <TooltipTrigger>
                                        <Button class="bg-blue-500">
                                            Approve<span v-if="props.section"> Update</span> Directly
                                        </Button>
                                    </TooltipTrigger>
                                    <TooltipContent>
                                        <p>Approve<span v-if="props.section"> Update</span> Directly</p>
                                    </TooltipContent>
                                </Tooltip>
                            </TooltipProvider>
                        </DialogTrigger>
                        <DialogContent>
                            <DialogHeader>
                                <DialogTitle>
                                    Approve<span v-if="props.section"> Update</span> Directly
                                </DialogTitle>
                                <DialogDescription>
                                    This will not be added to an existing batch and be submitted for direct publishing.
                                </DialogDescription>
                            </DialogHeader>

                            <DialogFooter class="sm:justify-start">
                                <DialogClose as-child>
                                    <Button class="bg-blue-500 ml-auto" @click="approveDirectly()">
                                        <CheckCheckIcon class="h-4 w-4" />Approve<span v-if="props.section"> Update</span> Directly
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
                                            Submit<span v-if="props.section"> Update</span> For Approval Directly
                                        </Button>
                                    </TooltipTrigger>
                                    <TooltipContent>
                                        <p>Submit<span v-if="props.section"> Update</span> For Approval Directly</p>
                                    </TooltipContent>
                                </Tooltip>
                            </TooltipProvider>
                        </DialogTrigger>
                        <DialogContent>
                            <DialogHeader>
                                <DialogTitle>
                                    Submit<span v-if="props.section"> Update</span> For Approval Directly
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
                                        <CheckCheckIcon class="h-4 w-4" />Submit<span v-if="props.section"> Update</span> For Approval Directly
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
