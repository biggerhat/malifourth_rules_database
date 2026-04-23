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
    season: {
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
    content: '',
    internal_notes: '',
    change_notes: '',
    url: '',
    batch_id: null,
    publish_directly: false,
    approve_directly: false,
});

const back = () => {
    history.back();
};

onMounted(() => {
    form.title = props.season?.title ?? null;
    form.content = props.season?.content ?? '';
    form.url = props.season?.url ?? '';
    form.internal_notes = props.season?.internal_notes ?? '';
    form.change_notes = props.season?.published_at ? '' : props.season?.approval?.change_notes ?? '';
    form.batch_id = props.season?.published_at ? null : props.season?.batch_id ?? null;
});

const submitSeason = () => {
    if (props.season) {
        form.post(route('admin.seasons.update', {season: props.season.slug}));
    } else {
        form.post(route('admin.seasons.store'));
    }
};
</script>

<template>
    <Head title="Season Information" />

    <Card>
        <CardHeader>
            <CardTitle>{{ props.season ? 'Edit' : 'New' }} Season</CardTitle>
            <CardDescription>
                <span class="text-destructive" v-if="!props.season">
                    You are creating a new Season item. To update an existing one, find it in the list and click Edit.
                </span>
                <span v-else>Editing: {{ props.season.title }}</span>
            </CardDescription>
        </CardHeader>
        <CardContent>
            <form @submit.prevent class="space-y-6">
                <div class="space-y-4">
                    <div class="space-y-2">
                        <Label for="title">Title</Label>
                        <Input id="title" type="text" required autofocus autocomplete="title" v-model="form.title" placeholder="Season Title" />
                        <InputError :message="form.errors.title" />
                    </div>
                    <div class="space-y-2">
                        <Label for="url">Announcement URL</Label>
                        <Input id="url" type="text" autocomplete="announcement_url" v-model="form.url" placeholder="Announcement URL" />
                        <InputError :message="form.errors.url" />
                    </div>
                </div>

                <Separator />

                <div class="space-y-4">
                    <TipTapEditor v-model="form.content" label="Content" />
                    <InputError :message="form.errors.content" />
                </div>

                <Separator />

                <Collapsible>
                    <CollapsibleTrigger class="flex items-center gap-2 text-sm font-medium text-muted-foreground hover:text-foreground transition-colors">
                        <ChevronDown class="h-4 w-4" />
                        Notes
                    </CollapsibleTrigger>
                    <CollapsibleContent class="space-y-4 pt-4">
                        <div class="space-y-2" v-if="(props.season && props.season?.published_at) || props.season?.approval?.change_notes">
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
                    <Button>{{ props.season ? 'Update' : 'Create' }} Season</Button>
                </DrawerTrigger>
                <DrawerContent class="max-w-lg mx-auto">
                    <DrawerHeader>
                        <DrawerTitle>{{ props.season ? 'Update' : 'Create' }} Season</DrawerTitle>
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
                        <div class="flex items-center justify-between" v-if="hasPermission('approve_season')">
                            <Label for="approve-directly">Approve Directly</Label>
                            <Switch id="approve-directly" v-model="form.approve_directly" />
                        </div>
                        <div class="flex items-center justify-between" v-if="hasPermission('publish_season')">
                            <Label for="publish-directly">Publish Directly</Label>
                            <Switch id="publish-directly" v-model="form.publish_directly" />
                        </div>
                    </div>
                    <DrawerFooter class="grid grid-cols-2 gap-2">
                        <DrawerClose as-child>
                            <Button variant="outline">Cancel</Button>
                        </DrawerClose>
                        <Button @click="submitSeason">Submit</Button>
                    </DrawerFooter>
                </DrawerContent>
            </Drawer>
        </CardFooter>
    </Card>
</template>
