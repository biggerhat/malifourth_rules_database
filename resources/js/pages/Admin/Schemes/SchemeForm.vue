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
    fetchViewData();
});

const submitScheme = () => {
    if (props.scheme) {
        form.post(route('admin.schemes.update', {scheme: props.scheme.slug}));
    } else {
        form.post(route('admin.schemes.store'));
    }
};

const viewData = ref(null);

const fetchViewData = () => {
    axios.post(route('admin.schemes.preview'), {
        title: form.title,
        prerequisites: form.prerequisites,
        reveal: form.reveal,
        scoring: form.scoring,
        additional: form.additional,
        change_notes: form.change_notes,
    }).then((response) => {
        viewData.value = JSON.parse(JSON.stringify(response.data));
    });
};

const prerequisitesUpdate = (newOrder) => { form.prerequisites = newOrder; };
const prerequisitesNewContent = (content) => { form.prerequisites = content; fetchViewData(); };
const revealUpdate = (newOrder) => { form.reveal = newOrder; };
const revealNewContent = (content) => { form.reveal = content; fetchViewData(); };
const scoringUpdate = (newOrder) => { form.scoring = newOrder; };
const scoringNewContent = (content) => { form.scoring = content; fetchViewData(); };
const additionalUpdate = (newOrder) => { form.additional = newOrder; };
const additionalNewContent = (content) => { form.additional = content; fetchViewData(); };
const changeNotesUpdate = (newOrder) => { form.change_notes = newOrder; };
const changeNotesNewContent = (content) => { form.change_notes = content; fetchViewData(); };
</script>

