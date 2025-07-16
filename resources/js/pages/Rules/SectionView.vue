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
    content: {
        type: [Object, Array],
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
    <div class="container grid lg:grid-cols-3 mx-auto">
        <div>
        </div>
        <div class="min-h-full mx-2 lg:mx-0">
            <Card>
                <CardHeader>
                    <CardTitle>{{ props.title }}</CardTitle>
                </CardHeader>
                <CardContent>
                    <ParsedContent :content="props.content" />
                </CardContent>
                <CardFooter class="mt-20 text-sm italic text-end">
                    <div class="w-full text-end">Last Updated: {{ props.published_at }}<br />by {{ props.published_by}}</div>
                </CardFooter>
            </Card>
        </div>
        <div>

        </div>
    </div>
</template>
