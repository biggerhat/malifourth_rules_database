<script setup lang="ts">
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import {Pencil, Trash2, CheckCheckIcon, Send, Eye, Component, Info} from "lucide-vue-next";
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
import axios from 'axios';
import {
    Drawer,
    DrawerClose,
    DrawerContent,
    DrawerDescription,
    DrawerFooter, DrawerHeader,
    DrawerTitle,
    DrawerTrigger
} from "@/components/ui/drawer";

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
    },
    viewRoute: {
        type: String,
        required: false,
        default() {
            return null;
        }
    },
    viewComponent: {
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
});

const viewData = ref(null);

const fetchViewData = () => {
    axios.get(props.viewRoute).then((response) => {
        viewData.value = response.data;
    });
}

const submitData = ref({
    approved: true,
    internal_notes: null,
});

</script>

<template>
    <Drawer v-if="props.viewRoute && hasPermission('view_' + permissionName)">
        <DrawerTrigger as-child>
            <Button class="bg-purple-500 mx-2" v-if="props.viewRoute" @click="fetchViewData()">
                <Eye class="h-4 w-4" />
            </Button>
        </DrawerTrigger>
        <DrawerContent>
            <div class="mx-auto w-full mt-2 container overflow-y-auto">
                <DrawerDescription>
                    <component
                        v-if="viewData"
                        :is="viewComponent"
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

    <TooltipProvider v-if="props.editRoute && hasPermission('edit_' + permissionName)">
        <Tooltip>
            <TooltipTrigger>
                <Button class="dark:bg-[#449e48] mx-2" @click="router.get(props.editRoute)">
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
                    <Button class="bg-destructive" :disabled="!props.deleteRoute" @click="router.post(props.deleteRoute)">
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
                    <Button class="bg-blue-500" :disabled="!props.approvalRoute" @click="router.post(props.approvalRoute, submitData)">
                        <CheckCheckIcon class="h-4 w-4" />Approve {{ props.name }}
                    </Button>
                </DialogClose>
            </DialogFooter>
        </DialogContent>
    </Dialog>

    <Dialog v-if="props.publishRoute && hasPermission('publish_' + permissionName)">
        <DialogTrigger>
            <TooltipProvider>
                <Tooltip>
                    <TooltipTrigger>
                        <Button class="bg-orange-500 mx-2">
                            <Send class="h-4 w-4" />
                        </Button>
                    </TooltipTrigger>
                    <TooltipContent>
                        <p>Publish {{ props.name }}</p>
                    </TooltipContent>
                </Tooltip>
            </TooltipProvider>
        </DialogTrigger>
        <DialogContent>
            <DialogHeader>
                <DialogTitle>
                    Publish {{ props.name }}
                </DialogTitle>
                <DialogDescription>
                    This Action Will Publish This Object And Make It Publicly Viewable. <br />
                    <span class="text-red-500" v-if="permissionName === 'batch'">If This Is A Batch, All Children Will Also Be Published (If They Are Approved).</span>
                    <span class="text-red-500" v-else>If this object is part of a batch, it will be taken out and published individually.</span>
                </DialogDescription>
            </DialogHeader>

            <DialogFooter class="sm:justify-start">
                <DialogClose as-child>
                    <Button class="bg-orange-500" :disabled="!props.publishRoute" @click="router.post(props.publishRoute, submitData)">
                        <Send class="h-4 w-4" />Publish {{ props.name }}
                    </Button>
                </DialogClose>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
