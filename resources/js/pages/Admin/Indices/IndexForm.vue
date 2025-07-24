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
import {
    Drawer,
    DrawerClose,
    DrawerContent,
    DrawerDescription,
    DrawerFooter,
    DrawerHeader,
    DrawerTitle,
    DrawerTrigger,
} from '@/components/ui/drawer';
import { Switch } from '@/components/ui/switch';
import {Select, SelectContent, SelectItem, SelectTrigger, SelectValue} from "@/components/ui/select";
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import InputError from "@/components/InputError.vue";
import {LoaderCircle, CircleX, CheckCheckIcon, ChevronsUpDown, Search, Check} from "lucide-vue-next";
import { Textarea } from '@/components/ui/textarea'
import RichTextEditor from "@/components/RichTextEditor.vue";
import {hasPermission} from "@/composables/hasPermission";

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
                        <RichTextEditor
                            placeholder="Add Index Content"
                            label="Content"
                            v-model="form.content"
                            :indices="props.indices"
                            :sections="props.sections"
                            :pages="props.pages"
                        />
                        <InputError :message="form.errors.content" />
                    </div>
                    <div class="flex flex-col space-y-1.5" v-if="(props.index && props.index?.published_at) || props.index?.approval?.change_notes">
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
                <Drawer>
                    <DrawerTrigger>
                        <Button class="bg-green-500">{{ props.index ? 'Update' : 'Create' }} Index</Button>
                    </DrawerTrigger>
                    <DrawerContent class="max-w-lg mx-auto">
                        <DrawerHeader>
                            <DrawerTitle>{{ props.index ? 'Update' : 'Create' }} Index</DrawerTitle>
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
                                    <div class="flex items-center mb-4 space-x-2" v-if="hasPermission('approve_index')">
                                        <Switch id="approve-directly" v-model="form.approve_directly" />
                                        <Label for="approve-directly">Approve Directly</Label>
                                    </div>
                                    <div class="flex items-center mb-4 space-x-2" v-if="hasPermission('publish_index')">
                                        <Switch id="publish-directly" v-model="form.publish_directly" />
                                        <Label for="publish-directly">Publish Directly</Label>
                                    </div>
                                </div>
                            </DrawerDescription>
                        </DrawerHeader>
                        <DrawerFooter class="container grid grid-cols-2">
                            <Button @click="submitIndex">Submit</Button>
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
