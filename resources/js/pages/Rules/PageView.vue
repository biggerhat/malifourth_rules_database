<script setup lang="ts">
import ParsedContent from "@/components/ParsedContent.vue";
import Button from "@/components/ui/button/Button.vue";
import ScrollToTop from "@/components/ScrollToTop.vue";
import { router } from "@inertiajs/vue3";
import {onMounted, ref, watch} from "vue";
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectLabel,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select'

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
    content: {
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
    },
    previous_page: {
        type: String,
        required: false,
        default() {
            return null;
        }
    },
    next_page: {
        type: String,
        required: false,
        default() {
            return null;
        }
    }
});

const pageParam = ref(props.slug);

const navigateToPage = () => {


};

watch(pageParam, (newValue) => {
    window.location.href = route('rules.page.view', pageParam.value);
});
</script>

<template>
    <Head :title="props.title" />

    <div class="grid grid-cols-1 lg:grid-cols-8 lg:gap-2 mx-1 text-primary leading-6 text-md">
        <div class="lg:col-span-2 hidden lg:block">
            <div class="lg:col-span-2">
                <Link v-for="page in props.pages" :key="page.slug" v-html="page.title" :href="route('rules.page.view', page.slug)" class="p-2 block hover:bg-secondary" :class="page.slug === props.slug ? 'bg-secondary' : ''">
                </Link>
            </div>
        </div>
        <div class="lg:col-span-6 min-h-screen">
            <div class="lg:hidden block mb-4 mx-2">
                <Select v-model="pageParam">
                    <SelectTrigger class="w-full">
                        <SelectValue placeholder="Select a Section" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectGroup>
                            <SelectLabel>Sections</SelectLabel>
                            <SelectItem v-for="page in props.pages" :key="page.slug" v-html="page.title" :value="page.slug">
                            </SelectItem>
                        </SelectGroup>
                    </SelectContent>
                </Select>
            </div>
            <div class="w-full text-center text-xl mb-4">
                <img src='/Images/page_banner_top.png' alt="Banner Top" class="w-3/4 lg:w-1/2 mx-auto" />
                <span v-html="props.title"></span>
                <img src='/Images/page_banner_bottom.png' alt="Banner Bottom" class="w-3/4 lg:w-1/2 mx-auto" />
            </div>
            <div class="min-h-full">
                <ParsedContent :content="props.content" />
            </div>
            <div class="w-full text-center text-sm mt-4 p-4 flex justify-between">
                <div><Button :disabled="!props.previous_page" @click="router.get(route('rules.page.view', props.previous_page))">Previous</Button></div>
                <div><Button :disabled="!props.next_page" @click="router.get(route('rules.page.view', props.next_page))">Next</Button></div>
            </div>
        </div>

        <ScrollToTop />
    </div>
</template>
