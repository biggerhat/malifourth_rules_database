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
import DraggableContent from "@/components/DraggableContent.vue";
import {router} from "@inertiajs/vue3";
import ScrollToTop from "@/components/ScrollToTop.vue";
import Button from "@/components/ui/button/Button.vue";

const props =defineProps({
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

    <div class="min-h-full mx-2 lg:mx-0">
        <Card>
            <CardHeader>
                <CardTitle>{{ props.title }}</CardTitle>
            </CardHeader>
            <CardContent>
                <div class="grid w-full mx-auto" :class="props.right_column && props.right_column.length > 0 ? 'grid-cols-2' : 'grid-cols-1'">
                    <div class="px-2">
                        <ParsedContent :content="props.left_column" />
                    </div>
                    <div v-if="props.right_column && props.right_column.length > 0" class="lg:border-l lg:border-secondary px-2">
                        <ParsedContent :content="props.right_column" />
                    </div>
                </div>
            </CardContent>
            <CardFooter class="mt-20 text-sm italic text-end">
                <div class="w-full text-end">Last Updated: {{ props.published_at }}<br />by {{ props.published_by}}</div>
            </CardFooter>
        </Card>
    </div>
</template>
