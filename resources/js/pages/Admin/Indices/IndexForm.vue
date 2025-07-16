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
    index: {
        type: [Object, Array],
        required: false,
        default() {
            return null;
        }
    },
    index_types: {
        type: [Object, Array],
        required: false,
        default() {
            return {};
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
    type: null,
    image: null,
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
    form.title = props.index?.title ?? null;
    form.type = props.index?.type ?? null;
    form.content = props.index?.content ?? '';
    form.internal_notes = props.index?.internal_notes ?? '';
    form.change_notes = props.index?.published_at ? '' : props.index?.approval?.change_notes ?? '';
    form.batch_id = props.index?.published_at ? null : props.index?.batch_id ?? null;
});

const addToBatch = () => {
    if (form.batch_id) {
        submitIndex();
    }

    return;
};

const updateUnpublished = () => {
    form.publish_directly = false;
    submitIndex();
};

const publishDirectly = () => {
    form.batch_id = null;
    form.publish_directly = true;
    form.approve_directly = true;
    submitIndex();
};

const approveDirectly = () => {
    form.batch_id = null;
    form.publish_directly = false;
    form.approve_directly = true;
    submitIndex();
}

const submitForApprovalDirectly = () => {
    form.batch_id = null;
    form.publish_directly = false;
    form.approve_directly = false;
    submitIndex();
}

const submitIndex = () => {
    if (props.index) {
        form.post(route('admin.indices.update', {index: props.index.slug}));
    } else {
        form.post(route('admin.indices.store'));
    }
};

</script>

<template>
    <Head title="Index Information" />

    <Card>
        <CardHeader>
            <CardTitle>Index Form</CardTitle>
            <CardDescription>
                Create and Edit Index Information
                <span class="text-destructive" v-if="!props.index"><br />Make sure you want an entirely NEW Index. <br />
                    If you just want to update or change an existing Index, you need to edit it.</span>
            </CardDescription>
        </CardHeader>
        <CardContent>
            <form @submit.prevent>
                <div class="grid items-center w-full gap-4">
                    <div class="flex flex-col space-y-1.5">
                        <Label for="title">Index</Label>
                        <Input id="title" type="text" required autofocus :tabindex="1" autocomplete="title" v-model="form.title" placeholder="Index Title" />
                        <InputError :message="form.errors.title" />
                    </div>
                    <div class="flex flex-col space-y-1.5">
                        <Label for="type">Index Type</Label>
                        <div class="flex">
                            <Select id="type" v-model="form.type" :disabled="props.index">
                                <SelectTrigger class="w-full">
                                    <SelectValue placeholder="Select Index Type" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="type in props.index_types" :value="type.value" :key="type.value">
                                        {{ type.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <CircleX class="text-destructive my-auto ml-2" v-if="form.type && !props.index" @click="form.type = null" />
                        </div>
                        <InputError :message="form.errors.type" />
                    </div>
                    <div class="flex flex-col space-y-1.5" v-if="props.index?.image">
                        <Label for="current_image">Current Image</Label>
                        <img id="current_image" :src="props.index?.image" :alt="props.index?.title" class="w-75" />
                    </div>
                    <div class="flex flex-col space-y-1.5" v-if="form.type === 'image'">
                        <Label for="image" v-if="props.index">New Image</Label>
                        <Label for="image" v-else>Image</Label>
                        <Input id="image" type="file" accept=".jpeg, .jpg, .png" @input="form.image = $event.target.files[0]" />
                    </div>
                    <div class="flex flex-col space-y-1.5" v-if="form.type === 'text'">
                        <RichTextEditor placeholder="Add Index Content" label="Content" v-model="form.content" />
                        <InputError :message="form.errors.content" />
                    </div>
                    <div class="flex flex-col space-y-1.5" v-if="(props.index && props.index?.published_at) || props.index?.approval?.change_notes">
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
                <div v-if="props.index && props.index?.batch_id && !props.index?.published_at">
                    <Button class="bg-green-500" @click="updateUnpublished()">
                        Update Index
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
                <div v-if="hasPermission('publish_index')" class="ml-2">
                    <Dialog>
                        <DialogTrigger>
                            <TooltipProvider>
                                <Tooltip>
                                    <TooltipTrigger>
                                        <Button class="bg-blue-500 my-auto">
                                            Approve & Publish<span v-if="props.index"> Update</span> Directly
                                        </Button>
                                    </TooltipTrigger>
                                    <TooltipContent>
                                        <p>Approve & Publish<span v-if="props.index"> Update</span> Directly</p>
                                    </TooltipContent>
                                </Tooltip>
                            </TooltipProvider>
                        </DialogTrigger>
                        <DialogContent>
                            <DialogHeader>
                                <DialogTitle>
                                    Approve & Publish<span v-if="props.index"> Update</span> Directly
                                </DialogTitle>
                                <DialogDescription>
                                    This Index will go directly live. Make sure this is what you want before submitting.
                                </DialogDescription>
                            </DialogHeader>

                            <DialogFooter class="sm:justify-start">
                                <DialogClose as-child>
                                    <Button class="bg-blue-500 ml-auto" @click="publishDirectly()">
                                        <CheckCheckIcon class="h-4 w-4" />Approve & Publish<span v-if="props.index"> Update</span> Directly
                                    </Button>
                                </DialogClose>
                            </DialogFooter>
                        </DialogContent>
                    </Dialog>
                </div>
                <div v-if="hasPermission('approve_index')" class="ml-2">
                    <Dialog>
                        <DialogTrigger>
                            <TooltipProvider>
                                <Tooltip>
                                    <TooltipTrigger>
                                        <Button class="bg-blue-500">
                                            Approve<span v-if="props.index"> Update</span> Directly
                                        </Button>
                                    </TooltipTrigger>
                                    <TooltipContent>
                                        <p>Approve<span v-if="props.index"> Update</span> Directly</p>
                                    </TooltipContent>
                                </Tooltip>
                            </TooltipProvider>
                        </DialogTrigger>
                        <DialogContent>
                            <DialogHeader>
                                <DialogTitle>
                                    Approve<span v-if="props.index"> Update</span> Directly
                                </DialogTitle>
                                <DialogDescription>
                                    This will not be added to an existing batch and be submitted for direct publishing.
                                </DialogDescription>
                            </DialogHeader>

                            <DialogFooter class="sm:justify-start">
                                <DialogClose as-child>
                                    <Button class="bg-blue-500 ml-auto" @click="approveDirectly()">
                                        <CheckCheckIcon class="h-4 w-4" />Approve<span v-if="props.index"> Update</span> Directly
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
                                            Submit<span v-if="props.index"> Update</span> For Approval Directly
                                        </Button>
                                    </TooltipTrigger>
                                    <TooltipContent>
                                        <p>Submit<span v-if="props.index"> Update</span> For Approval Directly</p>
                                    </TooltipContent>
                                </Tooltip>
                            </TooltipProvider>
                        </DialogTrigger>
                        <DialogContent>
                            <DialogHeader>
                                <DialogTitle>
                                    Submit<span v-if="props.index"> Update</span> For Approval Directly
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
                                        <CheckCheckIcon class="h-4 w-4" />Submit<span v-if="props.index"> Update</span> For Approval Directly
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
