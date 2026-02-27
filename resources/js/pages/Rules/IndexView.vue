<script setup lang="ts">
import ContentReferences from "@/components/ContentReferences.vue";
import ParsedContent from "@/components/ParsedContent.vue";
import ScrollToTop from "@/components/ScrollToTop.vue";
import {
    Card,
    CardContent,
    CardFooter,
} from '@/components/ui/card'
import { Alert, AlertDescription } from "@/components/ui/alert";

const props = defineProps({
    title: {
        type: String,
        required: false,
        default() {
            return '';
        }
    },
    title_text: {
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
    },
    references: {
        type: Object,
        required: false,
        default() {
            return null;
        }
    },
    viewing_old_version: {
        type: Boolean,
        required: false,
        default() {
            return false;
        }
    },
    current_version_url: {
        type: String,
        required: false,
        default() {
            return null;
        }
    }
});
</script>

<template>
    <Head :title="props.title_text || props.title" />

    <div class="max-w-4xl mx-auto px-2 sm:px-4 text-primary leading-6 text-md">
        <Alert v-if="props.viewing_old_version" variant="destructive" class="mb-4">
            <AlertDescription>
                You are viewing an older version of this content.
                <Link :href="props.current_version_url" class="underline font-medium ml-1">View the current version &rarr;</Link>
            </AlertDescription>
        </Alert>
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

        <ContentReferences
            v-if="props.references"
            :references="props.references.references"
            :referenced_by="props.references.referenced_by"
            :revision_history="props.references.revision_history"
        />

        <ScrollToTop />
    </div>
</template>
