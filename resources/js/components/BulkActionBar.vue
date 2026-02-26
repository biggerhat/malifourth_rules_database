<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { hasPermission } from '@/composables/hasPermission';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogTrigger,
} from '@/components/ui/alert-dialog';

const props = defineProps<{
    selectedCount: number
    selectedIds: number[]
    modelName: string
    bulkApproveRoute?: string
    bulkPublishRoute?: string
    bulkDeleteRoute?: string
}>();

const emit = defineEmits<{
    clear: []
}>();

const processing = ref(false);

const bulkApprove = () => {
    processing.value = true;
    router.post(props.bulkApproveRoute!, { ids: props.selectedIds }, {
        onFinish: () => {
            processing.value = false;
            emit('clear');
        },
    });
};

const bulkPublish = () => {
    processing.value = true;
    router.post(props.bulkPublishRoute!, { ids: props.selectedIds }, {
        onFinish: () => {
            processing.value = false;
            emit('clear');
        },
    });
};

const bulkDelete = () => {
    processing.value = true;
    router.post(props.bulkDeleteRoute!, { ids: props.selectedIds }, {
        onFinish: () => {
            processing.value = false;
            emit('clear');
        },
    });
};
</script>

<template>
    <div class="flex items-center gap-2 rounded-md border bg-muted/50 px-4 py-2">
        <span class="text-sm font-medium">{{ selectedCount }} item{{ selectedCount === 1 ? '' : 's' }} selected</span>
        <div class="ml-auto flex items-center gap-2">
            <Button
                v-if="bulkApproveRoute && hasPermission('approve_' + modelName)"
                size="sm"
                variant="outline"
                :disabled="processing"
                @click="bulkApprove"
            >
                Approve Selected
            </Button>
            <Button
                v-if="bulkPublishRoute && hasPermission('publish_' + modelName)"
                size="sm"
                variant="outline"
                :disabled="processing"
                @click="bulkPublish"
            >
                Publish Selected
            </Button>
            <AlertDialog v-if="bulkDeleteRoute && hasPermission('delete_' + modelName)">
                <AlertDialogTrigger as-child>
                    <Button size="sm" variant="destructive" :disabled="processing">
                        Delete Selected
                    </Button>
                </AlertDialogTrigger>
                <AlertDialogContent>
                    <AlertDialogHeader>
                        <AlertDialogTitle>Delete {{ selectedCount }} item{{ selectedCount === 1 ? '' : 's' }}?</AlertDialogTitle>
                        <AlertDialogDescription>
                            This will delete all selected unpublished items. This action cannot be undone.
                        </AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter>
                        <AlertDialogCancel>Cancel</AlertDialogCancel>
                        <AlertDialogAction @click="bulkDelete">Delete</AlertDialogAction>
                    </AlertDialogFooter>
                </AlertDialogContent>
            </AlertDialog>
            <Button size="sm" variant="ghost" @click="emit('clear')">
                Clear
            </Button>
        </div>
    </div>
</template>
