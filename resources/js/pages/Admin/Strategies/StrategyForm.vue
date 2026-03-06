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
});

const submitStrategy = () => {
    if (props.strategy) {
        form.post(route('admin.strategies.update', {strategy: props.strategy.slug}));
    } else {
        form.post(route('admin.strategies.store'));
    }
};
</script>

<template>
    <Head title="Strategy Information" />

    <Card>
        <CardHeader>
            <CardTitle>{{ props.strategy ? 'Edit' : 'New' }} Strategy</CardTitle>
            <CardDescription>
                <span class="text-destructive" v-if="!props.strategy">
                    You are creating a new Strategy item. To update an existing one, find it in the list and click Edit.
                </span>
                <span v-else>Editing: {{ props.strategy.title }}</span>
            </CardDescription>
        </CardHeader>
        <CardContent>
            <form @submit.prevent class="space-y-6">
                <div class="space-y-4">
                    <div class="space-y-2">
                        <Label for="title">Title</Label>
                        <Input id="title" type="text" required autofocus autocomplete="title" v-model="form.title" placeholder="Strategy Title" />
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
                        <Label for="suit">Suit</Label>
                        <div class="flex gap-2">
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
                            <Button variant="ghost" size="icon" v-if="form.suit" @click="form.suit = null">
                                <CircleX class="h-4 w-4 text-destructive" />
                            </Button>
                        </div>
                        <InputError :message="form.errors.suit" />
                    </div>
                </div>

                <Separator />

                <div class="space-y-4">
                    <TipTapEditor v-model="form.setup" label="Setup" />
                    <InputError :message="form.errors.setup" />
                </div>
                <div class="space-y-4">
                    <TipTapEditor v-model="form.rules" label="Rules" />
                    <InputError :message="form.errors.rules" />
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
                    <div class="space-y-2" v-if="props.strategy?.front_image">
                        <Label>Current Front Image</Label>
                        <img :src="props.strategy.front_image" :alt="props.strategy.title" class="w-75" />
                    </div>
                    <div class="space-y-2">
                        <Label for="front_image">{{ props.strategy?.front_image ? 'New ' : '' }}Front Image</Label>
                        <Input id="front_image" type="file" accept=".jpeg,.jpg,.png,.webp" @input="form.front_image = $event.target.files[0]" />
                        <InputError :message="form.errors.front_image" />
                    </div>
                    <div class="space-y-2" v-if="props.strategy?.back_image">
                        <Label>Current Back Image</Label>
                        <img :src="props.strategy.back_image" :alt="props.strategy.title" class="w-75" />
                    </div>
                    <div class="space-y-2">
                        <Label for="back_image">{{ props.strategy?.back_image ? 'New ' : '' }}Back Image</Label>
                        <Input id="back_image" type="file" accept=".jpeg,.jpg,.png,.webp" @input="form.back_image = $event.target.files[0]" />
                        <InputError :message="form.errors.back_image" />
                    </div>
                    <div class="space-y-2" v-if="props.strategy?.combination_image">
                        <Label>Current Combination Image</Label>
                        <img :src="props.strategy.combination_image" :alt="props.strategy.title" class="w-75" />
                    </div>
                    <div class="space-y-2">
                        <Label for="combination_image">{{ props.strategy?.combination_image ? 'New ' : '' }}Combination Image</Label>
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
                        <div class="space-y-2" v-if="(props.strategy && props.strategy?.published_at) || props.strategy?.approval?.change_notes">
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
                    <Button>{{ props.strategy ? 'Update' : 'Create' }} Strategy</Button>
                </DrawerTrigger>
                <DrawerContent class="max-w-lg mx-auto">
                    <DrawerHeader>
                        <DrawerTitle>{{ props.strategy ? 'Update' : 'Create' }} Strategy</DrawerTitle>
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
                        <div class="flex items-center justify-between" v-if="hasPermission('approve_strategy')">
                            <Label for="approve-directly">Approve Directly</Label>
                            <Switch id="approve-directly" v-model="form.approve_directly" />
                        </div>
                        <div class="flex items-center justify-between" v-if="hasPermission('publish_strategy')">
                            <Label for="publish-directly">Publish Directly</Label>
                            <Switch id="publish-directly" v-model="form.publish_directly" />
                        </div>
                    </div>
                    <DrawerFooter class="grid grid-cols-2 gap-2">
                        <DrawerClose as-child>
                            <Button variant="outline">Cancel</Button>
                        </DrawerClose>
                        <Button @click="submitStrategy">Submit</Button>
                    </DrawerFooter>
                </DrawerContent>
            </Drawer>
        </CardFooter>
    </Card>
</template>
