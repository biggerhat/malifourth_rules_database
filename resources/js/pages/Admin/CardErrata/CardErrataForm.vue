<script setup lang='ts'>
import { onMounted, ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import axios from 'axios';
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
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import InputError from '@/components/InputError.vue';
import { CircleX, Eye, Plus, Trash2 } from 'lucide-vue-next';
import { Textarea } from '@/components/ui/textarea'
import SimpleRichTextarea from '@/components/SimpleRichTextarea.vue';
import { hasPermission } from '@/composables/hasPermission';
import { Tabs, TabsList, TabsTrigger, TabsContent } from '@/components/ui/tabs'
import CardErrataView from '@/pages/Errata/CardErrata/CardErrataView.vue';

const props = defineProps({
    cardErrata: {
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
    faction_options: {
        type: Array,
        required: false,
        default() {
            return [];
        }
    }
});

function makeEmptyEntry() {
    return {
        what_changed: '',
        what_it_was: '',
        what_it_is_now: '',
    };
}

const form = useForm({
    faction: '',
    card_name: '',
    image: null,
    existing_image: null,
    internal_notes: '',
    change_notes: '',
    batch_id: null,
    publish_directly: false,
    approve_directly: false,
    entries: [makeEmptyEntry()],
});

const back = () => {
    history.back();
};

onMounted(() => {
    form.faction = props.cardErrata?.faction ?? '';
    form.card_name = props.cardErrata?.card_name ?? '';
    form.existing_image = props.cardErrata?.image ?? null;
    form.internal_notes = props.cardErrata?.internal_notes ?? '';
    form.change_notes = props.cardErrata?.published_at ? '' : props.cardErrata?.approval?.change_notes ?? '';
    form.batch_id = props.cardErrata?.published_at ? null : props.cardErrata?.batch_id ?? null;

    if (props.cardErrata?.entries?.length) {
        form.entries = props.cardErrata.entries.map(entry => ({
            what_changed: entry.what_changed ?? '',
            what_it_was: entry.what_it_was ?? '',
            what_it_is_now: entry.what_it_is_now ?? '',
        }));
    }
});

const addEntry = () => {
    form.entries.push(makeEmptyEntry());
};

const removeEntry = (index) => {
    form.entries.splice(index, 1);
};

const previewData = ref(null);

const fetchPreviewData = () => {
    axios.post(route('admin.card-errata.preview'), {
        card_name: form.card_name,
        faction: form.faction,
        entries: form.entries.map((entry) => ({
            what_changed: entry.what_changed,
            what_it_was: entry.what_it_was,
            what_it_is_now: entry.what_it_is_now,
        })),
    }).then((response) => {
        previewData.value = {
            ...JSON.parse(JSON.stringify(response.data)),
            faction: form.faction,
            slug: props.cardErrata?.slug ?? '',
            image: form.image ? URL.createObjectURL(form.image) : form.existing_image,
            published_at: props.cardErrata?.published_at ?? null,
            published_by: props.cardErrata?.published_by ?? null,
        };
    });
};

const submitCardErrata = () => {
    if (props.cardErrata) {
        form.post(route('admin.card-errata.update', { cardErrata: props.cardErrata.slug }));
    } else {
        form.post(route('admin.card-errata.store'));
    }
};
</script>

<template>
    <Head title="Card Errata Information" />

    <Card>
        <CardHeader>
            <CardTitle>Card Errata Form</CardTitle>
            <CardDescription>
                Create and Edit Card Errata Information
                <span class="text-destructive" v-if="!props.cardErrata"><br />Make sure you want an entirely NEW card. <br />
                    If you just want to add more errata to an existing card, you need to edit it.</span>
            </CardDescription>
        </CardHeader>
        <CardContent>
            <form @submit.prevent>
                <Tabs default-value="details">
                    <TabsList>
                        <TabsTrigger value="details">Details</TabsTrigger>
                        <TabsTrigger value="entries">Errata</TabsTrigger>
                        <TabsTrigger value="notes">Notes</TabsTrigger>
                    </TabsList>
                    <TabsContent value="details" force-mount class="data-[state=inactive]:hidden">
                        <div class="grid items-center w-full gap-4 pt-4">
                            <div class="flex flex-col space-y-1.5">
                                <Label for="faction">Faction</Label>
                                <div class="flex">
                                    <Select id="faction" v-model="form.faction">
                                        <SelectTrigger class="w-full">
                                            <SelectValue placeholder="Select Faction" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem v-for="faction in props.faction_options" :value="faction.value" :key="faction.value">
                                                {{ faction.name }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <CircleX class="text-destructive my-auto ml-2" v-if="form.faction" @click="form.faction = null" />
                                </div>
                                <InputError :message="form.errors.faction" />
                            </div>
                            <div class="flex flex-col space-y-1.5">
                                <Label for="card_name">Card Effected</Label>
                                <Input id="card_name" type="text" required autofocus :tabindex="1" autocomplete="off" v-model="form.card_name" placeholder="Card Name" />
                                <InputError :message="form.errors.card_name" />
                            </div>
                            <div class="flex flex-col space-y-1.5" v-if="form.existing_image">
                                <Label>Current Card Image</Label>
                                <img :src="form.existing_image" :alt="form.card_name" class="w-75" />
                            </div>
                            <div class="flex flex-col space-y-1.5">
                                <Label for="image">{{ form.existing_image ? 'New ' : '' }}Card Image</Label>
                                <Input id="image" type="file" accept=".jpeg,.jpg,.png,.webp" @input="form.image = $event.target.files[0]" />
                                <InputError :message="form.errors.image" />
                            </div>
                        </div>
                    </TabsContent>
                    <TabsContent value="entries" force-mount class="data-[state=inactive]:hidden">
                        <div class="grid items-center w-full gap-4 pt-4">
                            <Card v-for="(entry, index) in form.entries" :key="index" class="border-2">
                                <CardHeader class="flex flex-row items-center justify-between">
                                    <CardTitle class="text-base">Errata {{ index + 1 }}</CardTitle>
                                    <Button variant="destructive" size="sm" type="button" @click="removeEntry(index)" v-if="form.entries.length > 1">
                                        <Trash2 class="h-4 w-4" />
                                    </Button>
                                </CardHeader>
                                <CardContent>
                                    <div class="grid items-center w-full gap-4">
                                        <div class="flex flex-col space-y-1.5">
                                            <Label :for="`what_changed_${index}`">What Was Changed</Label>
                                            <SimpleRichTextarea :id="`what_changed_${index}`" v-model="entry.what_changed" placeholder="What was changed" />
                                            <InputError :message="form.errors[`entries.${index}.what_changed`]" />
                                        </div>
                                        <div class="flex flex-col space-y-1.5">
                                            <Label :for="`what_it_was_${index}`">What It Was</Label>
                                            <SimpleRichTextarea :id="`what_it_was_${index}`" v-model="entry.what_it_was" placeholder="What it was" />
                                            <InputError :message="form.errors[`entries.${index}.what_it_was`]" />
                                        </div>
                                        <div class="flex flex-col space-y-1.5">
                                            <Label :for="`what_it_is_now_${index}`">What It Is Now</Label>
                                            <SimpleRichTextarea :id="`what_it_is_now_${index}`" v-model="entry.what_it_is_now" placeholder="What it is now" />
                                            <InputError :message="form.errors[`entries.${index}.what_it_is_now`]" />
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                            <Button type="button" variant="outline" @click="addEntry">
                                <Plus class="h-4 w-4 mr-1" /> Add Another Errata
                            </Button>
                            <InputError :message="form.errors.entries" />
                        </div>
                    </TabsContent>
                    <TabsContent value="notes" force-mount class="data-[state=inactive]:hidden">
                        <div class="grid items-center w-full gap-4 pt-4">
                            <div class="flex flex-col space-y-1.5" v-if="(props.cardErrata && props.cardErrata?.published_at) || props.cardErrata?.approval?.change_notes">
                                <Label for="change_notes">Change Notes</Label>
                                <Textarea class="min-h-24" id="change_notes" v-model="form.change_notes" placeholder="What changed in this update" />
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
                <Drawer v-if="hasPermission('view_card_errata')">
                    <DrawerTrigger as-child>
                        <Button class="bg-purple-500 mx-2" @click="fetchPreviewData()">
                            <Eye class="h-4 w-4" /> Preview
                        </Button>
                    </DrawerTrigger>
                    <DrawerContent>
                        <div class="mx-auto w-full mt-2 container overflow-y-auto">
                            <DrawerDescription>
                                <CardErrataView v-if="previewData" v-bind="previewData" />
                            </DrawerDescription>
                            <DrawerFooter>
                                <DrawerClose as-child>
                                    <Button type="button" class="mx-auto w-25" variant="destructive">
                                        Close
                                    </Button>
                                </DrawerClose>
                            </DrawerFooter>
                        </div>
                    </DrawerContent>
                </Drawer>
                <Drawer>
                    <DrawerTrigger>
                        <Button class="bg-green-500">{{ props.cardErrata ? 'Update' : 'Create' }} Card Errata</Button>
                    </DrawerTrigger>
                    <DrawerContent class="max-w-lg mx-auto">
                        <DrawerHeader>
                            <DrawerTitle>{{ props.cardErrata ? 'Update' : 'Create' }} Card Errata</DrawerTitle>
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
                                    <div class="flex items-center mb-4 space-x-2" v-if="hasPermission('approve_card_errata')">
                                        <Switch id="approve-directly" v-model="form.approve_directly" />
                                        <Label for="approve-directly">Approve Directly</Label>
                                    </div>
                                    <div class="flex items-center mb-4 space-x-2" v-if="hasPermission('publish_card_errata')">
                                        <Switch id="publish-directly" v-model="form.publish_directly" />
                                        <Label for="publish-directly">Publish Directly</Label>
                                    </div>
                                </div>
                            </DrawerDescription>
                        </DrawerHeader>
                        <DrawerFooter class="container grid grid-cols-2">
                            <Button @click="submitCardErrata">Submit</Button>
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
