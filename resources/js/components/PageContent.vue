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
    content: {
        type: [Object, Array, String],
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
            <div class="min-h-full overflow-y-auto">
                <ParsedContent :content="props.content" />
            </div>
</template>
