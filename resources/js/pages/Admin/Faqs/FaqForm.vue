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
    faq: {
        type: [Object, Array],
        required: false,
        default() {
            return null;
        }
    },
    faq_categories: {
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
    category: null,
    sort_order: 0,
    answer: '',
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
    form.title = props.faq?.title ?? '';
    form.category = props.faq?.category ?? null;
    form.sort_order = props.faq?.sort_order ?? 0;
    form.answer = props.faq?.answer ?? '';
    form.internal_notes = props.faq?.internal_notes ?? '';
    form.change_notes = props.faq?.published_at ? '' : props.faq?.approval?.change_notes ?? '';
    form.batch_id = props.faq?.published_at ? null : props.faq?.batch_id ?? null;
    fetchViewData();
});

const submitFaq = () => {
    if (props.faq) {
        form.post(route('admin.faqs.update', {faq: props.faq.slug}));
    } else {
        form.post(route('admin.faqs.store'));
    }
};

const viewData = ref(null);

const fetchViewData = () => {
    axios.post(route('admin.faqs.preview'), { title: form.title, answer: form.answer, change_notes: form.change_notes }).then((response) => {
        viewData.value = response.data;
        viewData.value = JSON.parse(JSON.stringify(response.data));
    });
};

const titleUpdate = (newOrder) => {
    form.title = newOrder;
};

const titleNewContent = (content) => {
    form.title = content;
    fetchViewData();
};

const answerUpdate = (newOrder) => {
    form.answer = newOrder;
};

const answerNewContent = (content) => {
    form.answer = content;
    fetchViewData();
}

const changeNotesUpdate = (newOrder) => {
    form.change_notes = newOrder;
};

const changeNotesNewContent = (content) => {
    form.change_notes = content;
    fetchViewData();
}
</script>

<template>
    <Head title="FAQ Information" />

    <Card>
        <CardHeader>
            <CardTitle>FAQ Form</CardTitle>
            <CardDescription>
                Create and Edit FAQ Entries
                <span class="text-destructive" v-if="!props.faq"><br />Make sure you want an entirely NEW FAQ. <br />
                    If you just want to update or change an existing FAQ, you need to edit it.</span>
            </CardDescription>
        </CardHeader>
        <CardContent>
            <form @submit.prevent>
                <Tabs default-value="content">
                    <TabsList>
                        <TabsTrigger value="content">Content</TabsTrigger>
                        <TabsTrigger value="notes">Notes</TabsTrigger>
                    </TabsList>
                    <TabsContent value="content" force-mount class="data-[state=inactive]:hidden">
                        <div class="grid items-center w-full gap-4 pt-4">
                            <div class="flex flex-col space-y-1.5">
                                <DraggableContent
                                    v-if="viewData"
                                    @update:content-order="titleUpdate"
                                    @update:new-content="titleNewContent"
                                    :content="viewData.title ?? []"
                                    label="Question"
                                    :indices="props.indices"
                                    :sections="props.sections"
                                    :pages="props.pages"
                                    :key="viewData.title"
                                />
                                <InputError :message="form.errors.title" />
                            </div>
                            <div class="flex flex-col space-y-1.5">
                                <Label for="category">Category</Label>
                                <div class="flex">
                                    <Select id="category" v-model="form.category">
                                        <SelectTrigger class="w-full">
                                            <SelectValue placeholder="Select Category" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem v-for="cat in props.faq_categories" :value="cat.value" :key="cat.value">
                                                {{ cat.name }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <CircleX class="text-destructive my-auto ml-2" v-if="form.category" @click="form.category = null" />
                                </div>
                                <InputError :message="form.errors.category" />
                            </div>
                            <div class="flex flex-col space-y-1.5">
                                <Label for="sort_order">Sort Order</Label>
                                <Input id="sort_order" type="number" v-model="form.sort_order" placeholder="0" />
                                <InputError :message="form.errors.sort_order" />
                            </div>
                            <div class="flex flex-col space-y-1.5">
                                <DraggableContent
                                    v-if="viewData"
                                    @update:content-order="answerUpdate"
                                    @update:new-content="answerNewContent"
                                    :content="viewData.answer ?? []"
                                    label="Answer"
                                    :indices="props.indices"
                                    :sections="props.sections"
                                    :pages="props.pages"
                                    :key="viewData.answer"
                                />
                                <InputError :message="form.errors.answer" />
                            </div>
                        </div>
                    </TabsContent>
                    <TabsContent value="notes" force-mount class="data-[state=inactive]:hidden">
                        <div class="grid items-center w-full gap-4 pt-4">
                            <div class="flex flex-col space-y-1.5" v-if="(props.faq && props.faq?.published_at) || props.faq?.approval?.change_notes">
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
                        <Button class="bg-green-500">{{ props.faq ? 'Update' : 'Create' }} FAQ</Button>
                    </DrawerTrigger>
                    <DrawerContent class="max-w-lg mx-auto">
                        <DrawerHeader>
                            <DrawerTitle>{{ props.faq ? 'Update' : 'Create' }} FAQ</DrawerTitle>
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
                                    <div class="flex items-center mb-4 space-x-2" v-if="hasPermission('approve_faq')">
                                        <Switch id="approve-directly" v-model="form.approve_directly" />
                                        <Label for="approve-directly">Approve Directly</Label>
                                    </div>
                                    <div class="flex items-center mb-4 space-x-2" v-if="hasPermission('publish_faq')">
                                        <Switch id="publish-directly" v-model="form.publish_directly" />
                                        <Label for="publish-directly">Publish Directly</Label>
                                    </div>
                                </div>
                            </DrawerDescription>
                        </DrawerHeader>
                        <DrawerFooter class="container grid grid-cols-2">
                            <Button @click="submitFaq">Submit</Button>
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
