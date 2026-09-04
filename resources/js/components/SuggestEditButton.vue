<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Textarea } from '@/components/ui/textarea';
import axios from 'axios';
import { MessageSquarePlus } from 'lucide-vue-next';
import { ref } from 'vue';
import { toast } from 'vue-sonner';

const props = defineProps<{
    type: string;
    id: number;
}>();

const open = ref(false);
const body = ref('');
const pending = ref(false);
const error = ref<string | null>(null);

async function submit() {
    if (pending.value || body.value.trim().length < 5) {
        error.value = 'Please describe your suggestion in at least 5 characters.';
        return;
    }

    pending.value = true;
    error.value = null;

    try {
        const { data } = await axios.post(route('suggestions.store'), {
            type: props.type,
            id: props.id,
            body: body.value,
        });
        toast(data.message ?? 'Thanks — your suggestion has been sent to the editors.');
        body.value = '';
        open.value = false;
    } catch (e: any) {
        error.value = e?.response?.data?.message ?? 'Something went wrong. Please try again.';
    } finally {
        pending.value = false;
    }
}
</script>

<template>
    <Dialog v-model:open="open">
        <DialogTrigger as-child>
            <Button variant="outline" size="sm" class="print:hidden gap-1.5">
                <MessageSquarePlus class="size-4" />
                Suggest an edit
            </Button>
        </DialogTrigger>
        <DialogContent>
            <DialogHeader>
                <DialogTitle>Suggest an edit</DialogTitle>
                <DialogDescription>
                    Spot a typo or a rules question? Let the editors know — this doesn't change the page directly,
                    it goes to their review queue.
                </DialogDescription>
            </DialogHeader>
            <Textarea
                v-model="body"
                rows="5"
                maxlength="2000"
                placeholder="What should be corrected or clarified?"
            />
            <p v-if="error" class="text-sm text-destructive">{{ error }}</p>
            <DialogFooter>
                <Button variant="outline" @click="open = false">Cancel</Button>
                <Button :disabled="pending" @click="submit">Send suggestion</Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
