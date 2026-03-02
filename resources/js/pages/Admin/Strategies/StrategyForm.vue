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
    strategy: {
        type: [Object, Array],
        required: false,
        default() {
            return null;
        }
    },
    suit_options: {
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
    seasons: {
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
    suit: null,
    setup: '',
    rules: '',
    scoring: '',
    additional: '',
    front_image: null,
    back_image: null,
    combination_image: null,
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
    form.title = props.strategy?.title ?? '';
    form.season_id = props.strategy?.season_id ?? null;
    form.suit = props.strategy?.suit ?? null;
    form.setup = props.strategy?.setup ?? '';
    form.rules = props.strategy?.rules ?? '';
    form.scoring = props.strategy?.scoring ?? '';
    form.additional = props.strategy?.additional ?? '';
    form.internal_notes = props.strategy?.internal_notes ?? '';
    form.change_notes = props.strategy?.published_at ? '' : props.strategy?.approval?.change_notes ?? '';
    form.batch_id = props.strategy?.published_at ? null : props.strategy?.batch_id ?? null;
    fetchViewData();
});

const submitStrategy = () => {
    if (props.strategy) {
        form.post(route('admin.strategies.update', {strategy: props.strategy.slug}));
    } else {
        form.post(route('admin.strategies.store'));
    }
};

const viewData = ref(null);

const fetchViewData = () => {
    axios.post(route('admin.strategies.preview'), {
        title: form.title,
        setup: form.setup,
        rules: form.rules,
        scoring: form.scoring,
        additional: form.additional,
        change_notes: form.change_notes,
    }).then((response) => {
        viewData.value = JSON.parse(JSON.stringify(response.data));
    });
};

const setupUpdate = (newOrder) => { form.setup = newOrder; };
const setupNewContent = (content) => { form.setup = content; fetchViewData(); };
const rulesUpdate = (newOrder) => { form.rules = newOrder; };
const rulesNewContent = (content) => { form.rules = content; fetchViewData(); };
const scoringUpdate = (newOrder) => { form.scoring = newOrder; };
const scoringNewContent = (content) => { form.scoring = content; fetchViewData(); };
const additionalUpdate = (newOrder) => { form.additional = newOrder; };
const additionalNewContent = (content) => { form.additional = content; fetchViewData(); };
const changeNotesUpdate = (newOrder) => { form.change_notes = newOrder; };
const changeNotesNewContent = (content) => { form.change_notes = content; fetchViewData(); };
</script>

