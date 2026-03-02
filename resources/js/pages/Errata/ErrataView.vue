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
import { ChevronLeft } from "lucide-vue-next";

const props = defineProps({
    errata: {
        type: Object,
        required: true,
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
    <Head :title="props.errata.title" />

    <div class="max-w-4xl mx-auto px-2 sm:px-4 text-primary leading-6 text-md">
        <Alert v-if="props.viewing_old_version" variant="destructive" class="mb-4">
            <AlertDescription>
                You are viewing an older version of this content.
                <Link :href="props.current_version_url" class="underline font-medium ml-1">View the current version &rarr;</Link>
            </AlertDescription>
        </Alert>

        <!-- Back link -->
        <div class="pt-4 mb-2">
            <Link
                :href="route('errata.index')"
                class="inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-primary transition-colors"
            >
                <ChevronLeft class="size-4" />
                Errata
            </Link>
        </div>

        <!-- Banner -->
        <div class="w-full text-center text-xl py-4">
            <img src='/Images/page_banner_top.png' alt="" class="w-3/4 sm:w-1/2 lg:w-1/3 mx-auto" />
            <span>{{ props.errata.title }}</span>
            <img src='/Images/page_banner_bottom.png' alt="" class="w-3/4 sm:w-1/2 lg:w-1/3 mx-auto" />
        </div>

        <!-- Content -->
        <Card v-if="props.errata.content && props.errata.content.length > 0" class="mb-8">
            <CardContent>
                <ParsedContent :content="props.errata.content" />
            </CardContent>
            <CardFooter v-if="!props.viewing_old_version" class="border-t px-6 py-3 text-xs text-muted-foreground justify-end">
                Last updated {{ props.errata.published_at }} by {{ props.errata.published_by }}
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
