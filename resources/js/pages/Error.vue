<script setup lang="ts">
import SeoHead from "@/components/SeoHead.vue";
import { Search, Home } from "lucide-vue-next";
import { ref } from "vue";
import { router } from "@inertiajs/vue3";
import { Input } from "@/components/ui/input";
import { Button } from "@/components/ui/button";

const props = defineProps({
    status: {
        type: Number,
        required: true,
    },
});

const copy: Record<number, { title: string; message: string }> = {
    404: {
        title: 'Page Not Found',
        message: "That page doesn't exist, or may have been moved or renamed.",
    },
    403: {
        title: 'Access Denied',
        message: "You don't have permission to view this page.",
    },
    419: {
        title: 'Session Expired',
        message: 'Your session expired. Please go back and try again.',
    },
    500: {
        title: 'Something Went Wrong',
        message: 'An unexpected error occurred on our end. Please try again shortly.',
    },
    503: {
        title: 'Down for Maintenance',
        message: "We're making some updates. Please check back in a few minutes.",
    },
};

const content = copy[props.status] ?? {
    title: 'Unexpected Error',
    message: 'Something went wrong loading this page.',
};

const queryString = ref('');
const generalSearch = () => {
    if (queryString.value.length === 0) {
        return;
    }

    router.get(route('search'), { q: queryString.value });
};
</script>

<template>
    <SeoHead :title="content.title" />

    <div class="min-h-[60vh] flex items-center justify-center px-4 py-10">
        <div class="w-full max-w-lg text-center">
            <img src='/Images/page_banner_top.png' alt="" class="w-40 sm:w-48 mx-auto opacity-90" />
            <p class="text-[0.7rem] font-medium uppercase tracking-[0.18em] text-primary mt-2">Error {{ props.status }}</p>
            <h1 class="font-[family-name:var(--font-display)] text-3xl sm:text-4xl font-medium text-balance my-2">{{ content.title }}</h1>
            <img src='/Images/page_banner_bottom.png' alt="" class="w-40 sm:w-48 mx-auto opacity-90" />

            <p class="text-muted-foreground mt-4 max-w-sm mx-auto">{{ content.message }}</p>

            <div class="mt-8 relative w-full max-w-sm mx-auto">
                <Input type="text" placeholder="Search the rules..." class="pl-10" @keydown.enter="generalSearch" v-model="queryString" />
                <span class="absolute start-0 inset-y-0 flex items-center justify-center px-2">
                    <Search class="size-5 text-muted-foreground" />
                </span>
            </div>

            <Button as-child class="mt-4">
                <Link :href="route('index')">
                    <Home class="size-4" />
                    Back to Home
                </Link>
            </Button>
        </div>
    </div>
</template>
