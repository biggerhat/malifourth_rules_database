<script setup lang="ts">
import ParsedContent from "@/components/ParsedContent.vue";

const props =defineProps({
    title: {
        type: String,
        required: false,
        default() {
            return '';
        }
    },
    left_column: {
        type: [Object, Array],
        required: false,
        default() {
            return '';
        }
    },
    right_column: {
        type: [Object, Array],
        required: false,
        default() {
            return '';
        }
    },
    page_number: {
        type: Number,
        required: false,
        default() {
            return null;
        }
    },
    book_page_numbers: {
        type: String,
        required: false,
        default() {
            return null;
        }
    },
    published_at: {
        type: String,
        required: false,
        default() {
            return '';
        }
    },
    published_by: {
        type: String,
        required: false,
        default() {
            return '';
        }
    }
});

</script>

<template>
            <div class="w-full text-center text-xl mb-4">
                {{ props.title }}
            </div>
            <div class="grid lg:grid-cols-2 gap-2 min-h-full overflow-y-auto">
                <div class="lg:pr-2 lg:border-r lg:px-0 px-2 leading-6 text-md">
                    <ParsedContent :content="props.left_column" />
                </div>
                <div class="lg:px-0 px-2">
                    <ParsedContent :content="props.right_column" />
                </div>
            </div>
</template>
