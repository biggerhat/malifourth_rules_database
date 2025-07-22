<script setup lang="ts">
import ParsedContent from "@/components/ParsedContent.vue";
import Button from "@/components/ui/button/Button.vue";
import ScrollToTop from "@/components/ScrollToTop.vue";

const props =defineProps({
    pages: {
        type: [Object, Array],
        required: false,
        default() {
            return {};
        }
    },
    title: {
        type: String,
        required: false,
        default() {
            return '';
        }
    },
    slug: {
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
    <Head :title="props.title" />

    <div class="grid grid-cols-1 lg:grid-cols-8 lg:gap-2 text-primary leading-6 text-md">
        <div class="lg:col-span-2 hidden lg:block">
            <div class="lg:col-span-2">
                <Link v-for="page in props.pages" :key="page.slug" :href="route('rules.page.view', page.slug)" class="p-2 block hover:bg-secondary" :class="page.slug === props.slug ? 'bg-secondary' : ''">
                    {{ page.page_number }}. {{ page.title }}
                </Link>
            </div>
        </div>
        <div class="lg:col-span-6">
            <div class="w-full text-center text-xl mb-4">
                {{ props.title }}
            </div>
            <div class="grid lg:grid-cols-2 gap-2 min-h-full">
                <div class="lg:pr-2 lg:border-r lg:px-0 px-2 pb-10">
                    <ParsedContent :content="props.left_column" />
                </div>
                <div class="lg:px-0 px-2">
                    <ParsedContent :content="props.right_column" />
                </div>
            </div>
            <div class="w-full text-center text-sm mt-4 p-4 flex justify-between">
                <div><Button disabled>Previous</Button></div>
                <div><Button>Next</Button></div>
            </div>
        </div>

        <ScrollToTop />
    </div>
</template>
