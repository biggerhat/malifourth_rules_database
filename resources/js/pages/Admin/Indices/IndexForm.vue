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
import {LoaderCircle, CircleX } from "lucide-vue-next";
import { Textarea } from '@/components/ui/textarea'
import { Checkbox } from "@/components/ui/checkbox";
import RichTextEditor from "@/components/RichTextEditor.vue";

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
    }
});

const form = useForm({
    title: '',
    type: null,
    image: '',
    content: '',
    internal_notes: '',
    batch_id: '',
});

onMounted(() => {
    form.title = props.index?.title ?? null;
    form.release_notes = props.index?.release_notes ?? '';
    form.internal_notes = props.index?.internal_notes ?? '';
});

const submit = () => {
    if (props.index) {
        form.post(route('admin.indices.update', {index: props.index.slug}));
    } else {
        form.post(route('admin.indices.store', {}));
    }
}

</script>

<template>
    <Head title="Index Information" />

    <Card>
        <CardHeader>
            <CardTitle>Index Form</CardTitle>
            <CardDescription>Create and Edit Index Information</CardDescription>
        </CardHeader>
        <CardContent>
            <form @submit.prevent="submit">
                <div class="grid items-center w-full gap-4">
                    <div class="flex flex-col space-y-1.5">
                        <Label for="title">Index</Label>
                        <Input id="title" type="text" required autofocus :tabindex="1" autocomplete="title" v-model="form.title" placeholder="Index Name" />
                        <InputError :message="form.errors.title" />
                    </div>
                    <div class="flex flex-col space-y-1.5">
                        <Label for="type">Index Type</Label>
                        <div class="flex">
                            <Select id="type" v-model="form.type">
                                <SelectTrigger class="w-full">
                                    <SelectValue placeholder="Select Index Type" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="type in props.index_types" :value="type.value" :key="type.value">
                                        {{ type.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <CircleX class="text-destructive my-auto ml-2" v-if="form.type" @click="form.type = null" />
                        </div>
                    </div>
                    <div class="flex flex-col space-y-1.5" v-if="form.type === 'image'">
                        <Label for="image">Image</Label>
                        <Input id="image" type="file" accept=".jpeg, .jpg, .png" @input="form.image = $event.target.files[0]" />
                    </div>
                    <div class="flex flex-col space-y-1.5" v-if="form.type === 'text'">
                        <RichTextEditor placeholder="Add Index Content" label="Content" v-model="form.content" />
                        <InputError :message="form.errors.content" />
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
            <Button class="bg-green-500 ml-auto my-auto">
                Add To Existing Batch
            </Button>
            <Button class="bg-blue-500 my-auto mx-2">
                Publish Directly
            </Button>
<!--            <Button type="submit" class="my-auto mx-2" :disabled="form.processing">-->
<!--                <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />-->
<!--                <span v-if="props.index">Update Index</span>-->
<!--                <span v-else>Create Index</span>-->
<!--            </Button>-->
        </CardFooter>
    </Card>
</template>
