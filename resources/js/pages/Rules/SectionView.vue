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
    left_column: {
        type: [Object, Array, String],
        required: false,
        default() {
            return '';
        }
    },
    right_column: {
        type: [Object, Array, String],
        required: false,
        default() {
            return '';
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

    <div class="max-w-5xl mx-auto px-2 sm:px-4 text-primary leading-6 text-md">
        <div class="w-full text-center text-xl py-4">
            <img src='/Images/page_banner_top.png' alt="Banner Top" class="w-3/4 sm:w-1/2 lg:w-1/3 mx-auto" />
            <span v-html="props.title"></span>
            <img src='/Images/page_banner_bottom.png' alt="Banner Bottom" class="w-3/4 sm:w-1/2 lg:w-1/3 mx-auto" />
        </div>

        <Card>
            <CardContent class="pt-6">
                <div class="grid grid-cols-1 gap-6" :class="props.right_column && props.right_column.length > 0 ? 'lg:grid-cols-2' : ''">
                    <div>
                        <ParsedContent :content="props.left_column" />
                    </div>
                    <div v-if="props.right_column && props.right_column.length > 0" class="lg:border-l lg:border-border lg:pl-6">
                        <ParsedContent :content="props.right_column" />
                    </div>
                </div>
            </CardContent>
            <CardFooter class="border-t pt-4 text-xs text-muted-foreground italic justify-end">
                Last Updated: {{ props.published_at }} by {{ props.published_by }}
            </CardFooter>
        </Card>

        <ScrollToTop />
    </div>
</template>
