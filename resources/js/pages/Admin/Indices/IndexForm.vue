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
            <CardTitle>{{ props.index ? 'Edit' : 'New' }} Index</CardTitle>
            <CardDescription>
                <span class="text-destructive" v-if="!props.index">
                    You are creating a new Index item. To update an existing one, find it in the list and click Edit.
                </span>
                <span v-else>Editing: {{ props.index.title }}</span>
            </CardDescription>
        </CardHeader>
        <CardContent>
            <form @submit.prevent class="space-y-6">
                <div class="space-y-4">
                    <div class="space-y-2">
                        <Label for="title">Title</Label>
                        <Input id="title" type="text" required autofocus autocomplete="title" v-model="form.title" placeholder="Index Title" />
                        <InputError :message="form.errors.title" />
                    </div>
                    <div class="space-y-2">
                        <Label for="type">Index Type</Label>
                        <div class="flex gap-2">
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
                            <Button variant="ghost" size="icon" v-if="form.type && !props.index" @click="form.type = null">
                                <CircleX class="h-4 w-4 text-destructive" />
                            </Button>
                        </div>
                        <InputError :message="form.errors.type" />
                    </div>
                    <div class="space-y-2" v-if="props.index?.image">
                        <Label for="current_image">Current Image</Label>
                        <img id="current_image" :src="props.index?.image" :alt="props.index?.title" class="w-75" />
                    </div>
                    <div class="space-y-2" v-if="form.type === 'image'">
                        <Label for="image" v-if="props.index">New Image</Label>
                        <Label for="image" v-else>Image</Label>
                        <Input id="image" type="file" accept=".jpeg, .jpg, .png" @input="form.image = $event.target.files[0]" />
                    </div>
                </div>

                <Separator />

                <div class="space-y-4" v-if="form.type === 'text'">
                    <TipTapEditor v-model="form.content" label="Index Content" />
                    <InputError :message="form.errors.content" />
                </div>

                <Separator />

                <Collapsible>
                    <CollapsibleTrigger class="flex items-center gap-2 text-sm font-medium text-muted-foreground hover:text-foreground transition-colors">
                        <ChevronDown class="h-4 w-4" />
                        Notes
                    </CollapsibleTrigger>
                    <CollapsibleContent class="space-y-4 pt-4">
                        <div class="space-y-2" v-if="(props.index && props.index?.published_at) || props.index?.approval?.change_notes">
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
                    <Button>{{ props.index ? 'Update' : 'Create' }} Index</Button>
                </DrawerTrigger>
                <DrawerContent class="max-w-lg mx-auto">
                    <DrawerHeader>
                        <DrawerTitle>{{ props.index ? 'Update' : 'Create' }} Index</DrawerTitle>
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
                        <div class="flex items-center justify-between" v-if="hasPermission('approve_index')">
                            <Label for="approve-directly">Approve Directly</Label>
                            <Switch id="approve-directly" v-model="form.approve_directly" />
                        </div>
                        <div class="flex items-center justify-between" v-if="hasPermission('publish_index')">
                            <Label for="publish-directly">Publish Directly</Label>
                            <Switch id="publish-directly" v-model="form.publish_directly" />
                        </div>
                    </div>
                    <DrawerFooter class="grid grid-cols-2 gap-2">
                        <DrawerClose as-child>
                            <Button variant="outline">Cancel</Button>
                        </DrawerClose>
                        <Button @click="submitIndex">Submit</Button>
                    </DrawerFooter>
                </DrawerContent>
            </Drawer>
        </CardFooter>
    </Card>
</template>