<template>
    <Head title="Strategy Information" />

    <Card>
        <CardHeader>
            <CardTitle>Strategy Form</CardTitle>
            <CardDescription>
                Create and Edit Strategy Information
                <span class="text-destructive" v-if="!props.strategy"><br />Make sure you want an entirely NEW Strategy. <br />
                    If you just want to update or change an existing Strategy, you need to edit it.</span>
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
                                <Input id="title" type="text" required autofocus :tabindex="1" autocomplete="title" v-model="form.title" placeholder="Strategy Title" />
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
                                <Label for="suit">Suit</Label>
                                <div class="flex">
                                    <Select id="suit" v-model="form.suit">
                                        <SelectTrigger class="w-full">
                                            <SelectValue placeholder="Select Suit" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem v-for="suit in props.suit_options" :value="suit.value" :key="suit.value">
                                                {{ suit.name }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <CircleX class="text-destructive my-auto ml-2" v-if="form.suit" @click="form.suit = null" />
                                </div>
                                <InputError :message="form.errors.suit" />
                            </div>
                        </div>
                    </TabsContent>
                    <TabsContent value="content" force-mount class="data-[state=inactive]:hidden">
                        <div class="grid items-center w-full gap-4 pt-4">
                            <div class="flex flex-col space-y-1.5">
                                <DraggableContent
                                    v-if="viewData"
                                    @update:content-order="setupUpdate"
                                    @update:new-content="setupNewContent"
                                    :content="viewData.setup ?? []"
                                    label="Setup"
                                    :indices="props.indices"
                                    :sections="props.sections"
                                    :pages="props.pages"
                                    :key="viewData.setup"
                                />
                                <InputError :message="form.errors.setup" />
                            </div>
                            <div class="flex flex-col space-y-1.5">
                                <DraggableContent
                                    v-if="viewData"
                                    @update:content-order="rulesUpdate"
                                    @update:new-content="rulesNewContent"
                                    :content="viewData.rules ?? []"
                                    label="Rules"
                                    :indices="props.indices"
                                    :sections="props.sections"
                                    :pages="props.pages"
                                    :key="viewData.rules"
                                />
                                <InputError :message="form.errors.rules" />
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
                            <div class="flex flex-col space-y-1.5" v-if="props.strategy?.front_image">
                                <Label>Current Front Image</Label>
                                <img :src="props.strategy.front_image" :alt="props.strategy.title" class="w-75" />
                            </div>
                            <div class="flex flex-col space-y-1.5">
                                <Label for="front_image">{{ props.strategy?.front_image ? 'New ' : '' }}Front Image</Label>
                                <Input id="front_image" type="file" accept=".jpeg,.jpg,.png,.webp" @input="form.front_image = $event.target.files[0]" />
                                <InputError :message="form.errors.front_image" />
                            </div>
                            <div class="flex flex-col space-y-1.5" v-if="props.strategy?.back_image">
                                <Label>Current Back Image</Label>
                                <img :src="props.strategy.back_image" :alt="props.strategy.title" class="w-75" />
                            </div>
                            <div class="flex flex-col space-y-1.5">
                                <Label for="back_image">{{ props.strategy?.back_image ? 'New ' : '' }}Back Image</Label>
                                <Input id="back_image" type="file" accept=".jpeg,.jpg,.png,.webp" @input="form.back_image = $event.target.files[0]" />
                                <InputError :message="form.errors.back_image" />
                            </div>
                            <div class="flex flex-col space-y-1.5" v-if="props.strategy?.combination_image">
                                <Label>Current Combination Image</Label>
                                <img :src="props.strategy.combination_image" :alt="props.strategy.title" class="w-75" />
                            </div>
                            <div class="flex flex-col space-y-1.5">
                                <Label for="combination_image">{{ props.strategy?.combination_image ? 'New ' : '' }}Combination Image</Label>
                                <Input id="combination_image" type="file" accept=".jpeg,.jpg,.png,.webp" @input="form.combination_image = $event.target.files[0]" />
                                <InputError :message="form.errors.combination_image" />
                            </div>
                        </div>
                    </TabsContent>
                    <TabsContent value="notes" force-mount class="data-[state=inactive]:hidden">
                        <div class="grid items-center w-full gap-4 pt-4">
                            <div class="flex flex-col space-y-1.5" v-if="(props.strategy && props.strategy?.published_at) || props.strategy?.approval?.change_notes">
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
                        <Button class="bg-green-500">{{ props.strategy ? 'Update' : 'Create' }} Strategy</Button>
                    </DrawerTrigger>
                    <DrawerContent class="max-w-lg mx-auto">
                        <DrawerHeader>
                            <DrawerTitle>{{ props.strategy ? 'Update' : 'Create' }} Strategy</DrawerTitle>
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
                                    <div class="flex items-center mb-4 space-x-2" v-if="hasPermission('approve_strategy')">
                                        <Switch id="approve-directly" v-model="form.approve_directly" />
                                        <Label for="approve-directly">Approve Directly</Label>
                                    </div>
                                    <div class="flex items-center mb-4 space-x-2" v-if="hasPermission('publish_strategy')">
                                        <Switch id="publish-directly" v-model="form.publish_directly" />
                                        <Label for="publish-directly">Publish Directly</Label>
                                    </div>
                                </div>
                            </DrawerDescription>
                        </DrawerHeader>
                        <DrawerFooter class="container grid grid-cols-2">
                            <Button @click="submitStrategy">Submit</Button>
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
