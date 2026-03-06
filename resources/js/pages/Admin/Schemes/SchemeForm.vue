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
    scheme: {
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
    seasons: {
        type: [Object, Array],
        required: false,
        default() {
            return {};
        }
    },
    schemes: {
        type: [Object, Array],
        required: false,
        default() {
            return {};
        }
    },
});

const form = useForm({
    title: '',
    season_id: null,
    prerequisites: '',
    reveal: '',
    scoring: '',
    additional: '',
    front_image: null,
    back_image: null,
    combination_image: null,
    next_scheme_1: null,
    next_scheme_2: null,
    next_scheme_3: null,
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
    form.title = props.scheme?.title ?? '';
    form.season_id = props.scheme?.season_id ?? null;
    form.prerequisites = props.scheme?.prerequisites ?? '';
    form.reveal = props.scheme?.reveal ?? '';
    form.scoring = props.scheme?.scoring ?? '';
    form.additional = props.scheme?.additional ?? '';
    form.next_scheme_1 = props.scheme?.next_scheme_1 ?? null;
    form.next_scheme_2 = props.scheme?.next_scheme_2 ?? null;
    form.next_scheme_3 = props.scheme?.next_scheme_3 ?? null;
    form.internal_notes = props.scheme?.internal_notes ?? '';
    form.change_notes = props.scheme?.published_at ? '' : props.scheme?.approval?.change_notes ?? '';
    form.batch_id = props.scheme?.published_at ? null : props.scheme?.batch_id ?? null;
});

const submitScheme = () => {
    if (props.scheme) {
        form.post(route('admin.schemes.update', {scheme: props.scheme.slug}));
    } else {
        form.post(route('admin.schemes.store'));
    }
};
</script>

