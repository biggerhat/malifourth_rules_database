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
import {Select, SelectContent, SelectItem, SelectTrigger, SelectValue} from "@/components/ui/select";
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import InputError from "@/components/InputError.vue";
import {CircleX, ChevronDown} from "lucide-vue-next";
import { Textarea } from '@/components/ui/textarea'
import {hasPermission} from "@/composables/hasPermission";
import {
    Drawer, DrawerClose, DrawerContent,
    DrawerDescription,
    DrawerFooter,
    DrawerHeader,
    DrawerTitle,
    DrawerTrigger
} from "@/components/ui/drawer";
import {Switch} from "@/components/ui/switch";
import TipTapEditor from "@/components/tiptap/TipTapEditor.vue";
import { Separator } from '@/components/ui/separator';
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';

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
    form.left_column = props.section?.left_column ?? '';
    form.right_column = props.section?.right_column ?? '';
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
</script>

<template>
    <Head title="Section Information" />

    <Card>
        <CardHeader>
            <CardTitle>{{ props.section ? 'Edit' : 'New' }} Section</CardTitle>
            <CardDescription>
                <span class="text-destructive" v-if="!props.section">
                    You are creating a new Section item. To update an existing one, find it in the list and click Edit.
                </span>
                <span v-else>Editing: {{ props.section.title }}</span>
            </CardDescription>
        </CardHeader>
        <CardContent>
            <form @submit.prevent class="space-y-6">
                <div class="space-y-4">
                    <div class="space-y-2">
                        <Label for="title">Title</Label>
                        <Input id="title" type="text" required autofocus autocomplete="title" v-model="form.title" placeholder="Section Title" />
                        <InputError :message="form.errors.title" />
                    </div>
                </div>

                <Separator />

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <TipTapEditor v-model="form.left_column" label="Left Column" />
                        <InputError :message="form.errors.left_column" />
                    </div>
                    <div class="space-y-2">
                        <TipTapEditor v-model="form.right_column" label="Right Column" />
                        <InputError :message="form.errors.right_column" />
                    </div>
                </div>

                <Separator />

                <Collapsible>
                    <CollapsibleTrigger class="flex items-center gap-2 text-sm font-medium text-muted-foreground hover:text-foreground transition-colors">
                        <ChevronDown class="h-4 w-4" />
                        Notes
                    </CollapsibleTrigger>
                    <CollapsibleContent class="space-y-4 pt-4">
                        <div class="space-y-2" v-if="(props.section && props.section?.published_at) || props.section?.approval?.change_notes">
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
                    <Button>{{ props.section ? 'Update' : 'Create' }} Section</Button>
                </DrawerTrigger>
                <DrawerContent class="max-w-lg mx-auto">
                    <DrawerHeader>
                        <DrawerTitle>{{ props.section ? 'Update' : 'Create' }} Section</DrawerTitle>
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
                        <div class="flex items-center justify-between" v-if="hasPermission('approve_section')">
                            <Label for="approve-directly">Approve Directly</Label>
                            <Switch id="approve-directly" v-model="form.approve_directly" />
                        </div>
                        <div class="flex items-center justify-between" v-if="hasPermission('publish_section')">
                            <Label for="publish-directly">Publish Directly</Label>
                            <Switch id="publish-directly" v-model="form.publish_directly" />
                        </div>
                    </div>
                    <DrawerFooter class="grid grid-cols-2 gap-2">
                        <DrawerClose as-child>
                            <Button variant="outline">Cancel</Button>
                        </DrawerClose>
                        <Button @click="submitSection">Submit</Button>
                    </DrawerFooter>
                </DrawerContent>
            </Drawer>
        </CardFooter>
    </Card>
</template>
