<script setup lang='ts'>
import { onMounted } from 'vue';
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
import {CircleX, ChevronDown} from "lucide-vue-next";
import { Textarea } from '@/components/ui/textarea'
import {hasPermission} from "@/composables/hasPermission";
import TipTapEditor from "@/components/tiptap/TipTapEditor.vue";
import { Separator } from '@/components/ui/separator';
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';

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
});

const submitFaq = () => {
    if (props.faq) {
        form.post(route('admin.faqs.update', {faq: props.faq.slug}));
    } else {
        form.post(route('admin.faqs.store'));
    }
};
</script>

<template>
    <Head title="FAQ Information" />

    <Card>
        <CardHeader>
            <CardTitle>{{ props.faq ? 'Edit' : 'New' }} FAQ</CardTitle>
            <CardDescription>
                <span class="text-destructive" v-if="!props.faq">
                    You are creating a new FAQ item. To update an existing one, find it in the list and click Edit.
                </span>
                <span v-else>Editing: {{ props.faq.title }}</span>
            </CardDescription>
        </CardHeader>
        <CardContent>
            <form @submit.prevent class="space-y-6">
                <div class="space-y-4">
                    <div class="space-y-2">
                        <TipTapEditor v-model="form.title" label="Question" mode="inline" />
                        <InputError :message="form.errors.title" />
                    </div>
                    <div class="space-y-2">
                        <Label for="category">Category</Label>
                        <div class="flex gap-2">
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
                            <Button variant="ghost" size="icon" v-if="form.category" @click="form.category = null">
                                <CircleX class="h-4 w-4 text-destructive" />
                            </Button>
                        </div>
                        <InputError :message="form.errors.category" />
                    </div>
                    <div class="space-y-2">
                        <Label for="sort_order">Sort Order</Label>
                        <Input id="sort_order" type="number" v-model="form.sort_order" placeholder="0" />
                        <InputError :message="form.errors.sort_order" />
                    </div>
                </div>

                <Separator />

                <div class="space-y-4">
                    <TipTapEditor v-model="form.answer" label="Answer" />
                    <InputError :message="form.errors.answer" />
                </div>

                <Separator />

                <Collapsible>
                    <CollapsibleTrigger class="flex items-center gap-2 text-sm font-medium text-muted-foreground hover:text-foreground transition-colors">
                        <ChevronDown class="h-4 w-4" />
                        Notes
                    </CollapsibleTrigger>
                    <CollapsibleContent class="space-y-4 pt-4">
                        <div class="space-y-2" v-if="(props.faq && props.faq?.published_at) || props.faq?.approval?.change_notes">
                            <TipTapEditor v-model="form.change_notes" label="Change Notes" />
                            <InputError :message="form.errors.change_notes" />
                        </div>
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
            <Drawer>
                <DrawerTrigger as-child>
                    <Button>{{ props.faq ? 'Update' : 'Create' }} FAQ</Button>
                </DrawerTrigger>
                <DrawerContent class="max-w-lg mx-auto">
                    <DrawerHeader>
                        <DrawerTitle>{{ props.faq ? 'Update' : 'Create' }} FAQ</DrawerTitle>
                        <DrawerDescription>Configure batch and publishing options before submitting.</DrawerDescription>
                    </DrawerHeader>
                    <div class="px-4 space-y-4">
                        <div class="space-y-2">
                            <Label>Batch</Label>
                            <div class="flex gap-2">
                                <Select v-model="form.batch_id">
                                    <SelectTrigger class="w-full">
                                        <SelectValue placeholder="Select Batch" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="batch in props.batches" :value="batch.id" :key="batch.id">
                                            {{ batch.title }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <Button variant="ghost" size="icon" v-if="form.batch_id" @click="form.batch_id = null">
                                    <CircleX class="h-4 w-4 text-destructive" />
                                </Button>
                            </div>
                        </div>
                        <div class="flex items-center justify-between" v-if="hasPermission('approve_faq')">
                            <Label for="approve-directly">Approve Directly</Label>
                            <Switch id="approve-directly" v-model="form.approve_directly" />
                        </div>
                        <div class="flex items-center justify-between" v-if="hasPermission('publish_faq')">
                            <Label for="publish-directly">Publish Directly</Label>
                            <Switch id="publish-directly" v-model="form.publish_directly" />
                        </div>
                    </div>
                    <DrawerFooter class="grid grid-cols-2 gap-2">
                        <DrawerClose as-child>
                            <Button variant="outline">Cancel</Button>
                        </DrawerClose>
                        <Button @click="submitFaq">Submit</Button>
                    </DrawerFooter>
                </DrawerContent>
            </Drawer>
        </CardFooter>
    </Card>
</template>