<template>
    <Head title="Scheme Information" />

    <Card>
        <CardHeader>
            <CardTitle>{{ props.scheme ? 'Edit' : 'New' }} Scheme</CardTitle>
            <CardDescription>
                <span class="text-destructive" v-if="!props.scheme">
                    You are creating a new Scheme item. To update an existing one, find it in the list and click Edit.
                </span>
                <span v-else>Editing: {{ props.scheme.title }}</span>
            </CardDescription>
        </CardHeader>
        <CardContent>
            <form @submit.prevent class="space-y-6">
                <div class="space-y-4">
                    <div class="space-y-2">
                        <Label for="title">Title</Label>
                        <Input id="title" type="text" required autofocus autocomplete="title" v-model="form.title" placeholder="Scheme Title" />
                        <InputError :message="form.errors.title" />
                    </div>
                    <div class="space-y-2">
                        <Label for="season_id">Season</Label>
                        <div class="flex gap-2">
                            <Select id="season_id" v-model="form.season_id">
                                <SelectTrigger class="w-full">
                                    <SelectValue placeholder="Select Season" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="season in props.seasons" :value="season.id" :key="season.id">
                                        {{ season.display_name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <Button variant="ghost" size="icon" v-if="form.season_id" @click="form.season_id = null">
                                <CircleX class="h-4 w-4 text-destructive" />
                            </Button>
                        </div>
                        <InputError :message="form.errors.season_id" />
                    </div>
                    <div class="space-y-2">
                        <Label for="next_scheme_1">Next Available Scheme 1</Label>
                        <div class="flex gap-2">
                            <Select id="next_scheme_1" v-model="form.next_scheme_1">
                                <SelectTrigger class="w-full">
                                    <SelectValue placeholder="Select Next Scheme 1" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="s in props.schemes" :value="s.id" :key="s.id">
                                        {{ s.display_name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <Button variant="ghost" size="icon" v-if="form.next_scheme_1" @click="form.next_scheme_1 = null">
                                <CircleX class="h-4 w-4 text-destructive" />
                            </Button>
                        </div>
                        <InputError :message="form.errors.next_scheme_1" />
                    </div>
                    <div class="space-y-2">
                        <Label for="next_scheme_2">Next Available Scheme 2</Label>
                        <div class="flex gap-2">
                            <Select id="next_scheme_2" v-model="form.next_scheme_2">
                                <SelectTrigger class="w-full">
                                    <SelectValue placeholder="Select Next Scheme 2" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="s in props.schemes" :value="s.id" :key="s.id">
                                        {{ s.display_name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <Button variant="ghost" size="icon" v-if="form.next_scheme_2" @click="form.next_scheme_2 = null">
                                <CircleX class="h-4 w-4 text-destructive" />
                            </Button>
                        </div>
                        <InputError :message="form.errors.next_scheme_2" />
                    </div>
                    <div class="space-y-2">
                        <Label for="next_scheme_3">Next Available Scheme 3</Label>
                        <div class="flex gap-2">
                            <Select id="next_scheme_3" v-model="form.next_scheme_3">
                                <SelectTrigger class="w-full">
                                    <SelectValue placeholder="Select Next Scheme 3" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="s in props.schemes" :value="s.id" :key="s.id">
                                        {{ s.display_name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <Button variant="ghost" size="icon" v-if="form.next_scheme_3" @click="form.next_scheme_3 = null">
                                <CircleX class="h-4 w-4 text-destructive" />
                            </Button>
                        </div>
                        <InputError :message="form.errors.next_scheme_3" />
                    </div>
                </div>

                <Separator />

                <div class="space-y-4">
                    <TipTapEditor v-model="form.prerequisites" label="Prerequisites" />
                    <InputError :message="form.errors.prerequisites" />
                </div>
                <div class="space-y-4">
                    <TipTapEditor v-model="form.reveal" label="Reveal" />
                    <InputError :message="form.errors.reveal" />
                </div>
                <div class="space-y-4">
                    <TipTapEditor v-model="form.scoring" label="Scoring" />
                    <InputError :message="form.errors.scoring" />
                </div>
                <div class="space-y-4">
                    <TipTapEditor v-model="form.additional" label="Additional" />
                    <InputError :message="form.errors.additional" />
                </div>

                <Separator />

                <div class="space-y-4">
                    <div class="space-y-2" v-if="props.scheme?.front_image">
                        <Label>Current Front Image</Label>
                        <img :src="props.scheme.front_image" :alt="props.scheme.title" class="w-75" />
                    </div>
                    <div class="space-y-2">
                        <Label for="front_image">{{ props.scheme?.front_image ? 'New ' : '' }}Front Image</Label>
                        <Input id="front_image" type="file" accept=".jpeg,.jpg,.png,.webp" @input="form.front_image = $event.target.files[0]" />
                        <InputError :message="form.errors.front_image" />
                    </div>
                    <div class="space-y-2" v-if="props.scheme?.back_image">
                        <Label>Current Back Image</Label>
                        <img :src="props.scheme.back_image" :alt="props.scheme.title" class="w-75" />
                    </div>
                    <div class="space-y-2">
                        <Label for="back_image">{{ props.scheme?.back_image ? 'New ' : '' }}Back Image</Label>
                        <Input id="back_image" type="file" accept=".jpeg,.jpg,.png,.webp" @input="form.back_image = $event.target.files[0]" />
                        <InputError :message="form.errors.back_image" />
                    </div>
                    <div class="space-y-2" v-if="props.scheme?.combination_image">
                        <Label>Current Combination Image</Label>
                        <img :src="props.scheme.combination_image" :alt="props.scheme.title" class="w-75" />
                    </div>
                    <div class="space-y-2">
                        <Label for="combination_image">{{ props.scheme?.combination_image ? 'New ' : '' }}Combination Image</Label>
                        <Input id="combination_image" type="file" accept=".jpeg,.jpg,.png,.webp" @input="form.combination_image = $event.target.files[0]" />
                        <InputError :message="form.errors.combination_image" />
                    </div>
                </div>

                <Separator />

                <Collapsible>
                    <CollapsibleTrigger class="flex items-center gap-2 text-sm font-medium text-muted-foreground hover:text-foreground transition-colors">
                        <ChevronDown class="h-4 w-4" />
                        Notes
                    </CollapsibleTrigger>
                    <CollapsibleContent class="space-y-4 pt-4">
                        <div class="space-y-2" v-if="(props.scheme && props.scheme?.published_at) || props.scheme?.approval?.change_notes">
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
                    <Button>{{ props.scheme ? 'Update' : 'Create' }} Scheme</Button>
                </DrawerTrigger>
                <DrawerContent class="max-w-lg mx-auto">
                    <DrawerHeader>
                        <DrawerTitle>{{ props.scheme ? 'Update' : 'Create' }} Scheme</DrawerTitle>
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
                        <div class="flex items-center justify-between" v-if="hasPermission('approve_scheme')">
                            <Label for="approve-directly">Approve Directly</Label>
                            <Switch id="approve-directly" v-model="form.approve_directly" />
                        </div>
                        <div class="flex items-center justify-between" v-if="hasPermission('publish_scheme')">
                            <Label for="publish-directly">Publish Directly</Label>
                            <Switch id="publish-directly" v-model="form.publish_directly" />
                        </div>
                    </div>
                    <DrawerFooter class="grid grid-cols-2 gap-2">
                        <DrawerClose as-child>
                            <Button variant="outline">Cancel</Button>
                        </DrawerClose>
                        <Button @click="submitScheme">Submit</Button>
                    </DrawerFooter>
                </DrawerContent>
            </Drawer>
        </CardFooter>
    </Card>
</template>
