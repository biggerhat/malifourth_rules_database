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
    question: {
        type: String,
        required: false,
        default() {
            return '';
        }
    },
    answer: {
        type: [Object, Array, String],
        required: false,
        default() {
            return '';
        }
    },
    section: {
        type: [Object, Array, String],
        required: false,
        default() {
            return '';
        }
    },
    change_notes: {
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
    <Head title="FAQ" />

    <div class="min-h-full mx-2 lg:mx-0">
        <Card>
            <CardHeader>
                <CardTitle><ParsedContent :content="props.question" /></CardTitle>
            </CardHeader>
            <CardContent>
                <div class="grid w-full mx-auto">
                    <div class="px-2">
                        <ParsedContent :content="props.answer" />
                    </div>
                </div>
            </CardContent>
            <CardFooter class="mt-20 text-sm italic text-end">
                <div class="w-full text-end">Last Updated: {{ props.published_at }}<br />by {{ props.published_by}}</div>
            </CardFooter>
        </Card>
    </div>
</template>
