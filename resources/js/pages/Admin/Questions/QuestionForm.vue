<script setup lang='ts'>
import { ref, onMounted } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
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
import {LoaderCircle, CircleX, CheckCheckIcon, ChevronsUpDown, Search, Check, Component, Eye} from "lucide-vue-next";
import { Textarea } from '@/components/ui/textarea'
import { Checkbox } from "@/components/ui/checkbox";
import RichTextEditor from "@/components/RichTextEditor.vue";
import {hasPermission} from "@/composables/hasPermission";
import {
    Dialog, DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter, DialogHeader,
    DialogTitle,
    DialogTrigger
} from "@/components/ui/dialog";
import {Tooltip, TooltipContent, TooltipProvider, TooltipTrigger} from "@/components/ui/tooltip";
import {cn} from "@/lib/utils";
import {
    Combobox,
    ComboboxAnchor,
    ComboboxEmpty,
    ComboboxGroup, ComboboxInput,
    ComboboxItem,
    ComboboxList, ComboboxTrigger
} from "@/components/ui/combobox";
import {
    Drawer, DrawerClose, DrawerContent,
    DrawerDescription,
    DrawerFooter,
    DrawerHeader,
    DrawerTitle,
    DrawerTrigger
} from "@/components/ui/drawer";
import {Switch} from "@/components/ui/switch";
import axios from "axios";
import QuestionView from "@/pages/Rules/QuestionView.vue";
import DragDropEditor from "@/components/DragDropEditor.vue";
import DraggableContent from "@/components/DraggableContent.vue";

const props = defineProps({
    question: {
        type: [Object, Array],
        required: false,
        default() {
            return null;
        }
    },
    question_sections: {
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
    }
});

const form = useForm({
    question: '',
    answer: '',
    section: '',
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
    form.section = props.question?.section.value ?? null;
    form.question = replaceBrWithNewline(props.section?.question ?? '');
    form.answer = replaceBrWithNewline(props.section?.answer ?? '');
    form.internal_notes = props.question?.internal_notes ?? '';
    form.change_notes = props.question?.published_at ? '' : props.question?.approval?.change_notes ?? '';
    form.batch_id = props.question?.published_at ? null : props.question?.batch_id ?? null;
});

const submitQuestion = () => {
    if (props.question) {
        form.post(route('admin.questions.update', {question: props.question.id}));
    } else {
        form.post(route('admin.questions.store'));
    }
};

const viewData = ref(null);

const fetchViewData = () => {
    axios.post(route('admin.questions.preview'), { section: form.section, question: form.question, answer: form.answer, change_notes: form.change_notes }).then((response) => {
        viewData.value = response.data;
        viewData.value = JSON.parse(JSON.stringify(response.data));
    });
};

onMounted(() => {
    fetchViewData();
});

const questionUpdate = (newOrder) => {
    form.question = newOrder;
};

const questionNewContent = (content) => {
    form.question = content;
    fetchViewData();
}

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

const replaceBrWithNewline = (text) => {
    return text.replace(/<br\s*\/?>/gi, '\n');
};


</script>

<template>
    <Head title="FAQ Information" />

    <Card>
        <CardHeader>
            <CardTitle>FAQ Form</CardTitle>
            <CardDescription>
                Create and Edit FAQ Information
                <span class="text-destructive" v-if="!props.question"><br />Make sure you want an entirely NEW FAQ. <br />
                    If you just want to update or change an existing FAQ, you need to edit it.</span>
            </CardDescription>
        </CardHeader>
        <CardContent>
            <form @submit.prevent>
                <div class="grid items-center w-full gap-4">
                    <div class="flex flex-col space-y-1.5 my-auto">
                        <Label for="section">Section</Label>
                        <div class="flex">
                            <Select id="section" v-model="form.section">
                                <SelectTrigger class="w-full">
                                    <SelectValue placeholder="Select Section" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="section in props.question_sections" :value="section.value" :key="section.value">
                                        {{ section.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <CircleX class="text-destructive my-auto ml-2" v-if="form.section" @click="form.section = null" />
                        </div>
                        <InputError :message="form.errors.section" />
                    </div>
                    <div class="flex flex-col space-y-1.5">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-1">
                            <div>
                                <div class="flex flex-col space-y-1.5">
                                    <DraggableContent
                                        v-if="viewData"
                                        @update:content-order="questionUpdate"
                                        @update:new-content="questionNewContent"
                                        :content="viewData.question ?? []"
                                        label="Question"
                                        :indices="props.indices"
                                        :sections="props.sections"
                                        :pages="props.pages"
                                        :key="viewData.question"
                                    />
                                    <InputError :message="form.errors.question" />
                                </div>
                            </div>
                            <div>
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
                        </div>
                    </div>
                    <div class="flex flex-col space-y-1.5" v-if="(props.question && props.question?.published_at) || props.question?.approval?.change_notes">
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
            </form>
        </CardContent>
        <CardFooter>
            <div class="flex ml-auto my-auto">
                <Drawer v-if="hasPermission('view_question')">
                    <DrawerTrigger as-child>
                        <Button class="bg-purple-500 mx-2" @click="fetchViewData()">
                            <Eye class="h-4 w-4" /> Preview
                        </Button>
                    </DrawerTrigger>
                    <DrawerContent>
                        <div class="mx-auto w-full mt-2 container overflow-y-auto">
                            <DrawerDescription>
                                <component
                                    v-if="viewData"
                                    :is="QuestionView"
                                    v-bind="viewData"
                                />
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
                        <Button class="bg-green-500">{{ props.question ? 'Update' : 'Create' }} FAQ</Button>
                    </DrawerTrigger>
                    <DrawerContent class="max-w-lg mx-auto">
                        <DrawerHeader>
                            <DrawerTitle>{{ props.question ? 'Update' : 'Create' }} FAQ</DrawerTitle>
                            <DrawerDescription>
                                <div class="mx-auto max-w-lg mt-2 container overflow-y-auto">
                                    <div class="flex mb-4">
                                        <Select id="type" v-model="form.batch_id">
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
                                    <div class="flex items-center mb-4 space-x-2" v-if="hasPermission('approve_question')">
                                        <Switch id="approve-directly" v-model="form.approve_directly" />
                                        <Label for="approve-directly">Approve Directly</Label>
                                    </div>
                                    <div class="flex items-center mb-4 space-x-2" v-if="hasPermission('publish_question')">
                                        <Switch id="publish-directly" v-model="form.publish_directly" />
                                        <Label for="publish-directly">Publish Directly</Label>
                                    </div>
                                </div>
                            </DrawerDescription>
                        </DrawerHeader>
                        <DrawerFooter class="container grid grid-cols-2">
                            <Button @click="submitQuestion">Submit</Button>
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
