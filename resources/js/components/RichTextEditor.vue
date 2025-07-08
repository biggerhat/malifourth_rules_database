<script setup lang="ts">
import {Label} from "@/components/ui/label";
import {Button} from "@/components/ui/button";
import {Textarea} from "@/components/ui/textarea";
import { ref } from 'vue';
import axios from 'axios';

import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog'

const props = defineProps({
    label: {
        type: String,
        required: true,
        default() {
            return '';
        }
    },
    placeholder: {
        type: String,
        required: true,
        default() {
            return '';
        }
    },
})

const model = defineModel();
const indices = ref(null);
const sections = ref(null);
const pages = ref(null);

const boldStarted = ref(false);
const bold = () => {
    const textarea = document.getElementById('text_editor');

    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const selected  = textarea.value.substring(start, end);

    // If no text selected
    if (start === end) {
        if (boldStarted.value) {
            textarea.value += "{{/b}}";
        } else {
            textarea.value += "{{b}}";
        }
        boldStarted.value = !boldStarted.value;

        textarea.focus();
        return;
    }

    // Your replacement text
    const replacement = "{{b}}" + selected + "{{/b}}";

    // Replace selected text
    textarea.value = textarea.value.substring(0, start)
        + replacement
        + textarea.value.substring(end);

    // Move the cursor after the replacement
    textarea.selectionStart = textarea.selectionEnd = start + replacement.length;

    // Optionally focus the textarea again
    textarea.focus();
};

const italicStarted = ref(false);
const italic = () => {
    const textarea = document.getElementById('text_editor');

    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const selected  = textarea.value.substring(start, end);

    // If no text selected
    if (start === end) {
        if (italicStarted.value) {
            textarea.value += "{{/i}}";
        } else {
            textarea.value += "{{i}}";
        }
        italicStarted.value = !italicStarted.value;

        textarea.focus();
        return;
    }

    // Your replacement text
    const replacement = "{{i}}" + selected + "{{/i}}";

    // Replace selected text
    textarea.value = textarea.value.substring(0, start)
        + replacement
        + textarea.value.substring(end);

    // Move the cursor after the replacement
    textarea.selectionStart = textarea.selectionEnd = start + replacement.length;

    // Optionally focus the textarea again
    textarea.focus();
};

const underlineStarted = ref(false);
const underline = () => {
    const textarea = document.getElementById('text_editor');

    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const selected  = textarea.value.substring(start, end);

    // If no text selected
    if (start === end) {
        if (underlineStarted.value) {
            textarea.value += "{{/u}}";
        } else {
            textarea.value += "{{u}}";
        }
        underlineStarted.value = !underlineStarted.value;

        textarea.focus();
        return;
    }

    // Your replacement text
    const replacement = "{{u}}" + selected + "{{/u}}";

    // Replace selected text
    textarea.value = textarea.value.substring(0, start)
        + replacement
        + textarea.value.substring(end);

    // Move the cursor after the replacement
    textarea.selectionStart = textarea.selectionEnd = start + replacement.length;

    // Optionally focus the textarea again
    textarea.focus();
};

const loadIndices = () => {
    if (indices.value) {
        return
    }

    axios.get()
}

</script>

<template>
    <Label for="text_editor">{{ props.label }}</Label>
    <div class="flex gap-1">
        <div class="my-auto">Text Changes: </div>
        <Button type="button" :variant="boldStarted ? 'outline' : 'default'" @click="bold" class="font-bold p-2">Bold</Button>
        <Button type="button" :variant="italicStarted ? 'outline' : 'default'" @click="italic" class="italic p-2">Italic</Button>
        <Button type="button" :variant="underlineStarted ? 'outline' : 'default'" @click="underline" class="underline p-2">Underline</Button>
        <div>
            <Dialog>
                <DialogTrigger>
                    <Button type="button" class="p-2">Add Index Tooltip</Button>
                </DialogTrigger>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Edit profile</DialogTitle>
                        <DialogDescription>
                            Make changes to your profile here. Click save when you're done.
                        </DialogDescription>
                    </DialogHeader>

                    <DialogFooter>
                        Save changes
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </div>
        <div>
            <Dialog>
                <DialogTrigger>
                    <Button type="button" class="p-2">Add Section Link</Button>
                </DialogTrigger>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Edit profile</DialogTitle>
                        <DialogDescription>
                            Make changes to your profile here. Click save when you're done.
                        </DialogDescription>
                    </DialogHeader>

                    <DialogFooter>
                        Save changes
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </div>
        <div>
            <Dialog>
                <DialogTrigger>
                    <Button type="button" class="p-2">Add Page Link</Button>
                </DialogTrigger>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Edit profile</DialogTitle>
                        <DialogDescription>
                            Make changes to your profile here. Click save when you're done.
                        </DialogDescription>
                    </DialogHeader>

                    <DialogFooter>
                        Save changes
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </div>
    </div>
    <div class="flex gap-1">
        <div class="my-auto">Page Elements: </div>
        <div>
            <Dialog>
                <DialogTrigger>
                    <Button type="button" class="p-2">Add Index</Button>
                </DialogTrigger>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Edit profile</DialogTitle>
                        <DialogDescription>
                            Make changes to your profile here. Click save when you're done.
                        </DialogDescription>
                    </DialogHeader>

                    <DialogFooter>
                        Save changes
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </div>
        <div>
            <Dialog>
                <DialogTrigger>
                    <Button type="button" class="p-2">Add Section</Button>
                </DialogTrigger>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Edit profile</DialogTitle>
                        <DialogDescription>
                            Make changes to your profile here. Click save when you're done.
                        </DialogDescription>
                    </DialogHeader>

                    <DialogFooter>
                        Save changes
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </div>
        <div>
            <Dialog>
                <DialogTrigger>
                    <Button type="button" class="p-2">Add Section</Button>
                </DialogTrigger>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Edit profile</DialogTitle>
                        <DialogDescription>
                            Make changes to your profile here. Click save when you're done.
                        </DialogDescription>
                    </DialogHeader>

                    <DialogFooter>
                        Save changes
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </div>
    </div>
    <Textarea class="min-h-75" id="text_editor" v-model="model" :placeholder="props.placeholder" />
</template>