<template>
    <Head title="Scheme Information" />

    <Card>
        <CardHeader>
            <CardTitle>Scheme Form</CardTitle>
            <CardDescription>
                Create and Edit Scheme Information
                <span class="text-destructive" v-if="!props.scheme"><br />Make sure you want an entirely NEW Scheme. <br />
                    If you just want to update or change an existing Scheme, you need to edit it.</span>
            </CardDescription>
        </CardHeader>
        <CardContent>
            <form @submit.prevent>
                <Tabs default-value="details">
                    <TabsList>
                        <TabsTrigger value="details">Details</TabsTrigger>
                        <TabsTrigger value="content">Content</TabsTrigger>
                        <TabsTrigger value="images">Images</TabsTrigger>
                        <TabsTrigger value="notes">Notes</TabsTrigger>
                    </TabsList>
                    <TabsContent value="details" force-mount class="data-[state=inactive]:hidden">
                        <div class="grid items-center w-full gap-4 pt-4">
                            <div class="flex flex-col space-y-1.5">
                                <Label for="title">Title</Label>
                                <Input id="title" type="text" required autofocus :tabindex="1" autocomplete="title" v-model="form.title" placeholder="Scheme Title" />
                                <InputError :message="form.errors.title" />
                            </div>
                            <div class="flex flex-col space-y-1.5">
                                <Label for="season_id">Season</Label>
                                <div class="flex">
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
                                    <CircleX class="text-destructive my-auto ml-2" v-if="form.season_id" @click="form.season_id = null" />
                                </div>
                                <InputError :message="form.errors.season_id" />
                            </div>
                            <div class="flex flex-col space-y-1.5">
                                <Label for="next_scheme_1">Next Available Scheme 1</Label>
                                <div class="flex">
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
                                    <CircleX class="text-destructive my-auto ml-2" v-if="form.next_scheme_1" @click="form.next_scheme_1 = null" />
                                </div>
                                <InputError :message="form.errors.next_scheme_1" />
                            </div>
                            <div class="flex flex-col space-y-1.5">
                                <Label for="next_scheme_2">Next Available Scheme 2</Label>
                                <div class="flex">
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
                                    <CircleX class="text-destructive my-auto ml-2" v-if="form.next_scheme_2" @click="form.next_scheme_2 = null" />
                                </div>
                                <InputError :message="form.errors.next_scheme_2" />
                            </div>
                            <div class="flex flex-col space-y-1.5">
                                <Label for="next_scheme_3">Next Available Scheme 3</Label>
                                <div class="flex">
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
                                    <CircleX class="text-destructive my-auto ml-2" v-if="form.next_scheme_3" @click="form.next_scheme_3 = null" />
                                </div>
                                <InputError :message="form.errors.next_scheme_3" />
                            </div>
                        </div>
                    </TabsContent>
                    <TabsContent value="content" force-mount class="data-[state=inactive]:hidden">
                        <div class="grid items-center w-full gap-4 pt-4">
                            <div class="flex flex-col space-y-1.5">
                                <DraggableContent
                                    v-if="viewData"
                                    @update:content-order="prerequisitesUpdate"
                                    @update:new-content="prerequisitesNewContent"
                                    :content="viewData.prerequisites ?? []"
                                    label="Prerequisites"
                                    :indices="props.indices"
                                    :sections="props.sections"
                                    :pages="props.pages"
                                    :key="viewData.prerequisites"
                                />
                                <InputError :message="form.errors.prerequisites" />
                            </div>
                            <div class="flex flex-col space-y-1.5">
                                <DraggableContent
                                    v-if="viewData"
                                    @update:content-order="revealUpdate"
                                    @update:new-content="revealNewContent"
                                    :content="viewData.reveal ?? []"
                                    label="Reveal"
                                    :indices="props.indices"
                                    :sections="props.sections"
                                    :pages="props.pages"
                                    :key="viewData.reveal"
                                />
                                <InputError :message="form.errors.reveal" />
                            </div>
                            <div class="flex flex-col space-y-1.5">
                                <DraggableContent
                                    v-if="viewData"
                                    @update:content-order="scoringUpdate"
                                    @update:new-content="scoringNewContent"
                                    :content="viewData.scoring ?? []"
                                    label="Scoring"
                                    :indices="props.indices"
                                    :sections="props.sections"
                                    :pages="props.pages"
                                    :key="viewData.scoring"
                                />
                                <InputError :message="form.errors.scoring" />
                            </div>
                            <div class="flex flex-col space-y-1.5">
                                <DraggableContent
                                    v-if="viewData"
                                    @update:content-order="additionalUpdate"
                                    @update:new-content="additionalNewContent"
                                    :content="viewData.additional ?? []"
                                    label="Additional"
                                    :indices="props.indices"
                                    :sections="props.sections"
                                    :pages="props.pages"
                                    :key="viewData.additional"
                                />
                                <InputError :message="form.errors.additional" />
                            </div>
                        </div>
                    </TabsContent>
                    <TabsContent value="images" force-mount class="data-[state=inactive]:hidden">
                        <div class="grid items-center w-full gap-4 pt-4">
                            <div class="flex flex-col space-y-1.5" v-if="props.scheme?.front_image">
                                <Label>Current Front Image</Label>
                                <img :src="props.scheme.front_image" :alt="props.scheme.title" class="w-75" />
                            </div>
                            <div class="flex flex-col space-y-1.5">
                                <Label for="front_image">{{ props.scheme?.front_image ? 'New ' : '' }}Front Image</Label>
                                <Input id="front_image" type="file" accept=".jpeg,.jpg,.png,.webp" @input="form.front_image = $event.target.files[0]" />
                                <InputError :message="form.errors.front_image" />
                            </div>
                            <div class="flex flex-col space-y-1.5" v-if="props.scheme?.back_image">
                                <Label>Current Back Image</Label>
                                <img :src="props.scheme.back_image" :alt="props.scheme.title" class="w-75" />
                            </div>
                            <div class="flex flex-col space-y-1.5">
                                <Label for="back_image">{{ props.scheme?.back_image ? 'New ' : '' }}Back Image</Label>
                                <Input id="back_image" type="file" accept=".jpeg,.jpg,.png,.webp" @input="form.back_image = $event.target.files[0]" />
                                <InputError :message="form.errors.back_image" />
                            </div>
                            <div class="flex flex-col space-y-1.5" v-if="props.scheme?.combination_image">
                                <Label>Current Combination Image</Label>
                                <img :src="props.scheme.combination_image" :alt="props.scheme.title" class="w-75" />
                            </div>
                            <div class="flex flex-col space-y-1.5">
                                <Label for="combination_image">{{ props.scheme?.combination_image ? 'New ' : '' }}Combination Image</Label>
                                <Input id="combination_image" type="file" accept=".jpeg,.jpg,.png,.webp" @input="form.combination_image = $event.target.files[0]" />
                                <InputError :message="form.errors.combination_image" />
                            </div>
                        </div>
                    </TabsContent>
                    <TabsContent value="notes" force-mount class="data-[state=inactive]:hidden">
                        <div class="grid items-center w-full gap-4 pt-4">
                            <div class="flex flex-col space-y-1.5" v-if="(props.scheme && props.scheme?.published_at) || props.scheme?.approval?.change_notes">
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
                        <Button class="bg-green-500">{{ props.scheme ? 'Update' : 'Create' }} Scheme</Button>
                    </DrawerTrigger>
                    <DrawerContent class="max-w-lg mx-auto">
                        <DrawerHeader>
                            <DrawerTitle>{{ props.scheme ? 'Update' : 'Create' }} Scheme</DrawerTitle>
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
                                    <div class="flex items-center mb-4 space-x-2" v-if="hasPermission('approve_scheme')">
                                        <Switch id="approve-directly" v-model="form.approve_directly" />
                                        <Label for="approve-directly">Approve Directly</Label>
                                    </div>
                                    <div class="flex items-center mb-4 space-x-2" v-if="hasPermission('publish_scheme')">
                                        <Switch id="publish-directly" v-model="form.publish_directly" />
                                        <Label for="publish-directly">Publish Directly</Label>
                                    </div>
                                </div>
                            </DrawerDescription>
                        </DrawerHeader>
                        <DrawerFooter class="container grid grid-cols-2">
                            <Button @click="submitScheme">Submit</Button>
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
