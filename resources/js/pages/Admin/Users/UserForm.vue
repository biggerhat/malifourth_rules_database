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
import { cn } from '@/lib/utils'
import { Combobox, ComboboxAnchor, ComboboxEmpty, ComboboxGroup, ComboboxInput, ComboboxItem, ComboboxItemIndicator, ComboboxList, ComboboxTrigger } from '@/components/ui/combobox';
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import InputError from "@/components/InputError.vue";
import {LoaderCircle, ChevronsUpDown, Check, CircleX, Search } from "lucide-vue-next";

const props = defineProps({
    user: {
        type: [Object, Array],
        required: false,
        default() {
            return null;
        }
    },
    current_role: {
        type: [String],
        required: false,
        default() {
            return null;
        }
    },
    roles: {
        type: [Object, Array],
        required: false,
        default() {
            return {};
        }
    },
});

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: null,
});

onMounted(() => {
    form.name = props.user?.name ?? null;
    form.email = props.user?.email ?? null;
    form.role = props.current_role ?? null;
});

const submit = () => {
    if (props.user) {
        form.post(route('admin.users.update', {user: props.user.id}));
    } else {
        form.post(route('admin.users.store', {}));
    }
}

</script>

<template>
    <Head title="User Information" />

    <Card>
        <CardHeader>
            <CardTitle>User Form</CardTitle>
            <CardDescription>Create and Edit User Information</CardDescription>
        </CardHeader>
        <CardContent>
            <form @submit.prevent="submit">
                <div class="grid items-center w-full gap-4">
                    <div class="flex flex-col space-y-1.5">
                        <Label for="name">Username</Label>
                        <Input id="name" type="text" required autofocus :tabindex="1" autocomplete="name" v-model="form.name" placeholder="Username" />
                        <InputError :message="form.errors.name" />
                    </div>
                    <div class="flex flex-col space-y-1.5">
                        <Label for="email">Email address</Label>
                        <Input id="email" type="email" required :tabindex="2" autocomplete="email" v-model="form.email" placeholder="email@example.com" />
                        <InputError :message="form.errors.email" />
                    </div>
                    <div v-if="!props.user" class="flex flex-col space-y-1.5">
                        <Label for="password">Password</Label>
                        <Input
                            id="password"
                            type="password"
                            required
                            :tabindex="3"
                            autocomplete="new-password"
                            v-model="form.password"
                            placeholder="Password"
                        />
                        <InputError :message="form.errors.password" />
                    </div>
                    <div v-if="!props.user" class="flex flex-col space-y-1.5">
                        <Label for="password_confirmation">Confirm password</Label>
                        <Input
                            id="password_confirmation"
                            type="password"
                            required
                            :tabindex="4"
                            autocomplete="new-password"
                            v-model="form.password_confirmation"
                            placeholder="Confirm password"
                        />
                        <InputError :message="form.errors.password_confirmation" />
                    </div>
                    <div class="flex space-y-1.5 my-auto">
                        <Combobox v-model="form.role" by="label">
                            <ComboboxAnchor as-child>
                                <ComboboxTrigger as-child>
                                    <Button variant="outline" class="justify-between">
                                        {{ form.role?.name ?? 'Select Role' }}
                                        <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                                    </Button>
                                </ComboboxTrigger>
                            </ComboboxAnchor>

                            <ComboboxList class="max-h-80 overflow-y-auto">
                                <div class="relative w-full items-center">
                                    <ComboboxInput class="pl-9 focus-visible:ring-0 border-0 border-b rounded-none h-10" placeholder="Select Role..." />
                                    <span class="absolute start-0 inset-y-0 flex items-center justify-center px-3">
                                    <Search class="size-4 text-muted-foreground" />
                                  </span>
                                </div>

                                <ComboboxEmpty>
                                    No Role Found.
                                </ComboboxEmpty>

                                <ComboboxGroup>
                                    <ComboboxItem
                                        v-for="role in props.roles"
                                        :key="role.id"
                                        :value="role"
                                    >
                                        {{ role.name }}

                                        <Check v-if="form.role && form.role?.id === role.id" :class="cn('ml-auto h-4 w-4')" />
                                    </ComboboxItem>
                                </ComboboxGroup>
                            </ComboboxList>
                        </Combobox>
                        <CircleX class="text-destructive my-auto ml-2" v-if="form.role" @click="form.role = null" />
                        <InputError :message="form.errors.role" />
                    </div>

                    <Button type="submit" class="mt-2 w-full" tabindex="5" :disabled="form.processing">
                        <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                        <span v-if="props.user">Update User</span>
                        <span v-else>Create User</span>
                    </Button>
                </div>
            </form>
        </CardContent>
    </Card>
</template>
