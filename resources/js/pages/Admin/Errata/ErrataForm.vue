<script setup lang='ts'>
import { ref, onMounted } from 'vue';
import { useForm } from '@inertiajs/vue3';
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
import {CircleX} from "lucide-vue-next";
import { Textarea } from '@/components/ui/textarea'
import {hasPermission} from "@/composables/hasPermission";
import axios from "axios";
import DraggableContent from "@/components/DraggableContent.vue";
import { Tabs, TabsList, TabsTrigger, TabsContent } from '@/components/ui/tabs'

const props = defineProps({
    errata: {
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
    },
    faqs: {
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
    form.title = props.errata?.title ?? '';
    form.content = props.errata?.content ?? '';
    form.internal_notes = props.errata?.internal_notes ?? '';
    form.change_notes = props.errata?.published_at ? '' : props.errata?.approval?.change_notes ?? '';
    form.batch_id = props.errata?.published_at ? null : props.errata?.batch_id ?? null;
    fetchViewData();
});

const submitErrata = () => {
    if (props.errata) {
        form.post(route('admin.errata.update', {errata: props.errata.slug}));
    } else {
        form.post(route('admin.errata.store'));
    }
};

const viewData = ref(null);

const fetchViewData = () => {
    axios.post(route('admin.errata.preview'), {
        title: form.title,
        content: form.content,
        change_notes: form.change_notes,
    }).then((response) => {
        viewData.value = JSON.parse(JSON.stringify(response.data));
    });
};

const contentUpdate = (newOrder) => { form.content = newOrder; };
const contentNewContent = (content) => { form.content = content; fetchViewData(); };
const changeNotesUpdate = (newOrder) => { form.change_notes = newOrder; };
const changeNotesNewContent = (content) => { form.change_notes = content; fetchViewData(); };
</script>

<template>
    <Head title="Errata Information" />

    <Card>
        <CardHeader>
            <CardTitle>Errata Form</CardTitle>
            <CardDescription>
                Create and Edit Errata Information
                <span class="text-destructive" v-if="!props.errata"><br />Make sure you want an entirely NEW Errata item. <br />
                    If you just want to update or change an existing Errata item, you need to edit it.</span>
            </CardDescription>
        </CardHeader>
        <CardContent>
            <form @submit.prevent>
                <Tabs default-value="details">
                    <TabsList>
                        <TabsTrigger value="details">Details</TabsTrigger>
                        <TabsTrigger value="content">Content</TabsTrigger>
                        <TabsTrigger value="notes">Notes</TabsTrigger>
                    </TabsList>
                    <TabsContent value="details" force-mount class="data-[state=inactive]:hidden">
                        <div class="grid items-center w-full gap-4 pt-4">
                            <div class="flex flex-col space-y-1.5">
                                <Label for="title">Title</Label>
                                <Input id="title" type="text" required autofocus :tabindex="1" autocomplete="title" v-model="form.title" placeholder="Errata Title" />
                                <InputError :message="form.errors.title" />
                            </div>
                        </div>
                    </TabsContent>
                    <TabsContent value="content" force-mount class="data-[state=inactive]:hidden">
                        <div class="grid items-center w-full gap-4 pt-4">
                            <div class="flex flex-col space-y-1.5">
                                <DraggableContent
                                    v-if="viewData"
                                    @update:content-order="contentUpdate"
                                    @update:new-content="contentNewContent"
                                    :content="viewData.content ?? []"
                                    label="Content"
                                    :indices="props.indices"
                                    :sections="props.sections"
                                    :pages="props.pages"
                                    :key="viewData.content"
                                />
                                <InputError :message="form.errors.content" />
                            </div>
                        </div>
                    </TabsContent>
                    <TabsContent value="notes" force-mount class="data-[state=inactive]:hidden">
                        <div class="grid items-center w-full gap-4 pt-4">
                            <div class="flex flex-col space-y-1.5" v-if="(props.errata && props.errata?.published_at) || props.errata?.approval?.change_notes">
                                <DraggableContent
                                    v-if="viewData"
                                    @update:content-order="changeNotesUpdate"
                                    @update:new-content="changeNotesNewContent"
                                    :content="viewData.change_notes ?? []"
                                    label="Change Notes"
                                    :indices="props.indices"
                                    :sections="props.sections"
                                    :pages="props.pages"
                                    :key="viewData.change_notes"
                                />
                                <InputError :message="form.errors.change_notes" />
                            </div>
                            <div class="flex flex-col space-y-1.5">
                                <Label for="internal_notes">Internal Notes</Label>
                                <Textarea class="min-h-48" id="internal_notes" v-model="form.internal_notes" placeholder="Add Internal Notes" />
                                <InputError :message="form.errors.internal_notes" />
                            </div>
                        </div>
                    </TabsContent>
                </Tabs>
            </form>
        </CardContent>
        <CardFooter>
            <div class="flex ml-auto my-auto">
                <Drawer>
                    <DrawerTrigger>
                        <Button class="bg-green-500">{{ props.errata ? 'Update' : 'Create' }} Errata</Button>
                    </DrawerTrigger>
                    <DrawerContent class="max-w-lg mx-auto">
                        <DrawerHeader>
                            <DrawerTitle>{{ props.errata ? 'Update' : 'Create' }} Errata</DrawerTitle>
                            <DrawerDescription>
                                <div class="mx-auto max-w-lg mt-2 container overflow-y-auto">
                                    <div class="flex mb-4">
                                        <Select id="batch" v-model="form.batch_id">
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
                                    <div class="flex items-center mb-4 space-x-2" v-if="hasPermission('approve_errata')">
                                        <Switch id="approve-directly" v-model="form.approve_directly" />
                                        <Label for="approve-directly">Approve Directly</Label>
                                    </div>
                                    <div class="flex items-center mb-4 space-x-2" v-if="hasPermission('publish_errata')">
                                        <Switch id="publish-directly" v-model="form.publish_directly" />
                                        <Label for="publish-directly">Publish Directly</Label>
                                    </div>
                                </div>
                            </DrawerDescription>
                        </DrawerHeader>
                        <DrawerFooter class="container grid grid-cols-2">
                            <Button @click="submitErrata">Submit</Button>
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
