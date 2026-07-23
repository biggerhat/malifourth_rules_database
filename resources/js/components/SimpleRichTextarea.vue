<script setup lang="ts">
import { ref } from 'vue';
import { Bold, Italic } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';

const props = defineProps({
    modelValue: {
        type: String,
        required: false,
        default() {
            return '';
        }
    },
    id: {
        type: String,
        required: false,
        default() {
            return null;
        }
    },
    placeholder: {
        type: String,
        required: false,
        default() {
            return '';
        }
    },
    rows: {
        type: Number,
        required: false,
        default() {
            return 4;
        }
    }
});

const emit = defineEmits(['update:modelValue']);

const textareaRef = ref(null);

function wrap(openTag, closeTag) {
    const el = textareaRef.value;
    if (!el) return;

    const start = el.selectionStart;
    const end = el.selectionEnd;
    const value = props.modelValue ?? '';
    const selected = value.slice(start, end);
    const newValue = value.slice(0, start) + openTag + selected + closeTag + value.slice(end);

    emit('update:modelValue', newValue);

    requestAnimationFrame(() => {
        el.focus();
        el.setSelectionRange(start + openTag.length, start + openTag.length + selected.length);
    });
}
</script>

<template>
    <div>
        <div class="flex gap-1 mb-1">
            <Button type="button" size="sm" variant="outline" @click="wrap('{{b}}', '{{/b}}')">
                <Bold class="h-4 w-4" />
            </Button>
            <Button type="button" size="sm" variant="outline" @click="wrap('{{i}}', '{{/i}}')">
                <Italic class="h-4 w-4" />
            </Button>
        </div>
        <textarea
            :id="id"
            ref="textareaRef"
            :value="modelValue"
            :placeholder="placeholder"
            :rows="rows"
            class="border-input placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-ring/50 flex field-sizing-content min-h-16 w-full rounded-md border bg-transparent px-3 py-2 text-base shadow-xs transition-[color,box-shadow] outline-none focus-visible:ring-[3px] disabled:cursor-not-allowed disabled:opacity-50 md:text-sm"
            @input="emit('update:modelValue', $event.target.value)"
        ></textarea>
    </div>
</template>
