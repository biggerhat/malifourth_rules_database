<script setup lang="ts">
import ContentReferences from "@/components/ContentReferences.vue";
import ParsedContent from "@/components/ParsedContent.vue";
import ScrollToTop from "@/components/ScrollToTop.vue";
import SeoHead from "@/components/SeoHead.vue";
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
    meta_description: {
        type: String,
        required: false,
        default() {
            return null;
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
    <SeoHead :title="props.title_text || props.title" :description="props.meta_description" />

    <div class="max-w-5xl mx-auto px-2 sm:px-4 text-foreground leading-6 text-md">
        <Alert v-if="props.viewing_old_version" variant="destructive" class="mb-4">
            <AlertDescription>
                You are viewing an older version of this content.
                <Link :href="props.current_version_url" class="underline font-medium ml-1">View the current version &rarr;</Link>
            </AlertDescription>
        </Alert>
        <div class="w-full max-w-2xl mx-auto text-center py-4">
            <img src='/Images/page_banner_top.png' alt="" class="w-40 sm:w-48 mx-auto opacity-90" />
            <h1 class="font-[family-name:var(--font-display)] text-2xl sm:text-3xl font-medium text-balance my-1" v-html="props.title"></h1>
            <img src='/Images/page_banner_bottom.png' alt="" class="w-40 sm:w-48 mx-auto opacity-90" />
        </div>

        <div class="rounded-md border-t-2 border-primary bg-card shadow-sm">
            <div class="p-6">
                <div class="grid grid-cols-1 gap-6" :class="props.right_column && props.right_column.length > 0 ? 'lg:grid-cols-2' : ''">
                    <div>
                        <ParsedContent :content="props.left_column" />
                    </div>
                    <div v-if="props.right_column && props.right_column.length > 0" class="lg:border-l lg:border-border lg:pl-6">
                        <ParsedContent :content="props.right_column" />
                    </div>
                </div>
            </div>
            <div class="border-t border-border px-6 py-4 text-xs text-muted-foreground italic text-right">
                Last Updated: {{ props.published_at }} by {{ props.published_by }}
            </div>
        </div>

        <ContentReferences
            v-if="props.references"
            :references="props.references.references"
            :referenced_by="props.references.referenced_by"
            :revision_history="props.references.revision_history"
        />

        <ScrollToTop />
    </div>
</template>

<style scoped>
:deep(.font-\[symbolFont\]) {
    font-size: 1.25rem;
}
</style>
