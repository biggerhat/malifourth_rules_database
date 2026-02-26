<script setup lang="ts">
import ParsedContent from "@/components/ParsedContent.vue";
import ScrollToTop from "@/components/ScrollToTop.vue";
import {
    Card,
    CardContent,
    CardFooter,
} from '@/components/ui/card'

const props = defineProps({
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
    <Head :title="props.title" />

    <div class="max-w-4xl mx-auto px-2 sm:px-4 text-primary leading-6 text-md">
        <div class="w-full text-center text-xl py-4">
            <img src='/Images/page_banner_top.png' alt="Banner Top" class="w-3/4 sm:w-1/2 lg:w-1/3 mx-auto" />
            <span v-html="props.title"></span>
            <img src='/Images/page_banner_bottom.png' alt="Banner Bottom" class="w-3/4 sm:w-1/2 lg:w-1/3 mx-auto" />
        </div>

        <Card>
            <CardContent class="pt-6">
                <img :src="props.image" :alt="props.title" class="mx-auto" v-if="props.type === 'image'" />
                <ParsedContent :content="props.content" v-if="props.type === 'text'" />
            </CardContent>
            <CardFooter class="border-t pt-4 text-xs text-muted-foreground italic justify-end">
                Last Updated: {{ props.published_at }} by {{ props.published_by }}
            </CardFooter>
        </Card>

        <ScrollToTop />
    </div>
</template>
