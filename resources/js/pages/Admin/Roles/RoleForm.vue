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
import { Checkbox } from "@/components/ui/checkbox";

const props = defineProps({
    role: {
        type: [Object, Array],
        required: false,
        default() {
            return null;
        }
    },
    checked_permissions: {
        type: [Object, Array],
        required: false,
        default() {
            return [];
        }
    },
    permissions: {
        type: [Object, Array],
        required: false,
        default() {
            return {};
        }
    }
});

const checkedPermissions = ref([]);

const form = useForm({
    name: '',
    permissions: [],
});

const allPermissionsOn = ref(false);
const toggleAllPermissionsOn = () => {
    props.permissions.forEach((permission) => {
        if (!form.permissions.includes(permission)) {
            togglePermission(permission);
        }
    })
    allPermissionsOn.value = true;
}

const toggleAllPermissionsOff = () => {
    form.permissions = [];
    allPermissionsOn.value = false;
}

const togglePermission = (permission) => {
    for(let i = 0; i < form.permissions.length; i++) {
        if (form.permissions[i] === permission) {
            form.permissions.splice(i, 1);
            return;
        }
    }

    form.permissions.push(permission);
};

onMounted(() => {
    form.name = props.role?.name ?? null;
    if (props.role) {
        props.role.permissions.forEach((permission) => {
            togglePermission(permission.name);
        });
    }
});

const submit = () => {
    if (props.role) {
        form.post(route('admin.roles.update', {role: props.role.id}));
    } else {
        form.post(route('admin.roles.store', {}));
    }
}

</script>

<template>
    <Head title="Role Information" />

    <Card>
        <CardHeader>
            <CardTitle>Role Form</CardTitle>
            <CardDescription>Create and Edit Role Information</CardDescription>
        </CardHeader>
        <CardContent>
            <form @submit.prevent="submit">
                <div class="grid items-center w-full gap-4">
                    <div class="flex flex-col space-y-1.5">
                        <Label for="name">Role</Label>
                        <Input id="name" type="text" required autofocus :tabindex="1" autocomplete="name" v-model="form.name" placeholder="Role" />
                        <InputError :message="form.errors.name" />
                    </div>
                    <div class="grid md:grid-cols-4 w-full">
                        <div v-for="permission in props.permissions" :key="permission.value" class="flex items-center space-x-2 space-y-2">
                            <Checkbox
                                :id="permission.value"
                                class="inline-block"
                                :default-value="props.checked_permissions.includes(permission.value)"
                                @update:modelValue="togglePermission(permission.value)"
                            />
                            <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70 my-auto" :for="permission.value">
                                {{ permission.name }}
                            </label>
                        </div>
                    </div>

                    <Button type="submit" class="mt-2 w-full" tabindex="5" :disabled="form.processing">
                        <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                        <span v-if="props.role">Update Role</span>
                        <span v-else>Create Role</span>
                    </Button>
                </div>
            </form>
        </CardContent>
    </Card>
</template>
