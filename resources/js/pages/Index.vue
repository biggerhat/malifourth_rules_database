<script setup lang="ts">
import { Search } from 'lucide-vue-next'
import { Input } from '@/components/ui/input'
import { ref } from "vue";
import {router} from "@inertiajs/vue3";

const props = defineProps({
    wyrd_news: {
        type: [Object, Array],
        required: false,
        default() {
            return {};
        }
    },
    latest_updates: {
        type: [Object, Array],
        required: false,
        default() {
            return [];
        }
    }
});

const queryString = ref('');
const generalSearch = () => {
    if (queryString.value.length === 0) {
        return;
    }

    router.get(route('search'), {q: queryString.value}, {
        preserveState: true,
        replace: true,
    });
}

</script>

<template>
    <div class="min-h-screen pt-10 px-4">
        <div class="w-full max-w-2xl mx-auto text-center">
            <img src='/Images/page_banner_top.png' alt="" class="w-40 sm:w-48 mx-auto opacity-90" />
            <h1 class="font-[family-name:var(--font-display)] text-3xl sm:text-4xl font-medium text-balance my-2">Malifaux Rules Compendium</h1>
            <img src='/Images/page_banner_bottom.png' alt="" class="w-40 sm:w-48 mx-auto opacity-90" />
            <p class="text-[0.7rem] font-medium uppercase tracking-[0.18em] text-primary mt-2">Comprehensive Rules Database</p>
        </div>
        <div class="mt-8">
            <div class="relative w-full max-w-xl mx-auto">
                <Input id="search" type="text" autofocus placeholder="Search..." class="pl-10" @keydown.enter="generalSearch" v-model="queryString" />
                <span class="absolute start-0 inset-y-0 flex items-center justify-center px-2">
                    <Search class="size-6 text-muted-foreground" />
                </span>
            </div>
        </div>
        <div class="max-w-4xl mx-auto grid sm:grid-cols-2 mt-8 gap-4 pb-10">
            <div class="rounded-md border-t-2 border-primary bg-card shadow-sm">
                <div class="px-4 pt-4 pb-2">
                    <span class="text-[0.7rem] font-medium uppercase tracking-[0.14em] text-primary">Latest Updates</span>
                </div>
                <div class="px-2 pb-3">
                    <Link
                        v-for="(update, idx) in props.latest_updates"
                        :key="idx"
                        :href="update.href"
                        class="hover:bg-muted rounded-md px-2 py-2 block text-sm transition-colors"
                    >
                        <div class="font-medium">{{ update.title }}</div>
                        <div class="text-xs text-muted-foreground mt-0.5">{{ update.type }} &middot; {{ update.published_at }}</div>
                    </Link>
                    <div v-if="!props.latest_updates.length" class="text-sm text-muted-foreground px-2 py-2">
                        No updates yet.
                    </div>
                </div>
            </div>
            <div class="rounded-md border-t-2 border-primary bg-card shadow-sm">
                <div class="flex items-center gap-2 px-4 pt-4 pb-2">
                    <img src="/Images/wyrd-logo.png" alt="" class="h-5 w-auto" />
                    <span class="text-[0.7rem] font-medium uppercase tracking-[0.14em] text-primary">Latest Wyrd News</span>
                </div>
                <div class="px-2 pb-3">
                    <a v-for="news in props.wyrd_news" :href="news.url" :key="news.url" target="_blank" class="hover:bg-muted rounded-md px-2 py-2 block text-sm transition-colors">
                        {{ news.title }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</template>
