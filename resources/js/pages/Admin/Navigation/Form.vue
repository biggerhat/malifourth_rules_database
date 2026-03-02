<script setup lang='ts'>
import { computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import InputError from '@/components/InputError.vue';
import { LoaderCircle, CircleX } from 'lucide-vue-next';

const props = defineProps({
    navigationItem: {
        type: Object,
        required: false,
        default() {
            return null;
        },
    },
    linkables: {
        type: Object,
        required: false,
        default() {
            return { pages: [], sections: [], indices: [] };
        },
    },
});

const linkableTypeMap: Record<string, string> = {
    'App\\Models\\Page': 'page',
    'App\\Models\\Section': 'section',
    'App\\Models\\Index': 'index',
};

const editingType = props.navigationItem?.linkable_type
    ? (linkableTypeMap[props.navigationItem.linkable_type] ?? null)
    : null;

const editingId = props.navigationItem?.linkable_id != null
    ? String(props.navigationItem.linkable_id)
    : null;

const form = useForm({
    title: props.navigationItem?.title ?? '',
    linkable_type: editingType,
    linkable_id: editingId,
    sort_order: props.navigationItem?.sort_order ?? 0,
    is_active: props.navigationItem?.is_active ?? true,
});

const contentOptions = computed(() => {
    if (form.linkable_type === 'page') return props.linkables.pages;
    if (form.linkable_type === 'section') return props.linkables.sections;
    if (form.linkable_type === 'index') return props.linkables.indices;
    return [];
});

watch(() => form.linkable_type, (newVal, oldVal) => {
    if (oldVal !== undefined && newVal !== oldVal) {
        form.linkable_id = null;
    }
});

const back = () => {
    history.back();
};

const submit = () => {
    if (props.navigationItem) {
        form.post(route('admin.navigation.update', { navigationItem: props.navigationItem.id }));
    } else {
        form.post(route('admin.navigation.store'));
    }
};
</script>

<template>
    <Head title="Navigation Item" />

    <Card>
        <CardHeader>
            <CardTitle>Navigation Item</CardTitle>
            <CardDescription>{{ props.navigationItem ? 'Edit' : 'Create' }} a navigation item</CardDescription>
        </CardHeader>
        <CardContent>
            <form @submit.prevent="submit">
                <div class="grid items-center w-full gap-4">
                    <div class="flex flex-col space-y-1.5">
                        <Label for="title">Title</Label>
                        <Input id="title" type="text" required autofocus v-model="form.title" placeholder="Navigation Title" />
                        <InputError :message="form.errors.title" />
                    </div>

                    <div class="flex flex-col space-y-1.5">
                        <Label for="linkable_type">Content Type</Label>
                        <div class="flex">
                            <Select id="linkable_type" v-model="form.linkable_type">
                                <SelectTrigger class="w-full">
                                    <SelectValue placeholder="Select Content Type" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="page">Page</SelectItem>
                                    <SelectItem value="section">Section</SelectItem>
                                    <SelectItem value="index">Index</SelectItem>
                                </SelectContent>
                            </Select>
                            <CircleX class="text-destructive my-auto ml-2 cursor-pointer" v-if="form.linkable_type" @click="form.linkable_type = null; form.linkable_id = null" />
                        </div>
                        <InputError :message="form.errors.linkable_type" />
                    </div>

                    <div class="flex flex-col space-y-1.5" v-if="form.linkable_type">
                        <Label for="linkable_id">Content</Label>
                        <div class="flex">
                            <Select id="linkable_id" v-model="form.linkable_id" :key="form.linkable_type">
                                <SelectTrigger class="w-full">
                                    <SelectValue placeholder="Select Content" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="item in contentOptions" :value="String(item.id)" :key="item.id">
                                        {{ item.title }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <CircleX class="text-destructive my-auto ml-2 cursor-pointer" v-if="form.linkable_id" @click="form.linkable_id = null" />
                        </div>
                        <InputError :message="form.errors.linkable_id" />
                    </div>

                    <div class="flex flex-col space-y-1.5">
                        <Label for="sort_order">Sort Order</Label>
                        <Input id="sort_order" type="number" v-model="form.sort_order" />
                        <InputError :message="form.errors.sort_order" />
                    </div>

                    <div class="flex items-center space-x-2">
                        <Switch id="is_active" v-model="form.is_active" />
                        <Label for="is_active">Active</Label>
                    </div>

                    <div class="flex gap-2 mt-2">
                        <Button type="submit" class="flex-1" :disabled="form.processing">
                            <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                            <span v-if="props.navigationItem">Update Navigation Item</span>
                            <span v-else>Create Navigation Item</span>
                        </Button>
                        <Button type="button" variant="destructive" @click="back()">
                            Cancel
                        </Button>
                    </div>
                </div>
            </form>
        </CardContent>
    </Card>
</template>
