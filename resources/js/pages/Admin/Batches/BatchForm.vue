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
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import InputError from "@/components/InputError.vue";
import {LoaderCircle} from "lucide-vue-next";
import { Textarea } from '@/components/ui/textarea'
import { Checkbox } from "@/components/ui/checkbox";
import RichTextEditor from "@/components/RichTextEditor.vue";

const props = defineProps({
    batch: {
        type: [Object, Array],
        required: false,
        default() {
            return null;
        }
    },
});

const form = useForm({
    title: '',
    release_notes: '',
    internal_notes: '',
});

onMounted(() => {
    form.title = props.batch?.title ?? null;
    form.release_notes = props.batch?.release_notes ?? null;
    form.internal_notes = props.batch?.internal_notes ?? null;
});

const submit = () => {
    if (props.batch) {
        form.post(route('admin.batches.update', {batch: props.batch.id}));
    } else {
        form.post(route('admin.batches.store', {}));
    }
}

</script>

<template>
    <Head title="Batch Information" />

    <Card>
        <CardHeader>
            <CardTitle>Batch Form</CardTitle>
            <CardDescription>Create and Edit Batch Information</CardDescription>
        </CardHeader>
        <CardContent>
            <form @submit.prevent="submit">
                <div class="grid items-center w-full gap-4">
                    <div class="flex flex-col space-y-1.5">
                        <Label for="title">Batch</Label>
                        <Input id="title" type="text" required autofocus :tabindex="1" autocomplete="title" v-model="form.title" placeholder="Batch Name" />
                        <InputError :message="form.errors.title" />
                    </div>
                    <div class="flex flex-col space-y-1.5">
                        <RichTextEditor id="release_notes" placeholder="Add Release Notes" label="Release Notes" v-model="form.release_notes" />
                        <InputError :message="form.errors.release_notes" />
                    </div>
                    <div class="flex flex-col space-y-1.5">
                        <Label for="internal_notes">Internal Notes</Label>
                        <Textarea class="min-h-48" id="internal_notes" v-model="form.internal_notes" placeholder="Add Internal Notes" />
                        <InputError :message="form.errors.internal_notes" />
                    </div>

                    <Button type="submit" class="mt-2 w-full" tabindex="5" :disabled="form.processing">
                        <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                        <span v-if="props.batch">Update Batch</span>
                        <span v-else>Create Batch</span>
                    </Button>
                </div>
            </form>
        </CardContent>
    </Card>
</template>
