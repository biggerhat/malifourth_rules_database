<script setup lang="ts">
import ParsedContent from "@/components/ParsedContent.vue";
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card'

const props =defineProps({
    title: {
        type: String,
        required: false,
        default() {
            return '';
        }
    },
    type: {
        type: String,
        required: false,
        default() {
            return 'text';
        }
    },
    content: {
        type: [Object, Array, String],
        required: false,
        default() {
            return '';
        }
    },
    image: {
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
    <div>
        <img :src="props.image" :alt="props.title" class="mx-auto" v-if="props.type === 'image'" />
        <ParsedContent :content="props.content" v-if="props.type === 'text'" />
    </div>
</template>
