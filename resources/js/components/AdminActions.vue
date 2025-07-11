<script setup lang="ts">
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { Pencil, Trash2, CheckCheckIcon } from "lucide-vue-next";
import { Button } from "@/components/ui/button";
import { hasPermission } from "@/composables/hasPermission";
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger
} from '@/components/ui/tooltip'

import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogClose,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog'
import {Label} from "@/components/ui/label";
import {Textarea} from "@/components/ui/textarea";

const props = defineProps({
    name: {
        type: String,
        required: true,
        default() {
            return '';
        }
    },
    modelName: {
        type: String,
        required: false,
        default() {
            return '';
        }
    },
    editRoute: {
        type: String,
        required: false,
        default() {
            return null;
        }
    },
    deleteRoute: {
        type: String,
        required: false,
        default() {
            return null;
        }
    },
    approvalRoute: {
        type: String,
        required: false,
        default() {
            return null;
        }
    },
    publishRoute: {
        type: String,
        required: false,
        default() {
            return null;
        }
    }
})

const permissionName = computed(() => {
    const name = props.modelName;

    return name.replace(/([a-z])([A-Z])/g, '$1_$2')
        .replace(/\s+/g, '_')
        .replace(/-+/g, '_')
        .toLowerCase();
})

const submitData = ref({
    approved: true,
    internal_notes: null,
});

</script>

<template>
    <TooltipProvider>
        <Tooltip>
            <TooltipTrigger>
                <Button class="dark:bg-[#449e48] mx-2" v-if="props.editRoute && hasPermission('edit_' + permissionName)" @click="router.get(props.editRoute)">
                    <Pencil class="h-4 w-4" />
                </Button>
            </TooltipTrigger>
            <TooltipContent>
                <p>Edit {{ props.name }}</p>
            </TooltipContent>
        </Tooltip>
    </TooltipProvider>

    <Dialog v-if="props.deleteRoute && hasPermission('delete_' + permissionName)">
        <DialogTrigger>
            <TooltipProvider>
                <Tooltip>
                    <TooltipTrigger>
                        <Button class="bg-destructive mx-2" v-if="props.deleteRoute">
                            <Trash2 class="h-4 w-4" />
                        </Button>
                    </TooltipTrigger>
                    <TooltipContent>
                        <p>Delete {{ props.name }}</p>
                    </TooltipContent>
                </Tooltip>
            </TooltipProvider>
        </DialogTrigger>
        <DialogContent>
            <DialogHeader>
                <DialogTitle>
                    Delete {{ props.name }}
                </DialogTitle>
                <DialogDescription>
                    Are you sure you want to delete {{ props.name }}?
                </DialogDescription>
            </DialogHeader>

            <DialogFooter class="sm:justify-start">
                <DialogClose as-child>
                    <Button class="bg-destructive" v-if="props.deleteRoute" @click="router.post(props.deleteRoute)">
                        Delete {{ props.name }}
                    </Button>
                </DialogClose>
            </DialogFooter>
        </DialogContent>
    </Dialog>

    <Dialog v-if="props.approvalRoute && hasPermission('approve_' + permissionName)">
        <DialogTrigger>
            <TooltipProvider>
                <Tooltip>
                    <TooltipTrigger>
                        <Button class="bg-blue-500 mx-2">
                            <CheckCheckIcon class="h-4 w-4" />
                        </Button>
                    </TooltipTrigger>
                    <TooltipContent>
                        <p>Approve {{ props.name }}</p>
                    </TooltipContent>
                </Tooltip>
            </TooltipProvider>
        </DialogTrigger>
        <DialogContent>
            <DialogHeader>
                <DialogTitle>
                    Approve {{ props.name }}
                </DialogTitle>
                <DialogDescription>
                    <Textarea class="min-h-48" id="internal_notes" v-model="submitData.internal_notes" placeholder="Add Any Internal Notes (Optional)" />
                </DialogDescription>
            </DialogHeader>

            <DialogFooter class="sm:justify-start">
                <DialogClose as-child>
                    <Button class="bg-blue-500" v-if="props.approvalRoute" @click="router.post(props.approvalRoute, submitData)">
                        <CheckCheckIcon class="h-4 w-4" />Approve {{ props.name }}
                    </Button>
                </DialogClose>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
